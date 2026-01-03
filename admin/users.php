<?php
define('ADMIN_ACCESS', true);
$pageTitle = 'Manage Users';
require_once dirname(__DIR__) . '/includes/config.php';
require_once 'components/header.php';
require_once 'components/sidebar.php';

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="flex-1 overflow-x-hidden overflow-y-auto">
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manage Users</h1>
                <p class="text-sm text-gray-500">Total: <?php echo count($users); ?> users</p>
            </div>
            <button onclick="openAddUserModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Add User
            </button>
        </div>
    </header>
    
    <main class="p-6">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Last Login</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach($users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold mr-3">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="font-semibold"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
                                    <div class="text-sm text-gray-500">@<?php echo htmlspecialchars($user['username']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-500">
                            <?php echo $user['last_login'] ? timeAgo($user['last_login']) : 'Never'; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="editUser(<?php echo $user['id']; ?>)" class="text-blue-600 hover:text-blue-800 mr-2"><i class="fas fa-edit"></i></button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <button onclick="deleteItem('user', <?php echo $user['id']; ?>, '<?php echo addslashes($user['username']); ?>')" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once 'components/footer.php'; ?>