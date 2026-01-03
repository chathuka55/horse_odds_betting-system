<?php
/**
 * Admin: Manage Races
 */
define('ADMIN_ACCESS', true);
$pageTitle = 'Manage Races';

require_once dirname(__DIR__) . '/includes/config.php';
require_once 'components/header.php';
require_once 'components/sidebar.php';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Filters
$filterDate = $_GET['date'] ?? '';
$filterTrack = $_GET['track_id'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build WHERE clause
$where = ['1=1'];
$params = [];

if (!empty($filterDate)) {
    $where[] = 'r.race_date = ?';
    $params[] = $filterDate;
}

if (!empty($filterTrack)) {
    $where[] = 'r.track_id = ?';
    $params[] = intval($filterTrack);
}

if (!empty($filterStatus)) {
    $where[] = 'r.status = ?';
    $params[] = $filterStatus;
}

if (!empty($search)) {
    $where[] = '(r.name LIKE ? OR t.name LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereString = implode(' AND ', $where);

// Get total count
$countStmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM races r 
    LEFT JOIN tracks t ON r.track_id = t.id 
    WHERE {$whereString}
");
$countStmt->execute($params);
$totalRaces = $countStmt->fetch()['total'] ?? 0;
$totalPages = ceil($totalRaces / $perPage);

// Get races
$stmt = $db->prepare("SELECT r.*, t.name as track_name, t.country as track_country,
           (SELECT COUNT(*) FROM race_entries WHERE race_id = r.id AND is_non_runner = 0) as runner_count
    FROM races r
    LEFT JOIN tracks t ON r.track_id = t.id
    WHERE {$whereString}
    ORDER BY r.race_date DESC, r.race_time DESC
    LIMIT ?, ?
");
// Append offset and limit as positional parameters
$params[] = intval($offset);
$params[] = intval($perPage);
$stmt->execute($params);
$races = $stmt->fetchAll() ?? [];

// Get tracks for filter
$tracks = getTracks() ?? [];
?>

<!-- Main Content -->
<div class="flex-1 overflow-x-hidden overflow-y-auto">
    <!-- Top Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manage Races</h1>
                <p class="text-sm text-gray-500">Total: <?php echo number_format($totalRaces); ?> races</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="<?php echo ADMIN_URL; ?>/add-race.php" 
                   class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Race
                </a>
            </div>
        </div>
    </header>
    
    <main class="p-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                           placeholder="Race or track name...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($filterDate); ?>"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Track</label>
                    <select name="track_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">All Tracks</option>
                        <?php foreach($tracks as $track): ?>
                        <option value="<?php echo $track['id']; ?>" <?php echo $filterTrack == $track['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($track['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">All Status</option>
                        <option value="scheduled" <?php echo $filterStatus === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="live" <?php echo $filterStatus === 'live' ? 'selected' : ''; ?>>Live</option>
                        <option value="finished" <?php echo $filterStatus === 'finished' ? 'selected' : ''; ?>>Finished</option>
                        <option value="cancelled" <?php echo $filterStatus === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/races.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Bulk Actions -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6 hidden" id="bulk-actions">
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600"><span id="selected-count">0</span> selected</span>
                <button onclick="bulkAction('delete')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
                <button onclick="bulkAction('feature')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">
                    <i class="fas fa-star mr-1"></i> Feature
                </button>
                <button onclick="bulkAction('unfeature')" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
                    <i class="far fa-star mr-1"></i> Unfeature
                </button>
            </div>
        </div>
        
        <!-- Races Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" id="select-all" class="rounded text-emerald-600">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Race</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Track</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Runners</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Prize</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($races)): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-flag-checkered text-4xl mb-2"></i>
                                <p>No races found</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($races as $race): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="row-checkbox rounded text-emerald-600" value="<?php echo $race['id']; ?>">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <?php if ($race['is_featured']): ?>
                                    <i class="fas fa-star text-yellow-500 mr-2"></i>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($race['name']); ?></div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($race['distance'] ?? 'N/A'); ?> • <?php echo htmlspecialchars($race['race_class'] ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($race['track_name'] ?? 'N/A'); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($race['track_country'] ?? ''); ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900"><?php echo formatDate($race['race_date']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo formatTime($race['race_time']); ?></div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo $race['runner_count']; ?> runners
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-medium text-emerald-600">
                                    <?php echo formatCurrency($race['prize_money'], $race['currency']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php
                                $statusColors = [
                                    'scheduled' => 'bg-blue-100 text-blue-800',
                                    'live' => 'bg-red-100 text-red-800',
                                    'finished' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                    'postponed' => 'bg-yellow-100 text-yellow-800'
                                ];
                                $statusColor = $statusColors[$race['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                    <?php if ($race['status'] === 'live'): ?>
                                    <span class="w-2 h-2 bg-red-500 rounded-full mr-1 animate-pulse"></span>
                                    <?php endif; ?>
                                    <?php echo ucfirst($race['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="<?php echo ADMIN_URL; ?>/add-race.php?id=<?php echo $race['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo ADMIN_URL; ?>/race-entries.php?race_id=<?php echo $race['id']; ?>" 
                                       class="text-purple-600 hover:text-purple-900" title="Manage Entries">
                                        <i class="fas fa-horse"></i>
                                    </a>
                                    <?php if ($race['status'] === 'scheduled' || $race['status'] === 'live'): ?>
                                    <a href="<?php echo ADMIN_URL; ?>/add-result.php?race_id=<?php echo $race['id']; ?>" 
                                       class="text-green-600 hover:text-green-900" title="Add Result">
                                        <i class="fas fa-trophy"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?php echo SITE_URL; ?>/racecard.php?id=<?php echo $race['id']; ?>" 
                                       target="_blank" class="text-gray-600 hover:text-gray-900" title="View">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <button onclick="deleteItem('race', <?php echo $race['id']; ?>, '<?php echo addslashes($race['name']); ?>')" 
                                            class="text-red-600 hover:text-red-900" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t">
                <div class="text-sm text-gray-700">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalRaces); ?> of <?php echo $totalRaces; ?> races
                </div>
                <div class="flex space-x-1">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter(['search' => $search, 'date' => $filterDate, 'track_id' => $filterTrack, 'status' => $filterStatus])); ?>" 
                       class="px-3 py-1 bg-white border rounded hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter(['search' => $search, 'date' => $filterDate, 'track_id' => $filterTrack, 'status' => $filterStatus])); ?>" 
                       class="px-3 py-1 border rounded <?php echo $i === $page ? 'bg-emerald-600 text-white' : 'bg-white hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter(['search' => $search, 'date' => $filterDate, 'track_id' => $filterTrack, 'status' => $filterStatus])); ?>" 
                       class="px-3 py-1 bg-white border rounded hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
// Select all checkbox
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
    updateBulkActions();
});

// Individual checkboxes
document.querySelectorAll('.row-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    if (checked.length > 0) {
        bulkActions.classList.remove('hidden');
        selectedCount.textContent = checked.length;
    } else {
        bulkActions.classList.add('hidden');
    }
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => parseInt(cb.value));
}

function bulkAction(action) {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    
    Swal.fire({
        title: 'Are you sure?',
        text: `This will ${action} ${ids.length} race(s)`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, proceed!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?php echo ADMIN_URL; ?>/ajax/bulk-action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: action, type: 'race', ids: ids })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.error, 'error');
                }
            });
        }
    });
}

function deleteItem(type, id, name) {
    Swal.fire({
        title: 'Delete ' + name + '?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?php echo ADMIN_URL; ?>/ajax/delete-item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: type, id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.error, 'error');
                }
            });
        }
    });
}
</script>

<?php require_once 'components/footer.php'; ?>