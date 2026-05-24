<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/header.php';

if (!isset($_SESSION['pending_otp_email'])) {
    header('Location: /login.php');
    exit;
}

$error = '';
$email = $_SESSION['pending_otp_email'];
$db = db();

// Resend OTP - no rate limit (Chain 6 - email pump)
if (isset($_GET['resend'])) {
    $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    // Old OTPs intentionally NOT invalidated (Chain 6)
    $db->query("INSERT INTO otp_codes (email, otp) VALUES ('$email', '$otp')");
    sendOTPEmail($email, $otp);
    $success = 'Yeni kod göndərildi.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'] ?? '';

    // No rate limiting (Chain 6)
    // Old OTPs also valid (Chain 6)
    $result = $db->query("SELECT * FROM otp_codes WHERE email='$email' AND otp='$otp'");

    if ($result && $result->num_rows > 0) {
        unset($_SESSION['pending_otp_email']);
        $_SESSION['verified'] = true;
        header('Location: /index.php');
        exit;
    } else {
        $error = 'Kod yanlışdır. Yenidən cəhd edin.';
    }
}
?>

<div class="page-header">
    <h1>// Təsdiq</h1>
    <p>Email-ə göndərilən kodu daxil edin</p>
</div>

<div class="card" style="max-width:360px;">
    <div class="card-title">OTP Doğrulama</div>
    <div class="alert alert-info">
        Kod göndərildi: <strong><?= htmlspecialchars($email) ?></strong>
    </div>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>4 rəqəmli kod</label>
            <input type="text" name="otp" maxlength="4" placeholder="0000" 
                   style="font-size:1.5rem; text-align:center; letter-spacing:0.5rem;" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Təsdiqlə</button>
    </form>
    <div style="margin-top:1rem; text-align:center;">
        <a href="/verify.php?resend=1" style="font-size:0.8rem; color:var(--text2);">Kodu yenidən göndər</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
