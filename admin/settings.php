<?php
define('ADMIN_ACCESS', true);
$pageTitle = 'Site Settings';
require_once dirname(__DIR__) . '/includes/config.php';

$settings = [];
$stmt = $db->query("SELECT * FROM site_settings ORDER BY setting_key");
while ($row = $stmt->fetch()) $settings[$row['setting_key']] = $row['setting_value'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $value) {
        $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?")->execute([sanitize($value), $key]);
    }
    setFlashMessage('success', 'Settings saved!');
    redirect(ADMIN_URL . '/settings.php');
}

require_once 'components/header.php';
require_once 'components/sidebar.php';
?>

<div class="flex-1 overflow-x-hidden overflow-y-auto">
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Site Settings</h1>
                <p class="text-sm text-gray-500">Configure your website</p>
            </div>
        </div>
    </header>
    
    <main class="p-6">
        <form method="POST" class="max-w-4xl space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-cog text-emerald-600 mr-2"></i>General</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
                        <input type="text" name="settings[site_name]" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" class="w-full px-4 py-3 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tagline</label>
                        <input type="text" name="settings[site_tagline]" value="<?php echo htmlspecialchars($settings['site_tagline'] ?? ''); ?>" class="w-full px-4 py-3 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                        <input type="email" name="settings[contact_email]" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" class="w-full px-4 py-3 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                        <input type="text" name="settings[contact_phone]" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" class="w-full px-4 py-3 border rounded-lg">
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-desktop text-blue-600 mr-2"></i>Display</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                        <select name="settings[timezone]" class="w-full px-4 py-3 border rounded-lg">
                            <option value="UTC" <?php echo ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                            <option value="America/New_York" <?php echo ($settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : ''; ?>>Eastern</option>
                            <option value="Europe/London" <?php echo ($settings['timezone'] ?? '') === 'Europe/London' ? 'selected' : ''; ?>>London</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                        <select name="settings[currency]" class="w-full px-4 py-3 border rounded-lg">
                            <option value="USD" <?php echo ($settings['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                            <option value="GBP" <?php echo ($settings['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                            <option value="EUR" <?php echo ($settings['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Odds Format</label>
                        <select name="settings[odds_format]" class="w-full px-4 py-3 border rounded-lg">
                            <option value="fractional" <?php echo ($settings['odds_format'] ?? '') === 'fractional' ? 'selected' : ''; ?>>Fractional (5/1)</option>
                            <option value="decimal" <?php echo ($settings['odds_format'] ?? '') === 'decimal' ? 'selected' : ''; ?>>Decimal (6.00)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-lg font-bold">
                    <i class="fas fa-save mr-2"></i>Save Settings
                </button>
            </div>
        </form>
    </main>
</div>

<?php require_once 'components/footer.php'; ?>