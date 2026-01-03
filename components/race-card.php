<?php
require_once 'includes/config.php';
require_once 'components/navbar.php';

$horses = getMockHorses();
$raceId = isset($_GET['id']) ? $_GET['id'] : 1;
$races = getMockRaces();
$race = $races[$raceId - 1] ?? $races[0];
?>

<!-- Race Header -->
<section class="bg-gradient-to-r from-emerald-800 to-emerald-600 text-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2"><?php echo $race['name']; ?></h1>
                <p class="text-emerald-100">
                    <i class="fas fa-map-marker-alt mr-2"></i><?php echo $race['track']; ?> • 
                    <i class="fas fa-clock ml-2 mr-2"></i><?php echo $race['time']; ?> • 
                    <i class="fas fa-road ml-2 mr-2"></i><?php echo $race['distance']; ?>
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="bg-yellow-500 text-gray-900 px-6 py-3 rounded-lg font-bold text-lg">
                    Prize: <?php echo $race['prize']; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Live Timer -->
<section class="bg-gray-900 text-white py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-center space-x-4">
            <span class="text-yellow-500 animate-pulse">
                <i class="fas fa-circle text-xs"></i> LIVE
            </span>
            <span>Race starts in:</span>
            <div id="countdown" class="font-mono text-2xl font-bold text-yellow-500">
                00:45:23
            </div>
        </div>
    </div>
</section>

<!-- Odds Grid -->
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Betting Tips Alert -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-lightbulb text-yellow-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>AI Tip:</strong> Based on current conditions and form, horses #2 and #5 show the best value for money.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Odds Table -->
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horse</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jockey</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trainer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Form</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Odds</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Win %</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($horses as $index => $horse): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-center w-8 h-8 bg-emerald-600 text-white rounded-full font-bold">
                                    <?php echo $horse['number']; ?>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="font-semibold text-gray-900"><?php echo $horse['name']; ?></div>
                                <?php if($index < 3): ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 mt-1">
                                    <i class="fas fa-star mr-1"></i> Favorite
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo $horse['jockey']; ?>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo $horse['trainer']; ?>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex space-x-1">
                                    <?php 
                                    $formArray = explode('-', $horse['form']);
                                    foreach($formArray as $position): 
                                        $bgColor = $position == '1' ? 'bg-green-500' : ($position <= '3' ? 'bg-yellow-500' : 'bg-gray-400');
                                    ?>
                                    <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white <?php echo $bgColor; ?> rounded">
                                        <?php echo $position; ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <span class="text-lg font-bold text-emerald-600"><?php echo $horse['odds']; ?></span>
                                <div class="text-xs text-gray-500">
                                    <?php 
                                    $trend = rand(0, 2);
                                    if($trend == 0) echo '<i class="fas fa-arrow-down text-red-500"></i>';
                                    elseif($trend == 1) echo '<i class="fas fa-arrow-up text-green-500"></i>';
                                    else echo '<i class="fas fa-minus text-gray-400"></i>';
                                    ?>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <div class="relative">
                                    <div class="text-2xl font-bold text-gray-900"><?php echo $horse['win_chance']; ?>%</div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-emerald-600 h-2 rounded-full" style="width: <?php echo $horse['win_chance']; ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <button class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition transform hover:scale-105">
                                    Bet Now
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">Track Condition</div>
                <div class="text-lg font-bold text-emerald-600">Good to Firm</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">Weather</div>
                <div class="text-lg font-bold text-emerald-600">Sunny, 22°C</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">Total Pool</div>
                <div class="text-lg font-bold text-emerald-600">$2.4M</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">Confidence Level</div>
                <div class="text-lg font-bold text-emerald-600">87% High</div>
            </div>
        </div>
    </div>
</section>

<!-- Prediction Chart -->
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-xl p-6">
            <h2 class="text-2xl font-bold mb-4">
                <i class="fas fa-chart-bar text-emerald-600 mr-2"></i>
                AI Prediction Analysis
            </h2>
            <canvas id="predictionChart" width="400" height="100"></canvas>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Countdown Timer
function updateCountdown() {
    const countdown = document.getElementById('countdown');
    let time = countdown.innerHTML.split(':').map(Number);
    let hours = time[0], minutes = time[1], seconds = time[2];
    
    if(seconds > 0) seconds--;
    else if(minutes > 0) { minutes--; seconds = 59; }
    else if(hours > 0) { hours--; minutes = 59; seconds = 59; }
    
    countdown.innerHTML = 
        String(hours).padStart(2, '0') + ':' + 
        String(minutes).padStart(2, '0') + ':' + 
        String(seconds).padStart(2, '0');
}
setInterval(updateCountdown, 1000);

// Chart
const ctx = document.getElementById('predictionChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Lightning Strike', 'Thunder Bolt', 'Desert Rose', 'Ocean Wave', 'Storm Chaser', 'Golden Arrow', 'Wind Runner', 'Mountain King'],
        datasets: [{
            label: 'Win Probability %',
            data: [32.1, 28.5, 22.4, 18.9, 15.3, 12.5, 8.7, 6.2],
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 40
            }
        }
    }
});
</script>

<?php require_once 'components/footer.php'; ?>