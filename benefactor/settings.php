<?php
// Bật lỗi để dễ debug
ini_set('display_errors', 1);
error_reporting(E_ALL);
 
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
 
// PHÂN QUYỀN
requireBenefactorVerified();
 
$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';
 
// XỬ LÝ LƯU THAY ĐỔI VÀO CSDL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    requireCSRF();
    
    $fullname     = sanitize($_POST['display_name'] ?? '');
    $phone        = sanitize($_POST['phone'] ?? '');
    $address      = sanitize($_POST['address'] ?? '');
    $description  = sanitize($_POST['description'] ?? '');
    
    // XỬ LÝ DỮ LIỆU NGÂN HÀNG - Lưu vào benefactor_applications
    $bank_name    = $_POST['bank_name'] ?? '';
    $bank_account = sanitize($_POST['bank_account'] ?? '');
    $bank_owner   = strtoupper(sanitize($_POST['bank_owner'] ?? ''));
    
    // Xử lý upload ảnh Avatar
    $avatarPath = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $avatarPath = uploadImage($_FILES['logo'], 'avatars');
    }
    
    try {
        // 1. CẬP NHẬT THÔNG TIN CƠ BẢN VÀO USERS TABLE
        $sqlUser = "UPDATE users SET fullname = ?, phone = ?";
        $paramsUser = [$fullname, $phone];
        
        if (!empty($avatarPath)) {
            $sqlUser .= ", avatar = ?";
            $paramsUser[] = $avatarPath;
        }
        
        $sqlUser .= " WHERE id = ?";
        $paramsUser[] = $user_id;
        
        $stmtUser = $pdo->prepare($sqlUser);
        $stmtUser->execute($paramsUser);
        
        // 2. CẬP NHẬT THÔNG TIN NGÂN HÀNG VÀO BENEFACTOR_APPLICATIONS
        $sqlBenef = "UPDATE benefactor_applications 
                     SET fullname = ?, phone = ?, address = ?, 
                         bank_name = ?, bank_account = ?, bank_owner = ?,
                         additional_info = ?
                     WHERE user_id = ?";
        
        // Lưu description vào JSON additional_info
        $additionalInfo = json_encode(['description' => $description]);
        
        $stmtBenef = $pdo->prepare($sqlBenef);
        $stmtBenef->execute([
            $fullname, $phone, $address,
            $bank_name, $bank_account, $bank_owner,
            $additionalInfo,
            $user_id
        ]);
        
        // Cập nhật session
        $_SESSION['fullname'] = $fullname;
        if (!empty($avatarPath)) {
            $_SESSION['avatar'] = $avatarPath;
        }
        
        setFlashMessage('success', 'Cập nhật hồ sơ thành công!');
        header("Location: settings.php");
        exit;
        
    } catch (Exception $e) {
        $error_msg = "Lỗi cập nhật: " . $e->getMessage();
    }
}
 
// LẤY DỮ LIỆU HIỆN TẠI
try {
    // Lấy từ users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("<div style='text-align:center; padding: 50px;'>Không tìm thấy thông tin người dùng!</div>");
    }
    
    // Lấy thông tin benefactor (ngân hàng, địa chỉ...)
    $stmtBenef = $pdo->prepare("SELECT * FROM benefactor_applications WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmtBenef->execute([$user_id]);
    $benefactor = $stmtBenef->fetch(PDO::FETCH_ASSOC);
    
    // Merge data
    $profile = array_merge($user, $benefactor ?: []);
    
    // Parse additional_info JSON
    $description = '';
    if (!empty($profile['additional_info'])) {
        $additionalData = json_decode($profile['additional_info'], true);
        $description = $additionalData['description'] ?? '';
    }
    
    $current_avatar = !empty($user['avatar']) 
        ? BASE_URL . '/public/uploads/' . $user['avatar']
        : BASE_URL . '/public/images/default-avatar.png';
    
} catch (Exception $e) {
    die("Lỗi Database: " . $e->getMessage());
}
 
$message = getFlashMessage();
?>
 
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cài đặt tài khoản</title>
    <link rel="stylesheet" href="../public/css/benefactor/dashboard.css?v=<?= time() ?>">
    <style>
        .settings-card { background: #fff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; color: #444; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 5px; font-size: 15px; box-sizing: border-box; font-family: inherit; }
        .form-control:focus { border-color: #007bff; outline: none; }
        .btn-save { background-color: #d32f2f; color: white; padding: 12px 25px; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; width: 100%; transition: 0.2s; }
        .btn-save:hover { background-color: #b71c1c; }
        .avatar-preview-container { display: flex; align-items: center; gap: 20px; margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px solid #eee; }
        .avatar-preview { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #eee; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
 
<div class="dashboard-wrapper">
    <aside class="sidebar">
        <a href="../index.php" style="text-decoration: none;">
            <div class="sidebar-logo">Charity Events</div>
        </a>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Tổng quan</a></li>
            <li><a href="create_campaign.php">+ Tạo sự kiện mới</a></li>
            <li><a href="financial_report.php">💰 Báo cáo thu chi</a></li>
            <li><a href="tinhnguyen.php">👥 Quản lý tình nguyện viên</a></li>
            <li  class="active"><a href="settings.php">⚙️ Cài đặt tài khoản</a></li>
            <li><a href="../auth/logout.php">🚪 Đăng xuất</a></li>
            <li style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
                <a href="../index.php" style="background: rgba(255,255,255,0.1); border-radius: 6px;">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>
            </li>
        </ul>
    </aside>
 
    <main class="main-content">
        <div class="dash-header">
            <h1 class="dash-title">Cài đặt hồ sơ</h1>
        </div>
 
        <div class="settings-card">
            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show">
                    <?= $message['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success_msg): ?>
                <div class="alert alert-success">✅ <?= $success_msg ?></div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="alert alert-danger">❌ <?= $error_msg ?></div>
            <?php endif; ?>
 
            <form method="POST" action="settings.php" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update_profile">
 
                <div class="avatar-preview-container">
                    <img src="<?= $current_avatar ?>" alt="Avatar" class="avatar-preview" id="previewImg">
                    <div>
                        <h3 style="margin: 0 0 10px 0; color: #333;">Ảnh đại diện</h3>
                        <input type="file" name="logo" accept="image/*" id="logoInput" style="font-size: 14px;">
                        <p style="margin: 5px 0 0 0; font-size: 13px; color: #888;">Nên dùng ảnh vuông tỉ lệ 1:1, dung lượng < 2MB</p>
                    </div>
                </div>
 
                <div class="form-group">
                    <label>Tên hiển thị (Họ và tên)</label>
                    <input type="text" name="display_name" class="form-control" value="<?= sanitize($user['fullname']) ?>" required>
                </div>
 
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Số điện thoại liên hệ</label>
                        <input type="text" name="phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label>Email</label>
                        <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled>
                        <small style="color: #666;">Email không thể thay đổi</small>
                    </div>
                </div>
 
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="address" class="form-control" value="<?= sanitize($profile['address'] ?? '') ?>">
                </div>
 
                <div class="form-group">
                    <label>Mô tả ngắn giới thiệu về bạn</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Viết vài dòng giới thiệu về các hoạt động của bạn..."><?= sanitize($description) ?></textarea>
                </div>
 
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6; margin-bottom: 20px;">
                    <h4 style="color: #007bff; margin-top: 0; margin-bottom: 15px; font-size: 16px;">💳 Thông tin nhận tiền quyên góp</h4>
                    
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label>Ngân hàng <span style="color:red">*</span></label>
                            <select name="bank_name" class="form-control" required>
                                <option value="">-- Chọn ngân hàng --</option>
                                <?php
                                $banks = ['MB', 'VCB', 'TCB', 'ACB', 'BIDV', 'VIETINBANK', 'VPB', 'TPB', 'MOMO'];
                                foreach ($banks as $bank) {
                                    $selected = ($profile['bank_name'] ?? '') == $bank ? 'selected' : '';
                                    echo "<option value='$bank' $selected>$bank</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label>Số tài khoản <span style="color:red">*</span></label>
                            <input type="text" name="bank_account" class="form-control" placeholder="VD: 0987654321" value="<?= sanitize($profile['bank_account'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Tên chủ tài khoản <span style="color:red">*</span></label>
                        <input type="text" name="bank_owner" class="form-control" placeholder="VIẾT HOA KHÔNG DẤU (VD: NGUYEN VAN A)" value="<?= sanitize($profile['bank_owner'] ?? '') ?>" required>
                        <small style="color: #666; display: block; margin-top: 5px;">* Thông tin này sẽ được dùng để tạo mã QR thanh toán.</small>
                    </div>
                </div>
                
                <button type="submit" class="btn-save">💾 Lưu thay đổi hồ sơ</button>
            </form>
        </div>
 
    </main>
</div>
 
<script>
    const logoInput = document.getElementById('logoInput');
    const previewImg = document.getElementById('previewImg');
    logoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) { previewImg.src = e.target.result; }
            reader.readAsDataURL(file);
        }
    });
</script>
 
</body>
</html>
 