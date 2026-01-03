<?php
/**
 * AJAX Handler: Toggle API Status
 */
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$apiId = intval($input['api_id'] ?? 0);
$isActive = intval($input['is_active'] ?? 0);

if ($apiId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid API ID']);
    exit;
}

try {
    $apiModel = new ApiSetting($db);
    $apiModel->update($apiId, ['is_active' => $isActive]);
    logActivity($isActive ? 'Enabled API' : 'Disabled API', 'api_settings', $apiId);
    echo json_encode(['success' => true, 'message' => 'API status updated']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to update status']);
}
?>