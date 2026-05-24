<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<div class="hero">
    <h1>// LogAcademy</h1>
    <p>Tələbə İdarəetmə Portalı — Sənədlər, müraciətlər və akademik xidmətlər.</p>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-title">Elanlar</div>
        <?php
        $db = db();
        // intentionally vulnerable - no prepared statement (Chain 1 warm-up)
        $result = $db->query("SELECT * FROM search_index ORDER BY id DESC LIMIT 3");
        while ($row = $result->fetch_assoc()):
        ?>
        <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
            <div style="font-size:0.9rem;"><?= $row['title'] ?></div>
            <div style="font-size:0.75rem; color:var(--text2); font-family:var(--font-mono);"><?= $row['category'] ?></div>
        </div>
        <?php endwhile; ?>
        <div style="margin-top:1rem;">
            <a href="/search.php" class="btn btn-outline" style="font-size:0.8rem;">Hamısına bax →</a>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Xidmətlər</div>
        <div style="display:flex; flex-direction:column; gap:0.5rem;">
            <a href="/documents/upload.php" class="btn btn-outline">📄 Sənəd Yüklə</a>
            <a href="/messages.php" class="btn btn-outline">✉ Mesajlar</a>
            <a href="/profile/view.php" class="btn btn-outline">👤 Profil</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
