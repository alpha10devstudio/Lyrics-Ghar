<?php
/**
 * Lyrics Ghar - Helper Functions
 */

/**
 * Generate SEO-friendly slug
 */
function generateSlug($string) {
    $string = trim($string);
    $string = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    $string = strtolower($string);
    return empty($string) ? 'untitled' : $string;
}

/**
 * Ensure unique slug
 */
function ensureUniqueSlug($pdo, $table, $slug, $excludeId = null) {
    $originalSlug = $slug;
    $counter = 1;

    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ?";
        $params = [$slug];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if (!$stmt->fetch()) {
            return $slug;
        }

        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
}

/**
 * Escape HTML output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Truncate text
 */
function truncate($text, $length = 150) {
    $text = strip_tags($text);
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Get setting value
 */
function getSetting($pdo, $key, $default = '') {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

/**
 * Get all categories
 */
function getCategories($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM categories";
    if ($activeOnly) $sql .= " WHERE status = 'active'";
    $sql .= " ORDER BY sort_order, name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get all languages
 */
function getLanguages($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM languages";
    if ($activeOnly) $sql .= " WHERE status = 'active'";
    $sql .= " ORDER BY name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get all artists
 */
function getArtists($pdo, $activeOnly = true, $limit = null) {
    $sql = "SELECT a.*, (SELECT COUNT(*) FROM lyrics WHERE artist_id = a.id AND status = 'published') as lyrics_count FROM artists a";
    if ($activeOnly) $sql .= " WHERE a.status = 'active'";
    $sql .= " ORDER BY a.name";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get single category by slug
 */
function getCategoryBySlug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND status = 'active'");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Get single artist by slug
 */
function getArtistBySlug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT * FROM artists WHERE slug = ? AND status = 'active'");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Get single lyric by slug
 */
function getLyricBySlug($pdo, $slug) {
    $stmt = $pdo->prepare("
        SELECT l.*, 
               c.name as category_name, c.slug as category_slug,
               a.name as artist_name, a.slug as artist_slug, a.image as artist_image,
               lang.name as language_name, lang.slug as language_slug, lang.direction
        FROM lyrics l
        LEFT JOIN categories c ON l.category_id = c.id
        LEFT JOIN artists a ON l.artist_id = a.id
        LEFT JOIN languages lang ON l.language_id = lang.id
        WHERE l.slug = ? AND l.status = 'published'
    ");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Get tags for a lyric
 */
function getLyricTags($pdo, $lyricId) {
    $stmt = $pdo->prepare("
        SELECT t.* FROM tags t
        JOIN lyrics_tags lt ON t.id = lt.tag_id
        WHERE lt.lyric_id = ?
        ORDER BY t.name
    ");
    $stmt->execute([$lyricId]);
    return $stmt->fetchAll();
}

/**
 * Get related lyrics
 */
function getRelatedLyrics($pdo, $lyric, $limit = 6) {
    if (!$lyric) return [];

    $sql = "
        SELECT l.*, c.name as category_name, a.name as artist_name
        FROM lyrics l
        LEFT JOIN categories c ON l.category_id = c.id
        LEFT JOIN artists a ON l.artist_id = a.id
        WHERE l.id != ? AND l.status = 'published'
        AND (
            l.artist_id = ? OR l.category_id = ? OR l.language_id = ?
            OR l.id IN (
                SELECT lyric_id FROM lyrics_tags 
                WHERE tag_id IN (SELECT tag_id FROM lyrics_tags WHERE lyric_id = ?)
            )
        )
        ORDER BY 
            (l.artist_id = ?) DESC,
            (l.category_id = ?) DESC,
            l.views DESC
        LIMIT ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $lyric['id'], $lyric['artist_id'], $lyric['category_id'], 
        $lyric['language_id'], $lyric['id'],
        $lyric['artist_id'], $lyric['category_id'],
        (int)$limit
    ]);
    return $stmt->fetchAll();
}

/**
 * Increment view count with abuse prevention
 */
function incrementViews($pdo, $lyricId) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $userAgentHash = md5($userAgent);

    // Check if viewed in last 2 hours
    $stmt = $pdo->prepare("
        SELECT id FROM views_log 
        WHERE lyric_id = ? AND ip_address = ? AND user_agent = ? 
        AND viewed_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)
        LIMIT 1
    ");
    $stmt->execute([$lyricId, $ip, $userAgentHash]);

    if (!$stmt->fetch()) {
        // Log view
        $stmt = $pdo->prepare("INSERT INTO views_log (lyric_id, ip_address, user_agent) VALUES (?, ?, ?)");
        $stmt->execute([$lyricId, $ip, $userAgentHash]);

        // Increment counter
        $stmt = $pdo->prepare("UPDATE lyrics SET views = views + 1 WHERE id = ?");
        $stmt->execute([$lyricId]);
    }
}

/**
 * Clean old view logs (keep 7 days)
 */
function cleanOldViewLogs($pdo) {
    $stmt = $pdo->prepare("DELETE FROM views_log WHERE viewed_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute();
}

/**
 * Get featured lyrics
 */
function getFeaturedLyrics($pdo, $limit = 6) {
    $stmt = $pdo->prepare("
        SELECT l.*, c.name as category_name, c.slug as category_slug,
               a.name as artist_name, a.slug as artist_slug
        FROM lyrics l
        LEFT JOIN categories c ON l.category_id = c.id
        LEFT JOIN artists a ON l.artist_id = a.id
        WHERE l.is_featured = 1 AND l.status = 'published'
        ORDER BY l.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([(int)$limit]);
    return $stmt->fetchAll();
}

/**
 * Get latest lyrics
 */
function getLatestLyrics($pdo, $limit = 8) {
    $stmt = $pdo->prepare("
        SELECT l.*, c.name as category_name, c.slug as category_slug,
               a.name as artist_name, a.slug as artist_slug
        FROM lyrics l
        LEFT JOIN categories c ON l.category_id = c.id
        LEFT JOIN artists a ON l.artist_id = a.id
        WHERE l.status = 'published'
        ORDER BY l.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([(int)$limit]);
    return $stmt->fetchAll();
}

/**
 * Get popular lyrics
 */
function getPopularLyrics($pdo, $limit = 8) {
    $stmt = $pdo->prepare("
        SELECT l.*, c.name as category_name, c.slug as category_slug,
               a.name as artist_name, a.slug as artist_slug
        FROM lyrics l
        LEFT JOIN categories c ON l.category_id = c.id
        LEFT JOIN artists a ON l.artist_id = a.id
        WHERE l.status = 'published'
        ORDER BY l.views DESC, l.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([(int)$limit]);
    return $stmt->fetchAll();
}

/**
 * Get lyrics by category
 */
function getLyricsByCategory($pdo, $categoryId, $page = 1, $perPage = POSTS_PER_PAGE) {
    $offset = ($page - 1) * $perPage;

    $stmt = $pdo->prepare("
        SELECT l.*, a.name as artist_name, a.slug as artist_slug
        FROM lyrics l
        LEFT JOIN artists a ON l.artist_id = a.id
        WHERE l.category_id = ? AND l.status = 'published'
        ORDER BY l.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$categoryId, (int)$perPage, (int)$offset]);
    return $stmt->fetchAll();
}

/**
 * Get lyrics by artist
 */
function getLyricsByArtist($pdo, $artistId, $page = 1, $perPage = POSTS_PER_PAGE) {
    $offset = ($page - 1) * $perPage;

    $stmt = $pdo->prepare("
        SELECT l.*, c.name as category_name, c.slug as category_slug
        FROM lyrics l
        LEFT JOIN categories c ON l.category_id = c.id
        WHERE l.artist_id = ? AND l.status = 'published'
        ORDER BY l.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$artistId, (int)$perPage, (int)$offset]);
    return $stmt->fetchAll();
}

/**
 * Search lyrics
 */
function searchLyrics($pdo, $query, $filters = [], $page = 1, $perPage = POSTS_PER_PAGE) {
    $offset = ($page - 1) * $perPage;
    $params = [];
    $where = ["l.status = 'published'"];

    if (!empty($query)) {
        $where[] = "(l.title LIKE ? OR l.lyrics LIKE ? OR l.description LIKE ?)";
        $searchTerm = "%{$query}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($filters['category'])) {
        $where[] = "c.slug = ?";
        $params[] = $filters['category'];
    }

    if (!empty($filters['artist'])) {
        $where[] = "a.slug = ?";
        $params[] = $filters['artist'];
    }

    if (!empty($filters['language'])) {
        $where[] = "lang.slug = ?";
        $params[] = $filters['language'];
    }

    $whereClause = implode(' AND ', $where);

    // Count total
    $countSql = "SELECT COUNT(*) FROM lyrics l LEFT JOIN categories c ON l.category_id = c.id LEFT JOIN artists a ON l.artist_id = a.id LEFT JOIN languages lang ON l.language_id = lang.id WHERE {$whereClause}";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Get results
    $sql = "
        SELECT l.*, c.name as category_name, c.slug as category_slug,
               a.name as artist_name, a.slug as artist_slug,
               lang.name as language_name
        FROM lyrics l
        LEFT JOIN categories c ON l.category_id = c.id
        LEFT JOIN artists a ON l.artist_id = a.id
        LEFT JOIN languages lang ON l.language_id = lang.id
        WHERE {$whereClause}
        ORDER BY l.created_at DESC
        LIMIT ? OFFSET ?
    ";

    $params[] = (int)$perPage;
    $params[] = (int)$offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    return [
        'results' => $results,
        'total' => $total,
        'pages' => ceil($total / $perPage),
        'page' => $page
    ];
}

/**
 * Generate pagination
 */
function generatePagination($page, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return '';

    $html = '<nav class="pagination" aria-label="Pagination"><div class="pagination-links">';

    if ($page > 1) {
        $prevUrl = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . ($page - 1);
        $html .= '<a href="' . e($prevUrl) . '" class="pagination-prev">Previous</a>';
    }

    $start = max(1, $page - 2);
    $end = min($totalPages, $page + 2);

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            $html .= '<span class="pagination-current">' . $i . '</span>';
        } else {
            $pageUrl = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . $i;
            $html .= '<a href="' . e($pageUrl) . '" class="pagination-link">' . $i . '</a>';
        }
    }

    if ($page < $totalPages) {
        $nextUrl = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . ($page + 1);
        $html .= '<a href="' . e($nextUrl) . '" class="pagination-next">Next</a>';
    }

    $html .= '</div></nav>';
    return $html;
}

/**
 * Get YouTube thumbnail
 */
function getYouTubeThumbnail($youtubeId, $quality = 'mqdefault') {
    if (!$youtubeId) return '';
    return "https://img.youtube.com/vi/{$youtubeId}/{$quality}.jpg";
}

/**
 * Get YouTube embed URL
 */
function getYouTubeEmbedUrl($youtubeId) {
    if (!$youtubeId) return '';
    return "https://www.youtube-nocookie.com/embed/{$youtubeId}?rel=0&modestbranding=1";
}

/**
 * Format lyrics with proper line breaks
 */
function formatLyrics($lyrics) {
    return nl2br(e($lyrics), false);
}

/**
 * Check if user is admin logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Redirect function
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

/**
 * Flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function flashMessage() {
    $flash = getFlash();
    if ($flash) {
        $class = $flash['type'] === 'error' ? 'alert-error' : 'alert-success';
        return '<div class="alert ' . $class . '">' . e($flash['message']) . '</div>';
    }
    return '';
}
