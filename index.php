<?php
/**
 * Lyrics Ghar - Homepage
 */
require_once __DIR__ . "/config/config.php";

// Clean old view logs occasionally
if (rand(1, 100) === 1) {
 cleanOldViewLogs($pdo);
}

$pageTitle = SITE_NAME . " - " . SITE_TAGLINE;
$metaDescription = getSetting($pdo, "site_description", SITE_TAGLINE);
$activePage = "home";
$bodyClass = "home-page";

// Fetch data
$featuredLyrics = getFeaturedLyrics($pdo, 6);
$latestLyrics = getLatestLyrics($pdo, 8);
$popularLyrics = getPopularLyrics($pdo, 8);
$categories = getCategories($pdo, true);
$artists = getArtists($pdo, true, 8);

// Structured data
$structuredData = [
 "@context" => "https://schema.org",
 "@type" => "WebSite",
 "name" => SITE_NAME,
 "url" => SITE_URL,
 "description" => $metaDescription,
 "potentialAction" => [
  "@type" => "SearchAction",
  "target" => SITE_URL . "/search.php?q={search_term_string}",
  "query-input" => "required name=search_term_string",
 ],
];

include __DIR__ . "/includes/header.php";
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Find Your Favorite Islamic Lyrics</h1>
            <p class="hero-subtitle">Discover Hamd, Naat, Nasheed, Ghazal and more in one place</p>
            <form action="<?php echo e(
             SITE_URL
            ); ?>/search.php" method="GET" class="hero-search" role="search">
                <label for="hero-search-input" class="visually-hidden">Search lyrics</label>
                <div class="search-input-wrapper">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="search" id="hero-search-input" name="q" class="search-input" placeholder="Search by title, artist, or category..." required autocomplete="off">
                </div>
                <button type="submit" class="btn btn-primary btn-large">Search</button>
            </form>
        </div>
    </div>
</section>

<?php if (!empty($featuredLyrics)): ?>
<section class="section section-featured">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured Lyrics</h2>
            <a href="<?php echo e(
             SITE_URL
            ); ?>/lyrics.php" class="section-link">View All</a>
        </div>
        <div class="lyrics-grid">
            <?php foreach ($featuredLyrics as $lyric): ?>
            <article class="lyric-card">
                <a href="<?php echo e(SITE_URL); ?>/lyrics/<?php echo e(
 $lyric["slug"]
); ?>" class="card-link">
                    <div class="card-image">
                        <?php if ($lyric["thumbnail"]): ?>
                        <img src="<?php echo e(
                         UPLOADS_URL . "/lyrics/" . $lyric["thumbnail"]
                        ); ?>" alt="" loading="lazy">
                        <?php elseif ($lyric["youtube_id"]): ?>
                        <img src="<?php echo e(
                         getYouTubeThumbnail($lyric["youtube_id"])
                        ); ?>" alt="" loading="lazy">
                        <?php else: ?>
                        <div class="card-image-placeholder">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M9 18V5l12-2v13"/>
                                <circle cx="6" cy="18" r="3"/>
                                <circle cx="18" cy="16" r="3"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        <?php if ($lyric["category_name"]): ?>
                        <span class="card-category"><?php echo e(
                         $lyric["category_name"]
                        ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo e(
                         $lyric["title"]
                        ); ?></h3>
                        <?php if ($lyric["artist_name"]): ?>
                        <p class="card-artist"><?php echo e(
                         $lyric["artist_name"]
                        ); ?></p>
                        <?php endif; ?>
                        <div class="card-meta">
                            <?php if ($lyric["views"] > 0): ?>
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <?php echo number_format($lyric["views"]); ?>
                            </span>
                            <?php endif; ?>
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <?php echo formatDate($lyric["created_at"]); ?>
                            </span>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($categories)): ?>
<section class="section section-categories">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Browse by Category</h2>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="<?php echo e(SITE_URL); ?>/category/<?php echo e(
 $cat["slug"]
); ?>" class="category-card">
                <div class="category-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <h3 class="category-name"><?php echo e($cat["name"]); ?></h3>
                <?php if ($cat["description"]): ?>
                <p class="category-desc"><?php echo e(
                 truncate($cat["description"], 60)
                ); ?></p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($latestLyrics)): ?>
<section class="section section-latest">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Latest Lyrics</h2>
            <a href="<?php echo e(
             SITE_URL
            ); ?>/lyrics.php?sort=latest" class="section-link">View All</a>
        </div>
        <div class="lyrics-grid">
            <?php foreach ($latestLyrics as $lyric): ?>
            <article class="lyric-card">
                <a href="<?php echo e(SITE_URL); ?>/lyrics/<?php echo e(
 $lyric["slug"]
); ?>" class="card-link">
                    <div class="card-image">
                        <?php if ($lyric["youtube_id"]): ?>
                        <img src="<?php echo e(
                         getYouTubeThumbnail($lyric["youtube_id"])
                        ); ?>" alt="" loading="lazy">
                        <?php else: ?>
                        <div class="card-image-placeholder">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M9 18V5l12-2v13"/>
                                <circle cx="6" cy="18" r="3"/>
                                <circle cx="18" cy="16" r="3"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        <?php if ($lyric["category_name"]): ?>
                        <span class="card-category"><?php echo e(
                         $lyric["category_name"]
                        ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo e(
                         $lyric["title"]
                        ); ?></h3>
                        <?php if ($lyric["artist_name"]): ?>
                        <p class="card-artist"><?php echo e(
                         $lyric["artist_name"]
                        ); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($popularLyrics)): ?>
<section class="section section-popular">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Popular Lyrics</h2>
            <a href="<?php echo e(
             SITE_URL
            ); ?>/lyrics.php?sort=popular" class="section-link">View All</a>
        </div>
        <div class="lyrics-list">
            <?php foreach ($popularLyrics as $index => $lyric): ?>
            <article class="lyric-list-item">
                <span class="list-number"><?php echo $index + 1; ?></span>
                <div class="list-content">
                    <a href="<?php echo e(SITE_URL); ?>/lyrics/<?php echo e(
 $lyric["slug"]
); ?>" class="list-title"><?php echo e($lyric["title"]); ?></a>
                    <div class="list-meta">
                        <?php if ($lyric["artist_name"]): ?>
                        <span class="list-artist"><?php echo e(
                         $lyric["artist_name"]
                        ); ?></span>
                        <?php endif; ?>
                        <?php if ($lyric["category_name"]): ?>
                        <span class="list-category"><?php echo e(
                         $lyric["category_name"]
                        ); ?></span>
                        <?php endif; ?>
                        <span class="list-views">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <?php echo number_format($lyric["views"]); ?>
                        </span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($artists)): ?>
<section class="section section-artists">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Popular Artists</h2>
            <a href="<?php echo e(
             SITE_URL
            ); ?>/artist.php" class="section-link">View All</a>
        </div>
        <div class="artists-grid">
            <?php foreach ($artists as $artist): ?>
            <a href="<?php echo e(SITE_URL); ?>/artist/<?php echo e(
 $artist["slug"]
); ?>" class="artist-card">
                <div class="artist-avatar">
                    <?php if ($artist["image"]): ?>
                    <img src="<?php echo e(
                     UPLOADS_URL . "/artists/" . $artist["image"]
                    ); ?>" alt="" loading="lazy">
                    <?php else: ?>
                    <div class="artist-avatar-placeholder">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <?php endif; ?>
                </div>
                <h3 class="artist-name"><?php echo e($artist["name"]); ?></h3>
                <?php if ($artist["lyrics_count"] > 0): ?>
                <p class="artist-count"><?php echo $artist[
                 "lyrics_count"
                ]; ?> lyrics</p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . "/includes/footer.php";
