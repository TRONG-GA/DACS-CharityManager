<?php
/**
 * Admin - Users Management
 * Quản lý danh sách người dùng
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Quản lý người dùng';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filters
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(fullname LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role) {
    $where[] = "role = ?";
    $params[] = $role;
}

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = "SELECT COUNT(*) FROM users WHERE $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalUsers = $stmt->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

// Get users
$query = "SELECT * FROM users WHERE $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get statistics
$totalActive = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
$totalInactive = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'")->fetchColumn();
$totalBanned = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'banned'")->fetchColumn();
$totalBenefactors = $pdo->query("SELECT COUNT(*) FROM users WHERE benefactor_status = 'approved'")->fetchColumn();
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
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-muted">Đang hoạt động</h6>
                                <h3 class="text-success"><?= number_format($totalActive) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="text-muted">Không hoạt động</h6>
                                <h3 class="text-warning"><?= number_format($totalInactive) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-danger">
                            <div class="card-body">
                                <h6 class="text-muted">Đã khóa</h6>
                                <h3 class="text-danger"><?= number_format($totalBanned) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h6 class="text-muted">Nhà hảo tâm</h6>
                                <h3 class="text-primary"><?= number_format($totalBenefactors) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Tìm theo tên, email, SĐT..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="role">
                                    <option value="">Tất cả vai trò</option>
                                    <option value="user" <?= $role == 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="benefactor" <?= $role == 'benefactor' ? 'selected' : '' ?>>Benefactor</option>
                                    <option value="admin" <?= $role == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                                    <option value="inactive" <?= $status == 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
                                    <option value="banned" <?= $status == 'banned' ? 'selected' : '' ?>>Đã khóa</option>
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

                <!-- Users Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Danh sách người dùng (<?= number_format($totalUsers) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Người dùng</th>
                                        <th>Email</th>
                                        <th>Vai trò</th>
                                        <th>Trạng thái</th>
                                        <th>Đăng ký</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($users) > 0): ?>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= $user['avatar'] ?>" 
                                                         alt="Avatar" class="rounded-circle me-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;"
                                                         onerror="this.src='<?= BASE_URL ?>/public/uploads/avatars/avatar1.jpg'">
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($user['fullname']) ?></div>
                                                        <?php if ($user['phone']): ?>
                                                        <small class="text-muted"><?= htmlspecialchars($user['phone']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
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
                                                <span class="badge bg-<?= $roleColors[$user['role']] ?>">
                                                    <?= $roleLabels[$user['role']] ?>
                                                </span>
                                            </td>
                                            <td>
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
                                            </td>
                                            <td>
                                                <small><?= formatDate($user['created_at']) ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="user_detail.php?id=<?= $user['id'] ?>" 
                                                       class="btn btn-outline-info" title="Chi tiết">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit_user.php?id=<?= $user['id'] ?>" 
                                                       class="btn btn-outline-primary" title="Chỉnh sửa">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <?php if ($user['status'] != 'banned'): ?>
                                                    <a href="ban_user.php?id=<?= $user['id'] ?>" 
                                                       class="btn btn-outline-danger" 
                                                       onclick="return confirm('Bạn có chắc muốn khóa người dùng này?')" 
                                                       title="Khóa">
                                                        <i class="bi bi-lock"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                Không tìm thấy người dùng nào
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
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&role=<?= $role ?>&status=<?= $status ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= $role ?>&status=<?= $status ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&role=<?= $role ?>&status=<?= $status ?>">
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
