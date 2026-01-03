<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
if (!isAdmin()) {
    echo json_encode(['success'=>false]); exit;
}
$token = generate_csrf_token();
echo json_encode(['success'=>true,'token'=>$token]);
?>