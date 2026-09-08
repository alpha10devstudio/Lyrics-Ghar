<?php
/**
 * Lyrics Ghar - Breadcrumbs
 */
if (empty($breadcrumbs)) return;
?>
<nav class="breadcrumbs" aria-label="Breadcrumb">
    <div class="container">
        <ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="<?php echo e(SITE_URL); ?>/" itemprop="item">
                    <span itemprop="name">Home</span>
                </a>
                <meta itemprop="position" content="1">
            </li>
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <li class="breadcrumb-item<?php echo empty($crumb['url']) ? ' active' : ''; ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <?php if (!empty($crumb['url'])): ?>
                <a href="<?php echo e($crumb['url']); ?>" itemprop="item">
                    <span itemprop="name"><?php echo e($crumb['title']); ?></span>
                </a>
                <?php else: ?>
                <span itemprop="name"><?php echo e($crumb['title']); ?></span>
                <?php endif; ?>
                <meta itemprop="position" content="<?php echo $index + 2; ?>">
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>
