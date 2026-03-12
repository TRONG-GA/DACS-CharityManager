<?php
/**
 * ADMIN REJECT EVENT - UPDATED
 * Thêm notifyEventRejected() helper call
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';
 
requireAdmin();
 
$eventId = $_GET['id'] ?? 0;
 
// Get event info
$stmt = $pdo->prepare("
    SELECT e.*, u.fullname as benefactor_name, u.email as benefactor_email
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.id = ?
");
$stmt->execute([$eventId]);
$event = $stmt->fetch();
 
if (!$event) {
    setFlashMessage('error', 'Không tìm thấy sự kiện');
    header('Location: all_events.php');
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
            
            // Update event status
            $stmt = $pdo->prepare("
                UPDATE events SET 
                    status = 'rejected',
                    rejection_reason = ?
                WHERE id = ?
            ");
            $stmt->execute([$reason, $eventId]);
            
            // THÊM MỚI: Dùng helper function
            notifyEventRejected($event['user_id'], $eventId, $event['title'], $reason);
            
            // Send email
            sendEmail(
                $event['benefactor_email'],
                'Sự kiện chưa được phê duyệt - ' . SITE_NAME,
                "Xin chào {$event['benefactor_name']},\n\n" .
                "Rất tiếc, sự kiện \"{$event['title']}\" chưa được phê duyệt.\n\n" .
                "Lý do: {$reason}\n\n" .
                "Vui lòng chỉnh sửa và gửi lại.\n\n" .
                "Trân trọng,\n" . SITE_NAME
            );
            
            // Log action
            $logFile = __DIR__ . '/../../logs/admin_actions.log';
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0755, true);
            }
            file_put_contents($logFile, sprintf(
                "[%s] Admin #%d rejected event #%d: %s. Reason: %s\n",
                date('Y-m-d H:i:s'),
                $_SESSION['user_id'],
                $eventId,
                $event['title'],
                $reason
            ), FILE_APPEND);
            
            $pdo->commit();
            setFlashMessage('success', 'Đã từ chối sự kiện');
            header('Location: pending_events.php');
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
    <title>Từ chối sự kiện - <?= SITE_NAME ?></title>
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
                        <a href="event_detail.php?id=<?= $eventId ?>" class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        Từ chối sự kiện
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
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <?php if ($event['thumbnail']): ?>
                                    <img src="<?= BASE_URL ?>/public/uploads/events/<?= $event['thumbnail'] ?>" 
                                         alt="Thumbnail" class="img-fluid rounded mb-3" style="max-height: 200px;">
                                    <?php endif; ?>
                                    <h3><?= htmlspecialchars($event['title']) ?></h3>
                                    <p class="text-muted">
                                        Tổ chức bởi: <strong><?= htmlspecialchars($event['benefactor_name']) ?></strong>
                                    </p>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Cảnh báo:</strong> Bạn đang từ chối sự kiện này. 
                                    Nhà hảo tâm sẽ nhận được thông báo và có thể chỉnh sửa để gửi lại.
                                </div>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="reason" rows="5" 
                                                  placeholder="Nhập lý do từ chối chi tiết..." required></textarea>
                                        <small class="text-muted">
                                            Lý do này sẽ được gửi đến người tổ chức sự kiện. 
                                            Vui lòng viết rõ ràng để họ có thể chỉnh sửa.
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Gợi ý lý do thường gặp:</label>
                                        <div class="btn-group-vertical d-grid gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" 
                                                    onclick="document.querySelector('[name=reason]').value = this.textContent">
                                                Thông tin sự kiện chưa đầy đủ, cần bổ sung chi tiết
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" 
                                                    onclick="document.querySelector('[name=reason]').value = this.textContent">
                                                Hình ảnh không rõ ràng hoặc không liên quan
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" 
                                                    onclick="document.querySelector('[name=reason]').value = this.textContent">
                                                Mục tiêu quyên góp chưa hợp lý
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" 
                                                    onclick="document.querySelector('[name=reason]').value = this.textContent">
                                                Nội dung vi phạm quy định cộng đồng
                                            </button>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="event_detail.php?id=<?= $eventId ?>" class="btn btn-secondary">
                                            <i class="bi bi-x-lg"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-danger">
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
