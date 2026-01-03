<?php
/**
 * Authentication Functions
 */

/**
 * Attempt to login user
 */
function attemptLogin($username, $password) {
    global $db;
    
    // Check for lockout
    if (isLockedOut($username)) {
        return ['success' => false, 'error' => 'Account temporarily locked. Try again later.'];
    }
    
    try {
        $stmt = $db->prepare("
            SELECT id, username, email, password, full_name, role, is_active 
            FROM users 
            WHERE (username = ? OR email = ?) AND is_active = 1
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Clear login attempts
            clearLoginAttempts($username);
            
            // Update last login
            $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // Set session
            // Regenerate session id to prevent fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['logged_in'] = true;
            // Track last activity for session timeout
            $_SESSION['last_activity'] = time();
            
            // Log activity
            logActivity('login', 'user', $user['id']);
            
            return ['success' => true, 'user' => $user];
        } else {
            // Record failed attempt
            recordLoginAttempt($username);
            return ['success' => false, 'error' => 'Invalid username or password'];
        }
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Login failed. Please try again.'];
    }
}

/**
 * Register new user
 */
function registerUser($data) {
    global $db;
    
    // Validate
    if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        return ['success' => false, 'error' => 'All fields are required'];
    }
    
    if (!isValidEmail($data['email'])) {
        return ['success' => false, 'error' => 'Invalid email address'];
    }
    
    // Validate password strength
    $pwErrors = validatePassword($data['password']);
    if (!empty($pwErrors)) {
        return ['success' => false, 'error' => implode('; ', $pwErrors)];
    }

    // Check if email exists (duplicate email not allowed)
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'This email is already registered. Please login instead.'];
    }

    // Check if username exists separately
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Username already exists'];
    }
    
    try {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, full_name, role)
            VALUES (?, ?, ?, ?, 'user')
        ");
        
        $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['full_name'] ?? $data['username']
        ]);
        
        $userId = $db->lastInsertId();
        
        logActivity('register', 'user', $userId);
        
        return ['success' => true, 'user_id' => $userId];
    } catch (Exception $e) {
        error_log("Registration Error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Registration failed. Please try again.'];
    }
}

/**
 * Logout user
 */
function logout() {
    logActivity('logout', 'user', $_SESSION['user_id'] ?? null);
    
    $_SESSION = [];
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Check if account is locked out
 */
function isLockedOut($username) {
    $key = 'login_attempts_' . md5($username);
    
    if (isset($_SESSION[$key])) {
        $attempts = $_SESSION[$key];
        
        if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
            if (time() - $attempts['last_attempt'] < LOCKOUT_TIME) {
                return true;
            } else {
                // Reset after lockout period
                unset($_SESSION[$key]);
            }
        }
    }
    
    return false;
}

/**
 * Record failed login attempt
 */
function recordLoginAttempt($username) {
    $key = 'login_attempts_' . md5($username);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'last_attempt' => time()];
    }
    
    $_SESSION[$key]['count']++;
    $_SESSION[$key]['last_attempt'] = time();
}

/**
 * Clear login attempts
 */
function clearLoginAttempts($username) {
    $key = 'login_attempts_' . md5($username);
    unset($_SESSION[$key]);
}

/**
 * Change password
 */
function changePassword($userId, $currentPassword, $newPassword) {
    global $db;
    
    $pwErrors = validatePassword($newPassword);
    if (!empty($pwErrors)) {
        return ['success' => false, 'error' => implode('; ', $pwErrors)];
    }
    
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return ['success' => false, 'error' => 'Current password is incorrect'];
    }
    
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
    
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $userId]);
    
    logActivity('password_change', 'user', $userId);
    
    return ['success' => true];
}

/**
 * Get user by ID
 */
function getUserById($userId) {
    global $db;
    
    $stmt = $db->prepare("SELECT id, username, email, full_name, role, avatar, phone, is_active, last_login, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    return $stmt->fetch();
}

/**
 * Update user profile
 */
function updateUserProfile($userId, $data) {
    global $db;
    
    $allowedFields = ['full_name', 'email', 'phone', 'avatar'];
    $updates = [];
    $params = [];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "{$field} = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        return ['success' => false, 'error' => 'No data to update'];
    }
    
    // If email is being changed, ensure it's not already used by another user
    if (isset($data['email'])) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$data['email'], $userId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Email already in use by another account'];
        }
    }

    $params[] = $userId;
    
    try {
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        logActivity('profile_update', 'user', $userId);
        
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Update failed'];
    }
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to continue');
        redirect(SITE_URL . '/auth/login.php');
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        setFlashMessage('error', 'Access denied');
        redirect(SITE_URL . '/auth/login.php');
    }
}
?>