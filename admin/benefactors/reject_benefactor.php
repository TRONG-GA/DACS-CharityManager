<?php
/**
 * Admin - Reject Benefactor Application
 * Từ chối đơn đăng ký nhà hảo tâm
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
    setFlashMessage('error', 'Không tìm thấy đơn đăng ký');
    header('Location: benefactors.php');
    exit;
}

$errors = [];

// Handle rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = sanitize($_POST['reason'] ?? '');
    
    if (empty($reason)) {
        $errors[] = 'Vui lòng nhập lý do từ chối';
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Update application
            $stmt = $pdo->prepare("
                UPDATE benefactor_applications SET 
                    status = 'rejected',
                    rejection_reason = ?,
                    reviewed_by = ?,
                    reviewed_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$reason, $_SESSION['user_id'], $appId]);
            
            // Update user benefactor_status
            $stmt = $pdo->prepare("UPDATE users SET benefactor_status = 'rejected' WHERE id = ?");
            $stmt->execute([$app['user_id']]);
            
            // Notification
            createNotification(
                $app['user_id'],
                'admin',
                'Đơn đăng ký chưa được duyệt',
                'Đơn đăng ký nhà hảo tâm của bạn chưa được phê duyệt. Lý do: ' . $reason
            );
            
            // Email
            sendEmail(
                $app['email'],
                'Đơn đăng ký nhà hảo tâm - ' . SITE_NAME,
                "Xin chào {$app['fullname']},\n\n" .
                "Rất tiếc, đơn đăng ký nhà hảo tâm của bạn chưa được phê duyệt.\n\n" .
                "Lý do: {$reason}\n\n" .
                "Bạn có thể hoàn thiện hồ sơ và nộp đơn lại.\n\n" .
                "Trân trọng,\n" . SITE_NAME
            );
            
            // Log
            $logFile = __DIR__ . '/../../logs/admin_actions.log';
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0755, true);
            }
            file_put_contents($logFile, sprintf(
                "[%s] Admin #%d rejected benefactor application #%d. Reason: %s\n",
                date('Y-m-d H:i:s'),
                $_SESSION['user_id'],
                $appId,
                $reason
            ), FILE_APPEND);
            
            $pdo->commit();
            notifyBenefactorRejected($app['user_id'], $reason);
            setFlashMessage('success', 'Đã từ chối đơn đăng ký');
            header('Location: benefactors.php?tab=rejected');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Từ chối đơn đăng ký - <?= SITE_NAME ?></title>
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
                        Từ chối đơn đăng ký
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
                                <h3><?= htmlspecialchars($app['fullname']) ?></h3>
                                <p class="text-muted"><?= htmlspecialchars($app['email']) ?></p>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">Từ chối đơn đăng ký</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Người dùng sẽ nhận được email thông báo với lý do từ chối. 
                                    Họ có thể nộp lại đơn sau khi hoàn thiện.
                                </div>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="reason" rows="5" 
                                                  placeholder="Nhập lý do từ chối chi tiết..." required></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Gợi ý lý do:</label>
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-outline-secondary text-start" 
                                                    onclick="document.querySelector('[name=reason]').value = this.textContent">
                                                Giấy tờ tùy thân không rõ ràng hoặc không hợp lệ
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary text-start" 
                                                    onclick="document.querySelector('[name=reason]').value = this.textContent">
                                                Thông tin ngân hàng chưa đầy đủ hoặc không khớp
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary text-start" 
                                                    onclick="document.querySelector('[name=reason]').value = this.textContent">
                                                Lý do đăng ký chưa rõ ràng hoặc thiếu thuyết phục
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary text-start" 
                                                    onclick="document.querySelector('[name=reason]').value = this.textContent">
                                                Thông tin cá nhân không khớp với giấy tờ
                                            </button>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="application_detail.php?id=<?= $appId ?>" class="btn btn-secondary">
                                            <i class="bi bi-x-lg"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('Bạn có chắc chắn muốn từ chối đơn này?')">
                                            <i class="bi bi-x-circle"></i> Xác nhận từ chối
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
