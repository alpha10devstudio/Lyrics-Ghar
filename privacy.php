<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Privacy Policy - ' . SITE_NAME;
$metaDescription = 'Privacy Policy of Lyrics Ghar.';
$bodyClass = 'legal-page';
$breadcrumbs = [['title' => 'Privacy Policy', 'url' => '']];
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/breadcrumbs.php';
?>
<section class="page-header"><div class="container"><h1 class="page-title">Privacy Policy</h1></div></section>
<section class="section section-content"><div class="container content-container">
<div class="content-block">
<h2>Information We Collect</h2>
<p>We collect minimal information necessary to provide our services. This includes basic usage data such as page views for analytics purposes.</p>
<h2>Cookies</h2>
<p>We use essential cookies to maintain your session and preferences. We do not use tracking cookies for advertising purposes.</p>
<h2>Third-Party Services</h2>
<p>We embed YouTube videos using privacy-enhanced mode (youtube-nocookie.com). YouTube may collect data according to their own privacy policy.</p>
<h2>Data Security</h2>
<p>We implement appropriate security measures to protect your information. We do not sell or share personal data with third parties.</p>
<h2>Contact</h2>
<p>If you have any questions about this privacy policy, please contact us through our website.</p>
</div>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
