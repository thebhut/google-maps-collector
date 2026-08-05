/**
 * Robust Google Maps DOM Parser Module
 * Filters out UI navigation labels (e.g. "Results", "Visited link", "Directions")
 * and extracts exact business name, full address, phone, website, category, rating.
 */

const MapsParser = {
  blacklistedNames: [
    'results', 'search', 'back to top', 'visited link', 'directions', 'website',
    'permanently closed', 'temporarily closed', 'open', 'closed', 'rating',
    'share', 'save', 'nearby', 'send to phone', 'overview', 'reviews', 'about',
    'photos', 'updates', 'claim this business', 'suggest an edit', 'menu'
  ],

  isValidName(text) {
    if (!text || typeof text !== 'string') return false;
    const clean = text.trim().toLowerCase();
    if (clean.length < 2) return false;
    if (this.blacklistedNames.includes(clean)) return false;
    if (clean.startsWith('results for') || clean.startsWith('search for') || clean.startsWith('search maps')) return false;
    return true;
  },

  getBusinessName(profilePane, card) {
    // 1. Card title .qBF1Pd or fontHeadlineSmall
    if (card) {
      const qbf = card.querySelector('.qBF1Pd, div.qBF1Pd, .fontHeadlineSmall');
      if (qbf && this.isValidName(qbf.textContent)) {
        return qbf.textContent.trim();
      }

      const placeLink = card.querySelector('a[href*="/maps/place/"]');
      if (placeLink) {
        const aria = placeLink.getAttribute('aria-label');
        if (aria && this.isValidName(aria)) return aria.trim();
        if (this.isValidName(placeLink.textContent)) return placeLink.textContent.trim();
      }
    }

    // 2. Profile pane main heading (h1.DUwDvf)
    if (profilePane) {
      const h1 = profilePane.querySelector('h1.DUwDvf, h1[class*="title"], h1');
      if (h1 && this.isValidName(h1.textContent)) {
        return h1.textContent.trim();
      }
    }

    return null;
  },

  getFullAddress(profilePane, card) {
    if (profilePane) {
      const addrBtn = profilePane.querySelector('button[data-item-id="address"], button[data-tooltip*="address"], button[aria-label*="Address:"]');
      if (addrBtn) {
        const label = addrBtn.getAttribute('aria-label') || addrBtn.textContent;
        const clean = label.replace(/^Address:\s*/i, '').trim();
        if (clean.length > 3 && !clean.toLowerCase().includes('visited link')) {
          return clean;
        }
      }
    }

    if (card) {
      const addrBtn = card.querySelector('button[data-item-id="address"], button[data-tooltip*="address"]');
      if (addrBtn) {
        const label = addrBtn.getAttribute('aria-label') || addrBtn.textContent;
        const clean = label.replace(/^Address:\s*/i, '').trim();
        if (clean.length > 3 && !clean.toLowerCase().includes('visited link')) return clean;
      }

      const lines = (card.innerText || '').split('\n').map(l => l.trim());
      for (const line of lines) {
        if (line.includes('·') && !line.includes('Open') && !line.includes('Closed') && !line.includes('★')) {
          const parts = line.split('·');
          if (parts.length > 1) {
            const addrPart = parts[parts.length - 1].trim();
            if (addrPart.length > 3 && !addrPart.toLowerCase().includes('visited link')) {
              return addrPart;
            }
          }
        }
      }
    }

    return null;
  },

  getPhone(profilePane, card) {
    // Prefer card element text first to prevent profile pane cross-lead data leaks
    if (card) {
      const phoneBtn = card.querySelector('button[data-item-id*="phone:"], button[data-tooltip*="phone"], a[href^="tel:"]');
      if (phoneBtn) {
        const text = phoneBtn.getAttribute('aria-label') || phoneBtn.textContent;
        const match = text.match(/[\+\d\s\-\(\)\.]{7,20}/);
        if (match) return match[0].replace(/^Phone:\s*/i, '').trim();
      }

      const matches = (card.innerText || '').match(/(?:\+?\d{1,4}[\s\-]?)?(?:\(?\d{2,5}\)?[\s\-]?)?\d{3,5}[\s\-]?\d{3,5}/g);
      if (matches) {
        for (const m of matches) {
          const digits = m.replace(/\D/g, '');
          if (digits.length >= 8 && digits.length <= 15) {
            return m.trim();
          }
        }
      }
    }

    if (profilePane) {
      const phoneBtn = profilePane.querySelector('button[data-item-id*="phone:"], button[data-tooltip*="phone"], a[href^="tel:"]');
      if (phoneBtn) {
        const text = phoneBtn.getAttribute('aria-label') || phoneBtn.textContent;
        const match = text.match(/[\+\d\s\-\(\)\.]{7,20}/);
        if (match) return match[0].replace(/^Phone:\s*/i, '').trim();
      }
    }

    return null;
  },

  getCategory(profilePane, card, businessName = '') {
    if (profilePane) {
      const catBtn = profilePane.querySelector('button[jsaction*="category"], .fontBodyMedium span button');
      if (catBtn && catBtn.textContent.trim()) {
        const text = catBtn.textContent.trim();
        if (this.isValidName(text) && (!businessName || !text.toLowerCase().includes(businessName.toLowerCase()))) {
          return text;
        }
      }
    }

    if (card) {
      const catBtn = card.querySelector('button[jsaction*="category"], .fontBodyMedium span button, .W4Efsd span:first-child');
      if (catBtn && catBtn.textContent.trim()) {
        const text = catBtn.textContent.trim().split('·')[0].trim();
        if (this.isValidName(text) && (!businessName || !text.toLowerCase().includes(businessName.toLowerCase()))) {
          return text;
        }
      }

      const lines = (card.innerText || '').split('\n').map(l => l.trim());
      for (const line of lines) {
        if (line.includes('·')) {
          const first = line.split('·')[0].trim();
          if (first && this.isValidName(first) && (!businessName || !first.toLowerCase().includes(businessName.toLowerCase()))) {
            return first;
          }
        }
      }
    }

    return null;
  },

  getRating(profilePane, card) {
    if (card) {
      const ratingEl = card.querySelector('.ceSTg, .F7fe3c, .mw4A0, span[role="img"][aria-label*="stars"], span[aria-label*="rating"]');
      if (ratingEl) {
        const text = ratingEl.getAttribute('aria-label') || ratingEl.textContent;
        const match = text.match(/([0-5]\.\d|[0-5])/);
        if (match) return parseFloat(match[1]);
      }

      const match = (card.innerText || '').match(/([0-5]\.\d)\s*(?:★|\()/);
      if (match) return parseFloat(match[1]);
    }

    if (profilePane) {
      const ratingEl = profilePane.querySelector('.ceSTg, .F7fe3c, .mw4A0, span[role="img"][aria-label*="stars"], span[aria-label*="rating"]');
      if (ratingEl) {
        const text = ratingEl.getAttribute('aria-label') || ratingEl.textContent;
        const match = text.match(/([0-5]\.\d|[0-5])/);
        if (match) return parseFloat(match[1]);
      }
    }

    return null;
  },

  getReviewCount(profilePane, card) {
    if (card) {
      const reviewEl = card.querySelector('.UY7F9, span[aria-label*="reviews"]');
      if (reviewEl) {
        const text = reviewEl.getAttribute('aria-label') || reviewEl.textContent;
        const match = text.match(/([\d,]+)/);
        if (match) return parseInt(match[1].replace(/,/g, ''), 10);
      }

      const match = (card.innerText || '').match(/\(([\d,]+)\)/);
      if (match) return parseInt(match[1].replace(/,/g, ''), 10);
    }

    if (profilePane) {
      const reviewEl = profilePane.querySelector('.UY7F9, span[aria-label*="reviews"]');
      if (reviewEl) {
        const text = reviewEl.getAttribute('aria-label') || reviewEl.textContent;
        const match = text.match(/([\d,]+)/);
        if (match) return parseInt(match[1].replace(/,/g, ''), 10);
      }
    }

    return null;
  },

  getWebsite(profilePane, card) {
    const containers = [card, profilePane].filter(Boolean);
    for (const c of containers) {
      const webBtn = c.querySelector('a[data-item-id="authority"], a[data-tooltip*="website"], a[aria-label*="website"], a[data-value="Website"]');
      if (webBtn && webBtn.href) return webBtn.href;

      const extLink = c.querySelector('a[href^="http"]:not([href*="google.com"])');
      if (extLink && extLink.href) return extLink.href;
    }
    return null;
  },

  getMapsUrl(profilePane, card) {
    if (card) {
      const anchor = card.querySelector('a[href*="/maps/place/"]');
      if (anchor && anchor.href) return anchor.href;
    }
    if (window.location.href.includes('/maps/place/')) {
      return window.location.href;
    }
    return window.location.href;
  },

  parseProfileOrCard(profilePane, card) {
    const name = this.getBusinessName(profilePane, card);
    if (!name) return null;

    const address = this.getFullAddress(profilePane, card);
    const phone = this.getPhone(profilePane, card);
    const website = this.getWebsite(profilePane, card);
    const category = this.getCategory(profilePane, card, name);
    const rating = this.getRating(profilePane, card);
    const reviewCount = this.getReviewCount(profilePane, card);
    const mapsUrl = this.getMapsUrl(profilePane, card);

    let placeId = null;
    const match = mapsUrl.match(/!1s(0x[0-9a-f]+:0x[0-9a-f]+)/i);
    if (match) placeId = match[1];

    return {
      name,
      phone,
      address,
      website,
      category,
      rating,
      review_count: reviewCount,
      maps_url: mapsUrl,
      place_id: placeId
    };
  }
};

if (typeof module !== 'undefined') {
  module.exports = MapsParser;
}
