<?php
require_once 'includes/config.php';

$url = $_GET['url'] ?? '';

if ($url) {
    // Chain 5: no validation of destination URL
    header("Location: $url");
    exit;
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>// Yönləndirmə</h1>
    <p>Bu səhifə xarici resurslara keçid üçün istifadə olunur</p>
</div>

<div class="alert alert-info">URL parametri göstərilməyib.</div>

<?php require_once 'includes/footer.php'; ?>
