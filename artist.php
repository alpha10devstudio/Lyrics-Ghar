<?php
/**
 * Lyrics Ghar - Artist Page
 */
require_once __DIR__ . '/config/config.php';

$slug = $_GET['slug'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH) ?? '';
$path = trim($path, '/');

if (empty($slug) && strpos($path, 'artist/') === 0) {
    $slug = substr($path, 7);
    $slug = explode('/', $slug)[0];
    $slug = explode('?', $slug)[0];
}

// If no slug, show artists listing
if (empty($slug)) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = POSTS_PER_PAGE;
    $offset = ($page - 1) * $perPage;

    $stmt = $pdo->query("SELECT a.*, (SELECT COUNT(*) FROM lyrics WHERE artist_id = a.id AND status = 'published') as lyrics_count FROM artists a WHERE a.status = 'active' ORDER BY a.name LIMIT {$perPage} OFFSET {$offset}");
    $artists = $stmt->fetchAll();

    $countStmt = $pdo->query("SELECT COUNT(*) FROM artists WHERE status = 'active'");
    $total = $countStmt->fetchColumn();
    $totalPages = ceil($total / $perPage);

    $pageTitle = 'Artists - ' . SITE_NAME;
    $metaDescription = 'Browse Islamic artists and vocalists on Lyrics Ghar.';
    $activePage = 'artists';
    $bodyClass = 'artists-listing-page';

    include __DIR__ . '/includes/header.php';
    ?>

    <section class="page-header">
        <div class="container">
            <h1 class="page-title">Artists</h1>
            <p class="page-subtitle">Discover talented Islamic artists and vocalists</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php if (empty($artists)): ?>
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <h2>No artists found</h2>
            </div>
            <?php else: ?>
            <div class="artists-grid artists-grid-large">
                <?php foreach ($artists as $artist): ?>
                <a href="<?php echo e(SITE_URL); ?>/artist/<?php echo e($artist['slug']); ?>" class="artist-card artist-card-large">
                    <div class="artist-avatar artist-avatar-large">
                        <?php if ($artist['image']): ?>
                        <img src="<?php echo e(UPLOADS_URL . '/artists/' . $artist['image']); ?>" alt="" loading="lazy">
                        <?php else: ?>
                        <div class="artist-avatar-placeholder">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <h3 class="artist-name"><?php echo e($artist['name']); ?></h3>
                    <?php if ($artist['lyrics_count'] > 0): ?>
                    <p class="artist-count"><?php echo $artist['lyrics_count']; ?> lyrics</p>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): 
                echo generatePagination($page, $totalPages, SITE_URL . '/artist.php');
            endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Single artist page
$artist = getArtistBySlug($pdo, $slug);

if (!$artist) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = POSTS_PER_PAGE;

$lyrics = getLyricsByArtist($pdo, $artist['id'], $page, $perPage);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM lyrics WHERE artist_id = ? AND status = 'published'");
$countStmt->execute([$artist['id']]);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$pageTitle = $artist['name'] . ' - ' . SITE_NAME;
$metaDescription = $artist['biography'] ? truncate($artist['biography'], 160) : 'Browse all lyrics by ' . $artist['name'] . ' on Lyrics Ghar.';
$canonicalUrl = SITE_URL . '/artist/' . $artist['slug'];
$ogTitle = $artist['name'];
$ogDescription = $metaDescription;
$ogImage = $artist['image'] ? (UPLOADS_URL . '/artists/' . $artist['image']) : '';
$activePage = 'artists';
$bodyClass = 'artist-page';

$breadcrumbs = [
    ['title' => 'Artists', 'url' => SITE_URL . '/artist.php'],
    ['title' => $artist['name'], 'url' => '']
];

$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'MusicGroup',
    'name' => $artist['name'],
    'description' => $artist['biography'] ?? '',
    'url' => $canonicalUrl
];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/breadcrumbs.php';
?>

<section class="page-header page-header-artist">
    <div class="container">
        <div class="artist-profile">
            <div class="artist-profile-image">
                <?php if ($artist['image']): ?>
                <img src="<?php echo e(UPLOADS_URL . '/artists/' . $artist['image']); ?>" alt="<?php echo e($artist['name']); ?>">
                <?php else: ?>
                <div class="artist-avatar-placeholder artist-avatar-xl">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <?php endif; ?>
            </div>
            <div class="artist-profile-info">
                <h1 class="page-title"><?php echo e($artist['name']); ?></h1>
                <p class="artist-stats"><?php echo $total; ?> lyrics</p>
                <?php if ($artist['biography']): ?>
                <div class="artist-bio">
                    <?php echo nl2br(e($artist['biography'])); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title section-title-small">Lyrics by <?php echo e($artist['name']); ?></h2>

        <?php if (empty($lyrics)): ?>
        <div class="empty-state">
            <p>No lyrics available for this artist yet.</p>
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
                        <?php if ($lyric['category_name']): ?>
                        <span class="card-category"><?php echo e($lyric['category_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo e($lyric['title']); ?></h3>
                        <div class="card-meta">
                            <?php if ($lyric['views'] > 0): ?>
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <?php echo number_format($lyric['views']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): 
            $baseUrl = SITE_URL . '/artist/' . $artist['slug'];
            echo generatePagination($page, $totalPages, $baseUrl);
        endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
