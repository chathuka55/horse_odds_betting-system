<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

if (!isAdmin()) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'Invalid method']); exit;
}
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success'=>false,'error'=>'Invalid CSRF token']); exit;
}

$raceId = intval($_POST['race_id'] ?? 0);
try {
    // If race_id provided, generate for that race; otherwise for upcoming races
    if ($raceId > 0) {
        $rStmt = $db->prepare('SELECT id FROM races WHERE id = ?');
        $rStmt->execute([$raceId]);
        $races = $rStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $rStmt = $db->prepare("SELECT id FROM races WHERE race_date >= CURDATE() AND status IN ('scheduled','live') LIMIT 50");
        $rStmt->execute();
        $races = $rStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $upsert = $db->prepare("INSERT INTO predictions (race_id, race_entry_id, win_prob, confidence, model_version, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE win_prob = VALUES(win_prob), confidence = VALUES(confidence), model_version = VALUES(model_version), created_at = NOW()");

    foreach ($races as $r) {
        $entriesStmt = $db->prepare('SELECT re.* FROM race_entries re WHERE re.race_id = ? AND re.is_non_runner = 0');
        $entriesStmt->execute([$r['id']]);
        $entries = $entriesStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($entries as $entry) {
            $pred = getPredictionForEntry($entry);
            $upsert->execute([$r['id'], $entry['id'], $pred['win_prob'], $pred['confidence'], 'v1']);
        }
    }

    logActivity('generate_predictions', null, $raceId ?: null, null, null);
    echo json_encode(['success'=>true,'message'=>'Predictions generated']);
} catch (Exception $e) {
    error_log('Generate predictions error: ' . $e->getMessage());
    echo json_encode(['success'=>false,'error'=>'Server error']);
}
