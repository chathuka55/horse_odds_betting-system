<?php
/**
 * Admin: Add Race Results
 */
define('ADMIN_ACCESS', true);
$pageTitle = 'Add Race Results';

require_once dirname(__DIR__) . '/includes/config.php';

$raceId = intval($_GET['race_id'] ?? 0);
if ($raceId <= 0) {
    setFlashMessage('error', 'Invalid race ID');
    redirect(ADMIN_URL . '/results.php');
}

$stmt = $db->prepare("SELECT r.*, t.name as track_name FROM races r LEFT JOIN tracks t ON r.track_id = t.id WHERE r.id = ?");
$stmt->execute([$raceId]);
$race = $stmt->fetch();

if (!$race) {
    setFlashMessage('error', 'Race not found');
    redirect(ADMIN_URL . '/results.php');
}

$stmt = $db->prepare("
    SELECT re.*, h.name as horse_name, j.name as jockey_name, rr.finish_position, rr.finish_time, rr.margin
    FROM race_entries re
    LEFT JOIN horses h ON re.horse_id = h.id
    LEFT JOIN jockeys j ON re.jockey_id = j.id
    LEFT JOIN race_results rr ON rr.race_entry_id = re.id
    WHERE re.race_id = ? AND re.is_non_runner = 0
    ORDER BY COALESCE(rr.finish_position, 999), re.saddle_number
");
$stmt->execute([$raceId]);
$entries = $stmt->fetchAll();

require_once 'components/header.php';
require_once 'components/sidebar.php';
?>

<div class="flex-1 overflow-x-hidden overflow-y-auto">
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center">
                <a href="<?php echo ADMIN_URL; ?>/results.php" class="text-gray-500 hover:text-gray-700 mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Add Race Results</h1>
                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($race['name']); ?> - <?php echo formatDate($race['race_date']); ?></p>
                </div>
            </div>
        </div>
    </header>
    
    <main class="p-6">
        <form id="results-form">
            <input type="hidden" name="race_id" value="<?php echo $raceId; ?>">
            
            <!-- Race Info -->
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-xl p-6 mb-6 text-white">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold"><?php echo count($entries); ?></div>
                        <div class="text-emerald-100 text-sm">Runners</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold"><?php echo htmlspecialchars($race['distance'] ?? 'N/A'); ?></div>
                        <div class="text-emerald-100 text-sm">Distance</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold"><?php echo htmlspecialchars($race['going'] ?? 'N/A'); ?></div>
                        <div class="text-emerald-100 text-sm">Going</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold"><?php echo formatCurrency($race['prize_money'], $race['currency']); ?></div>
                        <div class="text-emerald-100 text-sm">Prize</div>
                    </div>
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-bold text-gray-800">Enter Finishing Positions</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-20">Position</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horse</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jockey</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Margin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach($entries as $i => $entry): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-center">
                                    <input type="number" name="results[<?php echo $entry['id']; ?>]" 
                                           value="<?php echo $entry['finish_position'] ?? ''; ?>"
                                           min="1" max="<?php echo count($entries); ?>"
                                           class="w-16 px-3 py-2 border rounded-lg text-center text-lg font-bold focus:ring-emerald-500">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center font-bold mr-3">
                                            <?php echo $entry['saddle_number']; ?>
                                        </span>
                                        <div>
                                            <div class="font-semibold"><?php echo htmlspecialchars($entry['horse_name']); ?></div>
                                            <div class="text-sm text-gray-500">Odds: <?php echo $entry['current_odds'] ?? 'N/A'; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($entry['jockey_name'] ?? 'N/A'); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <input type="text" name="times[<?php echo $entry['id']; ?>]" 
                                           value="<?php echo $entry['finish_time'] ?? ''; ?>"
                                           class="w-24 px-3 py-2 border rounded-lg text-center focus:ring-emerald-500"
                                           placeholder="0:00.00">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="text" name="margins[<?php echo $entry['id']; ?>]" 
                                           value="<?php echo $entry['margin'] ?? ''; ?>"
                                           class="w-24 px-3 py-2 border rounded-lg text-center focus:ring-emerald-500"
                                           placeholder="1/2 L">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-6 flex items-center justify-between">
                <a href="<?php echo ADMIN_URL; ?>/results.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg">Cancel</a>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-lg font-bold">
                    <i class="fas fa-save mr-2"></i>Save Results
                </button>
            </div>
        </form>
    </main>
</div>

<script>
document.getElementById('results-form').addEventListener('submit', function(e) {
    e.preventDefault();
    fetch('<?php echo ADMIN_URL; ?>/ajax/save-result.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({icon:'success',title:'Results Saved!',timer:1500}).then(() => window.location.href = '<?php echo ADMIN_URL; ?>/results.php');
        } else {
            Swal.fire('Error!', data.error, 'error');
        }
    });
});
</script>

<?php require_once 'components/footer.php'; ?>