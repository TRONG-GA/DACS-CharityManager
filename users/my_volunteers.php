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

// Pagination & Filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$status = $_GET['status'] ?? '';

// Build WHERE clause
$where = ["ev.user_id = ?"];
$params = [$userId];

if ($status) {
    $where[] = "ev.status = ?";
    $params[] = $status;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = $pdo->prepare("
    SELECT COUNT(*) 
    FROM event_volunteers ev
    WHERE $whereClause
");
$countQuery->execute($params);
$totalRecords = $countQuery->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get volunteer activities
$volunteersQuery = $pdo->prepare("
    SELECT ev.*, e.title as event_title, e.slug as event_slug, e.thumbnail, e.start_date, e.end_date, e.location
    FROM event_volunteers ev
    JOIN events e ON ev.event_id = e.id
    WHERE $whereClause
    ORDER BY ev.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$volunteersQuery->execute($params);
$volunteers = $volunteersQuery->fetchAll();

// Get statistics
$statsQuery = $pdo->prepare("
    SELECT 
        COUNT(*) as total_volunteers,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM event_volunteers 
    WHERE user_id = ?
");
$statsQuery->execute([$userId]);
$stats = $statsQuery->fetch();

$pageTitle = 'Hoạt động tình nguyện - ' . $user['fullname'];
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
                    <a href="my_volunteers.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-hands-helping me-2"></i>Tình nguyện
                    </a>
                    <a href="my_events.php" class="list-group-item list-group-item-action">
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
                        <i class="fas fa-hands-helping text-primary me-2"></i>
                        Hoạt động tình nguyện
                    </h4>
                    <p class="text-muted mb-0">Quản lý các hoạt động tình nguyện của bạn</p>
                </div>
                <a href="<?= BASE_URL ?>/events/events.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Đăng ký tình nguyện
                </a>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-clipboard-list fa-2x"></i>
                            </div>
                            <h4 class="text-primary mb-1"><?= $stats['total_volunteers'] ?></h4>
                            <p class="text-muted small mb-0">Tổng đăng ký</p>
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
                            <div class="text-danger mb-2">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                            <h4 class="text-danger mb-1"><?= $stats['rejected'] ?></h4>
                            <p class="text-muted small mb-0">Từ chối</p>
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
                            <a href="my_volunteers.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-redo me-1"></i>Xóa bộ lọc
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Volunteers List -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <?php if (!empty($volunteers)): ?>
                    <?php foreach ($volunteers as $vol): ?>
                    <div class="p-4 border-bottom">
                        <div class="row">
                            <div class="col-md-3">
                                <?php
                                $thumb = $vol['thumbnail'];
                                if (strpos($thumb, 'events/') !== 0) {
                                    $thumb = 'events/' . $thumb;
                                }
                                ?>
                                <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($thumb) ?>" 
                                     class="img-fluid rounded" 
                                     style="height: 150px; width: 100%; object-fit: cover;">
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1">
                                            <a href="<?= BASE_URL ?>/events/event_detail.php?slug=<?= $vol['event_slug'] ?>" 
                                               class="text-dark text-decoration-none">
                                                <?= sanitize($vol['event_title']) ?>
                                            </a>
                                        </h5>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i><?= sanitize($vol['location']) ?>
                                        </p>
                                    </div>
                                    <?php
                                    $statusClass = [
                                        'approved' => 'success',
                                        'pending' => 'warning',
                                        'rejected' => 'danger'
                                    ];
                                    $statusText = [
                                        'approved' => 'Đã duyệt',
                                        'pending' => 'Chờ duyệt',
                                        'rejected' => 'Từ chối'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $statusClass[$vol['status']] ?>">
                                        <?= $statusText[$vol['status']] ?>
                                    </span>
                                </div>
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <p class="mb-1 small text-muted">Thời gian sự kiện:</p>
                                        <p class="mb-0">
                                            <i class="fas fa-calendar text-primary me-1"></i>
                                            <?= date('d/m/Y', strtotime($vol['start_date'])) ?> - <?= date('d/m/Y', strtotime($vol['end_date'])) ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1 small text-muted">Ngày đăng ký:</p>
                                        <p class="mb-0">
                                            <i class="fas fa-clock text-success me-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($vol['created_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="mb-1 small text-muted">Kỹ năng đóng góp:</p>
                                    <p class="mb-0"><?= sanitize($vol['skills'] ?? 'Không có') ?></p>
                                </div>
                                
                                <?php if ($vol['status'] == 'rejected' && $vol['rejection_reason']): ?>
                                <div class="alert alert-danger alert-sm mb-3">
                                    <strong>Lý do từ chối:</strong> <?= sanitize($vol['rejection_reason']) ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="d-flex gap-2">
                                    <a href="<?= BASE_URL ?>/events/event_detail.php?slug=<?= $vol['event_slug'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                                    </a>
                                    <?php if ($vol['status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="cancelVolunteer(<?= $vol['id'] ?>)">
                                        <i class="fas fa-times me-1"></i>Hủy đăng ký
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white border-top">
                        <nav>
                            <ul class="pagination pagination-sm mb-0 justify-content-center">
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
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-hands-helping fa-4x text-muted"></i>
                        </div>
                        <h5 class="text-muted">Chưa có hoạt động tình nguyện nào</h5>
                        <p class="text-muted mb-4">Tham gia các chiến dịch để mang yêu thương đến cộng đồng</p>
                        <a href="<?= BASE_URL ?>/events/events.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Đăng ký tình nguyện
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelVolunteer(volunteerId) {
    if (confirm('Bạn có chắc muốn hủy đăng ký tình nguyện này?')) {
        // AJAX call to cancel
        fetch('<?= BASE_URL ?>/events/cancel_volunteer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ volunteer_id: volunteerId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi hủy đăng ký');
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
