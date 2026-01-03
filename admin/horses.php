<?php
/**
 * Admin: Manage Horses
 */
define('ADMIN_ACCESS', true);
$pageTitle = 'Manage Horses';

require_once dirname(__DIR__) . '/includes/config.php';
require_once 'components/header.php';
require_once 'components/sidebar.php';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Filters
$search = $_GET['search'] ?? '';
$filterTrainer = $_GET['trainer_id'] ?? '';
$filterActive = $_GET['active'] ?? '';

// Build WHERE clause
$where = ['1=1'];
$params = [];

if (!empty($search)) {
    $where[] = '(h.name LIKE ? OR h.sire LIKE ? OR h.dam LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($filterTrainer)) {
    $where[] = 'h.trainer_id = ?';
    $params[] = intval($filterTrainer);
}

if ($filterActive !== '') {
    $where[] = 'h.is_active = ?';
    $params[] = intval($filterActive);
}

$whereString = implode(' AND ', $where);

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) as total FROM horses h WHERE {$whereString}");
$countStmt->execute($params);
$totalHorses = $countStmt->fetch()['total'] ?? 0;
$totalPages = ceil($totalHorses / $perPage);

// Get horses
$stmt = $db->prepare("SELECT h.*, t.name as trainer_name, o.name as owner_name
    FROM horses h
    LEFT JOIN trainers t ON h.trainer_id = t.id
    LEFT JOIN owners o ON h.owner_id = o.id
    WHERE {$whereString}
    ORDER BY h.name ASC
    LIMIT ?, ?
");
// Append offset and limit as positional parameters
$params[] = intval($offset);
$params[] = intval($perPage);
$stmt->execute($params);
$horses = $stmt->fetchAll() ?? [];

// Get trainers for filter
$trainers = getTrainers() ?? [];
?>

<!-- Main Content -->
<div class="flex-1 overflow-x-hidden overflow-y-auto">
    <!-- Top Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manage Horses</h1>
                <p class="text-sm text-gray-500">Total: <?php echo number_format($totalHorses); ?> horses</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="<?php echo ADMIN_URL; ?>/add-horse.php" 
                   class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Horse
                </a>
            </div>
        </div>
    </header>
    
    <main class="p-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                           placeholder="Horse name, sire, dam...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trainer</label>
                    <select name="trainer_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">All Trainers</option>
                        <?php foreach($trainers as $trainer): ?>
                        <option value="<?php echo $trainer['id']; ?>" <?php echo $filterTrainer == $trainer['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($trainer['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $filterActive === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $filterActive === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/horses.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Bulk Actions -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6 hidden" id="bulk-actions">
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600"><span id="selected-count">0</span> selected</span>
                <button onclick="bulkAction('delete', 'horse')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
                <button onclick="bulkAction('activate', 'horse')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                    <i class="fas fa-check mr-1"></i> Activate
                </button>
                <button onclick="bulkAction('deactivate', 'horse')" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
                    <i class="fas fa-ban mr-1"></i> Deactivate
                </button>
            </div>
        </div>
        
        <!-- Horses Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" id="select-all" class="rounded text-emerald-600">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horse</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trainer / Owner</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Career</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rating</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($horses)): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-horse text-4xl mb-2"></i>
                                <p>No horses found</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($horses as $horse): ?>
                        <tr class="hover:bg-gray-50 transition" id="horse-row-<?php echo $horse['id']; ?>">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="row-checkbox rounded text-emerald-600" value="<?php echo $horse['id']; ?>">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <?php if (!empty($horse['image'])): ?>
                                    <img src="<?php echo UPLOADS_URL . '/' . $horse['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($horse['name']); ?>"
                                         class="w-12 h-12 rounded-full object-cover mr-3">
                                    <?php else: ?>
                                    <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-horse text-emerald-600"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($horse['name']); ?></div>
                                        <div class="text-sm text-gray-500">Form: <?php echo htmlspecialchars($horse['form'] ?: 'N/A'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm">
                                    <div><?php echo $horse['age']; ?>yo <?php echo ucfirst($horse['gender'] ?? 'Unknown'); ?></div>
                                    <div class="text-gray-500">
                                        <?php if (!empty($horse['sire'])): ?>
                                        Sire: <?php echo htmlspecialchars($horse['sire']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm">
                                    <div><?php echo htmlspecialchars($horse['trainer_name'] ?? 'N/A'); ?></div>
                                    <div class="text-gray-500"><?php echo htmlspecialchars($horse['owner_name'] ?? 'N/A'); ?></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="text-sm">
                                    <div class="font-semibold">
                                        <?php echo $horse['career_wins']; ?>-<?php echo $horse['career_places']; ?>-<?php echo $horse['career_shows']; ?>
                                    </div>
                                    <div class="text-gray-500"><?php echo $horse['career_starts']; ?> starts</div>
                                    <div class="text-green-600 text-xs">
                                        <?php echo formatCurrency($horse['career_earnings']); ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full 
                                    <?php echo $horse['rating'] >= 90 ? 'bg-green-100 text-green-800' : 
                                          ($horse['rating'] >= 80 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'); ?>
                                    font-bold">
                                    <?php echo $horse['rating'] ?: '-'; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($horse['is_active']): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="<?php echo ADMIN_URL; ?>/add-horse.php?id=<?php echo $horse['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/horse.php?id=<?php echo $horse['id']; ?>" 
                                       target="_blank" class="text-gray-600 hover:text-gray-900" title="View">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <button onclick="deleteItem('horse', <?php echo $horse['id']; ?>, '<?php echo addslashes($horse['name']); ?>')" 
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
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalHorses); ?> of <?php echo $totalHorses; ?> horses
                </div>
                <div class="flex space-x-1">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter(['search' => $search, 'trainer_id' => $filterTrainer, 'active' => $filterActive])); ?>" 
                       class="px-3 py-1 bg-white border rounded hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter(['search' => $search, 'trainer_id' => $filterTrainer, 'active' => $filterActive])); ?>" 
                       class="px-3 py-1 border rounded <?php echo $i === $page ? 'bg-emerald-600 text-white' : 'bg-white hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter(['search' => $search, 'trainer_id' => $filterTrainer, 'active' => $filterActive])); ?>" 
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
// Initialize select all and bulk actions
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
    updateBulkActions();
});

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
</script>

<?php require_once 'components/footer.php'; ?>