<?php
// Bật lỗi để dễ debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';

// PHÂN QUYỀN
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'benefactor') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// XỬ LÝ LƯU THAY ĐỔI VÀO CSDL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $display_name = trim($_POST['display_name'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $social_link  = trim($_POST['social_link'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    
    // XỬ LÝ DỮ LIỆU NGÂN HÀNG
    $bank_name    = $_POST['bank_name'] ?? '';
    $bank_account = trim($_POST['bank_account'] ?? '');
    $bank_owner   = strtoupper(trim($_POST['bank_owner'] ?? ''));
    
    // Xử lý upload ảnh Logo/Avatar
    $logoPathUpdate = "";
    $params = [
        $display_name, $display_name, // Cho fullname & org_name
        $phone, $phone, // Cho phone & rep_phone
        $address, 
        $social_link, $social_link, // Cho social_link & website
        $description,
        $bank_name, $bank_account, $bank_owner // 3 Cột Ngân hàng
    ];
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $logoName = time() . '_logo_' . basename($_FILES['logo']['name']);
        move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName);
        $logoPath = '/DACS-CharityManager-main/public/uploads/' . $logoName;
        
        $logoPathUpdate = ", logo_path = ?";
        $params[] = $logoPath;
    }
    
    $params[] = $user_id; // Điều kiện WHERE

    try {
        $sql = "UPDATE charity_registrations 
                SET fullname = ?, org_name = ?, 
                    phone = ?, rep_phone = ?, 
                    address = ?, 
                    social_link = ?, website = ?, 
                    description = ?,
                    bank_name = ?, bank_account = ?, bank_owner = ?
                    $logoPathUpdate 
                WHERE user_id = ?";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $success_msg = "Cập nhật hồ sơ thành công! Mã QR của bạn sẽ được tự động cập nhật.";
    } catch (Exception $e) {
        $error_msg = "Lỗi cập nhật: " . $e->getMessage();
    }
}

// LẤY DỮ LIỆU HIỆN TẠI ĐỂ ĐỔ VÀO FORM
try {
    $stmt = $pdo->prepare("SELECT * FROM charity_registrations WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profile) {
        die("<div style='text-align:center; padding: 50px;'>Không tìm thấy thông tin hồ sơ của bạn. Vui lòng tạo ít nhất 1 chiến dịch trước!</div>");
    }
    
    $current_name  = $profile['account_type'] === 'organization' ? $profile['org_name'] : $profile['fullname'];
    $current_phone = $profile['account_type'] === 'organization' ? $profile['rep_phone'] : $profile['phone'];
    $current_link  = $profile['account_type'] === 'organization' ? $profile['website'] : $profile['social_link'];
    $current_logo  = $profile['logo_path'] ? $profile['logo_path'] : '../../public/images/default-avatar.png'; 
    
} catch (Exception $e) {
    die("Lỗi Database: " . $e->getMessage());
}
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
        .alert-success { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <aside class="sidebar">
        <a href="../index.php" style="text-decoration: none;"><div class="sidebar-logo">Charity Events</div></a>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Tổng quan</a></li>
            <li><a href="#">💸 Báo cáo minh bạch (Thu/Chi)</a></li>
            <li><a href="manage_news.php">📝 Đăng tin tức</a></li>
            <li><a href="tinhnguyen.php">👥 Quản lý tình nguyện viên</a></li>
            <li class="active"><a href="settings.php">⚙️ Cài đặt tài khoản</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="dash-header">
            <h1 class="dash-title">Cài đặt hồ sơ</h1>
        </div>

        <div class="settings-card">
            <?php if ($success_msg): ?>
                <div class="alert-success">✅ <?= $success_msg ?></div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="alert-error">❌ <?= $error_msg ?></div>
            <?php endif; ?>

            <form method="POST" action="settings.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">

                <div class="avatar-preview-container">
                    <img src="<?= htmlspecialchars($current_logo) ?>" alt="Avatar" class="avatar-preview" id="previewImg">
                    <div>
                        <h3 style="margin: 0 0 10px 0; color: #333;">Ảnh đại diện / Logo</h3>
                        <input type="file" name="logo" accept="image/*" id="logoInput" style="font-size: 14px;">
                        <p style="margin: 5px 0 0 0; font-size: 13px; color: #888;">Nên dùng ảnh vuông tỉ lệ 1:1, dung lượng < 2MB</p>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tên hiển thị (Tên tổ chức / Họ và tên)</label>
                    <input type="text" name="display_name" class="form-control" value="<?= htmlspecialchars($current_name) ?>" required>
                </div>

                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Số điện thoại liên hệ</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($current_phone) ?>" required>
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label>Đường dẫn website / Facebook</label>
                        <input type="text" name="social_link" class="form-control" value="<?= htmlspecialchars($current_link) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($profile['address'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Mô tả ngắn giới thiệu về bạn / Tổ chức</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Viết vài dòng giới thiệu về các hoạt động của bạn..."><?= htmlspecialchars($profile['description'] ?? '') ?></textarea>
                </div>

                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6; margin-bottom: 20px;">
                    <h4 style="color: #007bff; margin-top: 0; margin-bottom: 15px; font-size: 16px;">💳 Thông tin nhận tiền quyên góp (Tự động tạo VietQR)</h4>
                    
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label>Ngân hàng <span style="color:red">*</span></label>
                            <select name="bank_name" class="form-control" required>
                                <option value="">-- Chọn ngân hàng --</option>
                                <option value="MB" <?= (isset($profile['bank_name']) && $profile['bank_name'] == 'MB') ? 'selected' : '' ?>>MB Bank</option>
                                <option value="VCB" <?= (isset($profile['bank_name']) && $profile['bank_name'] == 'VCB') ? 'selected' : '' ?>>Vietcombank (VCB)</option>
                                <option value="TCB" <?= (isset($profile['bank_name']) && $profile['bank_name'] == 'TCB') ? 'selected' : '' ?>>Techcombank (TCB)</option>
                                <option value="ACB" <?= (isset($profile['bank_name']) && $profile['bank_name'] == 'ACB') ? 'selected' : '' ?>>ACB</option>
                                <option value="BIDV" <?= (isset($profile['bank_name']) && $profile['bank_name'] == 'BIDV') ? 'selected' : '' ?>>BIDV</option>
                                <option value="VIETINBANK" <?= (isset($profile['bank_name']) && $profile['bank_name'] == 'VIETINBANK') ? 'selected' : '' ?>>VietinBank</option>
                                <option value="VPB" <?= (isset($profile['bank_name']) && $profile['bank_name'] == 'VPB') ? 'selected' : '' ?>>VPBank</option>
                                <option value="TPB" <?= (isset($profile['bank_name']) && $profile['bank_name'] == 'TPB') ? 'selected' : '' ?>>TPBank</option>
                                <option value="MOMO" <?= (isset($profile['bank_name']) && $profile['bank_name'] == 'MOMO') ? 'selected' : '' ?>>Ví MoMo</option>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label>Số tài khoản <span style="color:red">*</span></label>
                            <input type="text" name="bank_account" class="form-control" placeholder="VD: 0987654321" value="<?= htmlspecialchars($profile['bank_account'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Tên chủ tài khoản <span style="color:red">*</span></label>
                        <input type="text" name="bank_owner" class="form-control" placeholder="VIẾT HOA KHÔNG DẤU (VD: NGUYEN VAN A)" value="<?= htmlspecialchars($profile['bank_owner'] ?? '') ?>" required>
                        <small style="color: #666; display: block; margin-top: 5px;">* Thông tin này sẽ được dùng để tự động tạo mã QR đính kèm vào cuối các bài đăng tin tức của bạn.</small>
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