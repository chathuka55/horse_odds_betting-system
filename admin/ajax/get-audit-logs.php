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
$userId = intval($_POST['user_id'] ?? 0);
if ($userId <= 0) { echo json_encode(['success'=>false,'error'=>'Invalid user']); exit; }
try {
    $stmt = $db->prepare('SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 200');
    $stmt->execute([$userId]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'logs'=>$logs]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
?>