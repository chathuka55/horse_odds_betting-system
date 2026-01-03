<?php
require_once 'includes/config.php';
require_once 'components/navbar.php';

// Get today's featured races from database
$races = $db->query("
    SELECT r.*, t.name as track_name, t.country as track_country,
           (SELECT COUNT(*) FROM race_entries WHERE race_id = r.id AND is_non_runner = 0) as horses
    FROM races r
    LEFT JOIN tracks t ON r.track_id = t.id
    WHERE r.race_date >= CURDATE() AND (r.is_featured = 1 OR r.status = 'live')
    ORDER BY r.race_date ASC, r.race_time ASC
    LIMIT 6
")->fetchAll();

// If no races, use today's races
if (empty($races)) {
    $races = $db->query("
        SELECT r.*, t.name as track_name, t.country as track_country,
               (SELECT COUNT(*) FROM race_entries WHERE race_id = r.id AND is_non_runner = 0) as horses
        FROM races r
        LEFT JOIN tracks t ON r.track_id = t.id
        WHERE r.race_date = CURDATE()
        ORDER BY r.race_time ASC
        LIMIT 6
    ")->fetchAll();
}

// Get site statistics
try {
    $statsRaces = $db->prepare("SELECT COUNT(*) as count FROM races")->execute();
    $raceCount = $db->query("SELECT COUNT(*) as count FROM races")->fetch()['count'] ?? 0;
    
    $horseCount = $db->query("SELECT COUNT(*) as count FROM horses WHERE is_active = 1")->fetch()['count'] ?? 0;
    
    $userCount = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1")->fetch()['count'] ?? 0;
} catch (Exception $e) {
    $raceCount = 0;
    $horseCount = 0;
    $userCount = 0;
}

$stats = [
    'total_races' => $raceCount,
    'total_horses' => $horseCount,
    'total_users' => $userCount
];
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-900 text-white">
    <div class="absolute inset-0 overflow-hidden">
        <img src="https://images.unsplash.com/photo-1516673699707-4f2a243fafaf?w=1920" alt="Horse Racing" class="w-full h-full object-cover opacity-20">
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center animate-fade-in">
            <h1 class="text-5xl md:text-6xl font-bold mb-6">AI-Powered Racing Predictions</h1>
            <p class="text-xl md:text-2xl mb-8 text-emerald-100">Advanced analytics and real-time odds for smarter betting decisions</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo SITE_URL; ?>/auth/register.php" class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-3 px-8 rounded-full text-lg transition transform hover:scale-105 shadow-lg">
                    <i class="fas fa-rocket mr-2"></i> Get Started Free
                </a>
                <a href="<?php echo SITE_URL; ?>/races.php" class="bg-transparent border-2 border-white hover:bg-white hover:text-emerald-900 font-bold py-3 px-8 rounded-full text-lg transition">
                    <i class="fas fa-play-circle mr-2"></i> View Races
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="transform hover:scale-105 transition">
                <div class="text-4xl font-bold text-emerald-600 mb-2">95%</div>
                <div class="text-gray-600">Accuracy Rate</div>
            </div>
            <div class="transform hover:scale-105 transition">
                <div class="text-4xl font-bold text-emerald-600 mb-2"><?php echo number_format($stats['total_users']); ?>+</div>
                <div class="text-gray-600">Active Users</div>
            </div>
            <div class="transform hover:scale-105 transition">
                <div class="text-4xl font-bold text-emerald-600 mb-2">24/7</div>
                <div class="text-gray-600">Live Updates</div>
            </div>
            <div class="transform hover:scale-105 transition">
                <div class="text-4xl font-bold text-emerald-600 mb-2"><?php echo number_format($stats['total_races']); ?>+</div>
                <div class="text-gray-600">Total Races</div>
            </div>
        </div>
    </div>
</section>

<!-- Today's Featured Races -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                <i class="fas fa-flag-checkered text-emerald-600 mr-2"></i>
                Today's Featured Races
            </h2>
            <p class="text-xl text-gray-600">Live odds and AI predictions updated in real-time</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($races as $race): ?>
            <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg"><?php echo htmlspecialchars($race['name']); ?></h3>
                            <p class="text-emerald-100 text-sm"><?php echo htmlspecialchars($race['track_name']); ?></p>
                        </div>
                        <span class="bg-yellow-500 text-gray-900 px-3 py-1 rounded-full text-sm font-bold">
                            <?php echo formatTime($race['race_time']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                        <div>
                            <span class="text-gray-500">Distance:</span>
                            <p class="font-semibold"><?php echo htmlspecialchars($race['distance'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <span class="text-gray-500">Prize:</span>
                            <p class="font-semibold text-emerald-600"><?php echo formatCurrency($race['prize_money'], $race['currency']); ?></p>
                        </div>
                        <div>
                            <span class="text-gray-500">Class:</span>
                            <p class="font-semibold"><?php echo htmlspecialchars($race['race_class'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <span class="text-gray-500">Runners:</span>
                            <p class="font-semibold"><?php echo $race['horses']; ?> horses</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <a href="racecard.php?id=<?php echo $race['id']; ?>" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-center py-2 px-4 rounded-lg transition">
                            <i class="fas fa-chart-bar mr-1"></i> View Odds
                        </a>
                        <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg transition">
                            <i class="fas fa-bell"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($races)): ?>
        <div class="text-center py-12">
            <i class="fas fa-flag-checkered text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">No races available today</h3>
            <p class="text-gray-500">Check back later for upcoming races</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose RacingPro?</h2>
            <p class="text-xl text-gray-600">Advanced features for serious racing enthusiasts</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="text-center p-6 hover:bg-gray-50 rounded-lg transition">
                <div class="bg-emerald-100 text-emerald-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-brain text-2xl"></i>
                </div>
                <h3 class="font-bold text-xl mb-2">AI Predictions</h3>
                <p class="text-gray-600">Machine learning algorithms analyze thousands of data points</p>
            </div>
            
            <div class="text-center p-6 hover:bg-gray-50 rounded-lg transition">
                <div class="bg-emerald-100 text-emerald-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-sync text-2xl"></i>
                </div>
                <h3 class="font-bold text-xl mb-2">Live Updates</h3>
                <p class="text-gray-600">Real-time odds and results updated every second</p>
            </div>
            
            <div class="text-center p-6 hover:bg-gray-50 rounded-lg transition">
                <div class="bg-emerald-100 text-emerald-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <h3 class="font-bold text-xl mb-2">Form Analysis</h3>
                <p class="text-gray-600">Comprehensive form guides and historical tracking</p>
            </div>
            
            <div class="text-center p-6 hover:bg-gray-50 rounded-lg transition">
                <div class="bg-emerald-100 text-emerald-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-mobile-alt text-2xl"></i>
                </div>
                <h3 class="font-bold text-xl mb-2">Mobile Ready</h3>
                <p class="text-gray-600">Access predictions on the go</p>
            </div>
            
            <div class="text-center p-6 hover:bg-gray-50 rounded-lg transition">
                <div class="bg-emerald-100 text-emerald-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-2xl"></i>
                </div>
                <h3 class="font-bold text-xl mb-2">Secure Platform</h3>
                <p class="text-gray-600">Bank-level encryption and security</p>
            </div>
            
            <div class="text-center p-6 hover:bg-gray-50 rounded-lg transition">
                <div class="bg-emerald-100 text-emerald-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <h3 class="font-bold text-xl mb-2">Expert Community</h3>
                <p class="text-gray-600">Join thousands of racing enthusiasts</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Start Winning?</h2>
        <p class="text-xl mb-8 text-emerald-100">Join <?php echo number_format($stats['total_users']); ?>+ users already using our predictions</p>
        <a href="<?php echo SITE_URL; ?>/auth/register.php" class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-4 px-10 rounded-full text-lg transition transform hover:scale-105 shadow-lg inline-block">
            Start Free Trial - No Credit Card Required
        </a>
    </div>
</section>

<?php require_once 'components/footer.php'; ?>