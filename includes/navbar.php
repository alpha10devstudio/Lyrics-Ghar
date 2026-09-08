<?php
/**
 * Lyrics Ghar - Navigation Bar
 */
?>
<header class="site-header">
    <div class="container header-container">
        <a href="<?php echo e(SITE_URL); ?>/" class="logo" aria-label="Lyrics Ghar Home">
            <svg class="logo-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 18V5l12-2v13"/>
                <circle cx="6" cy="18" r="3"/>
                <circle cx="18" cy="16" r="3"/>
            </svg>
            <span class="logo-text">Lyrics Ghar</span>
        </a>

        <nav class="main-nav" aria-label="Main navigation">
            <ul class="nav-list">
                <li><a href="<?php echo e(SITE_URL); ?>/" class="nav-link<?php echo ($activePage ?? '') === 'home' ? ' active' : ''; ?>">Home</a></li>
                <li><a href="<?php echo e(SITE_URL); ?>/lyrics.php" class="nav-link<?php echo ($activePage ?? '') === 'lyrics' ? ' active' : ''; ?>">Lyrics</a></li>
                <li class="nav-dropdown">
                    <button class="nav-link nav-dropdown-toggle" aria-expanded="false" aria-haspopup="true">
                        Categories
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <?php 
                        $navCats = getCategories($pdo, true);
                        foreach ($navCats as $cat): 
                        ?>
                        <li role="none">
                            <a href="<?php echo e(SITE_URL); ?>/category/<?php echo e($cat['slug']); ?>" class="dropdown-link" role="menuitem">
                                <?php echo e($cat['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li><a href="<?php echo e(SITE_URL); ?>/artist.php" class="nav-link<?php echo ($activePage ?? '') === 'artists' ? ' active' : ''; ?>">Artists</a></li>
                <li><a href="<?php echo e(SITE_URL); ?>/search.php" class="nav-link<?php echo ($activePage ?? '') === 'search' ? ' active' : ''; ?>">Search</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <a href="<?php echo e(SITE_URL); ?>/search.php" class="header-search-btn" aria-label="Search">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </a>
            <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="main-nav">
                <svg class="menu-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
                <svg class="close-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>
</header>
