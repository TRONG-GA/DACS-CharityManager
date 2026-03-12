<?php
/**
 * Admin - Contacts Management
 * Quản lý liên hệ từ người dùng
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Quản lý liên hệ';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = "SELECT COUNT(*) FROM contacts WHERE $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalContacts = $stmt->fetchColumn();
$totalPages = ceil($totalContacts / $perPage);

// Get contacts
$query = "SELECT * FROM contacts 
          WHERE $whereClause
          ORDER BY created_at DESC
          LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$contacts = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn(),
    'new' => $pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'new'")->fetchColumn(),
    'processing' => $pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'processing'")->fetchColumn(),
    'resolved' => $pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'resolved'")->fetchColumn(),
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
                    <h1 class="h2"><i class="bi bi-envelope"></i> <?= $pageTitle ?></h1>
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
                        <div class="card border-primary">
                            <div class="card-body">
                                <h6 class="text-muted">Mới</h6>
                                <h3 class="text-primary"><?= number_format($stats['new']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="text-muted">Đang xử lý</h6>
                                <h3 class="text-warning"><?= number_format($stats['processing']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-muted">Đã giải quyết</h6>
                                <h3 class="text-success"><?= number_format($stats['resolved']) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Tìm theo tên, email, chủ đề..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="new" <?= $status == 'new' ? 'selected' : '' ?>>Mới</option>
                                    <option value="processing" <?= $status == 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                                    <option value="resolved" <?= $status == 'resolved' ? 'selected' : '' ?>>Đã giải quyết</option>
                                    <option value="closed" <?= $status == 'closed' ? 'selected' : '' ?>>Đã đóng</option>
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

                <!-- Contacts Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Danh sách liên hệ (<?= number_format($totalContacts) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">ID</th>
                                        <th>Người gửi</th>
                                        <th>Chủ đề</th>
                                        <th>Nội dung</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày gửi</th>
                                        <th style="width: 120px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($contacts) > 0): ?>
                                        <?php foreach ($contacts as $contact): ?>
                                        <tr class="<?= $contact['status'] === 'new' ? 'table-primary' : '' ?>">
                                            <td>
                                                <?= $contact['id'] ?>
                                                <?php if ($contact['status'] === 'new'): ?>
                                                <span class="badge bg-danger rounded-pill">New</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($contact['name']) ?></strong>
                                                    <br><small class="text-muted"><?= htmlspecialchars($contact['email']) ?></small>
                                                    <?php if ($contact['phone']): ?>
                                                    <br><small class="text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($contact['phone']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($contact['subject']) ?></strong>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars(truncate($contact['message'], 100)) ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $statusColors = [
                                                    'new' => 'primary',
                                                    'processing' => 'warning',
                                                    'resolved' => 'success',
                                                    'closed' => 'secondary'
                                                ];
                                                $statusLabels = [
                                                    'new' => 'Mới',
                                                    'processing' => 'Đang xử lý',
                                                    'resolved' => 'Đã giải quyết',
                                                    'closed' => 'Đã đóng'
                                                ];
                                                ?>
                                                <span class="badge bg-<?= $statusColors[$contact['status']] ?>">
                                                    <?= $statusLabels[$contact['status']] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= formatDate($contact['created_at'], 'd/m/Y H:i') ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#contactModal<?= $contact['id'] ?>"
                                                            title="Xem chi tiết">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <?php if ($contact['status'] === 'new'): ?>
                                                    <a href="update_contact_status.php?id=<?= $contact['id'] ?>&status=processing" 
                                                       class="btn btn-outline-warning" title="Đánh dấu đang xử lý">
                                                        <i class="bi bi-clock"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <?php if ($contact['status'] !== 'resolved'): ?>
                                                    <a href="update_contact_status.php?id=<?= $contact['id'] ?>&status=resolved" 
                                                       class="btn btn-outline-success" title="Đánh dấu đã giải quyết">
                                                        <i class="bi bi-check-lg"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Modal -->
                                                <div class="modal fade" id="contactModal<?= $contact['id'] ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Chi tiết liên hệ #<?= $contact['id'] ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <strong>Họ tên:</strong><br>
                                                                        <?= htmlspecialchars($contact['name']) ?>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <strong>Email:</strong><br>
                                                                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                                                                            <?= htmlspecialchars($contact['email']) ?>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <?php if ($contact['phone']): ?>
                                                                <div class="mb-3">
                                                                    <strong>Số điện thoại:</strong><br>
                                                                    <a href="tel:<?= htmlspecialchars($contact['phone']) ?>">
                                                                        <?= htmlspecialchars($contact['phone']) ?>
                                                                    </a>
                                                                </div>
                                                                <?php endif; ?>
                                                                <div class="mb-3">
                                                                    <strong>Chủ đề:</strong><br>
                                                                    <?= htmlspecialchars($contact['subject']) ?>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <strong>Nội dung:</strong><br>
                                                                    <div class="p-3 bg-light rounded">
                                                                        <?= nl2br(htmlspecialchars($contact['message'])) ?>
                                                                    </div>
                                                                </div>
                                                                <?php if ($contact['admin_notes']): ?>
                                                                <div class="mb-3">
                                                                    <strong>Ghi chú admin:</strong><br>
                                                                    <div class="p-3 bg-warning bg-opacity-10 rounded">
                                                                        <?= nl2br(htmlspecialchars($contact['admin_notes'])) ?>
                                                                    </div>
                                                                </div>
                                                                <?php endif; ?>
                                                                <div class="mb-3">
                                                                    <strong>Ngày gửi:</strong><br>
                                                                    <?= formatDate($contact['created_at'], 'd/m/Y H:i:s') ?>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                                <a href="reply_contact.php?id=<?= $contact['id'] ?>" class="btn btn-primary">
                                                                    <i class="bi bi-reply"></i> Trả lời
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                Không có liên hệ nào
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
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>">
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
