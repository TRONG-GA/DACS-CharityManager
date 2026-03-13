<?php
/**
 * BENEFACTOR REGISTER - FIXED VERSION
 * Thay đổi: INSERT vào benefactor_applications thay vì charity_registrations
 * Table charity_registrations đã migrate → events
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
 
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php'; 
require_once __DIR__ . '/../includes/navbar.php'; 
require_once __DIR__ . '/../includes/security.php';
 
// Yêu cầu đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("<div style='text-align:center; padding: 50px;'><h3>Vui lòng đăng nhập!</h3><a href='../auth/login.php'>Đăng nhập</a></div>");
}
$user_id = $_SESSION['user_id'];
 
// TRẠNG THÁI 3: XỬ LÝ SUBMIT FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['is_submit_form'])) {
    requireCSRF();
    
    try {
        $pdo->beginTransaction();
 
        $account_type = $_POST['account_type'] ?? 'personal';
        $fullname     = sanitize($_POST['fullname'] ?? $_POST['org_name'] ?? '');
        $phone        = sanitize($_POST['phone'] ?? $_POST['rep_phone'] ?? '');
        $email        = sanitize($_POST['email'] ?? $_POST['rep_email'] ?? '');
        $address      = sanitize($_POST['address'] ?? '');
        $organization_type = $_POST['organization'] ?? null;
        
        $bank_name    = $_POST['bank_name'] ?? null;
        $bank_account = sanitize($_POST['bank_account'] ?? '');
        $bank_owner   = strtoupper(sanitize($_POST['bank_owner'] ?? ''));
 
        $purposes = $_POST['purpose'] ?? [];
        if (in_array('other', $purposes)) {
            $purposes[] = "Khác: " . sanitize($_POST['purpose_other'] ?? '');
        }
        
        $platforms = $_POST['platform'] ?? [];
        if (in_array('other', $platforms)) {
            $platforms[] = "Khác: " . sanitize($_POST['platform_other'] ?? '');
        }
        
        $additional_info = json_encode([
            'account_type' => $account_type,
            'dob' => $_POST['dob'] ?? null,
            'social_link' => $_POST['social_link'] ?? $_POST['website'] ?? null,
            'role' => $_POST['role'] ?? null,
            'club_name' => $_POST['club_name'] ?? null,
            'org_name' => $_POST['org_name'] ?? null,
            'founding_date' => $_POST['founding_date'] ?? null,
            'website' => $_POST['website'] ?? null,
            'main_field' => $_POST['main_field'] ?? null,
            'rep_name' => $_POST['rep_name'] ?? null,
            'rep_phone' => $_POST['rep_phone'] ?? null,
            'rep_email' => $_POST['rep_email'] ?? null,
            'intro_link' => $_POST['intro_link'] ?? null,
            'description' => $_POST['description'] ?? null,
            'purposes' => $purposes,
            'commitment' => $_POST['commitment'] ?? null,
            'platforms' => $platforms,
            'laws' => $_POST['law'] ?? [],
            'channel' => $_POST['channel'] ?? null
        ], JSON_UNESCAPED_UNICODE);
 
        // Upload files
        $logo_file = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logo_file = uploadImage($_FILES['logo'], 'benefactor_docs');
        }
 
        $documents = [];
        if (isset($_FILES['achievements'])) {
            $totalFiles = count($_FILES['achievements']['name']);
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($_FILES['achievements']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpFile = [
                        'name' => $_FILES['achievements']['name'][$i],
                        'type' => $_FILES['achievements']['type'][$i],
                        'tmp_name' => $_FILES['achievements']['tmp_name'][$i],
                        'error' => $_FILES['achievements']['error'][$i],
                        'size' => $_FILES['achievements']['size'][$i]
                    ];
                    $uploaded = uploadFile($tmpFile, 'benefactor_docs');
                    if ($uploaded) $documents[] = $uploaded;
                }
            }
        }
        $documents_json = json_encode($documents, JSON_UNESCAPED_UNICODE);
 
        // INSERT VÀO benefactor_applications
        $sql = "INSERT INTO benefactor_applications 
                (user_id, fullname, phone, email, address, organization_type, 
                 logo_file, documents, additional_info, bank_name, bank_account, bank_owner, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id, $fullname, $phone, $email, $address, $organization_type,
            $logo_file, $documents_json, $additional_info, 
            $bank_name, $bank_account, $bank_owner
        ]);
 
        $appId = $pdo->lastInsertId();
 
        // Update user role (chưa verified)
        $updateRoleSql = "UPDATE users SET role = 'benefactor', benefactor_status = 'pending' WHERE id = ?";
        $pdo->prepare($updateRoleSql)->execute([$user_id]);
        
        $_SESSION['role'] = 'benefactor';
        $_SESSION['benefactor_status'] = 'pending';
 
        // Notify admins
        notifyAdmins(
            'Đơn đăng ký nhà hảo tâm mới',
            $fullname . ' đã nộp đơn đăng ký nhà hảo tâm',
            BASE_URL . '/admin/benefactors/application_detail.php?id=' . $appId
        );
 
        $pdo->commit();
 
        die("<!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <title>Thành công</title>
            <style>
                body { background: #f39b9b; margin: 0; font-family: Arial; overflow: hidden; }
                .toast-success {
                    position: fixed; top: 30px; right: 30px; background: #fff; color: #333; 
                    padding: 16px 25px; border-radius: 6px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                    font-size: 16px; font-weight: bold; border-left: 5px solid #28a745;
                    animation: slideIn 0.4s, fadeOut 0.4s 2s forwards; z-index: 9999;
                }
                @keyframes slideIn { from { transform: translateX(120%); } to { transform: translateX(0); } }
                @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
            </style>
        </head>
        <body>
            <div class='toast-success'>✅ Đăng ký thành công! Chờ admin phê duyệt.</div>
            <script>setTimeout(() => window.location.href = '../index.php', 2400);</script>
        </body>
        </html>");
 
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Lỗi SQL: " . $e->getMessage());
    }
}
 
// TRẠNG THÁI 1: HIỂN THỊ CHỌN LOẠI TÀI KHOẢN
if (!isset($_POST['form_type'])) {
?>
    <link rel="stylesheet" href="/DACS-CharityManager/public/css/benefactor/register0.css">
    <main class="register-page">
        <div class="register-title">Đăng ký mở Tài khoản thanh toán minh bạch</div>
        <div class="register-container">
            <a href="javascript:void(0)" onclick="submitRegister('organization')" class="register-card organization">
                <div class="card-inner">
                    <span class="register-btn">Đăng ký tài khoản tổ chức</span>
                    <img src="../public/uploads/documents/org_register.png" alt="Org" class="register-image">
                </div>
            </a>
            <a href="javascript:void(0)" onclick="submitRegister('personal')" class="register-card personal">
                <div class="card-inner">
                    <span class="register-btn light">Đăng ký tài khoản cá nhân</span>
                    <img src="../public/uploads/documents/personal_register.png" alt="Personal" class="register-image">
                </div>
            </a>
        </div>
    </main>
    <form id="hiddenForm" action="register.php" method="POST" style="display: none;">
        <?= csrfField() ?>
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
 
// TRẠNG THÁI 2: HIỂN THỊ FORM ĐĂNG KÝ
$type = $_POST['form_type'];
$isOrg = ($type === 'organization');
?>
 
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký nhà hảo tâm</title>
    <link rel="stylesheet" href="../public/css/benefactor/register.css?v=<?= time() ?>">
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
                <div class="step-text">Cam kết mục đích</div>
            </div>
            <div class="step" id="step-3-icon">
                <div class="step-icon">3</div>
                <div class="step-text">Khảo sát</div>
            </div>
        </div>
 
        <form id="multiStepForm" method="post" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="form_type" value="<?= htmlspecialchars($type) ?>">
            <input type="hidden" name="account_type" value="<?= $isOrg ? 'organization' : 'personal' ?>">
            <input type="hidden" name="is_submit_form" value="1">
 
            <!-- STEP 1 - Giống file cũ, giữ nguyên HTML -->
            <div id="step-1-content" class="card-container">
                <div class="banner-wrapper">
                    <img src="../public/uploads/documents/banner.png" alt="Banner" class="banner-img">
                </div>
                
                <div class="form-header">Phần 1: Thông tin chung</div>
 
                <?php if ($isOrg): ?>
                    <!-- Form Organization - Giữ nguyên HTML từ file cũ -->
                    <div class="form-group">
                        <label id="label-org-name">Tên tổ chức <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="org_name" class="form-input-line full-width" placeholder="Nhập tên tổ chức">
                    </div>
                    <div class="form-group">
                        <label id="label-founding-date">Ngày thành lập <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="founding_date" class="form-input-line" placeholder="01/01/2001">
                    </div>
                    <div class="form-group">
                        <label id="label-website">Website <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="website" class="form-input-line full-width" placeholder="Website">
                    </div>
                    <div class="form-group">
                        <label id="label-main-field">Lĩnh vực <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="main_field" class="form-input-line full-width" placeholder="Giáo dục">
                    </div>
                    <div class="form-group">
                        <label id="label-address-org">Địa chỉ <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="address" class="form-input-line full-width" placeholder="Địa chỉ">
                    </div>
                    <div class="form-group">
                        <label id="label-intro-link">Link giới thiệu <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="intro_link" class="form-input-line full-width" placeholder="Link">
                    </div>
 
                    <div style="background:#f1f8ff; padding:15px; border-radius:6px; margin:15px 0; border-left:4px solid #007bff;">
                        <div style="font-weight:bold; margin-bottom:10px;">💳 Thông tin ngân hàng</div>
                        <div style="display:flex; gap:15px; margin-bottom:10px;">
                            <div style="flex:1;">
                                <label id="label-bank-name">Ngân hàng <span class="req">*</span><span class="error-text hidden"></span></label>
                                <select name="bank_name" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                                    <option value="">-- Chọn --</option>
                                    <option value="MB">MB Bank</option>
                                    <option value="VCB">Vietcombank</option>
                                    <option value="TCB">Techcombank</option>
                                    <option value="ACB">ACB</option>
                                    <option value="BIDV">BIDV</option>
                                    <option value="VIETINBANK">VietinBank</option>
                                    <option value="TPB">TPBank</option>
                                    <option value="MOMO">MoMo</option>
                                </select>
                            </div>
                            <div style="flex:1;">
                                <label id="label-bank-account">Số TK <span class="req">*</span><span class="error-text hidden"></span></label>
                                <input type="text" name="bank_account" class="form-input-line" placeholder="0987654321">
                            </div>
                        </div>
                        <div class="form-group">
                            <label id="label-bank-owner">Tên chủ TK <span class="req">*</span><span class="error-text hidden"></span></label>
                            <input type="text" name="bank_owner" class="form-input-line full-width" placeholder="NGUYEN VAN A">
                        </div>
                    </div>
 
                    <div class="form-group">
                        <label id="label-achievements-org">Thành tích <span class="req">*</span><span class="error-text hidden"></span></label>
                        <div class="file-upload-wrapper">
                            <label class="btn-upload">Thêm tệp
                                <input type="file" name="achievements[]" multiple accept=".png,.jpg,.jpeg,.pdf" id="input-achievements">
                            </label>
                            <div id="display-achievements" class="file-name-list"></div>
                        </div>
                    </div>
 
                    <div style="background:#f9f9f9; padding:15px; font-weight:bold; margin:10px 0; border-left:4px solid #dc3545;">
                        Người đại diện
                    </div>
                    <div class="form-group">
                        <label id="label-rep-name">Họ tên <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="rep_name" class="form-input-line" placeholder="Nguyễn Văn A">
                    </div>
                    <div class="form-group">
                        <label id="label-rep-phone">SĐT <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="rep_phone" class="form-input-line" placeholder="0987654321">
                    </div>
                    <div class="form-group">
                        <label id="label-rep-email">Email <span class="req">*</span><span class="error-text hidden"></span></label>
                        <input type="email" name="rep_email" class="form-input-line" placeholder="email@example.com">
                    </div>
 
                <?php else: ?>
                    <!-- Form Personal - Giữ nguyên từ file cũ -->
                    <div class="form-group">
                        <label id="label-fullname">Họ tên <span>*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="fullname" class="form-input-line" placeholder="Nguyễn Văn A">
                    </div>
                    <div class="form-group">
                        <label id="label-dob">Ngày sinh <span>*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="dob" class="form-input-line" placeholder="01/01/2001">
                    </div>
                    <div class="form-group">
                        <label id="label-phone">SĐT <span>*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="phone" class="form-input-line" placeholder="0987654321">
                    </div>
                    <div class="form-group">
                        <label id="label-email">Email <span>*</span><span class="error-text hidden"></span></label>
                        <input type="email" name="email" class="form-input-line" placeholder="email@example.com">
                    </div>
                    
                    <div style="background:#f1f8ff; padding:15px; border-radius:6px; margin:15px 0; border-left:4px solid #007bff;">
                        <div style="font-weight:bold; margin-bottom:10px;">💳 Thông tin ngân hàng</div>
                        <div style="display:flex; gap:15px; margin-bottom:10px;">
                            <div style="flex:1;">
                                <label id="label-bank-name">Ngân hàng <span class="req">*</span><span class="error-text hidden"></span></label>
                                <select name="bank_name" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                                    <option value="">-- Chọn --</option>
                                    <option value="MB">MB Bank</option>
                                    <option value="VCB">Vietcombank</option>
                                    <option value="TCB">Techcombank</option>
                                    <option value="ACB">ACB</option>
                                    <option value="BIDV">BIDV</option>
                                    <option value="VIETINBANK">VietinBank</option>
                                    <option value="TPB">TPBank</option>
                                    <option value="MOMO">MoMo</option>
                                </select>
                            </div>
                            <div style="flex:1;">
                                <label id="label-bank-account">Số TK <span class="req">*</span><span class="error-text hidden"></span></label>
                                <input type="text" name="bank_account" class="form-input-line" placeholder="0987654321">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label id="label-bank-owner">Tên chủ TK <span class="req">*</span><span class="error-text hidden"></span></label>
                            <input type="text" name="bank_owner" class="form-input-line full-width" placeholder="NGUYEN VAN A">
                        </div>
                    </div>
 
                    <div class="form-group">
                        <label id="label-social">MXH <span>*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="social_link" class="form-input-line full-width" placeholder="Link Facebook">
                    </div>
                    <div class="form-group">
                        <label id="label-address">Địa chỉ <span>*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="address" class="form-input-line full-width" placeholder="Địa chỉ">
                    </div>
                    <div class="form-group">
                        <label id="label-role">Vai trò <span>*</span><span class="error-text hidden"></span></label>
                        <div class="radio-group">
                            <label><input type="radio" name="role" value="Sáng lập"> Sáng lập</label>
                            <label><input type="radio" name="role" value="Chủ nhiệm"> Chủ nhiệm</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label id="label-club-name">Tên CLB <span>*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="club_name" class="form-input-line full-width" placeholder="Nhóm Từ Thiện A">
                    </div>
                    <div class="form-group">
                        <label id="label-logo">Logo <span>*</span><span class="error-text hidden"></span></label>
                        <div class="file-upload-wrapper">
                            <label class="btn-upload">Thêm ảnh
                                <input type="file" name="logo" accept="image/*" id="input-logo">
                            </label>
                            <div id="display-logo" class="file-name-list"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label id="label-org">Trực thuộc <span>*</span><span class="error-text hidden"></span></label>
                        <div class="radio-group">
                            <label><input type="radio" name="organization" value="Chính trị xã hội"> Chính trị xã hội</label>
                            <label><input type="radio" name="organization" value="Xã hội"> Xã hội</label>
                            <label><input type="radio" name="organization" value="Xã hội nghề nghiệp"> Xã hội nghề nghiệp</label>
                            <label><input type="radio" name="organization" value="Tôn giáo"> Tôn giáo</label>
                            <label><input type="radio" name="organization" value="Kinh tế"> Kinh tế</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label id="label-description">Link giới thiệu <span>*</span><span class="error-text hidden"></span></label>
                        <input type="text" name="description" class="form-input-line full-width" placeholder="Link">
                    </div>
                    <div class="form-group">
                        <label id="label-achievements">Thành tích <span>*</span><span class="error-text hidden"></span></label>
                        <div class="file-upload-wrapper">
                            <label class="btn-upload">Thêm tệp
                                <input type="file" name="achievements[]" multiple accept=".png,.jpg,.jpeg,.pdf" id="input-achievements">
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