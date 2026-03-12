<?php
/**
 * Admin - All Events admin/all_events.php
 * Danh sách tất cả sự kiện với filter và search
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Quản lý sự kiện';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$benefactorId = $_GET['benefactor_id'] ?? '';

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(e.title LIKE ? OR e.location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "e.status = ?";
    $params[] = $status;
}

if ($category) {
    $where[] = "e.category = ?";
    $params[] = $category;
}

if ($benefactorId) {
    $where[] = "e.user_id = ?";
    $params[] = $benefactorId;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = "SELECT COUNT(*) FROM events e WHERE $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalEvents = $stmt->fetchColumn();
$totalPages = ceil($totalEvents / $perPage);

// Get events
$query = "SELECT e.*, u.fullname as benefactor_name,
          COUNT(DISTINCT d.id) as donation_count,
          COALESCE(SUM(d.amount), 0) as raised_amount,
          COUNT(DISTINCT v.id) as volunteer_count
          FROM events e
          JOIN users u ON e.user_id = u.id
          LEFT JOIN donations d ON e.id = d.event_id AND d.status = 'completed'
          LEFT JOIN event_volunteers v ON e.id = v.event_id
          WHERE $whereClause
          GROUP BY e.id
          ORDER BY e.created_at DESC
          LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'pending'")->fetchColumn(),
    'approved' => $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'approved'")->fetchColumn(),
    'ongoing' => $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'ongoing'")->fetchColumn(),
    'completed' => $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'completed'")->fetchColumn(),
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
                    <h1 class="h2"><i class="bi bi-calendar-event"></i> <?= $pageTitle ?></h1>
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
                    <div class="col-md-2">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted">Tổng số</h6>
                                <h3><?= number_format($stats['total']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="text-muted">Chờ duyệt</h6>
                                <h3 class="text-warning"><?= number_format($stats['pending']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-muted">Đã duyệt</h6>
                                <h3 class="text-success"><?= number_format($stats['approved']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-info">
                            <div class="card-body">
                                <h6 class="text-muted">Đang diễn ra</h6>
                                <h3 class="text-info"><?= number_format($stats['ongoing']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-secondary">
                            <div class="card-body">
                                <h6 class="text-muted">Đã hoàn thành</h6>
                                <h3 class="text-secondary"><?= number_format($stats['completed']) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Tìm theo tên, địa điểm..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                                    <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                                    <option value="rejected" <?= $status == 'rejected' ? 'selected' : '' ?>>Đã từ chối</option>
                                    <option value="ongoing" <?= $status == 'ongoing' ? 'selected' : '' ?>>Đang diễn ra</option>
                                    <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                                    <option value="closed" <?= $status == 'closed' ? 'selected' : '' ?>>Đã đóng</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="category">
                                    <option value="">Tất cả danh mục</option>
                                    <option value="medical" <?= $category == 'medical' ? 'selected' : '' ?>>Y tế</option>
                                    <option value="education" <?= $category == 'education' ? 'selected' : '' ?>>Giáo dục</option>
                                    <option value="disaster" <?= $category == 'disaster' ? 'selected' : '' ?>>Thiên tai</option>
                                    <option value="children" <?= $category == 'children' ? 'selected' : '' ?>>Trẻ em</option>
                                    <option value="elderly" <?= $category == 'elderly' ? 'selected' : '' ?>>Người cao tuổi</option>
                                    <option value="community" <?= $category == 'community' ? 'selected' : '' ?>>Cộng đồng</option>
                                    <option value="environment" <?= $category == 'environment' ? 'selected' : '' ?>>Môi trường</option>
                                    <option value="other" <?= $category == 'other' ? 'selected' : '' ?>>Khác</option>
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

                <!-- Events Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Danh sách sự kiện (<?= number_format($totalEvents) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th style="min-width: 250px;">Sự kiện</th>
                                        <th>Nhà hảo tâm</th>
                                        <th>Danh mục</th>
                                        <th>Mục tiêu</th>
                                        <th>Tiến độ</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($events) > 0): ?>
                                        <?php foreach ($events as $event): ?>
                                        <tr>
                                            <td><?= $event['id'] ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($event['thumbnail']): ?>
                                                    <img src="<?= BASE_URL ?>/public/uploads/events/<?= $event['thumbnail'] ?>" 
                                                         alt="Thumbnail" class="me-2" 
                                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($event['title']) ?></div>
                                                        <small class="text-muted">
                                                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['location']) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($event['benefactor_name']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= ucfirst($event['category']) ?></span>
                                            </td>
                                            <td>
                                                <strong><?= formatMoney($event['target_amount']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= $event['donation_count'] ?> người</small>
                                            </td>
                                            <td>
                                                <?php 
                                                $percentage = $event['target_amount'] > 0 
                                                    ? ($event['raised_amount'] / $event['target_amount']) * 100 
                                                    : 0;
                                                ?>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: <?= min(100, $percentage) ?>%">
                                                        <?= number_format($percentage, 1) ?>%
                                                    </div>
                                                </div>
                                                <small class="text-success"><?= formatMoney($event['raised_amount']) ?></small>
                                            </td>
                                            <td>
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
                                                <span class="badge bg-<?= $statusColors[$event['status']] ?>">
                                                    <?= $statusLabels[$event['status']] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="event_detail.php?id=<?= $event['id'] ?>" 
                                                       class="btn btn-outline-info" title="Chi tiết">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <?php if ($event['status'] === 'pending'): ?>
                                                    <a href="approve_event.php?id=<?= $event['id'] ?>" 
                                                       class="btn btn-outline-success" 
                                                       onclick="return confirm('Duyệt sự kiện này?')"
                                                       title="Duyệt">
                                                        <i class="bi bi-check-lg"></i>
                                                    </a>
                                                    <a href="reject_event.php?id=<?= $event['id'] ?>" 
                                                       class="btn btn-outline-danger" title="Từ chối">
                                                        <i class="bi bi-x-lg"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                Không tìm thấy sự kiện nào
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
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&category=<?= $category ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&category=<?= $category ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&category=<?= $category ?>">
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
