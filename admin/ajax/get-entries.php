<?php
/**
 * AJAX Handler: Get Race Entries
 * Returns entries for a specific race
 */

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$raceId = intval($_GET['race_id'] ?? 0);

if ($raceId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid race ID']);
    exit;
}

try {
    $raceModel = new Race($db);
    $entries = $raceModel->getRaceEntries($raceId);

    echo json_encode([
        'success' => true,
        'data' => $entries,
        'count' => count($entries)
    ]);

} catch (Exception $e) {
    error_log("Get Entries Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>