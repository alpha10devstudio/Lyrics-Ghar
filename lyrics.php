<?php
/**
 * Lyrics Ghar - Single Lyrics Page / Lyrics Listing
 */
require_once __DIR__ . '/config/config.php';

// Get slug from URL (supports both query string and path)
$slug = $_GET['slug'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH) ?? '';
$path = trim($path, '/');

// Try to extract slug from path like "lyrics/song-name"
if (empty($slug) && strpos($path, 'lyrics/') === 0) {
    $slug = substr($path, 7); // Remove "lyrics/"
    $slug = explode('/', $slug)[0]; // Get first segment
    $slug = explode('?', $slug)[0]; // Remove query string
}

// If no slug, show listing page
if (empty($slug)) {
    // Listing page
    $page = max(1, intval($_GET['page'] ?? 1));
    $sort = in_array($_GET['sort'] ?? '', ['latest', 'popular', 'az']) ? $_GET['sort'] : 'latest';
    $categoryFilter = $_GET['category'] ?? '';
    $languageFilter = $_GET['language'] ?? '';

    $perPage = POSTS_PER_PAGE;
    $offset = ($page - 1) * $perPage;

    $where = ["l.status = 'published'"];
    $params = [];

    if ($categoryFilter) {
        $where[] = "c.slug = ?";
        $params[] = $categoryFilter;
    }
    if ($languageFilter) {
        $where[] = "lang.slug = ?";
        $params[] = $languageFilter;
    }

    $whereClause = implode(' AND ', $where);

    // Sort
    $orderBy = match($sort) {
        'popular' => 'l.views DESC, l.created_at DESC',
        'az' => 'l.title ASC',
        default => 'l.created_at DESC'
    };

    // Count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM lyrics l LEFT JOIN categories c ON l.category_id = c.id LEFT JOIN languages lang ON l.language_id = lang.id WHERE {$whereClause}");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    $totalPages = ceil($total / $perPage);

    // Fetch
    $sql = "SELECT l.*, c.name as category_name, c.slug as category_slug, a.name as artist_name, a.slug as artist_slug, lang.name as language_name 
            FROM lyrics l 
            LEFT JOIN categories c ON l.category_id = c.id 
            LEFT JOIN artists a ON l.artist_id = a.id 
            LEFT JOIN languages lang ON l.language_id = lang.id 
            WHERE {$whereClause} 
            ORDER BY {$orderBy} 
            LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $lyricsList = $stmt->fetchAll();

    $pageTitle = 'All Lyrics - ' . SITE_NAME;
    $metaDescription = 'Browse all Islamic lyrics including Hamd, Naat, Nasheed, Ghazal and more on Lyrics Ghar.';
    $activePage = 'lyrics';
    $bodyClass = 'lyrics-listing-page';
    $categories = getCategories($pdo);
    $languages = getLanguages($pdo);

    include __DIR__ . '/includes/header.php';
    ?>

    <section class="page-header">
        <div class="container">
            <h1 class="page-title">All Lyrics</h1>
            <p class="page-subtitle">Browse our collection of Islamic lyrics</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="filter-bar">
                <div class="filter-group">
                    <label for="sort-filter" class="visually-hidden">Sort by</label>
                    <select id="sort-filter" class="filter-select" onchange="window.location.href='?sort='+this.value<?php echo $categoryFilter ? "+'&category=".e($categoryFilter)."'" : ''; ?><?php echo $languageFilter ? "+'&language=".e($languageFilter)."'" : ''; ?>">
                        <option value="latest" <?php echo $sort === 'latest' ? 'selected' : ''; ?>>Latest</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                        <option value="az" <?php echo $sort === 'az' ? 'selected' : ''; ?>>A-Z</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="category-filter" class="visually-hidden">Filter by category</label>
                    <select id="category-filter" class="filter-select" onchange="window.location.href='?category='+this.value<?php echo $sort !== 'latest' ? "+'&sort=".e($sort)."'" : ''; ?><?php echo $languageFilter ? "+'&language=".e($languageFilter)."'" : ''; ?>">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo e($cat['slug']); ?>" <?php echo $categoryFilter === $cat['slug'] ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="language-filter" class="visually-hidden">Filter by language</label>
                    <select id="language-filter" class="filter-select" onchange="window.location.href='?language='+this.value<?php echo $sort !== 'latest' ? "+'&sort=".e($sort)."'" : ''; ?><?php echo $categoryFilter ? "+'&category=".e($categoryFilter)."'" : ''; ?>">
                        <option value="">All Languages</option>
                        <?php foreach ($languages as $lang): ?>
                        <option value="<?php echo e($lang['slug']); ?>" <?php echo $languageFilter === $lang['slug'] ? 'selected' : ''; ?>><?php echo e($lang['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if (empty($lyricsList)): ?>
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <h2>No lyrics found</h2>
                <p>Try adjusting your filters or search criteria.</p>
            </div>
            <?php else: ?>
            <div class="lyrics-grid">
                <?php foreach ($lyricsList as $lyric): ?>
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
                            <?php if ($lyric['artist_name']): ?>
                            <p class="card-artist"><?php echo e($lyric['artist_name']); ?></p>
                            <?php endif; ?>
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
                $baseUrl = SITE_URL . '/lyrics.php' . ($sort !== 'latest' || $categoryFilter || $languageFilter ? '?' : '');
                $parts = [];
                if ($sort !== 'latest') $parts[] = 'sort=' . $sort;
                if ($categoryFilter) $parts[] = 'category=' . $categoryFilter;
                if ($languageFilter) $parts[] = 'language=' . $languageFilter;
                $baseUrl .= implode('&', $parts);
                echo generatePagination($page, $totalPages, $baseUrl);
            endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Single lyrics page
$lyric = getLyricBySlug($pdo, $slug);

if (!$lyric) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Increment views
incrementViews($pdo, $lyric['id']);

// Get tags and related
$tags = getLyricTags($pdo, $lyric['id']);
$relatedLyrics = getRelatedLyrics($pdo, $lyric, 6);

// Page metadata
$pageTitle = $lyric['meta_title'] ?? ($lyric['title'] . ' - ' . ($lyric['artist_name'] ? $lyric['artist_name'] . ' - ' : '') . SITE_NAME);
$metaDescription = $lyric['meta_description'] ?? truncate($lyric['description'] ?? $lyric['lyrics'], 160);
$canonicalUrl = SITE_URL . '/lyrics/' . $lyric['slug'];
$ogTitle = $lyric['title'];
$ogDescription = truncate($lyric['description'] ?? $lyric['lyrics'], 200);
$ogImage = $lyric['thumbnail'] ? (UPLOADS_URL . '/lyrics/' . $lyric['thumbnail']) : ($lyric['youtube_id'] ? getYouTubeThumbnail($lyric['youtube_id'], 'maxresdefault') : '');
$ogType = 'article';

// Determine text direction
$textDir = $lyric['direction'] ?? 'ltr';
$bodyClass = 'single-lyrics-page';

// Breadcrumbs
$breadcrumbs = [];
if ($lyric['category_name']) {
    $breadcrumbs[] = ['title' => $lyric['category_name'], 'url' => SITE_URL . '/category/' . $lyric['category_slug']];
}
if ($lyric['artist_name']) {
    $breadcrumbs[] = ['title' => $lyric['artist_name'], 'url' => SITE_URL . '/artist/' . $lyric['artist_slug']];
}
$breadcrumbs[] = ['title' => $lyric['title'], 'url' => ''];

// Structured data
$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'MusicRecording',
    'name' => $lyric['title'],
    'url' => $canonicalUrl,
    'description' => truncate($lyric['description'] ?? $lyric['lyrics'], 300),
    'datePublished' => $lyric['created_at'],
    'dateModified' => $lyric['updated_at'],
    'byArtist' => $lyric['artist_name'] ? [
        '@type' => 'MusicGroup',
        'name' => $lyric['artist_name'],
        'url' => SITE_URL . '/artist/' . $lyric['artist_slug']
    ] : null,
    'genre' => $lyric['category_name'] ?? 'Islamic Music',
    'inLanguage' => $lyric['language_name'] ?? 'en'
];

if ($lyric['youtube_id']) {
    $structuredData['video'] = [
        '@type' => 'VideoObject',
        'name' => $lyric['title'],
        'thumbnailUrl' => getYouTubeThumbnail($lyric['youtube_id']),
        'embedUrl' => getYouTubeEmbedUrl($lyric['youtube_id'])
    ];
}

$pageScripts = ['lyrics.js'];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/breadcrumbs.php';
?>

<article class="single-lyrics" dir="<?php echo e($textDir); ?>">
    <div class="container">
        <!-- Category Badge -->
        <?php if ($lyric['category_name']): ?>
        <div class="lyric-category-badge">
            <a href="<?php echo e(SITE_URL); ?>/category/<?php echo e($lyric['category_slug']); ?>" class="category-link">
                <?php echo e($lyric['category_name']); ?>
            </a>
        </div>
        <?php endif; ?>

        <!-- Title -->
        <header class="lyric-header">
            <h1 class="lyric-title"><?php echo e($lyric['title']); ?></h1>

            <!-- Credits -->
            <div class="lyric-credits">
                <?php if ($lyric['vocalist']): ?>
                <span class="credit-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    Vocalist: <?php echo e($lyric['vocalist']); ?>
                </span>
                <?php endif; ?>
                <?php if ($lyric['writer']): ?>
                <span class="credit-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Writer: <?php echo e($lyric['writer']); ?>
                </span>
                <?php endif; ?>
                <?php if ($lyric['composer']): ?>
                <span class="credit-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M9 18V5l12-2v13"/>
                        <circle cx="6" cy="18" r="3"/>
                        <circle cx="18" cy="16" r="3"/>
                    </svg>
                    Composer: <?php echo e($lyric['composer']); ?>
                </span>
                <?php endif; ?>
                <?php if ($lyric['production']): ?>
                <span class="credit-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    Production: <?php echo e($lyric['production']); ?>
                </span>
                <?php endif; ?>
            </div>

            <!-- Meta info -->
            <div class="lyric-meta-header">
                <?php if ($lyric['artist_name']): ?>
                <a href="<?php echo e(SITE_URL); ?>/artist/<?php echo e($lyric['artist_slug']); ?>" class="meta-artist">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <?php echo e($lyric['artist_name']); ?>
                </a>
                <?php endif; ?>
                <?php if ($lyric['language_name']): ?>
                <span class="meta-language">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    <?php echo e($lyric['language_name']); ?>
                </span>
                <?php endif; ?>
                <?php if ($lyric['views'] > 0): ?>
                <span class="meta-views">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <?php echo number_format($lyric['views']); ?> views
                </span>
                <?php endif; ?>
            </div>
        </header>

        <!-- Action Buttons -->
        <div class="lyric-actions">
            <button type="button" class="btn btn-primary action-btn" id="copy-lyrics-btn" data-lyrics="<?php echo e($lyric['lyrics']); ?>" data-title="<?php echo e($lyric['title']); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
                Copy Lyrics
            </button>
            <button type="button" class="btn btn-secondary action-btn" id="share-btn" data-url="<?php echo e($canonicalUrl); ?>" data-title="<?php echo e($lyric['title']); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="18" cy="5" r="3"/>
                    <circle cx="6" cy="12" r="3"/>
                    <circle cx="18" cy="19" r="3"/>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                </svg>
                Share
            </button>
            <button type="button" class="btn btn-secondary action-btn" onclick="window.print()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Print
            </button>
        </div>

        <!-- YouTube Video -->
        <?php if ($lyric['youtube_id']): ?>
        <div class="video-container">
            <iframe 
                src="<?php echo e(getYouTubeEmbedUrl($lyric['youtube_id']); ?>" 
                title="<?php echo e($lyric['title']); ?> - YouTube Video"
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen
                loading="lazy">
            </iframe>
        </div>
        <?php endif; ?>

        <!-- Lyrics Content -->
        <div class="lyrics-content-wrapper">
            <div class="lyrics-content" id="lyrics-text">
                <?php echo formatLyrics($lyric['lyrics']); ?>
            </div>
        </div>

        <!-- Tags -->
        <?php if (!empty($tags)): ?>
        <div class="lyric-tags">
            <h3 class="tags-title">Tags</h3>
            <div class="tags-list">
                <?php foreach ($tags as $tag): ?>
                <a href="<?php echo e(SITE_URL); ?>/search.php?q=<?php echo urlencode($tag['name']); ?>" class="tag-link"><?php echo e($tag['name']); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Information After Lyrics -->
        <div class="lyric-info-section">
            <?php if ($lyric['description']): ?>
            <section class="info-block">
                <h2 class="info-title">About This Song</h2>
                <div class="info-content">
                    <?php echo nl2br(e($lyric['description'])); ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($lyric['theme']): ?>
            <section class="info-block">
                <h2 class="info-title">Theme / Main Idea</h2>
                <div class="info-content">
                    <?php echo e($lyric['theme']); ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Detailed Credits -->
            <section class="info-block info-credits">
                <h2 class="info-title">Credits</h2>
                <dl class="credits-list">
                    <?php if ($lyric['vocalist']): ?>
                    <div class="credit-row">
                        <dt>Vocalist</dt>
                        <dd><?php echo e($lyric['vocalist']); ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($lyric['writer']): ?>
                    <div class="credit-row">
                        <dt>Writer</dt>
                        <dd><?php echo e($lyric['writer']); ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($lyric['composer']): ?>
                    <div class="credit-row">
                        <dt>Composer</dt>
                        <dd><?php echo e($lyric['composer']); ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($lyric['production']): ?>
                    <div class="credit-row">
                        <dt>Production</dt>
                        <dd><?php echo e($lyric['production']); ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($lyric['publisher']): ?>
                    <div class="credit-row">
                        <dt>Publisher</dt>
                        <dd><?php echo e($lyric['publisher']); ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($lyric['credits']): ?>
                    <div class="credit-row">
                        <dt>Additional Credits</dt>
                        <dd><?php echo nl2br(e($lyric['credits'])); ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </section>
        </div>

        <!-- Related Lyrics -->
        <?php if (!empty($relatedLyrics)): ?>
        <section class="section section-related">
            <div class="section-header">
                <h2 class="section-title">Related Lyrics</h2>
            </div>
            <div class="lyrics-grid lyrics-grid-small">
                <?php foreach ($relatedLyrics as $rel): ?>
                <article class="lyric-card card-compact">
                    <a href="<?php echo e(SITE_URL); ?>/lyrics/<?php echo e($rel['slug']); ?>" class="card-link">
                        <div class="card-image">
                            <?php if ($rel['youtube_id']): ?>
                            <img src="<?php echo e(getYouTubeThumbnail($rel['youtube_id'])); ?>" alt="" loading="lazy">
                            <?php else: ?>
                            <div class="card-image-placeholder">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M9 18V5l12-2v13"/>
                                    <circle cx="6" cy="18" r="3"/>
                                    <circle cx="18" cy="16" r="3"/>
                                </svg>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?php echo e($rel['title']); ?></h3>
                            <?php if ($rel['artist_name']): ?>
                            <p class="card-artist"><?php echo e($rel['artist_name']); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</article>

<!-- Share Modal -->
<div id="share-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="share-modal-title" hidden>
    <div class="modal-overlay" data-close-modal></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="share-modal-title">Share This Lyric</h3>
            <button type="button" class="modal-close" data-close-modal aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="share-options">
                <button type="button" class="share-btn share-native" id="share-native-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="18" cy="5" r="3"/>
                        <circle cx="6" cy="12" r="3"/>
                        <circle cx="18" cy="19" r="3"/>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                    </svg>
                    Share
                </button>
                <a href="#" class="share-btn share-whatsapp" id="share-whatsapp" target="_blank" rel="noopener">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                    WhatsApp
                </a>
                <a href="#" class="share-btn share-facebook" id="share-facebook" target="_blank" rel="noopener">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                    </svg>
                    Facebook
                </a>
                <button type="button" class="share-btn share-copy" id="share-copy-link">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                    Copy Link
                </button>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';
