<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
// Nếu đã đăng nhập, chuyển về trang chủ
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pageTitle = 'Đăng nhập - ' . SITE_NAME;
$pageDescription = 'Đăng nhập để tham gia các hoạt động từ thiện';

// Get flash message if any
$flash = getFlashMessage();

// Check if timeout
$isTimeout = isset($_GET['timeout']) && $_GET['timeout'] == 1;

include __DIR__ . '/../includes/header.php';
?>

<style>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px 0;
}

.auth-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    overflow: hidden;
    max-width: 900px;
    margin: 0 auto;
}

.auth-left {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.auth-left h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.auth-left p {
    font-size: 1.1rem;
    opacity: 0.9;
    line-height: 1.8;
}

.auth-left .stats {
    margin-top: 40px;
}

.auth-left .stat-item {
    margin-bottom: 20px;
}

.auth-left .stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    display: block;
}

.auth-left .stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.auth-right {
    padding: 60px 40px;
}

.auth-right h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: #333;
}

.auth-right .subtitle {
    color: #666;
    margin-bottom: 30px;
}

.form-control:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
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

.divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 30px 0;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #ddd;
}

.divider span {
    padding: 0 15px;
    color: #999;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .auth-left {
        padding: 40px 30px;
    }
    
    .auth-right {
        padding: 40px 30px;
    }
}
</style>

<div class="auth-page">
    <div class="container">
        <div class="auth-card">
            <div class="row g-0">
                <!-- Left Side - Info -->
                <div class="col-lg-5 auth-left">
                    <div>
                        <div class="mb-4">
                            <i class="fas fa-heart fa-3x"></i>
                        </div>
                        <h2>Chào mừng trở lại!</h2>
                        <p>Tiếp tục hành trình lan tỏa yêu thương cùng chúng tôi</p>
                        
                        <?php
                        $stats = getStatistics();
                        if ($stats):
                        ?>
                        <div class="stats">
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['total_events'] ?>+</span>
                                <span class="stat-label">Chiến dịch từ thiện</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?= formatMoney($stats['total_donations']) ?></span>
                                <span class="stat-label">Đã quyên góp</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['total_donors'] ?>+</span>
                                <span class="stat-label">Nhà hảo tâm</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <p class="mb-2">Chưa có tài khoản?</p>
                            <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-light btn-auth">
                                <i class="fas fa-user-plus me-2"></i>Đăng ký ngay
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Form -->
                <div class="col-lg-7 auth-right">
                    <div>
                        <h3>Đăng nhập</h3>
                        <p class="subtitle">Nhập thông tin để tiếp tục</p>
                        
                        <!-- Flash Message -->
                        <?php if ($flash): ?>
                        <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                            <i class="fas fa-<?= $flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                            <?= sanitize($flash['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Timeout Warning -->
                        <?php if ($isTimeout): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fas fa-clock me-2"></i>
                            Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <form action="<?= BASE_URL ?>/auth/process_login.php" method="POST" id="loginForm">
                            <?= csrfField() ?>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-envelope text-danger me-2"></i>Email
                                </label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control form-control-lg" 
                                       placeholder="email@example.com"
                                       required
                                       autofocus>
                            </div>
                            
                            <!-- Password -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock text-danger me-2"></i>Mật khẩu
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           name="password" 
                                           id="password"
                                           class="form-control form-control-lg" 
                                           placeholder="Nhập mật khẩu"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="remember" 
                                           id="remember"
                                           value="1">
                                    <label class="form-check-label" for="remember">
                                        Ghi nhớ đăng nhập
                                    </label>
                                </div>
                                <a href="<?= BASE_URL ?>/auth/forgot_password.php" class="text-danger">
                                    Quên mật khẩu?
                                </a>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger btn-lg btn-auth">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                                </button>
                            </div>
                        </form>
                        
                        <!-- Social Login (Optional - commented out) -->
                        <!--
                        <div class="divider">
                            <span>Hoặc đăng nhập bằng</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <button class="btn btn-outline-primary w-100">
                                    <i class="fab fa-facebook me-2"></i>Facebook
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-outline-danger w-100">
                                    <i class="fab fa-google me-2"></i>Google
                                </button>
                            </div>
                        </div>
                        -->
                        
                        <div class="text-center mt-4">
                            <a href="<?= BASE_URL ?>/index.php" class="text-muted">
                                <i class="fas fa-arrow-left me-2"></i>Về trang chủ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
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

// Auto-dismiss alerts after 5 seconds
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getInstance(alert) || new bootstrap.Alert(alert);
        bsAlert.close();
    }, 5000);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
