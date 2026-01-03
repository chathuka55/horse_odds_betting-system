<?php
/**
 * AJAX Handler: Save Horse
 * Handles creating and updating horses
 */

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$horseId = intval($_POST['horse_id'] ?? 0);
$isEdit = $horseId > 0;

// Validate required fields
if (empty($_POST['name'])) {
    echo json_encode(['success' => false, 'error' => 'Horse name is required']);
    exit;
}

// Sanitize input
$data = [
    'name' => sanitize($_POST['name']),
    'age' => intval($_POST['age'] ?? 0),
    'gender' => sanitize($_POST['gender'] ?? 'colt'),
    'color' => sanitize($_POST['color'] ?? ''),
    'sire' => sanitize($_POST['sire'] ?? ''),
    'dam' => sanitize($_POST['dam'] ?? ''),
    'country_of_birth' => sanitize($_POST['country_of_birth'] ?? ''),
    'date_of_birth' => !empty($_POST['date_of_birth']) ? sanitize($_POST['date_of_birth']) : null,
    'trainer_id' => !empty($_POST['trainer_id']) ? intval($_POST['trainer_id']) : null,
    'owner_id' => !empty($_POST['owner_id']) ? intval($_POST['owner_id']) : null,
    'weight' => sanitize($_POST['weight'] ?? ''),
    'career_wins' => intval($_POST['career_wins'] ?? 0),
    'career_places' => intval($_POST['career_places'] ?? 0),
    'career_shows' => intval($_POST['career_shows'] ?? 0),
    'career_starts' => intval($_POST['career_starts'] ?? 0),
    'career_earnings' => floatval($_POST['career_earnings'] ?? 0),
    'best_distance' => sanitize($_POST['best_distance'] ?? ''),
    'preferred_going' => sanitize($_POST['preferred_going'] ?? ''),
    'equipment' => sanitize($_POST['equipment'] ?? ''),
    'medication' => sanitize($_POST['medication'] ?? ''),
    'form' => sanitize($_POST['form'] ?? ''),
    'rating' => intval($_POST['rating'] ?? 0),
    'is_active' => isset($_POST['is_active']) ? 1 : 0
];

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadResult = uploadFile($_FILES['image'], 'horses');
    if ($uploadResult['success']) {
        $data['image'] = $uploadResult['path'];
        
        // Delete old image if editing
        if ($isEdit) {
            $horseModel = new Horse($db);
            $oldHorse = $horseModel->getById($horseId);
            if (!empty($oldHorse['image'])) {
                deleteFile($oldHorse['image']);
            }
        }
    }
}

try {
    // Use model for operations
    $horseModel = new Horse($db);

    if ($isEdit) {
        $oldHorse = $horseModel->getById($horseId);
        $updated = $horseModel->update($horseId, $data);

        if ($updated) {
            logActivity('Updated horse', 'horse', $horseId, $oldHorse, $data);
            echo json_encode([
                'success' => true,
                'message' => 'Horse updated successfully',
                'horse_id' => $horseId
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update horse']);
        }
    } else {
        $newHorseId = $horseModel->create($data);
        if ($newHorseId) {
            logActivity('Created horse', 'horse', $newHorseId, null, $data);
            echo json_encode([
                'success' => true,
                'message' => 'Horse created successfully',
                'horse_id' => $newHorseId
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create horse']);
        }
    }
} catch (Exception $e) {
    error_log("Save Horse Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>