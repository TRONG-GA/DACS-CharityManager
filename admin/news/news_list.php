<?php
/**
 * Admin - News Management
 * Quản lý tin tức
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Quản lý tin tức';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(n.title LIKE ? OR n.excerpt LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "n.status = ?";
    $params[] = $status;
}

if ($category) {
    $where[] = "n.category = ?";
    $params[] = $category;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = "SELECT COUNT(*) FROM news n WHERE $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalNews = $stmt->fetchColumn();
$totalPages = ceil($totalNews / $perPage);

// Get news
$query = "SELECT n.*, u.fullname as author_name
          FROM news n
          JOIN users u ON n.author_id = u.id
          WHERE $whereClause
          ORDER BY n.created_at DESC
          LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$newsList = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn(),
    'draft' => $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'draft'")->fetchColumn(),
    'published' => $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
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
                    <h1 class="h2"><i class="bi bi-newspaper"></i> <?= $pageTitle ?></h1>
                    <div class="btn-toolbar">
                        <a href="create_news.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Tạo tin mới
                        </a>
                    </div>
                </div>

                <?php 
                $flash = getFlashMessage();
                if ($flash): 
                ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted">Tổng số tin</h6>
                                <h3><?= number_format($stats['total']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="text-muted">Bản nháp</h6>
                                <h3 class="text-warning"><?= number_format($stats['draft']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-muted">Đã xuất bản</h6>
                                <h3 class="text-success"><?= number_format($stats['published']) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Tìm theo tiêu đề..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="draft" <?= $status == 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                                    <option value="published" <?= $status == 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                                    <option value="archived" <?= $status == 'archived' ? 'selected' : '' ?>>Lưu trữ</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="">Tất cả danh mục</option>
                                    <option value="announcement" <?= $category == 'announcement' ? 'selected' : '' ?>>Thông báo</option>
                                    <option value="news" <?= $category == 'news' ? 'selected' : '' ?>>Tin tức</option>
                                    <option value="success_story" <?= $category == 'success_story' ? 'selected' : '' ?>>Câu chuyện</option>
                                    <option value="guide" <?= $category == 'guide' ? 'selected' : '' ?>>Hướng dẫn</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Lọc
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- News Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Danh sách tin tức (<?= number_format($totalNews) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Tiêu đề</th>
                                        <th>Danh mục</th>
                                        <th>Tác giả</th>
                                        <th>Lượt xem</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th style="width: 150px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($newsList) > 0): ?>
                                        <?php foreach ($newsList as $news): ?>
                                        <tr>
                                            <td><?= $news['id'] ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($news['thumbnail']): ?>
                                                    <img src="<?= BASE_URL ?>/public/uploads/news/<?= $news['thumbnail'] ?>" 
                                                         alt="Thumbnail" class="me-2" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($news['title']) ?></strong>
                                                        <?php if ($news['is_featured']): ?>
                                                        <span class="badge bg-warning text-dark ms-1">Nổi bật</span>
                                                        <?php endif; ?>
                                                        <?php if ($news['is_breaking']): ?>
                                                        <span class="badge bg-danger ms-1">Breaking</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $categoryLabels = [
                                                    'announcement' => 'Thông báo',
                                                    'news' => 'Tin tức',
                                                    'success_story' => 'Câu chuyện',
                                                    'guide' => 'Hướng dẫn'
                                                ];
                                                ?>
                                                <span class="badge bg-secondary"><?= $categoryLabels[$news['category']] ?></span>
                                            </td>
                                            <td><small><?= htmlspecialchars($news['author_name']) ?></small></td>
                                            <td><small><?= number_format($news['views']) ?></small></td>
                                            <td>
                                                <?php
                                                $statusColors = [
                                                    'draft' => 'warning',
                                                    'published' => 'success',
                                                    'archived' => 'secondary'
                                                ];
                                                $statusLabels = [
                                                    'draft' => 'Bản nháp',
                                                    'published' => 'Xuất bản',
                                                    'archived' => 'Lưu trữ'
                                                ];
                                                ?>
                                                <span class="badge bg-<?= $statusColors[$news['status']] ?>">
                                                    <?= $statusLabels[$news['status']] ?>
                                                </span>
                                            </td>
                                            <td><small><?= formatDate($news['created_at'], 'd/m/Y') ?></small></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit_news.php?id=<?= $news['id'] ?>" 
                                                       class="btn btn-outline-primary" title="Chỉnh sửa">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>/news/<?= $news['slug'] ?>" 
                                                       class="btn btn-outline-info" target="_blank" title="Xem">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="delete_news.php?id=<?= $news['id'] ?>" 
                                                       class="btn btn-outline-danger" title="Xóa"
                                                       onclick="return confirm('Bạn có chắc muốn xóa tin này?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                Không tìm thấy tin tức nào
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&category=<?= $category ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&category=<?= $category ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&category=<?= $category ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
