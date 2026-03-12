<?php
/**
 * Admin - Verify Donation
 * Xác minh quyên góp
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$donationId = $_GET['id'] ?? 0;

// Get donation info
$stmt = $pdo->prepare("
    SELECT d.*, e.title as event_title, e.user_id as benefactor_id,
           u.fullname as donor_fullname, u.email as donor_email
    FROM donations d
    JOIN events e ON d.event_id = e.id
    LEFT JOIN users u ON d.user_id = u.id
    WHERE d.id = ?
");
$stmt->execute([$donationId]);
$donation = $stmt->fetch();

if (!$donation) {
    setFlashMessage('error', 'Không tìm thấy quyên góp');
    header('Location: donations.php');
    exit;
}

$errors = [];

// Handle verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (!in_array($action, ['approve', 'reject'])) {
        $errors[] = 'Hành động không hợp lệ';
    }
    
    if ($action === 'reject' && empty($notes)) {
        $errors[] = 'Vui lòng nhập lý do từ chối';
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            if ($action === 'approve') {
                // Update donation status
                $stmt = $pdo->prepare("UPDATE donations SET status = 'completed' WHERE id = ?");
                $stmt->execute([$donationId]);
                
                // Update event current_amount
                $stmt = $pdo->prepare("
                    UPDATE events SET current_amount = current_amount + ? WHERE id = ?
                ");
                $stmt->execute([$donation['amount'], $donation['event_id']]);
                
                // Create notification for donor
                if ($donation['user_id']) {
                    createNotification(
                        $donation['user_id'],
                        'donation',
                        'Quyên góp thành công',
                        'Quyên góp ' . formatMoney($donation['amount']) . ' cho sự kiện "' . $donation['event_title'] . '" đã được xác nhận. Cảm ơn bạn!'
                    );
                }
                
                // Create notification for benefactor
                createNotification(
                    $donation['benefactor_id'],
                    'donation',
                    'Có quyên góp mới',
                    'Sự kiện "' . $donation['event_title'] . '" vừa nhận được quyên góp ' . formatMoney($donation['amount'])
                );
                
                // Update statistics
                $pdo->query("CALL update_statistics_cache()");
                
                setFlashMessage('success', 'Đã xác nhận quyên góp thành công');
                
            } else {
                // Reject donation
                $stmt = $pdo->prepare("UPDATE donations SET status = 'failed' WHERE id = ?");
                $stmt->execute([$donationId]);
                
                if ($donation['user_id']) {
                    createNotification(
                        $donation['user_id'],
                        'donation',
                        'Quyên góp không thành công',
                        'Quyên góp cho sự kiện "' . $donation['event_title'] . '" không được xác nhận. Lý do: ' . $notes
                    );
                }
                
                setFlashMessage('warning', 'Đã từ chối quyên góp');
            }
            
            // Log action
            $logFile = __DIR__ . '/../../logs/admin_actions.log';
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0755, true);
            }
            file_put_contents($logFile, sprintf(
                "[%s] Admin #%d %s donation #%d (Amount: %s)\n",
                date('Y-m-d H:i:s'),
                $_SESSION['user_id'],
                $action === 'approve' ? 'approved' : 'rejected',
                $donationId,
                formatMoney($donation['amount'])
            ), FILE_APPEND);
            
            $pdo->commit();
            header('Location: donations.php?status=pending');
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
    <title>Xác minh quyên góp - <?= SITE_NAME ?></title>
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
                        <a href="donations.php" class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        Xác minh quyên góp
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
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Thông tin quyên góp</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>ID:</strong> #<?= $donation['id'] ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Trạng thái:</strong>
                                        <span class="badge bg-warning">Chờ xác nhận</span>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Số tiền:</strong>
                                        <h4 class="text-success mb-0"><?= formatMoney($donation['amount']) ?></h4>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Phương thức:</strong>
                                        <?php
                                        $methodLabels = [
                                            'bank_transfer' => 'Chuyển khoản ngân hàng',
                                            'momo' => 'MoMo',
                                            'vnpay' => 'VNPay',
                                            'zalopay' => 'ZaloPay',
                                            'cash' => 'Tiền mặt'
                                        ];
                                        ?>
                                        <p class="mb-0"><?= $methodLabels[$donation['payment_method']] ?></p>
                                    </div>
                                </div>

                                <hr>

                                <div class="mb-3">
                                    <strong>Người quyên góp:</strong>
                                    <p class="mb-0">
                                        <?php if ($donation['is_anonymous']): ?>
                                            <span class="text-muted">Ẩn danh</span>
                                        <?php else: ?>
                                            <?= htmlspecialchars($donation['donor_fullname'] ?? $donation['donor_name']) ?>
                                            <?php if ($donation['donor_email']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($donation['donor_email']) ?></small>
                                            <?php endif; ?>
                                            <?php if ($donation['donor_phone']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($donation['donor_phone']) ?></small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <strong>Sự kiện:</strong>
                                    <p class="mb-0">
                                        <a href="../events/event_detail.php?id=<?= $donation['event_id'] ?>">
                                            <?= htmlspecialchars($donation['event_title']) ?>
                                        </a>
                                    </p>
                                </div>

                                <?php if ($donation['message']): ?>
                                <div class="mb-3">
                                    <strong>Lời nhắn:</strong>
                                    <p class="mb-0 fst-italic">"<?= htmlspecialchars($donation['message']) ?>"</p>
                                </div>
                                <?php endif; ?>

                                <?php if ($donation['transaction_id']): ?>
                                <div class="mb-3">
                                    <strong>Mã giao dịch:</strong>
                                    <p class="mb-0"><code><?= htmlspecialchars($donation['transaction_id']) ?></code></p>
                                </div>
                                <?php endif; ?>

                                <?php if ($donation['payment_proof']): ?>
                                <div class="mb-3">
                                    <strong>Chứng từ thanh toán:</strong>
                                    <div class="mt-2">
                                        <a href="<?= BASE_URL ?>/public/uploads/<?= $donation['payment_proof'] ?>" 
                                           target="_blank">
                                            <img src="<?= BASE_URL ?>/public/uploads/<?= $donation['payment_proof'] ?>" 
                                                 alt="Chứng từ" class="img-fluid" style="max-height: 400px;">
                                        </a>
                                        <br>
                                        <small class="text-muted">Click để xem ảnh lớn</small>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <strong>Thời gian:</strong>
                                    <p class="mb-0"><?= formatDate($donation['created_at'], 'd/m/Y H:i:s') ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Form -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Xác minh</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Ghi chú (tùy chọn)</label>
                                        <textarea class="form-control" name="notes" rows="3" 
                                                  placeholder="Thêm ghi chú nếu cần..."></textarea>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <a href="donations.php" class="btn btn-secondary">
                                                <i class="bi bi-x-lg"></i> Hủy
                                            </a>
                                        </div>
                                        <div>
                                            <button type="submit" name="action" value="reject" 
                                                    class="btn btn-danger me-2"
                                                    onclick="return confirm('Bạn có chắc muốn từ chối quyên góp này?')">
                                                <i class="bi bi-x-circle"></i> Từ chối
                                            </button>
                                            <button type="submit" name="action" value="approve" 
                                                    class="btn btn-success"
                                                    onclick="return confirm('Xác nhận quyên góp này?')">
                                                <i class="bi bi-check-circle"></i> Xác nhận
                                            </button>
                                        </div>
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
