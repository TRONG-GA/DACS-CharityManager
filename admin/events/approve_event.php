<?php
/**
 * ADMIN APPROVE EVENT - UPDATED
 * Thêm notifyEventApproved() helper call
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
 
if ($event['status'] !== 'pending') {
    setFlashMessage('warning', 'Sự kiện này đã được xử lý');
    header('Location: event_detail.php?id=' . $eventId);
    exit;
}
 
// Handle approval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Update event status
        $stmt = $pdo->prepare("
            UPDATE events SET 
                status = 'approved',
                approved_by = ?,
                approved_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $eventId]);
        
        // THÊM MỚI: Dùng helper function
        notifyEventApproved($event['user_id'], $eventId, $event['title']);
        
        // Send email
        sendEmail(
            $event['benefactor_email'],
            'Sự kiện được phê duyệt - ' . SITE_NAME,
            "Xin chào {$event['benefactor_name']},\n\n" .
            "Sự kiện \"{$event['title']}\" của bạn đã được phê duyệt!\n\n" .
            "Sự kiện giờ đã được hiển thị công khai và mọi người có thể quyên góp.\n\n" .
            "Trân trọng,\n" . SITE_NAME
        );
        
        // Log action
        $logFile = __DIR__ . '/../../logs/admin_actions.log';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, sprintf(
            "[%s] Admin #%d approved event #%d: %s\n",
            date('Y-m-d H:i:s'),
            $_SESSION['user_id'],
            $eventId,
            $event['title']
        ), FILE_APPEND);
        
        $pdo->commit();
        updateStatistics();
        
        setFlashMessage('success', 'Đã duyệt sự kiện thành công');
        header('Location: pending_events.php');
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
    }
}
?>
<!-- HTML giữ nguyên... -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyệt sự kiện - <?= SITE_NAME ?></title>
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
                        <a href="pending_events.php" class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        Duyệt sự kiện
                    </h1>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <?php if ($event['thumbnail']): ?>
                                    <img src="<?= BASE_URL ?>/public/uploads/events/<?= $event['thumbnail'] ?>" 
                                         alt="Thumbnail" class="img-fluid rounded mb-3" style="max-height: 300px;">
                                    <?php endif; ?>
                                    <h3><?= htmlspecialchars($event['title']) ?></h3>
                                    <p class="text-muted">
                                        Tổ chức bởi: <strong><?= htmlspecialchars($event['benefactor_name']) ?></strong>
                                    </p>
                                </div>

                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle"></i>
                                    <strong>Xác nhận duyệt sự kiện</strong>
                                    <p class="mb-0 mt-2">
                                        Sau khi duyệt, sự kiện sẽ được hiển thị công khai và mọi người có thể quyên góp.
                                        Nhà hảo tâm sẽ nhận được thông báo qua email.
                                    </p>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Danh mục:</strong> <?= ucfirst($event['category']) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Địa điểm:</strong> <?= htmlspecialchars($event['location']) ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Mục tiêu:</strong> 
                                        <span class="text-success"><?= formatMoney($event['target_amount']) ?></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Thời gian:</strong> 
                                        <?= formatDate($event['start_date']) ?> - <?= formatDate($event['end_date']) ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <strong>Mô tả:</strong>
                                    <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                                </div>

                                <form method="POST">
                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="pending_events.php" class="btn btn-secondary">
                                            <i class="bi bi-x-lg"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-check-lg"></i> Xác nhận duyệt sự kiện
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
