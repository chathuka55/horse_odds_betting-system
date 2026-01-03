<?php
// CLI helper to run admin/ajax/generate-predictions.php as admin and bypass CSRF by setting session token
require_once dirname(__DIR__) . '/includes/config.php';
if (session_status()===PHP_SESSION_NONE) session_start();
// make current session admin
$_SESSION['user_role'] = 'admin';
$_SESSION['user_id'] = 1;
// generate csrf token
require_once dirname(__DIR__) . '/includes/functions.php';
$token = generate_csrf_token();
// prepare POST to generate for all upcoming races
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['csrf_token' => $token];
ob_start();
include __DIR__ . '/../admin/ajax/generate-predictions.php';
$resp = ob_get_clean();
echo "Generator response:\n" . $resp . "\n";
// print some sample predictions
try {
    $stmt = $db->prepare('SELECT p.*, re.saddle_number, h.name as horse_name, r.name as race_name, r.race_date FROM predictions p JOIN race_entries re ON p.race_entry_id = re.id JOIN horses h ON re.horse_id = h.id JOIN races r ON p.race_id = r.id ORDER BY p.created_at DESC LIMIT 10');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        echo "\nSample Predictions:\n";
        foreach ($rows as $row) {
            printf("race=%s (%s) | saddle=%s horse=%s | prob=%s%% conf=%s model=%s\n", $row['race_name'], $row['race_date'], $row['saddle_number'], $row['horse_name'], $row['win_prob'], $row['confidence'], $row['model_version']);
        }
    } else {
        echo "No predictions found.\n";
    }
} catch (Exception $e) {
    echo "Error reading predictions: " . $e->getMessage() . "\n";
}
?>