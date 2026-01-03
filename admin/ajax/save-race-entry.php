<?php
/**
 * AJAX Handler: Save Race Entry
 * Handles adding horses to races
 */

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$entryId = intval($_POST['entry_id'] ?? 0);
$isEdit = $entryId > 0;

// Validate required fields
if (empty($_POST['race_id']) || empty($_POST['horse_id'])) {
    echo json_encode(['success' => false, 'error' => 'Race and Horse are required']);
    exit;
}

$data = [
    'race_id' => intval($_POST['race_id']),
    'horse_id' => intval($_POST['horse_id']),
    'jockey_id' => !empty($_POST['jockey_id']) ? intval($_POST['jockey_id']) : null,
    'saddle_number' => intval($_POST['saddle_number'] ?? 0),
    'draw_position' => intval($_POST['draw_position'] ?? 0),
    'weight_carried' => sanitize($_POST['weight_carried'] ?? ''),
    'official_rating' => intval($_POST['official_rating'] ?? 0),
    'current_odds' => sanitize($_POST['current_odds'] ?? ''),
    'odds_decimal' => floatval($_POST['odds_decimal'] ?? 0),
    'win_probability' => floatval($_POST['win_probability'] ?? 0),
    'place_probability' => floatval($_POST['place_probability'] ?? 0),
    'is_favorite' => isset($_POST['is_favorite']) ? 1 : 0,
    'is_non_runner' => isset($_POST['is_non_runner']) ? 1 : 0,
    'equipment' => sanitize($_POST['equipment'] ?? ''),
    'medication' => sanitize($_POST['medication'] ?? ''),
    'comments' => sanitize($_POST['comments'] ?? '')
];

try {
    // Use models for operations
    $entryModel = new RaceEntry($db);
    $raceModel = new Race($db);

    // Check if horse is already entered in this race (for new entries)
    if (!$isEdit) {
        $existing = $entryModel->getOne('race_id = ? AND horse_id = ?', [$data['race_id'], $data['horse_id']]);
        if ($existing) {
            echo json_encode(['success' => false, 'error' => 'This horse is already entered in this race']);
            exit;
        }
    }

    if ($isEdit) {
        $old = $entryModel->getById($entryId);
        $updated = $entryModel->update($entryId, $data);

        if ($updated) {
            // Record odds history via model
            if (!empty($data['current_odds'])) {
                $oddsModel = new OddsHistory($db);
                $oddsModel->create([
                    'race_entry_id' => $entryId,
                    'odds_value' => $data['current_odds'],
                    'odds_decimal' => $data['odds_decimal']
                ]);
            }

            logActivity('Updated race entry', 'race_entry', $entryId, $old, $data);
            echo json_encode(['success' => true, 'message' => 'Entry updated successfully', 'entry_id' => $entryId]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update entry']);
        }
    } else {
        $newEntryId = $entryModel->create($data);

        if ($newEntryId) {
            // Update runner count in race
            $totalRunners = $entryModel->count('race_id = ? AND is_non_runner = 0', [$data['race_id']]);
            $raceModel->update($data['race_id'], ['total_runners' => $totalRunners]);

            // Record initial odds via model
            if (!empty($data['current_odds'])) {
                $oddsModel = new OddsHistory($db);
                $oddsModel->create([
                    'race_entry_id' => $newEntryId,
                    'odds_value' => $data['current_odds'],
                    'odds_decimal' => $data['odds_decimal']
                ]);
            }

            logActivity('Created race entry', 'race_entry', $newEntryId, null, $data);
            echo json_encode(['success' => true, 'message' => 'Entry created successfully', 'entry_id' => $newEntryId]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create entry']);
        }
    }
} catch (Exception $e) {
    error_log("Save Race Entry Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>