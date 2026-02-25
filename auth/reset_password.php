<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
// Nếu đã đăng nhập, chuyển về trang chủ
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pageTitle = 'Đặt lại mật khẩu - ' . SITE_NAME;

// Lấy token từ URL
$token = $_GET['token'] ?? '';
$validToken = false;
$email = '';

// Kiểm tra token
if (!empty($token)) {
    $stmt = $pdo->prepare("
        SELECT email, created_at 
        FROM password_resets 
        WHERE token = ?
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if ($reset) {
        // Kiểm tra token còn hạn không (1 giờ)
        $createdTime = strtotime($reset['created_at']);
        $currentTime = time();
        
        if (($currentTime - $createdTime) <= 3600) {
            $validToken = true;
            $email = $reset['email'];
        }
    }
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    requireCSRF();
    
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['password_confirm'] ?? '';
    
    $errors = [];
    
    // Validate password
    if (empty($newPassword)) {
        $errors[] = 'Vui lòng nhập mật khẩu mới';
    } else {
        $passwordValidation = validatePassword($newPassword);
        if (!$passwordValidation['valid']) {
            $errors = array_merge($errors, $passwordValidation['errors']);
        }
    }
    
    // Validate confirmation
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }
    
    if (empty($errors)) {
        try {
            // Hash password mới
            $passwordHash = hashPassword($newPassword);
            
            // Cập nhật password
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$passwordHash, $email]);
            
            // Xóa token đã sử dụng
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);
            $logFile = dirname(__DIR__) . '/logs/password_resets.log';
            // Log
            $logContent = sprintf(
                "[%s] Password Reset Success - Email: %s | IP: %s\n",
                date('Y-m-d H:i:s'),
                $email,
                getClientIP()
            );
            file_put_contents($logFile, $logContent, FILE_APPEND);
            
            setFlashMessage('success', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.');
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
            
        } catch (PDOException $e) {
            error_log("Reset Password Error: " . $e->getMessage());
            $errors[] = 'Đã xảy ra lỗi. Vui lòng thử lại sau.';
        }
    }
    
    if (!empty($errors)) {
        setFlashMessage('error', implode('<br>', $errors));
    }
}

$flash = getFlashMessage();
include __DIR__ . '/../includes/header.php';
?>

<style>
.reset-password-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px 0;
}

.reset-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    padding: 60px;
    max-width: 500px;
    margin: 0 auto;
}

.reset-card .icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
}

.reset-card .icon i {
    font-size: 2rem;
    color: white;
}

.reset-card h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 15px;
    text-align: center;
}

.reset-card .subtitle {
    color: #666;
    text-align: center;
    margin-bottom: 30px;
}

.password-strength {
    height: 5px;
    border-radius: 3px;
    margin-top: 5px;
    transition: all 0.3s;
}

.strength-weak { background: #dc3545; width: 33%; }
.strength-medium { background: #ffc107; width: 66%; }
.strength-strong { background: #28a745; width: 100%; }

.password-requirements {
    font-size: 0.85rem;
    margin-top: 10px;
}

.password-requirements li {
    color: #999;
}

.password-requirements li.valid {
    color: #28a745;
}

.password-requirements li.invalid {
    color: #dc3545;
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
    .reset-card {
        padding: 40px 30px;
    }
}
</style>

<div class="reset-password-page">
    <div class="container">
        <div class="reset-card">
            <?php if (!$validToken): ?>
                <!-- Token không hợp lệ hoặc hết hạn -->
                <div class="text-center">
                    <div class="icon">
                        <i class="fas fa-times"></i>
                    </div>
                    <h3>Link không hợp lệ</h3>
                    <p class="subtitle">Link đặt lại mật khẩu đã hết hạn hoặc không tồn tại</p>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Link chỉ có hiệu lực trong 1 giờ kể từ khi gửi yêu cầu.
                    </div>
                    
                    <a href="<?= BASE_URL ?>/auth/forgot_password.php" class="btn btn-danger btn-auth">
                        <i class="fas fa-redo me-2"></i>Gửi lại yêu cầu
                    </a>
                    
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/auth/login.php" class="text-danger">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại đăng nhập
                        </a>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Form đặt lại mật khẩu -->
                <div class="icon">
                    <i class="fas fa-lock"></i>
                </div>
                
                <h3>Đặt lại mật khẩu</h3>
                <p class="subtitle">
                    Nhập mật khẩu mới cho tài khoản: <strong><?= sanitize($email) ?></strong>
                </p>
                
                <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                    <i class="fas fa-<?= $flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form action="" method="POST" id="resetForm">
                    <?= csrfField() ?>
                    
                    <!-- Mật khẩu mới -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-lock text-danger me-2"></i>Mật khẩu mới
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   class="form-control form-control-lg" 
                                   placeholder="Nhập mật khẩu mới"
                                   required
                                   minlength="8"
                                   autofocus>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                        
                        <ul class="password-requirements mt-2 small">
                            <li id="req-length">
                                <i class="fas fa-circle"></i> Ít nhất 8 ký tự
                            </li>
                            <li id="req-uppercase">
                                <i class="fas fa-circle"></i> Có chữ hoa (A-Z)
                            </li>
                            <li id="req-lowercase">
                                <i class="fas fa-circle"></i> Có chữ thường (a-z)
                            </li>
                            <li id="req-number">
                                <i class="fas fa-circle"></i> Có số (0-9)
                            </li>
                            <li id="req-special">
                                <i class="fas fa-circle"></i> Có ký tự đặc biệt (!@#$...)
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Xác nhận mật khẩu -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-lock text-danger me-2"></i>Xác nhận mật khẩu
                        </label>
                        <input type="password" 
                               name="password_confirm" 
                               id="passwordConfirm"
                               class="form-control form-control-lg" 
                               placeholder="Nhập lại mật khẩu"
                               required>
                        <div class="invalid-feedback" id="passwordMatchError">
                            Mật khẩu xác nhận không khớp
                        </div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-danger btn-lg btn-auth">
                            <i class="fas fa-check me-2"></i>Đặt lại mật khẩu
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <a href="<?= BASE_URL ?>/auth/login.php" class="text-danger">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại đăng nhập
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Password strength checker
document.getElementById('password')?.addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('passwordStrength');
    
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    // Update requirement indicators
    Object.keys(requirements).forEach(req => {
        const element = document.getElementById('req-' + req);
        if (requirements[req]) {
            element.classList.add('valid');
            element.classList.remove('invalid');
            element.querySelector('i').classList.remove('fa-circle');
            element.querySelector('i').classList.add('fa-check-circle');
        } else {
            element.classList.add('invalid');
            element.classList.remove('valid');
            element.querySelector('i').classList.remove('fa-check-circle');
            element.querySelector('i').classList.add('fa-circle');
        }
    });
    
    // Calculate strength
    const validCount = Object.values(requirements).filter(v => v).length;
    
    strengthBar.className = 'password-strength';
    if (validCount <= 2) {
        strengthBar.classList.add('strength-weak');
    } else if (validCount <= 4) {
        strengthBar.classList.add('strength-medium');
    } else {
        strengthBar.classList.add('strength-strong');
    }
});

// Password match validation
document.getElementById('passwordConfirm')?.addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    const error = document.getElementById('passwordMatchError');
    
    if (confirm && password !== confirm) {
        this.classList.add('is-invalid');
        error.style.display = 'block';
    } else {
        this.classList.remove('is-invalid');
        error.style.display = 'none';
    }
});

// Form validation
document.getElementById('resetForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('passwordConfirm').value;
    
    if (password !== confirm) {
        e.preventDefault();
        document.getElementById('passwordConfirm').classList.add('is-invalid');
        alert('Mật khẩu xác nhận không khớp!');
        return false;
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
