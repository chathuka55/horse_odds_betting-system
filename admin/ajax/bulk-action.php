<?php
/**
 * AJAX Handler: Bulk Actions
 * Handles bulk operations on multiple items
 */

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$type = $input['type'] ?? '';
$ids = $input['ids'] ?? [];

if (empty($action) || empty($type) || empty($ids) || !is_array($ids)) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Sanitize IDs
$ids = array_map('intval', $ids);
$ids = array_filter($ids, function($id) { return $id > 0; });

if (empty($ids)) {
    echo json_encode(['success' => false, 'error' => 'No valid IDs provided']);
    exit;
}

$allowedTypes = [
    'race' => 'races',
    'horse' => 'horses',
    'track' => 'tracks',
    'jockey' => 'jockeys',
    'trainer' => 'trainers',
    'user' => 'users'
];

if (!isset($allowedTypes[$type])) {
    echo json_encode(['success' => false, 'error' => 'Invalid type']);
    exit;
}

$table = $allowedTypes[$type];
$placeholders = implode(',', array_fill(0, count($ids), '?'));

try {
    // Map type => model class
    $typeModelMap = [
        'race' => 'Race',
        'horse' => 'Horse',
        'track' => 'Track',
        'jockey' => 'Jockey',
        'trainer' => 'Trainer',
        'user' => 'User'
    ];

    $modelClass = $typeModelMap[$type] ?? null;
    if (!$modelClass) {
        echo json_encode(['success' => false, 'error' => 'Unsupported type']);
        exit;
    }

    $model = new $modelClass($db);

    switch ($action) {
        case 'delete':
            $affected = 0;
            foreach ($ids as $id) {
                if ($model->delete($id)) $affected++;
            }
            logActivity("Bulk deleted {$affected} {$type}s", $type, null, ['ids' => $ids], null);
            echo json_encode(['success' => true, 'message' => "{$affected} items deleted"]);
            break;

        case 'activate':
            $affected = 0;
            foreach ($ids as $id) {
                if ($model->update($id, ['is_active' => 1])) $affected++;
            }
            logActivity("Bulk activated {$affected} {$type}s", $type, null, ['ids' => $ids], null);
            echo json_encode(['success' => true, 'message' => "{$affected} items activated"]);
            break;

        case 'deactivate':
            $affected = 0;
            foreach ($ids as $id) {
                if ($model->update($id, ['is_active' => 0])) $affected++;
            }
            logActivity("Bulk deactivated {$affected} {$type}s", $type, null, ['ids' => $ids], null);
            echo json_encode(['success' => true, 'message' => "{$affected} items deactivated"]);
            break;

        case 'feature':
            if ($type !== 'race') { echo json_encode(['success' => false, 'error' => 'Feature action only available for races']); exit; }
            $raceModel = new Race($db);
            $affected = 0;
            foreach ($ids as $id) { if ($raceModel->update($id, ['is_featured' => 1])) $affected++; }
            echo json_encode(['success' => true, 'message' => "{$affected} races featured"]);
            break;

        case 'unfeature':
            if ($type !== 'race') { echo json_encode(['success' => false, 'error' => 'Unfeature action only available for races']); exit; }
            $raceModel = new Race($db);
            $affected = 0;
            foreach ($ids as $id) { if ($raceModel->update($id, ['is_featured' => 0])) $affected++; }
            echo json_encode(['success' => true, 'message' => "{$affected} races unfeatured"]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
} catch (Exception $e) {
    error_log("Bulk Action Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>