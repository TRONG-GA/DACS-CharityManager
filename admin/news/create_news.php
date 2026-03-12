<?php
/**
 * Admin - Create News
 * Tạo tin tức mới
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Tạo tin tức mới';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? ''; // Don't sanitize - allow HTML
    $category = $_POST['category'] ?? 'news';
    $status = $_POST['status'] ?? 'draft';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBreaking = isset($_POST['is_breaking']) ? 1 : 0;
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Tiêu đề không được để trống';
    }
    
    if (empty($content)) {
        $errors[] = 'Nội dung không được để trống';
    }
    
    // Generate slug
    $slug = generateSlug($title);
    
    // Check slug unique
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM news WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetchColumn() > 0) {
        $slug = $slug . '-' . time();
    }
    
    // Handle thumbnail upload
    $thumbnail = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = uploadImage($_FILES['thumbnail'], 'news');
        if ($uploadedFile) {
            $thumbnail = basename($uploadedFile);
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO news (
                    author_id, title, slug, excerpt, content, thumbnail,
                    category, status, is_featured, is_breaking,
                    published_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
            
            $stmt->execute([
                $_SESSION['user_id'],
                $title,
                $slug,
                $excerpt,
                $content,
                $thumbnail,
                $category,
                $status,
                $isFeatured,
                $isBreaking,
                $publishedAt
            ]);
            
            $newsId = $pdo->lastInsertId();
            
            setFlashMessage('success', 'Đã tạo tin tức thành công');
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
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- TinyMCE -->
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
                        <?= $pageTitle ?>
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
                            <!-- Title -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="title" 
                                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" 
                                               required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Mô tả ngắn</label>
                                        <textarea class="form-control" name="excerpt" rows="3"><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                                        <small class="text-muted">Hiển thị trong danh sách tin tức</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                        <textarea id="content" name="content" class="form-control"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <!-- Publish -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Xuất bản</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Trạng thái</label>
                                        <select class="form-select" name="status">
                                            <option value="draft">Bản nháp</option>
                                            <option value="published">Xuất bản ngay</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Danh mục</label>
                                        <select class="form-select" name="category">
                                            <option value="announcement">Thông báo</option>
                                            <option value="news" selected>Tin tức</option>
                                            <option value="success_story">Câu chuyện</option>
                                            <option value="guide">Hướng dẫn</option>
                                        </select>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="featured">
                                        <label class="form-check-label" for="featured">
                                            <i class="bi bi-star text-warning"></i> Tin nổi bật
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_breaking" id="breaking">
                                        <label class="form-check-label" for="breaking">
                                            <i class="bi bi-lightning text-danger"></i> Breaking News
                                        </label>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-save"></i> Lưu tin tức
                                    </button>
                                </div>
                            </div>

                            <!-- Thumbnail -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Ảnh đại diện</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <img id="thumbnailPreview" src="" alt="Preview" 
                                             class="img-fluid rounded d-none mb-2">
                                        <input type="file" class="form-control" name="thumbnail" 
                                               accept="image/*" onchange="previewImage(this, 'thumbnailPreview')">
                                        <small class="text-muted">Khuyến nghị: 1200x630px</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/js/admin.js"></script>
    <script>
    // TinyMCE Editor
    tinymce.init({
        selector: '#content',
        height: 500,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        content_style: 'body { font-family: Arial, sans-serif; font-size:14px }'
    });
    </script>
</body>
</html>
