<?php
/**
 * Admin - Benefactors Management
 * Quản lý nhà hảo tâm và đơn đăng ký
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Quản lý nhà hảo tâm';

// Get tab
$tab = $_GET['tab'] ?? 'approved';

// Statistics
$totalApproved = $pdo->query("SELECT COUNT(*) FROM users WHERE benefactor_status = 'approved'")->fetchColumn();
$totalPending = $pdo->query("SELECT COUNT(*) FROM benefactor_applications WHERE status = 'pending'")->fetchColumn();
$totalRejected = $pdo->query("SELECT COUNT(*) FROM benefactor_applications WHERE status = 'rejected'")->fetchColumn();

// Get data based on tab
if ($tab === 'approved') {
    // Approved benefactors
    $benefactors = $pdo->query("
        SELECT u.*, 
               COUNT(DISTINCT e.id) as total_events,
               COALESCE(SUM(d.amount), 0) as total_raised
        FROM users u
        LEFT JOIN events e ON u.id = e.user_id AND e.status = 'approved'
        LEFT JOIN donations d ON e.id = d.event_id AND d.status = 'completed'
        WHERE u.benefactor_status = 'approved'
        GROUP BY u.id
        ORDER BY total_raised DESC
    ")->fetchAll();
} elseif ($tab === 'pending') {
    // Pending applications
    $benefactors = $pdo->query("
        SELECT ba.*, u.fullname, u.email, u.phone, u.avatar
        FROM benefactor_applications ba
        JOIN users u ON ba.user_id = u.id
        WHERE ba.status = 'pending'
        ORDER BY ba.created_at DESC
    ")->fetchAll();
} else {
    // Rejected applications
    $benefactors = $pdo->query("
        SELECT ba.*, u.fullname, u.email, u.phone
        FROM benefactor_applications ba
        JOIN users u ON ba.user_id = u.id
        WHERE ba.status = 'rejected'
        ORDER BY ba.reviewed_at DESC
    ")->fetchAll();
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
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-award"></i> <?= $pageTitle ?></h1>
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

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-muted">Đã duyệt</h6>
                                <h3 class="text-success"><?= number_format($totalApproved) ?></h3>
                                <small>Nhà hảo tâm đang hoạt động</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="text-muted">Chờ duyệt</h6>
                                <h3 class="text-warning"><?= number_format($totalPending) ?></h3>
                                <small>Đơn đăng ký mới</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-danger">
                            <div class="card-body">
                                <h6 class="text-muted">Đã từ chối</h6>
                                <h3 class="text-danger"><?= number_format($totalRejected) ?></h3>
                                <small>Đơn không đủ điều kiện</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'approved' ? 'active' : '' ?>" 
                           href="?tab=approved">
                            <i class="bi bi-check-circle"></i> Đã duyệt (<?= $totalApproved ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'pending' ? 'active' : '' ?>" 
                           href="?tab=pending">
                            <i class="bi bi-clock-history"></i> Chờ duyệt (<?= $totalPending ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'rejected' ? 'active' : '' ?>" 
                           href="?tab=rejected">
                            <i class="bi bi-x-circle"></i> Đã từ chối (<?= $totalRejected ?>)
                        </a>
                    </li>
                </ul>

                <!-- Content -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <?php if ($tab === 'approved'): ?>
                                            <th>ID</th>
                                            <th>Nhà hảo tâm</th>
                                            <th>Email</th>
                                            <th>Sự kiện</th>
                                            <th>Tổng quyên góp</th>
                                            <th>Ngày duyệt</th>
                                            <th>Thao tác</th>
                                        <?php else: ?>
                                            <th>ID</th>
                                            <th>Người đăng ký</th>
                                            <th>Email</th>
                                            <th>Ngày đăng ký</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($benefactors) > 0): ?>
                                        <?php foreach ($benefactors as $benefactor): ?>
                                        <tr>
                                            <?php if ($tab === 'approved'): ?>
                                                <td><?= $benefactor['id'] ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= $benefactor['avatar'] ?>" 
                                                             alt="Avatar" class="rounded-circle me-2" 
                                                             style="width: 40px; height: 40px; object-fit: cover;"
                                                             onerror="this.src='<?= BASE_URL ?>/public/uploads/avatars/avatar1.jpg'">
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars($benefactor['fullname']) ?></div>
                                                            <?php if ($benefactor['phone']): ?>
                                                            <small class="text-muted"><?= htmlspecialchars($benefactor['phone']) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($benefactor['email']) ?></td>
                                                <td>
                                                    <span class="badge bg-primary"><?= $benefactor['total_events'] ?> sự kiện</span>
                                                </td>
                                                <td>
                                                    <span class="text-success fw-bold"><?= formatMoney($benefactor['total_raised']) ?></span>
                                                </td>
                                                <td>
                                                    <small><?= formatDate($benefactor['benefactor_verified_at']) ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="../users/user_detail.php?id=<?= $benefactor['id'] ?>" 
                                                           class="btn btn-outline-info" title="Chi tiết">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="../events/all_events.php?benefactor_id=<?= $benefactor['id'] ?>" 
                                                           class="btn btn-outline-primary" title="Xem sự kiện">
                                                            <i class="bi bi-calendar-event"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php else: ?>
                                                <td><?= $benefactor['id'] ?></td>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($benefactor['fullname']) ?></div>
                                                    <?php if (isset($benefactor['phone'])): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($benefactor['phone']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($benefactor['email']) ?></td>
                                                <td>
                                                    <small><?= formatDate($benefactor['created_at']) ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($tab === 'pending'): ?>
                                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Đã từ chối</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="application_detail.php?id=<?= $benefactor['id'] ?>" 
                                                           class="btn btn-outline-info" title="Xem chi tiết">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <?php if ($tab === 'pending'): ?>
                                                        <a href="approve_benefactor.php?id=<?= $benefactor['id'] ?>" 
                                                           class="btn btn-outline-success" 
                                                           onclick="return confirm('Duyệt đơn này?')"
                                                           title="Duyệt">
                                                            <i class="bi bi-check-lg"></i>
                                                        </a>
                                                        <a href="reject_benefactor.php?id=<?= $benefactor['id'] ?>" 
                                                           class="btn btn-outline-danger" 
                                                           title="Từ chối">
                                                            <i class="bi bi-x-lg"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <?php if ($tab === 'approved'): ?>
                                                    Chưa có nhà hảo tâm nào được duyệt
                                                <?php elseif ($tab === 'pending'): ?>
                                                    Không có đơn đăng ký nào chờ duyệt
                                                <?php else: ?>
                                                    Không có đơn đăng ký nào bị từ chối
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
