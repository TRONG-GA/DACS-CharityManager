<?php
require_once '../config/db.php';
require_once '../includes/security.php';

$eventId = $_GET['event_id'] ?? 0;

// XỬ LÝ FORM SUBMIT
$formSubmitted = false;
$pendingDonationId = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_donation'])) {
    $donor_name = isset($_POST['donor_name']) && !empty(trim($_POST['donor_name'])) ? sanitize($_POST['donor_name']) : 'Mạnh thường quân';
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $message = isset($_POST['message']) ? sanitize($_POST['message']) : '';
    $amount = str_replace(['.', ','], '', $_POST['amount'] ?? '0');
    
    if ($is_anonymous) {
        $donor_name = 'Nhà hảo tâm ẩn danh';
    }
    
    // LẤY USER_ID NẾU ĐANG ĐĂNG NHẬP
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    // TẠO LUÔN RECORD PENDING TRONG DATABASE VỚI USER_ID
    $stmtInsert = $pdo->prepare("
        INSERT INTO donations (
            event_id, user_id, donor_name, amount, message, 
            payment_method, status, is_anonymous, show_amount
        ) VALUES (?, ?, ?, ?, ?, 'bank_transfer', 'pending', ?, 1)
    ");
    $stmtInsert->execute([
        $eventId, 
        $user_id,  // THÊM USER_ID VÀO ĐÂY
        $donor_name, 
        $amount, 
        $message, 
        $is_anonymous
    ]);
    $pendingDonationId = $pdo->lastInsertId();

    $_SESSION['pending_donation'] = [
        'donor_name' => $donor_name,
        'is_anonymous' => $is_anonymous,
        'message' => $message,
        'amount' => $amount,
        'event_id' => $eventId,
        'donation_id' => $pendingDonationId,
        'user_id' => $user_id  // LƯU LUÔN USER_ID VÀO SESSION
    ];
    
    $formSubmitted = true;
}

// LẤY THÔNG TIN SỰ KIỆN VÀ NGÂN HÀNG
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

// TẠO LINK MÃ QR THEO ID CỦA ĐƠN QUYÊN GÓP
$has_bank_info = !empty($event['bank_account']);
$qr_base_url = "";
$ma_giao_dich = "";

if ($has_bank_info && $formSubmitted) {
    $bank_id = $event['bank_name'] ?: 'MOMO';
    $acc_no = $event['bank_account'];
    $acc_name = $event['bank_owner'];

    // CHÚ Ý: MÃ BÂY GIỜ SẼ LÀ ID CỦA BẢNG DONATIONS
    $ma_giao_dich = "UHCD" . $_SESSION['pending_donation']['donation_id'];
    
    $addInfo = rawurlencode($ma_giao_dich);
    $accName = rawurlencode($acc_name);
    $amountQR = $_SESSION['pending_donation']['amount'];
    
    $qr_base_url = "https://img.vietqr.io/image/{$bank_id}-{$acc_no}-compact2.png?addInfo={$addInfo}&accountName={$accName}&amount={$amountQR}";
}

$pageTitle = 'Quyên góp - ' . htmlspecialchars($event['title']);
include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
.donate-page { min-height: 100vh; padding: 60px 0; background: #f8f9fa; }
.donate-card { background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); padding: 30px; }
.amount-option { border: 2px solid #e9ecef; border-radius: 10px; padding: 15px; text-align: center; cursor: pointer; transition: all 0.2s; font-weight: bold; color: #555;}
.amount-option:hover { border-color: #ffc107; background: #fffdf5; }
.amount-option.active { border-color: #d32f2f; background: #ffebee; color: #d32f2f; }
.qr-section { background: #f4fff6; border: 2px dashed #28a745; border-radius: 16px; padding: 30px 20px; text-align: center; height: 100%;}
.qr-image-wrapper img { max-width: 100%; width: 250px; border-radius: 12px; border: 1px solid #ddd; padding: 10px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.donor-info-section { background: #fff9e6; border: 2px solid #ffc107; border-radius: 12px; padding: 20px; margin-bottom: 20px;}
.polling-status { display: inline-block; margin-top: 15px; padding: 8px 15px; background: #e8f5e9; color: #28a745; border-radius: 20px; font-size: 14px; font-weight: 500; animation: pulse 1.5s infinite;}
@keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

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
                        <div class="donor-info-section">
                            <h5 class="fw-bold mb-3"><i class="fas fa-user-circle"></i> Thông tin của bạn</h5>
                            <div class="form-group mb-3">
                                <label class="fw-bold text-muted mb-2">Tên của bạn</label>
                                <input type="text" name="donor_name" id="donorName" class="form-control" placeholder="Nhập tên của bạn...">
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_anonymous" id="isAnonymous">
                                <label class="form-check-label" for="isAnonymous"><i class="fas fa-user-secret"></i> Quyên góp ẩn danh</label>
                            </div>
                            <div class="form-group mb-0">
                                <label class="fw-bold text-muted mb-2">Lời chúc / Lời nhắn</label>
                                <textarea name="message" id="donorMessage" class="form-control" rows="3" placeholder="Gửi lời chúc của bạn đến chiến dịch..."></textarea>
                            </div>
                        </div>
                        
                        <h5 class="fw-bold mb-4">1. Chọn số tiền quyên góp</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-4"><div class="amount-option active" data-value="50000">50.000đ<input type="radio" name="amount_preset" value="50000" class="d-none" checked></div></div>
                            <div class="col-4"><div class="amount-option" data-value="100000">100.000đ<input type="radio" name="amount_preset" value="100000" class="d-none"></div></div>
                            <div class="col-4"><div class="amount-option" data-value="200000">200.000đ<input type="radio" name="amount_preset" value="200000" class="d-none"></div></div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="fw-bold text-muted mb-2">Hoặc nhập số tiền khác (VNĐ)</label>
                            <input type="text" id="amountCustom" name="amount" class="form-control form-control-lg" placeholder="Nhập số tiền..." style="font-weight:bold; color:#d32f2f;" value="50000">
                        </div>

                        <button type="submit" name="submit_donation" class="btn btn-warning btn-lg w-100 fw-bold">
                            <i class="fas fa-check-circle"></i> Xác nhận lấy mã QR
                        </button>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="qr-section">
                        <?php if ($formSubmitted): ?>
                        <h5 class="fw-bold text-success mb-3">2. Quét mã để thanh toán</h5>
                        <div class="qr-image-wrapper mb-3">
                            <img id="qrImage" src="<?= $qr_base_url ?>" alt="Mã QR">
                        </div>
                        <div class="text-center mb-3">
                            <span class="text-muted d-block" style="font-size: 13px;">Số tiền thanh toán:</span>
                            <h3 class="text-danger fw-bold mb-0">
                                <?= number_format($_SESSION['pending_donation']['amount'], 0, ',', '.') ?> VNĐ
                            </h3>
                        </div>
                        <div style="background: #fff; padding: 12px; border-radius: 8px; border: 1px dashed #28a745; text-align: left;">
                            <div style="font-size: 13px; color: #555;">Nội dung chuyển khoản (bắt buộc):</div>
                            <strong style="font-size: 18px; color: #d32f2f; user-select: all;"><?= $ma_giao_dich ?></strong>
                        </div>
                        <div class="polling-status">
                            <i class="fas fa-spinner fa-spin me-2"></i>Hệ thống đang chờ tiền về...
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-qrcode" style="font-size: 80px; color: #ccc;"></i>
                            <h5 class="mt-3 text-muted">Mã QR sẽ hiển thị tại đây</h5>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
        <?php else: ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <h3 class="fw-bold mt-3 text-danger">Chiến dịch chưa cập nhật tài khoản ngân hàng</h3>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const amountCustom = document.getElementById('amountCustom');
    const amountOptions = document.querySelectorAll('.amount-option');
    const donorName = document.getElementById('donorName');
    const isAnonymous = document.getElementById('isAnonymous');

    if(isAnonymous) {
        isAnonymous.addEventListener('change', function() {
            if (this.checked) {
                donorName.value = '';
                donorName.disabled = true;
                donorName.placeholder = 'Nhà hảo tâm ẩn danh';
            } else {
                donorName.disabled = false;
                donorName.placeholder = 'Nhập tên của bạn...';
            }
        });
    }

    amountOptions.forEach(option => {
        option.addEventListener('click', function() {
            amountOptions.forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input[type="radio"]').checked = true;
            amountCustom.value = new Intl.NumberFormat('vi-VN').format(this.getAttribute('data-value'));
        });
    });

    if(amountCustom) {
        amountCustom.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value !== '') this.value = new Intl.NumberFormat('vi-VN').format(value);
            amountOptions.forEach(o => o.classList.remove('active'));
        });
    }

    <?php if ($formSubmitted): ?>
    // AJAX Polling: Hỏi thăm hệ thống xem có ai chuyển tiền vào đơn hàng ID này chưa
    let pendingDonationId = <?= (int)$_SESSION['pending_donation']['donation_id'] ?>;
    
    let checkInterval = setInterval(function() {
        fetch('check_payment.php?donation_id=' + pendingDonationId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                clearInterval(checkInterval);
                confetti({ particleCount: 150, spread: 100, origin: { y: 0.6 }, zIndex: 9999 });
                
                Swal.fire({
                    title: 'Nhận tiền thành công!',
                    html: `Cảm ơn tấm lòng của bạn.<br>Tên của bạn và lời chúc đã được lưu!`,
                    icon: 'success',
                    confirmButtonText: 'Xem chi tiết',
                    confirmButtonColor: '#d32f2f',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'event_detail.php?slug=<?= htmlspecialchars($event['slug']) ?>';
                    }
                });
            }
        })
        .catch(err => console.error(err));
    }, 3000); // 3 giây kiểm tra 1 lần
    <?php endif; ?>
});
</script>

<?php include '../includes/footer.php'; ?>
