<?php
/**
 * Admin Login Page
 */
require_once dirname(__DIR__) . '/includes/config.php';

// Redirect if already logged in
if (isLoggedIn() && isAdmin()) {
    redirect(ADMIN_URL);
}

$error = '';

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $result = attemptLogin($username, $password);
        
        if ($result['success']) {
            if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'editor') {
                redirect(ADMIN_URL);
            } else {
                logout();
                $error = 'Access denied. Admin privileges required.';
            }
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-gray-900 via-emerald-900 to-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-600 rounded-full mb-4">
                <i class="fas fa-horse text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white"><?php echo SITE_NAME; ?></h1>
            <p class="text-emerald-200">Admin Panel</p>
        </div>
        
        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Sign In</h2>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <span class="text-red-700"><?php echo $error; ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Username or Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-user text-gray-400"></i>
                        </span>
                        <input type="text" name="username" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                               placeholder="Enter your username"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-lock text-gray-400"></i>
                        </span>
                        <input type="password" name="password" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                               placeholder="Enter your password">
                        <button type="button" onclick="togglePassword(this)" 
                                class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i class="fas fa-eye text-gray-400"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-emerald-600 rounded">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="text-sm text-emerald-600 hover:text-emerald-700">
                        Forgot password?
                    </a>
                </div>
                
                <button type="submit" 
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-lg transition transform hover:scale-105 shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </form>
        </div>
        
        <!-- Back to site -->
        <div class="text-center mt-6">
            <a href="<?php echo SITE_URL; ?>" class="text-emerald-200 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Website
            </a>
        </div>
        
        <!-- Demo credentials -->
        <div class="mt-6 bg-gray-800/50 rounded-lg p-4 text-center">
            <p class="text-emerald-200 text-sm">Demo Credentials:</p>
            <p class="text-white text-sm">Username: <strong>admin</strong> | Password: <strong>admin123</strong></p>
        </div>
    </div>
    
    <script>
        function togglePassword(btn) {
            const input = btn.previousElementSibling;
            const icon = btn.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>