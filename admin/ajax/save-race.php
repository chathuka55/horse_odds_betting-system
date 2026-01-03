<?php
/**
 * AJAX Handler: Save Race
 * Handles creating and updating races
 */

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get POST data
$raceId = intval($_POST['race_id'] ?? 0);
$isEdit = $raceId > 0;

// Validate required fields
$requiredFields = ['name', 'track_id', 'race_date', 'race_time'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'error' => "Field '{$field}' is required"]);
        exit;
    }
}

// Sanitize input
$data = [
    'name' => sanitize($_POST['name']),
    'track_id' => intval($_POST['track_id']),
    'race_date' => sanitize($_POST['race_date']),
    'race_time' => sanitize($_POST['race_time']),
    'distance' => sanitize($_POST['distance'] ?? ''),
    'race_class' => sanitize($_POST['race_class'] ?? ''),
    'race_type' => sanitize($_POST['race_type'] ?? 'flat'),
    'prize_money' => floatval($_POST['prize_money'] ?? 0),
    'currency' => sanitize($_POST['currency'] ?? 'USD'),
    'going' => sanitize($_POST['going'] ?? ''),
    'weather' => sanitize($_POST['weather'] ?? ''),
    'temperature' => sanitize($_POST['temperature'] ?? ''),
    'wind' => sanitize($_POST['wind'] ?? ''),
    'humidity' => sanitize($_POST['humidity'] ?? ''),
    'rail_position' => sanitize($_POST['rail_position'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'scheduled'),
    'description' => sanitize($_POST['description'] ?? ''),
    'is_featured' => isset($_POST['is_featured']) ? 1 : 0
];

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadResult = uploadFile($_FILES['image'], 'races');
    if ($uploadResult['success']) {
        $data['image'] = $uploadResult['path'];

        // Delete old image if editing
        if ($isEdit) {
            $raceModel = new Race($db);
            $oldRace = $raceModel->getById($raceId);
            if (!empty($oldRace['image'])) {
                deleteFile($oldRace['image']);
            }
        }
    }
}

try {
    $raceModel = new Race($db);

    if ($isEdit) {
        $updated = $raceModel->update($raceId, $data);
        if ($updated) {
            logActivity('Updated race', 'race', $raceId, null, $data);
            echo json_encode(['success' => true, 'message' => 'Race updated successfully', 'race_id' => $raceId]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update race']);
        }
    } else {
        $newRaceId = $raceModel->create($data);
        if ($newRaceId) {
            logActivity('Created race', 'race', $newRaceId, null, $data);
            echo json_encode(['success' => true, 'message' => 'Race created successfully', 'race_id' => $newRaceId]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create race']);
        }
    }
} catch (Exception $e) {
    error_log("Save Race Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>