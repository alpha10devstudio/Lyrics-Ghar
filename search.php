<?php
/**
 * Lyrics Ghar - Search Page
 */
require_once __DIR__ . '/config/config.php';

$query = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';
$artist = $_GET['artist'] ?? '';
$language = $_GET['language'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

$filters = [];
if ($category) $filters['category'] = $category;
if ($artist) $filters['artist'] = $artist;
if ($language) $filters['language'] = $language;

$searchResults = searchLyrics($pdo, $query, $filters, $page, POSTS_PER_PAGE);
$categories = getCategories($pdo);
$languages = getLanguages($pdo);

$pageTitle = $query ? 'Search: ' . $query . ' - ' . SITE_NAME : 'Search - ' . SITE_NAME;
$metaDescription = 'Search Islamic lyrics on Lyrics Ghar. Find Hamd, Naat, Nasheed, Ghazal and more.';
$activePage = 'search';
$bodyClass = 'search-page';
$canonicalUrl = SITE_URL . '/search.php' . ($query ? '?q=' . urlencode($query) : '');

include __DIR__ . '/includes/header.php';
?>

<section class="page-header page-header-search">
    <div class="container">
        <h1 class="page-title">Search Lyrics</h1>
        <form action="<?php echo e(SITE_URL); ?>/search.php" method="GET" class="search-form-large" role="search">
            <label for="search-input" class="visually-hidden">Search lyrics</label>
            <div class="search-input-wrapper search-input-wrapper-large">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="search" id="search-input" name="q" class="search-input" value="<?php echo e($query); ?>" placeholder="Search by title, artist, lyrics..." autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($query || $filters): ?>
        <div class="search-results-header">
            <p class="results-count">
                <?php if ($searchResults['total'] > 0): ?>
                Found <?php echo number_format($searchResults['total']); ?> result<?php echo $searchResults['total'] !== 1 ? 's' : ''; ?>
                <?php else: ?>
                No results found
                <?php endif; ?>
            </p>
        </div>

        <?php if (!empty($searchResults['results'])): ?>
        <div class="lyrics-grid">
            <?php foreach ($searchResults['results'] as $lyric): ?>
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
                        <?php if ($lyric['language_name']): ?>
                        <p class="card-language"><?php echo e($lyric['language_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ($searchResults['pages'] > 1): 
            $baseUrl = SITE_URL . '/search.php?q=' . urlencode($query);
            if ($category) $baseUrl .= '&category=' . urlencode($category);
            if ($artist) $baseUrl .= '&artist=' . urlencode($artist);
            if ($language) $baseUrl .= '&language=' . urlencode($language);
            echo generatePagination($page, $searchResults['pages'], $baseUrl);
        endif; ?>

        <?php else: ?>
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <h2>No results found</h2>
            <p>Try different keywords or browse by category.</p>
            <div class="empty-actions">
                <a href="<?php echo e(SITE_URL); ?>/" class="btn btn-primary">Browse Homepage</a>
                <a href="<?php echo e(SITE_URL); ?>/lyrics.php" class="btn btn-secondary">All Lyrics</a>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="search-categories">
            <h2 class="section-title section-title-small">Browse by Category</h2>
            <div class="categories-grid categories-grid-small">
                <?php foreach ($categories as $cat): ?>
                <a href="<?php echo e(SITE_URL); ?>/category/<?php echo e($cat['slug']); ?>" class="category-card category-card-small">
                    <span class="category-name"><?php echo e($cat['name']); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
