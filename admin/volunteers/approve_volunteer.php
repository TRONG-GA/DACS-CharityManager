<?php
/**
 * Admin - Approve Volunteer
 * Duyệt tình nguyện viên
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$volunteerId = $_GET['id'] ?? 0;

// Get volunteer info
$stmt = $pdo->prepare("
    SELECT ev.*, e.title as event_title, e.user_id as benefactor_id, u.fullname as user_fullname
    FROM event_volunteers ev
    JOIN events e ON ev.event_id = e.id
    LEFT JOIN users u ON ev.user_id = u.id
    WHERE ev.id = ?
");
$stmt->execute([$volunteerId]);
$volunteer = $stmt->fetch();

if (!$volunteer || $volunteer['status'] !== 'pending') {
    setFlashMessage('error', 'Không tìm thấy đơn đăng ký hoặc đã được xử lý');
    header('Location: volunteers.php');
    exit;
}

// Handle approval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Update volunteer status
        $stmt = $pdo->prepare("
            UPDATE event_volunteers SET 
                status = 'approved',
                reviewed_at = NOW(),
                reviewed_by = ?
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $volunteerId]);
        
        // Update event volunteer count
        $stmt = $pdo->prepare("
            UPDATE events SET volunteer_registered = volunteer_registered + 1 
            WHERE id = ?
        ");
        $stmt->execute([$volunteer['event_id']]);
        
        // Notification for volunteer
        if ($volunteer['user_id']) {
            createNotification(
                $volunteer['user_id'],
                'volunteer',
                'Đăng ký tình nguyện được duyệt',
                'Đăng ký tình nguyện viên cho sự kiện "' . $volunteer['event_title'] . '" đã được phê duyệt!'
            );
        }
        
        // Notification for benefactor
        createNotification(
            $volunteer['benefactor_id'],
            'volunteer',
            'Có tình nguyện viên mới',
            'Sự kiện "' . $volunteer['event_title'] . '" có tình nguyện viên mới: ' . $volunteer['fullname']
        );
        
        // Send email
        sendEmail(
            $volunteer['email'],
            'Đăng ký tình nguyện được duyệt - ' . SITE_NAME,
            "Xin chào {$volunteer['fullname']},\n\n" .
            "Đăng ký tình nguyện viên của bạn cho sự kiện \"{$volunteer['event_title']}\" đã được phê duyệt!\n\n" .
            "Vui lòng theo dõi thông tin chi tiết về sự kiện và liên hệ với ban tổ chức.\n\n" .
            "Cảm ơn bạn đã tham gia!\n\n" .
            "Trân trọng,\n" . SITE_NAME
        );
        
        $pdo->commit();
        setFlashMessage('success', 'Đã duyệt tình nguyện viên');
        header('Location: volunteers.php?status=approved');
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyệt tình nguyện viên - <?= SITE_NAME ?></title>
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
                        <a href="volunteers.php" class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        Duyệt tình nguyện viên
                    </h1>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-check-circle"></i> Xác nhận duyệt</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Họ tên:</strong><br>
                                    <?= htmlspecialchars($volunteer['fullname']) ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Email:</strong><br>
                                    <?= htmlspecialchars($volunteer['email']) ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Số điện thoại:</strong><br>
                                    <?= htmlspecialchars($volunteer['phone']) ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Sự kiện:</strong><br>
                                    <a href="../events/event_detail.php?id=<?= $volunteer['event_id'] ?>">
                                        <?= htmlspecialchars($volunteer['event_title']) ?>
                                    </a>
                                </div>
                                <?php if ($volunteer['skills']): ?>
                                <div class="mb-3">
                                    <strong>Kỹ năng:</strong><br>
                                    <?= nl2br(htmlspecialchars($volunteer['skills'])) ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($volunteer['motivation']): ?>
                                <div class="mb-3">
                                    <strong>Lý do tham gia:</strong><br>
                                    <?= nl2br(htmlspecialchars($volunteer['motivation'])) ?>
                                </div>
                                <?php endif; ?>

                                <div class="alert alert-success mt-4">
                                    <i class="bi bi-info-circle"></i> Sau khi duyệt, tình nguyện viên sẽ nhận được email thông báo.
                                </div>

                                <form method="POST">
                                    <div class="d-flex justify-content-between">
                                        <a href="volunteers.php" class="btn btn-secondary">
                                            <i class="bi bi-x-lg"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Xác nhận duyệt tình nguyện viên này?')">
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
