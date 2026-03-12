<?php
require_once '../config/db.php';

$pageTitle = 'Tin tức - ' . SITE_NAME;

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Get category filter
$category = $_GET['category'] ?? '';

// Build query
$params = [];

// WHERE cho query COUNT (không có alias)
$whereCount = ["status = 'published'"];

// WHERE cho query SELECT (có alias n)
$whereSelect = ["n.status = 'published'"];

if (!empty($category) && in_array($category, ['success_story', 'event_report', 'announcement', 'urgent'])) {
    $whereCount[] = "category = ?";
    $whereSelect[] = "n.category = ?";
    $params[] = $category;
}

$whereClauseCount = implode(' AND ', $whereCount);
$whereClauseSelect = implode(' AND ', $whereSelect);

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM news WHERE $whereClauseCount");
$countStmt->execute($params);
$totalNews = $countStmt->fetchColumn();
$totalPages = ceil($totalNews / $perPage);

// Get news
$stmt = $pdo->prepare("
    SELECT n.*, u.fullname as author_name
    FROM news n
    JOIN users u ON n.author_id = u.id
    WHERE $whereClauseSelect
    ORDER BY n.published_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$newsList = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>



<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-white">Trang chủ</a></li>
                <li class="breadcrumb-item active text-white">Tin tức</li>
            </ol>
        </nav>
        <h1>Tin tức & Sự kiện</h1>
        <p class="lead mb-0">Cập nhật hoạt động và câu chuyện từ thiện</p>
    </div>
</div>

<div class="news-page">
    <div class="container">
        <!-- Filter -->
        <div class="mb-4">
            <a href="<?= BASE_URL ?>/news/news.php" class="btn btn-sm <?= empty($category) ? 'btn-primary' : 'btn-outline-primary' ?>">Tất cả</a>
            <a href="?category=success_story" class="btn btn-sm <?= $category === 'success_story' ? 'btn-primary' : 'btn-outline-primary' ?>">Câu chuyện thành công</a>
            <a href="?category=event_report" class="btn btn-sm <?= $category === 'event_report' ? 'btn-primary' : 'btn-outline-primary' ?>">Báo cáo sự kiện</a>
            <a href="?category=announcement" class="btn btn-sm <?= $category === 'announcement' ? 'btn-primary' : 'btn-outline-primary' ?>">Thông báo</a>
            <a href="?category=urgent" class="btn btn-sm <?= $category === 'urgent' ? 'btn-primary' : 'btn-outline-primary' ?>">Khẩn cấp</a>
        </div>

        <h5 class="mb-4">Tìm thấy <strong class="text-primary"><?= $totalNews ?></strong> tin tức</h5>

        <?php if (empty($newsList)): ?>
            <div class="text-center py-5">
                <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                <h4>Chưa có tin tức nào</h4>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($newsList as $news): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="news-card">
                        <img src="<?= BASE_URL ?>/public/uploads/news/<?= $news['thumbnail'] ?>" 
                             alt="<?= sanitize($news['title']) ?>"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22200%22%3E%3Crect width=%22400%22 height=%22200%22 fill=%22%23eee%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2220%22 fill=%22%23999%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                        
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="category-badge category-<?= $news['category'] ?>">
                                    <?php
                                    $catNames = [
                                        'success_story' => 'Câu chuyện thành công',
                                        'event_report' => 'Báo cáo sự kiện',
                                        'announcement' => 'Thông báo',
                                        'urgent' => 'Khẩn cấp'
                                    ];
                                    echo $catNames[$news['category']] ?? $news['category'];
                                    ?>
                                </span>
                            </div>

                            <h5 class="card-title mb-3">
                                <a href="<?= BASE_URL ?>/news/news_detail.php?slug=<?= $news['slug'] ?>" 
                                   class="text-dark text-decoration-none">
                                    <?= sanitize($news['title']) ?>
                                </a>
                            </h5>

                            <p class="text-muted mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= sanitize($news['summary']) ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                <span>
                                    <i class="fas fa-user me-1"></i><?= sanitize($news['author_name']) ?>
                                </span>
                                <span>
                                    <i class="fas fa-calendar me-1"></i><?= date('d/m/Y', strtotime($news['published_at'])) ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top-0 p-3">
                            <a href="<?= BASE_URL ?>/news/news_detail.php?slug=<?= $news['slug'] ?>" 
                               class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-arrow-right me-2"></i>Đọc tiếp
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page-1 ?><?= $category ? '&category='.$category : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $category ? '&category='.$category : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page+1 ?><?= $category ? '&category='.$category : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>