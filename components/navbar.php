<?php
// Navbar now works with database
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/custom.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-emerald-800 to-emerald-600 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?php echo SITE_URL; ?>" class="flex items-center space-x-2">
                        <i class="fas fa-horse text-white text-2xl"></i>
                        <span class="text-white font-bold text-xl hidden sm:block">RacingPro</span>
                    </a>
                    
                    <div class="hidden md:flex items-center space-x-1 ml-10">
                        <a href="<?php echo SITE_URL; ?>" 
                           class="text-white hover:bg-emerald-700 px-3 py-2 rounded-md text-sm font-medium transition <?php echo $currentPage === 'index' ? 'bg-emerald-700' : ''; ?>">
                            <i class="fas fa-home mr-1"></i> Home
                        </a>
                        <a href="<?php echo SITE_URL; ?>/races.php" 
                           class="text-white hover:bg-emerald-700 px-3 py-2 rounded-md text-sm font-medium transition <?php echo $currentPage === 'races' ? 'bg-emerald-700' : ''; ?>">
                            <i class="fas fa-flag-checkered mr-1"></i> Today's Races
                        </a>
                        <a href="<?php echo SITE_URL; ?>/predictions.php" 
                           class="text-white hover:bg-emerald-700 px-3 py-2 rounded-md text-sm font-medium transition <?php echo $currentPage === 'predictions' ? 'bg-emerald-700' : ''; ?>">
                            <i class="fas fa-chart-line mr-1"></i> Predictions
                        </a>
                        <a href="<?php echo SITE_URL; ?>/results.php" 
                           class="text-white hover:bg-emerald-700 px-3 py-2 rounded-md text-sm font-medium transition <?php echo $currentPage === 'results' ? 'bg-emerald-700' : ''; ?>">
                            <i class="fas fa-trophy mr-1"></i> Results
                        </a>
                        <a href="<?php echo SITE_URL; ?>/about.php" 
                           class="text-white hover:bg-emerald-700 px-3 py-2 rounded-md text-sm font-medium transition <?php echo $currentPage === 'about' ? 'bg-emerald-700' : ''; ?>">
                            <i class="fas fa-info-circle mr-1"></i> About
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <div class="hidden md:flex items-center space-x-3">
                            <a href="<?php echo SITE_URL; ?>/account.php" class="text-white text-sm mr-2 hover:underline">
                                <i class="fas fa-user-circle mr-1"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['username']); ?>
                            </a>
                        </div>
                        <?php if (isAdmin() || isEditor()): ?>
                        <a href="<?php echo ADMIN_URL; ?>" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-full transition hidden sm:block">
                            <i class="fas fa-cog mr-1"></i> Admin
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-full transition hidden sm:block">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/auth/login.php" class="bg-white hover:bg-gray-100 text-emerald-600 px-4 py-2 rounded-full transition hidden sm:block">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a href="<?php echo SITE_URL; ?>/auth/register.php" class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-2 px-4 rounded-full transition hidden sm:block">
                            <i class="fas fa-crown mr-1"></i> Sign Up
                        </a>
                    <?php endif; ?>
                    
                    <button id="mobile-menu-button" class="md:hidden text-white hover:text-gray-200">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-emerald-700">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="<?php echo SITE_URL; ?>" class="text-white block px-3 py-2 rounded-md">
                    <i class="fas fa-home mr-2"></i> Home
                </a>
                <a href="<?php echo SITE_URL; ?>/races.php" class="text-white block px-3 py-2 rounded-md">
                    <i class="fas fa-flag-checkered mr-2"></i> Today's Races
                </a>
                <a href="<?php echo SITE_URL; ?>/predictions.php" class="text-white block px-3 py-2 rounded-md">
                    <i class="fas fa-chart-line mr-2"></i> Predictions
                </a>
                <a href="<?php echo SITE_URL; ?>/results.php" class="text-white block px-3 py-2 rounded-md">
                    <i class="fas fa-trophy mr-2"></i> Results
                </a>
                <a href="<?php echo SITE_URL; ?>/about.php" class="text-white block px-3 py-2 rounded-md">
                    <i class="fas fa-info-circle mr-2"></i> About
                </a>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin() || isEditor()): ?>
                    <a href="<?php echo ADMIN_URL; ?>" class="text-white block px-3 py-2 rounded-md">
                        <i class="fas fa-cog mr-2"></i> Admin
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/account.php" class="text-white block px-3 py-2 rounded-md">
                        <i class="fas fa-user-circle mr-2"></i> My Account
                    </a>
                    <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="text-white block px-3 py-2 rounded-md">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-white block px-3 py-2 rounded-md">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </a>
                    <a href="<?php echo SITE_URL; ?>/auth/register.php" class="text-white block px-3 py-2 rounded-md">
                        <i class="fas fa-user-plus mr-2"></i> Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>