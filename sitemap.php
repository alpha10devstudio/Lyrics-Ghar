<?php
require_once __DIR__ . '/config/config.php';
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url><loc><?php echo e(SITE_URL); ?>/</loc><changefreq>daily</changefreq><priority>1.0</priority></url>
<url><loc><?php echo e(SITE_URL); ?>/lyrics.php</loc><changefreq>daily</changefreq><priority>0.9</priority></url>
<url><loc><?php echo e(SITE_URL); ?>/artist.php</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>
<url><loc><?php echo e(SITE_URL); ?>/search.php</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>
<url><loc><?php echo e(SITE_URL); ?>/about.php</loc><changefreq>monthly</changefreq><priority>0.5</priority></url>
<url><loc><?php echo e(SITE_URL); ?>/privacy.php</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>
<url><loc><?php echo e(SITE_URL); ?>/terms.php</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>
<?php
$stmt = $pdo->query("SELECT slug, updated_at FROM lyrics WHERE status = 'published' ORDER BY updated_at DESC");
while ($row = $stmt->fetch()) {
    echo '<url><loc>' . e(SITE_URL) . '/lyrics/' . e($row['slug']) . '</loc><lastmod>' . date('Y-m-d', strtotime($row['updated_at'])) . '</lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>' . "\n";
}
$stmt = $pdo->query("SELECT slug, updated_at FROM categories WHERE status = 'active' ORDER BY updated_at DESC");
while ($row = $stmt->fetch()) {
    echo '<url><loc>' . e(SITE_URL) . '/category/' . e($row['slug']) . '</loc><lastmod>' . date('Y-m-d', strtotime($row['updated_at'])) . '</lastmod><changefreq>weekly</changefreq><priority>0.7</priority></url>' . "\n";
}
$stmt = $pdo->query("SELECT slug, updated_at FROM artists WHERE status = 'active' ORDER BY updated_at DESC");
while ($row = $stmt->fetch()) {
    echo '<url><loc>' . e(SITE_URL) . '/artist/' . e($row['slug']) . '</loc><lastmod>' . date('Y-m-d', strtotime($row['updated_at'])) . '</lastmod><changefreq>weekly</changefreq><priority>0.7</priority></url>' . "\n";
}
?>
</urlset>
