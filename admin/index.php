<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$totalLyrics = $pdo->query("SELECT COUNT(*) FROM lyrics")->fetchColumn();
$totalArtists = $pdo->query("SELECT COUNT(*) FROM artists")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalViews = $pdo->query("SELECT SUM(views) FROM lyrics")->fetchColumn() ?: 0;
$publishedLyrics = $pdo->query("SELECT COUNT(*) FROM lyrics WHERE status = 'published'")->fetchColumn();
$draftLyrics = $pdo->query("SELECT COUNT(*) FROM lyrics WHERE status = 'draft'")->fetchColumn();

$recentLyrics = $pdo->query("SELECT l.*, c.name as category_name, a.name as artist_name FROM lyrics l LEFT JOIN categories c ON l.category_id = c.id LEFT JOIN artists a ON l.artist_id = a.id ORDER BY l.created_at DESC LIMIT 10")->fetchAll();

$pageTitle = 'Dashboard - Admin';
$adminPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($pageTitle); ?></title>
<link rel="stylesheet" href="<?php echo e(ASSETS_URL); ?>/css/admin.css?v=1">
</head>
<body class="admin-page">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="admin-main">
<div class="admin-header"><h1>Dashboard</h1></div>
<div class="admin-content">

<div class="stats-grid">
<div class="stat-card">
<div class="stat-icon" style="background:#e8f5ee;color:#0D5C3A;">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
</div>
<div class="stat-info"><h3><?php echo number_format($totalLyrics); ?></h3><p>Total Lyrics</p></div>
</div>
<div class="stat-card">
<div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
</div>
<div class="stat-info"><h3><?php echo number_format($totalViews); ?></h3><p>Total Views</p></div>
</div>
<div class="stat-card">
<div class="stat-icon" style="background:#fef3c7;color:#b45309;">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
</div>
<div class="stat-info"><h3><?php echo number_format($totalArtists); ?></h3><p>Artists</p></div>
</div>
<div class="stat-card">
<div class="stat-icon" style="background:#fce7f3;color:#be185d;">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
</div>
<div class="stat-info"><h3><?php echo number_format($totalCategories); ?></h3><p>Categories</p></div>
</div>
</div>

<div class="admin-row">
<div class="admin-card" style="flex:2;">
<div class="admin-card-header"><h2>Recent Lyrics</h2><a href="lyrics.php">View All</a></div>
<div class="admin-table-wrapper">
<table class="admin-table">
<thead><tr><th>Title</th><th>Artist</th><th>Category</th><th>Status</th><th>Date</th></tr></thead>
<tbody>
<?php foreach ($recentLyrics as $lyric): ?>
<tr>
<td><a href="edit-lyrics.php?id=<?php echo $lyric['id']; ?>"><?php echo e(truncate($lyric['title'], 40)); ?></a></td>
<td><?php echo e($lyric['artist_name'] ?? '-'); ?></td>
<td><?php echo e($lyric['category_name'] ?? '-'); ?></td>
<td><span class="badge badge-<?php echo $lyric['status']; ?>"><?php echo ucfirst($lyric['status']); ?></span></td>
<td><?php echo formatDate($lyric['created_at']); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<div class="admin-card" style="flex:1;">
<div class="admin-card-header"><h2>Quick Stats</h2></div>
<div class="quick-stats">
<div class="quick-stat"><span class="quick-stat-label">Published</span><span class="quick-stat-value"><?php echo number_format($publishedLyrics); ?></span></div>
<div class="quick-stat"><span class="quick-stat-label">Drafts</span><span class="quick-stat-value"><?php echo number_format($draftLyrics); ?></span></div>
<div class="quick-stat"><span class="quick-stat-label">Featured</span><span class="quick-stat-value"><?php echo number_format($pdo->query("SELECT COUNT(*) FROM lyrics WHERE is_featured = 1")->fetchColumn()); ?></span></div>
</div>
</div>
</div>

</div>
</main>
</body>
</html>
