<?php
require_once __DIR__ . '/config/config.php';
http_response_code(404);
$pageTitle = 'Page Not Found - ' . SITE_NAME;
$metaDescription = 'The page you are looking for could not be found.';
$bodyClass = 'error-page';
include __DIR__ . '/includes/header.php';
?>
<section class="page-header page-header-error">
<div class="container">
<h1 class="page-title">404 - Page Not Found</h1>
<p class="page-subtitle">The page you are looking for does not exist or has been moved.</p>
<div class="error-actions">
<a href="<?php echo e(SITE_URL); ?>/" class="btn btn-primary">Go Home</a>
<a href="<?php echo e(SITE_URL); ?>/search.php" class="btn btn-secondary">Search</a>
</div>
</div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
