<?php
/**
 * AJAX Handler: Update Odds
 * Handles real-time odds updates
 */

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$entryId = intval($input['entry_id'] ?? $_POST['entry_id'] ?? 0);
$odds = sanitize($input['odds'] ?? $_POST['odds'] ?? '');

if ($entryId <= 0 || empty($odds)) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Convert fractional to decimal
$oddsDecimal = fractionalToDecimal($odds);

// Calculate win probability from odds
$winProbability = round((1 / $oddsDecimal) * 100, 2);
$placeProbability = round($winProbability * 2.1, 2);

try {
    // Update race entry via model
    $entryModel = new RaceEntry($db);
    $entryModel->update($entryId, [
        'current_odds' => $odds,
        'odds_decimal' => $oddsDecimal,
        'win_probability' => $winProbability,
        'place_probability' => $placeProbability
    ]);

    // Record in odds history via model
    $oddsModel = new OddsHistory($db);
    $oddsModel->create([
        'race_entry_id' => $entryId,
        'odds_value' => $odds,
        'odds_decimal' => $oddsDecimal
    ]);

    // Get updated entry info
    $entry = $entryModel->getById($entryId);
    $horseName = '';
    if (!empty($entry['horse_id'])) {
        $horseModel = new Horse($db);
        $horse = $horseModel->getById($entry['horse_id']);
        $horseName = $horse['name'] ?? '';
    }

    echo json_encode([
        'success' => true,
        'message' => 'Odds updated successfully',
        'data' => [
            'entry_id' => $entryId,
            'horse_name' => $horseName,
            'current_odds' => $odds,
            'odds_decimal' => $oddsDecimal,
            'win_probability' => $winProbability,
            'place_probability' => $placeProbability
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Update Odds Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>