<?php
/**
 * Admin - General Settings
 * Cài đặt chung hệ thống
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Cài đặt chung';
$errors = [];

// Get current settings
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value, setting_type FROM system_settings WHERE category = 'general'");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = [
        'value' => $row['setting_value'],
        'type' => $row['setting_type']
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        foreach ($_POST as $key => $value) {
            if (isset($settings[$key])) {
                // Handle boolean values
                if ($settings[$key]['type'] === 'boolean') {
                    $value = isset($_POST[$key]) ? 'true' : 'false';
                }
                
                // Update setting
                $stmt = $pdo->prepare("
                    UPDATE system_settings 
                    SET setting_value = ?, updated_by = ?, updated_at = NOW()
                    WHERE setting_key = ?
                ");
                $stmt->execute([$value, $_SESSION['user_id'], $key]);
            }
        }
        
        $pdo->commit();
        setFlashMessage('success', 'Đã cập nhật cài đặt');
        header('Location: general.php');
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $errors[] = 'Lỗi: ' . $e->getMessage();
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
                    <h1 class="h2"><i class="bi bi-gear"></i> <?= $pageTitle ?></h1>
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

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <form method="POST">
                            <!-- Site Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Thông tin website</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Tên website</label>
                                        <input type="text" class="form-control" name="site_name" 
                                               value="<?= htmlspecialchars($settings['site_name']['value'] ?? '') ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Slogan</label>
                                        <input type="text" class="form-control" name="site_tagline" 
                                               value="<?= htmlspecialchars($settings['site_tagline']['value'] ?? '') ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email admin</label>
                                        <input type="email" class="form-control" name="admin_email" 
                                               value="<?= htmlspecialchars($settings['admin_email']['value'] ?? '') ?>">
                                        <small class="text-muted">Email nhận thông báo hệ thống</small>
                                    </div>
                                </div>
                            </div>

                            <!-- System Settings -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-sliders"></i> Cài đặt hệ thống</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Số items mỗi trang</label>
                                        <input type="number" class="form-control" name="items_per_page" 
                                               value="<?= htmlspecialchars($settings['items_per_page']['value'] ?? '20') ?>" 
                                               min="10" max="100">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Kích thước upload tối đa (bytes)</label>
                                        <input type="number" class="form-control" name="max_upload_size" 
                                               value="<?= htmlspecialchars($settings['max_upload_size']['value'] ?? '5242880') ?>">
                                        <small class="text-muted">Mặc định: 5MB (5242880 bytes)</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Thời gian session (giây)</label>
                                        <input type="number" class="form-control" name="session_lifetime" 
                                               value="<?= htmlspecialchars($settings['session_lifetime']['value'] ?? '7200') ?>">
                                        <small class="text-muted">Mặc định: 2 giờ (7200 giây)</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Số lần đăng nhập thất bại tối đa</label>
                                        <input type="number" class="form-control" name="max_login_attempts" 
                                               value="<?= htmlspecialchars($settings['max_login_attempts']['value'] ?? '5') ?>" 
                                               min="3" max="10">
                                    </div>

                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="enable_registrations" 
                                               id="enableRegistrations"
                                               <?= ($settings['enable_registrations']['value'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enableRegistrations">
                                            Cho phép đăng ký mới
                                        </label>
                                    </div>

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="maintenance_mode" 
                                               id="maintenanceMode"
                                               <?= ($settings['maintenance_mode']['value'] ?? 'false') === 'true' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="maintenanceMode">
                                            <span class="text-danger">Chế độ bảo trì</span>
                                        </label>
                                        <small class="text-muted d-block">Khi bật, chỉ admin có thể truy cập</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Settings -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-envelope"></i> Cài đặt email</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="smtp_enabled" 
                                               id="smtpEnabled"
                                               <?= ($settings['smtp_enabled']['value'] ?? 'false') === 'true' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="smtpEnabled">
                                            Kích hoạt SMTP
                                        </label>
                                        <small class="text-muted d-block">Gửi email qua SMTP thay vì PHP mail()</small>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        <small>Cấu hình SMTP chi tiết (host, port, username, password) cần được thêm vào file config riêng</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i> Hủy
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Lưu cài đặt
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-4">
                        <!-- Info Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Thông tin</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Phiên bản:</strong> 1.0.0
                                </div>
                                <div class="mb-2">
                                    <strong>PHP Version:</strong> <?= phpversion() ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Database:</strong> MySQL <?= $pdo->query("SELECT VERSION()")->fetchColumn() ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?>
                                </div>
                            </div>
                        </div>

                        <!-- Cache Card -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-arrow-clockwise"></i> Cache</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Xóa cache để áp dụng thay đổi ngay lập tức</p>
                                <button class="btn btn-outline-warning w-100" onclick="clearCache()">
                                    <i class="bi bi-trash"></i> Xóa cache
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function clearCache() {
        if (confirm('Bạn có chắc muốn xóa cache?')) {
            // TODO: Implement cache clearing
            showToast('Cache đã được xóa', 'success');
        }
    }

    function showToast(message, type) {
        alert(message);
    }
    </script>
</body>
</html>
