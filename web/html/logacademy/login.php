<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $db = db();
    // Chain 1 warm-up: no prepared statement, but login itself uses MD5
    $hashed = md5($password);
    $result = $db->query("SELECT * FROM users WHERE username='$username' AND password='$hashed'");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];

        // Send OTP
        $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Intentionally: old OTPs NOT deleted (Chain 6)
        $db->query("INSERT INTO otp_codes (email, otp) VALUES ('{$user['email']}', '$otp')");
        
        $_SESSION['pending_otp_email'] = $user['email'];
        
        sendOTPEmail($user['email'], $otp);
        
        header('Location: /verify.php');
        exit;
    } else {
        $error = 'İstifadəçi adı və ya şifrə yanlışdır.';
    }
}
?>

<div class="page-header">
    <h1>// Giriş</h1>
    <p>Portaldan istifadə etmək üçün daxil olun</p>
</div>

<div class="card" style="max-width:400px;">
    <div class="card-title">Hesaba daxil ol</div>
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
    <div style="margin-top:1rem; font-size:0.8rem; color:var(--text2); text-align:center;">
        Hesabınız yoxdur? <a href="/register.php" style="color:var(--accent2)">Qeydiyyat</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
