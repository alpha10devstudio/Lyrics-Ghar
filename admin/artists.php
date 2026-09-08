<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$errors = [];
$editId = intval($_GET['edit'] ?? 0);
$editArtist = $editId ? $pdo->prepare("SELECT * FROM artists WHERE id = ?")->execute([$editId]) ? $pdo->prepare("SELECT * FROM artists WHERE id = ?")->fetch() : null : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrfToken();
    $name = trim($_POST['name'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? $name);
    $biography = $_POST['biography'] ?? '';
    $status = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';

    if (empty($name)) $errors[] = 'Name is required.';

    if (empty($errors)) {
        if ($editId) {
            $slug = ensureUniqueSlug($pdo, 'artists', $slug, $editId);
            $pdo->prepare("UPDATE artists SET name=?, slug=?, biography=?, status=? WHERE id=?")
                ->execute([$name, $slug, $biography, $status, $editId]);
            setFlash('success', 'Artist updated.');
        } else {
            $slug = ensureUniqueSlug($pdo, 'artists', $slug);
            $pdo->prepare("INSERT INTO artists (name, slug, biography, status) VALUES (?, ?, ?, ?)")
                ->execute([$name, $slug, $biography, $status]);
            setFlash('success', 'Artist added.');
        }
        redirect(SITE_URL . '/admin/artists.php');
    }
}

if (isset($_GET['delete'])) {
    validateCsrfToken();
    $delId = intval($_GET['delete']);
    $pdo->prepare("UPDATE lyrics SET artist_id = NULL WHERE artist_id = ?")->execute([$delId]);
    $pdo->prepare("DELETE FROM artists WHERE id = ?")->execute([$delId]);
    setFlash('success', 'Artist deleted.');
    redirect(SITE_URL . '/admin/artists.php');
}

$artists = $pdo->query("SELECT a.*, (SELECT COUNT(*) FROM lyrics WHERE artist_id = a.id) as lyrics_count FROM artists a ORDER BY a.name")->fetchAll();
$pageTitle = 'Artists - Admin';
$adminPage = 'artists';
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($pageTitle); ?></title>
<link rel="stylesheet" href="<?php echo e(ASSETS_URL); ?>/css/admin.css?v=1">
</head>
<body class="admin-page">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="admin-main">
<div class="admin-header"><h1>Artists</h1></div>
<div class="admin-content">
<?php echo flashMessage(); ?>
<div class="admin-row">
<div class="admin-card" style="flex:1;">
<div class="admin-card-header"><h2><?php echo $editId ? 'Edit Artist' : 'Add Artist'; ?></h2></div>
<?php if (!empty($errors)): ?><div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="POST" class="admin-form">
<?php echo csrfField(); ?>
<div class="form-group"><label>Name *</label><input type="text" name="name" value="<?php echo e($_POST['name'] ?? $editArtist['name'] ?? ''); ?>" required></div>
<div class="form-group"><label>Slug</label><input type="text" name="slug" value="<?php echo e($_POST['slug'] ?? $editArtist['slug'] ?? ''); ?>" placeholder="Auto-generated"></div>
<div class="form-group"><label>Biography</label><textarea name="biography" rows="4"><?php echo e($_POST['biography'] ?? $editArtist['biography'] ?? ''); ?></textarea></div>
<div class="form-group"><label>Status</label><select name="status"><option value="active" <?php echo ($_POST['status']??$editArtist['status']??'')=='active'?'selected':''; ?>>Active</option><option value="inactive" <?php echo ($_POST['status']??$editArtist['status']??'')=='inactive'?'selected':''; ?>>Inactive</option></select></div>
<div class="form-actions"><button type="submit" class="btn btn-primary"><?php echo $editId ? 'Update' : 'Add'; ?></button><?php if ($editId): ?><a href="artists.php" class="btn btn-secondary">Cancel</a><?php endif; ?></div>
</form>
</div>
<div class="admin-card" style="flex:2;">
<div class="admin-card-header"><h2>All Artists</h2></div>
<div class="admin-table-wrapper">
<table class="admin-table">
<thead><tr><th>Name</th><th>Slug</th><th>Lyrics</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($artists as $artist): ?>
<tr>
<td><?php echo e($artist['name']); ?></td>
<td><?php echo e($artist['slug']); ?></td>
<td><?php echo $artist['lyrics_count']; ?></td>
<td><span class="badge badge-<?php echo $artist['status']; ?>"><?php echo ucfirst($artist['status']); ?></span></td>
<td class="actions">
<a href="?edit=<?php echo $artist['id']; ?>" class="action-btn action-edit" title="Edit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
<a href="?delete=<?php echo $artist['id']; ?>" class="action-btn action-delete" title="Delete" onclick="return confirm('Delete this artist?')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</main>
</body>
</html>
