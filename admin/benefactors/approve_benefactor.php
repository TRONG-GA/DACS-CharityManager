<?php
/**
 * Admin - Approve Benefactor admin/benefactors/approve_benefactor.php
 * Duyệt nhà hảo tâm
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$appId = $_GET['id'] ?? 0;

// Get application
$stmt = $pdo->prepare("
    SELECT ba.*, u.fullname, u.email
    FROM benefactor_applications ba
    JOIN users u ON ba.user_id = u.id
    WHERE ba.id = ?
");
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app || $app['status'] !== 'pending') {
    setFlashMessage('error', 'Không tìm thấy đơn đăng ký hoặc đơn đã được xử lý');
    header('Location: benefactors.php');
    exit;
}

$errors = [];

// Handle approval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notes = sanitize($_POST['notes'] ?? '');
    
    try {
        $pdo->beginTransaction();
        
        // Update application status
        $stmt = $pdo->prepare("
            UPDATE benefactor_applications SET 
                status = 'approved',
                admin_notes = ?,
                reviewed_by = ?,
                reviewed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$notes, $_SESSION['user_id'], $appId]);
        
        // Update user role and status
        $stmt = $pdo->prepare("
            UPDATE users SET 
                role = 'benefactor',
                benefactor_status = 'approved',
                benefactor_verified_at = NOW(),
                benefactor_verified_by = ?
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $app['user_id']]);
        
        // Create notification
        createNotification(
            $app['user_id'],
            'admin',
            'Đơn đăng ký nhà hảo tâm được duyệt',
            'Chúc mừng! Đơn đăng ký nhà hảo tâm của bạn đã được phê duyệt. Bạn có thể bắt đầu tạo sự kiện từ thiện ngay bây giờ.'
        );
        
        // Send email
        sendEmail(
            $app['email'],
            'Đơn đăng ký nhà hảo tâm được duyệt - ' . SITE_NAME,
            "Xin chào {$app['fullname']},\n\n" .
            "Chúc mừng! Đơn đăng ký nhà hảo tâm của bạn đã được phê duyệt.\n\n" .
            "Bạn giờ đây có thể:\n" .
            "- Tạo và quản lý sự kiện từ thiện\n" .
            "- Tiếp nhận quyên góp từ cộng đồng\n" .
            "- Quản lý tình nguyện viên\n\n" .
            "Hãy bắt đầu tạo sự kiện đầu tiên của bạn!\n\n" .
            "Trân trọng,\n" . SITE_NAME
        );
        
        // Log action
        $logFile = __DIR__ . '/../../logs/admin_actions.log';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, sprintf(
            "[%s] Admin #%d approved benefactor application #%d for user #%d (%s)\n",
            date('Y-m-d H:i:s'),
            $_SESSION['user_id'],
            $appId,
            $app['user_id'],
            $app['fullname']
        ), FILE_APPEND);
        
        // Update statistics
        $pdo->query("CALL update_statistics_cache()");
        
        $pdo->commit();
        notifyBenefactorApproved($app['user_id']);
        setFlashMessage('success', 'Đã duyệt nhà hảo tâm thành công');
        header('Location: benefactors.php?tab=approved');
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
    <title>Duyệt nhà hảo tâm - <?= SITE_NAME ?></title>
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
                        <a href="application_detail.php?id=<?= $appId ?>" class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        Duyệt nhà hảo tâm
                    </h1>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="mb-4">
                                    <i class="bi bi-award text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h3><?= htmlspecialchars($app['fullname']) ?></h3>
                                <p class="text-muted"><?= htmlspecialchars($app['email']) ?></p>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Xác nhận duyệt đơn</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Sau khi duyệt:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Người dùng sẽ được nâng cấp lên vai trò Nhà hảo tâm</li>
                                        <li>Họ có thể tạo và quản lý sự kiện từ thiện</li>
                                        <li>Email thông báo sẽ được gửi tự động</li>
                                        <li>Thống kê hệ thống sẽ được cập nhật</li>
                                    </ul>
                                </div>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Ghi chú (tùy chọn)</label>
                                        <textarea class="form-control" name="notes" rows="3" 
                                                  placeholder="Thêm ghi chú nếu cần..."></textarea>
                                        <small class="text-muted">Ghi chú này chỉ dành cho admin, không gửi cho người dùng</small>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="application_detail.php?id=<?= $appId ?>" class="btn btn-secondary">
                                            <i class="bi bi-x-lg"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-success btn-lg"
                                                onclick="return confirm('Bạn có chắc chắn muốn duyệt đơn này?')">
                                            <i class="bi bi-check-circle"></i> Xác nhận duyệt
                                        </button>
                                    </div>
                                </form>
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
