<?php
/**
 * Admin - Edit User
 * Form chỉnh sửa thông tin user
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$userId = $_GET['id'] ?? 0;

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'Không tìm thấy người dùng');
    header('Location: users.php');
    exit;
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitize($_POST['fullname'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $district = sanitize($_POST['district'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($fullname)) {
        $errors[] = 'Họ tên không được để trống';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    // Check email unique
    if ($email !== $user['email']) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Email đã tồn tại';
        }
    }
    
    // Handle avatar upload
    $avatar = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadedAvatar = uploadImage($_FILES['avatar'], 'avatars');
        if ($uploadedAvatar) {
            // Delete old avatar
            if ($avatar !== 'default-avatar.png') {
                deleteFile('avatars/' . $avatar);
            }
            $avatar = basename($uploadedAvatar);
        }
    }
    
    // Handle password change
    $updatePassword = false;
    $newPassword = '';
    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < MIN_PASSWORD_LENGTH) {
            $errors[] = 'Mật khẩu phải có ít nhất ' . MIN_PASSWORD_LENGTH . ' ký tự';
        } else {
            $updatePassword = true;
            $newPassword = hashPassword($_POST['new_password']);
        }
    }
    
    if (empty($errors)) {
        try {
            // Update user
            $sql = "UPDATE users SET 
                    fullname = ?, email = ?, phone = ?, 
                    address = ?, city = ?, district = ?,
                    role = ?, status = ?, avatar = ?";
            $params = [$fullname, $email, $phone, $address, $city, $district, $role, $status, $avatar];
            
            if ($updatePassword) {
                $sql .= ", password = ?";
                $params[] = $newPassword;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $userId;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            setFlashMessage('success', 'Đã cập nhật thông tin người dùng');
            header('Location: user_detail.php?id=' . $userId);
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Lỗi cập nhật: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Chỉnh sửa người dùng';
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

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <!-- Avatar -->
                                        <div class="col-12 mb-4 text-center">
                                            <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= $user['avatar'] ?>" 
                                                 alt="Avatar" id="avatarPreview"
                                                 class="rounded-circle mb-3" 
                                                 style="width: 150px; height: 150px; object-fit: cover;"
                                                 onerror="this.src='<?= BASE_URL ?>/public/uploads/avatars/default-avatar.png'">
                                            <div>
                                                <label for="avatar" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-upload"></i> Đổi ảnh đại diện
                                                </label>
                                                <input type="file" class="d-none" id="avatar" name="avatar" 
                                                       accept="image/*" onchange="previewImage(this, 'avatarPreview')">
                                            </div>
                                        </div>

                                        <!-- Fullname -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="fullname" 
                                                   value="<?= htmlspecialchars($user['fullname']) ?>" required>
                                        </div>

                                        <!-- Email -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                                        </div>

                                        <!-- Phone -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Số điện thoại</label>
                                            <input type="tel" class="form-control" name="phone" 
                                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                        </div>

                                        <!-- Role -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Vai trò</label>
                                            <select class="form-select" name="role">
                                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                                <option value="benefactor" <?= $user['role'] === 'benefactor' ? 'selected' : '' ?>>Benefactor</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </div>

                                        <!-- Status -->
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Trạng thái</label>
                                            <select class="form-select" name="status">
                                                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                                                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
                                                <option value="banned" <?= $user['status'] === 'banned' ? 'selected' : '' ?>>Đã khóa</option>
                                            </select>
                                        </div>

                                        <!-- Address -->
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Địa chỉ</label>
                                            <input type="text" class="form-control" name="address" 
                                                   value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                                        </div>

                                        <!-- District -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Quận/Huyện</label>
                                            <input type="text" class="form-control" name="district" 
                                                   value="<?= htmlspecialchars($user['district'] ?? '') ?>">
                                        </div>

                                        <!-- City -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tỉnh/Thành phố</label>
                                            <input type="text" class="form-control" name="city" 
                                                   value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                                        </div>

                                        <!-- Password Section -->
                                        <div class="col-12 mb-3">
                                            <hr>
                                            <h5>Đổi mật khẩu (để trống nếu không đổi)</h5>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mật khẩu mới</label>
                                            <input type="password" class="form-control" name="new_password" 
                                                   placeholder="Tối thiểu <?= MIN_PASSWORD_LENGTH ?> ký tự">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Xác nhận mật khẩu</label>
                                            <input type="password" class="form-control" name="confirm_password">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="user_detail.php?id=<?= $userId ?>" class="btn btn-secondary">
                                            <i class="bi bi-x-lg"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg"></i> Lưu thay đổi
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
    <script src="<?= BASE_URL ?>/public/js/admin.js"></script>
</body>
</html>
