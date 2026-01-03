<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to place a bet']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// CSRF protection
require_once __DIR__ . '/../includes/functions.php';
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$userId = $_SESSION['user_id'];
$entryId = intval($_POST['race_entry_id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);
$betType = sanitize($_POST['bet_type'] ?? 'win');

if ($entryId <= 0 || $amount <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

try {
    // Ensure bets table exists
    $db->exec("CREATE TABLE IF NOT EXISTS bets (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        race_id INT NOT NULL,
        race_entry_id INT NOT NULL,
        bet_type VARCHAR(50) DEFAULT 'win',
        amount DECIMAL(12,2) NOT NULL,
        odds_value VARCHAR(50) DEFAULT NULL,
        odds_decimal DECIMAL(10,2) DEFAULT NULL,
        potential_payout DECIMAL(12,2) DEFAULT NULL,
        payout_amount DECIMAL(12,2) DEFAULT NULL,
        status ENUM('pending','won','lost','refunded') DEFAULT 'pending',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        settled_at DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Fetch race entry
    $stmt = $db->prepare("SELECT re.*, r.prize_money FROM race_entries re LEFT JOIN races r ON re.race_id = r.id WHERE re.id = ?");
    $stmt->execute([$entryId]);
    $entry = $stmt->fetch();
    if (!$entry) throw new Exception('Race entry not found');

    $raceId = $entry['race_id'];

    // Determine odds decimal
    $oddsDecimal = floatval($entry['odds_decimal'] ?? 0);
    if ($oddsDecimal <= 0) {
        $oddsDecimal = fractionalToDecimal($entry['current_odds'] ?? '');
    }
    if ($oddsDecimal <= 0) $oddsDecimal = 1.0;

    // Check user credit
    $stmt = $db->prepare("SELECT credit FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    $credit = floatval($user['credit'] ?? 0);
    if ($credit < $amount) {
        echo json_encode(['success' => false, 'error' => 'Insufficient credit']);
        exit;
    }

    // Place bet within transaction
    $db->beginTransaction();

    // Deduct credit
    $stmt = $db->prepare("UPDATE users SET credit = credit - ? WHERE id = ?");
    $stmt->execute([$amount, $userId]);

    $potential = round($amount * $oddsDecimal, 2);

    $stmt = $db->prepare("INSERT INTO bets (user_id, race_id, race_entry_id, bet_type, amount, odds_value, odds_decimal, potential_payout, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([
        $userId,
        $raceId,
        $entryId,
        $betType,
        $amount,
        $entry['current_odds'] ?? null,
        $oddsDecimal,
        $potential
    ]);

    $betId = $db->lastInsertId();

    $db->commit();

    // Audit the financial action (deduct and bet placed)
    logActivity('place_bet', 'bet', $betId, null, ['user_id' => $userId, 'entry' => $entryId, 'amount' => $amount]);
    logAudit('place_bet', $userId, $betId, -1 * $amount, ['entry' => $entryId]);

    echo json_encode(['success' => true, 'message' => 'Bet placed', 'bet_id' => $betId, 'potential_payout' => $potential]);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Place Bet Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to place bet: ' . $e->getMessage()]);
}
