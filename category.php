<?php
/**
 * Lyrics Ghar - Category Page
 */
require_once __DIR__ . '/config/config.php';

$slug = $_GET['slug'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH) ?? '';
$path = trim($path, '/');

if (empty($slug) && strpos($path, 'category/') === 0) {
    $slug = substr($path, 9);
    $slug = explode('/', $slug)[0];
    $slug = explode('?', $slug)[0];
}

$category = getCategoryBySlug($pdo, $slug);

if (!$category) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = POSTS_PER_PAGE;

$lyrics = getLyricsByCategory($pdo, $category['id'], $page, $perPage);

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM lyrics WHERE category_id = ? AND status = 'published'");
$countStmt->execute([$category['id']]);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$pageTitle = ($category['meta_title'] ?? $category['name']) . ' - ' . SITE_NAME;
$metaDescription = $category['meta_description'] ?? 'Browse ' . $category['name'] . ' lyrics on Lyrics Ghar.';
$canonicalUrl = SITE_URL . '/category/' . $category['slug'];
$ogTitle = $category['name'];
$ogDescription = $metaDescription;
$ogImage = $category['image'] ? (UPLOADS_URL . '/categories/' . $category['image']) : '';
$activePage = 'lyrics';
$bodyClass = 'category-page';

$breadcrumbs = [
    ['title' => $category['name'], 'url' => '']
];

$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $category['name'],
    'description' => $metaDescription,
    'url' => $canonicalUrl,
    'mainEntity' => [
        '@type' => 'ItemList',
        'itemListElement' => array_map(function($lyric, $index) {
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $lyric['title'],
                'url' => SITE_URL . '/lyrics/' . $lyric['slug']
            ];
        }, $lyrics, array_keys($lyrics))
    ]
];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/breadcrumbs.php';
?>

<section class="page-header page-header-category">
    <div class="container">
        <h1 class="page-title"><?php echo e($category['name']); ?></h1>
        <?php if ($category['description']): ?>
        <p class="page-subtitle"><?php echo e($category['description']); ?></p>
        <?php endif; ?>
        <p class="page-count"><?php echo $total; ?> lyrics</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($lyrics)): ?>
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <h2>No lyrics found</h2>
            <p>No lyrics available in this category yet.</p>
        </div>
        <?php else: ?>
        <div class="lyrics-grid">
            <?php foreach ($lyrics as $lyric): ?>
            <article class="lyric-card">
                <a href="<?php echo e(SITE_URL); ?>/lyrics/<?php echo e($lyric['slug']); ?>" class="card-link">
                    <div class="card-image">
                        <?php if ($lyric['youtube_id']): ?>
                        <img src="<?php echo e(getYouTubeThumbnail($lyric['youtube_id'])); ?>" alt="" loading="lazy">
                        <?php else: ?>
                        <div class="card-image-placeholder">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M9 18V5l12-2v13"/>
                                <circle cx="6" cy="18" r="3"/>
                                <circle cx="18" cy="16" r="3"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo e($lyric['title']); ?></h3>
                        <?php if ($lyric['artist_name']): ?>
                        <p class="card-artist"><?php echo e($lyric['artist_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): 
            $baseUrl = SITE_URL . '/category/' . $category['slug'];
            echo generatePagination($page, $totalPages, $baseUrl);
        endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
