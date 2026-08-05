/**
 * Direct Backend Client Utility for Chrome Extension
 * Sends collected business leads directly to Laravel backend
 */

const ApiClient = {
  /**
   * Helper to execute fetch request to backend
   */
  async request(endpoint, options = {}) {
    const settings = await StorageUtil.getSettings();
    const baseUrl = (settings.backendUrl || 'http://127.0.0.1:8000').replace(/\/+$/, '');
    const url = `${baseUrl}/api/v1${endpoint}`;

    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...(options.headers || {})
    };

    try {
      const response = await fetch(url, {
        ...options,
        headers
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        const errorMsg = data.message || `Server returned status: ${response.status}`;
        throw new Error(errorMsg);
      }

      return data;
    } catch (err) {
      StorageUtil.log('API Request Error:', err.message);
      throw err;
    }
  },

  /**
   * Test connection to backend
   */
  async testConnection() {
    return this.request('/me', { method: 'GET' });
  },

  /**
   * Create a collection session
   */
  async createCollectionSession(payload) {
    return this.request('/collection-sessions', {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  /**
   * Update collection session metrics/status
   */
  async updateCollectionSession(sessionId, payload) {
    return this.request(`/collection-sessions/${sessionId}`, {
      method: 'PATCH',
      body: JSON.stringify(payload)
    });
  },

  /**
   * Bulk upload business records directly to Laravel
   */
  async uploadBulkBusinesses(businesses, sessionId = null) {
    return this.request('/businesses/bulk', {
      method: 'POST',
      body: JSON.stringify({
        session_id: sessionId,
        businesses
      })
    });
  },

  /**
   * Remote log parsing or extension error
   */
  async reportError(errorType, message, payload = null, sessionId = null) {
    return this.request('/errors', {
      method: 'POST',
      body: JSON.stringify({
        collection_session_id: sessionId,
        error_type: errorType,
        message,
        payload
      })
    }).catch(() => {});
  }
};

if (typeof module !== 'undefined') {
  module.exports = ApiClient;
}
