<?php
require_once dirname(__DIR__) . '/includes/config.php';
try {
    $c = $db->query('SELECT COUNT(*) as c FROM predictions')->fetch();
    echo 'predictions_count=' . ($c['c'] ?? 0) . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>