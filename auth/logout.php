<?php
/**
 * User Logout
 */
require_once dirname(__DIR__) . '/includes/config.php';

logout();
setFlashMessage('success', 'You have been logged out successfully');
redirect(SITE_URL);
?>