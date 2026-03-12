<?php
require_once '../config/db.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
$pageTitle = 'Danh sách sự kiện từ thiện - ' . SITE_NAME;
$pageDescription = 'Khám phá và tham gia các chiến dịch từ thiện đang diễn ra';

// =====================================================
// GET PARAMETERS
// =====================================================

$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$location = $_GET['location'] ?? '';
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'newest'; // newest, urgent, ending_soon, popular
$page = max(1, intval($_GET['page'] ?? 1));

// =====================================================
// BUILD QUERY
// =====================================================

$where = ["e.status = 'approved'"];
$params = [];

// Filter by category
if (!empty($category) && in_array($category, ['medical', 'education', 'disaster', 'children', 'elderly', 'community', 'environment', 'other'])) {
    $where[] = "e.category = ?";
    $params[] = $category;
}

// Filter by status
if ($status === 'ongoing') {
    $where[] = "e.start_date <= CURDATE() AND e.end_date >= CURDATE()";
} elseif ($status === 'upcoming') {
    $where[] = "e.start_date > CURDATE()";
} elseif ($status === 'completed') {
    $where[] = "e.end_date < CURDATE()";
}

// Filter by location
if (!empty($location)) {
    $where[] = "(e.province LIKE ? OR e.district LIKE ? OR e.location LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
    $params[] = "%$location%";
}

// Search
if (!empty($search)) {
    $where[] = "(e.title LIKE ? OR e.description LIKE ? OR e.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Build WHERE clause
$whereClause = implode(' AND ', $where);

// =====================================================
// SORTING
// =====================================================

$orderBy = match($sort) {
    'urgent' => "e.is_urgent DESC, e.created_at DESC",
    'ending_soon' => "e.end_date ASC",
    'popular' => "e.views DESC",
    'featured' => "e.is_featured DESC, e.created_at DESC",
    default => "e.created_at DESC" // newest
};

// =====================================================
// PAGINATION
// =====================================================

$perPage = 12;
$offset = ($page - 1) * $perPage;

// Count total
$countQuery = "SELECT COUNT(*) FROM events e WHERE $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalEvents = $stmt->fetchColumn();
$totalPages = ceil($totalEvents / $perPage);

// Get events
$query = "
    SELECT e.*, 
           u.fullname as organizer_name,
           (SELECT COUNT(*) FROM donations WHERE event_id = e.id AND status = 'completed') as donor_count,
           DATEDIFF(e.end_date, CURDATE()) as days_left
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE $whereClause
    ORDER BY $orderBy
    LIMIT $perPage OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll();

// =====================================================
// GET CATEGORIES FOR FILTER
// =====================================================

$categoriesQuery = $pdo->query("
    SELECT category, COUNT(*) as count
    FROM events
    WHERE status = 'approved'
    GROUP BY category
    ORDER BY count DESC
");
$categories = $categoriesQuery->fetchAll();

// =====================================================
// GET PROVINCES FOR FILTER
// =====================================================

$provincesQuery = $pdo->query("
    SELECT DISTINCT province
    FROM events
    WHERE status = 'approved' AND province IS NOT NULL
    ORDER BY province
");
$provinces = $provincesQuery->fetchAll(PDO::FETCH_COLUMN);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
.events-page {
    background: #f8f9fa;
    padding: 40px 0;
}

.page-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    padding: 60px 0 40px;
    margin-bottom: 40px;
}

.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}

.breadcrumb-item a {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: white;
}

.filter-section {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.filter-tag {
    display: inline-block;
    background: #dc3545;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    margin: 5px;
    font-size: 0.9rem;
}

.filter-tag .btn-close {
    margin-left: 8px;
    font-size: 0.7rem;
}

.event-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.event-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.event-card img {
    width: 100%;
    height: 250px;
    object-fit: cover;
}

.event-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.event-card .card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 10px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.event-card .card-text {
    font-size: 0.9rem;
    color: #666;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 15px;
}

.progress-wrapper {
    margin-top: auto;
}

.no-events {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
}

.no-events i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Trang chủ</a></li>
                <li class="breadcrumb-item active">Sự kiện</li>
            </ol>
        </nav>
        <h1>Chiến dịch từ thiện</h1>
        <p class="lead mb-0">Khám phá và tham gia các hoạt động từ thiện đang diễn ra</p>
    </div>
</div>

<!-- Main Content -->
<div class="events-page">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="filter-section">
                    <h5 class="mb-4">
                        <i class="fas fa-filter text-danger me-2"></i>Bộ lọc
                    </h5>
                    
                    <form method="GET" action="">
                        <!-- Search -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Tìm kiếm</label>
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Nhập từ khóa..."
                                   value="<?= sanitize($search) ?>">
                        </div>
                        
                        <!-- Category -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Danh mục</label>
                            <select name="category" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="medical" <?= $category === 'medical' ? 'selected' : '' ?>>Y tế & Sức khỏe</option>
                                <option value="education" <?= $category === 'education' ? 'selected' : '' ?>>Giáo dục</option>
                                <option value="disaster" <?= $category === 'disaster' ? 'selected' : '' ?>>Thiên tai & Khẩn cấp</option>
                                <option value="children" <?= $category === 'children' ? 'selected' : '' ?>>Trẻ em</option>
                                <option value="elderly" <?= $category === 'elderly' ? 'selected' : '' ?>>Người già</option>
                                <option value="community" <?= $category === 'community' ? 'selected' : '' ?>>Cộng đồng</option>
                                <option value="environment" <?= $category === 'environment' ? 'selected' : '' ?>>Môi trường</option>
                                <option value="other" <?= $category === 'other' ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>
                        
                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="ongoing" <?= $status === 'ongoing' ? 'selected' : '' ?>>Đang diễn ra</option>
                                <option value="upcoming" <?= $status === 'upcoming' ? 'selected' : '' ?>>Sắp diễn ra</option>
                                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Đã hoàn thành</option>
                            </select>
                        </div>
                        
                        <!-- Location -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Địa điểm</label>
                            <input type="text" 
                                   name="location" 
                                   class="form-control" 
                                   placeholder="Tỉnh/thành phố..."
                                   value="<?= sanitize($location) ?>">
                        </div>
                        
                        <!-- Sort -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Sắp xếp</label>
                            <select name="sort" class="form-select">
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                                <option value="urgent" <?= $sort === 'urgent' ? 'selected' : '' ?>>Khẩn cấp</option>
                                <option value="ending_soon" <?= $sort === 'ending_soon' ? 'selected' : '' ?>>Sắp kết thúc</option>
                                <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Phổ biến</option>
                                <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Nổi bật</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-search me-2"></i>Tìm kiếm
                            </button>
                            <a href="<?= BASE_URL ?>/events.php" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Đặt lại
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Quick Stats -->
                <div class="filter-section mt-3">
                    <h6 class="mb-3">
                        <i class="fas fa-chart-pie text-danger me-2"></i>Thống kê
                    </h6>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tổng sự kiện:</span>
                            <strong><?= $totalEvents ?></strong>
                        </div>
                        <?php
                        $stats = getStatistics();
                        if ($stats):
                        ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Đã quyên góp:</span>
                            <strong class="text-success"><?= formatMoney($stats['total_donations']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Tình nguyện viên:</span>
                            <strong class="text-primary"><?= $stats['total_volunteers'] ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Events List -->
            <div class="col-lg-9">
                <!-- Active Filters -->
                <?php if ($category || $status || $location || $search): ?>
                <div class="mb-3">
                    <span class="text-muted me-2">Bộ lọc đang áp dụng:</span>
                    <?php if ($category): ?>
                        <span class="filter-tag">
                            <?= ucfirst($category) ?>
                            <a href="<?= BASE_URL ?>/events.php?status=<?= $status ?>&location=<?= $location ?>&search=<?= $search ?>&sort=<?= $sort ?>" 
                               class="btn-close btn-close-white"></a>
                        </span>
                    <?php endif; ?>
                    <?php if ($status): ?>
                        <span class="filter-tag">
                            <?= $status === 'ongoing' ? 'Đang diễn ra' : ($status === 'upcoming' ? 'Sắp diễn ra' : 'Đã hoàn thành') ?>
                            <a href="<?= BASE_URL ?>/events.php?category=<?= $category ?>&location=<?= $location ?>&search=<?= $search ?>&sort=<?= $sort ?>" 
                               class="btn-close btn-close-white"></a>
                        </span>
                    <?php endif; ?>
                    <?php if ($location): ?>
                        <span class="filter-tag">
                            <?= sanitize($location) ?>
                            <a href="<?= BASE_URL ?>/events.php?category=<?= $category ?>&status=<?= $status ?>&search=<?= $search ?>&sort=<?= $sort ?>" 
                               class="btn-close btn-close-white"></a>
                        </span>
                    <?php endif; ?>
                    <?php if ($search): ?>
                        <span class="filter-tag">
                            "<?= sanitize($search) ?>"
                            <a href="<?= BASE_URL ?>/events.php?category=<?= $category ?>&status=<?= $status ?>&location=<?= $location ?>&sort=<?= $sort ?>" 
                               class="btn-close btn-close-white"></a>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Results Count -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">
                        Tìm thấy <strong class="text-danger"><?= $totalEvents ?></strong> sự kiện
                    </h5>
                    <div class="text-muted small">
                        Trang <?= $page ?>/<?= max(1, $totalPages) ?>
                    </div>
                </div>
                
                <!-- Events Grid -->
                <?php if (empty($events)): ?>
                    <div class="no-events">
                        <i class="fas fa-inbox"></i>
                        <h4>Không tìm thấy sự kiện nào</h4>
                        <p class="text-muted">Thử điều chỉnh bộ lọc hoặc tìm kiếm với từ khóa khác</p>
                        <a href="<?= BASE_URL ?>/events.php" class="btn btn-danger">
                            <i class="fas fa-redo me-2"></i>Xem tất cả sự kiện
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($events as $event): 
                            $progress = ($event['target_amount'] > 0) ? ($event['current_amount'] / $event['target_amount'] * 100) : 0;
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="event-card">
                                <div class="position-relative">
                                    <img src="<?= BASE_URL ?>/public/uploads/events/<?= $event['thumbnail'] ?>" 
                                         alt="<?= sanitize($event['title']) ?>"
                                         onerror="this.src='<?= BASE_URL ?>/public/images/placeholder.php'">
                                    
                                    <?php if ($event['is_urgent']): ?>
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                        <i class="fas fa-bolt me-1"></i>KHẨN CẤP
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($event['days_left'] > 0 && $event['days_left'] <= 7): ?>
                                    <span class="badge bg-warning text-dark position-absolute" style="top: 50px; right: 8px;">
                                        <i class="fas fa-clock me-1"></i>Còn <?= $event['days_left'] ?> ngày
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body p-3">
                                    <span class="category-badge category-<?= $event['category'] ?> small">
                                        <?= ucfirst($event['category']) ?>
                                    </span>
                                    
                                    <h5 class="card-title mt-2">
                                        <a href="<?= BASE_URL ?>/events/event_detail.php?slug=<?= $event['slug'] ?>" 
                                           class="text-dark text-decoration-none">
                                            <?= sanitize($event['title']) ?>
                                        </a>
                                    </h5>
                                    
                                    <p class="card-text text-muted small">
                                        <?= sanitize($event['description']) ?>
                                    </p>
                                    
                                    <div class="progress-wrapper">
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar bg-success" 
                                                 style="width: <?= min($progress, 100) ?>%">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between small mb-2">
                                            <span class="text-success fw-bold">
                                                <?= formatMoney($event['current_amount']) ?>
                                            </span>
                                            <span class="text-muted">
                                                <?= number_format($progress, 0) ?>%
                                            </span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between text-muted small">
                                            <span>
                                                <i class="fas fa-users me-1"></i><?= $event['donor_count'] ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-map-marker-alt me-1"></i><?= sanitize($event['province']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-white border-top-0 p-3">
                                    <a href="<?= BASE_URL ?>/events/event_detail.php?slug=<?= $event['slug'] ?>" 
                                       class="btn btn-danger w-100 btn-sm">
                                        <i class="fas fa-hand-holding-heart me-2"></i>Xem chi tiết
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
                                <a class="page-link" href="?page=<?= $page-1 ?>&category=<?= $category ?>&status=<?= $status ?>&location=<?= $location ?>&search=<?= $search ?>&sort=<?= $sort ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&category=<?= $category ?>&status=<?= $status ?>&location=<?= $location ?>&search=<?= $search ?>&sort=<?= $sort ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page+1 ?>&category=<?= $category ?>&status=<?= $status ?>&location=<?= $location ?>&search=<?= $search ?>&sort=<?= $sort ?>">
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
    </div>
</div>

<?php include '../includes/footer.php'; ?>