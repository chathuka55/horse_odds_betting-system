<?php
/**
 * AJAX: Save API Settings
 */
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$apiId = intval($_POST['api_id'] ?? 0);

if ($apiId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid API ID']);
    exit;
}

// Collect & sanitize input
$data = [
    'api_key' => sanitize($_POST['api_key'] ?? ''),
    'api_secret' => sanitize($_POST['api_secret'] ?? ''),
    'base_url' => sanitize($_POST['base_url'] ?? ''),
    'sync_interval' => intval($_POST['sync_interval'] ?? 0),
    'is_active' => isset($_POST['is_active']) ? 1 : 0
];

try {
    $apiModel = new ApiSetting($db);
    $updated = $apiModel->update($apiId, $data);

    if ($updated) {
        logActivity('Updated API settings', 'api_settings', $apiId, null, $data);
        echo json_encode(['success' => true, 'message' => 'API settings saved']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save API settings']);
    }
} catch (Exception $e) {
    error_log('Save API settings error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to save']);
}
?>