/**
 * Service Worker Background Script
 */

chrome.runtime.onInstalled.addListener(() => {
  chrome.storage.local.set({
    isCollecting: false,
    foundCount: 0,
    collectedCount: 0,
    duplicatesCount: 0,
    errorsCount: 0
  });
});
