document.addEventListener('DOMContentLoaded', async () => {
  const settingsForm = document.getElementById('settingsForm');
  const backendUrlInput = document.getElementById('backendUrl');
  const batchSizeInput = document.getElementById('batchSize');
  const uploadIntervalInput = document.getElementById('uploadInterval');
  const collectionDelayInput = document.getElementById('collectionDelay');
  const debugLoggingInput = document.getElementById('debugLogging');
  const alertMessage = document.getElementById('alertMessage');
  const testConnBtn = document.getElementById('testConnBtn');
  const clearQueueBtn = document.getElementById('clearQueueBtn');

  // Load existing settings
  const settings = await StorageUtil.getSettings();
  backendUrlInput.value = settings.backendUrl || 'http://127.0.0.1:8000';
  batchSizeInput.value = settings.batchSize || 50;
  uploadIntervalInput.value = settings.uploadInterval || 10;
  collectionDelayInput.value = settings.collectionDelay || 1000;
  debugLoggingInput.checked = !!settings.debugLogging;

  function showAlert(msg, type = 'success') {
    alertMessage.textContent = msg;
    alertMessage.className = `alert ${type}`;
    alertMessage.style.display = 'block';
    setTimeout(() => {
      alertMessage.style.display = 'none';
    }, 5000);
  }

  // Save Settings Form
  settingsForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const updated = {
      backendUrl: backendUrlInput.value.trim(),
      batchSize: parseInt(batchSizeInput.value, 10) || 50,
      uploadInterval: parseInt(uploadIntervalInput.value, 10) || 10,
      collectionDelay: parseInt(collectionDelayInput.value, 10) || 1000,
      debugLogging: debugLoggingInput.checked
    };

    await StorageUtil.saveSettings(updated);
    showAlert('Settings saved successfully!');
  });

  // Test Connection Button
  testConnBtn.addEventListener('click', async () => {
    testConnBtn.disabled = true;
    testConnBtn.textContent = 'Testing...';

    await StorageUtil.saveSettings({
      backendUrl: backendUrlInput.value.trim()
    });

    try {
      const res = await ApiClient.testConnection();
      if (res && res.user) {
        showAlert(`Backend Connection Successful! Connected to: ${res.user.name} (${res.user.email})`, 'success');
      } else {
        showAlert('Connected to backend successfully!', 'success');
      }
    } catch (err) {
      showAlert(`Connection test failed: ${err.message}`, 'error');
    } finally {
      testConnBtn.disabled = false;
      testConnBtn.textContent = 'Test Connection';
    }
  });

  // Clear Offline Queue
  clearQueueBtn.addEventListener('click', async () => {
    if (confirm('Are you sure you want to clear the local upload queue? Un-uploaded records will be discarded.')) {
      await StorageUtil.clearQueue();
      showAlert('Offline upload queue cleared.', 'success');
    }
  });
});
