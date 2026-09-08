<?php
/**
 * Lyrics Ghar - Header Include
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#0D5C3A">
    <meta name="description" content="<?php echo e($metaDescription ?? SITE_TAGLINE); ?>">
    <meta name="keywords" content="Islamic lyrics, nasheed, hamd, naat, ghazal, Islamic songs, Ramadan songs, Bengali Islamic lyrics, Arabic nasheed">
    <meta name="author" content="Lyrics Ghar">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:site_name" content="<?php echo e(SITE_NAME); ?>">
    <meta property="og:title" content="<?php echo e($ogTitle ?? SITE_NAME); ?>">
    <meta property="og:description" content="<?php echo e($ogDescription ?? SITE_TAGLINE); ?>">
    <meta property="og:type" content="<?php echo e($ogType ?? 'website'); ?>">
    <meta property="og:url" content="<?php echo e($canonicalUrl ?? SITE_URL); ?>">
    <?php if (!empty($ogImage)): ?>
    <meta property="og:image" content="<?php echo e($ogImage); ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($ogTitle ?? SITE_NAME); ?>">
    <meta name="twitter:description" content="<?php echo e($ogDescription ?? SITE_TAGLINE); ?>">
    <?php if (!empty($ogImage)): ?>
    <meta name="twitter:image" content="<?php echo e($ogImage); ?>">
    <?php endif; ?>

    <!-- Canonical -->
    <link rel="canonical" href="<?php echo e($canonicalUrl ?? SITE_URL); ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo e(ASSETS_URL); ?>/images/favicon.png">
    <link rel="apple-touch-icon" href="<?php echo e(ASSETS_URL); ?>/images/apple-touch-icon.png">

    <!-- PWA -->
    <link rel="manifest" href="<?php echo e(SITE_URL); ?>/manifest.webmanifest">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Naskh+Arabic:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo e(ASSETS_URL); ?>/css/style.css?v=1.0">
    <link rel="stylesheet" href="<?php echo e(ASSETS_URL); ?>/css/responsive.css?v=1.0">

    <!-- Structured Data -->
    <?php if (!empty($structuredData)): ?>
    <script type="application/ld+json">
    <?php echo json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>
    <?php endif; ?>

    <title><?php echo e($pageTitle ?? SITE_NAME); ?></title>
</head>
<body class="<?php echo e($bodyClass ?? ''); ?>">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <?php include __DIR__ . '/navbar.php'; ?>
    <main id="main-content">
