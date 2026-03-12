<?php
/**
 * Admin Dashboard, admin/dashboard.php
 * Hiển thị tổng quan hệ thống, thống kê và hoạt động gần đây
 */
require_once '../config/db.php';
require_once '../includes/security.php';

requireAdmin();

$pageTitle = 'Admin Dashboard';

// Lấy thống kê tổng quan
$stats = getStatistics();

// Lấy thống kê nâng cao
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$newUsersThisMonth = $pdo->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetchColumn();

$pendingBenefactors = $pdo->query("SELECT COUNT(*) FROM benefactor_applications WHERE status = 'pending'")->fetchColumn();
$pendingEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'pending'")->fetchColumn();
$pendingDonations = $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'pending'")->fetchColumn();
$pendingVolunteers = $pdo->query("SELECT COUNT(*) FROM event_volunteers WHERE status = 'pending'")->fetchColumn();

// Quyên góp trong tháng
$donationsThisMonth = $pdo->query("
    SELECT COALESCE(SUM(amount), 0) FROM donations 
    WHERE status = 'completed' 
    AND MONTH(created_at) = MONTH(NOW()) 
    AND YEAR(created_at) = YEAR(NOW())
")->fetchColumn();

// Hoạt động gần đây
$recentActivities = $pdo->query("
    (SELECT 'donation' as type, id, user_id, event_id, amount as value, created_at, NULL as title
     FROM donations WHERE status = 'completed' ORDER BY created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'event' as type, id, user_id, NULL as event_id, NULL as value, created_at, title
     FROM events WHERE status = 'approved' ORDER BY created_at DESC LIMIT 5)
    ORDER BY created_at DESC LIMIT 10
")->fetchAll();

// Sự kiện sắp diễn ra
$upcomingEvents = $pdo->query("
    SELECT e.*, u.fullname as benefactor_name
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.status = 'approved' AND e.start_date >= CURDATE()
    ORDER BY e.start_date ASC
    LIMIT 5
")->fetchAll();

// Top nhà hảo tâm theo quyên góp
$topBenefactors = $pdo->query("
    SELECT u.id, u.fullname, u.avatar, COUNT(e.id) as event_count, 
           COALESCE(SUM(d.amount), 0) as total_raised
    FROM users u
    LEFT JOIN events e ON u.id = e.user_id AND e.status = 'approved'
    LEFT JOIN donations d ON e.id = d.event_id AND d.status = 'completed'
    WHERE u.benefactor_status = 'approved'
    GROUP BY u.id
    ORDER BY total_raised DESC
    LIMIT 5
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin.css">
    <style>
        .stat-card {
            border-left: 4px solid #0d6efd;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-card.warning {
            border-left-color: #ffc107;
        }
        .stat-card.success {
            border-left-color: #198754;
        }
        .stat-card.danger {
            border-left-color: #dc3545;
        }
        .activity-item {
            border-left: 3px solid #e9ecef;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        .activity-item:hover {
            border-left-color: #0d6efd;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-speedometer2"></i> Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Làm mới
                            </button>
                        </div>
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

                <!-- Thống kê chính -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stat-card success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1">Tổng quyên góp</h6>
                                        <h3 class="mb-0"><?= formatMoney($stats['total_donations']) ?></h3>
                                        <small class="text-success">
                                            <i class="bi bi-arrow-up"></i> Tháng này: <?= formatMoney($donationsThisMonth) ?>
                                        </small>
                                    </div>
                                    <div class="text-success" style="font-size: 2.5rem;">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1">Sự kiện</h6>
                                        <h3 class="mb-0"><?= number_format($stats['total_events']) ?></h3>
                                        <small class="text-primary">
                                            <i class="bi bi-calendar-event"></i> Đang hoạt động
                                        </small>
                                    </div>
                                    <div class="text-primary" style="font-size: 2.5rem;">
                                        <i class="bi bi-calendar3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1">Người dùng</h6>
                                        <h3 class="mb-0"><?= number_format($totalUsers) ?></h3>
                                        <small class="text-info">
                                            <i class="bi bi-person-plus"></i> Mới tháng này: <?= $newUsersThisMonth ?>
                                        </small>
                                    </div>
                                    <div class="text-info" style="font-size: 2.5rem;">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1">Nhà hảo tâm</h6>
                                        <h3 class="mb-0"><?= number_format($stats['total_benefactors']) ?></h3>
                                        <small class="text-warning">
                                            <i class="bi bi-star"></i> Đã xác minh
                                        </small>
                                    </div>
                                    <div class="text-warning" style="font-size: 2.5rem;">
                                        <i class="bi bi-award"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cảnh báo cần xử lý -->
                <?php if ($pendingBenefactors > 0 || $pendingEvents > 0 || $pendingDonations > 0): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Cần xử lý</h5>
                            <div class="row">
                                <?php if ($pendingBenefactors > 0): ?>
                                <div class="col-md-3">
                                    <a href="<?= BASE_URL ?>/admin/benefactors/application_detail.php" class="text-decoration-none">
                                        <strong><?= $pendingBenefactors ?></strong> đơn đăng ký nhà hảo tâm
                                    </a>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($pendingEvents > 0): ?>
                                <div class="col-md-3">
                                    <a href="<?= BASE_URL ?>/admin/events/pending_events.php" class="text-decoration-none">
                                        <strong><?= $pendingEvents ?></strong> sự kiện chờ duyệt
                                    </a>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($pendingDonations > 0): ?>
                                <div class="col-md-3">
                                    <a href="<?= BASE_URL ?>/admin/donations/donations.php" class="text-decoration-none">
                                        <strong><?= $pendingDonations ?></strong> quyên góp chờ xác nhận
                                    </a>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($pendingVolunteers > 0): ?>
                                <div class="col-md-3">
                                    <a href="<?= BASE_URL ?>/admin/volunteers/" class="text-decoration-none">
                                        <strong><?= $pendingVolunteers ?></strong> tình nguyện viên chờ duyệt
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Sự kiện sắp diễn ra -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Sự kiện sắp diễn ra</h5>
                                <a href="<?= BASE_URL ?>/admin/events/all_events.php" class="btn btn-sm btn-outline-primary">
                                    Xem tất cả
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (count($upcomingEvents) > 0): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($upcomingEvents as $event): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="<?= BASE_URL ?>/admin/events/event_detail.php?id=<?= $event['id'] ?>" class="text-decoration-none">
                                                            <?= htmlspecialchars($event['title']) ?>
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-person"></i> <?= htmlspecialchars($event['benefactor_name']) ?> |
                                                        <i class="bi bi-calendar"></i> <?= formatDate($event['start_date']) ?>
                                                    </small>
                                                    <div class="mt-1">
                                                        <span class="badge bg-info">
                                                            <?= formatMoney($event['current_amount']) ?> / <?= formatMoney($event['target_amount']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center py-3">Không có sự kiện sắp diễn ra</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Top nhà hảo tâm -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-star"></i> Top nhà hảo tâm</h5>
                                <a href="<?= BASE_URL ?>/admin/benefactors/" class="btn btn-sm btn-outline-primary">
                                    Xem tất cả
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (count($topBenefactors) > 0): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($topBenefactors as $index => $benefactor): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="badge bg-primary rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                        <?= $index + 1 ?>
                                                    </span>
                                                </div>
                                                <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= $benefactor['avatar'] ?>" 
                                                     alt="Avatar" class="rounded-circle me-3" 
                                                     style="width: 40px; height: 40px; object-fit: cover;"
                                                     onerror="this.src='<?= BASE_URL ?>/public/uploads/avatars/avatar1.jpg'">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0">
                                                        <a href="<?= BASE_URL ?>/admin/users/user_detail.php?id=<?= $benefactor['id'] ?>" class="text-decoration-none">
                                                            <?= htmlspecialchars($benefactor['fullname']) ?>
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?= $benefactor['event_count'] ?> sự kiện | 
                                                        <span class="text-success fw-bold"><?= formatMoney($benefactor['total_raised']) ?></span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center py-3">Chưa có dữ liệu</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hoạt động gần đây -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-activity"></i> Hoạt động gần đây</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($recentActivities) > 0): ?>
                                    <?php foreach ($recentActivities as $activity): ?>
                                    <div class="activity-item">
                                        <?php if ($activity['type'] == 'donation'): ?>
                                            <i class="bi bi-currency-dollar text-success"></i>
                                            <strong>Quyên góp mới</strong>: <?= formatMoney($activity['value']) ?>
                                            <br>
                                            <small class="text-muted"><?= timeAgo($activity['created_at']) ?></small>
                                        <?php else: ?>
                                            <i class="bi bi-calendar-event text-primary"></i>
                                            <strong>Sự kiện mới</strong>: <?= htmlspecialchars($activity['title']) ?>
                                            <br>
                                            <small class="text-muted"><?= timeAgo($activity['created_at']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center py-3">Chưa có hoạt động nào</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/js/admin.js"></script>
</body>
</html>
