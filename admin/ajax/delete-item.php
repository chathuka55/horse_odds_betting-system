<?php
/**
 * AJAX Handler: Delete Item
 * Handles deletion of races, horses, tracks, etc.
 */

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? $_POST['type'] ?? '';
$id = intval($input['id'] ?? $_POST['id'] ?? 0);

if (empty($type) || $id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Allowed types and their table mappings
$allowedTypes = [
    'race' => 'races',
    'horse' => 'horses',
    'track' => 'tracks',
    'jockey' => 'jockeys',
    'trainer' => 'trainers',
    'owner' => 'owners',
    'user' => 'users',
    'race_entry' => 'race_entries',
    'result' => 'race_results'
];

if (!isset($allowedTypes[$type])) {
    echo json_encode(['success' => false, 'error' => 'Invalid type']);
    exit;
}

$table = $allowedTypes[$type];

try {
    // Fetch current item for logging via model when possible
    $modelMap = [
        'race' => 'Race',
        'horse' => 'Horse',
        'track' => 'Track',
        'jockey' => 'Jockey',
        'trainer' => 'Trainer',
        'owner' => 'Owner',
        'user' => 'User',
        'race_entry' => 'RaceEntry',
        'result' => 'RaceResult'
    ];

    $oldItem = null;
    $modelForType = $modelMap[$type] ?? null;
    if ($modelForType && class_exists($modelForType)) {
        $m = new $modelForType($db);
        $oldItem = $m->getById($id);
    } else {
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        $oldItem = $stmt->fetch();
    }

    if (!$oldItem) {
        echo json_encode(['success' => false, 'error' => 'Item not found']);
        exit;
    }

    // Soft-delete for common entities
    $softDeleteTypes = ['horse','user','track','jockey','trainer','owner'];

    if (in_array($type, $softDeleteTypes)) {
        // Use model update to set is_active = 0 when possible
        if ($modelForType && class_exists($modelForType)) {
            $m = new $modelForType($db);
            $m->update($id, ['is_active' => 0]);
        } else {
            // Fallback to direct update
            $db->prepare("UPDATE {$table} SET is_active = 0 WHERE id = ?")->execute([$id]);
        }

        logActivity("Deleted {$type}", $type, $id, $oldItem, null);
        echo json_encode(['success' => true, 'message' => ucfirst($type) . ' deleted (soft)']);
        exit;
    }

    // Cascading delete for races (remove related entries/results/predictions/payouts)
    if ($type === 'race') {
        $db->beginTransaction();
        try {
            // Delete odds history for entries in this race
            $entryModel = new RaceEntry($db);
            $entries = $entryModel->getAll('race_id = ' . intval($id));
            $oddsModel = new OddsHistory($db);
            foreach ($entries as $e) {
                $oddsModel->delete($e['id']);
            }

            // Delete race results
            $raceResultModel = new RaceResult($db);
            $results = $raceResultModel->getAll('race_id = ' . intval($id));
            foreach ($results as $r) { $raceResultModel->delete($r['id']); }

            // Delete predictions
            $predictionModel = new Prediction($db);
            $preds = $predictionModel->getAll('race_id = ' . intval($id));
            foreach ($preds as $p) { $predictionModel->delete($p['id']); }

            // Delete payouts if model exists
            if (class_exists('Payout')) {
                $payoutModel = new Payout($db);
                $payouts = $payoutModel->getAll('race_id = ' . intval($id));
                foreach ($payouts as $po) { $payoutModel->delete($po['id']); }
            } else {
                $db->prepare("DELETE FROM payouts WHERE race_id = ?")->execute([$id]);
            }

            // Delete entries
            foreach ($entries as $e) { $entryModel->delete($e['id']); }

            // Finally delete the race
            if ($modelForType && class_exists($modelForType)) {
                $m = new $modelForType($db);
                $m->delete($id);
            } else {
                $db->prepare("DELETE FROM {$table} WHERE id = ?")->execute([$id]);
            }

            $db->commit();
            logActivity("Deleted race", 'race', $id, $oldItem, null);
            echo json_encode(['success' => true, 'message' => 'Race deleted successfully']);
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Race delete error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to delete race']);
            exit;
        }
    }

    // For other types, perform hard delete
    // For other types, perform hard delete via model when possible
    if ($modelForType && class_exists($modelForType)) {
        $m = new $modelForType($db);
        $deleted = $m->delete($id);
    } else {
        $stmt = $db->prepare("DELETE FROM {$table} WHERE id = ?");
        $deleted = $stmt->execute([$id]);
    }

    if ($deleted) {
        logActivity("Deleted {$type}", $type, $id, $oldItem, null);
        echo json_encode(['success' => true, 'message' => ucfirst($type) . ' deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete item']);
    }
    
} catch (Exception $e) {
    error_log("Delete Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>