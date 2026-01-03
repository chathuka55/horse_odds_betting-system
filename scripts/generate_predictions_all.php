<?php
// CLI script: generate predictions for all races (including past) for demonstration
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

try {
    $rstmt = $db->prepare('SELECT id FROM races');
    $rstmt->execute();
    $races = $rstmt->fetchAll(PDO::FETCH_ASSOC);

    $upsert = $db->prepare("INSERT INTO predictions (race_id, race_entry_id, win_prob, confidence, model_version, created_at) VALUES (?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE win_prob = VALUES(win_prob), confidence = VALUES(confidence), model_version = VALUES(model_version), created_at = NOW()");

    $count = 0;
    foreach ($races as $r) {
        $entriesStmt = $db->prepare('SELECT re.* FROM race_entries re WHERE re.race_id = ? AND re.is_non_runner = 0');
        $entriesStmt->execute([$r['id']]);
        $entries = $entriesStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($entries as $entry) {
            $pred = getPredictionForEntry($entry);
            $upsert->execute([$r['id'], $entry['id'], $pred['win_prob'], $pred['confidence'], 'v1']);
            $count++;
        }
    }
    echo "Generated predictions for {$count} entries\n";
    // show some samples
    $s = $db->prepare('SELECT p.*, re.saddle_number, h.name as horse_name, r.name as race_name FROM predictions p JOIN race_entries re ON p.race_entry_id = re.id JOIN horses h ON re.horse_id = h.id JOIN races r ON p.race_id = r.id ORDER BY p.created_at DESC LIMIT 20');
    $s->execute();
    $rows = $s->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        printf("race=%s | saddle=%s | horse=%s | prob=%s%% conf=%s\n", $row['race_name'], $row['saddle_number'], $row['horse_name'], $row['win_prob'], $row['confidence']);
    }
} catch (Exception $e) {
    echo 'Error generating predictions: ' . $e->getMessage() . PHP_EOL;
}
?>