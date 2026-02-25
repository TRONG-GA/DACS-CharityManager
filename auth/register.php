<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
// Nếu đã đăng nhập, chuyển về trang chủ
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pageTitle = 'Đăng ký tài khoản - ' . SITE_NAME;
$pageDescription = 'Tạo tài khoản để tham gia các hoạt động từ thiện';

// Get flash message if any
$flash = getFlashMessage();

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

.auth-left .feature-list {
    list-style: none;
    padding: 0;
    margin-top: 30px;
}

.auth-left .feature-list li {
    padding: 10px 0;
    display: flex;
    align-items: center;
}

.auth-left .feature-list li i {
    margin-right: 15px;
    font-size: 1.3rem;
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
                        <h2>Tham gia cộng đồng từ thiện</h2>
                        <p>Hãy cùng chúng tôi lan tỏa yêu thương đến mọi miền đất nước</p>
                        
                        <ul class="feature-list">
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Quyên góp minh bạch, an toàn</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Tham gia tình nguyện ý nghĩa</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Cập nhật hoạt động từ thiện</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Kết nối cộng đồng hảo tâm</span>
                            </li>
                        </ul>
                        
                        <div class="mt-4">
                            <p class="mb-2">Đã có tài khoản?</p>
                            <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-light btn-auth">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập ngay
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Form -->
                <div class="col-lg-7 auth-right">
                    <div>
                        <h3>Tạo tài khoản mới</h3>
                        <p class="subtitle">Điền thông tin để bắt đầu hành trình từ thiện</p>
                        
                        <!-- Flash Message -->
                        <?php if ($flash): ?>
                        <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                            <i class="fas fa-<?= $flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                            <?= sanitize($flash['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <form action="<?= BASE_URL ?>/auth/process_register.php" method="POST" id="registerForm">
                            <?= csrfField() ?>
                            
                            <!-- Họ và tên -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user text-danger me-2"></i>Họ và tên
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="fullname" 
                                       class="form-control" 
                                       placeholder="Nguyễn Văn A"
                                       required
                                       minlength="3"
                                       maxlength="100"
                                       value="<?= isset($_POST['fullname']) ? sanitize($_POST['fullname']) : '' ?>">
                                <div class="invalid-feedback">
                                    Vui lòng nhập họ tên (ít nhất 3 ký tự)
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-envelope text-danger me-2"></i>Email
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control" 
                                       placeholder="email@example.com"
                                       required
                                       value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>">
                                <div class="invalid-feedback">
                                    Vui lòng nhập email hợp lệ
                                </div>
                            </div>
                            
                            <!-- Số điện thoại -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-phone text-danger me-2"></i>Số điện thoại
                                    <span class="text-muted">(Không bắt buộc)</span>
                                </label>
                                <input type="tel" 
                                       name="phone" 
                                       class="form-control" 
                                       placeholder="0987654321"
                                       pattern="[0-9]{10}"
                                       value="<?= isset($_POST['phone']) ? sanitize($_POST['phone']) : '' ?>">
                                <small class="text-muted">Định dạng: 10 chữ số</small>
                            </div>
                            
                            <!-- Mật khẩu -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock text-danger me-2"></i>Mật khẩu
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           name="password" 
                                           id="password"
                                           class="form-control" 
                                           placeholder="Nhập mật khẩu"
                                           required
                                           minlength="8">
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
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock text-danger me-2"></i>Xác nhận mật khẩu
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       name="password_confirm" 
                                       id="passwordConfirm"
                                       class="form-control" 
                                       placeholder="Nhập lại mật khẩu"
                                       required>
                                <div class="invalid-feedback" id="passwordMatchError">
                                    Mật khẩu xác nhận không khớp
                                </div>
                            </div>
                            
                            <!-- Điều khoản -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="agree_terms" 
                                           id="agreeTerms"
                                           required>
                                    <label class="form-check-label" for="agreeTerms">
                                        Tôi đồng ý với 
                                        <a href="<?= BASE_URL ?>/terms.php" target="_blank">Điều khoản sử dụng</a> 
                                        và 
                                        <a href="<?= BASE_URL ?>/privacy.php" target="_blank">Chính sách bảo mật</a>
                                        <span class="text-danger">*</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger btn-auth">
                                    <i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản
                                </button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Bằng việc đăng ký, bạn xác nhận đã đọc và đồng ý với các điều khoản của chúng tôi
                                </small>
                            </div>
                        </form>
                        
                        <!-- Social Login (Optional - commented out) -->
                        <!--
                        <div class="text-center my-4">
                            <span class="text-muted">Hoặc đăng ký bằng</span>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary">
                                <i class="fab fa-facebook me-2"></i>Facebook
                            </button>
                            <button class="btn btn-outline-danger">
                                <i class="fab fa-google me-2"></i>Google
                            </button>
                        </div>
                        -->
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

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('passwordStrength');
    
    // Requirements
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
document.getElementById('passwordConfirm').addEventListener('input', function() {
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
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('passwordConfirm').value;
    
    if (password !== confirm) {
        e.preventDefault();
        document.getElementById('passwordConfirm').classList.add('is-invalid');
        alert('Mật khẩu xác nhận không khớp!');
        return false;
    }
    
    // Check password strength
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    const allValid = Object.values(requirements).every(v => v);
    if (!allValid) {
        e.preventDefault();
        alert('Mật khẩu chưa đủ mạnh! Vui lòng đáp ứng tất cả các yêu cầu.');
        return false;
    }
});

// Phone validation
document.querySelector('input[name="phone"]').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
