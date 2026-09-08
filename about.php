<?php
/**
 * Lyrics Ghar - About Page
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = 'About Us - ' . SITE_NAME;
$metaDescription = 'Learn about Lyrics Ghar, your destination for Islamic lyrics including Hamd, Naat, Nasheed, Ghazal and more.';
$activePage = 'about';
$bodyClass = 'about-page';

$breadcrumbs = [
    ['title' => 'About Us', 'url' => '']
];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/breadcrumbs.php';
?>

<section class="page-header">
    <div class="container">
        <h1 class="page-title">About Lyrics Ghar</h1>
    </div>
</section>

<section class="section section-content">
    <div class="container content-container">
        <div class="content-block">
            <h2>Our Mission</h2>
            <p>Lyrics Ghar is dedicated to preserving and sharing the beautiful tradition of Islamic vocal arts. We believe that the words of Hamd, Naat, Nasheed, and Ghazal carry profound spiritual meaning that deserves to be accessible to everyone.</p>
            <p>Our platform serves as a comprehensive database where you can discover, read, and learn the lyrics of your favorite Islamic songs across multiple languages including Bengali, Arabic, English, Urdu, and Hindi.</p>
        </div>

        <div class="content-block">
            <h2>What We Offer</h2>
            <ul>
                <li>Complete lyrics database of Islamic songs</li>
                <li>Multiple categories: Hamd, Naat, Ghazal, Nasheed, and more</li>
                <li>Multi-language support with proper text direction</li>
                <li>Artist profiles and biographies</li>
                <li>YouTube video integration</li>
                <li>Easy copy, share, and print features</li>
                <li>Mobile-optimized reading experience</li>
            </ul>
        </div>

        <div class="content-block">
            <h2>Our Values</h2>
            <p>We are committed to sharing only permissible Islamic content. All lyrics on our platform are carefully curated to ensure they align with Islamic principles and contribute positively to spiritual growth.</p>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
