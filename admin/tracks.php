<?php
/**
 * Admin: Manage Tracks
 */
define('ADMIN_ACCESS', true);
$pageTitle = 'Manage Tracks';

require_once dirname(__DIR__) . '/includes/config.php';
require_once 'components/header.php';
require_once 'components/sidebar.php';

$tracks = $db->query("
    SELECT t.*, (SELECT COUNT(*) FROM races WHERE track_id = t.id) as race_count
    FROM tracks t ORDER BY t.name
")->fetchAll();
?>

<div class="flex-1 overflow-x-hidden overflow-y-auto">
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manage Tracks</h1>
                <p class="text-sm text-gray-500">Total: <?php echo count($tracks); ?> tracks</p>
            </div>
            <button onclick="openAddModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Add Track
            </button>
        </div>
    </header>
    
    <main class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($tracks as $track): ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition">
                <div class="h-32 bg-gradient-to-r from-emerald-500 to-emerald-600">
                    <?php if (!empty($track['image'])): ?>
                    <img src="<?php echo UPLOADS_URL . '/' . $track['image']; ?>" class="w-full h-full object-cover">
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg"><?php echo htmlspecialchars($track['name']); ?></h3>
                    <p class="text-sm text-gray-500 mb-3">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <?php echo htmlspecialchars($track['location'] ?? ''); ?>, <?php echo htmlspecialchars($track['country'] ?? ''); ?>
                    </p>
                    <div class="flex justify-between text-sm">
                        <span><i class="fas fa-flag-checkered mr-1"></i><?php echo $track['race_count']; ?> races</span>
                        <span><?php echo ucfirst($track['track_type'] ?? 'turf'); ?></span>
                    </div>
                    <div class="flex justify-between mt-4 pt-4 border-t">
                        <span class="<?php echo $track['is_active'] ? 'text-green-600' : 'text-gray-400'; ?>">
                            <i class="fas fa-circle text-xs mr-1"></i><?php echo $track['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                        <div class="flex space-x-2">
                            <button onclick="editTrack(<?php echo htmlspecialchars(json_encode($track)); ?>)" class="text-blue-600"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteItem('track', <?php echo $track['id']; ?>, '<?php echo addslashes($track['name']); ?>')" class="text-red-600"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- Modal -->
<div id="track-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
        <div class="p-6 border-b flex justify-between">
            <h3 class="text-xl font-bold" id="modal-title">Add Track</h3>
            <button onclick="closeModal()" class="text-gray-500"><i class="fas fa-times"></i></button>
        </div>
        <form id="track-form" class="p-6">
            <input type="hidden" name="track_id" id="track_id" value="0">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Track Name *</label>
                    <input type="text" name="name" id="track_name" required class="w-full px-4 py-3 border rounded-lg focus:ring-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" name="location" id="track_location" class="w-full px-4 py-3 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                        <input type="text" name="country" id="track_country" class="w-full px-4 py-3 border rounded-lg">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="track_type" id="track_type" class="w-full px-4 py-3 border rounded-lg">
                            <option value="turf">Turf</option>
                            <option value="dirt">Dirt</option>
                            <option value="synthetic">Synthetic</option>
                            <option value="all-weather">All-Weather</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Length</label>
                        <input type="text" name="track_length" id="track_length" class="w-full px-4 py-3 border rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" id="track_is_active" value="1" checked class="rounded mr-2">
                        <span class="text-sm">Active</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg"><i class="fas fa-save mr-2"></i>Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modal-title').textContent = 'Add Track';
    document.getElementById('track-form').reset();
    document.getElementById('track_id').value = '0';
    document.getElementById('track_is_active').checked = true;
    document.getElementById('track-modal').classList.remove('hidden');
}

function editTrack(track) {
    document.getElementById('modal-title').textContent = 'Edit Track';
    document.getElementById('track_id').value = track.id;
    document.getElementById('track_name').value = track.name || '';
    document.getElementById('track_location').value = track.location || '';
    document.getElementById('track_country').value = track.country || '';
    document.getElementById('track_type').value = track.track_type || 'turf';
    document.getElementById('track_length').value = track.track_length || '';
    document.getElementById('track_is_active').checked = track.is_active == 1;
    document.getElementById('track-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('track-modal').classList.add('hidden');
}

document.getElementById('track-form').addEventListener('submit', function(e) {
    e.preventDefault();
    fetch('<?php echo ADMIN_URL; ?>/ajax/save-track.php', {method: 'POST', body: new FormData(this)})
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({icon:'success',title:'Success!',text:data.message,timer:1500}).then(() => location.reload());
        } else Swal.fire('Error!', data.error, 'error');
    });
});
</script>

<?php require_once 'components/footer.php'; ?>