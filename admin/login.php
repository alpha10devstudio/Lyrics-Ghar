<?php
require_once __DIR__ . '/../config/config.php';
if (isAdminLoggedIn()) redirect(SITE_URL . '/admin/');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrfToken();
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash, name, role FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        if ($user && verifyPassword($password, $user['password_hash'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_role'] = $user['role'];
            $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
            session_regenerate_id(true);
            redirect(SITE_URL . '/admin/');
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - <?php echo e(SITE_NAME); ?></title>
<link rel="stylesheet" href="<?php echo e(ASSETS_URL); ?>/css/admin.css?v=1">
</head>
<body class="admin-login-page">
<div class="admin-login-wrapper">
<div class="admin-login-box">
<div class="admin-login-header">
<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
<h1>Lyrics Ghar</h1><p>Admin Panel</p>
</div>
<?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
<form method="POST" class="admin-login-form">
<?php echo csrfField(); ?>
<div class="form-group"><label for="username">Username or Email</label><input type="text" id="username" name="username" required autofocus autocomplete="username"></div>
<div class="form-group"><label for="password">Password</label><input type="password" id="password" name="password" required autocomplete="current-password"></div>
<button type="submit" class="btn btn-primary btn-block">Sign In</button>
</form>
<div class="admin-login-footer"><a href="<?php echo e(SITE_URL); ?>/">Back to Website</a></div>
</div>
</div>
</body>
</html>
