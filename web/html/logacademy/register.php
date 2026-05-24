<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = $_POST['username']  ?? '';
    $email     = $_POST['email']     ?? '';
    $password  = $_POST['password']  ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $phone     = $_POST['phone']     ?? '';

    $db = db();
    $check = $db->query("SELECT id FROM users WHERE username='$username'");
    if ($check && $check->num_rows > 0) {
        $error = 'Bu istifadəçi adı artıq mövcuddur.';
    } else {
        $hashed = md5($password);
        $db->query("INSERT INTO users (username, email, password, full_name, phone) 
                    VALUES ('$username','$email','$hashed','$full_name','$phone')");
        $success = 'Qeydiyyat uğurlu oldu! <a href="/login.php">Daxil olun</a>';
    }
}
?>

<div class="page-header">
    <h1>// Qeydiyyat</h1>
    <p>Yeni hesab yaradın</p>
</div>

<div class="card" style="max-width:480px;">
    <div class="card-title">Yeni hesab</div>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Ad Soyad</label>
            <input type="text" name="full_name" required>
        </div>
        <div class="form-group">
            <label>İstifadəçi adı</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Telefon</label>
            <input type="text" name="phone">
        </div>
        <div class="form-group">
            <label>Şifrə</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Qeydiyyatdan keç</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
