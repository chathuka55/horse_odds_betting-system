<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// CSRF protection
require_once dirname(__DIR__, 2) . '/includes/functions.php';
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$userId = intval($_POST['user_id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);
$action = $_POST['action'] ?? 'add'; // add | subtract | set

if ($userId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid user']);
    exit;
}

try {
    if ($action === 'set') {
        $stmt = $db->prepare("UPDATE users SET credit = ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
    } elseif ($action === 'subtract') {
        $stmt = $db->prepare("UPDATE users SET credit = GREATEST(0, credit - ?) WHERE id = ?");
        $stmt->execute([$amount, $userId]);
    } else {
        $stmt = $db->prepare("UPDATE users SET credit = credit + ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
    }

    // Audit the credit change
    logActivity('update_credit', 'user', $userId, null, ['action' => $action, 'amount' => $amount]);
    logAudit('update_credit', $userId, null, ($action === 'subtract' ? -1 * $amount : $amount), ['action' => $action]);

    echo json_encode(['success' => true, 'message' => 'Credit updated']);
} catch (Exception $e) {
    error_log('Update Credit Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to update credit']);
}
?>