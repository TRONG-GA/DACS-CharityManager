<?php
require_once '../config/db.php';
require_once '../includes/security.php';

$eventId = $_GET['event_id'] ?? 0;

// XỬ LÝ FORM SUBMIT
$formSubmitted = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_donation'])) {
    $donor_name = isset($_POST['donor_name']) && !empty(trim($_POST['donor_name'])) 
        ? sanitize($_POST['donor_name']) 
        : '';
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $message = isset($_POST['message']) ? sanitize($_POST['message']) : '';
    $amount = str_replace(['.', ','], '', $_POST['amount'] ?? '0');
    
    // Nếu chọn ẩn danh thì xóa tên
    if ($is_anonymous) {
        $donor_name = '';
    }
    
    // Lưu vào session để hiển thị
    $_SESSION['pending_donation'] = [
        'donor_name' => $donor_name,
        'is_anonymous' => $is_anonymous,
        'message' => $message,
        'amount' => $amount,
        'event_id' => $eventId
    ];
    
    $formSubmitted = true;
}

// 1. LẤY THÔNG TIN SỰ KIỆN VÀ THÔNG TIN NGÂN HÀNG CỦA NGƯỜI TẠO SỰ KIỆN
$stmt = $pdo->prepare("
    SELECT e.id, e.title, e.slug, e.target_amount, e.current_amount,
           ba.bank_name, ba.bank_account, ba.bank_owner
    FROM events e 
    LEFT JOIN benefactor_applications ba ON e.user_id = ba.user_id
    WHERE e.id = ? AND e.status = 'approved'
    ORDER BY ba.id DESC LIMIT 1
");
$stmt->execute([$eventId]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header('Location: ' . BASE_URL . '/events/events.php');
    exit;
}

// Hàm hỗ trợ tạo chữ không dấu
if (!function_exists('createSlugLocal')) {
    function createSlugLocal($string) {
        $search = array('#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#', '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#', '#(ì|í|ị|ỉ|ĩ)#', '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#', '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#', '#(ỳ|ý|ỵ|ỷ|ỹ)#', '#(đ)#', '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#', '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#', '#(Ì|Í|Ị|Ỉ|Ĩ)#', '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#', '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#', '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#', '#(Đ)#');
        $replace = array('a', 'e', 'i', 'o', 'u', 'y', 'd', 'A', 'E', 'I', 'O', 'U', 'Y', 'D');
        $string = preg_replace($search, $replace, $string);
        return preg_replace('/[^a-z0-9-]+/', '-', strtolower(trim($string)));
    }
}

// 2. CHUẨN BỊ LINK MÃ QR GỐC (Chưa có số tiền)
$has_bank_info = !empty($event['bank_account']);
$qr_base_url = "";
$ma_giao_dich = "";

if ($has_bank_info) {
    $bank_id = $event['bank_name'] ?: 'MOMO';
    $acc_no = $event['bank_account'];
    $acc_name = $event['bank_owner'];

    // Tạo mã Tracking: UHCD + ID + Tên viết tắt (VD: UHCD15 XTMN)
    $noAccent = createSlugLocal($event['title']); 
    $words = explode('-', $noAccent);
    $acronym = '';
    foreach ($words as $w) { if(!empty($w)) $acronym .= strtoupper($w[0]); }
    
    $ma_giao_dich = "UHCD" . $event['id'] . " " . $acronym;
    
    $addInfo = rawurlencode($ma_giao_dich);
    $accName = rawurlencode($acc_name);
    
    // Lưu ý: Chưa gắn amount vào link, JS sẽ gắn sau
    $qr_base_url = "https://img.vietqr.io/image/{$bank_id}-{$acc_no}-compact2.png?addInfo={$addInfo}&accountName={$accName}";
}

$pageTitle = 'Quyên góp - ' . htmlspecialchars($event['title']);
include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
.donate-page { min-height: 100vh; padding: 60px 0; background: #f8f9fa; }
.donate-card { background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); padding: 30px; }
.amount-option { 
    border: 2px solid #e9ecef; 
    border-radius: 10px; 
    padding: 15px; 
    text-align: center; 
    cursor: pointer; 
    transition: all 0.2s;
    font-weight: bold;
    color: #555;
}
.amount-option:hover { border-color: #ffc107; background: #fffdf5; }
.amount-option.active { border-color: #d32f2f; background: #ffebee; color: #d32f2f; }
.qr-section {
    background: #f4fff6;
    border: 2px dashed #28a745;
    border-radius: 16px;
    padding: 30px 20px;
    text-align: center;
    height: 100%;
}
.qr-image-wrapper img {
    max-width: 100%;
    width: 250px;
    border-radius: 12px;
    border: 1px solid #ddd;
    padding: 10px;
    background: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    transition: 0.3s;
}
.loading-qr { opacity: 0.5; filter: blur(2px); }
.donor-info-section {
    background: #fff9e6;
    border: 2px solid #ffc107;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}
</style>

<div class="donate-page">
    <div class="container">
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h2 class="fw-bold" style="color: #333;">Ủng hộ dự án</h2>
                <h4 class="text-danger">"<?= htmlspecialchars($event['title']) ?>"</h4>
            </div>
        </div>

        <?php if ($has_bank_info): ?>
        
        <form method="POST" action="" id="donationForm">
            <div class="row g-4 align-items-stretch justify-content-center">
                
                <div class="col-lg-6">
                    <div class="donate-card h-100">
                        <!-- PHẦN THÔNG TIN NGƯỜI QUYÊN GÓP -->
                        <div class="donor-info-section">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-user-circle"></i> Thông tin của bạn
                            </h5>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold text-muted mb-2">Tên của bạn</label>
                                <input type="text" name="donor_name" id="donorName" 
                                       class="form-control" 
                                       placeholder="Nhập tên của bạn..."
                                       value="<?= isset($_SESSION['pending_donation']['donor_name']) ? htmlspecialchars($_SESSION['pending_donation']['donor_name']) : '' ?>">
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" 
                                       name="is_anonymous" id="isAnonymous" 
                                       <?= isset($_SESSION['pending_donation']['is_anonymous']) && $_SESSION['pending_donation']['is_anonymous'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isAnonymous">
                                    <i class="fas fa-user-secret"></i> Quyên góp ẩn danh
                                </label>
                            </div>
                            
                            <div class="form-group mb-0">
                                <label class="fw-bold text-muted mb-2">Lời chúc / Lời nhắn</label>
                                <textarea name="message" id="donorMessage" 
                                          class="form-control" rows="3" 
                                          placeholder="Gửi lời chúc của bạn đến chiến dịch..."><?= isset($_SESSION['pending_donation']['message']) ? htmlspecialchars($_SESSION['pending_donation']['message']) : '' ?></textarea>
                                <small class="text-muted">Lời chúc của bạn sẽ được hiển thị công khai</small>
                            </div>
                        </div>
                        
                        <h5 class="fw-bold mb-4">1. Chọn số tiền quyên góp</h5>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="amount-option active" data-value="50000">
                                    50.000đ
                                    <input type="radio" name="amount_preset" value="50000" class="d-none" checked>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="amount-option" data-value="100000">
                                    100.000đ
                                    <input type="radio" name="amount_preset" value="100000" class="d-none">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="amount-option" data-value="200000">
                                    200.000đ
                                    <input type="radio" name="amount_preset" value="200000" class="d-none">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="amount-option" data-value="500000">
                                    500.000đ
                                    <input type="radio" name="amount_preset" value="500000" class="d-none">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="amount-option" data-value="1000000">
                                    1.000.000đ
                                    <input type="radio" name="amount_preset" value="1000000" class="d-none">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="fw-bold text-muted mb-2">Hoặc nhập số tiền khác (VNĐ)</label>
                            <input type="text" id="amountCustom" name="amount" class="form-control form-control-lg" placeholder="Nhập số tiền..." style="font-weight:bold; color:#d32f2f;" value="50000">
                            <input type="hidden" name="amount_raw" id="amountRaw" value="50000">
                        </div>

                        <button type="submit" name="submit_donation" class="btn btn-warning btn-lg w-100 fw-bold">
                            <i class="fas fa-check-circle"></i> Xác nhận thông tin
                        </button>
                        
                        <div class="alert alert-info mt-3" style="font-size: 14px;">
                            <i class="fas fa-info-circle"></i> Sau khi xác nhận, mã QR sẽ hiển thị bên cạnh để bạn quét và thanh toán!
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="qr-section">
                        <?php if ($formSubmitted): ?>
                        <h5 class="fw-bold text-success mb-3">2. Quét mã để thanh toán</h5>
                        
                        <div class="qr-image-wrapper mb-3">
                            <img id="qrImage" src="<?= $qr_base_url ?>&amount=<?= $_SESSION['pending_donation']['amount'] ?>" alt="Mã QR">
                        </div>

                        <div class="text-center mb-3">
                            <span class="text-muted d-block" style="font-size: 13px;">Số tiền thanh toán:</span>
                            <h3 id="qrAmountText" class="text-danger fw-bold mb-0">
                                <?= number_format($_SESSION['pending_donation']['amount'], 0, ',', '.') ?> VNĐ
                            </h3>
                        </div>

                        <div style="background: #fff; padding: 12px; border-radius: 8px; border: 1px dashed #28a745; text-align: left;">
                            <div style="font-size: 13px; color: #555;">Nội dung chuyển khoản:</div>
                            <strong style="font-size: 18px; color: #d32f2f; user-select: all;"><?= $ma_giao_dich ?></strong>
                            <div style="font-size: 12px; color: #888; margin-top: 5px;">* Nội dung này đã được đính kèm sẵn trong mã QR.</div>
                        </div>
                        
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-user"></i> 
                            <strong>Người quyên góp:</strong> 
                            <?php if ($_SESSION['pending_donation']['is_anonymous']): ?>
                                Ẩn danh
                            <?php else: ?>
                                <?= htmlspecialchars($_SESSION['pending_donation']['donor_name'] ?: 'Chưa nhập tên') ?>
                            <?php endif; ?>
                            
                            <?php if (!empty($_SESSION['pending_donation']['message'])): ?>
                            <div class="mt-2" style="font-size: 13px;">
                                <i class="fas fa-comment"></i> 
                                <em>"<?= htmlspecialchars($_SESSION['pending_donation']['message']) ?>"</em>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 d-flex justify-content-center gap-2">
                            <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo" style="height: 25px;">
                            <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png" alt="ZaloPay" style="height: 25px; border-radius: 4px;">
                            <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/02/Logo-VNPay-V.png" alt="VNPay" style="height: 25px;">
                        </div>
                        
                        <?php 
                        // Xóa session sau khi hiển thị
                        unset($_SESSION['pending_donation']); 
                        ?>
                        
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-qrcode" style="font-size: 80px; color: #ccc;"></i>
                            <h5 class="mt-3 text-muted">Mã QR sẽ hiển thị sau khi bạn xác nhận thông tin</h5>
                            <p class="text-muted small">Vui lòng điền thông tin bên trái và nhấn "Xác nhận"</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </form>
        
        <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-md-8 text-center py-5 bg-white rounded-4 shadow-sm">
                <h1 style="font-size: 60px;">⚠️</h1>
                <h3 class="fw-bold mt-3">Chưa cấu hình thanh toán</h3>
                <p class="text-muted">Đại diện của chiến dịch này chưa cập nhật thông tin tài khoản ngân hàng nhận tiền. Xin vui lòng quay lại sau!</p>
                <a href="<?= BASE_URL ?>/events/events.php" class="btn btn-outline-secondary mt-3">Quay lại danh sách</a>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const amountCustom = document.getElementById('amountCustom');
    const amountRaw = document.getElementById('amountRaw');
    const amountOptions = document.querySelectorAll('.amount-option');
    const donorName = document.getElementById('donorName');
    const isAnonymous = document.getElementById('isAnonymous');

    // Disable tên khi chọn ẩn danh
    isAnonymous.addEventListener('change', function() {
        if (this.checked) {
            donorName.value = '';
            donorName.disabled = true;
            donorName.placeholder = 'Bạn đã chọn quyên góp ẩn danh';
        } else {
            donorName.disabled = false;
            donorName.placeholder = 'Nhập tên của bạn...';
        }
    });

    // Sự kiện khi bấm các ô số tiền cài sẵn
    amountOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Xóa active cũ
            amountOptions.forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            
            // Check radio button
            this.querySelector('input[type="radio"]').checked = true;
            
            // Cập nhật ô input
            const value = this.getAttribute('data-value');
            amountCustom.value = new Intl.NumberFormat('vi-VN').format(value);
            amountRaw.value = value;
        });
    });

    // Sự kiện khi nhập số tiền tùy chọn
    amountCustom.addEventListener('input', function(e) {
        // Chỉ cho nhập số và format dấu chấm
        let value = this.value.replace(/\D/g, '');
        if (value !== '') {
            this.value = new Intl.NumberFormat('vi-VN').format(value);
            amountRaw.value = value;
        } else {
            amountRaw.value = '0';
        }
        
        // Bỏ chọn các ô preset
        amountOptions.forEach(o => o.classList.remove('active'));
        document.querySelectorAll('input[name="amount_preset"]').forEach(r => r.checked = false);
    });
});
</script>

<?php include '../includes/footer.php'; ?>