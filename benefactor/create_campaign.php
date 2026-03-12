<?php
/**
 * BENEFACTOR CREATE CAMPAIGN - MERGED VERSION
 * Giữ UI đẹp từ file cũ + Logic mới INSERT vào events table
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
 
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
 
// Yêu cầu benefactor đã verified
requireBenefactorVerified();
 
$user_id = $_SESSION['user_id'];
$error = '';
 
// XỬ LÝ TẠO SỰ KIỆN MỚI - LOGIC MỚI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_campaign') {
    requireCSRF();
    
    $target_name = sanitize($_POST['target_name'] ?? '');
    $target_amount = str_replace(['.', ','], '', $_POST['target_amount']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $story = $_POST['story'] ?? '';
    $campaign_location = sanitize($_POST['campaign_location'] ?? '');
    $campaign_category = $_POST['campaign_category'] ?? '';
 
    // Map campaign_category → events.category
    $categoryMap = [
        'Giáo dục & Trường học' => 'education',
        'Y tế & Sức khỏe' => 'medical',
        'Cứu trợ khẩn cấp (Lũ lụt, thiên tai)' => 'disaster',
        'Xây dựng cơ sở hạ tầng' => 'community',
        'Bảo vệ môi trường' => 'environment',
        'Trẻ em mồ côi & Người già neo đơn' => 'children',
        'Khác' => 'other'
    ];
    $category = $categoryMap[$campaign_category] ?? 'other';
 
    // Upload ảnh
    $campaign_image = '';
    if (isset($_FILES['campaign_image']) && $_FILES['campaign_image']['error'] === UPLOAD_ERR_OK) {
        $campaign_image = uploadImage($_FILES['campaign_image'], 'events');
    }
 
    // Validation
    $today = date('Y-m-d');
    if (empty($target_name)) {
        $error = "Vui lòng nhập tên chiến dịch.";
    } elseif ($start_date < $today) {
        $error = "Ngày bắt đầu không được ở trong quá khứ.";
    } elseif ($end_date <= $start_date) {
        $error = "Ngày kết thúc phải lớn hơn ngày bắt đầu.";
    } elseif (empty($campaign_image)) {
        $error = "Vui lòng tải lên ảnh bìa cho chiến dịch.";
    } else {
        try {
            // INSERT VÀO EVENTS TABLE (LOGIC MỚI!)
            $sql = "INSERT INTO events 
                    (user_id, title, description, category, target_amount, 
                     start_date, end_date, location, thumbnail, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $user_id,
                $target_name,
                $story,
                $category,
                $target_amount,
                $start_date,
                $end_date,
                $campaign_location,
                $campaign_image
            ]);
 
            $eventId = $pdo->lastInsertId();
 
            // Notify admins
            notifyAdmins(
                'Sự kiện mới chờ duyệt',
                $_SESSION['fullname'] . ' đã tạo sự kiện "' . $target_name . '" chờ phê duyệt',
                BASE_URL . '/admin/events/event_detail.php?id=' . $eventId
            );
 
            setFlashMessage('success', 'Tạo chiến dịch thành công! Chờ admin phê duyệt.');
            header("Location: dashboard.php");
            exit;
 
        } catch (Exception $e) {
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo chiến dịch mới</title>
    <link rel="stylesheet" href="../public/css/benefactor/dashboard.css?v=<?= time() ?>">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
        .form-card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 900px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 15px; font-family: inherit; }
        .form-control:focus { outline: none; border-color: #007bff; }
        .ck-editor__editable_inline { min-height: 300px; font-size: 15px; }
        .btn-submit { background: #d32f2f; color: #fff; border: none; padding: 14px 30px; font-size: 16px; font-weight: bold; border-radius: 5px; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn-submit:hover { background: #b71c1c; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
        .input-money-wrapper { position: relative; }
        .input-money-wrapper::after { content: 'VNĐ'; position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #666; font-weight: bold; }
        .img-upload-box { border: 2px dashed #ccc; border-radius: 8px; padding: 20px; text-align: center; background: #f9f9f9; cursor: pointer; transition: 0.3s; }
        .img-upload-box:hover { border-color: #007bff; background: #f1f7ff; }
        .preview-img { max-width: 100%; max-height: 250px; border-radius: 6px; display: none; margin: 10px auto; object-fit: cover;}
    </style>
</head>
<body>
 
<div class="dashboard-wrapper">
    <aside class="sidebar"> 
        <a href="../index.php"><div class="sidebar-logo">Charity Events</div></a>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Tổng quan</a></li>
            <li class="active"><a href="create_campaign.php">+ Tạo chiến dịch mới</a></li>
            <li><a href="manage_news.php">📝 Đăng tin tức</a></li>
            <li><a href="tinhnguyen.php">👥 Quản lý tình nguyện viên</a></li>
            <li><a href="settings.php">⚙️ Cài đặt tài khoản</a></li>
        </ul>
    </aside>
 
    <main class="main-content">
        <div class="dash-header" style="display: grid; grid-template-columns: 100px 1fr 100px; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 25px;">
            <div style="text-align: left;">
                <a href="dashboard.php" style="background-color: #d32f2f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 15px; transition: 0.3s; display: inline-block;">Trở về</a>
            </div>
            <h1 class="dash-title" style="margin: 0; border: none; padding: 0; text-align: center; width: 100%;">Tạo chiến dịch gây quỹ mới</h1>
            <div></div>
        </div>
 
        <div class="form-card">
            <?php if(!empty($error)): ?>
                <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create_campaign">
                
                <div class="form-group">
                    <label>Tên mục tiêu / Tên chiến dịch <span style="color:red">*</span></label>
                    <input type="text" name="target_name" class="form-control" placeholder="Vd: Xây trường cho em nhỏ vùng cao..." required>
                </div>
                
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Phân loại chiến dịch <span style="color:red">*</span></label>
                        <select name="campaign_category" class="form-control" required>
                            <option value="">-- Chọn danh mục --</option>
                            <option value="Giáo dục & Trường học">Giáo dục & Trường học</option>
                            <option value="Y tế & Sức khỏe">Y tế & Sức khỏe</option>
                            <option value="Cứu trợ khẩn cấp (Lũ lụt, thiên tai)">Cứu trợ khẩn cấp (Lũ lụt, thiên tai)</option>
                            <option value="Xây dựng cơ sở hạ tầng">Xây dựng cơ sở hạ tầng (Cầu, đường...)</option>
                            <option value="Bảo vệ môi trường">Bảo vệ môi trường</option>
                            <option value="Trẻ em mồ côi & Người già neo đơn">Trẻ em & Người già neo đơn</option>
                            <option value="Khác">Khác...</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Địa điểm triển khai dự án <span style="color:red">*</span></label>
                        <input type="text" name="campaign_location" class="form-control" placeholder="Vd: Xã Mèo Vạc, Tỉnh Hà Giang" required>
                    </div>
                </div>
 
                <div class="form-group">
                    <label>Số tiền mục tiêu cần huy động <span style="color:red">*</span></label>
                    <div class="input-money-wrapper">
                        <input type="text" id="money_input" name="target_amount" class="form-control" placeholder="Vd: 100,000,000" required>
                    </div>
                </div>
 
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Ngày bắt đầu <span style="color:red">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Ngày kết thúc <span style="color:red">*</span></label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                </div>
 
                <div class="form-group">
                    <label>Ảnh bìa đại diện cho chiến dịch <span style="color:red">*</span></label>
                    <p style="font-size: 13px; color: #666; margin-top: -5px;">Nên chọn ảnh ngang, chất lượng cao, thể hiện rõ nhất nội dung kêu gọi.</p>
                    <div class="img-upload-box" onclick="document.getElementById('imageUpload').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 30px; color: #007bff; margin-bottom: 10px;"></i>
                        <p id="uploadText" style="margin: 0; color: #555;">Bấm vào đây để chọn ảnh từ máy tính của bạn</p>
                        <input type="file" id="imageUpload" name="campaign_image" accept="image/*" style="display: none;" required>
                        <img id="imagePreview" class="preview-img" alt="Preview">
                    </div>
                </div>
 
                <div class="form-group">
                    <label>Câu chuyện chiến dịch <span style="color:red">*</span></label>
                    <p style="font-size: 13px; color: #666; margin-top: -5px;">Hãy chia sẻ hoàn cảnh và lý do bạn tạo chiến dịch này để kêu gọi sự đồng cảm từ cộng đồng.</p>
                    <textarea id="story_editor" name="story"></textarea>
                </div>
 
                <button type="submit" class="btn-submit">🚀 TẠO CHIẾN DỊCH NGAY</button>
            </form>
        </div>
    </main>
</div>
 
<script>
    ClassicEditor.create(document.querySelector('#story_editor'), {
        toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo' ]
    }).catch(error => { console.error(error); });
 
    const moneyInput = document.getElementById('money_input');
    moneyInput.addEventListener('input', function(e) {
        let value = this.value.replace(/[^0-9]/g, ''); 
        if (value !== '') {
            this.value = new Intl.NumberFormat('en-US').format(value);
        }
    });
 
    const imageUpload = document.getElementById('imageUpload');
    const imagePreview = document.getElementById('imagePreview');
    const uploadText = document.getElementById('uploadText');
 
    imageUpload.addEventListener('change', function(event) {
        if(event.target.files.length > 0){
            let src = URL.createObjectURL(event.target.files[0]);
            imagePreview.src = src;
            imagePreview.style.display = "block";
            uploadText.style.display = "none";
        }
    });
</script>
 
</body>
</html>