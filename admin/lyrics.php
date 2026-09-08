<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$where = ["1=1"];
$params = [];
if ($search) { $where[] = "(l.title LIKE ? OR l.slug LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($statusFilter && in_array($statusFilter, ['published','draft'])) { $where[] = "l.status = ?"; $params[] = $statusFilter; }
$whereClause = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM lyrics l WHERE $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$sql = "SELECT l.*, c.name as category_name, a.name as artist_name, lang.name as language_name 
        FROM lyrics l LEFT JOIN categories c ON l.category_id = c.id LEFT JOIN artists a ON l.artist_id = a.id LEFT JOIN languages lang ON l.language_id = lang.id 
        WHERE $whereClause ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage; $params[] = $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lyrics = $stmt->fetchAll();

$pageTitle = 'Manage Lyrics - Admin';
$adminPage = 'lyrics';
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
<div class="admin-header">
<h1>Manage Lyrics</h1>
<a href="<?php echo e(SITE_URL); ?>/admin/add-lyrics.php" class="btn btn-primary">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
Add New Lyric
</a>
</div>
<div class="admin-content">
<?php echo flashMessage(); ?>
<div class="admin-toolbar">
<form method="GET" class="admin-search-form">
<input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Search lyrics...">
<select name="status" onchange="this.form.submit()">
<option value="">All Status</option>
<option value="published" <?php echo $statusFilter==='published'?'selected':''; ?>>Published</option>
<option value="draft" <?php echo $statusFilter==='draft'?'selected':''; ?>>Draft</option>
</select>
<button type="submit" class="btn btn-secondary">Filter</button>
</form>
</div>
<div class="admin-table-wrapper">
<table class="admin-table">
<thead>
<tr><th>ID</th><th>Title</th><th>Artist</th><th>Category</th><th>Language</th><th>Views</th><th>Status</th><th>Featured</th><th>Actions</th></tr>
</thead>
<tbody>
<?php foreach ($lyrics as $lyric): ?>
<tr>
<td><?php echo $lyric['id']; ?></td>
<td><a href="<?php echo e(SITE_URL); ?>/lyrics/<?php echo e($lyric['slug']); ?>" target="_blank"><?php echo e(truncate($lyric['title'], 35)); ?></a></td>
<td><?php echo e($lyric['artist_name'] ?? '-'); ?></td>
<td><?php echo e($lyric['category_name'] ?? '-'); ?></td>
<td><?php echo e($lyric['language_name'] ?? '-'); ?></td>
<td><?php echo number_format($lyric['views']); ?></td>
<td><span class="badge badge-<?php echo $lyric['status']; ?>"><?php echo ucfirst($lyric['status']); ?></span></td>
<td><?php echo $lyric['is_featured'] ? '<span class="badge badge-featured">Yes</span>' : 'No'; ?></td>
<td class="actions">
<a href="edit-lyrics.php?id=<?php echo $lyric['id']; ?>" class="action-btn action-edit" title="Edit">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
</a>
<a href="<?php echo e(SITE_URL); ?>/lyrics/<?php echo e($lyric['slug']); ?>" target="_blank" class="action-btn action-view" title="View">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php if ($totalPages > 1): 
    $baseUrl = SITE_URL . '/admin/lyrics.php' . ($search || $statusFilter ? '?' : '');
    $parts = [];
    if ($search) $parts[] = 'search=' . urlencode($search);
    if ($statusFilter) $parts[] = 'status=' . $statusFilter;
    $baseUrl .= implode('&', $parts);
    echo generatePagination($page, $totalPages, $baseUrl);
endif; ?>
</div>
</main>
</body>
</html>
