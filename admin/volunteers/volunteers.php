<?php
/**
 * Admin - Volunteers Management
 * Quản lý tình nguyện viên
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Quản lý tình nguyện viên';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$eventId = $_GET['event_id'] ?? '';

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(ev.fullname LIKE ? OR ev.email LIKE ? OR ev.phone LIKE ? OR e.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "ev.status = ?";
    $params[] = $status;
}

if ($eventId) {
    $where[] = "ev.event_id = ?";
    $params[] = $eventId;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = "SELECT COUNT(*) FROM event_volunteers ev WHERE $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalVolunteers = $stmt->fetchColumn();
$totalPages = ceil($totalVolunteers / $perPage);

// Get volunteers
$query = "SELECT ev.*, e.title as event_title, e.slug as event_slug, u.fullname as user_fullname
          FROM event_volunteers ev
          JOIN events e ON ev.event_id = e.id
          LEFT JOIN users u ON ev.user_id = u.id
          WHERE $whereClause
          ORDER BY ev.created_at DESC
          LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$volunteers = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM event_volunteers")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM event_volunteers WHERE status = 'pending'")->fetchColumn(),
    'approved' => $pdo->query("SELECT COUNT(*) FROM event_volunteers WHERE status = 'approved'")->fetchColumn(),
    'attended' => $pdo->query("SELECT COUNT(*) FROM event_volunteers WHERE status = 'attended'")->fetchColumn(),
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
                    <h1 class="h2"><i class="bi bi-people"></i> <?= $pageTitle ?></h1>
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
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="text-muted">Chờ duyệt</h6>
                                <h3 class="text-warning"><?= number_format($stats['pending']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-muted">Đã duyệt</h6>
                                <h3 class="text-success"><?= number_format($stats['approved']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body">
                                <h6 class="text-muted">Đã tham gia</h6>
                                <h3 class="text-info"><?= number_format($stats['attended']) ?></h3>
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
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Tìm theo tên, email, sự kiện..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                                    <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                                    <option value="rejected" <?= $status == 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                                    <option value="attended" <?= $status == 'attended' ? 'selected' : '' ?>>Đã tham gia</option>
                                    <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
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

                <!-- Volunteers Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Danh sách tình nguyện viên (<?= number_format($totalVolunteers) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Tình nguyện viên</th>
                                        <th>Sự kiện</th>
                                        <th>Kỹ năng</th>
                                        <th>Trạng thái</th>
                                        <th>Đăng ký</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($volunteers) > 0): ?>
                                        <?php foreach ($volunteers as $v): ?>
                                        <tr>
                                            <td><?= $v['id'] ?></td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($v['user_fullname'] ?? $v['fullname']) ?></strong>
                                                    <br><small class="text-muted"><?= htmlspecialchars($v['email']) ?></small>
                                                    <?php if ($v['phone']): ?>
                                                    <br><small class="text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($v['phone']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="../events/event_detail.php?id=<?= $v['event_id'] ?>">
                                                    <?= htmlspecialchars(truncate($v['event_title'], 50)) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($v['skills']): ?>
                                                <small><?= htmlspecialchars(truncate($v['skills'], 50)) ?></small>
                                                <?php else: ?>
                                                <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'attended' => 'info',
                                                    'cancelled' => 'secondary'
                                                ];
                                                $statusLabels = [
                                                    'pending' => 'Chờ duyệt',
                                                    'approved' => 'Đã duyệt',
                                                    'rejected' => 'Từ chối',
                                                    'attended' => 'Đã tham gia',
                                                    'cancelled' => 'Đã hủy'
                                                ];
                                                ?>
                                                <span class="badge bg-<?= $statusColors[$v['status']] ?>">
                                                    <?= $statusLabels[$v['status']] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= formatDate($v['created_at'], 'd/m/Y H:i') ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($v['status'] === 'pending'): ?>
                                                    <a href="approve_volunteer.php?id=<?= $v['id'] ?>" 
                                                       class="btn btn-outline-success" 
                                                       title="Duyệt"
                                                       onclick="return confirm('Duyệt tình nguyện viên này?')">
                                                        <i class="bi bi-check-lg"></i>
                                                    </a>
                                                    <a href="reject_volunteer.php?id=<?= $v['id'] ?>" 
                                                       class="btn btn-outline-danger" 
                                                       title="Từ chối"
                                                       onclick="return confirm('Từ chối tình nguyện viên này?')">
                                                        <i class="bi bi-x-lg"></i>
                                                    </a>
                                                    <?php elseif ($v['status'] === 'approved'): ?>
                                                    <a href="mark_attended.php?id=<?= $v['id'] ?>" 
                                                       class="btn btn-outline-info" 
                                                       title="Đánh dấu đã tham gia"
                                                       onclick="return confirm('Xác nhận đã tham gia?')">
                                                        <i class="bi bi-check2-circle"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                Không tìm thấy tình nguyện viên nào
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
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?><?= $eventId ? '&event_id=' . $eventId : '' ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?><?= $eventId ? '&event_id=' . $eventId : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?><?= $eventId ? '&event_id=' . $eventId : '' ?>">
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
