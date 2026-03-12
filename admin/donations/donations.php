<?php
/**
 * Admin - Donations Management
 * Danh sách tất cả quyên góp
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Quản lý quyên góp';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$eventId = $_GET['event_id'] ?? '';
$paymentMethod = $_GET['payment_method'] ?? '';

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(d.donor_name LIKE ? OR d.donor_email LIKE ? OR e.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "d.status = ?";
    $params[] = $status;
}

if ($eventId) {
    $where[] = "d.event_id = ?";
    $params[] = $eventId;
}

if ($paymentMethod) {
    $where[] = "d.payment_method = ?";
    $params[] = $paymentMethod;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = "SELECT COUNT(*) FROM donations d WHERE $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalDonations = $stmt->fetchColumn();
$totalPages = ceil($totalDonations / $perPage);

// Get donations
$query = "SELECT d.*, e.title as event_title, e.slug as event_slug, u.fullname as donor_fullname
          FROM donations d
          JOIN events e ON d.event_id = e.id
          LEFT JOIN users u ON d.user_id = u.id
          WHERE $whereClause
          ORDER BY d.created_at DESC
          LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$donations = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM donations")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'pending'")->fetchColumn(),
    'completed' => $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'completed'")->fetchColumn(),
    'failed' => $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'failed'")->fetchColumn(),
    'total_amount' => $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'completed'")->fetchColumn(),
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
                    <h1 class="h2"><i class="bi bi-currency-dollar"></i> <?= $pageTitle ?></h1>
                    <div class="btn-toolbar">
                        <a href="export_donations.php<?= $eventId ? '?event_id=' . $eventId : '' ?>" class="btn btn-success">
                            <i class="bi bi-file-excel"></i> Xuất Excel
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
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted">Tổng số</h6>
                                <h3><?= number_format($stats['total']) ?></h3>
                                <small class="text-success"><?= formatMoney($stats['total_amount']) ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="text-muted">Chờ xác nhận</h6>
                                <h3 class="text-warning"><?= number_format($stats['pending']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-muted">Thành công</h6>
                                <h3 class="text-success"><?= number_format($stats['completed']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-danger">
                            <div class="card-body">
                                <h6 class="text-muted">Thất bại</h6>
                                <h3 class="text-danger"><?= number_format($stats['failed']) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <?php if ($eventId): ?>
                            <input type="hidden" name="event_id" value="<?= $eventId ?>">
                            <?php endif; ?>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Tìm theo người góp, sự kiện..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                                    <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Thành công</option>
                                    <option value="failed" <?= $status == 'failed' ? 'selected' : '' ?>>Thất bại</option>
                                    <option value="refunded" <?= $status == 'refunded' ? 'selected' : '' ?>>Đã hoàn</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="payment_method">
                                    <option value="">Tất cả phương thức</option>
                                    <option value="bank_transfer" <?= $paymentMethod == 'bank_transfer' ? 'selected' : '' ?>>Chuyển khoản</option>
                                    <option value="momo" <?= $paymentMethod == 'momo' ? 'selected' : '' ?>>MoMo</option>
                                    <option value="vnpay" <?= $paymentMethod == 'vnpay' ? 'selected' : '' ?>>VNPay</option>
                                    <option value="zalopay" <?= $paymentMethod == 'zalopay' ? 'selected' : '' ?>>ZaloPay</option>
                                    <option value="cash" <?= $paymentMethod == 'cash' ? 'selected' : '' ?>>Tiền mặt</option>
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

                <!-- Donations Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Danh sách quyên góp (<?= number_format($totalDonations) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Người góp</th>
                                        <th>Sự kiện</th>
                                        <th>Số tiền</th>
                                        <th>Phương thức</th>
                                        <th>Trạng thái</th>
                                        <th>Thời gian</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($donations) > 0): ?>
                                        <?php foreach ($donations as $donation): ?>
                                        <tr>
                                            <td><?= $donation['id'] ?></td>
                                            <td>
                                                <?php if ($donation['is_anonymous']): ?>
                                                    <span class="text-muted">Ẩn danh</span>
                                                <?php else: ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($donation['donor_fullname'] ?? $donation['donor_name']) ?></strong>
                                                        <?php if ($donation['donor_email']): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($donation['donor_email']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="../events/event_detail.php?id=<?= $donation['event_id'] ?>">
                                                    <?= htmlspecialchars(truncate($donation['event_title'], 50)) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <strong class="text-success"><?= formatMoney($donation['amount']) ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $methodLabels = [
                                                    'bank_transfer' => 'Chuyển khoản',
                                                    'momo' => 'MoMo',
                                                    'vnpay' => 'VNPay',
                                                    'zalopay' => 'ZaloPay',
                                                    'cash' => 'Tiền mặt'
                                                ];
                                                ?>
                                                <small><?= $methodLabels[$donation['payment_method']] ?? $donation['payment_method'] ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'completed' => 'success',
                                                    'failed' => 'danger',
                                                    'refunded' => 'secondary'
                                                ];
                                                $statusLabels = [
                                                    'pending' => 'Chờ xác nhận',
                                                    'completed' => 'Thành công',
                                                    'failed' => 'Thất bại',
                                                    'refunded' => 'Đã hoàn'
                                                ];
                                                ?>
                                                <span class="badge bg-<?= $statusColors[$donation['status']] ?>">
                                                    <?= $statusLabels[$donation['status']] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= formatDate($donation['created_at'], 'd/m/Y H:i') ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($donation['payment_proof']): ?>
                                                    <a href="<?= BASE_URL ?>/public/uploads/<?= $donation['payment_proof'] ?>" 
                                                       class="btn btn-outline-info" target="_blank" title="Xem chứng từ">
                                                        <i class="bi bi-image"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <?php if ($donation['status'] === 'pending'): ?>
                                                    <a href="verify_donation.php?id=<?= $donation['id'] ?>" 
                                                       class="btn btn-outline-success" title="Xác minh">
                                                        <i class="bi bi-check-lg"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                Không tìm thấy quyên góp nào
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
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&payment_method=<?= $paymentMethod ?><?= $eventId ? '&event_id=' . $eventId : '' ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&payment_method=<?= $paymentMethod ?><?= $eventId ? '&event_id=' . $eventId : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&payment_method=<?= $paymentMethod ?><?= $eventId ? '&event_id=' . $eventId : '' ?>">
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
