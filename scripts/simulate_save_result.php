<?php
// Simulate admin saving results for a race to trigger bet settlement
require_once dirname(__DIR__) . '/includes/config.php';

// Make sure session exists and is admin
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_role'] = 'admin';
$_SESSION['user_id'] = 1; // assume admin user id 1 exists

// Choose a race_id and mark the first entry as winner
$raceId = intval($argv[1] ?? 0);
if ($raceId <= 0) {
    // pick any race
    $r = $db->query("SELECT id FROM races LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $raceId = $r['id'] ?? 0;
}

if ($raceId <= 0) {
    echo "No race available to simulate\n";
    exit(1);
}

// Get first non-runner entry
$entry = $db->prepare("SELECT id FROM race_entries WHERE race_id = ? LIMIT 1");
$entry->execute([$raceId]);
$e = $entry->fetch(PDO::FETCH_ASSOC);
if (!$e) {
    echo "No race entry found for race {$raceId}\n";
    exit(1);
}
$entryId = $e['id'];

// Prepare POST environment and include the AJAX handler
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [];
$_POST['race_id'] = $raceId;
$_POST['results'] = [ $entryId => 1 ];
$_POST['times'] = [ $entryId => '1:12.34' ];
$_POST['margins'] = [ $entryId => 'Nose' ];
// Add CSRF token for admin AJAX handlers
require_once dirname(__DIR__) . '/includes/functions.php';
$_POST['csrf_token'] = generate_csrf_token();

// Include the handler directly to run settlement logic
ob_start();
include __DIR__ . '/../admin/ajax/save-result.php';
$resp = ob_get_clean();

echo "save-result response:\n" . $resp . "\n";

// Show the affected bet(s) for this race
$stmt = $db->prepare("SELECT * FROM bets WHERE race_id = ? ORDER BY id DESC LIMIT 10");
$stmt->execute([$raceId]);
$bets = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($bets) {
    echo "Recent bets for race {$raceId}:\n";
    foreach ($bets as $bet) {
        echo sprintf("bet id=%d user=%d entry=%d amount=%s status=%s payout=%s\n", $bet['id'], $bet['user_id'], $bet['race_entry_id'], $bet['amount'], $bet['status'], $bet['payout_amount']);
    }
} else {
    echo "No bets found for race {$raceId}\n";
}

// Show user credit after settlement
$stmt = $db->prepare("SELECT id, email, credit FROM users ORDER BY id DESC LIMIT 5");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Recent users and credit:\n";
foreach ($users as $u) {
    echo sprintf("id=%d email=%s credit=%s\n", $u['id'], $u['email'], $u['credit']);
}

?>