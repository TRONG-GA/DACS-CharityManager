<?php
/**
 * Admin - Terms Management
 * Quản lý điều khoản
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Quản lý điều khoản';

// Get all terms
$terms = $pdo->query("SELECT * FROM terms_conditions ORDER BY type, version DESC")->fetchAll();

// Group by type
$termsByType = [];
foreach ($terms as $term) {
    $termsByType[$term['type']][] = $term;
}

// Handle form submission (create/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $termId = $_POST['term_id'] ?? 0;
        $type = $_POST['type'] ?? '';
        $title = sanitize($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $version = sanitize($_POST['version'] ?? '1.0');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        try {
            if ($action === 'create') {
                $stmt = $pdo->prepare("
                    INSERT INTO terms_conditions (type, title, content, version, is_active)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$type, $title, $content, $version, $isActive]);
                setFlashMessage('success', 'Đã tạo điều khoản mới');
            } else {
                $stmt = $pdo->prepare("
                    UPDATE terms_conditions 
                    SET title = ?, content = ?, version = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $content, $version, $isActive, $termId]);
                setFlashMessage('success', 'Đã cập nhật điều khoản');
            }
            
            header('Location: terms.php');
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
                    <h1 class="h2"><i class="bi bi-file-text"></i> <?= $pageTitle ?></h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="bi bi-plus-lg"></i> Tạo điều khoản mới
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

                <?php
                $typeLabels = [
                    'donation' => 'Quyên góp',
                    'volunteer' => 'Tình nguyện viên',
                    'benefactor' => 'Nhà hảo tâm',
                    'event_creation' => 'Tạo sự kiện'
                ];
                ?>

                <?php foreach ($typeLabels as $type => $label): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= $label ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($termsByType[$type])): ?>
                            <?php foreach ($termsByType[$type] as $term): ?>
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6>
                                            <?= htmlspecialchars($term['title']) ?>
                                            <?php if ($term['is_active']): ?>
                                            <span class="badge bg-success">Đang áp dụng</span>
                                            <?php endif; ?>
                                            <small class="text-muted">v<?= htmlspecialchars($term['version']) ?></small>
                                        </h6>
                                        <div class="text-muted" style="max-height: 100px; overflow: hidden;">
                                            <?= substr(strip_tags($term['content']), 0, 200) ?>...
                                        </div>
                                        <small class="text-muted">
                                            Cập nhật: <?= formatDate($term['updated_at'], 'd/m/Y H:i') ?>
                                        </small>
                                    </div>
                                    <div class="btn-group btn-group-sm ms-3">
                                        <button class="btn btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?= $term['id'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?= $term['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="term_id" value="<?= $term['id'] ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Chỉnh sửa điều khoản</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tiêu đề</label>
                                                        <input type="text" class="form-control" name="title" 
                                                               value="<?= htmlspecialchars($term['title']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Phiên bản</label>
                                                        <input type="text" class="form-control" name="version" 
                                                               value="<?= htmlspecialchars($term['version']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nội dung</label>
                                                        <textarea class="form-control" name="content" rows="10" required><?= htmlspecialchars($term['content']) ?></textarea>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="is_active" 
                                                               id="active<?= $term['id'] ?>" <?= $term['is_active'] ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="active<?= $term['id'] ?>">
                                                            Đang áp dụng
                                                        </label>
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
                        <?php else: ?>
                            <p class="text-muted">Chưa có điều khoản nào</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </main>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title">Tạo điều khoản mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Loại điều khoản</label>
                            <select class="form-select" name="type" required>
                                <option value="donation">Quyên góp</option>
                                <option value="volunteer">Tình nguyện viên</option>
                                <option value="benefactor">Nhà hảo tâm</option>
                                <option value="event_creation">Tạo sự kiện</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phiên bản</label>
                            <input type="text" class="form-control" name="version" value="1.0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nội dung</label>
                            <textarea class="form-control" name="content" rows="10" required></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="newActive" checked>
                            <label class="form-check-label" for="newActive">
                                Đang áp dụng
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Tạo điều khoản</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
