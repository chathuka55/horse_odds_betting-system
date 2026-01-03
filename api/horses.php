<?php
/**
 * Public API: Get Horses
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once dirname(__DIR__) . '/includes/config.php';

$search = $_GET['search'] ?? '';
$limit = min(100, intval($_GET['limit'] ?? 50));
$raceId = intval($_GET['race_id'] ?? 0);

try {
    if ($raceId > 0) {
        // Get horses in a specific race
        $stmt = $db->prepare("
            SELECT re.*, h.name as horse_name, h.age, h.gender, h.form, h.rating,
                   j.name as jockey_name, t.name as trainer_name
            FROM race_entries re
            JOIN horses h ON re.horse_id = h.id
            LEFT JOIN jockeys j ON re.jockey_id = j.id
            LEFT JOIN trainers t ON h.trainer_id = t.id
            WHERE re.race_id = ?
            ORDER BY re.saddle_number
        ");
        $stmt->execute([$raceId]);
    } else {
        // Get all horses
        $where = 'h.is_active = 1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND h.name LIKE ?';
            $params[] = "%{$search}%";
        }
        
        $stmt = $db->prepare("
            SELECT h.*, t.name as trainer_name, o.name as owner_name
            FROM horses h
            LEFT JOIN trainers t ON h.trainer_id = t.id
            LEFT JOIN owners o ON h.owner_id = o.id
            WHERE {$where}
            ORDER BY h.name ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', intval($limit), PDO::PARAM_INT);
        $stmt->execute($params);
    }
    
    $horses = $stmt->fetchAll() ?? [];
    
    echo json_encode([
        'success' => true,
        'count' => count($horses),
        'data' => $horses
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>