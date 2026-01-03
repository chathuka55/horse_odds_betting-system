<?php
/**
 * Admin Dashboard
 */
define('ADMIN_ACCESS', true);
$pageTitle = 'Dashboard';

require_once dirname(__DIR__) . '/includes/config.php';
require_once 'components/header.php';
require_once 'components/sidebar.php';

// Get dashboard statistics
try {
    $totalRaces = $db->query("SELECT COUNT(*) as count FROM races")->fetch()['count'] ?? 0;
    $todaysRaces = $db->query("SELECT COUNT(*) as count FROM races WHERE race_date = CURDATE()")->fetch()['count'] ?? 0;
    $activeHorses = $db->query("SELECT COUNT(*) as count FROM horses WHERE is_active = 1")->fetch()['count'] ?? 0;
    $totalUsers = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'] ?? 0;
    $liveRaces = $db->query("SELECT COUNT(*) as count FROM races WHERE status = 'live'")->fetch()['count'] ?? 0;
    $finishedToday = $db->query("SELECT COUNT(*) as count FROM races WHERE status = 'finished' AND race_date = CURDATE()")->fetch()['count'] ?? 0;
} catch (Exception $e) {
    $totalRaces = $todaysRaces = $activeHorses = $totalUsers = $liveRaces = $finishedToday = 0;
}

$stats = [
    'total_races' => $totalRaces,
    'todays_races' => $todaysRaces,
    'total_horses' => $activeHorses,
    'total_users' => $totalUsers,
    'live_races' => $liveRaces,
    'finished_today' => $finishedToday
];

// Get recent activity
try {
    $recentActivity = $db->query("
        SELECT al.*, u.username 
        FROM activity_log al 
        LEFT JOIN users u ON al.user_id = u.id 
        ORDER BY al.created_at DESC 
        LIMIT 10
    ")->fetchAll();
} catch (Exception $e) {
    $recentActivity = [];
}

// Get upcoming races
try {
    $upcomingRaces = $db->query("
        SELECT r.*, t.name as track_name 
        FROM races r 
        LEFT JOIN tracks t ON r.track_id = t.id 
        WHERE r.race_date >= CURDATE() AND r.status = 'scheduled'
        ORDER BY r.race_date ASC, r.race_time ASC 
        LIMIT 5
    ")->fetchAll();
} catch (Exception $e) {
    $upcomingRaces = [];
}
?>

<!-- Main Content -->
<div class="flex-1 overflow-x-hidden overflow-y-auto">
    <!-- Top Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-sm text-gray-500"><?php echo date('l, F j, Y'); ?></p>
            </div>
            <div class="flex items-center space-x-4">
                <button class="relative p-2 text-gray-600 hover:text-gray-900">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                <a href="<?php echo SITE_URL; ?>" target="_blank" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
                <a href="<?php echo ADMIN_URL; ?>/logout.php" class="text-red-600 hover:text-red-700">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>
    
    <!-- Dashboard Content -->
    <main class="p-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-emerald-500">
                <div class="flex items-center">
                    <div class="bg-emerald-100 p-3 rounded-lg">
                        <i class="fas fa-flag-checkered text-emerald-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Races</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_races']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Today's Races</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['todays_races']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-horse text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Horses</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_horses']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-users text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_users']); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <a href="<?php echo ADMIN_URL; ?>/add-race.php" 
                   class="flex flex-col items-center p-4 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">
                    <i class="fas fa-plus-circle text-emerald-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-emerald-700">Add Race</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/add-horse.php" 
                   class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <i class="fas fa-horse text-blue-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-blue-700">Add Horse</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/add-result.php" 
                   class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                    <i class="fas fa-trophy text-purple-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-purple-700">Add Result</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/races.php?status=live" 
                   class="flex flex-col items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition">
                    <i class="fas fa-broadcast-tower text-red-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-red-700">Live Races</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/api-settings.php" 
                   class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                    <i class="fas fa-sync text-yellow-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-yellow-700">Sync API</span>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/settings.php" 
                   class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-cog text-gray-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">Settings</span>
                </a>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Upcoming Races -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Upcoming Races</h2>
                    <a href="<?php echo ADMIN_URL; ?>/races.php" class="text-emerald-600 hover:text-emerald-700 text-sm">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <?php if (empty($upcomingRaces)): ?>
                <p class="text-gray-500 text-center py-8">No upcoming races</p>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($upcomingRaces as $race): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div>
                            <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($race['name']); ?></h3>
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($race['track_name']); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-emerald-600">
                                <?php echo formatDate($race['race_date']); ?>
                            </p>
                            <p class="text-sm text-gray-500">
                                <?php echo formatTime($race['race_time']); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Recent Activity</h2>
                </div>
                
                <?php if (empty($recentActivity)): ?>
                <p class="text-gray-500 text-center py-8">No recent activity</p>
                <?php else: ?>
                <div class="space-y-4 max-h-80 overflow-y-auto">
                    <?php foreach($recentActivity as $activity): ?>
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-<?php echo getActivityIcon($activity['action']); ?> text-emerald-600 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-800">
                                <span class="font-medium"><?php echo htmlspecialchars($activity['username'] ?? 'System'); ?></span>
                                <?php echo htmlspecialchars($activity['action']); ?>
                            </p>
                            <p class="text-xs text-gray-500"><?php echo timeAgo($activity['created_at']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Races This Week</h2>
                <canvas id="racesChart" height="200"></canvas>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Prediction Accuracy</h2>
                <canvas id="accuracyChart" height="200"></canvas>
            </div>
        </div>
    </main>
</div>

<?php
function getActivityIcon($action) {
    $icons = [
        'login' => 'sign-in-alt',
        'logout' => 'sign-out-alt',
        'create' => 'plus',
        'update' => 'edit',
        'delete' => 'trash',
        'default' => 'circle'
    ];
    
    foreach ($icons as $key => $icon) {
        if (stripos($action, $key) !== false) {
            return $icon;
        }
    }
    return $icons['default'];
}
?>

<script>
// Races Chart
const racesCtx = document.getElementById('racesChart').getContext('2d');
new Chart(racesCtx, {
    type: 'bar',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Races',
            data: [5, 8, 6, 12, 9, 15, 10],
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Accuracy Chart
const accuracyCtx = document.getElementById('accuracyChart').getContext('2d');
new Chart(accuracyCtx, {
    type: 'doughnut',
    data: {
        labels: ['Correct', 'Incorrect'],
        datasets: [{
            data: [89.9, 10.1],
            backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(239, 68, 68, 0.8)'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

<?php require_once 'components/footer.php'; ?>