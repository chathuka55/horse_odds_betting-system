<?php
/**
 * Today's Races Page - Database Version
 * Shows all races scheduled for today with filtering options
 */
require_once 'includes/config.php';

$pageTitle = "Today's Races";
require_once 'components/navbar.php';

// Get filter parameters
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedTrack = intval($_GET['track_id'] ?? 0);
$selectedStatus = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Build WHERE clause
$where = ['r.race_date = ?'];
$params = [$selectedDate];

if ($selectedTrack > 0) {
    $where[] = 'r.track_id = ?';
    $params[] = $selectedTrack;
}

if (!empty($selectedStatus)) {
    $where[] = 'r.status = ?';
    $params[] = $selectedStatus;
}

if (!empty($searchQuery)) {
    $where[] = '(r.name LIKE ? OR t.name LIKE ?)';
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

$whereString = implode(' AND ', $where);

// Get races from database
$stmt = $db->prepare("
    SELECT r.*, 
           t.name as track_name, 
           t.location as track_location,
           t.country as track_country,
           (SELECT COUNT(*) FROM race_entries WHERE race_id = r.id AND is_non_runner = 0) as runner_count,
           (SELECT COUNT(*) FROM race_results WHERE race_id = r.id) as has_results
    FROM races r
    LEFT JOIN tracks t ON r.track_id = t.id
    WHERE {$whereString}
    ORDER BY r.race_time ASC
");
$stmt->execute($params);
$races = $stmt->fetchAll();

// Get all tracks for filter dropdown
$tracks = $db->query("SELECT id, name FROM tracks WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

// Count races by status
$statusCounts = $db->query("
    SELECT status, COUNT(*) as count 
    FROM races 
    WHERE race_date = '{$selectedDate}'
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-emerald-800 to-emerald-600 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-4xl font-bold mb-2">
                    <i class="fas fa-flag-checkered mr-2"></i>
                    Today's Racing Schedule
                </h1>
                <p class="text-xl text-emerald-100">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <?php echo date('l, F j, Y', strtotime($selectedDate)); ?> • 
                    <span id="live-time"></span>
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="bg-white/20 backdrop-blur rounded-lg p-4">
                    <div class="text-3xl font-bold"><?php echo count($races); ?></div>
                    <div class="text-emerald-100 text-sm">Total Races</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Stats Bar -->
<section class="bg-white border-b sticky top-16 z-40 shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex flex-wrap gap-3 items-center justify-between">
            <div class="flex flex-wrap gap-3">
                <div class="flex items-center space-x-2 text-sm">
                    <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="font-semibold"><?php echo $statusCounts['live'] ?? 0; ?> Live</span>
                </div>
                <div class="flex items-center space-x-2 text-sm">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                    <span class="font-semibold"><?php echo $statusCounts['scheduled'] ?? 0; ?> Upcoming</span>
                </div>
                <div class="flex items-center space-x-2 text-sm">
                    <span class="w-3 h-3 bg-gray-500 rounded-full"></span>
                    <span class="font-semibold"><?php echo $statusCounts['finished'] ?? 0; ?> Finished</span>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                <i class="fas fa-sync-alt mr-1"></i> Auto-refreshing
            </div>
        </div>
    </div>
</section>

<!-- Filters Section -->
<section class="bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" class="bg-white rounded-xl shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Date Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar text-emerald-600 mr-1"></i> Date
                    </label>
                    <input type="date" 
                           name="date" 
                           value="<?php echo $selectedDate; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                
                <!-- Track Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt text-emerald-600 mr-1"></i> Track
                    </label>
                    <select name="track_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">All Tracks</option>
                        <?php foreach($tracks as $track): ?>
                        <option value="<?php echo $track['id']; ?>" <?php echo $selectedTrack == $track['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($track['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-filter text-emerald-600 mr-1"></i> Status
                    </label>
                    <select name="status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">All Status</option>
                        <option value="scheduled" <?php echo $selectedStatus === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="live" <?php echo $selectedStatus === 'live' ? 'selected' : ''; ?>>Live</option>
                        <option value="finished" <?php echo $selectedStatus === 'finished' ? 'selected' : ''; ?>>Finished</option>
                    </select>
                </div>
                
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search text-emerald-600 mr-1"></i> Search
                    </label>
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($searchQuery); ?>"
                           placeholder="Race or track name..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                
                <!-- Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition font-medium">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a href="races.php" 
                       class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Races List -->
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (empty($races)): ?>
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <i class="fas fa-flag-checkered text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-2xl font-bold text-gray-700 mb-2">No Races Found</h3>
            <p class="text-gray-500 mb-6">No races scheduled for the selected filters</p>
            <a href="races.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg inline-block transition">
                View All Races
            </a>
        </div>
        <?php else: ?>
        
        <!-- Race Cards Grid -->
        <div class="grid grid-cols-1 gap-6">
            <?php foreach($races as $race): ?>
            <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border-l-4 
                <?php 
                echo $race['status'] === 'live' ? 'border-red-500 animate-pulse-slow' : 
                     ($race['status'] === 'finished' ? 'border-gray-400' : 'border-emerald-500');
                ?>">
                
                <div class="flex flex-col md:flex-row">
                    <!-- Left Side - Race Image & Time -->
                    <div class="md:w-1/4 relative">
                        <div class="h-48 md:h-full bg-gradient-to-br from-emerald-600 to-emerald-800 relative overflow-hidden">
                            <?php if(!empty($race['image'])): ?>
                                <img src="<?php echo UPLOADS_URL . '/' . $race['image']; ?>" 
                                     alt="Race Image" 
                                     class="w-full h-full object-cover opacity-40">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=400" 
                                     alt="Horse Racing" 
                                     class="w-full h-full object-cover opacity-40">
                            <?php endif; ?>
                            
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-white">
                                <div class="text-5xl font-bold mb-2"><?php echo date('H:i', strtotime($race['race_time'])); ?></div>
                                <div class="text-sm uppercase tracking-wider"><?php echo $race['race_class'] ?? 'Race'; ?></div>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="absolute top-3 left-3">
                                <?php if($race['status'] === 'live'): ?>
                                <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold animate-pulse flex items-center">
                                    <span class="w-2 h-2 bg-white rounded-full mr-2 animate-ping"></span>
                                    LIVE
                                </span>
                                <?php elseif($race['status'] === 'finished'): ?>
                                <span class="bg-gray-600 text-white px-3 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-check-circle mr-1"></i> FINISHED
                                </span>
                                <?php else: ?>
                                <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-clock mr-1"></i> UPCOMING
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Featured Badge -->
                            <?php if($race['is_featured']): ?>
                            <div class="absolute top-3 right-3">
                                <span class="bg-yellow-500 text-gray-900 px-2 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-star"></i> FEATURED
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Middle - Race Details -->
                    <div class="md:w-1/2 p-6">
                        <div class="mb-4">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                <?php echo htmlspecialchars($race['name']); ?>
                            </h3>
                            <div class="flex flex-wrap gap-3 text-sm text-gray-600">
                                <span class="flex items-center">
                                    <i class="fas fa-map-marker-alt text-emerald-600 mr-1"></i>
                                    <?php echo htmlspecialchars($race['track_name']); ?>, <?php echo htmlspecialchars($race['track_country']); ?>
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-road text-emerald-600 mr-1"></i>
                                    <?php echo htmlspecialchars($race['distance'] ?? 'N/A'); ?>
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-layer-group text-emerald-600 mr-1"></i>
                                    <?php echo htmlspecialchars($race['race_class'] ?? 'N/A'); ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Race Stats Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-xs text-gray-500 mb-1">Runners</div>
                                <div class="text-lg font-bold text-gray-900">
                                    <i class="fas fa-horse text-emerald-600 mr-1"></i>
                                    <?php echo $race['runner_count']; ?>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-xs text-gray-500 mb-1">Prize</div>
                                <div class="text-lg font-bold text-emerald-600">
                                    <?php echo formatCurrency($race['prize_money'], $race['currency']); ?>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-xs text-gray-500 mb-1">Going</div>
                                <div class="text-sm font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($race['going'] ?? 'N/A'); ?>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-xs text-gray-500 mb-1">Weather</div>
                                <div class="text-sm font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($race['weather'] ?? 'N/A'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <?php if(!empty($race['description'])): ?>
                        <p class="text-sm text-gray-600 line-clamp-2">
                            <?php echo htmlspecialchars(substr($race['description'], 0, 150)); ?>...
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Right Side - Actions -->
                    <div class="md:w-1/4 p-6 bg-gray-50 flex flex-col justify-between border-l">
                        <!-- Countdown/Status -->
                        <div class="mb-4">
                            <?php
                            $raceDateTime = strtotime($race['race_date'] . ' ' . $race['race_time']);
                            $now = time();
                            $diff = $raceDateTime - $now;
                            ?>
                            
                            <?php if($race['status'] === 'scheduled' && $diff > 0): ?>
                            <div class="text-center bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500 mb-1">Starts in</div>
                                <div class="text-2xl font-bold text-emerald-600" data-countdown="<?php echo $raceDateTime; ?>">
                                    <?php 
                                    $hours = floor($diff / 3600);
                                    $minutes = floor(($diff % 3600) / 60);
                                    echo sprintf('%02d:%02d', $hours, $minutes);
                                    ?>
                                </div>
                            </div>
                            <?php elseif($race['status'] === 'live'): ?>
                            <div class="text-center bg-red-50 p-3 rounded-lg border-2 border-red-500">
                                <div class="text-red-600 font-bold text-lg animate-pulse">
                                    <i class="fas fa-circle text-xs mr-1"></i> RACE IN PROGRESS
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="text-center bg-gray-200 p-3 rounded-lg">
                                <div class="text-gray-600 font-bold">
                                    <i class="fas fa-flag-checkered mr-1"></i> Race Finished
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-2">
                            <a href="racecard.php?id=<?php echo $race['id']; ?>" 
                               class="block w-full bg-emerald-600 hover:bg-emerald-700 text-white text-center py-3 px-4 rounded-lg transition font-bold shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <i class="fas fa-chart-bar mr-2"></i> Race Card
                            </a>
                            
                            <div class="grid grid-cols-2 gap-2">
                                <button class="bg-white hover:bg-gray-100 text-gray-700 py-2 px-3 rounded-lg border transition text-sm font-medium">
                                    <i class="fas fa-star mr-1"></i> Save
                                </button>
                                <button class="bg-white hover:bg-gray-100 text-gray-700 py-2 px-3 rounded-lg border transition text-sm font-medium">
                                    <i class="fas fa-share-alt mr-1"></i> Share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Live Time Update
function updateLiveTime() {
    const now = new Date();
    document.getElementById('live-time').textContent = now.toLocaleTimeString();
}
setInterval(updateLiveTime, 1000);
updateLiveTime();

// Countdown Timer
function updateCountdowns() {
    const now = Math.floor(Date.now() / 1000);
    document.querySelectorAll('[data-countdown]').forEach(el => {
        const target = parseInt(el.dataset.countdown);
        const diff = target - now;
        
        if (diff > 0) {
            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;
            
            if (hours > 0) {
                el.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
            } else {
                el.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                el.classList.add('text-red-600');
            }
        } else {
            el.textContent = "Starting...";
            el.classList.add('animate-pulse');
        }
    });
}
setInterval(updateCountdowns, 1000);
</script>

<?php require_once 'components/footer.php'; ?>