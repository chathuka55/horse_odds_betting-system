<?php


// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Debug mode (set to true for development)
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}


// DATABASE CONFIGURATION

define('DB_HOST', 'localhost');
define('DB_NAME', 'horse_racing_db');
define('DB_USER', 'root');
define('DB_PASS', ''); //  Password is empty (default)


// SITE CONFIGURATION

define('SITE_NAME', 'RacingPro Analytics');
define('SITE_TAGLINE', 'AI-Powered Racing Predictions');
define('SITE_URL', 'http://localhost/horse-racing-platform');
define('ADMIN_URL', SITE_URL . '/admin');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/assets/uploads');


// PATH CONFIGURATION

define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('COMPONENTS_PATH', ROOT_PATH . 'components/');
define('ADMIN_PATH', ROOT_PATH . 'admin/');
define('DATA_PATH', ROOT_PATH . 'data/');
define('UPLOADS_PATH', ROOT_PATH . 'assets/uploads/');


// API CONFIGURATION

define('RACING_API_KEY', ''); // Add API Key For get Data and configuer
define('RACING_API_URL', 'https://api.theracingapi.com/v1');


// SECURITY CONFIGURATION

define('HASH_COST', 12);
define('SESSION_LIFETIME', 86400); // 24 hours
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes


// PAGINATION

define('ITEMS_PER_PAGE', 20);
define('ADMIN_ITEMS_PER_PAGE', 25);


// FILE UPLOAD LIMITS

define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);


// INCLUDE REQUIRED FILES

require_once INCLUDES_PATH . 'database.php';
require_once INCLUDES_PATH . 'functions.php';
require_once INCLUDES_PATH . 'auth.php';
require_once INCLUDES_PATH . 'models.php';
require_once INCLUDES_PATH . 'api-helpers.php';


// INITIALIZE DATABASE CONNECTION

$database = new Database();
$db = $database->getConnection();


// LOAD SITE SETTINGS FROM DATABASE

function loadSiteSettings($db) {
    $settings = [];
    try {
        $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // Use defaults if database not available
    }
    return $settings;
}

$siteSettings = loadSiteSettings($db);


// HELPER FUNCTIONS



 // Get site setting
 
function getSetting($key, $default = '') {
    global $siteSettings;
    return $siteSettings[$key] ?? $default;
}


 // Check if user is logged in
 
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}


 // Check if user is admin
 
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}


 // Check if user is editor or admin
 
function isEditor() {
    return isLoggedIn() && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'editor']);
}


 // Redirect function

function redirect($url) {
    header("Location: $url");
    exit;
}


 // Flash message functions
 
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}


 // CSRF Token functions
 
function generateCSRFToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}


 // Format currency
 
function formatCurrency($amount, $currency = 'USD') {
    $symbols = ['USD' => '$', 'GBP' => '£', 'EUR' => '€', 'AUD' => 'A$'];
    $symbol = $symbols[$currency] ?? '$';
    return $symbol . number_format($amount, 2);
}


  //Format date
 
function formatDate($date, $format = null) {
    $format = $format ?? getSetting('date_format', 'Y-m-d');
    return date($format, strtotime($date));
}


 // Format time
 
function formatTime($time, $format = null) {
    $format = $format ?? getSetting('time_format', 'H:i');
    return date($format, strtotime($time));
}
?>