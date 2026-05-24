<?php
ob_start();
require_once '../includes/config.php';
require_once '../includes/header.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$db = db();

$users    = $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$messages = $db->query("SELECT COUNT(*) as c FROM messages")->fetch_assoc()['c'];
$docs     = $db->query("SELECT COUNT(*) as c FROM documents")->fetch_assoc()['c'];
$unread   = $db->query("SELECT COUNT(*) as c FROM messages WHERE receiver_id=0 AND is_read=0")->fetch_assoc()['c'];

// Fetch messages sent to admin (receiver_id=0)
$adminMsgs = $db->query("SELECT m.*, u.full_name as sender_name 
                          FROM messages m 
                          JOIN users u ON m.sender_id = u.id 
                          WHERE m.receiver_id = 0 
                          ORDER BY m.created_at DESC");
?>

<div class="page-header">
    <h1>// Admin Dashboard</h1>
    <p>Xoş gəldiniz, <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong></p>
</div>

<!-- Stats -->
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem;">
    <?php
    $stats = [
        ['İstifadəçilər', $users, '--accent'],
        ['Mesajlar', $messages, '--warning'],
        ['Sənədlər', $docs, '--success'],
        ['Oxunmamış', $unread, '--danger'],
    ];
    foreach ($stats as [$label, $val, $color]):
    ?>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem; font-family:var(--font-mono); color:var(<?= $color ?>);"><?= $val ?></div>
        <div style="font-size:0.75rem; color:var(--text2); margin-top:0.25rem;"><?= $label ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- User list -->
<div class="card" style="margin-bottom:1rem;">
    <div class="card-title">İstifadəçilər</div>
    <?php $userList = $db->query("SELECT id, username, email, full_name, created_at FROM users ORDER BY id"); ?>
    <table>
        <tr><th>ID</th><th>Ad</th><th>Email</th><th>Qeydiyyat</th></tr>
        <?php while ($u = $userList->fetch_assoc()): ?>
        <tr>
            <td style="font-family:var(--font-mono);"><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['full_name']) ?></td>
            <td style="font-family:var(--font-mono);"><?= htmlspecialchars($u['email']) ?></td>
            <td style="font-family:var(--font-mono); font-size:0.8rem;"><?= $u['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Messages to admin - Chain 2: XSS triggers here -->
<div class="card">
    <div class="card-title">Gələn mesajlar</div>
    <?php if ($adminMsgs && $adminMsgs->num_rows > 0): ?>
        <?php while ($m = $adminMsgs->fetch_assoc()): ?>
        <div style="padding:0.75rem 0; border-bottom:1px solid var(--border);">
            <div style="font-weight:600; margin-bottom:0.2rem;"><?= $m['subject'] ?></div>
            <div style="font-size:0.75rem; color:var(--text2); font-family:var(--font-mono); margin-bottom:0.5rem;">
                <?= htmlspecialchars($m['sender_name']) ?> · <?= $m['created_at'] ?>
            </div>
            <!-- Chain 2: body NOT escaped - XSS fires when admin reads message -->
            <div style="font-size:0.875rem; color:var(--text2);"><?= $m['body'] ?></div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="color:var(--text2); font-size:0.875rem;">Mesaj yoxdur.</p>
    <?php endif; ?>
</div>

<div style="margin-top:1rem;">
    <a href="/admin/logout.php" class="btn btn-danger">Çıxış</a>
</div>

<?php require_once '../includes/footer.php'; ?>
