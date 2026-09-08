<?php
/**
 * Lyrics Ghar - Footer Include
 */
?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3 class="footer-title">Lyrics Ghar</h3>
                    <p class="footer-desc">Islamic Lyrics, All in One Place. Discover and read lyrics of Islamic songs, Nasheeds, Hamd, Naat, Ghazals and more.</p>
                </div>
                <div class="footer-section">
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo e(SITE_URL); ?>/">Home</a></li>
                        <li><a href="<?php echo e(SITE_URL); ?>/lyrics.php">All Lyrics</a></li>
                        <li><a href="<?php echo e(SITE_URL); ?>/search.php">Search</a></li>
                        <li><a href="<?php echo e(SITE_URL); ?>/about.php">About Us</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4 class="footer-heading">Categories</h4>
                    <ul class="footer-links">
                        <?php 
                        $footerCats = getCategories($pdo, true);
                        foreach (array_slice($footerCats, 0, 6) as $cat): 
                        ?>
                        <li><a href="<?php echo e(SITE_URL); ?>/category/<?php echo e($cat['slug']); ?>"><?php echo e($cat['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4 class="footer-heading">Legal</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo e(SITE_URL); ?>/privacy.php">Privacy Policy</a></li>
                        <li><a href="<?php echo e(SITE_URL); ?>/terms.php">Terms of Use</a></li>
                        <li><a href="<?php echo e(SITE_URL); ?>/sitemap.php">Sitemap</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Lyrics Ghar. All rights reserved.</p>
                <p class="footer-tagline">Islamic Lyrics, All in One Place</p>
            </div>
        </div>
    </footer>

    <!-- Notification Container -->
    <div id="notification" class="notification" role="status" aria-live="polite" aria-atomic="true">
        <span id="notification-text"></span>
    </div>

    <!-- Scripts -->
    <script src="<?php echo e(ASSETS_URL); ?>/js/main.js?v=1.0" defer></script>
    <?php if (!empty($pageScripts)): ?>
    <?php foreach ($pageScripts as $script): ?>
    <script src="<?php echo e(ASSETS_URL); ?>/js/<?php echo e($script); ?>?v=1.0" defer></script>
    <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
