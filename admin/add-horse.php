<?php
/**
 * Admin: Add/Edit Horse
 * Complete horse management form
 */
define('ADMIN_ACCESS', true);

require_once dirname(__DIR__) . '/includes/config.php';

$horseId = intval($_GET['id'] ?? 0);
$isEdit = $horseId > 0;
$pageTitle = $isEdit ? 'Edit Horse' : 'Add New Horse';

$horse = null;
if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM horses WHERE id = ?");
    $stmt->execute([$horseId]);
    $horse = $stmt->fetch();
    
    if (!$horse) {
        setFlashMessage('error', 'Horse not found');
        redirect(ADMIN_URL . '/horses.php');
    }
}

// Get dropdown data
$trainers = getTrainers();
$owners = getOwners();

require_once 'components/header.php';
require_once 'components/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 overflow-x-hidden overflow-y-auto">
    <!-- Top Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center">
                <a href="<?php echo ADMIN_URL; ?>/horses.php" class="text-gray-500 hover:text-gray-700 mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800"><?php echo $pageTitle; ?></h1>
                    <p class="text-sm text-gray-500"><?php echo $isEdit ? 'Update horse information' : 'Add a new horse to the database'; ?></p>
                </div>
            </div>
        </div>
    </header>
    
    <main class="p-6">
        <form id="horse-form" enctype="multipart/form-data" class="max-w-5xl mx-auto">
            <input type="hidden" name="horse_id" value="<?php echo $horseId; ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <span class="bg-emerald-100 text-emerald-600 p-2 rounded-lg mr-3">
                                <i class="fas fa-horse"></i>
                            </span>
                            Basic Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Horse Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo htmlspecialchars($horse['name'] ?? ''); ?>"
                                       placeholder="e.g., Thunder Bolt">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                                <input type="number" name="age" min="1" max="30"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo $horse['age'] ?? ''; ?>"
                                       placeholder="e.g., 4">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                <select name="gender"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">-- Select Gender --</option>
                                    <option value="colt" <?php echo ($horse['gender'] ?? '') === 'colt' ? 'selected' : ''; ?>>Colt</option>
                                    <option value="filly" <?php echo ($horse['gender'] ?? '') === 'filly' ? 'selected' : ''; ?>>Filly</option>
                                    <option value="stallion" <?php echo ($horse['gender'] ?? '') === 'stallion' ? 'selected' : ''; ?>>Stallion</option>
                                    <option value="mare" <?php echo ($horse['gender'] ?? '') === 'mare' ? 'selected' : ''; ?>>Mare</option>
                                    <option value="gelding" <?php echo ($horse['gender'] ?? '') === 'gelding' ? 'selected' : ''; ?>>Gelding</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                                <input type="text" name="color"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo htmlspecialchars($horse['color'] ?? ''); ?>"
                                       placeholder="e.g., Bay, Chestnut, Gray">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Country of Birth</label>
                                <input type="text" name="country_of_birth"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo htmlspecialchars($horse['country_of_birth'] ?? ''); ?>"
                                       placeholder="e.g., USA, UK, Ireland">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                                <input type="date" name="date_of_birth"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo $horse['date_of_birth'] ?? ''; ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Weight</label>
                                <input type="text" name="weight"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo htmlspecialchars($horse['weight'] ?? ''); ?>"
                                       placeholder="e.g., 126 lbs">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pedigree -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <span class="bg-purple-100 text-purple-600 p-2 rounded-lg mr-3">
                                <i class="fas fa-sitemap"></i>
                            </span>
                            Pedigree
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sire (Father)</label>
                                <input type="text" name="sire"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo htmlspecialchars($horse['sire'] ?? ''); ?>"
                                       placeholder="Father's name">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dam (Mother)</label>
                                <input type="text" name="dam"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo htmlspecialchars($horse['dam'] ?? ''); ?>"
                                       placeholder="Mother's name">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Connections -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <span class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3">
                                <i class="fas fa-users"></i>
                            </span>
                            Connections
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Trainer</label>
                                <select name="trainer_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">-- Select Trainer --</option>
                                    <?php foreach($trainers as $trainer): ?>
                                    <option value="<?php echo $trainer['id']; ?>" 
                                            <?php echo ($horse['trainer_id'] ?? '') == $trainer['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($trainer['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    <a href="<?php echo ADMIN_URL; ?>/trainers.php" class="text-emerald-600 hover:underline">+ Add new trainer</a>
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Owner</label>
                                <select name="owner_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">-- Select Owner --</option>
                                    <?php foreach($owners as $owner): ?>
                                    <option value="<?php echo $owner['id']; ?>" 
                                            <?php echo ($horse['owner_id'] ?? '') == $owner['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($owner['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    <a href="<?php echo ADMIN_URL; ?>/owners.php" class="text-emerald-600 hover:underline">+ Add new owner</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Career Statistics -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <span class="bg-yellow-100 text-yellow-600 p-2 rounded-lg mr-3">
                                <i class="fas fa-trophy"></i>
                            </span>
                            Career Statistics
                        </h2>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Starts</label>
                                <input type="number" name="career_starts" min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo $horse['career_starts'] ?? 0; ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Wins (1st)</label>
                                <input type="number" name="career_wins" min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo $horse['career_wins'] ?? 0; ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Places (2nd)</label>
                                <input type="number" name="career_places" min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo $horse['career_places'] ?? 0; ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Shows (3rd)</label>
                                <input type="number" name="career_shows" min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo $horse['career_shows'] ?? 0; ?>">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Career Earnings ($)</label>
                                <input type="number" name="career_earnings" min="0" step="0.01"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo $horse['career_earnings'] ?? 0; ?>">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Form (Last 5 races)</label>
                                <input type="text" name="form"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo htmlspecialchars($horse['form'] ?? ''); ?>"
                                       placeholder="e.g., 1-2-1-3-1">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preferences & Equipment -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <span class="bg-gray-100 text-gray-600 p-2 rounded-lg mr-3">
                                <i class="fas fa-cog"></i>
                            </span>
                            Preferences & Equipment
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Best Distance</label>
                                <input type="text" name="best_distance"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo htmlspecialchars($horse['best_distance'] ?? ''); ?>"
                                       placeholder="e.g., 1 1/4 miles">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Going</label>
                                <select name="preferred_going"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">-- Select --</option>
                                    <option value="Firm" <?php echo ($horse['preferred_going'] ?? '') === 'Firm' ? 'selected' : ''; ?>>Firm</option>
                                    <option value="Good to Firm" <?php echo ($horse['preferred_going'] ?? '') === 'Good to Firm' ? 'selected' : ''; ?>>Good to Firm</option>
                                    <option value="Good" <?php echo ($horse['preferred_going'] ?? '') === 'Good' ? 'selected' : ''; ?>>Good</option>
                                    <option value="Good to Soft" <?php echo ($horse['preferred_going'] ?? '') === 'Good to Soft' ? 'selected' : ''; ?>>Good to Soft</option>
                                    <option value="Soft" <?php echo ($horse['preferred_going'] ?? '') === 'Soft' ? 'selected' : ''; ?>>Soft</option>
                                    <option value="Heavy" <?php echo ($horse['preferred_going'] ?? '') === 'Heavy' ? 'selected' : ''; ?>>Heavy</option>
                                    <option value="Any" <?php echo ($horse['preferred_going'] ?? '') === 'Any' ? 'selected' : ''; ?>>Any Ground</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Equipment</label>
                                <input type="text" name="equipment"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo htmlspecialchars($horse['equipment'] ?? ''); ?>"
                                       placeholder="e.g., Blinkers, Visor, Tongue Tie">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Medication</label>
                                <input type="text" name="medication"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo htmlspecialchars($horse['medication'] ?? ''); ?>"
                                       placeholder="e.g., Lasix">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Sidebar -->
                <div class="space-y-6">
                    <!-- Status -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Status & Rating</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rating (1-100)</label>
                                <input type="number" name="rating" min="0" max="100"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       value="<?php echo $horse['rating'] ?? 80; ?>">
                            </div>
                            
                            <div class="border-t pt-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" 
                                           class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 mr-3"
                                           <?php echo ($horse['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    <span>
                                        <span class="text-sm font-medium text-gray-700">Active</span>
                                        <span class="block text-xs text-gray-500">Horse is available for races</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Horse Image -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Horse Image</h2>
                        
                        <div class="space-y-4">
                            <?php if (!empty($horse['image'])): ?>
                            <div class="relative" id="current-image">
                                <img src="<?php echo UPLOADS_URL . '/' . $horse['image']; ?>" 
                                     alt="Horse Image" 
                                     class="w-full h-48 object-cover rounded-lg">
                                <button type="button" onclick="removeImage()"
                                        class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <input type="hidden" name="remove_image" id="remove_image" value="0">
                            <?php endif; ?>
                            
                            <div id="image-upload" class="<?php echo !empty($horse['image']) ? 'hidden' : ''; ?>">
                                <label class="block w-full cursor-pointer">
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-emerald-500 transition">
                                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                        <p class="text-sm text-gray-600">Click to upload image</p>
                                        <p class="text-xs text-gray-400 mt-1">PNG, JPG, GIF up to 5MB</p>
                                    </div>
                                    <input type="file" name="image" accept="image/*" class="hidden" onchange="previewImage(this)">
                                </label>
                            </div>
                            
                            <div id="image-preview" class="hidden">
                                <img id="preview-img" src="" alt="Preview" class="w-full h-48 object-cover rounded-lg">
                                <button type="button" onclick="clearPreview()" 
                                        class="mt-2 text-sm text-red-600 hover:text-red-700">
                                    <i class="fas fa-times mr-1"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats (Edit Mode Only) -->
                    <?php if ($isEdit): ?>
                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-sm p-6 text-white">
                        <h2 class="text-lg font-bold mb-4">Quick Stats</h2>
                        
                        <?php
                        $winRate = ($horse['career_starts'] > 0) ? round(($horse['career_wins'] / $horse['career_starts']) * 100, 1) : 0;
                        ?>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span>Win Rate:</span>
                                <span class="font-bold"><?php echo $winRate; ?>%</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Record:</span>
                                <span class="font-bold"><?php echo $horse['career_wins']; ?>-<?php echo $horse['career_places']; ?>-<?php echo $horse['career_shows']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Created:</span>
                                <span class="font-bold"><?php echo formatDate($horse['created_at']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <a href="<?php echo ADMIN_URL; ?>/horses.php" 
                       class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg transition text-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                    
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <button type="submit" name="action" value="save_and_new"
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">
                            <i class="fas fa-plus mr-2"></i> Save & Add New
                        </button>
                        
                        <button type="submit" name="action" value="save"
                                class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg transition">
                            <i class="fas fa-save mr-2"></i> 
                            <?php echo $isEdit ? 'Update Horse' : 'Create Horse'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<script>
// Image Preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').classList.remove('hidden');
            document.getElementById('image-upload').classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function clearPreview() {
    document.getElementById('image-preview').classList.add('hidden');
    document.getElementById('image-upload').classList.remove('hidden');
    document.querySelector('input[name="image"]').value = '';
}

function removeImage() {
    document.getElementById('current-image').classList.add('hidden');
    document.getElementById('image-upload').classList.remove('hidden');
    document.getElementById('remove_image').value = '1';
}

// Form Submit Handler
document.getElementById('horse-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const action = e.submitter.value;
    formData.append('submit_action', action);
    
    const submitBtn = e.submitter;
    const originalHTML = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
    
    fetch('<?php echo ADMIN_URL; ?>/ajax/save-horse.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                if (action === 'save_and_new') {
                    window.location.href = '<?php echo ADMIN_URL; ?>/add-horse.php';
                } else {
                    window.location.href = '<?php echo ADMIN_URL; ?>/horses.php';
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.error
            });
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred.'
        });
    });
});
</script>

<?php require_once 'components/footer.php'; ?>