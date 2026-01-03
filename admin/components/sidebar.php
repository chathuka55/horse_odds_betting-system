<?php
/**
 * Admin Sidebar Component
 */
$currentPage = $currentPage ?? basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Sidebar -->
<aside class="hidden md:flex md:flex-shrink-0">
    <div class="flex flex-col w-64 bg-gray-900">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 bg-gray-800">
            <a href="<?php echo ADMIN_URL; ?>" class="flex items-center space-x-2">
                <i class="fas fa-horse text-emerald-500 text-2xl"></i>
                <span class="text-white font-bold text-xl">RacingPro</span>
            </a>
        </div>
        
        <!-- Navigation -->
        <div class="flex flex-col flex-1 overflow-y-auto">
            <nav class="flex-1 px-2 py-4 space-y-1">
                <!-- Dashboard -->
                <a href="<?php echo ADMIN_URL; ?>/index.php" 
                   class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'index' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>
                
                <!-- Races Management -->
                <div class="pt-4">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Race Management</p>
                </div>
                
                <a href="<?php echo ADMIN_URL; ?>/races.php" 
                   class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'races' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    <i class="fas fa-flag-checkered w-5 mr-3"></i>
                    <span>Races</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/horses.php" 
                   class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'horses' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    <i class="fas fa-horse w-5 mr-3"></i>
                    <span>Horses</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/results.php" 
                   class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'results' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    <i class="fas fa-trophy w-5 mr-3"></i>
                    <span>Results</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/tracks.php" 
                   class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'tracks' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    <i class="fas fa-road w-5 mr-3"></i>
                    <span>Tracks</span>
                </a>
                
                <!-- People Management -->
                <div class="pt-4">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">People</p>
                </div>
                
                <a href="<?php echo ADMIN_URL; ?>/jockeys.php" 
                   class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'jockeys' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    <i class="fas fa-user-tie w-5 mr-3"></i>
                    <span>Jockeys</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/trainers.php" 
                   class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'trainers' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    <i class="fas fa-chalkboard-teacher w-5 mr-3"></i>
                    <span>Trainers</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/owners.php" 
                   class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'owners' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    <i class="fas fa-user-crown w-5 mr-3"></i>
                    <span>Owners</span>
                </a>
                
                <!-- System -->
                <div class="pt-4">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">System</p>
                </div>
                
                <a href="<?php echo ADMIN_URL; ?>/users.php" 
                   class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'users' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    <i class="fas fa-users w-5 mr-3"></i>
                    <span>Users</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/api-settings.php" 
                   class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'api-settings' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    <i class="fas fa-plug w-5 mr-3"></i>
                    <span>API Settings</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/settings.php" 
                   class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'settings' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    <i class="fas fa-cog w-5 mr-3"></i>
                    <span>Settings</span>
                </a>
            </nav>
            
            <!-- User Info -->
            <div class="flex-shrink-0 p-4 border-t border-gray-800">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold">
                        <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></p>
                        <p class="text-xs text-gray-400"><?php echo ucfirst($_SESSION['user_role'] ?? 'admin'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Toggle -->
<div class="md:hidden fixed bottom-4 left-4 z-50">
    <button id="mobile-menu-toggle" class="bg-emerald-600 text-white p-3 rounded-full shadow-lg">
        <i class="fas fa-bars"></i>
    </button>
</div>