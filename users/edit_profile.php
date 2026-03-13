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

if (!$user) {
    session_destroy();
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validation
    if (empty($fullname)) {
        $errors[] = 'Vui lòng nhập họ tên';
    }
    
    if (empty($email)) {
        $errors[] = 'Vui lòng nhập email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    // Check email duplicate (except current user)
    if (!empty($email)) {
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->execute([$email, $userId]);
        if ($checkEmail->fetch()) {
            $errors[] = 'Email đã được sử dụng';
        }
    }
    
    if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = 'Số điện thoại không hợp lệ (10 chữ số)';
    }
    
    // Handle avatar upload
    $avatarPath = $user['avatar'] ?? null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['avatar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Chỉ chấp nhận file ảnh: jpg, jpeg, png, gif';
        } elseif ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Kích thước ảnh tối đa 5MB';
        } else {
            $uploadDir = '../public/uploads/avatars/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $newFilename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            $uploadPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                // Delete old avatar
                if (!empty($user['avatar']) && file_exists($uploadDir . $user['avatar'])) {
                    unlink($uploadDir . $user['avatar']);
                }
                $avatarPath = $newFilename;
            } else {
                $errors[] = 'Lỗi upload ảnh';
            }
        }
    }
    
    // Update if no errors
    if (empty($errors)) {
        try {
            $updateQuery = $pdo->prepare("
                UPDATE users 
                SET fullname = ?, email = ?, phone = ?, address = ?, avatar = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $updateQuery->execute([$fullname, $email, $phone, $address, $avatarPath, $userId]);
            
            $_SESSION['user_fullname'] = $fullname;
            $success = 'Cập nhật thông tin thành công!';
            
            // Refresh user data
            $userQuery->execute([$userId]);
            $user = $userQuery->fetch();
            
        } catch (PDOException $e) {
            $errors[] = 'Lỗi cập nhật: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Chỉnh sửa thông tin - ' . $user['fullname'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="profile.php">Trang cá nhân</a></li>
                    <li class="breadcrumb-item active">Chỉnh sửa thông tin</li>
                </ol>
            </nav>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Chỉnh sửa thông tin cá nhân
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
                    
                    <form method="POST" enctype="multipart/form-data">
                        <?= csrfField() ?>
                        
                        <!-- Avatar -->
                        <div class="mb-4 text-center">
                            <div class="mb-3">
                                <?php if (!empty($user['avatar'])): ?>
                                <?php
$thumb = $user['avatar'];
if (strpos($thumb, 'avatars/') !== 0) {
    $thumb = 'avatars/' . $thumb;
}
?>
<img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($thumb) ?>"
                                     class="rounded-circle" 
                                     id="avatar-preview"
                                     width="150" height="150"
                                     style="object-fit: cover;">
                                <?php else: ?>
                                <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center" 
                                     id="avatar-preview"
                                     style="width: 150px; height: 150px; font-size: 4rem;">
                                    <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <label class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-camera me-2"></i>Chọn ảnh đại diện
                                <input type="file" name="avatar" id="avatar-input" accept="image/*" class="d-none">
                            </label>
                            <small class="d-block text-muted mt-2">JPG, PNG, GIF. Tối đa 5MB</small>
                        </div>
                        
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" 
                                   value="<?= sanitize($user['fullname']) ?>" required>
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= sanitize($user['email']) ?>" required>
                        </div>
                        
                        <!-- Phone -->
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?= sanitize($user['phone'] ?? '') ?>" 
                                   placeholder="0123456789">
                        </div>
                        
                        <!-- Address -->
                        <div class="mb-4">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="address" class="form-control" 
                                   value="<?= sanitize($user['address'] ?? '') ?>" 
                                   placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành">
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-save me-2"></i>Lưu thay đổi
                            </button>
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview avatar
document.getElementById('avatar-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                // Replace div with img
                const img = document.createElement('img');
                img.id = 'avatar-preview';
                img.className = 'rounded-circle';
                img.width = 150;
                img.height = 150;
                img.style.objectFit = 'cover';
                img.src = e.target.result;
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include '../includes/footer.php'; ?>