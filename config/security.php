<?php
/**
 * Lyrics Ghar - Security Functions
 */

/**
 * Generate CSRF Token
 */
function generateCsrfToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Get CSRF Token field
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
}

/**
 * Validate CSRF Token
 */
function validateCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return true;

    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    $sessionToken = $_SESSION[CSRF_TOKEN_NAME] ?? '';

    if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        die('Invalid security token. Please refresh the page and try again.');
    }
    return true;
}

/**
 * Sanitize input
 */
function sanitizeInput($input, $type = 'string') {
    if (is_array($input)) {
        return array_map(function($item) use ($type) {
            return sanitizeInput($item, $type);
        }, $input);
    }

    $input = trim($input);

    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT);
        case 'html':
            // Allow only safe HTML tags
            $allowed = '<br><p><div><span><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a>';
            return strip_tags($input, $allowed);
        default:
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Validate YouTube ID
 */
function validateYouTubeId($id) {
    return preg_match('/^[a-zA-Z0-9_-]{11}$/', $id);
}

/**
 * Validate slug
 */
function validateSlug($slug) {
    return preg_match('/^[a-z0-9-]+$/', $slug);
}

/**
 * Rate limiting check (simple implementation)
 */
function checkRateLimit($action = 'default', $maxAttempts = 10, $window = 300) {
    $key = 'rate_limit_' . $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $now = time();

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 1, 'start' => $now];
        return true;
    }

    if ($now - $_SESSION[$key]['start'] > $window) {
        $_SESSION[$key] = ['count' => 1, 'start' => $now];
        return true;
    }

    if ($_SESSION[$key]['count'] >= $maxAttempts) {
        return false;
    }

    $_SESSION[$key]['count']++;
    return true;
}

/**
 * Secure file upload
 */
function secureUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'], $maxSize = 2097152) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File too large. Max 2MB allowed.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, WebP allowed.'];
    }

    // Verify it's a real image
    if (!getimagesize($file['tmp_name'])) {
        return ['success' => false, 'error' => 'Invalid image file.'];
    }

    $extension = match($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'jpg'
    };

    $filename = bin2hex(random_bytes(16)) . '.' . $extension;

    return ['success' => true, 'filename' => $filename, 'mime' => $mimeType];
}

/**
 * Generate secure random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Security headers
 */
function setSecurityHeaders() {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://www.youtube-nocookie.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; frame-src https://www.youtube-nocookie.com; connect-src 'self';");
}

// Set security headers on every request
setSecurityHeaders();
