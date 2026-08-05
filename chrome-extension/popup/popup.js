document.addEventListener('DOMContentLoaded', async () => {
  const statusDot = document.getElementById('statusDot');
  const statusText = document.getElementById('statusText');
  const pageIndicator = document.getElementById('pageIndicator');
  const detectedCountEl = document.getElementById('detectedCount');
  const uploadedCountEl = document.getElementById('uploadedCount');
  const duplicatesCountEl = document.getElementById('duplicatesCount');
  const errorsCountEl = document.getElementById('errorsCount');
  const startBtn = document.getElementById('startBtn');
  const stopBtn = document.getElementById('stopBtn');
  const optionsBtn = document.getElementById('optionsBtn');

  optionsBtn.addEventListener('click', (e) => {
    e.preventDefault();
    chrome.runtime.openOptionsPage();
  });

  // Query active tab
  const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
  const isGoogleMaps = tab && tab.url && tab.url.includes('google.') && tab.url.includes('/maps');

  if (!isGoogleMaps) {
    pageIndicator.textContent = 'Please open Google Maps to start collection.';
    pageIndicator.style.color = '#f87171';
    startBtn.disabled = true;
  } else {
    pageIndicator.textContent = 'Google Maps';
    pageIndicator.style.color = '#cbd5e1';
    startBtn.disabled = false;
  }

  // Update UI from chrome.storage.local
  function updateUI() {
    chrome.storage.local.get(
      ['isCollecting', 'foundCount', 'collectedCount', 'duplicatesCount', 'errorsCount'],
      (data) => {
        const isCollecting = !!data.isCollecting;

        detectedCountEl.textContent = data.foundCount || 0;
        uploadedCountEl.textContent = data.collectedCount || 0;
        duplicatesCountEl.textContent = data.duplicatesCount || 0;
        errorsCountEl.textContent = data.errorsCount || 0;

        if (isCollecting) {
          statusDot.className = 'dot blue';
          statusText.textContent = 'Status: ● Collecting';
          startBtn.style.display = 'none';
          stopBtn.style.display = 'block';
        } else {
          statusDot.className = 'dot';
          statusText.textContent = 'Status: ● Ready';
          startBtn.style.display = 'block';
          stopBtn.style.display = 'none';
        }
      }
    );
  }

  setInterval(updateUI, 300);
  updateUI();

  // Start Collection - Resets session counters to 0 for fresh collection
  startBtn.addEventListener('click', async () => {
    if (!isGoogleMaps || !tab) return;

    chrome.storage.local.set({
      isCollecting: true,
      foundCount: 0,
      collectedCount: 0,
      duplicatesCount: 0,
      errorsCount: 0
    }, () => {
      updateUI();
      chrome.tabs.sendMessage(tab.id, { type: 'START_COLLECTION' }).catch(() => {});
    });
  });

  // Stop Collection
  stopBtn.addEventListener('click', async () => {
    chrome.storage.local.set({ isCollecting: false }, () => {
      updateUI();
      if (tab) {
        chrome.tabs.sendMessage(tab.id, { type: 'STOP_COLLECTION' }).catch(() => {});
      }
    });
  });

});
