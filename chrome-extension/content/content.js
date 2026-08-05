/**
 * Google Maps Content Script - Synchronized Auto-Click & Auto-Scroll Deep Scraping
 * Clicks business cards, waits for profile pane synchronization, extracts full address & details,
 * highlights with WHITE (Scanning) -> GREEN (Found), and uploads leads directly to Laravel backend.
 */

(function () {
  let isCollecting = false;
  let isScanningLoopRunning = false;
  let processedCards = new Set();
  const BACKEND_URL = 'http://127.0.0.1:8000/api/v1/businesses/bulk';

  function checkState() {
    chrome.storage.local.get(['isCollecting'], (data) => {
      isCollecting = !!data.isCollecting;
      if (isCollecting && !isScanningLoopRunning) {
        startScanningLoop();
      }
    });
  }

  chrome.storage.onChanged.addListener((changes) => {
    if (changes.isCollecting) {
      isCollecting = changes.isCollecting.newValue;
      if (isCollecting && !isScanningLoopRunning) {
        startScanningLoop();
      }
    }
  });

  chrome.runtime.onMessage.addListener((msg) => {
    if (msg.type === 'START_COLLECTION') {
      isCollecting = true;
      processedCards = new Set();
      document.querySelectorAll('.gmaps-collector-badge').forEach(b => b.remove());
      document.querySelectorAll('.gmaps-collector-scanning, .gmaps-collector-lead-found, .gmaps-collector-not-lead').forEach(el => {
        el.classList.remove('gmaps-collector-scanning', 'gmaps-collector-lead-found', 'gmaps-collector-not-lead');
      });

      if (!isScanningLoopRunning) {
        startScanningLoop();
      }
    } else if (msg.type === 'STOP_COLLECTION') {
      isCollecting = false;
    }
  });

  function getSearchListContainer() {
    return document.querySelector('div[role="feed"], div.m6QEbc[aria-label^="Results for"], div.ec25xe, div.m6QEbc');
  }

  function getBusinessCards() {
    const cards = [];
    document.querySelectorAll('div.Nv2Pk').forEach(el => cards.push(el));
    document.querySelectorAll('a[href*="/maps/place/"]').forEach(a => {
      const card = a.closest('div.Nv2Pk, div[role="article"]');
      if (card && !cards.includes(card)) {
        cards.push(card);
      }
    });
    return cards;
  }

  function setCardState(card, state) {
    if (!card || card === document.body) return;

    card.classList.remove('gmaps-collector-scanning', 'gmaps-collector-lead-found', 'gmaps-collector-not-lead');
    const oldBadge = card.querySelector('.gmaps-collector-badge');
    if (oldBadge) oldBadge.remove();

    const badge = document.createElement('div');
    badge.className = 'gmaps-collector-badge ' + state;

    if (state === 'scanning') {
      card.classList.add('gmaps-collector-scanning');
      badge.textContent = '🔍 Opening Details...';
    } else if (state === 'found') {
      card.classList.add('gmaps-collector-lead-found');
      badge.textContent = '✓ Lead Found';
    } else if (state === 'skipped') {
      card.classList.add('gmaps-collector-not-lead');
      badge.textContent = '⚠ Skipped';
    }

    card.appendChild(badge);
  }

  async function waitForProfileToLoad(expectedName, maxWaitMs = 1000) {
    const startTime = Date.now();
    const cleanExpected = expectedName.toLowerCase().substring(0, 6);

    while (Date.now() - startTime < maxWaitMs) {
      const profilePane = document.querySelector('div[role="main"]');
      if (profilePane) {
        const h1 = profilePane.querySelector('h1.DUwDvf, h1');
        if (h1 && h1.textContent.trim().toLowerCase().includes(cleanExpected)) {
          return profilePane;
        }
      }
      await new Promise(r => setTimeout(r, 100));
    }

    return document.querySelector('div[role="main"]');
  }

  async function saveLeadToLaravel(lead) {
    try {
      const res = await fetch(BACKEND_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          businesses: [lead]
        })
      });

      const data = await res.json();

      chrome.storage.local.get(['foundCount', 'collectedCount', 'duplicatesCount', 'errorsCount'], (store) => {
        let found = store.foundCount || 0;
        let collected = store.collectedCount || 0;
        let duplicates = store.duplicatesCount || 0;
        let errors = store.errorsCount || 0;

        if (data && data.success && data.summary) {
          if (data.summary.collected > 0) {
            found += 1;
            collected += data.summary.collected;
          } else if (data.summary.failed > 0) {
            errors += data.summary.failed;
          }
        } else {
          errors += 1;
        }

        chrome.storage.local.set({
          foundCount: found,
          collectedCount: collected,
          duplicatesCount: duplicates,
          errorsCount: errors
        });
      });
    } catch (err) {
      console.error('[GmapsCollector] Save lead failed:', err);
    }
  }

  async function startScanningLoop() {
    if (isScanningLoopRunning) return;
    isScanningLoopRunning = true;

    while (isCollecting) {
      const cards = getBusinessCards();
      let processedAnyInThisPass = false;

      for (const card of cards) {
        if (!isCollecting) break;
        if (processedCards.has(card)) continue;

        processedCards.add(card);
        processedAnyInThisPass = true;

        try {
          card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (e) {}

        setCardState(card, 'scanning');

        const cardName = MapsParser.getBusinessName(null, card);
        const clickTarget = card.querySelector('a[href*="/maps/place/"], .qBF1Pd, h1, h2, h3, [role="heading"]') || card;
        clickTarget.click();

        let profilePane = null;
        if (cardName) {
          profilePane = await waitForProfileToLoad(cardName, 900);
        } else {
          await new Promise(r => setTimeout(r, 700));
          profilePane = document.querySelector('div[role="main"]');
        }

        const fullLead = MapsParser.parseProfileOrCard(profilePane, card);

        if (fullLead && fullLead.name && MapsParser.isValidName(fullLead.name)) {
          setCardState(card, 'found');
          await saveLeadToLaravel(fullLead);
        } else {
          setCardState(card, 'skipped');
        }

        await new Promise(resolve => setTimeout(resolve, 200));
      }

      const feedContainer = getSearchListContainer();
      if (feedContainer) {
        feedContainer.scrollTop += 500;
      } else {
        window.scrollBy(0, 400);
      }

      await new Promise(resolve => setTimeout(resolve, 800));

      const endText = document.body.innerText.includes("You've reached the end of the list");
      if (!processedAnyInThisPass && endText) {
        break;
      }
    }

    isScanningLoopRunning = false;
  }

  checkState();

})();
