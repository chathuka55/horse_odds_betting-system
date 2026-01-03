<?php
/**
 * Admin: Add/Edit Race
 * Complete race management form
 */
define('ADMIN_ACCESS', true);

require_once dirname(__DIR__) . '/includes/config.php';

$raceId = intval($_GET['id'] ?? 0);
$isEdit = $raceId > 0;
$pageTitle = $isEdit ? 'Edit Race' : 'Add New Race';

$race = null;
if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM races WHERE id = ?");
    $stmt->execute([$raceId]);
    $race = $stmt->fetch();
    
    if (!$race) {
        setFlashMessage('error', 'Race not found');
        redirect(ADMIN_URL . '/races.php');
    }
}

// Get dropdown data
$tracks = getTracks();

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
                    <h1 class="text-2xl font-bold text-gray-800"><?php echo $pageTitle; ?></h1>
                    <p class="text-sm text-gray-500"><?php echo $isEdit ? 'Update race information' : 'Create a new race event'; ?></p>
                </div>
            </div>
            <?php if ($isEdit): ?>
            <div class="flex items-center space-x-3">
                <a href="<?php echo ADMIN_URL; ?>/race-entries.php?race_id=<?php echo $raceId; ?>" 
                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-horse mr-2"></i> Manage Entries
                </a>
                <a href="<?php echo SITE_URL; ?>/racecard.php?id=<?php echo $raceId; ?>" target="_blank"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-external-link-alt mr-2"></i> View Race
                </a>
            </div>
            <?php endif; ?>
        </div>
    </header>
    
    <main class="p-6">
        <form id="race-form" enctype="multipart/form-data" class="max-w-5xl mx-auto">
            <input type="hidden" name="race_id" value="<?php echo $raceId; ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <span class="bg-emerald-100 text-emerald-600 p-2 rounded-lg mr-3">
                                <i class="fas fa-info-circle"></i>
                            </span>
                            Basic Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Race Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                       value="<?php echo htmlspecialchars($race['name'] ?? ''); ?>"
                                       placeholder="e.g., Kentucky Derby Stakes">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Track <span class="text-red-500">*</span>
                                </label>
                                <select name="track_id" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                    <option value="">-- Select Track --</option>
                                    <?php foreach($tracks as $track): ?>
                                    <option value="<?php echo $track['id']; ?>" 
                                            <?php echo ($race['track_id'] ?? '') == $track['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($track['name']); ?> 
                                        (<?php echo htmlspecialchars($track['country']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    <a href="<?php echo ADMIN_URL; ?>/tracks.php" class="text-emerald-600 hover:underline">
                                        + Add new track
                                    </a>
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Race Class</label>
                                <select name="race_class"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                    <option value="">-- Select Class --</option>
                                    <?php 
                                    $classes = [
                                        'Grade 1' => 'Grade 1 (Highest)',
                                        'Grade 2' => 'Grade 2',
                                        'Grade 3' => 'Grade 3',
                                        'Group 1' => 'Group 1 (Highest)',
                                        'Group 2' => 'Group 2',
                                        'Group 3' => 'Group 3',
                                        'Listed' => 'Listed Race',
                                        'Stakes' => 'Stakes',
                                        'Handicap' => 'Handicap',
                                        'Maiden' => 'Maiden',
                                        'Claiming' => 'Claiming',
                                        'Allowance' => 'Allowance',
                                        'Novice' => 'Novice',
                                        'Conditions' => 'Conditions'
                                    ];
                                    foreach($classes as $value => $label):
                                    ?>
                                    <option value="<?php echo $value; ?>" 
                                            <?php echo ($race['race_class'] ?? '') === $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Race Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="race_date" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                       value="<?php echo $race['race_date'] ?? date('Y-m-d'); ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Race Time <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="race_time" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                       value="<?php echo isset($race['race_time']) ? substr($race['race_time'], 0, 5) : '14:00'; ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Distance</label>
                                <input type="text" name="distance"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                       value="<?php echo htmlspecialchars($race['distance'] ?? ''); ?>"
                                       placeholder="e.g., 1 1/4 miles, 2000m">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Race Type</label>
                                <select name="race_type"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                    <option value="flat" <?php echo ($race['race_type'] ?? 'flat') === 'flat' ? 'selected' : ''; ?>>
                                        Flat Racing
                                    </option>
                                    <option value="hurdle" <?php echo ($race['race_type'] ?? '') === 'hurdle' ? 'selected' : ''; ?>>
                                        Hurdle
                                    </option>
                                    <option value="chase" <?php echo ($race['race_type'] ?? '') === 'chase' ? 'selected' : ''; ?>>
                                        Steeplechase
                                    </option>
                                    <option value="bumper" <?php echo ($race['race_type'] ?? '') === 'bumper' ? 'selected' : ''; ?>>
                                        National Hunt Flat (Bumper)
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Track Conditions -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <span class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3">
                                <i class="fas fa-cloud-sun"></i>
                            </span>
                            Track Conditions & Weather
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Going (Track Condition)</label>
                                <select name="going"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                    <option value="">-- Select Going --</option>
                                    <?php 
                                    $goings = [
                                        'Firm' => 'Firm',
                                        'Good to Firm' => 'Good to Firm',
                                        'Good' => 'Good',
                                        'Good to Soft' => 'Good to Soft',
                                        'Soft' => 'Soft',
                                        'Heavy' => 'Heavy',
                                        'Standard' => 'Standard (AW)',
                                        'Standard to Fast' => 'Standard to Fast (AW)',
                                        'Standard to Slow' => 'Standard to Slow (AW)',
                                        'Fast' => 'Fast',
                                        'Yielding' => 'Yielding'
                                    ];
                                    foreach($goings as $value => $label):
                                    ?>
                                    <option value="<?php echo $value; ?>" 
                                            <?php echo ($race['going'] ?? '') === $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Weather</label>
                                <select name="weather"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                    <option value="">-- Select Weather --</option>
                                    <?php 
                                    $weathers = ['Sunny', 'Partly Cloudy', 'Cloudy', 'Overcast', 'Light Rain', 'Rain', 'Heavy Rain', 'Showers', 'Windy', 'Foggy', 'Cold', 'Hot'];
                                    foreach($weathers as $weather):
                                    ?>
                                    <option value="<?php echo $weather; ?>" 
                                            <?php echo ($race['weather'] ?? '') === $weather ? 'selected' : ''; ?>>
                                        <?php echo $weather; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Temperature</label>
                                <input type="text" name="temperature"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                       value="<?php echo htmlspecialchars($race['temperature'] ?? ''); ?>"
                                       placeholder="e.g., 22°C or 72°F">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Wind</label>
                                <input type="text" name="wind"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                       value="<?php echo htmlspecialchars($race['wind'] ?? ''); ?>"
                                       placeholder="e.g., 8 mph SW">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Humidity</label>
                                <input type="text" name="humidity"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                       value="<?php echo htmlspecialchars($race['humidity'] ?? ''); ?>"
                                       placeholder="e.g., 45%">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rail Position</label>
                                <input type="text" name="rail_position"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                       value="<?php echo htmlspecialchars($race['rail_position'] ?? ''); ?>"
                                       placeholder="e.g., True, +2m, -3m">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <span class="bg-gray-100 text-gray-600 p-2 rounded-lg mr-3">
                                <i class="fas fa-file-alt"></i>
                            </span>
                            Description & Notes
                        </h2>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Race Description</label>
                            <textarea name="description" rows="5"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                      placeholder="Enter race description, history, and any relevant notes..."><?php echo htmlspecialchars($race['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Sidebar -->
                <div class="space-y-6">
                    <!-- Status & Visibility -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Status</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Race Status</label>
                                <select name="status"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                    <option value="scheduled" <?php echo ($race['status'] ?? 'scheduled') === 'scheduled' ? 'selected' : ''; ?>>
                                        📅 Scheduled
                                    </option>
                                    <option value="live" <?php echo ($race['status'] ?? '') === 'live' ? 'selected' : ''; ?>>
                                        🔴 Live
                                    </option>
                                    <option value="finished" <?php echo ($race['status'] ?? '') === 'finished' ? 'selected' : ''; ?>>
                                        ✅ Finished
                                    </option>
                                    <option value="cancelled" <?php echo ($race['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>
                                        ❌ Cancelled
                                    </option>
                                    <option value="postponed" <?php echo ($race['status'] ?? '') === 'postponed' ? 'selected' : ''; ?>>
                                        ⏸️ Postponed
                                    </option>
                                </select>
                            </div>
                            
                            <div class="border-t pt-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_featured" value="1" 
                                           class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 mr-3"
                                           <?php echo ($race['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                    <span>
                                        <span class="text-sm font-medium text-gray-700">Featured Race</span>
                                        <span class="block text-xs text-gray-500">Show on homepage</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prize Money -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                            Prize Money
                        </h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                                    <input type="number" name="prize_money" step="0.01" min="0"
                                           class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                           value="<?php echo $race['prize_money'] ?? 0; ?>"
                                           placeholder="500000">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                                <select name="currency"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                    <option value="USD" <?php echo ($race['currency'] ?? 'USD') === 'USD' ? 'selected' : ''; ?>>
                                        USD - US Dollar ($)
                                    </option>
                                    <option value="GBP" <?php echo ($race['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>
                                        GBP - British Pound (£)
                                    </option>
                                    <option value="EUR" <?php echo ($race['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>
                                        EUR - Euro (€)
                                    </option>
                                    <option value="AUD" <?php echo ($race['currency'] ?? '') === 'AUD' ? 'selected' : ''; ?>>
                                        AUD - Australian Dollar (A$)
                                    </option>
                                    <option value="AED" <?php echo ($race['currency'] ?? '') === 'AED' ? 'selected' : ''; ?>>
                                        AED - UAE Dirham
                                    </option>
                                    <option value="JPY" <?php echo ($race['currency'] ?? '') === 'JPY' ? 'selected' : ''; ?>>
                                        JPY - Japanese Yen (¥)
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Race Image -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Race Image</h2>
                        
                        <div class="space-y-4">
                            <?php if (!empty($race['image'])): ?>
                            <div class="relative" id="current-image">
                                <img src="<?php echo UPLOADS_URL . '/' . $race['image']; ?>" 
                                     alt="Race Image" 
                                     class="w-full h-40 object-cover rounded-lg">
                                <button type="button" onclick="removeImage()"
                                        class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                            <input type="hidden" name="remove_image" id="remove_image" value="0">
                            <?php endif; ?>
                            
                            <div id="image-upload" class="<?php echo !empty($race['image']) ? 'hidden' : ''; ?>">
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
                                <img id="preview-img" src="" alt="Preview" class="w-full h-40 object-cover rounded-lg">
                                <button type="button" onclick="clearPreview()" 
                                        class="mt-2 text-sm text-red-600 hover:text-red-700">
                                    <i class="fas fa-times mr-1"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Video URL -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-video text-red-500 mr-2"></i>
                            Video
                        </h2>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Video URL</label>
                            <input type="url" name="video_url"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                   value="<?php echo htmlspecialchars($race['video_url'] ?? ''); ?>"
                                   placeholder="https://youtube.com/watch?v=...">
                            <p class="text-xs text-gray-500 mt-1">YouTube or Vimeo URL</p>
                        </div>
                    </div>
                    
                    <!-- Quick Stats (Edit Mode Only) -->
                    <?php if ($isEdit): ?>
                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-sm p-6 text-white">
                        <h2 class="text-lg font-bold mb-4">Quick Stats</h2>
                        
                        <?php
                        $stmt = $db->prepare("SELECT COUNT(*) as count FROM race_entries WHERE race_id = ? AND is_non_runner = 0");
                        $stmt->execute([$raceId]);
                        $entryCount = $stmt->fetch()['count'];
                        ?>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span>Runners:</span>
                                <span class="font-bold"><?php echo $entryCount; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Created:</span>
                                <span class="font-bold"><?php echo formatDate($race['created_at']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Updated:</span>
                                <span class="font-bold"><?php echo timeAgo($race['updated_at']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <a href="<?php echo ADMIN_URL; ?>/races.php" 
                       class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg transition text-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                    
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <button type="submit" name="action" value="save_and_new"
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">
                            <i class="fas fa-plus mr-2"></i> Save & Add New
                        </button>
                        
                        <button type="submit" name="action" value="save_and_entries"
                                class="w-full sm:w-auto bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition">
                            <i class="fas fa-horse mr-2"></i> Save & Add Entries
                        </button>
                        
                        <button type="submit" name="action" value="save"
                                class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg transition">
                            <i class="fas fa-save mr-2"></i> 
                            <?php echo $isEdit ? 'Update Race' : 'Create Race'; ?>
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
document.getElementById('race-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const action = e.submitter.value;
    formData.append('submit_action', action);
    
    const submitBtn = e.submitter;
    const originalHTML = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
    
    fetch('<?php echo ADMIN_URL; ?>/ajax/save-race.php', {
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
                switch(action) {
                    case 'save_and_new':
                        window.location.href = '<?php echo ADMIN_URL; ?>/add-race.php';
                        break;
                    case 'save_and_entries':
                        window.location.href = '<?php echo ADMIN_URL; ?>/race-entries.php?race_id=' + data.race_id;
                        break;
                    default:
                        window.location.href = '<?php echo ADMIN_URL; ?>/races.php';
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
            text: 'An unexpected error occurred. Please try again.'
        });
        console.error('Error:', error);
    });
});
</script>

<?php require_once 'components/footer.php'; ?>