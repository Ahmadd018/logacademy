<?php
ob_start();
require_once '../includes/config.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$db  = db();
$uid = $_SESSION['user_id'];
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file     = $_FILES['document'];
    $origName = $file['name'];
    $tmpPath  = $file['tmp_name'];

    // Chain 4: no extension or MIME validation at all
    $saveName = $origName; // keep original name including .php
    $savePath = '/var/www/html/logacademy/uploads/' . $saveName;

    if (move_uploaded_file($tmpPath, $savePath)) {
        $db->query("INSERT INTO documents (user_id, filename, original_name) 
                    VALUES ($uid, '$saveName', '$origName')");
        $msg = "Fayl uğurla yükləndi: <a href='/uploads/$saveName' style='color:var(--accent2)'>/uploads/$saveName</a>";
    } else {
        $error = 'Fayl yüklənmədi.';
    }
}

// List own documents
$docs = $db->query("SELECT * FROM documents WHERE user_id=$uid ORDER BY uploaded_at DESC");
?>

<div class="page-header">
    <h1>// Sənədlər</h1>
    <p>Şəxsi sənədlərinizi yükləyin</p>
</div>

<div class="grid-2" style="align-items:start;">
    <div class="card">
        <div class="card-title">Fayl yüklə</div>
        <?php if ($msg): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Fayl seçin</label>
                <input type="file" name="document" 
                       style="background:var(--bg3); border:1px solid var(--border); 
                              padding:0.5rem; border-radius:4px; width:100%; color:var(--text);">
            </div>
            <div style="font-size:0.75rem; color:var(--text2); margin-bottom:0.75rem; font-family:var(--font-mono);">
                Dəstəklənən formatlar: PDF, DOC, DOCX, JPG, PNG
            </div>
            <button type="submit" class="btn btn-primary">Yüklə</button>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Yüklənmiş sənədlər</div>
        <?php if ($docs && $docs->num_rows > 0): ?>
            <?php while ($doc = $docs->fetch_assoc()): ?>
            <div style="display:flex; justify-content:space-between; align-items:center;
                        padding:0.5rem 0; border-bottom:1px solid var(--border);">
                <div>
                    <div style="font-size:0.875rem;"><?= htmlspecialchars($doc['original_name']) ?></div>
                    <div style="font-size:0.75rem; color:var(--text2); font-family:var(--font-mono);"><?= $doc['uploaded_at'] ?></div>
                </div>
                <a href="/uploads/<?= urlencode($doc['filename']) ?>" 
                   class="btn btn-outline" style="font-size:0.75rem; padding:0.3rem 0.7rem;">Aç</a>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:var(--text2); font-size:0.875rem;">Hələ fayl yüklənməyib.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
