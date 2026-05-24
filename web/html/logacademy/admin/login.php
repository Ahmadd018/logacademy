<?php
ob_start();
require_once '../includes/config.php';
require_once '../includes/header.php';

$error = '';

if (isset($_SESSION['admin_id'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $hashed   = md5($password);

    $db     = db();
    $result = $db->query("SELECT * FROM admin_x7k9mq WHERE username='$username' AND password='$hashed'");

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        $error = 'Yanlış istifadəçi adı və ya şifrə.';
    }
}
?>

<div class="page-header">
    <h1>// Admin Girişi</h1>
</div>

<div class="card" style="max-width:380px;">
    <div class="card-title">Administrator paneli</div>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>İstifadəçi adı</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Şifrə</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Daxil ol</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
