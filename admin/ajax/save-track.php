<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$trackId = intval($_POST['track_id'] ?? 0);
$isEdit = $trackId > 0;

if (empty($_POST['name'])) {
    echo json_encode(['success' => false, 'error' => 'Name required']);
    exit;
}

$data = [
    'name' => sanitize($_POST['name']),
    'location' => sanitize($_POST['location'] ?? ''),
    'country' => sanitize($_POST['country'] ?? ''),
    'track_type' => sanitize($_POST['track_type'] ?? 'turf'),
    'track_length' => sanitize($_POST['track_length'] ?? ''),
    'is_active' => isset($_POST['is_active']) ? 1 : 0
];

try {
    $trackModel = new Track($db);

    if ($isEdit) {
        $updated = $trackModel->update($trackId, $data);
        if ($updated) {
            logActivity('Updated track', 'track', $trackId, null, $data);
            echo json_encode(['success' => true, 'message' => 'Track updated']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update track']);
        }
    } else {
        $newId = $trackModel->create($data);
        if ($newId) {
            logActivity('Created track', 'track', $newId, null, $data);
            echo json_encode(['success' => true, 'message' => 'Track created', 'track_id' => $newId]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create track']);
        }
    }
} catch (Exception $e) {
    error_log('Save Track Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

?>