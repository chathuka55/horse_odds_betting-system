<?php
require_once dirname(__DIR__) . '/includes/config.php';
$stmt = $db->query('SELECT id, name, race_date, status FROM races ORDER BY race_date DESC LIMIT 20');
$r = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($r as $row) {
    echo sprintf("id=%d name=%s date=%s status=%s\n", $row['id'], $row['name'], $row['race_date'], $row['status']);
}
?>