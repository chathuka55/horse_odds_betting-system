<?php
/**
 * Forgot Password Page
 */
require_once dirname(__DIR__) . '/includes/config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email) || !isValidEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        $stmt = $db->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // In production, send email with reset link
            // For demo, just show success message
            $message = 'Password reset instructions have been sent to your email address.';
        } else {
            $message = 'If an account exists with this email, password reset instructions have been sent.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-emerald-50 to-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="<?php echo SITE_URL; ?>" class="inline-flex items-center justify-center w-16 h-16 bg-emerald-600 rounded-full mb-4">
                <i class="fas fa-horse text-white text-3xl"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Forgot Password?</h1>
            <p class="text-gray-600">Enter your email to reset your password</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <span class="text-red-700"><?php echo $error; ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                <span class="text-green-700"><?php echo $message; ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Email Address</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                           placeholder="your@email.com">
                </div>
                
                <button type="submit" 
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-lg transition">
                    <i class="fas fa-paper-plane mr-2"></i> Send Reset Link
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="login.php" class="text-emerald-600 hover:text-emerald-700">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>