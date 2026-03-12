<?php
/**
 * Admin - Ban/Unban User
 * Khóa hoặc mở khóa tài khoản người dùng
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$userId = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? 'ban';

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'Không tìm thấy người dùng');
    header('Location: users.php');
    exit;
}

// Don't allow banning admin accounts
if ($user['role'] === 'admin' && $action === 'ban') {
    setFlashMessage('error', 'Không thể khóa tài khoản admin');
    header('Location: user_detail.php?id=' . $userId);
    exit;
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = sanitize($_POST['reason'] ?? '');
    
    if ($action === 'ban' && empty($reason)) {
        $errors[] = 'Vui lòng nhập lý do khóa tài khoản';
    }
    
    if (empty($errors)) {
        try {
            if ($action === 'ban') {
                // Ban user
                $stmt = $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
                $stmt->execute([$userId]);
                
                // Create notification
                createNotification(
                    $userId,
                    'admin',
                    'Tài khoản bị khóa',
                    'Tài khoản của bạn đã bị khóa. Lý do: ' . $reason
                );
                
                // Log action
                $logFile = __DIR__ . '/../../logs/admin_actions.log';
                $logDir = dirname($logFile);
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
                file_put_contents($logFile, sprintf(
                    "[%s] Admin #%d banned user #%d. Reason: %s\n",
                    date('Y-m-d H:i:s'),
                    $_SESSION['user_id'],
                    $userId,
                    $reason
                ), FILE_APPEND);
                
                setFlashMessage('success', 'Đã khóa tài khoản người dùng');
            } else {
                // Unban user
                $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $stmt->execute([$userId]);
                
                // Create notification
                createNotification(
                    $userId,
                    'admin',
                    'Tài khoản được mở khóa',
                    'Tài khoản của bạn đã được mở khóa. Bạn có thể đăng nhập trở lại.'
                );
                
                setFlashMessage('success', 'Đã mở khóa tài khoản người dùng');
            }
            
            header('Location: user_detail.php?id=' . $userId);
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Lỗi: ' . $e->getMessage();
        }
    }
}

$pageTitle = $action === 'ban' ? 'Khóa tài khoản' : 'Mở khóa tài khoản';
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
                    <h1 class="h2">
                        <a href="user_detail.php?id=<?= $userId ?>" class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <?= $pageTitle ?>
                    </h1>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Lỗi:</strong>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= $user['avatar'] ?>" 
                                         alt="Avatar" class="rounded-circle mb-3" 
                                         style="width: 100px; height: 100px; object-fit: cover;"
                                         onerror="this.src='<?= BASE_URL ?>/public/uploads/avatars/default-avatar.png'">
                                    <h4><?= htmlspecialchars($user['fullname']) ?></h4>
                                    <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                                </div>

                                <?php if ($action === 'ban'): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Cảnh báo:</strong> Bạn đang khóa tài khoản này. 
                                    Người dùng sẽ không thể đăng nhập cho đến khi được mở khóa.
                                </div>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Lý do khóa tài khoản <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="reason" rows="4" 
                                                  placeholder="Nhập lý do khóa tài khoản..." required></textarea>
                                        <small class="text-muted">Lý do này sẽ được gửi đến người dùng</small>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="user_detail.php?id=<?= $userId ?>" class="btn btn-secondary">
                                            <i class="bi bi-x-lg"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-lock"></i> Khóa tài khoản
                                        </button>
                                    </div>
                                </form>

                                <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Xác nhận:</strong> Bạn đang mở khóa tài khoản này. 
                                    Người dùng sẽ có thể đăng nhập trở lại.
                                </div>

                                <form method="POST">
                                    <div class="d-flex justify-content-between">
                                        <a href="user_detail.php?id=<?= $userId ?>" class="btn btn-secondary">
                                            <i class="bi bi-x-lg"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-unlock"></i> Mở khóa tài khoản
                                        </button>
                                    </div>
                                </form>
                                <?php endif; ?>
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
