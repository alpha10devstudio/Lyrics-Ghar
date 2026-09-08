<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrfToken();
    $settings = [
        'site_title' => trim($_POST['site_title'] ?? ''),
        'site_tagline' => trim($_POST['site_tagline'] ?? ''),
        'site_description' => trim($_POST['site_description'] ?? ''),
        'site_email' => trim($_POST['site_email'] ?? ''),
        'posts_per_page' => trim($_POST['posts_per_page'] ?? '12'),
    ];
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    setFlash('success', 'Settings saved.');
    redirect(SITE_URL . '/admin/settings.php');
}

$currentSettings = [];
$stmt = $pdo->query("SELECT * FROM settings");
while ($row = $stmt->fetch()) { $currentSettings[$row['setting_key']] = $row['setting_value']; }

$pageTitle = 'Settings - Admin';
$adminPage = 'settings';
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($pageTitle); ?></title>
<link rel="stylesheet" href="<?php echo e(ASSETS_URL); ?>/css/admin.css?v=1">
</head>
<body class="admin-page">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="admin-main">
<div class="admin-header"><h1>Settings</h1></div>
<div class="admin-content">
<?php echo flashMessage(); ?>
<div class="admin-card" style="max-width:600px;">
<div class="admin-card-header"><h2>General Settings</h2></div>
<form method="POST" class="admin-form">
<?php echo csrfField(); ?>
<div class="form-group"><label>Site Title</label><input type="text" name="site_title" value="<?php echo e($currentSettings['site_title'] ?? 'Lyrics Ghar'); ?>"></div>
<div class="form-group"><label>Site Tagline</label><input type="text" name="site_tagline" value="<?php echo e($currentSettings['site_tagline'] ?? 'Islamic Lyrics, All in One Place'); ?>"></div>
<div class="form-group"><label>Site Description</label><textarea name="site_description" rows="3"><?php echo e($currentSettings['site_description'] ?? ''); ?></textarea></div>
<div class="form-group"><label>Contact Email</label><input type="email" name="site_email" value="<?php echo e($currentSettings['site_email'] ?? ''); ?>"></div>
<div class="form-group"><label>Posts Per Page</label><input type="number" name="posts_per_page" value="<?php echo e($currentSettings['posts_per_page'] ?? '12'); ?>"></div>
<div class="form-actions"><button type="submit" class="btn btn-primary">Save Settings</button></div>
</form>
</div>
</div>
</main>
</body>
</html>
