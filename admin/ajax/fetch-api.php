<?php
/**
 * AJAX Handler: Fetch Data from External API
 * Manually trigger API data sync
 */
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/api-handler.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$apiId = intval($_POST['api_id'] ?? 0);
$dataType = sanitize($_POST['data_type'] ?? 'races'); // races, horses, results

if ($apiId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid API ID']);
    exit;
}

try {
    $apiHandler = new APIHandler($apiId);
    $result = $apiHandler->fetchAndSync($dataType);

    if ($result['success']) {
        // Update last sync time via model
        $apiModel = new ApiSetting($db);
        $apiModel->update($apiId, ['last_sync' => date('Y-m-d H:i:s')]);

        logActivity("Synced {$dataType} from API", 'api_settings', $apiId);

        echo json_encode([
            'success' => true,
            'message' => "Successfully synced {$result['count']} {$dataType}",
            'count' => $result['count']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Sync failed'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Fetch API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'API sync failed: ' . $e->getMessage()]);
}
?>