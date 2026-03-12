<?php
require_once '../config/db.php';

// Check login
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get user info
$userQuery = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userQuery->execute([$userId]);
$user = $userQuery->fetch();

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($currentPassword)) {
        $errors[] = 'Vui lòng nhập mật khẩu hiện tại';
    } elseif (!password_verify($currentPassword, $user['password'])) {
        $errors[] = 'Mật khẩu hiện tại không đúng';
    }
    
    if (empty($newPassword)) {
        $errors[] = 'Vui lòng nhập mật khẩu mới';
    } elseif (strlen($newPassword) < 6) {
        $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }
    
    if ($currentPassword === $newPassword) {
        $errors[] = 'Mật khẩu mới phải khác mật khẩu hiện tại';
    }
    
    // Update if no errors
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $updateQuery->execute([$hashedPassword, $userId]);
            
            $success = 'Đổi mật khẩu thành công!';
            
            // Clear form
            $_POST = [];
            
        } catch (PDOException $e) {
            $errors[] = 'Lỗi cập nhật: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Đổi mật khẩu - ' . $user['fullname'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="profile.php">Trang cá nhân</a></li>
                    <li class="breadcrumb-item active">Đổi mật khẩu</li>
                </ol>
            </nav>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Đổi mật khẩu
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Lỗi:</strong>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="change-password-form">
                        <?= csrfField() ?>
                        
                        <!-- Current Password -->
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="current_password" id="current-password" 
                                       class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="togglePassword('current-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- New Password -->
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="new_password" id="new-password" 
                                       class="form-control" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="togglePassword('new-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Tối thiểu 6 ký tự</small>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confirm-password" 
                                       class="form-control" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="togglePassword('confirm-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text text-danger" id="password-match-error" style="display: none;">
                                Mật khẩu xác nhận không khớp
                            </small>
                        </div>
                        
                        <!-- Password Strength Indicator -->
                        <div class="mb-4">
                            <div class="progress" style="height: 5px;">
                                <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="password-strength-text" class="text-muted"></small>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-save me-2"></i>Đổi mật khẩu
                            </button>
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Security Tips -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-shield-alt text-primary me-2"></i>
                        Mẹo bảo mật
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Sử dụng mật khẩu dài ít nhất 8-12 ký tự</li>
                        <li>Kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt</li>
                        <li>Không sử dụng thông tin cá nhân dễ đoán</li>
                        <li>Không chia sẻ mật khẩu với bất kỳ ai</li>
                        <li>Thay đổi mật khẩu định kỳ (3-6 tháng)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = event.currentTarget.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('new-password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    
    let strength = 0;
    let text = '';
    let color = '';
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    switch(strength) {
        case 0:
        case 1:
            text = 'Rất yếu';
            color = 'bg-danger';
            break;
        case 2:
            text = 'Yếu';
            color = 'bg-warning';
            break;
        case 3:
            text = 'Trung bình';
            color = 'bg-info';
            break;
        case 4:
            text = 'Mạnh';
            color = 'bg-primary';
            break;
        case 5:
            text = 'Rất mạnh';
            color = 'bg-success';
            break;
    }
    
    strengthBar.style.width = (strength * 20) + '%';
    strengthBar.className = 'progress-bar ' + color;
    strengthText.textContent = 'Độ mạnh: ' + text;
});

// Check password match
document.getElementById('confirm-password').addEventListener('input', function() {
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = this.value;
    const error = document.getElementById('password-match-error');
    
    if (confirmPassword && newPassword !== confirmPassword) {
        error.style.display = 'block';
        this.classList.add('is-invalid');
    } else {
        error.style.display = 'none';
        this.classList.remove('is-invalid');
    }
});

// Form validation
document.getElementById('change-password-form').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Mật khẩu xác nhận không khớp!');
    }
});
</script>

<?php include '../includes/footer.php'; ?>
