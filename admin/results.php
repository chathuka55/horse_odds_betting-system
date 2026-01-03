<?php
/**
 * Admin: Manage Race Results
 */
define('ADMIN_ACCESS', true);
$pageTitle = 'Race Results';

require_once dirname(__DIR__) . '/includes/config.php';
require_once 'components/header.php';
require_once 'components/sidebar.php';

$pendingResults = $db->query("
    SELECT r.*, t.name as track_name,
           (SELECT COUNT(*) FROM race_entries WHERE race_id = r.id AND is_non_runner = 0) as runner_count,
           (SELECT COUNT(*) FROM race_results WHERE race_id = r.id) as result_count
    FROM races r
    LEFT JOIN tracks t ON r.track_id = t.id
    WHERE r.status IN ('scheduled', 'live', 'finished')
    ORDER BY r.race_date DESC, r.race_time DESC
    LIMIT 50
")->fetchAll();

$completedResults = $db->query("
    SELECT r.*, t.name as track_name,
           (SELECT h.name FROM race_results rr 
            JOIN race_entries re ON rr.race_entry_id = re.id 
            JOIN horses h ON re.horse_id = h.id 
            WHERE rr.race_id = r.id AND rr.finish_position = 1 LIMIT 1) as winner_name
    FROM races r
    LEFT JOIN tracks t ON r.track_id = t.id
    WHERE r.status = 'finished' AND EXISTS (SELECT 1 FROM race_results WHERE race_id = r.id)
    ORDER BY r.race_date DESC LIMIT 20
")->fetchAll();
?>

<div class="flex-1 overflow-x-hidden overflow-y-auto">
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Race Results</h1>
                <p class="text-sm text-gray-500">Manage and record race results</p>
            </div>
        </div>
    </header>
    
    <main class="p-6">
        <!-- Pending Results -->
        <div class="bg-white rounded-xl shadow-sm mb-6">
            <div class="p-4 border-b">
                <h2 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-clock text-yellow-500 mr-2"></i>Pending Results
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Race</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Track</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Runners</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach($pendingResults as $race): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($race['name']); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($race['track_name']); ?></td>
                            <td class="px-4 py-3 text-center text-sm">
                                <?php echo formatDate($race['race_date']); ?><br>
                                <span class="text-gray-500"><?php echo formatTime($race['race_time']); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs"><?php echo $race['runner_count']; ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($race['result_count'] > 0): ?>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Results Entered</span>
                                <?php else: ?>
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs"><?php echo ucfirst($race['status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="<?php echo ADMIN_URL; ?>/add-result.php?race_id=<?php echo $race['id']; ?>" 
                                   class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm">
                                    <i class="fas fa-trophy mr-1"></i><?php echo $race['result_count'] > 0 ? 'Edit' : 'Add'; ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Completed Results -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b">
                <h2 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>Completed Results
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Race</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Track</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Winner</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach($completedResults as $race): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($race['name']); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($race['track_name']); ?></td>
                            <td class="px-4 py-3 text-center text-sm"><?php echo formatDate($race['race_date']); ?></td>
                            <td class="px-4 py-3">
                                <i class="fas fa-trophy text-yellow-500 mr-2"></i><?php echo htmlspecialchars($race['winner_name'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="<?php echo ADMIN_URL; ?>/add-result.php?race_id=<?php echo $race['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-3"><i class="fas fa-edit"></i></a>
                                <a href="<?php echo SITE_URL; ?>/racecard.php?id=<?php echo $race['id']; ?>" target="_blank" class="text-gray-600 hover:text-gray-800"><i class="fas fa-external-link-alt"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once 'components/footer.php'; ?>