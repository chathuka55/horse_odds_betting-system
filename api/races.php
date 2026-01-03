<?php
/**
 * Public API: Get Races
 * Returns race data in JSON format
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once dirname(__DIR__) . '/includes/config.php';

$date = $_GET['date'] ?? date('Y-m-d');
$trackId = intval($_GET['track_id'] ?? 0);
$status = $_GET['status'] ?? '';
$limit = min(100, intval($_GET['limit'] ?? 50));

$where = ['r.race_date >= ?'];
$params = [$date];

if ($trackId > 0) {
    $where[] = 'r.track_id = ?';
    $params[] = $trackId;
}

if (!empty($status)) {
    $where[] = 'r.status = ?';
    $params[] = $status;
}

$whereString = implode(' AND ', $where);

try {
    $stmt = $db->prepare("
        SELECT r.*, t.name as track_name, t.country as track_country,
               (SELECT COUNT(*) FROM race_entries WHERE race_id = r.id AND is_non_runner = 0) as runner_count
        FROM races r
        LEFT JOIN tracks t ON r.track_id = t.id
        WHERE {$whereString}
        ORDER BY r.race_date ASC, r.race_time ASC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', intval($limit), PDO::PARAM_INT);
    $stmt->execute($params);
    $races = $stmt->fetchAll() ?? [];
    
    echo json_encode([
        'success' => true,
        'count' => count($races),
        'data' => $races
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>