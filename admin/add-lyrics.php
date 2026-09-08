<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$categories = getCategories($pdo, false);
$artists = getArtists($pdo, false);
$languages = getLanguages($pdo, false);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrfToken();
    $title = trim($_POST['title'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? $title);
    $slug = ensureUniqueSlug($pdo, 'lyrics', $slug);
    $artistId = intval($_POST['artist_id'] ?? 0) ?: null;
    $categoryId = intval($_POST['category_id'] ?? 0) ?: null;
    $languageId = intval($_POST['language_id'] ?? 0) ?: null;
    $lyricsText = $_POST['lyrics'] ?? '';
    $youtubeId = trim($_POST['youtube_id'] ?? '');
    $description = $_POST['description'] ?? '';
    $theme = trim($_POST['theme'] ?? '');
    $writer = trim($_POST['writer'] ?? '');
    $composer = trim($_POST['composer'] ?? '');
    $vocalist = trim($_POST['vocalist'] ?? '');
    $production = trim($_POST['production'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $credits = $_POST['credits'] ?? '';
    $status = in_array($_POST['status'] ?? '', ['published','draft']) ? $_POST['status'] : 'draft';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDesc = trim($_POST['meta_description'] ?? '');
    $tagNames = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));

    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($lyricsText)) $errors[] = 'Lyrics content is required.';
    if ($youtubeId && !validateYouTubeId($youtubeId)) $errors[] = 'Invalid YouTube ID.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO lyrics (title, slug, artist_id, category_id, language_id, lyrics, youtube_id, description, theme, writer, composer, vocalist, production, publisher, credits, status, is_featured, meta_title, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $artistId, $categoryId, $languageId, $lyricsText, $youtubeId, $description, $theme, $writer, $composer, $vocalist, $production, $publisher, $credits, $status, $isFeatured, $metaTitle, $metaDesc]);
        $lyricId = $pdo->lastInsertId();

        // Handle tags
        foreach ($tagNames as $tagName) {
            $tagSlug = generateSlug($tagName);
            $stmt = $pdo->prepare("SELECT id FROM tags WHERE slug = ?");
            $stmt->execute([$tagSlug]);
            $tag = $stmt->fetch();
            if (!$tag) {
                $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
                $stmt->execute([$tagName, $tagSlug]);
                $tagId = $pdo->lastInsertId();
            } else {
                $tagId = $tag['id'];
            }
            $pdo->prepare("INSERT IGNORE INTO lyrics_tags (lyric_id, tag_id) VALUES (?, ?)")->execute([$lyricId, $tagId]);
        }

        setFlash('success', 'Lyric added successfully.');
        redirect(SITE_URL . '/admin/edit-lyrics.php?id=' . $lyricId);
    }
}

$pageTitle = 'Add Lyric - Admin';
$adminPage = 'lyrics';
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
<div class="admin-header"><h1>Add New Lyric</h1><a href="lyrics.php" class="btn btn-secondary">Back to List</a></div>
<div class="admin-content">
<?php if (!empty($errors)): ?>
<div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<form method="POST" class="admin-form">
<?php echo csrfField(); ?>
<div class="form-row">
<div class="form-group form-group-large"><label for="title">Title *</label><input type="text" id="title" name="title" value="<?php echo e($_POST['title'] ?? ''); ?>" required></div>
<div class="form-group"><label for="slug">Slug</label><input type="text" id="slug" name="slug" value="<?php echo e($_POST['slug'] ?? ''); ?>" placeholder="Auto-generated"></div>
</div>
<div class="form-row">
<div class="form-group"><label for="artist_id">Artist</label><select id="artist_id" name="artist_id"><option value="">-- Select --</option><?php foreach ($artists as $a): ?><option value="<?php echo $a['id']; ?>" <?php echo ($_POST['artist_id']??'')==$a['id']?'selected':''; ?>><?php echo e($a['name']); ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label for="category_id">Category</label><select id="category_id" name="category_id"><option value="">-- Select --</option><?php foreach ($categories as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo ($_POST['category_id']??'')==$c['id']?'selected':''; ?>><?php echo e($c['name']); ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label for="language_id">Language</label><select id="language_id" name="language_id"><option value="">-- Select --</option><?php foreach ($languages as $l): ?><option value="<?php echo $l['id']; ?>" <?php echo ($_POST['language_id']??'')==$l['id']?'selected':''; ?>><?php echo e($l['name']); ?></option><?php endforeach; ?></select></div>
</div>
<div class="form-group"><label for="lyrics">Lyrics *</label><textarea id="lyrics" name="lyrics" rows="12" required><?php echo e($_POST['lyrics'] ?? ''); ?></textarea></div>
<div class="form-row">
<div class="form-group"><label for="youtube_id">YouTube Video ID</label><input type="text" id="youtube_id" name="youtube_id" value="<?php echo e($_POST['youtube_id'] ?? ''); ?>" placeholder="e.g. dQw4w9WgXcQ"></div>
<div class="form-group"><label for="tags">Tags</label><input type="text" id="tags" name="tags" value="<?php echo e($_POST['tags'] ?? ''); ?>" placeholder="Comma separated"></div>
</div>
<div class="form-group"><label for="description">Description / About</label><textarea id="description" name="description" rows="4"><?php echo e($_POST['description'] ?? ''); ?></textarea></div>
<div class="form-group"><label for="theme">Theme / Main Idea</label><input type="text" id="theme" name="theme" value="<?php echo e($_POST['theme'] ?? ''); ?>"></div>
<div class="form-row">
<div class="form-group"><label for="vocalist">Vocalist</label><input type="text" id="vocalist" name="vocalist" value="<?php echo e($_POST['vocalist'] ?? ''); ?>"></div>
<div class="form-group"><label for="writer">Writer</label><input type="text" id="writer" name="writer" value="<?php echo e($_POST['writer'] ?? ''); ?>"></div>
<div class="form-group"><label for="composer">Composer</label><input type="text" id="composer" name="composer" value="<?php echo e($_POST['composer'] ?? ''); ?>"></div>
</div>
<div class="form-row">
<div class="form-group"><label for="production">Production</label><input type="text" id="production" name="production" value="<?php echo e($_POST['production'] ?? ''); ?>"></div>
<div class="form-group"><label for="publisher">Publisher</label><input type="text" id="publisher" name="publisher" value="<?php echo e($_POST['publisher'] ?? ''); ?>"></div>
</div>
<div class="form-group"><label for="credits">Additional Credits</label><textarea id="credits" name="credits" rows="3"><?php echo e($_POST['credits'] ?? ''); ?></textarea></div>
<div class="form-row">
<div class="form-group"><label for="meta_title">Meta Title</label><input type="text" id="meta_title" name="meta_title" value="<?php echo e($_POST['meta_title'] ?? ''); ?>"></div>
<div class="form-group"><label for="meta_description">Meta Description</label><input type="text" id="meta_description" name="meta_description" value="<?php echo e($_POST['meta_description'] ?? ''); ?>"></div>
</div>
<div class="form-row form-row-inline">
<div class="form-group"><label for="status">Status</label><select id="status" name="status"><option value="draft" <?php echo ($_POST['status']??'draft')=='draft'?'selected':''; ?>>Draft</option><option value="published" <?php echo ($_POST['status']??'')=='published'?'selected':''; ?>>Published</option></select></div>
<div class="form-group form-checkbox"><label><input type="checkbox" name="is_featured" value="1" <?php echo isset($_POST['is_featured'])?'checked':''; ?>> Featured</label></div>
</div>
<div class="form-actions"><button type="submit" class="btn btn-primary">Save Lyric</button><a href="lyrics.php" class="btn btn-secondary">Cancel</a></div>
</form>
</div>
</main>
</body>
</html>
