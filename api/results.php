<?php
/**
 * Public API: Get Race Results
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once dirname(__DIR__) . '/includes/config.php';

$raceId = intval($_GET['race_id'] ?? 0);
$limit = min(100, intval($_GET['limit'] ?? 20));

try {
    if ($raceId > 0) {
        // Get specific race results
        $stmt = $db->prepare("
            SELECT rr.*, h.name as horse_name, j.name as jockey_name,
                   re.saddle_number, re.current_odds
            FROM race_results rr
            JOIN race_entries re ON rr.race_entry_id = re.id
            JOIN horses h ON re.horse_id = h.id
            LEFT JOIN jockeys j ON re.jockey_id = j.id
            WHERE rr.race_id = ?
            ORDER BY rr.finish_position ASC
        ");
        $stmt->execute([$raceId]);
        $results = $stmt->fetchAll() ?? [];
        
        echo json_encode([
            'success' => true,
            'race_id' => $raceId,
            'count' => count($results),
            'data' => $results
        ]);
    } else {
        // Get recent results
        $stmt = $db->prepare("
            SELECT r.id, r.name, r.race_date, t.name as track_name,
                   (SELECT h.name FROM race_results rr 
                    JOIN race_entries re ON rr.race_entry_id = re.id 
                    JOIN horses h ON re.horse_id = h.id 
                    WHERE rr.race_id = r.id AND rr.finish_position = 1 LIMIT 1) as winner
            FROM races r
            LEFT JOIN tracks t ON r.track_id = t.id
            WHERE r.status = 'finished'
            AND EXISTS (SELECT 1 FROM race_results WHERE race_id = r.id)
            ORDER BY r.race_date DESC, r.race_time DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll() ?? [];
        
        echo json_encode([
            'success' => true,
            'count' => count($results),
            'data' => $results
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>