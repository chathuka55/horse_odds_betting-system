<?php
/**
 * External API Integration Handler
 * Connects to third-party racing APIs
 */
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/api-handler.php';

$action = $_GET['action'] ?? '';
$apiName = $_GET['api'] ?? 'theracingapi';

if (empty($action)) {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
    exit;
}

try {
    $apiHandler = new APIHandler(null, $apiName);
    
    switch ($action) {
        case 'get_races':
            $date = $_GET['date'] ?? date('Y-m-d');
            $result = $apiHandler->getRaces($date);
            break;
            
        case 'get_race_details':
            $raceId = $_GET['race_id'] ?? '';
            $result = $apiHandler->getRaceDetails($raceId);
            break;
            
        case 'get_odds':
            $raceId = $_GET['race_id'] ?? '';
            $result = $apiHandler->getOdds($raceId);
            break;
            
        default:
            $result = ['success' => false, 'error' => 'Unknown action'];
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>