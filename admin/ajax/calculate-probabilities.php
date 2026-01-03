<?php
/**
 * AJAX Handler: Auto-Calculate Win Probabilities
 */

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$raceId = intval($_GET['race_id'] ?? 0);

if ($raceId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid race ID']);
    exit;
}

try {
    // Get all entries with horse data using model
    $raceModel = new Race($db);
    $entries = $raceModel->getRaceEntries($raceId);
    
    if (empty($entries)) {
        echo json_encode(['success' => false, 'error' => 'No entries found']);
        exit;
    }
    
    $totalScore = 0;
    $scores = [];
    
    // Calculate raw scores for each horse
    foreach ($entries as $entry) {
        $score = 0;
        
        // Form analysis (recent positions)
        $form = $entry['form'] ?? '';
        if (!empty($form)) {
            $positions = array_map('intval', explode('-', $form));
            $formScore = 0;
            $weight = 1.0;
            foreach ($positions as $pos) {
                if ($pos > 0) {
                    $formScore += (6 - min(5, $pos)) * $weight;
                    $weight *= 0.8; // More recent races weighted higher
                }
            }
            $score += $formScore * 10;
        }
        
        // Rating factor
        $rating = $entry['rating'] ?? 80;
        $score += $rating * 0.5;
        
        // Win rate factor
        $starts = max(1, $entry['career_starts']);
        $wins = $entry['career_wins'] ?? 0;
        $winRate = ($wins / $starts) * 100;
        $score += $winRate * 2;
        
        $scores[$entry['id']] = max(1, $score);
        $totalScore += $scores[$entry['id']];
    }
    
    // Convert scores to probabilities
    $entryModel = new RaceEntry($db);

    foreach ($scores as $entryId => $score) {
        $winProb = round(($score / $totalScore) * 100, 1);
        $placeProb = round(min(95, $winProb * 2.2), 1);

        $entryModel->update($entryId, ['win_probability' => $winProb, 'place_probability' => $placeProb]);
    }
    
    logActivity('Calculated probabilities', 'race', $raceId);
    
    echo json_encode(['success' => true, 'message' => 'Probabilities calculated for ' . count($entries) . ' entries']);
    
} catch (Exception $e) {
    error_log("Calculate Probabilities Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Calculation failed']);
}
?>