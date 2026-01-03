<?php
/**
 * Admin Logout
 */
require_once dirname(__DIR__) . '/includes/config.php';

logout();
redirect(ADMIN_URL . '/login.php');
?>