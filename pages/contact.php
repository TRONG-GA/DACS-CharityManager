<?php
require_once '../config/db.php';
$pageTitle = 'Liên hệ - ' . SITE_NAME;

$alertMessage = null;
if (isset($_GET['success'])) {
    $alertMessage = ['type' => 'success', 'message' => urldecode($_GET['success'])];
} elseif (isset($_GET['error'])) {
    $alertMessage = ['type' => 'error', 'message' => urldecode($_GET['error'])];
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="text-center mb-4">Liên hệ với chúng tôi</h1>
            <p class="text-center text-muted mb-5">
                Có câu hỏi hoặc cần hỗ trợ? Hãy gửi tin nhắn cho chúng tôi!
            </p>

            <?php if ($alertMessage): ?>
            <div class="alert alert-<?= $alertMessage['type'] == 'error' ? 'danger' : $alertMessage['type'] ?> alert-dismissible fade show">
                <?= $alertMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-phone fa-2x text-danger mb-3"></i>
                        <h5>Điện thoại</h5>
                        <p class="text-muted">1900 1234</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-envelope fa-2x text-danger mb-3"></i>
                        <h5>Email</h5>
                        <p class="text-muted">contact@charityevent.vn</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-map-marker-alt fa-2x text-danger mb-3"></i>
                        <h5>Địa chỉ</h5>
                        <p class="text-muted">Hà Nội, Việt Nam</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="<?= BASE_URL ?>/pages/process_contact.php" method="POST">
                        <?= csrfField() ?>

                        <div class="mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= isLoggedIn() ? sanitize($_SESSION['fullname']) : '' ?>">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?= isLoggedIn() ? sanitize($_SESSION['email']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control" pattern="[0-9]{10}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Chủ đề <span class="text-danger">*</span></label>
                            <select name="subject" class="form-select" required>
                                <option value="">Chọn chủ đề</option>
                                <option value="general">Câu hỏi chung</option>
                                <option value="donate">Quyên góp</option>
                                <option value="volunteer">Tình nguyện</option>
                                <option value="partner">Hợp tác</option>
                                <option value="report">Báo cáo sự cố</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="6" required
                                      placeholder="Nhập nội dung liên hệ của bạn..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Gửi tin nhắn
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
