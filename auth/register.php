<?php
/**
 * User Registration Page
 */
require_once dirname(__DIR__) . '/includes/config.php';

if (isLoggedIn()) {
    redirect(SITE_URL);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => sanitize($_POST['username'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'full_name' => sanitize($_POST['full_name'] ?? '')
    ];
    
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($data['password'] !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $result = registerUser($data);
        
        if ($result['success']) {
            $success = 'Registration successful! You can now login.';
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
    <title>Register - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-emerald-50 to-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="<?php echo SITE_URL; ?>" class="inline-flex items-center justify-center w-16 h-16 bg-emerald-600 rounded-full mb-4">
                <i class="fas fa-horse text-white text-3xl"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-800"><?php echo SITE_NAME; ?></h1>
            <p class="text-gray-600">Create your account</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <span class="text-red-700"><?php echo $error; ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-green-700"><?php echo $success; ?></span>
                </div>
                <a href="login.php" class="text-green-700 underline mt-2 inline-block">Click here to login</a>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Full Name</label>
                    <input type="text" name="full_name" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Username</label>
                    <input type="text" name="username" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>
                
                <button type="submit" 
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-lg transition transform hover:scale-105">
                    <i class="fas fa-user-plus mr-2"></i> Create Account
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">Already have an account? 
                    <a href="login.php" class="text-emerald-600 hover:text-emerald-700 font-semibold">Sign In</a>
                </p>
            </div>
        </div>
        
        <div class="text-center mt-6">
            <a href="<?php echo SITE_URL; ?>" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Website
            </a>
        </div>
    </div>
</body>
</html>