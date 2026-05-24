<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$db = db();
$token = $_GET['token'] ?? '';
$profile = null;
$error = '';

if ($token) {
    // Chain 3: decode token, no ownership check
    $decoded = base64_decode($token);
    $parts   = explode(':', $decoded);
    $user_id = intval($parts[0] ?? 0);

    // No check: does this token belong to logged-in user?
    $result = $db->query("SELECT id, username, email, full_name, phone, created_at FROM users WHERE id=$user_id");
    if ($result && $result->num_rows > 0) {
        $profile = $result->fetch_assoc();
    } else {
        $error = 'İstifadəçi tapılmadı.';
    }
} else {
    // Default: own profile
    $uid    = $_SESSION['user_id'];
    $result = $db->query("SELECT id, username, email, full_name, phone, created_at FROM users WHERE id=$uid");
    $profile = $result->fetch_assoc();
}
?>

<div class="page-header">
    <h1>// Profil</h1>
    <p>İstifadəçi məlumatları</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php elseif ($profile): ?>
<div class="card" style="max-width:520px;">
    <div class="card-title">İstifadəçi məlumatları</div>
    <table>
        <tr><th>Ad Soyad</th><td><?= htmlspecialchars($profile['full_name']) ?></td></tr>
        <tr><th>İstifadəçi adı</th><td><?= htmlspecialchars($profile['username']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($profile['email']) ?></td></tr>
        <tr><th>Telefon</th><td><?= htmlspecialchars($profile['phone']) ?></td></tr>
        <tr><th>Qeydiyyat tarixi</th><td><?= htmlspecialchars($profile['created_at']) ?></td></tr>
    </table>
</div>

<div class="card" style="max-width:520px; margin-top:1rem;">
    <div class="card-title">Token məlumatı</div>
    <div style="font-family:var(--font-mono); font-size:0.8rem; color:var(--text2); word-break:break-all;">
        <?php
        $myToken = base64_encode($profile['id'] . ':' . SECRET_KEY);
        echo "token: " . $myToken;
        ?>
    </div>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
