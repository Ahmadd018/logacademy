<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$db = db();
$results = [];
$query = '';

if (isset($_GET['q']) && $_GET['q'] !== '') {
    $query = $_GET['q'];
    // Chain 1: intentionally vulnerable - no prepared statement
    // Inject: ' UNION SELECT table_name,null,null,null FROM information_schema.tables WHERE table_name LIKE 'admin%'-- -
    $sql = "SELECT id,title,content,category FROM search_index WHERE title='$query'";
    $res = $db->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $results[] = $row;
        }
    }
}
?>

<div class="page-header">
    <h1>// Axtarış</h1>
    <p>Portal məzmununda axtarış edin</p>
</div>

<div class="card">
    <form method="GET" style="display:flex; gap:0.5rem;">
        <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" 
               placeholder="Axtarış..." style="flex:1;">
        <button type="submit" class="btn btn-primary">Axtar</button>
    </form>
</div>

<?php if ($query): ?>
<div class="card">
    <div class="card-title">Nəticələr — "<?= htmlspecialchars($query) ?>"</div>
    <?php if (empty($results)): ?>
        <p style="color:var(--text2); font-size:0.875rem;">Heç bir nəticə tapılmadı.</p>
    <?php else: ?>
        <?php foreach ($results as $row): ?>
        <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
            <div style="font-weight:600; margin-bottom:0.25rem;"><?= $row['title'] ?></div>
            <div style="font-size:0.85rem; color:var(--text2);"><?= substr((string)($row['content'] ?? ''), 0, 120) ?>...</div>
            <div style="font-size:0.75rem; color:var(--accent); font-family:var(--font-mono); margin-top:0.25rem;">[<?= $row['category'] ?>]</div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
