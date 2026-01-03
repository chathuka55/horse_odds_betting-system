<?php
/**
 * AJAX Handler: Test API Connection
 */
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$apiId = intval($_GET['api_id'] ?? 0);

if ($apiId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid API ID']);
    exit;
}

try {
    $apiModel = new ApiSetting($db);
    $api = $apiModel->getById($apiId);

    if (!$api) {
        echo json_encode(['success' => false, 'error' => 'API not found']);
        exit;
    }
    
    // Test connection
    $startTime = microtime(true);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['base_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . ($api['api_key'] ?? '')
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $responseTime = round((microtime(true) - $startTime) * 1000, 2);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode([
            'success' => true,
            'message' => 'Connection successful',
            'response_time' => $responseTime,
            'http_code' => $httpCode
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => "HTTP Error {$httpCode}: " . ($error ?: 'Connection failed'),
            'http_code' => $httpCode
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>