/**
 * Storage Utility for Chrome Extension
 * Manages configuration settings and offline upload queue via chrome.storage.local
 */

const DEFAULT_SETTINGS = {
  backendUrl: 'http://127.0.0.1:8000',
  batchSize: 50,
  uploadInterval: 10, // seconds
  collectionDelay: 1000, // ms
  debugLogging: false
};

const StorageUtil = {
  /**
   * Retrieve extension configuration settings
   */
  async getSettings() {
    return new Promise((resolve) => {
      chrome.storage.local.get(['settings'], (result) => {
        resolve({ ...DEFAULT_SETTINGS, ...(result.settings || {}) });
      });
    });
  },

  /**
   * Save configuration settings
   */
  async saveSettings(settings) {
    const current = await this.getSettings();
    const updated = { ...current, ...settings };
    return new Promise((resolve) => {
      chrome.storage.local.set({ settings: updated }, () => {
        resolve(updated);
      });
    });
  },

  /**
   * Get items currently waiting in the offline queue
   */
  async getQueue() {
    return new Promise((resolve) => {
      chrome.storage.local.get(['offlineQueue'], (result) => {
        resolve(result.offlineQueue || []);
      });
    });
  },

  /**
   * Add business records to offline queue
   */
  async addToQueue(items) {
    const queue = await this.getQueue();
    // Avoid duplicate insertions into queue based on place_id or name+address
    const existingKeys = new Set(queue.map(i => i.place_id || `${i.name}|${i.address}`));
    const newItems = items.filter(i => {
      const key = i.place_id || `${i.name}|${i.address}`;
      if (existingKeys.has(key)) return false;
      existingKeys.add(key);
      return true;
    });

    const updatedQueue = [...queue, ...newItems];
    return new Promise((resolve) => {
      chrome.storage.local.set({ offlineQueue: updatedQueue }, () => {
        resolve(updatedQueue);
      });
    });
  },

  /**
   * Remove specified count of processed items from front of queue
   */
  async removeItemsFromQueue(count) {
    const queue = await this.getQueue();
    const updatedQueue = queue.slice(count);
    return new Promise((resolve) => {
      chrome.storage.local.set({ offlineQueue: updatedQueue }, () => {
        resolve(updatedQueue);
      });
    });
  },

  /**
   * Clear entire offline queue
   */
  async clearQueue() {
    return new Promise((resolve) => {
      chrome.storage.local.set({ offlineQueue: [] }, () => {
        resolve([]);
      });
    });
  },

  /**
   * Log message if debug logging enabled
   */
  async log(...args) {
    const settings = await this.getSettings();
    if (settings.debugLogging) {
      console.log('[MapsCollector]', ...args);
    }
  }
};

if (typeof module !== 'undefined') {
  module.exports = StorageUtil;
}
