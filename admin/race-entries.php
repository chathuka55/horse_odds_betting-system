<?php
/**
 * Admin: Manage Race Entries
 * Add/remove horses from a race
 */
define('ADMIN_ACCESS', true);
$pageTitle = 'Manage Race Entries';

require_once dirname(__DIR__) . '/includes/config.php';

$raceId = intval($_GET['race_id'] ?? 0);

if ($raceId <= 0) {
    setFlashMessage('error', 'Invalid race ID');
    redirect(ADMIN_URL . '/races.php');
}

// Get race details
$stmt = $db->prepare("
    SELECT r.*, t.name as track_name 
    FROM races r 
    LEFT JOIN tracks t ON r.track_id = t.id 
    WHERE r.id = ?
");
$stmt->execute([$raceId]);
$race = $stmt->fetch();

if (!$race) {
    setFlashMessage('error', 'Race not found');
    redirect(ADMIN_URL . '/races.php');
}

// Get current entries
$stmt = $db->prepare("
    SELECT re.*, 
           h.name as horse_name, h.age, h.gender, h.form, h.rating,
           j.name as jockey_name,
           t.name as trainer_name
    FROM race_entries re
    LEFT JOIN horses h ON re.horse_id = h.id
    LEFT JOIN jockeys j ON re.jockey_id = j.id
    LEFT JOIN trainers t ON h.trainer_id = t.id
    WHERE re.race_id = ?
    ORDER BY re.saddle_number ASC
");
$stmt->execute([$raceId]);
$entries = $stmt->fetchAll();

// Get available horses (not already in this race)
$enteredHorseIds = array_column($entries, 'horse_id');
$enteredHorseIdsStr = !empty($enteredHorseIds) ? implode(',', $enteredHorseIds) : '0';

$availableHorses = $db->query("
    SELECT h.*, t.name as trainer_name 
    FROM horses h 
    LEFT JOIN trainers t ON h.trainer_id = t.id
    WHERE h.is_active = 1 AND h.id NOT IN ({$enteredHorseIdsStr})
    ORDER BY h.name ASC
")->fetchAll();

// Get jockeys
$jockeys = getJockeys();

require_once 'components/header.php';
require_once 'components/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 overflow-x-hidden overflow-y-auto">
    <!-- Top Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center">
                <a href="<?php echo ADMIN_URL; ?>/races.php" class="text-gray-500 hover:text-gray-700 mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Manage Entries</h1>
                    <p class="text-sm text-gray-500">
                        <?php echo htmlspecialchars($race['name']); ?> - 
                        <?php echo htmlspecialchars($race['track_name']); ?> - 
                        <?php echo formatDate($race['race_date']); ?> <?php echo formatTime($race['race_time']); ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="openAddEntryModal()" 
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i> Add Horse
                </button>
                <a href="<?php echo ADMIN_URL; ?>/add-race.php?id=<?php echo $raceId; ?>" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-edit mr-2"></i> Edit Race
                </a>
            </div>
        </div>
    </header>
    
    <main class="p-6">
        <!-- Race Summary -->
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-xl p-6 mb-6 text-white">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                <div>
                    <div class="text-3xl font-bold"><?php echo count($entries); ?></div>
                    <div class="text-emerald-100 text-sm">Total Entries</div>
                </div>
                <div>
                    <div class="text-3xl font-bold"><?php echo htmlspecialchars($race['distance'] ?? 'N/A'); ?></div>
                    <div class="text-emerald-100 text-sm">Distance</div>
                </div>
                <div>
                    <div class="text-3xl font-bold"><?php echo htmlspecialchars($race['race_class'] ?? 'N/A'); ?></div>
                    <div class="text-emerald-100 text-sm">Class</div>
                </div>
                <div>
                    <div class="text-3xl font-bold"><?php echo formatCurrency($race['prize_money'], $race['currency']); ?></div>
                    <div class="text-emerald-100 text-sm">Prize Money</div>
                </div>
                <div>
                    <div class="text-3xl font-bold">
                        <span class="px-3 py-1 bg-white/20 rounded-full text-sm">
                            <?php echo ucfirst($race['status']); ?>
                        </span>
                    </div>
                    <div class="text-emerald-100 text-sm">Status</div>
                </div>
            </div>
        </div>
        
        <!-- Entries Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-list text-emerald-600 mr-2"></i>
                    Race Entries (<?php echo count($entries); ?>)
                </h2>
                <button onclick="autoCalculateProbabilities()" class="text-sm text-emerald-600 hover:text-emerald-700">
                    <i class="fas fa-calculator mr-1"></i> Auto-Calculate Probabilities
                </button>
            </div>
            
            <?php if (empty($entries)): ?>
            <div class="p-12 text-center">
                <i class="fas fa-horse text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Entries Yet</h3>
                <p class="text-gray-500 mb-4">Start adding horses to this race</p>
                <button onclick="openAddEntryModal()" 
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i> Add First Horse
                </button>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horse</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jockey</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trainer</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Draw</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Weight</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Odds</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Win %</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="entries-tbody">
                        <?php foreach($entries as $entry): ?>
                        <tr class="hover:bg-gray-50 transition" id="entry-row-<?php echo $entry['id']; ?>">
                            <td class="px-4 py-3">
                                <div class="w-10 h-10 bg-emerald-600 text-white rounded-full flex items-center justify-center font-bold">
                                    <?php echo $entry['saddle_number'] ?: '-'; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($entry['horse_name']); ?>
                                    <?php if ($entry['is_favorite']): ?>
                                    <span class="ml-2 text-yellow-500"><i class="fas fa-star"></i></span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo $entry['age']; ?>yo <?php echo ucfirst($entry['gender']); ?> • 
                                    Rating: <?php echo $entry['rating'] ?: 'N/A'; ?>
                                </div>
                                <div class="text-xs text-gray-400">Form: <?php echo $entry['form'] ?: 'N/A'; ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700"><?php echo htmlspecialchars($entry['jockey_name'] ?? 'TBA'); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700"><?php echo htmlspecialchars($entry['trainer_name'] ?? 'N/A'); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-200 rounded-full text-sm font-medium">
                                    <?php echo $entry['draw_position'] ?: '-'; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                <?php echo htmlspecialchars($entry['weight_carried'] ?: 'N/A'); ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="text" 
                                       class="w-20 px-2 py-1 border rounded text-center text-lg font-bold text-emerald-600 odds-input"
                                       data-entry-id="<?php echo $entry['id']; ?>"
                                       value="<?php echo htmlspecialchars($entry['current_odds'] ?: ''); ?>"
                                       placeholder="5/1">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="text-lg font-bold text-gray-800"><?php echo $entry['win_probability']; ?>%</div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                    <div class="bg-emerald-600 h-1.5 rounded-full" 
                                         style="width: <?php echo min(100, $entry['win_probability']); ?>%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($entry['is_non_runner']): ?>
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                    Non-Runner
                                </span>
                                <?php else: ?>
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                    Running
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button onclick="editEntry(<?php echo $entry['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleNonRunner(<?php echo $entry['id']; ?>, <?php echo $entry['is_non_runner'] ? 0 : 1; ?>)" 
                                            class="text-yellow-600 hover:text-yellow-900" 
                                            title="<?php echo $entry['is_non_runner'] ? 'Mark as Running' : 'Mark as Non-Runner'; ?>">
                                        <i class="fas fa-<?php echo $entry['is_non_runner'] ? 'check' : 'ban'; ?>"></i>
                                    </button>
                                    <button onclick="deleteEntry(<?php echo $entry['id']; ?>, '<?php echo addslashes($entry['horse_name']); ?>')" 
                                            class="text-red-600 hover:text-red-900" title="Remove">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Available Horses -->
        <?php if (!empty($availableHorses)): ?>
        <div class="bg-white rounded-xl shadow-sm mt-6 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-horse text-purple-600 mr-2"></i>
                Quick Add Available Horses (<?php echo count($availableHorses); ?>)
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach(array_slice($availableHorses, 0, 9) as $horse): ?>
                <div class="border rounded-lg p-4 hover:border-emerald-500 transition cursor-pointer"
                     onclick="quickAddHorse(<?php echo $horse['id']; ?>, '<?php echo addslashes($horse['name']); ?>')">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($horse['name']); ?></div>
                            <div class="text-sm text-gray-500">
                                <?php echo $horse['age']; ?>yo • <?php echo htmlspecialchars($horse['trainer_name'] ?? 'N/A'); ?>
                            </div>
                            <div class="text-xs text-gray-400">Rating: <?php echo $horse['rating'] ?: 'N/A'; ?></div>
                        </div>
                        <button class="bg-emerald-100 text-emerald-600 p-2 rounded-full hover:bg-emerald-200">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($availableHorses) > 9): ?>
            <div class="text-center mt-4">
                <button onclick="openAddEntryModal()" class="text-emerald-600 hover:text-emerald-700">
                    View all <?php echo count($availableHorses); ?> available horses <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<!-- Add/Edit Entry Modal -->
<div id="entry-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-800" id="modal-title">Add Horse to Race</h3>
                <button onclick="closeEntryModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <form id="entry-form" class="p-6">
            <input type="hidden" name="entry_id" id="entry_id" value="0">
            <input type="hidden" name="race_id" value="<?php echo $raceId; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Horse *</label>
                    <select name="horse_id" id="horse_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Select Horse --</option>
                        <?php foreach($availableHorses as $horse): ?>
                        <option value="<?php echo $horse['id']; ?>">
                            <?php echo htmlspecialchars($horse['name']); ?> 
                            (<?php echo $horse['age']; ?>yo, Rating: <?php echo $horse['rating'] ?: 'N/A'; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jockey</label>
                    <select name="jockey_id" id="jockey_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Select Jockey --</option>
                        <?php foreach($jockeys as $jockey): ?>
                        <option value="<?php echo $jockey['id']; ?>">
                            <?php echo htmlspecialchars($jockey['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Saddle Number</label>
                    <input type="number" name="saddle_number" id="saddle_number" min="1" max="99"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                           value="<?php echo count($entries) + 1; ?>">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Draw Position</label>
                    <input type="number" name="draw_position" id="draw_position" min="1" max="99"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Weight Carried</label>
                    <input type="text" name="weight_carried" id="weight_carried"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                           placeholder="e.g., 126 lbs">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Odds</label>
                    <input type="text" name="current_odds" id="current_odds"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                           placeholder="e.g., 5/1">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Win Probability %</label>
                    <input type="number" name="win_probability" id="win_probability" min="0" max="100" step="0.1"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                           placeholder="0.0">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Equipment</label>
                    <input type="text" name="equipment" id="equipment"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                           placeholder="e.g., Blinkers, Visor">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Comments</label>
                    <textarea name="comments" id="comments" rows="2"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                              placeholder="Any notes about this entry..."></textarea>
                </div>
                
                <div class="md:col-span-2 flex items-center space-x-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_favorite" id="is_favorite" value="1" 
                               class="w-5 h-5 rounded text-yellow-500 focus:ring-yellow-500 mr-2">
                        <span class="text-sm text-gray-700">Mark as Favorite</span>
                    </label>
                    
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_non_runner" id="is_non_runner" value="1" 
                               class="w-5 h-5 rounded text-red-500 focus:ring-red-500 mr-2">
                        <span class="text-sm text-gray-700">Non-Runner</span>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeEntryModal()"
                        class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Save Entry
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const raceId = <?php echo $raceId; ?>;

// Modal functions
function openAddEntryModal() {
    document.getElementById('modal-title').textContent = 'Add Horse to Race';
    document.getElementById('entry-form').reset();
    document.getElementById('entry_id').value = '0';
    document.getElementById('saddle_number').value = <?php echo count($entries) + 1; ?>;
    document.getElementById('entry-modal').classList.remove('hidden');
}

function closeEntryModal() {
    document.getElementById('entry-modal').classList.add('hidden');
}

function editEntry(entryId) {
    // Fetch entry data and populate form
    fetch('<?php echo ADMIN_URL; ?>/ajax/get-entry.php?id=' + entryId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modal-title').textContent = 'Edit Entry';
                document.getElementById('entry_id').value = data.data.id;
                document.getElementById('horse_id').value = data.data.horse_id;
                document.getElementById('jockey_id').value = data.data.jockey_id || '';
                document.getElementById('saddle_number').value = data.data.saddle_number || '';
                document.getElementById('draw_position').value = data.data.draw_position || '';
                document.getElementById('weight_carried').value = data.data.weight_carried || '';
                document.getElementById('current_odds').value = data.data.current_odds || '';
                document.getElementById('win_probability').value = data.data.win_probability || '';
                document.getElementById('equipment').value = data.data.equipment || '';
                document.getElementById('comments').value = data.data.comments || '';
                document.getElementById('is_favorite').checked = data.data.is_favorite == 1;
                document.getElementById('is_non_runner').checked = data.data.is_non_runner == 1;
                document.getElementById('entry-modal').classList.remove('hidden');
            }
        });
}

function quickAddHorse(horseId, horseName) {
    Swal.fire({
        title: 'Add ' + horseName + '?',
        text: 'Add this horse to the race with default settings?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Yes, add it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('race_id', raceId);
            formData.append('horse_id', horseId);
            formData.append('saddle_number', <?php echo count($entries) + 1; ?>);
            
            fetch('<?php echo ADMIN_URL; ?>/ajax/save-race-entry.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Added!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.error, 'error');
                }
            });
        }
    });
}

function deleteEntry(entryId, horseName) {
    Swal.fire({
        title: 'Remove ' + horseName + '?',
        text: 'This will remove the horse from this race.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?php echo ADMIN_URL; ?>/ajax/delete-item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: 'race_entry', id: entryId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('entry-row-' + entryId).remove();
                    Swal.fire('Removed!', data.message, 'success');
                } else {
                    Swal.fire('Error!', data.error, 'error');
                }
            });
        }
    });
}

function toggleNonRunner(entryId, status) {
    fetch('<?php echo ADMIN_URL; ?>/ajax/toggle-non-runner.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ entry_id: entryId, is_non_runner: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            Swal.fire('Error!', data.error, 'error');
        }
    });
}

// Update odds on blur
document.querySelectorAll('.odds-input').forEach(input => {
    input.addEventListener('blur', function() {
        const entryId = this.dataset.entryId;
        const odds = this.value.trim();
        
        if (odds) {
            fetch('<?php echo ADMIN_URL; ?>/ajax/update-odds.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ entry_id: entryId, odds: odds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success indicator
                    this.classList.add('border-green-500');
                    setTimeout(() => this.classList.remove('border-green-500'), 1000);
                }
            });
        }
    });
});

// Form submit
document.getElementById('entry-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?php echo ADMIN_URL; ?>/ajax/save-race-entry.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                timer: 1500
            }).then(() => location.reload());
        } else {
            Swal.fire('Error!', data.error, 'error');
        }
    });
});

function autoCalculateProbabilities() {
    Swal.fire({
        title: 'Calculate Probabilities',
        text: 'This will auto-calculate win probabilities based on horse form and ratings.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Calculate'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?php echo ADMIN_URL; ?>/ajax/calculate-probabilities.php?race_id=' + raceId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Done!', 'Probabilities calculated.', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error!', data.error, 'error');
                    }
                });
        }
    });
}

// Close modal on outside click
document.getElementById('entry-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEntryModal();
    }
});
</script>

<?php require_once 'components/footer.php'; ?>