<?php
require_once '../config/db.php';

// Check login
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get user info
$userQuery = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userQuery->execute([$userId]);
$user = $userQuery->fetch();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

$status = $_GET['status'] ?? '';

// Build WHERE clause
$where = ["user_id = ?"];
$params = [$userId];

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = $pdo->prepare("SELECT COUNT(*) FROM events WHERE $whereClause");
$countQuery->execute($params);
$totalRecords = $countQuery->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get events
$eventsQuery = $pdo->prepare("
    SELECT e.*,
           (SELECT COUNT(*) FROM donations WHERE event_id = e.id AND status = 'completed') as donor_count,
           (SELECT COUNT(*) FROM event_volunteers WHERE event_id = e.id AND status = 'approved') as volunteer_count
    FROM events e
    WHERE $whereClause
    ORDER BY e.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$eventsQuery->execute($params);
$events = $eventsQuery->fetchAll();

// Get statistics
$statsQuery = $pdo->prepare("
    SELECT 
        COUNT(*) as total_events,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        COALESCE(SUM(current_amount), 0) as total_raised
    FROM events 
    WHERE user_id = ?
");
$statsQuery->execute([$userId]);
$stats = $statsQuery->fetch();

$pageTitle = 'Sự kiện của tôi - ' . $user['fullname'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if ($user['avatar']): ?>
                        <?php
$thumb = $user['avatar'];
if (strpos($thumb, 'avatars/') !== 0) {
    $thumb = 'avatars/' . $thumb;
}
?>
<img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($thumb) ?>"
                             class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                        <?php else: ?>
                        <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <h6 class="mb-0"><?= sanitize($user['fullname']) ?></h6>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-3">
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i>Tổng quan
                    </a>
                    <a href="my_donations.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-hand-holding-heart me-2"></i>Quyên góp
                    </a>
                    <a href="my_volunteers.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-hands-helping me-2"></i>Tình nguyện
                    </a>
                    <a href="my_events.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-calendar-alt me-2"></i>Sự kiện của tôi
                    </a>
                    <a href="settings.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog me-2"></i>Cài đặt
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-calendar-alt text-warning me-2"></i>
                        Sự kiện của tôi
                    </h4>
                    <p class="text-muted mb-0">Quản lý các chiến dịch bạn đã tạo</p>
                </div>
                <a href="<?= BASE_URL ?>/events/create_event.php" class="btn btn-danger">
                    <i class="fas fa-plus me-2"></i>Tạo sự kiện mới
                </a>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-calendar fa-2x"></i>
                            </div>
                            <h4 class="text-warning mb-1"><?= $stats['total_events'] ?></h4>
                            <p class="text-muted small mb-0">Tổng sự kiện</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h4 class="text-success mb-1"><?= $stats['approved'] ?></h4>
                            <p class="text-muted small mb-0">Đã duyệt</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h4 class="text-warning mb-1"><?= $stats['pending'] ?></h4>
                            <p class="text-muted small mb-0">Chờ duyệt</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-donate fa-2x"></i>
                            </div>
                            <h6 class="text-primary mb-1"><?= formatMoney($stats['total_raised']) ?></h6>
                            <p class="text-muted small mb-0">Đã quyên góp</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small">Trạng thái</label>
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Tất cả</option>
                                <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                                <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                                <option value="rejected" <?= $status == 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                            </select>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <a href="my_events.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-redo me-1"></i>Xóa bộ lọc
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Events List -->
            <?php if (!empty($events)): ?>
            <div class="row g-4">
                <?php foreach ($events as $event): 
                    $progress = ($event['target_amount'] > 0) ? ($event['current_amount'] / $event['target_amount'] * 100) : 0;
                ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="position-relative">
                            <?php
                            $thumb = $event['thumbnail'];
                            if (strpos($thumb, 'events/') !== 0) {
                                $thumb = 'events/' . $thumb;
                            }
                            ?>
                            <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($thumb) ?>" 
                                 class="card-img-top" 
                                 style="height: 200px; object-fit: cover;">
                            <?php
                            $statusClass = ['approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger'];
                            $statusText = ['approved' => 'Đã duyệt', 'pending' => 'Chờ duyệt', 'rejected' => 'Từ chối'];
                            ?>
                            <span class="badge bg-<?= $statusClass[$event['status']] ?> position-absolute top-0 end-0 m-2">
                                <?= $statusText[$event['status']] ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?= sanitize($event['title']) ?></h5>
                            <p class="text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i><?= sanitize($event['location']) ?>
                            </p>
                            
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?= min($progress, 100) ?>%"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <small class="text-muted">Đã quyên góp</small>
                                    <div class="fw-bold text-success"><?= formatMoney($event['current_amount']) ?></div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">Mục tiêu</small>
                                    <div class="fw-bold"><?= formatMoney($event['target_amount']) ?></div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3 text-muted small">
                                <span><i class="fas fa-users me-1"></i><?= $event['donor_count'] ?> người quyên góp</span>
                                <span><i class="fas fa-hands-helping me-1"></i><?= $event['volunteer_count'] ?> tình nguyện</span>
                            </div>
                            
                            <?php if ($event['status'] == 'rejected' && $event['rejection_reason']): ?>
                            <div class="alert alert-danger alert-sm">
                                <strong>Lý do từ chối:</strong> <?= sanitize($event['rejection_reason']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>/events/event_detail.php?slug=<?= $event['slug'] ?>" 
                                   class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="fas fa-eye me-1"></i>Xem
                                </a>
                                <?php if ($event['status'] == 'approved'): ?>
                                <a href="<?= BASE_URL ?>/events/edit_event.php?id=<?= $event['id'] ?>" 
                                   class="btn btn-sm btn-outline-warning flex-fill">
                                    <i class="fas fa-edit me-1"></i>Sửa
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $status ? '&status=' . $status : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $status ? '&status=' . $status : '' ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $status ? '&status=' . $status : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-calendar-plus fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted">Chưa có sự kiện nào</h5>
                    <p class="text-muted mb-4">Tạo chiến dịch từ thiện đầu tiên của bạn</p>
                    <a href="<?= BASE_URL ?>/events/create_event.php" class="btn btn-danger">
                        <i class="fas fa-plus me-2"></i>Tạo sự kiện mới
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
