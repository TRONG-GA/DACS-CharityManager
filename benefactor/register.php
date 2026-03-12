<?php
// BẬT HIỂN THỊ LỖI ĐỂ KIỂM TRA 
// benefactor register.php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Gọi kết nối Database và Header
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php'; 
require_once __DIR__ . '/../includes/navbar.php'; 

// Yêu cầu user phải đăng nhập mới được đăng ký (Chặn luôn từ đầu)
if (!isset($_SESSION['user_id'])) {
    die("<div style='text-align:center; padding: 50px; font-family: Arial;'><h3>Vui lòng đăng nhập trước khi đăng ký!</h3><a href='../login.php'>Đăng nhập tại đây</a></div>");
}
$user_id = $_SESSION['user_id'];

// =========================================================================
// TRẠNG THÁI 3: XỬ LÝ LƯU DATABASE KHI FORM SUBMIT CUỐI CÙNG
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['is_submit_form'])) {
    try {
        $pdo->beginTransaction();

        $account_type = $_POST['account_type'] ?? 'personal';
        $fullname     = $_POST['fullname'] ?? null;
        $dob          = $_POST['dob'] ?? null;
        $phone        = $_POST['phone'] ?? null;
        $email        = $_POST['email'] ?? null;
        $social_link  = $_POST['social_link'] ?? null;
        $address      = $_POST['address'] ?? null;
        $role         = $_POST['role'] ?? null;
        $club_name    = $_POST['club_name'] ?? null;
        $organization = $_POST['organization'] ?? null;
        $description  = $_POST['description'] ?? null;
        $org_name     = $_POST['org_name'] ?? null;
        $founding_date= $_POST['founding_date'] ?? null;
        $website      = $_POST['website'] ?? null;
        $main_field   = $_POST['main_field'] ?? null;
        $rep_name     = $_POST['rep_name'] ?? null;
        $rep_phone    = $_POST['rep_phone'] ?? null;
        $rep_email    = $_POST['rep_email'] ?? null;

        // BỔ SUNG 3 TRƯỜNG THÔNG TIN NGÂN HÀNG
        $bank_name    = $_POST['bank_name'] ?? null;
        $bank_account = $_POST['bank_account'] ?? null;
        $bank_owner   = strtoupper(trim($_POST['bank_owner'] ?? ''));

        $purposes = $_POST['purpose'] ?? [];
        if (in_array('other', $purposes)) {
            $purposes[] = "Khác: " . ($_POST['purpose_other'] ?? '');
        }
        $purposes_json = json_encode($purposes, JSON_UNESCAPED_UNICODE); 
        $commitment   = trim($_POST['commitment'] ?? '');

        $platforms = $_POST['platform'] ?? [];
        if (in_array('other', $platforms)) {
            $platforms[] = "Khác: " . ($_POST['platform_other'] ?? '');
        }
        $platforms_json = json_encode($platforms, JSON_UNESCAPED_UNICODE);
        
        $laws_json    = json_encode($_POST['law'] ?? [], JSON_UNESCAPED_UNICODE);
        $channel      = trim($_POST['channel'] ?? '');

        // UPLOAD FILE
        $uploadDir = __DIR__ . '/../../public/uploads/'; 
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true); 

        $logoPath = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoName = time() . '_logo_' . basename($_FILES['logo']['name']);
            move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName);
            $logoPath = '/DACS-CharityManager-main/public/uploads/' . $logoName;
        }

        $achievementsPaths = [];
        if (isset($_FILES['achievements'])) {
            $totalFiles = count($_FILES['achievements']['name']);
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($_FILES['achievements']['error'][$i] === UPLOAD_ERR_OK) {
                    $achName = time() . '_ach_' . $i . '_' . basename($_FILES['achievements']['name'][$i]);
                    move_uploaded_file($_FILES['achievements']['tmp_name'][$i], $uploadDir . $achName);
                    $achievementsPaths[] = '/DACS-CharityManager-main/public/uploads/' . $achName;
                }
            }
        }
        $achievements_json = json_encode($achievementsPaths, JSON_UNESCAPED_UNICODE);

        // LƯU VÀO DATABASE (Đã bổ sung 3 cột Bank)
        $sql = "INSERT INTO charity_registrations 
        (user_id, account_type, fullname, dob, phone, email, social_link, address, role, club_name, logo_path, organization, description, achievements_path, purposes, commitment, platforms, laws, channel, 
        website, main_field, rep_name, rep_phone, rep_email, founding_date, bank_name, bank_account, bank_owner) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id, $account_type, $fullname, $dob, $phone, $email, $social_link, $address, $role, $club_name, $logoPath, $organization, $description, $achievements_json, 
            $purposes_json, $commitment, $platforms_json, $laws_json, $channel,
            $website, $main_field, $rep_name, $rep_phone, $rep_email, $founding_date,
            $bank_name, $bank_account, $bank_owner
        ]);

        $updateRoleSql = "UPDATE users SET role = 'benefactor' WHERE id = ?";
        $stmtRole = $pdo->prepare($updateRoleSql);
        $stmtRole->execute([$user_id]);
        
        $_SESSION['role'] = 'benefactor'; 
        $pdo->commit();

        die("<!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Thành công</title>
            <style>
                body { background-color: #f39b9b; margin: 0; font-family: Arial, sans-serif; overflow: hidden; }
                .toast-success {
                    position: fixed; top: 30px; right: 30px;
                    background: #ffffff; color: #333; 
                    padding: 16px 25px; border-radius: 6px; 
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                    font-size: 16px; font-weight: bold;
                    border-left: 5px solid #28a745;
                    animation: slideIn 0.4s ease-out, fadeOut 0.4s 2s forwards;
                    z-index: 9999;
                }
                @keyframes slideIn { from { transform: translateX(120%); } to { transform: translateX(0); } }
                @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
            </style>
        </head>
        <body>
            <div class='toast-success'>✅ Đăng ký thành công</div>
            <script>
                setTimeout(function() {
                    window.location.href = '../index.php'; 
                }, 2400); 
            </script>
        </body>
        </html>");

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Lỗi SQL: " . $e->getMessage());
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Lỗi hệ thống: " . $e->getMessage());
    }
}

// =========================================================================
// TRẠNG THÁI 1: CHƯA CHỌN LOẠI TÀI KHOẢN -> HIỂN THỊ GIAO DIỆN CHỌN
// =========================================================================
if (!isset($_POST['form_type'])) {
?>
    <link rel="stylesheet" href="/DACS-CharityManager/public/css/benefactor/register0.css">
    <main class="register-page">
        <div class="register-title">
            Đăng ký mở Tài khoản thanh toán minh bạch
        </div>
        <div class="register-container">
            <a href="javascript:void(0)" onclick="submitRegister('organization')" class="register-card organization">
                <div class="card-inner">
                    <span class="register-btn">Đăng ký tài khoản tổ chức</span>
                    <img src="../public/uploads/documents/org_register.png" alt="Đăng ký tổ chức" class="register-image">
                </div>
            </a>
            <a href="javascript:void(0)" onclick="submitRegister('personal')" class="register-card personal">
                <div class="card-inner">
                    <span class="register-btn light">Đăng ký tài khoản cá nhân</span>
                    <img src="../public/uploads/documents/personal_register.png" alt="Đăng ký cá nhân" class="register-image">
                </div>
            </a>
        </div>
    </main>
    <form id="hiddenForm" action="register.php" method="POST" style="display: none;">
        <input type="hidden" name="form_type" id="formTypeInput" value="">
    </form>
    <script>
    function submitRegister(type) {
        document.getElementById('formTypeInput').value = type;
        document.getElementById('hiddenForm').submit();
    }
    </script>
<?php
    require_once __DIR__ . '/../includes/footer.php';
    exit; 
}

// =========================================================================
// TRẠNG THÁI 2: ĐÃ CHỌN LOẠI TÀI KHOẢN -> HIỂN THỊ FORM ĐIỀN THÔNG TIN
// =========================================================================
$type = $_POST['form_type'];
$isOrg = ($type === 'organization');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký mở Tài khoản thanh toán minh bạch</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/benefactor/register.css?v=<?= time(); ?>">
</head>
<body>

<div class="register-main-bg">
    <div class="page-wrapper">

        <h2 class="page-title">Đăng ký mở Tài khoản thanh toán minh bạch</h2>

        <div class="stepper-container">
            <div class="stepper-bg-line"></div>
            <div class="stepper-active-line" id="stepper-line" style="width: 0%;"></div>
            
            <div class="step active" id="step-1-icon">
                <div class="step-icon">1</div>
                <div class="step-text">Thông tin chung</div>
            </div>
            <div class="step" id="step-2-icon">
                <div class="step-icon">2</div>
                <div class="step-text">Cam kết mục đích sử dụng</div>
            </div>
            <div class="step" id="step-3-icon">
                <div class="step-icon">3</div>
                <div class="step-text">Câu hỏi khảo sát</div>
            </div>
        </div>

        <form id="multiStepForm" method="post" enctype="multipart/form-data" action="register.php">
            <input type="hidden" name="form_type" value="<?= htmlspecialchars($type) ?>">
            <input type="hidden" name="account_type" value="<?= $isOrg ? 'organization' : 'personal' ?>">
            <input type="hidden" name="is_submit_form" value="1">

            <div id="step-1-content" class="card-container">
                <div class="banner-wrapper">
                    <img src="../public/uploads/documents/banner.png" alt="Banner đăng ký" class="banner-img">
                </div>
                
                <div class="form-header">Phần 1: Thông tin chung của <?= $isOrg ? 'tổ chức' : 'cá nhân' ?></div>

                <?php if ($isOrg): ?>
                    <div class="form-group">
                        <label id="label-org-name">Tên tổ chức <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="org_name" class="form-input-line full-width" placeholder="Nhập đầy đủ tên của tổ chức">
                    </div>
                    <div class="form-group">
                        <label id="label-founding-date">Ngày thành lập <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="founding_date" class="form-input-line" placeholder="Vd: 01/01/2001">
                    </div>
                    <div class="form-group">
                        <label id="label-website">Website hoặc trang tin điện tử <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="website" class="form-input-line full-width" placeholder="Nhập đường dẫn tới website hoặc trang điện tử của tổ chức">
                    </div>
                    <div class="form-group">
                        <label id="label-main-field">Lĩnh vực hoạt động chính <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="main_field" class="form-input-line full-width" placeholder="Vd: Giáo dục">
                    </div>
                    <div class="form-group">
                        <label id="label-address-org">Địa chỉ trụ sở chính <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="address" class="form-input-line full-width" placeholder="Địa chỉ của tổ chức">
                    </div>
                    <div class="form-group">
                        <label id="label-intro-link">Thông tin giới thiệu hoạt động của tổ chức <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="intro_link" class="form-input-line full-width" placeholder="Nhập đường dẫn">
                        <span class="label-desc">(Đường dẫn Facebook, website, youtube, instagram, tiktok . . .)</span>
                    </div>

                    <div style="background-color: #f1f8ff; padding: 15px 25px; border-radius: 6px; margin-top: 15px; border-left: 4px solid #007bff;">
                        <div style="font-weight: bold; color: #0056b3; margin-bottom: 10px;">💳 Thông tin tài khoản nhận tiền (Dùng để tạo QR)</div>
                        <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                            <div style="flex: 1;">
                                <label id="label-bank-name">Ngân hàng <span class="req">*</span><span class="error-text hidden"></span></label>
                                <select name="bank_name" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                                    <option value="">-- Chọn ngân hàng --</option>
                                    <option value="MB">MB Bank</option>
                                    <option value="VCB">Vietcombank</option>
                                    <option value="TCB">Techcombank</option>
                                    <option value="ACB">ACB</option>
                                    <option value="BIDV">BIDV</option>
                                    <option value="VIETINBANK">VietinBank</option>
                                    <option value="TPB">TPBank</option>
                                    <option value="MOMO">Ví MoMo</option>
                                </select>
                            </div>
                            <div style="flex: 1;">
                                <label id="label-bank-account">Số tài khoản <span class="req">*</span><span class="error-text hidden"></span></label>
                                <input type="text" name="bank_account" class="form-input-line" placeholder="Vd: 0987654321">
                            </div>
                        </div>
                        <div class="form-group">
                            <label id="label-bank-owner">Tên chủ tài khoản <span class="req">*</span><span class="error-text hidden"></span></label>
                            <input type="text" name="bank_owner" class="form-input-line full-width" placeholder="VIẾT HOA KHÔNG DẤU (Vd: QUY TU THIEN ABC)">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label id="label-achievements-org">Thành tích, khen thưởng, được ghi nhận <span class="req">*</span><span class="error-text hidden"></span></label>
                        <div class="file-upload-wrapper">
                            <label class="btn-upload">
                                <svg width="16" height="16" viewBox="0 0 24 24"><path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"></path></svg> Thêm tệp
                                <input type="file" name="achievements[]" multiple accept=".png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx" id="input-achievements">
                            </label>
                            <div id="display-achievements" class="file-name-list"></div>
                        </div>
                    </div>

                    <div style="background-color: #f9f9f9; padding: 15px 25px; font-weight: bold; border-radius: 6px; margin-top: 10px; border-left: 4px solid #dc3545; color: #333;">
                        Thông tin người đại diện liên hệ
                    </div>
                    <div class="form-group">
                        <label id="label-rep-name">Họ tên người đại diện <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="rep_name" class="form-input-line" placeholder="Vd: Nguyễn Văn A">
                    </div>
                    <div class="form-group">
                        <label id="label-rep-phone">Điện thoại <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="rep_phone" class="form-input-line" placeholder="Số điện thoại">
                    </div>
                    <div class="form-group">
                        <label id="label-rep-email">Email <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="email" name="rep_email" class="form-input-line" placeholder="Vd: email@example.com">
                    </div>

                <?php else: ?>
                    <div class="form-group">
                        <label id="label-fullname">Họ và Tên <span>*</span> <span class="error-text hidden"></span></label>
                        <input type="text" name="fullname" class="form-input-line" placeholder="Vd: Nguyễn Văn A">
                    </div>
                    <div class="form-group">
                        <label id="label-dob">Ngày/tháng/năm sinh <span>*</span> <span class="error-text hidden"></span></label>
                        <input type="text" name="dob" class="form-input-line" placeholder="Vd: 01/01/2001">
                    </div>
                    <div class="form-group">
                        <label id="label-phone">Điện thoại <span>*</span> <span class="error-text hidden"></span></label>
                        <input type="text" name="phone" class="form-input-line" placeholder="Số điện thoại của bạn">
                    </div>
                    <div class="form-group">
                        <label id="label-email">Email <span>*</span> <span class="error-text hidden"></span></label>
                        <input type="email" name="email" class="form-input-line" placeholder="Vd: email@example.com">
                    </div>
                    
                    <div style="background-color: #f1f8ff; padding: 15px 25px; border-radius: 6px; margin-top: 15px; margin-bottom: 15px; border-left: 4px solid #007bff;">
                        <div style="font-weight: bold; color: #0056b3; margin-bottom: 10px;">💳 Thông tin tài khoản nhận tiền (Dùng để tạo QR)</div>
                        <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                            <div style="flex: 1;">
                                <label id="label-bank-name">Ngân hàng <span class="req">*</span><span class="error-text hidden"></span></label>
                                <select name="bank_name" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                                    <option value="">-- Chọn ngân hàng --</option>
                                    <option value="MB">MB Bank</option>
                                    <option value="VCB">Vietcombank</option>
                                    <option value="TCB">Techcombank</option>
                                    <option value="ACB">ACB</option>
                                    <option value="BIDV">BIDV</option>
                                    <option value="VIETINBANK">VietinBank</option>
                                    <option value="TPB">TPBank</option>
                                    <option value="MOMO">Ví MoMo</option>
                                </select>
                            </div>
                            <div style="flex: 1;">
                                <label id="label-bank-account">Số tài khoản <span class="req">*</span><span class="error-text hidden"></span></label>
                                <input type="text" name="bank_account" class="form-input-line" placeholder="Vd: 0987654321">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label id="label-bank-owner">Tên chủ tài khoản <span class="req">*</span><span class="error-text hidden"></span></label>
                            <input type="text" name="bank_owner" class="form-input-line full-width" placeholder="VIẾT HOA KHÔNG DẤU (Vd: NGUYEN VAN A)">
                        </div>
                    </div>

                    <div class="form-group">
                        <label id="label-social">Tài khoản mạng xã hội của bạn <span>*</span> <span class="error-text hidden"></span></label>
                        <input type="text" name="social_link" class="form-input-line full-width" placeholder="Nhập đường dẫn tới tài khoản mạng xã hội của bạn">
                    </div>
                    <div class="form-group">
                        <label id="label-address">Địa chỉ thường trú <span>*</span> <span class="error-text hidden"></span></label>
                        <input type="text" name="address" class="form-input-line full-width" placeholder="Phường/xã, quận huyện, thành phố">
                    </div>
                    <div class="form-group">
                        <label id="label-role">Vai trò của bạn trong CLB/Đội/Nhóm <span>*</span> <span class="error-text hidden"></span></label>
                        <div class="radio-group">
                            <label><input type="radio" name="role" value="Sáng lập"> Sáng lập</label>
                            <label><input type="radio" name="role" value="Chủ nhiệm"> Chủ nhiệm</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label id="label-club-name">Tên CLB/Đội/Nhóm của bạn <span>*</span> <span class="error-text hidden"></span></label>
                        <input type="text" name="club_name" class="form-input-line full-width" placeholder="Vd: Nhóm Từ Thiện A">
                    </div>
                    <div class="form-group">
                        <label id="label-logo">Logo, hình ảnh nhận diện CLB/Đội/Nhóm <span>*</span> <span class="error-text hidden"></span></label>
                        <div class="file-upload-wrapper">
                            <label class="btn-upload">
                                <svg width="16" height="16" viewBox="0 0 24 24"><path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"></path></svg> Thêm ảnh
                                <input type="file" name="logo" accept="image/*" id="input-logo">
                            </label>
                            <div id="display-logo" class="file-name-list"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label id="label-org">CLB/Đội/Nhóm của bạn trực thuộc tổ chức nào? <span>*</span> <span class="error-text hidden"></span></label>
                        <div class="radio-group">
                            <label><input type="radio" name="organization" value="Chính trị xã hội"> Tổ chức chính trị xã hội</label>
                            <label><input type="radio" name="organization" value="Xã hội"> Tổ chức xã hội</label>
                            <label><input type="radio" name="organization" value="Xã hội nghề nghiệp"> Tổ chức xã hội nghề nghiệp</label>
                            <label><input type="radio" name="organization" value="Tôn giáo"> Tổ chức tôn giáo</label>
                            <label><input type="radio" name="organization" value="Kinh tế"> Tổ chức kinh tế, Doanh nghiệp</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label id="label-description">Đường dẫn giới thiệu hoạt động <span>*</span> <span class="error-text hidden"></span></label>
                        <input type="text" name="description" class="form-input-line full-width" placeholder="Nhập đường dẫn">
                    </div>
                    <div class="form-group">
                        <label id="label-achievements">Thành tích, khen thưởng <span>*</span><span class="error-text hidden"></span></label>
                        <div class="file-upload-wrapper">
                            <label class="btn-upload">
                                <svg width="16" height="16" viewBox="0 0 24 24"><path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"></path></svg> Thêm tệp
                                <input type="file" name="achievements[]" multiple accept=".png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx" id="input-achievements">
                            </label>
                            <div id="display-achievements" class="file-name-list"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-footer">
                    <button type="button" class="btn-submit" id="btnNext1">Tiếp tục</button>
                </div>
            </div>

            <div id="step-2-content" class="card-container hidden">
                <div>
                    <div class="card-header-orange">Phần 2: Cam kết mục đích sử dụng Tài khoản thanh toán minh bạch</div>
                    <div class="card card-attached">
                        <label class="question-label" id="label-purpose">Anh/Chị cam kết sử dụng TKTT MB cho mục đích nào sau đây? <span>*</span><span class="error-text hidden"></span></label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="purpose[]" value="voluntary"> Vận động, tiếp nhận các nguồn đóng góp tự nguyện</label>
                            <label><input type="checkbox" name="purpose[]" value="community"> Vận động gây quỹ nhằm phát triển dự án cộng đồng</label>
                            <label><input type="checkbox" name="purpose[]" value="transparency"> Để công khai minh bạch</label>
                            <label><input type="checkbox" name="purpose[]" value="nonprofit"> Các mục đích phi lợi nhuận khác</label>
                            <label style="align-items: center;">
                                <input type="checkbox" name="purpose[]" value="other" id="cb-other-purpose"> Mục khác:
                                <span id="input-purpose-container" class="hidden">
                                    <input type="text" name="purpose_other" class="other-input-line">
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <label class="question-label" id="label-commitment">Anh/Chị có cam kết công bố công khai việc sử dụng TKTT MB? <span>*</span><span class="error-text hidden"></span></label>
                    <div class="radio-group">
                        <label><input type="radio" name="commitment" value="agree"> Đồng ý</label>
                        <label><input type="radio" name="commitment" value="unsure"> Chưa chắc chắn</label>
                        <label><input type="radio" name="commitment" value="disagree"> Không đồng ý</label>
                    </div>
                </div>
                
                <div class="form-footer">
                    <button type="button" class="btn-action" id="btnPrev2">Quay lại</button>
                    <button type="button" class="btn-action" id="btnNext2">Tiếp tục</button>
                </div>
            </div>

            <div id="step-3-content" class="card-container hidden">
                <div>
                    <div class="card-header-orange">Phần 3: Câu hỏi khảo sát</div>
                    <div class="card card-attached">
                        <label class="question-label" id="label-platform">Anh/Chị biết đến nền tảng gây quỹ trực tuyến nào sau đây? <span>*</span><span class="error-text hidden"></span></label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="platform[]" value="momo"> Momo</label>
                            <label><input type="checkbox" name="platform[]" value="vinid"> VinID</label>
                            <label><input type="checkbox" name="platform[]" value="grab"> Grab</label>
                            <label><input type="checkbox" name="platform[]" value="kickstarter"> Kickstarter</label>
                            <label style="align-items: center;">
                                <input type="checkbox" name="platform[]" value="other" id="cb-other-platform"> Mục khác:
                                <span id="input-platform-container" class="hidden">
                                    <input type="text" name="platform_other" class="other-input-line">
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <label class="question-label" id="label-law">Anh/Chị có biết đến các quy định pháp luật nào dưới đây? <span>*</span><span class="error-text hidden"></span></label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="law[]" value="nd93_2021"> Nghị định 93/2021/NĐ-CP</label>
                        <label><input type="checkbox" name="law[]" value="nd93_2019"> Nghị định 93/2019/NĐ-CP</label>
                        <label><input type="checkbox" name="law[]" value="nd45_2010"> Nghị định 45/2010/NĐ-CP</label>
                        <label><input type="checkbox" name="law[]" value="tt41_2022"> Thông tư 41/2022/TT-BTC</label>
                        <label><input type="checkbox" name="law[]" value="none"> Không biết</label>
                    </div>
                </div>
                <div class="card">
                    <label class="question-label" id="label-channel">Kênh chính nào anh/chị biết đến ứng dụng Thiện nguyện <span>*</span><span class="error-text hidden"></span></label>
                    <div class="radio-group">
                        <label><input type="radio" name="channel" value="media"> Truyền hình, Báo giấy, Báo điện tử</label>
                        <label><input type="radio" name="channel" value="search"> Công cụ tìm kiếm</label>
                        <label><input type="radio" name="channel" value="staff"> Nhân viên MB tư vấn</label>
                        <label><input type="radio" name="channel" value="friend"> Bạn bè giới thiệu</label>
                    </div>
                </div>
                <div class="form-footer">
                    <button type="button" class="btn-action" id="btnPrev3">Quay lại</button>
                    <button type="button" class="btn-action" id="btnNext3">Tiếp tục</button>
                </div>
            </div>

        </form>

        <div id="confirmModal" class="modal-overlay hidden">
            <div class="modal-card">
                <h3 class="modal-title">Xác nhận đăng ký mở tài khoản thanh toán minh bạch?</h3>
                <p class="modal-desc">Bằng việc sử dụng ứng dụng Thiện Nguyện hay tạo tài khoản tại Thiện Nguyện, Tài khoản thiện nguyện sẽ đồng hành cùng bạn thực hiện sứ mệnh cộng đồng.</p>
                <div class="modal-actions">
                    <button type="button" class="btn-text" id="btnCancelModal">HỦY BỎ</button>
                    <button type="button" class="btn-text" id="btnConfirmSubmit">ĐĂNG KÝ</button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const isOrg = <?= json_encode($isOrg) ?>;
    const form = document.getElementById('multiStepForm');

    const step1 = document.getElementById('step-1-content');
    const step2 = document.getElementById('step-2-content');
    const step3 = document.getElementById('step-3-content');

    const icon1 = document.getElementById('step-1-icon');
    const icon2 = document.getElementById('step-2-icon');
    const icon3 = document.getElementById('step-3-icon');
    const stepperLine = document.getElementById('stepper-line');

    function updateStepper(step) {
        [icon1, icon2, icon3].forEach((icon, idx) => {
            icon.className = 'step';
            icon.querySelector('.step-icon').innerHTML = idx + 1;
        });

        if (step === 1) {
            icon1.classList.add('active');
            stepperLine.style.width = '0%';
        } else if (step === 2) {
            icon1.classList.add('completed');
            icon1.querySelector('.step-icon').innerHTML = '&#10003;';
            icon2.classList.add('active');
            stepperLine.style.width = '35%';
        } else if (step === 3) {
            icon1.classList.add('completed');
            icon1.querySelector('.step-icon').innerHTML = '&#10003;';
            icon2.classList.add('completed');
            icon2.querySelector('.step-icon').innerHTML = '&#10003;';
            icon3.classList.add('active');
            stepperLine.style.width = '70%';
        }
    }

    function setupOtherToggle(checkboxId, containerId) {
        const cb = document.getElementById(checkboxId);
        const container = document.getElementById(containerId);
        if(cb && container) {
            cb.addEventListener('change', () => {
                if(cb.checked) {
                    container.classList.remove('hidden');
                    container.querySelector('input').focus();
                } else {
                    container.classList.add('hidden');
                    container.querySelector('input').value = '';
                }
            });
        }
    }
    setupOtherToggle('cb-other-purpose', 'input-purpose-container');
    setupOtherToggle('cb-other-platform', 'input-platform-container');

    function validateFields(fieldsArray) {
        let isValid = true;
        fieldsArray.forEach(f => {
            const label = document.getElementById(f.id);
            if (!label) return;
            const errEl = label.querySelector('.error-text');
            errEl.classList.add('hidden');

            if (f.type === 'text') {
                const input = document.querySelector(f.selector);
                if (!input || input.value.trim() === '') {
                    isValid = false;
                    errEl.textContent = f.msg;
                    errEl.classList.remove('hidden');
                }
            } else if (f.type === 'radio' || f.type === 'checkbox') {
                const checked = document.querySelectorAll(f.selector);
                if (checked.length === 0) {
                    isValid = false;
                    errEl.textContent = f.msg;
                    errEl.classList.remove('hidden');
                } else if (f.otherCb && document.getElementById(f.otherCb).checked) {
                    const otherText = document.querySelector(f.otherInput).value.trim();
                    if (otherText === "") {
                        isValid = false;
                        errEl.textContent = f.otherMsg;
                        errEl.classList.remove('hidden');
                    }
                }
            } else if (f.type === 'file') {
                const fileInput = document.querySelector(f.selector);
                if (!fileInput || fileInput.files.length === 0) {
                    isValid = false;
                    errEl.textContent = f.msg;
                    errEl.classList.remove('hidden');
                }
            }
        });
        return isValid;
    }

    const inputLogo = document.getElementById('input-logo');
    const displayLogo = document.getElementById('display-logo');
    
    if(inputLogo) {
        inputLogo.addEventListener('change', function() {
            displayLogo.innerHTML = ''; 
            if (this.files.length > 0) {
                const div = document.createElement('div');
                div.className = 'file-name-item';
                div.textContent = this.files[0].name;
                displayLogo.appendChild(div);
            }
        });
    }

    const inputAch = document.getElementById('input-achievements');
    const displayAch = document.getElementById('display-achievements');

    if(inputAch) {
        inputAch.addEventListener('change', function() {
            displayAch.innerHTML = ''; 
            Array.from(this.files).forEach(file => {
                const div = document.createElement('div');
                div.className = 'file-name-item';
                div.textContent = file.name;
                displayAch.appendChild(div);
            });
        });
    }

    document.getElementById('btnNext1').addEventListener('click', function() {
        let fieldsStep1 = [];

        if (isOrg) {
            fieldsStep1 = [
                { id: 'label-org-name', selector: 'input[name="org_name"]', type: 'text', msg: '(Vui lòng nhập tên)' },
                { id: 'label-founding-date', selector: 'input[name="founding_date"]', type: 'text', msg: '(Vui lòng nhập)' },
                { id: 'label-website', selector: 'input[name="website"]', type: 'text', msg: '(Vui lòng nhập)' },
                { id: 'label-main-field', selector: 'input[name="main_field"]', type: 'text', msg: '(Vui lòng nhập)' },
                { id: 'label-address-org', selector: 'input[name="address"]', type: 'text', msg: '(Vui lòng nhập)' },
                { id: 'label-intro-link', selector: 'input[name="intro_link"]', type: 'text', msg: '(Vui lòng nhập link)' },
                
                // VALIDATE NGÂN HÀNG
                { id: 'label-bank-name', selector: 'select[name="bank_name"]', type: 'text', msg: '(Vui lòng chọn)' },
                { id: 'label-bank-account', selector: 'input[name="bank_account"]', type: 'text', msg: '(Vui lòng nhập)' },
                { id: 'label-bank-owner', selector: 'input[name="bank_owner"]', type: 'text', msg: '(Vui lòng nhập)' },

                { id: 'label-achievements-org', selector: 'input[name="achievements[]"]', type: 'file', msg: '(Vui lòng tải tệp)' },
                { id: 'label-rep-name', selector: 'input[name="rep_name"]', type: 'text', msg: '(Vui lòng nhập tên)' },
                { id: 'label-rep-phone', selector: 'input[name="rep_phone"]', type: 'text', msg: '(Vui lòng nhập sđt)' },
                { id: 'label-rep-email', selector: 'input[name="rep_email"]', type: 'text', msg: '(Vui lòng nhập email)' }
            ];
        } else {
            fieldsStep1 = [
                { id: 'label-fullname', selector: 'input[name="fullname"]', type: 'text', msg: '(Vui lòng nhập họ tên)' },
                { id: 'label-dob', selector: 'input[name="dob"]', type: 'text', msg: '(Vui lòng nhập ngày sinh)' },
                { id: 'label-phone', selector: 'input[name="phone"]', type: 'text', msg: '(Vui lòng nhập sđt)' },
                { id: 'label-email', selector: 'input[name="email"]', type: 'text', msg: '(Vui lòng nhập email)' },
                
                // VALIDATE NGÂN HÀNG
                { id: 'label-bank-name', selector: 'select[name="bank_name"]', type: 'text', msg: '(Vui lòng chọn)' },
                { id: 'label-bank-account', selector: 'input[name="bank_account"]', type: 'text', msg: '(Vui lòng nhập)' },
                { id: 'label-bank-owner', selector: 'input[name="bank_owner"]', type: 'text', msg: '(Vui lòng nhập)' },

                { id: 'label-social', selector: 'input[name="social_link"]', type: 'text', msg: '(Vui lòng nhập link)' },
                { id: 'label-address', selector: 'input[name="address"]', type: 'text', msg: '(Vui lòng nhập địa chỉ)' },
                { id: 'label-role', selector: 'input[name="role"]:checked', type: 'radio', msg: '(Vui lòng chọn)' },
                { id: 'label-club-name', selector: 'input[name="club_name"]', type: 'text', msg: '(Vui lòng nhập tên)' },
                { id: 'label-logo', selector: 'input[name="logo"]', type: 'file', msg: '(Vui lòng tải ảnh)' },
                { id: 'label-org', selector: 'input[name="organization"]:checked', type: 'radio', msg: '(Vui lòng chọn)' },
                { id: 'label-description', selector: 'input[name="description"]', type: 'text', msg: '(Vui lòng nhập)' },
                { id: 'label-achievements', selector: 'input[name="achievements[]"]', type: 'file', msg: '(Vui lòng tải tệp)' }
            ];
        }

        if(validateFields(fieldsStep1)) {
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
            updateStepper(2);
            window.scrollTo(0, 0);
        }
    });

    document.getElementById('btnPrev2').addEventListener('click', function() {
        step2.classList.add('hidden');
        step1.classList.remove('hidden');
        updateStepper(1);
        window.scrollTo(0, 0);
    });

    document.getElementById('btnNext2').addEventListener('click', function() {
        let isValid = validateFields([
            { id: 'label-purpose', selector: 'input[name="purpose[]"]:checked', type: 'checkbox', msg: '(Chọn ít nhất 1 mục)', otherCb: 'cb-other-purpose', otherInput: 'input[name="purpose_other"]', otherMsg: '(Vui lòng nhập rõ)' },
            { id: 'label-commitment', selector: 'input[name="commitment"]:checked', type: 'radio', msg: '(Vui lòng chọn)' }
        ]);

        if(isValid) {
            step2.classList.add('hidden');
            step3.classList.remove('hidden');
            updateStepper(3);
            window.scrollTo(0, 0);
        }
    });

    document.getElementById('btnPrev3').addEventListener('click', function() {
        step3.classList.add('hidden');
        step2.classList.remove('hidden');
        updateStepper(2);
        window.scrollTo(0, 0);
    });

    const modal = document.getElementById('confirmModal');
    
    document.getElementById('btnNext3').addEventListener('click', function() {
        const isValid = validateFields([
            { id: 'label-platform', selector: 'input[name="platform[]"]:checked', type: 'checkbox', msg: '(Chọn ít nhất 1 mục)', otherCb: 'cb-other-platform', otherInput: 'input[name="platform_other"]', otherMsg: '(Vui lòng nhập rõ)' },
            { id: 'label-law', selector: 'input[name="law[]"]:checked', type: 'checkbox', msg: '(Vui lòng chọn)' },
            { id: 'label-channel', selector: 'input[name="channel"]:checked', type: 'radio', msg: '(Vui lòng chọn)' }
        ]);

        if(isValid) {
            modal.classList.remove('hidden'); 
        }
    });

    document.getElementById('btnCancelModal').addEventListener('click', function() {
        modal.classList.add('hidden');
    });

    document.getElementById('btnConfirmSubmit').addEventListener('click', function() {
        form.submit();
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>