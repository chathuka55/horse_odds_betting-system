<?php
/**
 * AI Predictions Page
 * Shows AI-generated predictions for upcoming races
 */
require_once 'includes/config.php';
$pageTitle = "AI Predictions";
require_once 'components/navbar.php';

// Get upcoming races with stored predictions (top 3 per race)
try {
    $stmt = $db->prepare(" 
        SELECT r.id, r.name, r.race_date, r.race_time, t.name as track_name,
               (SELECT COUNT(*) FROM race_entries WHERE race_id = r.id AND is_non_runner = 0) as runner_count,
               (SELECT COUNT(*) FROM predictions p WHERE p.race_id = r.id) as has_predictions
        FROM races r
        LEFT JOIN tracks t ON r.track_id = t.id
        WHERE r.race_date >= CURDATE() AND r.status IN ('scheduled', 'live')
        ORDER BY r.race_date ASC, r.race_time ASC
        LIMIT 10
    ");
    $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $races = [];
}
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-purple-800 to-purple-600 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-3">
            <i class="fas fa-brain mr-2"></i> AI-Powered Predictions
        </h1>
        <p class="text-xl text-purple-100">
            Advanced machine learning analysis for accurate race predictions
        </p>
    </div>
</section>

<!-- How It Works -->
<section class="py-12 bg-white border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">How Our AI Works</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="bg-purple-100 text-purple-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-database text-2xl"></i>
                </div>
                <h3 class="font-bold text-lg mb-2">1. Data Collection</h3>
                <p class="text-gray-600">We analyze millions of historical race records and real-time data</p>
            </div>
            
            <div class="text-center">
                <div class="bg-purple-100 text-purple-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cogs text-2xl"></i>
                </div>
                <h3 class="font-bold text-lg mb-2">2. Feature Engineering</h3>
                <p class="text-gray-600">We extract relevant patterns and performance indicators</p>
            </div>
            
            <div class="text-center">
                <div class="bg-purple-100 text-purple-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-bar text-2xl"></i>
                </div>
                <h3 class="font-bold text-lg mb-2">3. Model Training</h3>
                <p class="text-gray-600">Machine learning models learn from comprehensive training data</p>
            </div>
            
            <div class="text-center">
                <div class="bg-purple-100 text-purple-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-star text-2xl"></i>
                </div>
                <h3 class="font-bold text-lg mb-2">4. Predictions</h3>
                <p class="text-gray-600">Accurate win probability and confidence ratings for each runner</p>
            </div>
        </div>
    </div>
</section>

<!-- Prediction Factors -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Factors We Analyze</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $factors = [
                ['icon' => 'fa-horse', 'title' => 'Horse Form', 'desc' => 'Recent performance and winning streaks'],
                ['icon' => 'fa-user-tie', 'title' => 'Jockey Stats', 'desc' => 'Win percentage and track record'],
                ['icon' => 'fa-briefcase', 'title' => 'Trainer Record', 'desc' => 'Historical success and specializations'],
                ['icon' => 'fa-route', 'title' => 'Course Suitability', 'desc' => 'Performance on track type and distance'],
                ['icon' => 'fa-cloud', 'title' => 'Track Conditions', 'desc' => 'Weather, going, and ground conditions'],
                ['icon' => 'fa-history', 'title' => 'Historical Data', 'desc' => '10+ years of race records analyzed'],
            ];
            foreach ($factors as $factor): ?>
            <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                <div class="text-3xl text-purple-600 mb-3">
                    <i class="fas <?php echo $factor['icon']; ?>"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo $factor['title']; ?></h3>
                <p class="text-gray-600"><?php echo $factor['desc']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Upcoming Races with Predictions -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Today's Top Predicted Races</h2>
        
        <?php if (empty($races)): ?>
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <p class="text-gray-600">No races available for predictions at this time</p>
        </div>
        <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($races as $race): ?>
            <div class="bg-gradient-to-r from-gray-50 to-purple-50 rounded-lg shadow-md p-6 border-l-4 border-purple-600 hover:shadow-lg transition">
                <div class="flex flex-col md:flex-row justify-between md:items-center mb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($race['name']); ?></h3>
                        <div class="flex flex-wrap gap-3 text-sm text-gray-600 mt-2">
                            <span><i class="fas fa-map-marker-alt text-purple-600 mr-1"></i><?php echo htmlspecialchars($race['track_name']); ?></span>
                            <span><i class="fas fa-calendar-alt text-purple-600 mr-1"></i><?php echo formatDate($race['race_date']); ?></span>
                            <span class="font-bold text-purple-600"><i class="fas fa-clock mr-1"></i><?php echo formatTime($race['race_time']); ?></span>
                        </div>
                    </div>
                    
                    <div class="mt-4 md:mt-0 text-center">
                        <div class="bg-purple-600 text-white px-4 py-2 rounded-lg">
                            <div class="text-sm text-purple-100">Runners</div>
                            <div class="text-2xl font-bold"><?php echo $race['runner_count']; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3 items-center">
                    <a href="racecard.php?id=<?php echo $race['id']; ?>" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-3 px-4 rounded-lg transition text-center font-semibold">
                        <i class="fas fa-chart-bar mr-2"></i> View Full Predictions
                    </a>
                    <?php if (!empty($race['has_predictions'])): ?>
                        <div class="bg-white border rounded p-3">
                            <strong>Top Picks:</strong>
                            <?php
                                $pstmt = $db->prepare('SELECT p.*, re.saddle_number, h.name as horse_name FROM predictions p LEFT JOIN race_entries re ON p.race_entry_id = re.id LEFT JOIN horses h ON re.horse_id = h.id WHERE p.race_id = ? ORDER BY p.win_prob DESC LIMIT 3');
                                $pstmt->execute([$race['id']]);
                                $top = $pstmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($top as $t):
                            ?>
                                <div style="font-size:0.95rem;">#<?php echo $t['saddle_number']; ?> <?php echo htmlspecialchars($t['horse_name']); ?> — <?php echo $t['win_prob']; ?>% (<?php echo $t['confidence']; ?>)</div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="/admin/ajax/generate-predictions.php" style="margin:0">
                            <input type="hidden" name="race_id" value="<?php echo $race['id']; ?>" />
                            <?php echo get_csrf_token_input(); ?>
                            <button class="bg-white border-2 border-purple-600 text-purple-600 hover:bg-purple-50 py-2 px-3 rounded-lg transition font-semibold">Generate Predictions</button>
                        </form>
                    <?php endif; ?>
                    <button class="bg-white border-2 border-purple-600 text-purple-600 hover:bg-purple-50 py-3 px-4 rounded-lg transition font-semibold">
                        <i class="fas fa-bell mr-2"></i> Set Alert
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Accuracy Stats -->
<section class="py-12 bg-gradient-to-r from-purple-600 to-purple-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold mb-8 text-center">Our Track Record</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-5xl font-bold mb-3">95%</div>
                <div class="text-purple-100">Average Accuracy</div>
                <p class="text-sm text-purple-200 mt-2">Win prediction accuracy rate</p>
            </div>
            
            <div>
                <div class="text-5xl font-bold mb-3">87%</div>
                <div class="text-purple-100">ROI Positive Bets</div>
                <p class="text-sm text-purple-200 mt-2">Users report positive returns</p>
            </div>
            
            <div>
                <div class="text-5xl font-bold mb-3">2.3x</div>
                <div class="text-purple-100">Average Odds Multiplier</div>
                <p class="text-sm text-purple-200 mt-2">Performance above baseline</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-12 bg-white">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Ready to Get Started?</h2>
        <p class="text-lg text-gray-600 mb-8">
            Join thousands of users making smarter betting decisions with RacingPro's AI predictions.
        </p>
        <?php if (!isLoggedIn()): ?>
        <a href="<?php echo SITE_URL; ?>/auth/register.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-lg inline-block transition">
            Create Free Account
        </a>
        <?php else: ?>
        <a href="<?php echo SITE_URL; ?>/races.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-lg inline-block transition">
            View Races
        </a>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'components/footer.php'; ?>
