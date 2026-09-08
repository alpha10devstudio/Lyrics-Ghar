<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$errors = [];
$editId = intval($_GET['edit'] ?? 0);
$editCategory = $editId ? $pdo->prepare("SELECT * FROM categories WHERE id = ?")->execute([$editId]) ? $pdo->prepare("SELECT * FROM categories WHERE id = ?")->fetch() : null : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrfToken();
    $name = trim($_POST['name'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? $name);
    $description = $_POST['description'] ?? '';
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDesc = trim($_POST['meta_description'] ?? '');
    $sortOrder = intval($_POST['sort_order'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';

    if (empty($name)) $errors[] = 'Name is required.';

    if (empty($errors)) {
        if ($editId) {
            $slug = ensureUniqueSlug($pdo, 'categories', $slug, $editId);
            $pdo->prepare("UPDATE categories SET name=?, slug=?, description=?, meta_title=?, meta_description=?, sort_order=?, status=? WHERE id=?")
                ->execute([$name, $slug, $description, $metaTitle, $metaDesc, $sortOrder, $status, $editId]);
            setFlash('success', 'Category updated.');
        } else {
            $slug = ensureUniqueSlug($pdo, 'categories', $slug);
            $pdo->prepare("INSERT INTO categories (name, slug, description, meta_title, meta_description, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$name, $slug, $description, $metaTitle, $metaDesc, $sortOrder, $status]);
            setFlash('success', 'Category added.');
        }
        redirect(SITE_URL . '/admin/categories.php');
    }
}

if (isset($_GET['delete'])) {
    validateCsrfToken();
    $delId = intval($_GET['delete']);
    $pdo->prepare("UPDATE lyrics SET category_id = NULL WHERE category_id = ?")->execute([$delId]);
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$delId]);
    setFlash('success', 'Category deleted.');
    redirect(SITE_URL . '/admin/categories.php');
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order, name")->fetchAll();
$pageTitle = 'Categories - Admin';
$adminPage = 'categories';
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
<div class="admin-header"><h1>Categories</h1></div>
<div class="admin-content">
<?php echo flashMessage(); ?>
<div class="admin-row">
<div class="admin-card" style="flex:1;">
<div class="admin-card-header"><h2><?php echo $editId ? 'Edit Category' : 'Add Category'; ?></h2></div>
<?php if (!empty($errors)): ?><div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="POST" class="admin-form">
<?php echo csrfField(); ?>
<div class="form-group"><label>Name *</label><input type="text" name="name" value="<?php echo e($_POST['name'] ?? $editCategory['name'] ?? ''); ?>" required></div>
<div class="form-group"><label>Slug</label><input type="text" name="slug" value="<?php echo e($_POST['slug'] ?? $editCategory['slug'] ?? ''); ?>" placeholder="Auto-generated"></div>
<div class="form-group"><label>Description</label><textarea name="description" rows="3"><?php echo e($_POST['description'] ?? $editCategory['description'] ?? ''); ?></textarea></div>
<div class="form-row">
<div class="form-group"><label>Meta Title</label><input type="text" name="meta_title" value="<?php echo e($_POST['meta_title'] ?? $editCategory['meta_title'] ?? ''); ?>"></div>
<div class="form-group"><label>Meta Description</label><input type="text" name="meta_description" value="<?php echo e($_POST['meta_description'] ?? $editCategory['meta_description'] ?? ''); ?>"></div>
</div>
<div class="form-row">
<div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?php echo e($_POST['sort_order'] ?? $editCategory['sort_order'] ?? 0); ?>"></div>
<div class="form-group"><label>Status</label><select name="status"><option value="active" <?php echo ($_POST['status']??$editCategory['status']??'')=='active'?'selected':''; ?>>Active</option><option value="inactive" <?php echo ($_POST['status']??$editCategory['status']??'')=='inactive'?'selected':''; ?>>Inactive</option></select></div>
</div>
<div class="form-actions"><button type="submit" class="btn btn-primary"><?php echo $editId ? 'Update' : 'Add'; ?></button><?php if ($editId): ?><a href="categories.php" class="btn btn-secondary">Cancel</a><?php endif; ?></div>
</form>
</div>
<div class="admin-card" style="flex:2;">
<div class="admin-card-header"><h2>All Categories</h2></div>
<div class="admin-table-wrapper">
<table class="admin-table">
<thead><tr><th>Name</th><th>Slug</th><th>Status</th><th>Order</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($categories as $cat): ?>
<tr>
<td><?php echo e($cat['name']); ?></td>
<td><?php echo e($cat['slug']); ?></td>
<td><span class="badge badge-<?php echo $cat['status']; ?>"><?php echo ucfirst($cat['status']); ?></span></td>
<td><?php echo $cat['sort_order']; ?></td>
<td class="actions">
<a href="?edit=<?php echo $cat['id']; ?>" class="action-btn action-edit" title="Edit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
<a href="?delete=<?php echo $cat['id']; ?>" class="action-btn action-delete" title="Delete" onclick="return confirm('Delete this category?')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></a>
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
