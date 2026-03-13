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
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

// Build WHERE clause
$where = ["d.user_id = ?"];
$params = [$userId];

if ($status) {
    $where[] = "d.status = ?";
    $params[] = $status;
}

if ($fromDate) {
    $where[] = "DATE(d.created_at) >= ?";
    $params[] = $fromDate;
}

if ($toDate) {
    $where[] = "DATE(d.created_at) <= ?";
    $params[] = $toDate;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = $pdo->prepare("
    SELECT COUNT(*) 
    FROM donations d
    WHERE $whereClause
");
$countQuery->execute($params);
$totalRecords = $countQuery->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get donations
$donationsQuery = $pdo->prepare("
    SELECT d.*, e.title as event_title, e.slug as event_slug, e.thumbnail
    FROM donations d
    JOIN events e ON d.event_id = e.id
    WHERE $whereClause
    ORDER BY d.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$donationsQuery->execute($params);
$donations = $donationsQuery->fetchAll();

// Get statistics
$statsQuery = $pdo->prepare("
    SELECT 
        COUNT(*) as total_donations,
        COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) as total_amount,
        COUNT(DISTINCT event_id) as events_count
    FROM donations 
    WHERE user_id = ?
");
$statsQuery->execute([$userId]);
$stats = $statsQuery->fetch();

$pageTitle = 'Lịch sử quyên góp - ' . $user['fullname'];
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
                    <a href="my_donations.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-hand-holding-heart me-2"></i>Quyên góp
                    </a>
                    <a href="my_volunteers.php" class="list-group-item list-group-item-action">
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
                        <i class="fas fa-hand-holding-heart text-success me-2"></i>
                        Lịch sử quyên góp
                    </h4>
                    <p class="text-muted mb-0">Quản lý các khoản quyên góp của bạn</p>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-coins fa-2x"></i>
                            </div>
                            <h4 class="text-success mb-1"><?= formatMoney($stats['total_amount']) ?></h4>
                            <p class="text-muted small mb-0">Tổng quyên góp</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-donate fa-2x"></i>
                            </div>
                            <h4 class="text-primary mb-1"><?= $stats['total_donations'] ?></h4>
                            <p class="text-muted small mb-0">Lần quyên góp</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-heart fa-2x"></i>
                            </div>
                            <h4 class="text-warning mb-1"><?= $stats['events_count'] ?></h4>
                            <p class="text-muted small mb-0">Chiến dịch hỗ trợ</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small">Trạng thái</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Tất cả</option>
                                <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Thành công</option>
                                <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Đang xử lý</option>
                                <option value="failed" <?= $status == 'failed' ? 'selected' : '' ?>>Thất bại</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Từ ngày</label>
                            <input type="date" name="from_date" class="form-control form-control-sm" value="<?= $fromDate ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Đến ngày</label>
                            <input type="date" name="to_date" class="form-control form-control-sm" value="<?= $toDate ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-filter me-1"></i>Lọc
                            </button>
                            <a href="my_donations.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-redo me-1"></i>Xóa
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Donations Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <?php if (!empty($donations)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Chiến dịch</th>
                                    <th>Số tiền</th>
                                    <th>Ngày quyên góp</th>
                                    <th>Trạng thái</th>
                                    <th class="pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php
                                            $thumb = $donation['thumbnail'];
                                            if (strpos($thumb, 'events/') !== 0) {
                                                $thumb = 'events/' . $thumb;
                                            }
                                            ?>
                                            <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($thumb) ?>" 
                                                 class="rounded me-3" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <a href="<?= BASE_URL ?>/events/event_detail.php?slug=<?= $donation['event_slug'] ?>" 
                                                   class="text-dark text-decoration-none fw-500">
                                                    <?= sanitize(substr($donation['event_title'], 0, 50)) ?>...
                                                </a>
                                                <?php if ($donation['is_anonymous']): ?>
                                                <span class="badge bg-secondary small ms-1">Ẩn danh</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold"><?= formatMoney($donation['amount']) ?></span>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($donation['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger'
                                        ];
                                        $statusText = [
                                            'completed' => 'Thành công',
                                            'pending' => 'Đang xử lý',
                                            'failed' => 'Thất bại'
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $statusClass[$donation['status']] ?>">
                                            <?= $statusText[$donation['status']] ?>
                                        </span>
                                    </td>
                                    <td class="pe-4">
                                        <a href="<?= BASE_URL ?>/events/event_detail.php?slug=<?= $donation['event_slug'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white border-top">
                        <nav>
                            <ul class="pagination pagination-sm mb-0 justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?><?= $status ? '&status=' . $status : '' ?><?= $fromDate ? '&from_date=' . $fromDate : '' ?><?= $toDate ? '&to_date=' . $toDate : '' ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= $status ? '&status=' . $status : '' ?><?= $fromDate ? '&from_date=' . $fromDate : '' ?><?= $toDate ? '&to_date=' . $toDate : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?><?= $status ? '&status=' . $status : '' ?><?= $fromDate ? '&from_date=' . $fromDate : '' ?><?= $toDate ? '&to_date=' . $toDate : '' ?>">
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
                            <i class="fas fa-hand-holding-heart fa-4x text-muted"></i>
                        </div>
                        <h5 class="text-muted">Chưa có quyên góp nào</h5>
                        <p class="text-muted mb-4">Hãy bắt đầu chia sẻ yêu thương với những chiến dịch từ thiện</p>
                        <a href="<?= BASE_URL ?>/events/events.php" class="btn btn-danger">
                            <i class="fas fa-heart me-2"></i>Khám phá chiến dịch
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
