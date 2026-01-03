<?php
require_once __DIR__ . '/includes/config.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}
$users = $db->query('SELECT id, email, credit FROM users ORDER BY id DESC')->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin - User Credits</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<?php include __DIR__ . '/components/header.php'; ?>
<div class="admin-container">
    <h1>User Credits</h1>
    <table class="admin-table">
        <thead><tr><th>ID</th><th>Email</th><th>Credit</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td id="credit-<?php echo $u['id']; ?>"><?php echo number_format($u['credit'],2); ?></td>
                <td>
                    <input type="number" id="amount-<?php echo $u['id']; ?>" step="0.01" placeholder="Amount" />
                    <button onclick="updateCredit(<?php echo $u['id']; ?>, 'add')">Add</button>
                    <button onclick="updateCredit(<?php echo $u['id']; ?>, 'subtract')">Subtract</button>
                    <button onclick="showAudit(<?php echo $u['id']; ?>)">Audit</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="auditModal" style="display:none; position:fixed; left:10%; top:10%; width:80%; height:70%; background:#fff; border:1px solid #ccc; overflow:auto; padding:1rem;">
    <button onclick="closeAudit()">Close</button>
    <h2>Audit Logs</h2>
    <div id="auditContent">Loading...</div>
</div>

<script>
function getCsrf() {
    // read from cookie or embedded meta, server may expose via endpoint; simple approach: AJAX fetch token
    return fetch('/admin/ajax/get-csrf.php').then(r=>r.json()).then(j=>j.token);
}
async function updateCredit(userId, action) {
    const amount = parseFloat(document.getElementById('amount-' + userId).value || 0);
    if (!amount) return alert('Enter amount');
    const token = await getCsrf();
    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('amount', amount);
    fd.append('action', action);
    fd.append('csrf_token', token);
    const res = await fetch('/admin/ajax/update-credit.php', {method:'POST', body: fd});
    const json = await res.json();
    if (json.success) {
        // refresh credit
        location.reload();
    } else {
        alert(json.error || 'Failed');
    }
}
async function showAudit(userId) {
    const token = await getCsrf();
    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('csrf_token', token);
    const res = await fetch('/admin/ajax/get-audit-logs.php', {method:'POST', body: fd});
    const json = await res.json();
    if (!json.success) {
        alert(json.error || 'Failed to fetch audit');
        return;
    }
    let html = '<table style="width:100%"><tr><th>ID</th><th>Action</th><th>Bet</th><th>Amount</th><th>Before</th><th>After</th><th>Details</th><th>When</th></tr>';
    json.logs.forEach(l=>{
        html += `<tr><td>${l.id}</td><td>${l.action}</td><td>${l.bet_id}</td><td>${l.amount}</td><td>${l.before_credit}</td><td>${l.after_credit}</td><td>${l.details}</td><td>${l.created_at}</td></tr>`;
    });
    html += '</table>';
    document.getElementById('auditContent').innerHTML = html;
    document.getElementById('auditModal').style.display = 'block';
}
function closeAudit(){document.getElementById('auditModal').style.display='none';}
</script>
</body>
</html>
