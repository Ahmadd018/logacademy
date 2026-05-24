<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$db  = db();
$uid = $_SESSION['user_id'];
$msg = '';

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to      = $_POST['to']      ?? '';
    $subject = $_POST['subject'] ?? '';
    $body    = $_POST['body']    ?? '';

    // Chain 2: no sanitization - stored XSS
    $db->query("INSERT INTO messages (sender_id, receiver_id, subject, body) 
                VALUES ($uid, $to, '$subject', '$body')");
    $msg = 'Mesaj göndərildi.';
}

// List messages
$inbox = $db->query("SELECT m.*, u.full_name as sender_name 
                     FROM messages m 
                     JOIN users u ON m.sender_id = u.id 
                     WHERE m.receiver_id = $uid 
                     ORDER BY m.created_at DESC");

$users = $db->query("SELECT id, full_name FROM users WHERE id != $uid");
?>

<div class="page-header">
    <h1>// Mesajlar</h1>
</div>

<div class="grid-2" style="align-items:start;">
    <!-- Inbox -->
    <div class="card">
        <div class="card-title">Gələn qutu</div>
        <?php if ($inbox && $inbox->num_rows > 0): ?>
            <?php while ($m = $inbox->fetch_assoc()): ?>
            <div class="message-item <?= $m['is_read'] ? '' : 'unread' ?>" 
                 onclick="toggleMsg(<?= $m['id'] ?>)">
                <div class="msg-subject"><?= $m['subject'] ?></div>
                <div class="msg-meta"><?= $m['sender_name'] ?> · <?= $m['created_at'] ?></div>
                <!-- Chain 2: body rendered without escaping -->
                <div id="msg-<?= $m['id'] ?>" style="display:none; margin-top:0.5rem; font-size:0.875rem; color:var(--text2);">
                    <?= $m['body'] ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:var(--text2); font-size:0.875rem;">Mesaj yoxdur.</p>
        <?php endif; ?>
    </div>

    <!-- Send -->
    <div class="card">
        <div class="card-title">Mesaj göndər</div>
        <?php if ($msg): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Alıcı</label>
                <select name="to">
                    <?php while ($u = $users->fetch_assoc()): ?>
                        <option value="<?= $u['id'] ?>"><?= $u['full_name'] ?></option>
                    <?php endwhile; ?>
                    <!-- Admin seçimi -->
                    <option value="0">[ Admin ]</option>
                </select>
            </div>
            <div class="form-group">
                <label>Mövzu</label>
                <input type="text" name="subject" required>
            </div>
            <div class="form-group">
                <label>Mətn</label>
                <textarea name="body" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Göndər</button>
        </form>
    </div>
</div>

<script>
function toggleMsg(id) {
    const el = document.getElementById('msg-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php require_once 'includes/footer.php'; ?>
