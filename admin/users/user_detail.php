<?php
/**
 * Admin - User Detail
 * Xem chi tiết thông tin user, hoạt động, quyên góp, tình nguyện
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$userId = $_GET['id'] ?? 0;

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'Không tìm thấy người dùng');
    header('Location: users.php');
    exit;
}

// Get statistics
$donationStats = $pdo->prepare("
    SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
    FROM donations WHERE user_id = ? AND status = 'completed'
")->execute([$userId])->fetch() ?: ['count' => 0, 'total' => 0];

$volunteerCount = $pdo->query("
    SELECT COUNT(*) FROM event_volunteers WHERE user_id = $userId
")->fetchColumn();

$eventsCreated = 0;
if ($user['role'] === 'benefactor') {
    $eventsCreated = $pdo->query("
        SELECT COUNT(*) FROM events WHERE user_id = $userId
    ")->fetchColumn();
}

// Get recent donations
$recentDonations = $pdo->prepare("
    SELECT d.*, e.title as event_title
    FROM donations d
    JOIN events e ON d.event_id = e.id
    WHERE d.user_id = ?
    ORDER BY d.created_at DESC
    LIMIT 10
")->execute([$userId])->fetchAll();

// Get volunteer activities
$volunteerActivities = $pdo->prepare("
    SELECT ev.*, e.title as event_title
    FROM event_volunteers ev
    JOIN events e ON ev.event_id = e.id
    WHERE ev.user_id = ?
    ORDER BY ev.created_at DESC
    LIMIT 10
")->execute([$userId])->fetchAll();

$pageTitle = 'Chi tiết người dùng';
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
                    <h1 class="h2">
                        <a href="users.php" class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <?= $pageTitle ?>
                    </h1>
                    <div class="btn-toolbar">
                        <a href="edit_user.php?id=<?= $userId ?>" class="btn btn-primary me-2">
                            <i class="bi bi-pencil"></i> Chỉnh sửa
                        </a>
                        <?php if ($user['status'] !== 'banned'): ?>
                        <a href="ban_user.php?id=<?= $userId ?>" class="btn btn-danger"
                           onclick="return confirm('Bạn có chắc muốn khóa tài khoản này?')">
                            <i class="bi bi-lock"></i> Khóa tài khoản
                        </a>
                        <?php else: ?>
                        <a href="ban_user.php?id=<?= $userId ?>&action=unban" class="btn btn-success">
                            <i class="bi bi-unlock"></i> Mở khóa
                        </a>
                        <?php endif; ?>
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

                <div class="row">
                    <!-- User Info Card -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= $user['avatar'] ?>" 
                                     alt="Avatar" class="rounded-circle mb-3" 
                                     style="width: 120px; height: 120px; object-fit: cover;"
                                     onerror="this.src='<?= BASE_URL ?>/public/uploads/avatars/default-avatar.png'">
                                <h4><?= htmlspecialchars($user['fullname']) ?></h4>
                                <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                                
                                <?php 
                                $roleColors = [
                                    'user' => 'secondary',
                                    'benefactor' => 'primary',
                                    'admin' => 'danger'
                                ];
                                $roleLabels = [
                                    'user' => 'User',
                                    'benefactor' => 'Nhà hảo tâm',
                                    'admin' => 'Admin'
                                ];
                                ?>
                                <span class="badge bg-<?= $roleColors[$user['role']] ?> mb-3">
                                    <?= $roleLabels[$user['role']] ?>
                                </span>
                                
                                <?php 
                                $statusColors = [
                                    'active' => 'success',
                                    'inactive' => 'warning',
                                    'banned' => 'danger'
                                ];
                                $statusLabels = [
                                    'active' => 'Hoạt động',
                                    'inactive' => 'Không hoạt động',
                                    'banned' => 'Đã khóa'
                                ];
                                ?>
                                <span class="badge bg-<?= $statusColors[$user['status']] ?>">
                                    <?= $statusLabels[$user['status']] ?>
                                </span>
                            </div>
                            <div class="card-body border-top">
                                <div class="mb-2">
                                    <i class="bi bi-telephone text-muted"></i>
                                    <strong>SĐT:</strong> <?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-geo-alt text-muted"></i>
                                    <strong>Địa chỉ:</strong> 
                                    <?php if ($user['address']): ?>
                                        <?= htmlspecialchars($user['address']) ?>
                                        <?php if ($user['district']): ?>
                                        , <?= htmlspecialchars($user['district']) ?>
                                        <?php endif; ?>
                                        <?php if ($user['city']): ?>
                                        , <?= htmlspecialchars($user['city']) ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Chưa cập nhật
                                    <?php endif; ?>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-calendar text-muted"></i>
                                    <strong>Đăng ký:</strong> <?= formatDate($user['created_at'], 'd/m/Y H:i') ?>
                                </div>
                                <?php if ($user['last_login']): ?>
                                <div class="mb-2">
                                    <i class="bi bi-clock text-muted"></i>
                                    <strong>Truy cập:</strong> <?= timeAgo($user['last_login']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics & Activities -->
                    <div class="col-lg-8">
                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-1">Tổng quyên góp</h6>
                                        <h4 class="text-success mb-0"><?= formatMoney($donationStats['total']) ?></h4>
                                        <small class="text-muted"><?= $donationStats['count'] ?> lần</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-1">Tình nguyện</h6>
                                        <h4 class="text-primary mb-0"><?= number_format($volunteerCount) ?></h4>
                                        <small class="text-muted">sự kiện</small>
                                    </div>
                                </div>
                            </div>
                            <?php if ($user['role'] === 'benefactor'): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-1">Sự kiện tạo</h6>
                                        <h4 class="text-warning mb-0"><?= number_format($eventsCreated) ?></h4>
                                        <small class="text-muted">sự kiện</small>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3" id="userTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="donations-tab" data-bs-toggle="tab" 
                                        data-bs-target="#donations" type="button">
                                    <i class="bi bi-currency-dollar"></i> Quyên góp (<?= $donationStats['count'] ?>)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="volunteer-tab" data-bs-toggle="tab" 
                                        data-bs-target="#volunteer" type="button">
                                    <i class="bi bi-heart"></i> Tình nguyện (<?= $volunteerCount ?>)
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="userTabsContent">
                            <!-- Donations Tab -->
                            <div class="tab-pane fade show active" id="donations" role="tabpanel">
                                <div class="card">
                                    <div class="card-body">
                                        <?php if (count($recentDonations) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Sự kiện</th>
                                                        <th>Số tiền</th>
                                                        <th>Trạng thái</th>
                                                        <th>Ngày</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recentDonations as $donation): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($donation['event_title']) ?></td>
                                                        <td><strong class="text-success"><?= formatMoney($donation['amount']) ?></strong></td>
                                                        <td>
                                                            <?php
                                                            $colors = [
                                                                'pending' => 'warning',
                                                                'completed' => 'success',
                                                                'failed' => 'danger'
                                                            ];
                                                            ?>
                                                            <span class="badge bg-<?= $colors[$donation['status']] ?>">
                                                                <?= ucfirst($donation['status']) ?>
                                                            </span>
                                                        </td>
                                                        <td><small><?= formatDate($donation['created_at']) ?></small></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php else: ?>
                                        <p class="text-muted text-center py-4">Chưa có quyên góp nào</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Volunteer Tab -->
                            <div class="tab-pane fade" id="volunteer" role="tabpanel">
                                <div class="card">
                                    <div class="card-body">
                                        <?php if (count($volunteerActivities) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Sự kiện</th>
                                                        <th>Vai trò</th>
                                                        <th>Trạng thái</th>
                                                        <th>Ngày đăng ký</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($volunteerActivities as $vol): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($vol['event_title']) ?></td>
                                                        <td><?= htmlspecialchars($vol['skills'] ?? 'Chung') ?></td>
                                                        <td>
                                                            <?php
                                                            $colors = [
                                                                'pending' => 'warning',
                                                                'approved' => 'success',
                                                                'rejected' => 'danger',
                                                                'attended' => 'info'
                                                            ];
                                                            ?>
                                                            <span class="badge bg-<?= $colors[$vol['status']] ?>">
                                                                <?= ucfirst($vol['status']) ?>
                                                            </span>
                                                        </td>
                                                        <td><small><?= formatDate($vol['created_at']) ?></small></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php else: ?>
                                        <p class="text-muted text-center py-4">Chưa có hoạt động tình nguyện nào</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
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
