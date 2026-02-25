<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
// Nếu đã đăng nhập, chuyển về trang chủ
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pageTitle = 'Quên mật khẩu - ' . SITE_NAME;

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $email = trim($_POST['email'] ?? '');
    
    // Rate limiting
    $rateLimit = checkRateLimit('forgot_password', getClientIP(), 3, 3600); // 3 lần/giờ
    if ($rateLimit !== true) {
        setFlashMessage('error', $rateLimit['message']);
    } elseif (empty($email) || !validateEmail($email)) {
        setFlashMessage('error', 'Email không hợp lệ');
    } else {
        // Kiểm tra email có tồn tại không
        $stmt = $pdo->prepare("SELECT id, fullname, email FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Tạo reset token
            $resetToken = bin2hex(random_bytes(32));
            $resetExpiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            
            // Lưu token vào database
            $stmt = $pdo->prepare("
                INSERT INTO password_resets (email, token, created_at) 
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                token = ?, created_at = NOW()
            ");
            $stmt->execute([$email, $resetToken, $resetToken]);
            
            // Tạo link reset
            $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/auth/reset_password.php?token=' . $resetToken;
            
            // Gửi email (mock - ghi vào file)
            $emailSubject = 'Đặt lại mật khẩu - ' . SITE_NAME;
            $emailBody = "
Xin chào {$user['fullname']},

Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản {$email}.

Vui lòng click vào link sau để đặt lại mật khẩu:
{$resetLink}

Link này sẽ hết hạn sau 1 giờ.

Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.

Trân trọng,
Đội ngũ " . SITE_NAME . "
            ";
            
            sendEmail($email, $emailSubject, $emailBody);
            
            // Log
            $logFile = dirname(__DIR__) . '/logs/password_resets.log';
            $logContent = sprintf(
                "[%s] Password Reset Requested - Email: %s | IP: %s\n",
                date('Y-m-d H:i:s'),
                $email,
                getClientIP()
            );
            file_put_contents($logFile, $logContent, FILE_APPEND);
        }
        
        // Luôn hiển thị thông báo thành công (tránh lộ thông tin user)
        setFlashMessage('success', 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu. Vui lòng kiểm tra email (cả thư mục Spam).');
        
        clearRateLimit('forgot_password', getClientIP());
    }
}

$flash = getFlashMessage();
include __DIR__ . '/../includes/header.php';
?>

<style>
.forgot-password-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px 0;
}

.forgot-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    padding: 60px;
    max-width: 500px;
    margin: 0 auto;
}

.forgot-card .icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
}

.forgot-card .icon i {
    font-size: 2rem;
    color: white;
}

.forgot-card h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 15px;
    text-align: center;
}

.forgot-card .subtitle {
    color: #666;
    text-align: center;
    margin-bottom: 30px;
}

.btn-auth {
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s;
}

.btn-auth:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
}

@media (max-width: 576px) {
    .forgot-card {
        padding: 40px 30px;
    }
}
</style>

<div class="forgot-password-page">
    <div class="container">
        <div class="forgot-card">
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            
            <h3>Quên mật khẩu?</h3>
            <p class="subtitle">
                Nhập email đăng ký của bạn, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu
            </p>
            
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <i class="fas fa-<?= $flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <?= csrfField() ?>
                
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-envelope text-danger me-2"></i>Email đã đăng ký
                    </label>
                    <input type="email" 
                           name="email" 
                           class="form-control form-control-lg" 
                           placeholder="email@example.com"
                           required
                           autofocus>
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-danger btn-lg btn-auth">
                        <i class="fas fa-paper-plane me-2"></i>Gửi yêu cầu
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="<?= BASE_URL ?>/auth/login.php" class="text-danger">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại đăng nhập
                    </a>
                </div>
            </form>
            
            <div class="alert alert-info mt-4 mb-0">
                <small>
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Lưu ý:</strong> Link đặt lại mật khẩu sẽ hết hạn sau 1 giờ. 
                    Nếu không nhận được email, vui lòng kiểm tra thư mục Spam.
                </small>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
