<?php
/**
 * Admin - Edit News
 * Chỉnh sửa tin tức
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$newsId = $_GET['id'] ?? 0;

// Get news info
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$newsId]);
$news = $stmt->fetch();

if (!$news) {
    setFlashMessage('error', 'Không tìm thấy tin tức');
    header('Location: news_list.php');
    exit;
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? 'news';
    $status = $_POST['status'] ?? 'draft';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBreaking = isset($_POST['is_breaking']) ? 1 : 0;
    
    if (empty($title)) {
        $errors[] = 'Tiêu đề không được để trống';
    }
    
    if (empty($content)) {
        $errors[] = 'Nội dung không được để trống';
    }
    
    // Handle thumbnail upload
    $thumbnail = $news['thumbnail'];
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = uploadImage($_FILES['thumbnail'], 'news');
        if ($uploadedFile) {
            // Delete old thumbnail
            if ($thumbnail) {
                deleteFile('news/' . $thumbnail);
            }
            $thumbnail = basename($uploadedFile);
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE news SET 
                    title = ?, excerpt = ?, content = ?, thumbnail = ?,
                    category = ?, status = ?, is_featured = ?, is_breaking = ?,
                    published_at = ?
                WHERE id = ?
            ");
            
            $publishedAt = $status === 'published' && !$news['published_at'] 
                ? date('Y-m-d H:i:s') 
                : $news['published_at'];
            
            $stmt->execute([
                $title, $excerpt, $content, $thumbnail,
                $category, $status, $isFeatured, $isBreaking,
                $publishedAt, $newsId
            ]);
            
            setFlashMessage('success', 'Đã cập nhật tin tức');
            header('Location: news_list.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa tin tức - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <a href="news_list.php" class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        Chỉnh sửa tin tức
                    </h1>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="title" 
                                               value="<?= htmlspecialchars($news['title']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Mô tả ngắn</label>
                                        <textarea class="form-control" name="excerpt" rows="3"><?= htmlspecialchars($news['excerpt'] ?? '') ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                        <textarea id="content" name="content" class="form-control"><?= htmlspecialchars($news['content']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Xuất bản</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Trạng thái</label>
                                        <select class="form-select" name="status">
                                            <option value="draft" <?= $news['status'] === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                                            <option value="published" <?= $news['status'] === 'published' ? 'selected' : '' ?>>Xuất bản</option>
                                            <option value="archived" <?= $news['status'] === 'archived' ? 'selected' : '' ?>>Lưu trữ</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Danh mục</label>
                                        <select class="form-select" name="category">
                                            <option value="announcement" <?= $news['category'] === 'announcement' ? 'selected' : '' ?>>Thông báo</option>
                                            <option value="news" <?= $news['category'] === 'news' ? 'selected' : '' ?>>Tin tức</option>
                                            <option value="success_story" <?= $news['category'] === 'success_story' ? 'selected' : '' ?>>Câu chuyện</option>
                                            <option value="guide" <?= $news['category'] === 'guide' ? 'selected' : '' ?>>Hướng dẫn</option>
                                        </select>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_featured" 
                                               id="featured" <?= $news['is_featured'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="featured">
                                            <i class="bi bi-star text-warning"></i> Tin nổi bật
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_breaking" 
                                               id="breaking" <?= $news['is_breaking'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="breaking">
                                            <i class="bi bi-lightning text-danger"></i> Breaking News
                                        </label>
                                    </div>

                                    <?php if ($news['published_at']): ?>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            Xuất bản: <?= formatDate($news['published_at'], 'd/m/Y H:i') ?>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-save"></i> Cập nhật tin tức
                                    </button>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Ảnh đại diện</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($news['thumbnail']): ?>
                                    <img src="<?= BASE_URL ?>/public/uploads/news/<?= $news['thumbnail'] ?>" 
                                         alt="Current" class="img-fluid rounded mb-3">
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="thumbnail" accept="image/*">
                                    <small class="text-muted">Để trống nếu không muốn thay đổi</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    tinymce.init({
        selector: '#content',
        height: 500,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        content_style: 'body { font-family: Arial, sans-serif; font-size:14px }'
    });
    </script>
</body>
</html>
