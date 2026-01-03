<?php
/**
 * Admin: API Settings & Configuration
 * Manage external API integrations
 */
define('ADMIN_ACCESS', true);
$pageTitle = 'API Settings';

require_once dirname(__DIR__) . '/includes/config.php';
require_once 'components/header.php';
require_once 'components/sidebar.php';

// Get all API configurations
$apis = $db->query("SELECT * FROM api_settings ORDER BY api_name ASC")->fetchAll();
?>

<!-- Main Content -->
<div class="flex-1 overflow-x-hidden overflow-y-auto">
    <!-- Top Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">API Settings</h1>
                <p class="text-sm text-gray-500">Configure external API integrations for live data</p>
            </div>
            <button onclick="testAllConnections()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-plug mr-2"></i> Test All Connections
            </button>
        </div>
    </header>
    
    <main class="p-6">
        <!-- API Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <?php foreach($apis as $api): ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- API Header -->
                <div class="bg-gradient-to-r from-gray-800 to-gray-700 text-white p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold"><?php echo htmlspecialchars($api['api_name']); ?></h3>
                            <p class="text-sm text-gray-300"><?php echo htmlspecialchars($api['base_url']); ?></p>
                        </div>
                        <div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       class="sr-only peer api-toggle" 
                                       data-api-id="<?php echo $api['id']; ?>"
                                       <?php echo $api['is_active'] ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- API Configuration Form -->
                <form class="p-6 space-y-4" onsubmit="saveApiSettings(event, <?php echo $api['id']; ?>)">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                        <div class="relative">
                            <input type="password" 
                                   name="api_key" 
                                   id="api_key_<?php echo $api['id']; ?>"
                                   value="<?php echo htmlspecialchars($api['api_key'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                                   placeholder="Enter your API key">
                            <button type="button" 
                                    onclick="togglePasswordVisibility('api_key_<?php echo $api['id']; ?>')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">API Secret (Optional)</label>
                        <input type="password" 
                               name="api_secret" 
                               value="<?php echo htmlspecialchars($api['api_secret'] ?? ''); ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                               placeholder="Enter API secret if required">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Base URL</label>
                        <input type="url" 
                               name="base_url" 
                               value="<?php echo htmlspecialchars($api['base_url'] ?? ''); ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                               placeholder="https://api.example.com/v1">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sync Interval (minutes)</label>
                        <input type="number" 
                               name="sync_interval" 
                               min="1" 
                               value="<?php echo $api['sync_interval'] ?? 60; ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                    </div>
                    
                    <?php if ($api['last_sync']): ?>
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-sync-alt mr-1"></i> 
                            Last synced: <?php echo timeAgo($api['last_sync']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex items-center justify-between pt-4 border-t">
                        <button type="button" 
                                onclick="testConnection(<?php echo $api['id']; ?>)" 
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">
                            <i class="fas fa-flask mr-2"></i> Test Connection
                        </button>
                        <button type="submit" 
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg transition">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Sync History -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-history text-purple-600 mr-2"></i>
                Recent Sync Activity
            </h2>
            <div class="space-y-3">
                <?php
                $syncHistory = $db->query("
                    SELECT api_name, last_sync 
                    FROM api_settings 
                    WHERE last_sync IS NOT NULL 
                    ORDER BY last_sync DESC 
                    LIMIT 10
                ")->fetchAll();
                
                if (empty($syncHistory)):
                ?>
                <p class="text-center text-gray-500 py-4">No sync history available</p>
                <?php else: ?>
                    <?php foreach($syncHistory as $sync): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="font-medium"><?php echo htmlspecialchars($sync['api_name']); ?></span>
                        </div>
                        <span class="text-sm text-gray-500"><?php echo timeAgo($sync['last_sync']); ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Guide -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 mt-6 text-white">
            <h2 class="text-lg font-bold mb-4">
                <i class="fas fa-book mr-2"></i> API Integration Guide
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <h4 class="font-bold mb-2">1. Get API Key</h4>
                    <p class="text-blue-100">Register at the API provider's website to obtain your API credentials.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-2">2. Configure</h4>
                    <p class="text-blue-100">Enter your API key and configure sync settings above.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-2">3. Test & Activate</h4>
                    <p class="text-blue-100">Test the connection and enable the API to start syncing data.</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Toggle password visibility
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = event.target;
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// API toggle
document.querySelectorAll('.api-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const apiId = this.dataset.apiId;
        const isActive = this.checked ? 1 : 0;
        
        fetch('<?php echo ADMIN_URL; ?>/ajax/toggle-api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({api_id: apiId, is_active: isActive})
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSuccess(isActive ? 'API enabled' : 'API disabled');
            } else {
                showError(data.error);
                this.checked = !this.checked;
            }
        });
    });
});

// Save API settings
function saveApiSettings(e, apiId) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('api_id', apiId);
    
    fetch('<?php echo ADMIN_URL; ?>/ajax/save-api-settings.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Settings Saved!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Error!', data.error, 'error');
        }
    });
}

// Test connection
function testConnection(apiId) {
    Swal.fire({
        title: 'Testing Connection...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('<?php echo ADMIN_URL; ?>/ajax/test-api-connection.php?api_id=' + apiId)
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Connection Successful!',
                html: `
                    <p>API is responding correctly</p>
                    <p class="text-sm text-gray-500 mt-2">Response time: ${data.response_time}ms</p>
                `
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Connection Failed',
                text: data.error
            });
        }
    });
}

// Test all connections
function testAllConnections() {
    Swal.fire({
        title: 'Testing All APIs...',
        text: 'This may take a moment',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('<?php echo ADMIN_URL; ?>/ajax/test-all-apis.php')
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            let html = '<div class="text-left">';
            data.results.forEach(result => {
                const icon = result.success ? 'fa-check-circle text-green-500' : 'fa-times-circle text-red-500';
                html += `<div class="flex items-center mb-2"><i class="fas ${icon} mr-2"></i> ${result.api_name}: ${result.message}</div>`;
            });
            html += '</div>';
            
            Swal.fire({
                icon: 'info',
                title: 'Connection Test Results',
                html: html,
                width: '600px'
            });
        } else {
            Swal.fire('Error!', data.error, 'error');
        }
    });
}
</script>

<?php require_once 'components/footer.php'; ?>