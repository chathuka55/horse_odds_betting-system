<?php
/**
 * Individual Race Card - Database Version
 * Detailed view of a single race with odds and form
 */
require_once 'includes/config.php';

$raceId = intval($_GET['id'] ?? 0);

if ($raceId <= 0) {
    header("Location: races.php");
    exit;
}

// Get race details
$stmt = $db->prepare("
    SELECT r.*, t.name as track_name, t.country as track_country, t.track_type
    FROM races r
    LEFT JOIN tracks t ON r.track_id = t.id
    WHERE r.id = ?
");
$stmt->execute([$raceId]);
$race = $stmt->fetch();

if (!$race) {
    header("Location: races.php");
    exit;
}

// Get entries/horses
$stmt = $db->prepare("
    SELECT re.*, 
           h.name as horse_name, h.age, h.gender, h.form, h.rating, 
           h.career_wins, h.career_places, h.career_earnings,
           j.name as jockey_name,
           t.name as trainer_name,
           o.name as owner_name
    FROM race_entries re
    LEFT JOIN horses h ON re.horse_id = h.id
    LEFT JOIN jockeys j ON re.jockey_id = j.id
    LEFT JOIN trainers t ON h.trainer_id = t.id
    LEFT JOIN owners o ON h.owner_id = o.id
    WHERE re.race_id = ? AND re.is_non_runner = 0
    ORDER BY re.current_odds ASC, re.saddle_number ASC
");
$stmt->execute([$raceId]);
$entries = $stmt->fetchAll();

// Enrich entries with latest prediction if available
try {
    $predStmt = $db->prepare('SELECT win_prob, confidence FROM predictions WHERE race_id = ? AND race_entry_id = ? ORDER BY created_at DESC LIMIT 1');
    foreach ($entries as $k => $e) {
        $predStmt->execute([$raceId, $e['id']]);
        $p = $predStmt->fetch(PDO::FETCH_ASSOC);
        if ($p) {
            $entries[$k]['win_probability'] = $p['win_prob'];
            $entries[$k]['prediction_confidence'] = $p['confidence'];
        } else {
            // keep existing field or set default
            $entries[$k]['win_probability'] = $entries[$k]['win_probability'] ?? 0;
            $entries[$k]['prediction_confidence'] = $entries[$k]['prediction_confidence'] ?? 0;
        }
    }
} catch (Exception $e) {
    // ignore prediction errors
}

$pageTitle = $race['name'];
require_once 'components/navbar.php';
?>

<!-- Race Header -->
<section class="bg-gradient-to-r from-emerald-800 to-emerald-600 text-white relative">
    <div class="absolute inset-0 bg-black bg-opacity-30"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
            <div class="mb-4 lg:mb-0">
                <div class="flex items-center mb-2">
                    <a href="races.php" class="text-white hover:text-emerald-200 mr-4">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <?php if($race['status'] === 'live'): ?>
                    <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold animate-pulse mr-3">
                        <i class="fas fa-circle text-xs mr-1"></i> LIVE
                    </span>
                    <?php endif; ?>
                    <h1 class="text-3xl md:text-4xl font-bold"><?php echo htmlspecialchars($race['name']); ?></h1>
                </div>
                
                <div class="flex flex-wrap gap-4 text-emerald-100 text-sm md:text-base">
                    <span><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($race['track_name']); ?></span>
                    <span><i class="fas fa-clock mr-2"></i><?php echo formatTime($race['race_time']); ?></span>
                    <span><i class="fas fa-road mr-2"></i><?php echo htmlspecialchars($race['distance']); ?></span>
                    <span><i class="fas fa-layer-group mr-2"></i><?php echo htmlspecialchars($race['race_class']); ?></span>
                    <span><i class="fas fa-trophy mr-2"></i><?php echo formatCurrency($race['prize_money'], $race['currency']); ?></span>
                </div>
            </div>
            
            <div class="flex flex-col items-end">
                <?php
                $raceTime = strtotime($race['race_date'] . ' ' . $race['race_time']);
                $timeLeft = $raceTime - time();
                ?>
                <?php if($timeLeft > 0): ?>
                <div class="text-right">
                    <div class="text-xs uppercase text-emerald-200">Race Starts In</div>
                    <div class="text-3xl font-mono font-bold text-yellow-400" id="countdown" data-time="<?php echo $raceTime; ?>">
                        00:00:00
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Race Info Bar -->
<section class="bg-white border-b py-3 sticky top-16 z-30 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Going:</span>
                <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($race['going'] ?? 'N/A'); ?></span>
            </div>
            <div>
                <span class="text-gray-500">Weather:</span>
                <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($race['weather'] ?? 'N/A'); ?></span>
            </div>
            <div>
                <span class="text-gray-500">Runners:</span>
                <span class="font-semibold text-gray-900"><?php echo count($entries); ?></span>
            </div>
            <div>
                <span class="text-gray-500">Distance:</span>
                <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($race['distance']); ?></span>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Betting Tip -->
        <?php 
        // Find prediction if available
        $fav = $entries[0] ?? null;
        if($fav): 
        ?>
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-lightbulb text-blue-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-bold text-blue-800">AI Prediction</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        Our model favors <strong><?php echo htmlspecialchars($fav['horse_name']); ?></strong> 
                        (<?php echo $fav['win_probability']; ?>% win chance) based on recent form and track conditions.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Race Card Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No.</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Draw</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Horse / Connections</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Age/Wt</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Form</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">OR</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Win %</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Odds</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($entries as $index => $entry): ?>
                        <tr class="hover:bg-blue-50 transition group">
                            <!-- Number -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="w-8 h-8 bg-emerald-700 text-white rounded-sm flex items-center justify-center font-bold shadow-sm">
                                    <?php echo $entry['saddle_number']; ?>
                                </div>
                            </td>
                            
                            <!-- Draw -->
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <span class="text-gray-500 font-mono">(<?php echo $entry['draw_position']; ?>)</span>
                            </td>
                            
                            <!-- Horse Info -->
                            <td class="px-4 py-4">
                                <div class="flex items-start">
                                    <?php if($entry['is_favorite']): ?>
                                        <i class="fas fa-star text-yellow-400 mt-1 mr-2" title="Favorite"></i>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <div class="font-bold text-gray-900 text-lg">
                                            <?php echo htmlspecialchars($entry['horse_name']); ?>
                                            <?php if(!empty($entry['equipment'])): ?>
                                                <span class="text-xs font-normal text-gray-500 ml-1">[<?php echo htmlspecialchars($entry['equipment']); ?>]</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="text-sm text-gray-600 mt-1">
                                            <span class="inline-flex items-center mr-3" title="Jockey">
                                                <i class="fas fa-user-tie text-gray-400 mr-1"></i> 
                                                <?php echo htmlspecialchars($entry['jockey_name'] ?? '-'); ?>
                                            </span>
                                            <span class="inline-flex items-center" title="Trainer">
                                                <i class="fas fa-user-cog text-gray-400 mr-1"></i> 
                                                <?php echo htmlspecialchars($entry['trainer_name'] ?? '-'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Age/Weight -->
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $entry['age']; ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($entry['weight_carried']); ?></div>
                            </td>
                            
                            <!-- Form -->
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="flex justify-center space-x-1">
                                    <?php 
                                    $formStr = $entry['form'] ?? '';
                                    if(empty($formStr)) echo '<span class="text-gray-400">-</span>';
                                    else {
                                        $forms = explode('-', $formStr);
                                        foreach($forms as $pos) {
                                            $color = 'bg-gray-200 text-gray-700';
                                            if($pos == '1') $color = 'bg-yellow-400 text-yellow-900 font-bold';
                                            elseif($pos == '2') $color = 'bg-gray-300 text-gray-800 font-bold';
                                            elseif($pos == '3') $color = 'bg-orange-200 text-orange-800 font-bold';
                                            
                                            echo "<span class='w-6 h-6 flex items-center justify-center rounded-full text-xs {$color}'>{$pos}</span>";
                                        }
                                    }
                                    ?>
                                </div>
                            </td>
                            
                            <!-- Official Rating -->
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-700"><?php echo $entry['rating'] ?? '-'; ?></span>
                            </td>
                            
                            <!-- Win Probability -->
                            <td class="px-4 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-bold text-emerald-600"><?php echo $entry['win_probability']; ?>%</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-1.5 mt-1">
                                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?php echo $entry['win_probability']; ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Odds -->
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="bg-emerald-50 text-emerald-700 py-2 px-3 rounded-lg font-bold text-lg border border-emerald-200">
                                    <?php echo htmlspecialchars($entry['current_odds'] ?? 'SP'); ?>
                                </div>
                            </td>
                            
                            <!-- Action -->
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <button class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-2 px-4 rounded shadow transition transform hover:scale-105">
                                    Bet Now
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Additional Info Tabs -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="border-b border-gray-200 mb-4">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button class="border-emerald-500 text-emerald-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Analysis & Verdict
                    </button>
                    <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Race History
                    </button>
                    <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Weather
                    </button>
                </nav>
            </div>
            
            <!-- Analysis Content -->
            <div class="prose max-w-none text-gray-600">
                <p><?php echo nl2br(htmlspecialchars($race['description'] ?? 'No analysis available for this race yet.')); ?></p>
            </div>
        </div>
    </div>
</section>

<script>
// Countdown Timer
function updateCountdown() {
    const el = document.getElementById('countdown');
    if (!el) return;
    
    const target = parseInt(el.dataset.time);
    const now = Math.floor(Date.now() / 1000);
    const diff = target - now;
    
    if (diff > 0) {
        const hours = Math.floor(diff / 3600);
        const minutes = Math.floor((diff % 3600) / 60);
        const seconds = diff % 60;
        
        el.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    } else {
        el.textContent = "RACE STARTED";
        el.classList.add('text-red-500', 'animate-pulse');
    }
}
setInterval(updateCountdown, 1000);
updateCountdown();
</script>

<?php require_once 'components/footer.php'; ?>