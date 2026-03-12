<?php
require_once '../config/db.php';

$eventId = $_GET['event_id'] ?? 0;

// Get event info
$stmt = $pdo->prepare("SELECT id, title, slug, target_amount, current_amount FROM events WHERE id = ? AND status = 'approved'");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: ' . BASE_URL . '/events/events.php');
    exit;
}

$pageTitle = 'Quyên góp - ' . sanitize($event['title']);
$flash = getFlashMessage();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
.donate-page { min-height: 100vh; padding: 60px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.donate-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); padding: 40px; max-width: 600px; margin: 0 auto; }
.amount-option { 
    border: 2px solid #ddd; 
    border-radius: 10px; 
    padding: 20px; 
    text-align: center; 
    cursor: pointer; 
    transition: all 0.3s;
}
.amount-option:hover { border-color: #dc3545; background: #fff5f5; }
.amount-option.active { border-color: #dc3545; background: #dc3545; color: white; }
.amount-option input[type="radio"] { display: none; }
.payment-method { 
    border: 2px solid #ddd; 
    border-radius: 10px; 
    padding: 15px; 
    cursor: pointer;
    transition: all 0.3s;
}
.payment-method:hover { border-color: #0d6efd; }
.payment-method.active { border-color: #0d6efd; background: #e7f3ff; }
.payment-method input[type="radio"] { display: none; }
</style>

<div class="donate-page">
    <div class="container">
        <div class="donate-card">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="fas fa-hand-holding-heart fa-3x text-danger"></i>
                </div>
                <h2>Quyên góp</h2>
                <h5 class="text-muted"><?= sanitize($event['title']) ?></h5>
            </div>
            
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form action="<?= BASE_URL ?>/events/process_donation.php" method="POST" id="donateForm">
                <?= csrfField() ?>
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                
                <!-- Amount Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Chọn số tiền quyên góp:</label>
                    <div class="row g-3">
                        <?php
                        $amounts = [50000, 100000, 200000, 500000, 1000000, 2000000];
                        foreach ($amounts as $amount):
                        ?>
                        <div class="col-6 col-md-4">
                            <label class="amount-option">
                                <input type="radio" name="amount_preset" value="<?= $amount ?>">
                                <div class="fw-bold"><?= formatMoney($amount) ?></div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Custom Amount -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Hoặc nhập số tiền khác:</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">
                            <i class="fas fa-donate text-danger"></i>
                        </span>
                        <input type="number" 
                               name="amount_custom" 
                               id="amountCustom"
                               class="form-control" 
                               placeholder="Nhập số tiền (VNĐ)"
                               min="10000"
                               step="1000">
                        <span class="input-group-text">VNĐ</span>
                    </div>
                    <small class="text-muted">Số tiền tối thiểu: 10,000 VNĐ</small>
                </div>
                
                <!-- Donor Info -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-user text-danger me-2"></i>Họ và tên
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="donor_name" 
                           class="form-control" 
                           placeholder="Nguyễn Văn A"
                           required
                           value="<?= isLoggedIn() ? sanitize($_SESSION['fullname']) : '' ?>">
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="fas fa-envelope text-danger me-2"></i>Email
                        </label>
                        <input type="email" 
                               name="donor_email" 
                               class="form-control" 
                               placeholder="email@example.com"
                               value="<?= isLoggedIn() ? sanitize($_SESSION['email']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="fas fa-phone text-danger me-2"></i>Số điện thoại
                        </label>
                        <input type="tel" 
                               name="donor_phone" 
                               class="form-control" 
                               placeholder="0987654321"
                               pattern="[0-9]{10}">
                    </div>
                </div>
                
                <!-- Message -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-comment text-danger me-2"></i>Lời nhắn (không bắt buộc)
                    </label>
                    <textarea name="message" 
                              class="form-control" 
                              rows="3" 
                              placeholder="Gửi lời động viên..."></textarea>
                </div>
                
                <!-- Payment Method -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Phương thức thanh toán:</label>
                    <div class="d-grid gap-2">
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="bank_transfer" checked>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-university fa-2x text-primary me-3"></i>
                                <div>
                                    <strong>Chuyển khoản ngân hàng</strong>
                                    <div class="small text-muted">Quét QR hoặc chuyển thủ công</div>
                                </div>
                            </div>
                        </label>
                        
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="momo">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-wallet fa-2x text-danger me-3"></i>
                                <div>
                                    <strong>Ví MoMo</strong>
                                    <div class="small text-muted">Thanh toán qua ví điện tử</div>
                                </div>
                            </div>
                        </label>
                        
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="vnpay">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-credit-card fa-2x text-info me-3"></i>
                                <div>
                                    <strong>VNPay</strong>
                                    <div class="small text-muted">Thanh toán qua cổng VNPay</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Anonymous -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_anonymous" id="isAnonymous">
                        <label class="form-check-label" for="isAnonymous">
                            Quyên góp ẩn danh (không hiển thị tên)
                        </label>
                    </div>
                </div>
                
                <!-- Submit -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="fas fa-heart me-2"></i>Xác nhận quyên góp
                    </button>
                    <a href="<?= BASE_URL ?>/events/event_detail.php?slug=<?= $event['slug'] ?>" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Amount selection logic
document.querySelectorAll('.amount-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.amount-option').forEach(o => o.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input[type="radio"]').checked = true;
        document.getElementById('amountCustom').value = '';
    });
});

// Custom amount clears preset
document.getElementById('amountCustom').addEventListener('input', function() {
    if (this.value) {
        document.querySelectorAll('.amount-option').forEach(o => o.classList.remove('active'));
        document.querySelectorAll('input[name="amount_preset"]').forEach(r => r.checked = false);
    }
});

// Payment method selection
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input[type="radio"]').checked = true;
    });
});

// Form validation
document.getElementById('donateForm').addEventListener('submit', function(e) {
    const preset = document.querySelector('input[name="amount_preset"]:checked');
    const custom = document.getElementById('amountCustom').value;
    
    if (!preset && !custom) {
        e.preventDefault();
        alert('Vui lòng chọn hoặc nhập số tiền quyên góp!');
        return false;
    }
    
    if (custom && parseInt(custom) < 10000) {
        e.preventDefault();
        alert('Số tiền quyên góp tối thiểu là 10,000 VNĐ!');
        return false;
    }
});
</script>

<?php include '../includes/footer.php'; ?>
