<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => false,  // Chain 2 - intentionally no httponly
        'cookie_samesite' => 'Lax',
    ]);
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogAcademy — Tələbə Portalı</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans+Arabic:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- jQuery UI 1.9.2 - intentionally vulnerable (Chain 2 assist) -->
    <script src="https://code.jquery.com/jquery-1.8.3.min.js"></script>
    <script src="https://code.jquery.com/ui/1.9.2/jquery-ui.min.js"></script>
</head>
<body>
<nav class="navbar">
    <a class="brand" href="/">LogAcademy</a>
    <div class="nav-links">
        <a href="/search.php">Axtarış</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/profile/view.php?token=<?= base64_encode($_SESSION['user_id'] . ':' . SECRET_KEY) ?>">Profil</a>
            <a href="/messages.php">Mesajlar</a>
            <a href="/documents/upload.php">Sənədlər</a>
            <a href="/logout.php">Çıxış</a>
        <?php else: ?>
            <a href="/login.php">Giriş</a>
            <a href="/register.php">Qeydiyyat</a>
        <?php endif; ?>
    </div>
</nav>
<main class="container">
