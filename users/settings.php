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

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'privacy') {
        // Update privacy settings
        $publicProfile = isset($_POST['public_profile']) ? 1 : 0;
        $showDonations = isset($_POST['show_donations']) ? 1 : 0;
        $showVolunteers = isset($_POST['show_volunteers']) ? 1 : 0;
        
        try {
            $updateQuery = $pdo->prepare("
                UPDATE users 
                SET public_profile = ?, show_donations = ?, show_volunteers = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $updateQuery->execute([$publicProfile, $showDonations, $showVolunteers, $userId]);
            $success = 'Cập nhật cài đặt quyền riêng tư thành công!';
            
            // Refresh user data
            $userQuery->execute([$userId]);
            $user = $userQuery->fetch();
            
        } catch (PDOException $e) {
            $errors[] = 'Lỗi cập nhật: ' . $e->getMessage();
        }
    }
    
    elseif ($action === 'notifications') {
        // Update notification settings
        $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
        $donationNotifications = isset($_POST['donation_notifications']) ? 1 : 0;
        $volunteerNotifications = isset($_POST['volunteer_notifications']) ? 1 : 0;
        $eventNotifications = isset($_POST['event_notifications']) ? 1 : 0;
        
        try {
            $updateQuery = $pdo->prepare("
                UPDATE users 
                SET email_notifications = ?, donation_notifications = ?, 
                    volunteer_notifications = ?, event_notifications = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $updateQuery->execute([
                $emailNotifications, $donationNotifications, 
                $volunteerNotifications, $eventNotifications, $userId
            ]);
            $success = 'Cập nhật cài đặt thông báo thành công!';
            
            // Refresh user data
            $userQuery->execute([$userId]);
            $user = $userQuery->fetch();
            
        } catch (PDOException $e) {
            $errors[] = 'Lỗi cập nhật: ' . $e->getMessage();
        }
    }
    
    elseif ($action === 'delete_account') {
        // Xác nhận mật khẩu
        $password = $_POST['password'] ?? '';
        
        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu để xác nhận';
        } elseif (!password_verify($password, $user['password'])) {
            $errors[] = 'Mật khẩu không đúng';
        } else {
            // Soft delete - chỉ đánh dấu là deleted
            try {
                $deleteQuery = $pdo->prepare("
                    UPDATE users 
                    SET status = 'deleted', updated_at = NOW()
                    WHERE id = ?
                ");
                $deleteQuery->execute([$userId]);
                
                // Logout
                session_destroy();
                header('Location: ' . BASE_URL . '?account_deleted=1');
                exit;
                
            } catch (PDOException $e) {
                $errors[] = 'Lỗi xóa tài khoản: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Cài đặt - ' . $user['fullname'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if ($user['avatar']): ?>
                        <?php
$thumb = $user['avatar'];
if (strpos($thumb, 'avatars/') !== 0) {
    $thumb = 'avatars/' . $thumb;
}
?>
<img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($thumb) ?>"
                             class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                        <?php else: ?>
                        <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <h6 class="mb-0"><?= sanitize($user['fullname']) ?></h6>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-3">
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i>Tổng quan
                    </a>
                    <a href="my_donations.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-hand-holding-heart me-2"></i>Quyên góp
                    </a>
                    <a href="my_volunteers.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-hands-helping me-2"></i>Tình nguyện
                    </a>
                    <a href="my_events.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt me-2"></i>Sự kiện của tôi
                    </a>
                    <a href="settings.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-cog me-2"></i>Cài đặt
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <h4 class="mb-4">
                <i class="fas fa-cog text-danger me-2"></i>
                Cài đặt tài khoản
            </h4>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
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
            
            <!-- Privacy Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt text-primary me-2"></i>
                        Quyền riêng tư
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="privacy">
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="public_profile" 
                                   id="public-profile" <?= $user['public_profile'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="public-profile">
                                <strong>Hồ sơ công khai</strong>
                                <p class="text-muted small mb-0">Cho phép mọi người xem hồ sơ của bạn</p>
                            </label>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="show_donations" 
                                   id="show-donations" <?= $user['show_donations'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show-donations">
                                <strong>Hiển thị lịch sử quyên góp</strong>
                                <p class="text-muted small mb-0">Cho phép người khác xem các khoản quyên góp của bạn</p>
                            </label>
                        </div>
                        
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="show_volunteers" 
                                   id="show-volunteers" <?= $user['show_volunteers'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show-volunteers">
                                <strong>Hiển thị hoạt động tình nguyện</strong>
                                <p class="text-muted small mb-0">Cho phép người khác xem các hoạt động tình nguyện của bạn</p>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Lưu cài đặt
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Notification Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bell text-warning me-2"></i>
                        Thông báo
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="notifications">
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="email_notifications" 
                                   id="email-notifications" <?= $user['email_notifications'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="email-notifications">
                                <strong>Nhận thông báo qua email</strong>
                                <p class="text-muted small mb-0">Gửi email thông báo về các hoạt động quan trọng</p>
                            </label>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="donation_notifications" 
                                   id="donation-notifications" <?= $user['donation_notifications'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="donation-notifications">
                                <strong>Thông báo quyên góp</strong>
                                <p class="text-muted small mb-0">Nhận thông báo khi có người quyên góp vào sự kiện của bạn</p>
                            </label>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="volunteer_notifications" 
                                   id="volunteer-notifications" <?= $user['volunteer_notifications'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="volunteer-notifications">
                                <strong>Thông báo tình nguyện</strong>
                                <p class="text-muted small mb-0">Nhận thông báo về đơn đăng ký tình nguyện</p>
                            </label>
                        </div>
                        
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="event_notifications" 
                                   id="event-notifications" <?= $user['event_notifications'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="event-notifications">
                                <strong>Thông báo sự kiện</strong>
                                <p class="text-muted small mb-0">Nhận thông báo về các sự kiện mới và cập nhật</p>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Lưu cài đặt
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Danger Zone -->
            <div class="card border-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Vùng nguy hiểm
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="text-danger mb-3">Xóa tài khoản</h6>
                    <p class="text-muted mb-3">
                        Sau khi xóa tài khoản, tất cả dữ liệu của bạn sẽ bị xóa vĩnh viễn. 
                        Hành động này không thể hoàn tác.
                    </p>
                    
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="fas fa-trash me-2"></i>Xóa tài khoản
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Xác nhận xóa tài khoản
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="delete_account">
                
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Cảnh báo!</strong> Hành động này không thể hoàn tác.
                    </div>
                    
                    <p>Nhập mật khẩu của bạn để xác nhận xóa tài khoản:</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirm-delete" required>
                        <label class="form-check-label" for="confirm-delete">
                            Tôi hiểu rằng tất cả dữ liệu sẽ bị xóa vĩnh viễn
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Xóa tài khoản
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
