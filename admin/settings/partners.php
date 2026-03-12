<?php
/**
 * Admin - Partners Management
 * Quản lý đối tác
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Quản lý đối tác';

// Get all partners
$partners = $pdo->query("SELECT * FROM partners ORDER BY display_order, name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $partnerId = $_POST['partner_id'] ?? 0;
        $name = sanitize($_POST['name'] ?? '');
        $website = sanitize($_POST['website'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        
        // Handle logo upload
        $logo = '';
        if ($action === 'update') {
            $stmt = $pdo->prepare("SELECT logo FROM partners WHERE id = ?");
            $stmt->execute([$partnerId]);
            $logo = $stmt->fetchColumn();
        }
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadedFile = uploadImage($_FILES['logo'], 'partners');
            if ($uploadedFile) {
                if ($logo) {
                    deleteFile('partners/' . $logo);
                }
                $logo = basename($uploadedFile);
            }
        }
        
        try {
            if ($action === 'create') {
                $stmt = $pdo->prepare("
                    INSERT INTO partners (name, logo, website, description, display_order, status)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $logo, $website, $description, $displayOrder, $status]);
                setFlashMessage('success', 'Đã thêm đối tác mới');
            } else {
                $stmt = $pdo->prepare("
                    UPDATE partners 
                    SET name = ?, logo = ?, website = ?, description = ?, display_order = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $logo, $website, $description, $displayOrder, $status, $partnerId]);
                setFlashMessage('success', 'Đã cập nhật đối tác');
            }
            
            header('Location: partners.php');
            exit;
            
        } catch (PDOException $e) {
            setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
        }
    } elseif ($action === 'delete') {
        $partnerId = $_POST['partner_id'] ?? 0;
        
        try {
            // Get logo to delete
            $stmt = $pdo->prepare("SELECT logo FROM partners WHERE id = ?");
            $stmt->execute([$partnerId]);
            $logo = $stmt->fetchColumn();
            
            if ($logo) {
                deleteFile('partners/' . $logo);
            }
            
            // Delete partner
            $stmt = $pdo->prepare("DELETE FROM partners WHERE id = ?");
            $stmt->execute([$partnerId]);
            
            setFlashMessage('success', 'Đã xóa đối tác');
            header('Location: partners.php');
            exit;
            
        } catch (PDOException $e) {
            setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
        }
    }
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
                    <h1 class="h2"><i class="bi bi-people"></i> <?= $pageTitle ?></h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="bi bi-plus-lg"></i> Thêm đối tác
                    </button>
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
                    <?php foreach ($partners as $partner): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <?php if ($partner['logo']): ?>
                            <img src="<?= BASE_URL ?>/public/uploads/partners/<?= $partner['logo'] ?>" 
                                 class="card-img-top p-3" alt="<?= htmlspecialchars($partner['name']) ?>"
                                 style="height: 150px; object-fit: contain;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?= htmlspecialchars($partner['name']) ?>
                                    <?php if ($partner['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </h5>
                                <?php if ($partner['description']): ?>
                                <p class="card-text text-muted">
                                    <?= htmlspecialchars(truncate($partner['description'], 100)) ?>
                                </p>
                                <?php endif; ?>
                                <?php if ($partner['website']): ?>
                                <p class="card-text">
                                    <small>
                                        <i class="bi bi-link"></i>
                                        <a href="<?= htmlspecialchars($partner['website']) ?>" target="_blank">
                                            <?= htmlspecialchars($partner['website']) ?>
                                        </a>
                                    </small>
                                </p>
                                <?php endif; ?>
                                <p class="card-text">
                                    <small class="text-muted">Thứ tự: <?= $partner['display_order'] ?></small>
                                </p>
                            </div>
                            <div class="card-footer">
                                <div class="btn-group btn-group-sm w-100">
                                    <button class="btn btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal<?= $partner['id'] ?>">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </button>
                                    <button class="btn btn-outline-danger" 
                                            onclick="confirmDelete(<?= $partner['id'] ?>, '<?= htmlspecialchars($partner['name']) ?>')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?= $partner['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="partner_id" value="<?= $partner['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Chỉnh sửa đối tác</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php if ($partner['logo']): ?>
                                            <div class="mb-3 text-center">
                                                <img src="<?= BASE_URL ?>/public/uploads/partners/<?= $partner['logo'] ?>" 
                                                     alt="Logo" class="img-fluid" style="max-height: 100px;">
                                            </div>
                                            <?php endif; ?>
                                            <div class="mb-3">
                                                <label class="form-label">Tên đối tác</label>
                                                <input type="text" class="form-control" name="name" 
                                                       value="<?= htmlspecialchars($partner['name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Logo</label>
                                                <input type="file" class="form-control" name="logo" accept="image/*">
                                                <small class="text-muted">Để trống nếu không muốn đổi</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Website</label>
                                                <input type="url" class="form-control" name="website" 
                                                       value="<?= htmlspecialchars($partner['website'] ?? '') ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mô tả</label>
                                                <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($partner['description'] ?? '') ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Thứ tự hiển thị</label>
                                                <input type="number" class="form-control" name="display_order" 
                                                       value="<?= $partner['display_order'] ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Trạng thái</label>
                                                <select class="form-select" name="status">
                                                    <option value="active" <?= $partner['status'] === 'active' ? 'selected' : '' ?>>Hiển thị</option>
                                                    <option value="inactive" <?= $partner['status'] === 'inactive' ? 'selected' : '' ?>>Ẩn</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($partners)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Chưa có đối tác nào. Click "Thêm đối tác" để thêm mới.
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm đối tác mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên đối tác</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website" placeholder="https://...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Thứ tự hiển thị</label>
                            <input type="number" class="form-control" name="display_order" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="active">Hiển thị</option>
                                <option value="inactive">Ẩn</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thêm đối tác</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="partner_id" id="deletePartnerId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmDelete(id, name) {
        if (confirm('Bạn có chắc muốn xóa đối tác "' + name + '"?')) {
            document.getElementById('deletePartnerId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
    </script>
</body>
</html>
