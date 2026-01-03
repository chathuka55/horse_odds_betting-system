<?php
/**
 * User Profile/Settings Page
 * Edit user information, email, phone, password, etc.
 */
require_once 'includes/config.php';

// Debug logging function
function debugLog($message, $data = null) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        $logMessage = date('Y-m-d H:i:s') . " [ACCOUNT.PHP] " . $message;
        if ($data !== null) {
            $logMessage .= " | Data: " . print_r($data, true);
        }
        error_log($logMessage);
    }
}

debugLog("Account page accessed", [
    'user_id' => $_SESSION['user_id'] ?? 'not set',
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'session_id' => session_id()
]);

requireLogin();

$userId = $_SESSION['user_id'] ?? null;
$error = '';
$success = '';
$user = null;
$userCredit = 0;
$bets = [];

// Validate user ID
if (empty($userId) || !is_numeric($userId)) {
    debugLog("Invalid user ID", ['user_id' => $userId]);
    $error = 'Invalid user session. Please login again.';
    setFlashMessage('error', $error);
    redirect(SITE_URL . '/auth/login.php');
    exit;
}

// Get user data with error handling
try {
    debugLog("Fetching user data", ['user_id' => $userId]);
    $user = getUserById($userId);
    
    if (!$user || !is_array($user)) {
        debugLog("User not found in database", ['user_id' => $userId]);
        $error = 'User account not found. Please contact support.';
        // Logout and redirect
        logout();
        setFlashMessage('error', $error);
        redirect(SITE_URL . '/auth/login.php');
        exit;
    }
    
    debugLog("User data retrieved successfully", [
        'user_id' => $user['id'] ?? 'missing',
        'username' => $user['username'] ?? 'missing',
        'email' => $user['email'] ?? 'missing'
    ]);
} catch (Exception $e) {
    debugLog("Error fetching user data", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    error_log("Account Page Error (getUserById): " . $e->getMessage());
    $error = 'Unable to load user data. Please try again later.';
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debugLog("POST request received", [
        'action' => $_POST['action'] ?? 'none',
        'user_id' => $userId
    ]);
    
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        try {
            debugLog("Updating profile", [
                'user_id' => $userId,
                'full_name' => $_POST['full_name'] ?? '',
                'email' => $_POST['email'] ?? ''
            ]);
            
            $result = updateUserProfile($userId, [
                'full_name' => sanitize($_POST['full_name'] ?? ''),
                'email' => sanitize($_POST['email'] ?? ''),
                'phone' => sanitize($_POST['phone'] ?? '')
            ]);
            
            if ($result['success']) {
                $success = 'Profile updated successfully';
                debugLog("Profile updated successfully", ['user_id' => $userId]);
                
                // Refresh user data
                $user = getUserById($userId);
                if ($user && isset($user['full_name'])) {
                    $_SESSION['user_name'] = $user['full_name'];
                }
            } else {
                $error = $result['error'] ?? 'Failed to update profile';
                debugLog("Profile update failed", [
                    'user_id' => $userId,
                    'error' => $error
                ]);
            }
        } catch (Exception $e) {
            debugLog("Exception during profile update", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            error_log("Account Page Error (update_profile): " . $e->getMessage());
            $error = 'An error occurred while updating your profile. Please try again.';
        }
    } elseif ($action === 'change_password') {
        try {
            debugLog("Changing password", ['user_id' => $userId]);
            
            $current = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            if ($newPass !== $confirmPass) {
                $error = 'Passwords do not match';
                debugLog("Password change failed - passwords don't match", ['user_id' => $userId]);
            } else {
                $result = changePassword($userId, $current, $newPass);
                if ($result['success']) {
                    $success = 'Password changed successfully';
                    debugLog("Password changed successfully", ['user_id' => $userId]);
                } else {
                    $error = $result['error'] ?? 'Failed to change password';
                    debugLog("Password change failed", [
                        'user_id' => $userId,
                        'error' => $error
                    ]);
                }
            }
        } catch (Exception $e) {
            debugLog("Exception during password change", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            error_log("Account Page Error (change_password): " . $e->getMessage());
            $error = 'An error occurred while changing your password. Please try again.';
        }
    } else {
        debugLog("Unknown action in POST request", ['action' => $action]);
    }
}

// Get user credit for betting with error handling
try {
    debugLog("Fetching user credit", ['user_id' => $userId]);
    $stmt = $db->prepare("SELECT credit FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare credit query");
    }
    
    $stmt->execute([$userId]);
    $creditResult = $stmt->fetch();
    
    if ($creditResult !== false && is_array($creditResult)) {
        $userCredit = floatval($creditResult['credit'] ?? 0);
        debugLog("User credit retrieved", [
            'user_id' => $userId,
            'credit' => $userCredit
        ]);
    } else {
        debugLog("Credit query returned no result, using default 0", ['user_id' => $userId]);
        $userCredit = 0;
    }
} catch (Exception $e) {
    debugLog("Error fetching user credit", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    error_log("Account Page Error (get credit): " . $e->getMessage());
    $userCredit = 0;
    // Don't show error to user for credit, just use 0
}

// Get recent bets with error handling
try {
    debugLog("Fetching recent bets", ['user_id' => $userId]);
    
    // Check if bets table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'bets'")->fetch();
    if (!$tableCheck) {
        debugLog("Bets table does not exist", ['user_id' => $userId]);
        $bets = [];
    } else {
        $stmt = $db->prepare("
            SELECT b.*, h.name as horse_name, r.name as race_name, r.race_date
            FROM bets b
            LEFT JOIN race_entries re ON b.race_entry_id = re.id
            LEFT JOIN horses h ON re.horse_id = h.id
            LEFT JOIN races r ON b.race_id = r.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
            LIMIT 10
        ");
        
        if (!$stmt) {
            throw new Exception("Failed to prepare bets query: " . print_r($db->errorInfo(), true));
        }
        
        $stmt->execute([$userId]);
        $betsResult = $stmt->fetchAll();
        
        if ($betsResult !== false && is_array($betsResult)) {
            $bets = $betsResult;
            debugLog("Bets retrieved successfully", [
                'user_id' => $userId,
                'bet_count' => count($bets)
            ]);
        } else {
            debugLog("Bets query returned no results", ['user_id' => $userId]);
            $bets = [];
        }
    }
} catch (Exception $e) {
    debugLog("Error fetching bets", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    error_log("Account Page Error (get bets): " . $e->getMessage());
    $bets = [];
    // Don't show error to user for bets, just show empty list
}

// Final validation - ensure user data exists before rendering
if (!$user || !is_array($user)) {
    debugLog("User data missing before render", ['user_id' => $userId]);
    $error = 'Unable to load account information. Please try again or contact support.';
    setFlashMessage('error', $error);
    redirect(SITE_URL . '/auth/login.php');
    exit;
}

// Validate critical user fields
if (empty($user['username']) || empty($user['email'])) {
    debugLog("Critical user fields missing", [
        'user_id' => $userId,
        'has_username' => !empty($user['username']),
        'has_email' => !empty($user['email'])
    ]);
    $error = 'User account data is incomplete. Please contact support.';
    setFlashMessage('error', $error);
    redirect(SITE_URL . '/auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <?php require_once 'components/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">My Account</h1>
                <p class="text-gray-600">Manage your profile and account settings</p>
            </div>

            <!-- Alerts -->
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                    <span class="text-red-700"><?php echo $error; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                    <span class="text-green-700"><?php echo $success; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Sidebar Stats -->
                <div>
                    <!-- Credit Card -->
                    <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-lg p-6 text-white mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Your Credit</h3>
                            <i class="fas fa-wallet text-2xl opacity-50"></i>
                        </div>
                        <div class="text-3xl font-bold"><?php echo formatCurrency($userCredit); ?></div>
                        <p class="text-emerald-100 text-sm mt-1">Available for betting</p>
                        <a href="<?php echo SITE_URL; ?>/races.php" class="inline-block mt-4 bg-white text-emerald-600 px-3 py-1 rounded text-sm font-semibold hover:bg-emerald-50">
                            Place Bets
                        </a>
                    </div>

                    <!-- Account Info Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Account Info</h3>
                        <div class="space-y-3 text-sm">
                            <div>
                                <p class="text-gray-600">Username</p>
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Joined</p>
                                <p class="font-semibold text-gray-800"><?php echo !empty($user['created_at']) ? formatDate($user['created_at']) : 'N/A'; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Last Login</p>
                                <p class="font-semibold text-gray-800"><?php echo !empty($user['last_login']) ? formatDate($user['last_login']) : 'Never'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="md:col-span-2">
                    <!-- Update Profile Form -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Profile</h2>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="update_profile">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" name="full_name" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                                       value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="tel" name="phone"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>

                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-lg transition">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        </form>
                    </div>

                    <!-- Change Password Form -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Change Password</h2>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="change_password">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" name="current_password" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" name="new_password" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                <p class="text-xs text-gray-500 mt-1">Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <input type="password" name="confirm_password" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg transition">
                                <i class="fas fa-key mr-2"></i> Change Password
                            </button>
                        </form>
                    </div>

                    <!-- Recent Bets -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Bets</h2>
                        <?php if (!empty($bets)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Horse</th>
                                        <th class="px-4 py-2 text-left">Race</th>
                                        <th class="px-4 py-2 text-center">Amount</th>
                                        <th class="px-4 py-2 text-center">Odds</th>
                                        <th class="px-4 py-2 text-center">Potential</th>
                                        <th class="px-4 py-2 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bets as $bet): ?>
                                    <tr class="border-t">
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($bet['horse_name'] ?? 'N/A'); ?></td>
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($bet['race_name'] ?? 'N/A'); ?></td>
                                        <td class="px-4 py-2 text-center"><?php echo formatCurrency($bet['amount'] ?? 0); ?></td>
                                        <td class="px-4 py-2 text-center"><?php 
                                            $oddsDisplay = $bet['odds_value'] ?? $bet['odds_decimal'] ?? 'N/A';
                                            echo htmlspecialchars($oddsDisplay);
                                        ?></td>
                                        <td class="px-4 py-2 text-center"><?php echo formatCurrency($bet['potential_payout'] ?? 0); ?></td>
                                        <td class="px-4 py-2 text-center">
                                            <?php 
                                            $statusClass = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'won' => 'bg-green-100 text-green-800',
                                                'lost' => 'bg-red-100 text-red-800',
                                                'refunded' => 'bg-blue-100 text-blue-800'
                                            ];
                                            $betStatus = $bet['status'] ?? 'pending';
                                            $class = $statusClass[$betStatus] ?? 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span class="px-2 py-1 rounded text-xs font-bold <?php echo $class; ?>">
                                                <?php echo ucfirst($betStatus); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No bets yet. <a href="<?php echo SITE_URL; ?>/races.php" class="text-emerald-600 hover:underline">Place a bet now</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once 'components/footer.php'; ?>
    
    <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
    <!-- Debug Information (only visible in debug mode) -->
    <div class="container mx-auto px-4 py-4 max-w-4xl">
        <div class="bg-gray-800 text-white p-4 rounded text-xs font-mono">
            <h3 class="font-bold mb-2">Debug Information</h3>
            <div class="space-y-1">
                <div><strong>User ID:</strong> <?php echo htmlspecialchars($userId ?? 'N/A'); ?></div>
                <div><strong>User Data:</strong> <?php echo $user ? 'Loaded' : 'Missing'; ?></div>
                <div><strong>Credit:</strong> <?php echo $userCredit; ?></div>
                <div><strong>Bets Count:</strong> <?php echo count($bets); ?></div>
                <div><strong>Session ID:</strong> <?php echo session_id(); ?></div>
                <div><strong>Database Connection:</strong> <?php echo $db ? 'Connected' : 'Failed'; ?></div>
                <?php if ($error): ?>
                <div class="text-red-400"><strong>Error:</strong> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
