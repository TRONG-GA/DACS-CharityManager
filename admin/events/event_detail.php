<?php
/**
 * Admin - Event Detail
 * Chi tiết sự kiện với donations, volunteers, và actions
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$eventId = $_GET['id'] ?? 0;

// Get event info
$stmt = $pdo->prepare("
    SELECT e.*, u.fullname as benefactor_name, u.email as benefactor_email, u.phone as benefactor_phone,
           COUNT(DISTINCT d.id) as donation_count,
           COALESCE(SUM(CASE WHEN d.status = 'completed' THEN d.amount ELSE 0 END), 0) as raised_amount,
           COUNT(DISTINCT v.id) as volunteer_count,
           ua.fullname as approved_by_name
    FROM events e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN donations d ON e.id = d.event_id
    LEFT JOIN event_volunteers v ON e.id = v.event_id
    LEFT JOIN users ua ON e.approved_by = ua.id
    WHERE e.id = ?
    GROUP BY e.id
");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    setFlashMessage('error', 'Không tìm thấy sự kiện');
    header('Location: all_events.php');
    exit;
}

// Get donations
$donations = $pdo->prepare("
    SELECT d.*, u.fullname as donor_name
    FROM donations d
    LEFT JOIN users u ON d.user_id = u.id
    WHERE d.event_id = ?
    ORDER BY d.created_at DESC
    LIMIT 10
")->execute([$eventId])->fetchAll();

// Get volunteers
$volunteers = $pdo->prepare("
    SELECT ev.*, u.fullname as user_fullname
    FROM event_volunteers ev
    LEFT JOIN users u ON ev.user_id = u.id
    WHERE ev.event_id = ?
    ORDER BY ev.created_at DESC
    LIMIT 10
")->execute([$eventId])->fetchAll();

// Calculate progress
$progress = $event['target_amount'] > 0 
    ? ($event['raised_amount'] / $event['target_amount']) * 100 
    : 0;

$pageTitle = 'Chi tiết sự kiện';
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
                        <a href="all_events.php" class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <?= $pageTitle ?>
                    </h1>
                    <div class="btn-toolbar">
                        <?php if ($event['status'] === 'pending'): ?>
                        <a href="approve_event.php?id=<?= $eventId ?>" class="btn btn-success me-2">
                            <i class="bi bi-check-lg"></i> Duyệt
                        </a>
                        <a href="reject_event.php?id=<?= $eventId ?>" class="btn btn-danger me-2">
                            <i class="bi bi-x-lg"></i> Từ chối
                        </a>
                        <?php elseif ($event['status'] === 'approved' || $event['status'] === 'ongoing'): ?>
                        <a href="close_event.php?id=<?= $eventId ?>" class="btn btn-warning me-2">
                            <i class="bi bi-archive"></i> Đóng sự kiện
                        </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/events/event_detail.php?id=<?= $eventId ?>" 
                           class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-eye"></i> Xem trang công khai
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

                <div class="row">
                    <!-- Main Content -->
                    <div class="col-lg-8">
                        <!-- Event Info Card -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <?php if ($event['thumbnail']): ?>
                                <img src="<?= BASE_URL ?>/public/uploads/events/<?= $event['thumbnail'] ?>" 
                                     alt="Thumbnail" class="img-fluid rounded mb-3">
                                <?php endif; ?>
                                
                                <h3><?= htmlspecialchars($event['title']) ?></h3>
                                
                                <div class="mb-3">
                                    <?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'ongoing' => 'info',
                                        'completed' => 'secondary',
                                        'closed' => 'dark'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Từ chối',
                                        'ongoing' => 'Đang diễn ra',
                                        'completed' => 'Hoàn thành',
                                        'closed' => 'Đã đóng'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $statusColors[$event['status']] ?> me-2">
                                        <?= $statusLabels[$event['status']] ?>
                                    </span>
                                    <span class="badge bg-secondary"><?= ucfirst($event['category']) ?></span>
                                    <?php if ($event['is_featured']): ?>
                                    <span class="badge bg-primary"><i class="bi bi-star"></i> Nổi bật</span>
                                    <?php endif; ?>
                                    <?php if ($event['is_urgent']): ?>
                                    <span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> Khẩn cấp</span>
                                    <?php endif; ?>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p><i class="bi bi-geo-alt"></i> <strong>Địa điểm:</strong> <?= htmlspecialchars($event['location']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><i class="bi bi-calendar"></i> <strong>Thời gian:</strong> 
                                        <?= formatDate($event['start_date']) ?> - <?= formatDate($event['end_date']) ?></p>
                                    </div>
                                </div>

                                <h5>Mô tả:</h5>
                                <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>

                                <h5>Nội dung chi tiết:</h5>
                                <div><?= $event['content'] ?></div>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#donations">
                                    <i class="bi bi-currency-dollar"></i> Quyên góp (<?= $event['donation_count'] ?>)
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#volunteers">
                                    <i class="bi bi-people"></i> Tình nguyện viên (<?= $event['volunteer_count'] ?>)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content border border-top-0 p-3">
                            <!-- Donations Tab -->
                            <div class="tab-pane fade show active" id="donations">
                                <?php if (count($donations) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Người góp</th>
                                                <th>Số tiền</th>
                                                <th>Phương thức</th>
                                                <th>Trạng thái</th>
                                                <th>Thời gian</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($donations as $d): ?>
                                            <tr>
                                                <td><?= $d['is_anonymous'] ? 'Ẩn danh' : htmlspecialchars($d['donor_name'] ?? $d['donor_name']) ?></td>
                                                <td><strong class="text-success"><?= formatMoney($d['amount']) ?></strong></td>
                                                <td><?= $d['payment_method'] ?></td>
                                                <td>
                                                    <?php
                                                    $colors = ['pending' => 'warning', 'completed' => 'success', 'failed' => 'danger'];
                                                    ?>
                                                    <span class="badge bg-<?= $colors[$d['status']] ?>">
                                                        <?= ucfirst($d['status']) ?>
                                                    </span>
                                                </td>
                                                <td><small><?= timeAgo($d['created_at']) ?></small></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="<?= BASE_URL ?>/admin/donations/donations.php?event_id=<?= $eventId ?>" class="btn btn-sm btn-outline-primary">
                                    Xem tất cả quyên góp
                                </a>
                                <?php else: ?>
                                <p class="text-muted">Chưa có quyên góp nào</p>
                                <?php endif; ?>
                            </div>

                            <!-- Volunteers Tab -->
                            <div class="tab-pane fade" id="volunteers">
                                <?php if (count($volunteers) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Họ tên</th>
                                                <th>Email</th>
                                                <th>SĐT</th>
                                                <th>Trạng thái</th>
                                                <th>Đăng ký</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($volunteers as $v): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($v['fullname']) ?></td>
                                                <td><?= htmlspecialchars($v['email']) ?></td>
                                                <td><?= htmlspecialchars($v['phone']) ?></td>
                                                <td>
                                                    <?php
                                                    $colors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                                                    ?>
                                                    <span class="badge bg-<?= $colors[$v['status']] ?>">
                                                        <?= ucfirst($v['status']) ?>
                                                    </span>
                                                </td>
                                                <td><small><?= timeAgo($v['created_at']) ?></small></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-muted">Chưa có tình nguyện viên nào</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Progress Card -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Tiến độ</h5>
                            </div>
                            <div class="card-body">
                                <div class="progress mb-3" style="height: 30px;">
                                    <div class="progress-bar bg-success" style="width: <?= min(100, $progress) ?>%">
                                        <?= number_format($progress, 1) ?>%
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Đã quyên góp:</span>
                                    <strong class="text-success"><?= formatMoney($event['raised_amount']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Mục tiêu:</span>
                                    <strong><?= formatMoney($event['target_amount']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Còn thiếu:</span>
                                    <strong class="text-danger">
                                        <?= formatMoney(max(0, $event['target_amount'] - $event['raised_amount'])) ?>
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <!-- Benefactor Info -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-person"></i> Người tổ chức</h5>
                            </div>
                            <div class="card-body">
                                <h6><?= htmlspecialchars($event['benefactor_name']) ?></h6>
                                <p class="mb-1"><i class="bi bi-envelope"></i> <?= htmlspecialchars($event['benefactor_email']) ?></p>
                                <?php if ($event['benefactor_phone']): ?>
                                <p class="mb-1"><i class="bi bi-telephone"></i> <?= htmlspecialchars($event['benefactor_phone']) ?></p>
                                <?php endif; ?>
                                <a href="../users/user_detail.php?id=<?= $event['user_id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                    Xem hồ sơ
                                </a>
                            </div>
                        </div>

                        <!-- Stats Card -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Thống kê</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Lượt xem:</span>
                                    <strong><?= number_format($event['views']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Người góp:</span>
                                    <strong><?= number_format($event['donation_count']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tình nguyện viên:</span>
                                    <strong><?= number_format($event['volunteer_count']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Ngày tạo:</span>
                                    <small><?= formatDate($event['created_at'], 'd/m/Y H:i') ?></small>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Info -->
                        <?php if ($event['approved_by']): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Thông tin duyệt</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Người duyệt:</strong> <?= htmlspecialchars($event['approved_by_name']) ?></p>
                                <p class="mb-0"><strong>Thời gian:</strong> <?= formatDate($event['approved_at'], 'd/m/Y H:i') ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
