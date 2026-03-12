<?php
/**
 * Admin - Close Event admin/close_event.php
 * Đóng sự kiện
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$eventId = $_GET['id'] ?? 0;

// Get event info
$stmt = $pdo->prepare("
    SELECT e.*, u.fullname as benefactor_name, u.email as benefactor_email,
           COALESCE(SUM(d.amount), 0) as total_raised
    FROM events e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN donations d ON e.id = d.event_id AND d.status = 'completed'
    WHERE e.id = ?
    GROUP BY e.id
");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    setFlashMessage('error', 'Không tìm thấy sự kiện');
    header('Location: all_events.php');
    exit;
}

if (!in_array($event['status'], ['approved', 'ongoing', 'completed'])) {
    setFlashMessage('error', 'Không thể đóng sự kiện này');
    header('Location: event_detail.php?id=' . $eventId);
    exit;
}

$errors = [];

// Handle closing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = sanitize($_POST['reason'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($reason)) {
        $errors[] = 'Vui lòng chọn lý do đóng sự kiện';
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Update event status
            $stmt = $pdo->prepare("
                UPDATE events SET 
                    status = 'closed',
                    admin_notes = ?
                WHERE id = ?
            ");
            $stmt->execute([$reason . ($notes ? "\n\nGhi chú: " . $notes : ''), $eventId]);
            
            // Notification
            createNotification(
                $event['user_id'],
                'admin',
                'Sự kiện đã đóng',
                'Sự kiện "' . $event['title'] . '" đã được đóng. Lý do: ' . $reason
            );
            
            // Email
            sendEmail(
                $event['benefactor_email'],
                'Sự kiện đã đóng - ' . SITE_NAME,
                "Xin chào {$event['benefactor_name']},\n\n" .
                "Sự kiện \"{$event['title']}\" đã được đóng.\n\n" .
                "Lý do: {$reason}\n\n" .
                "Tổng quyên góp: " . formatMoney($event['total_raised']) . "\n\n" .
                "Vui lòng hoàn tất báo cáo minh bạch trong vòng 30 ngày.\n\n" .
                "Trân trọng,\n" . SITE_NAME
            );
            
            // Log
            $logFile = __DIR__ . '/../../logs/admin_actions.log';
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0755, true);
            }
            file_put_contents($logFile, sprintf(
                "[%s] Admin #%d closed event #%d: %s. Reason: %s\n",
                date('Y-m-d H:i:s'),
                $_SESSION['user_id'],
                $eventId,
                $event['title'],
                $reason
            ), FILE_APPEND);
            
            $pdo->commit();
            setFlashMessage('success', 'Đã đóng sự kiện');
            header('Location: all_events.php?status=closed');
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
    <title>Đóng sự kiện - <?= SITE_NAME ?></title>
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
                        Đóng sự kiện
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
                                    <p class="text-muted">Tổ chức bởi: <?= htmlspecialchars($event['benefactor_name']) ?></p>
                                </div>

                                <!-- Statistics -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-light rounded">
                                            <h6 class="text-muted mb-1">Mục tiêu</h6>
                                            <h5><?= formatMoney($event['target_amount']) ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                            <h6 class="text-muted mb-1">Đã quyên góp</h6>
                                            <h5 class="text-success"><?= formatMoney($event['total_raised']) ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-light rounded">
                                            <h6 class="text-muted mb-1">Tỷ lệ</h6>
                                            <h5>
                                                <?php 
                                                $percentage = $event['target_amount'] > 0 
                                                    ? ($event['total_raised'] / $event['target_amount']) * 100 
                                                    : 0;
                                                echo number_format($percentage, 1);
                                                ?>%
                                            </h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Lưu ý:</strong> Sau khi đóng sự kiện:
                                    <ul class="mb-0 mt-2">
                                        <li>Sự kiện sẽ không nhận thêm quyên góp</li>
                                        <li>Nhà hảo tâm phải xuất báo cáo minh bạch trong 30 ngày</li>
                                        <li>Không thể mở lại sự kiện</li>
                                    </ul>
                                </div>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Lý do đóng sự kiện <span class="text-danger">*</span></label>
                                        <select class="form-select" name="reason" required>
                                            <option value="">-- Chọn lý do --</option>
                                            <option value="Sự kiện đã hoàn thành">Sự kiện đã hoàn thành</option>
                                            <option value="Đã đạt mục tiêu quyên góp">Đã đạt mục tiêu quyên góp</option>
                                            <option value="Hết thời gian quyên góp">Hết thời gian quyên góp</option>
                                            <option value="Vi phạm quy định">Vi phạm quy định</option>
                                            <option value="Yêu cầu của nhà hảo tâm">Yêu cầu của nhà hảo tâm</option>
                                            <option value="Khác">Khác</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Ghi chú (tùy chọn)</label>
                                        <textarea class="form-control" name="notes" rows="3" 
                                                  placeholder="Thêm ghi chú nếu cần..."></textarea>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="event_detail.php?id=<?= $eventId ?>" class="btn btn-secondary">
                                            <i class="bi bi-x-lg"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-warning"
                                                onclick="return confirm('Bạn có chắc chắn muốn đóng sự kiện này?\n\nHành động này không thể hoàn tác!')">
                                            <i class="bi bi-archive"></i> Xác nhận đóng sự kiện
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
