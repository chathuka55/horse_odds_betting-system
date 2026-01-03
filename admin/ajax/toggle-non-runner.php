<?php
/**
 * AJAX Handler: Toggle Non-Runner Status
 */

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$entryId = intval($input['entry_id'] ?? 0);
$isNonRunner = intval($input['is_non_runner'] ?? 0);

if ($entryId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid entry ID']);
    exit;
}

try {
    $entryModel = new RaceEntry($db);
    $entryModel->update($entryId, ['is_non_runner' => $isNonRunner]);

    // Update race runner count
    $entry = $entryModel->getById($entryId);
    if ($entry && !empty($entry['race_id'])) {
        $raceId = $entry['race_id'];
        $count = $entryModel->count('race_id = ? AND is_non_runner = 0', [$raceId]);
        $raceModel = new Race($db);
        $raceModel->update($raceId, ['total_runners' => $count]);
    }

    logActivity($isNonRunner ? 'Marked as non-runner' : 'Marked as running', 'race_entry', $entryId);

    echo json_encode(['success' => true, 'message' => 'Status updated']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>