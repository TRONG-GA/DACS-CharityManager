<?php
/**
 * Admin - Delete News
 * Xóa tin tức
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

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete thumbnail if exists
        if ($news['thumbnail']) {
            deleteFile('news/' . $news['thumbnail']);
        }
        
        // Delete news
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$newsId]);
        
        setFlashMessage('success', 'Đã xóa tin tức');
        header('Location: news_list.php');
        exit;
        
    } catch (PDOException $e) {
        setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xóa tin tức - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
                        Xóa tin tức
                    </h1>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="bi bi-trash"></i> Xác nhận xóa</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($news['thumbnail']): ?>
                                <img src="<?= BASE_URL ?>/public/uploads/news/<?= $news['thumbnail'] ?>" 
                                     alt="Thumbnail" class="img-fluid rounded mb-3">
                                <?php endif; ?>
                                
                                <h5><?= htmlspecialchars($news['title']) ?></h5>
                                
                                <div class="alert alert-danger mt-3">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Cảnh báo:</strong> Hành động này không thể hoàn tác!
                                    Tin tức và ảnh đính kèm sẽ bị xóa vĩnh viễn.
                                </div>

                                <form method="POST">
                                    <div class="d-flex justify-content-between">
                                        <a href="news_list.php" class="btn btn-secondary">
                                            <i class="bi bi-x-lg"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa tin tức này?\n\nHành động này không thể hoàn tác!')">
                                            <i class="bi bi-trash"></i> Xác nhận xóa
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
