<?php
require_once '../includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
unset($_SESSION['admin_id'], $_SESSION['admin_username']);
header('Location: /admin/login.php');
exit;
