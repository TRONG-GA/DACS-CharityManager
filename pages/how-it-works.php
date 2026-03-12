<?php
require_once '../config/db.php';
$pageTitle = 'Cách thức hoạt động - ' . SITE_NAME;
include '../includes/header.php';
include '../includes/navbar.php';
?>



<div class="container my-5">
    <h1 class="text-center mb-2">Cách thức hoạt động</h1>
    <p class="text-center text-muted mb-5">Quy trình đơn giản để bạn tham gia từ thiện</p>

    <h2 class="text-center mb-4">Dành cho người quyên góp</h2>
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="step-card">
                <div class="step-number">1</div>
                <h5>Tìm sự kiện</h5>
                <p class="text-muted">Duyệt các sự kiện từ thiện đang cần hỗ trợ</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="step-card">
                <div class="step-number">2</div>
                <h5>Chọn số tiền</h5>
                <p class="text-muted">Quyết định số tiền bạn muốn đóng góp</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="step-card">
                <div class="step-number">3</div>
                <h5>Thanh toán</h5>
                <p class="text-muted">Chọn phương thức và hoàn tất thanh toán</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="step-card">
                <div class="step-number">4</div>
                <h5>Nhận xác nhận</h5>
                <p class="text-muted">Nhận email xác nhận và theo dõi sự kiện</p>
            </div>
        </div>
    </div>

    <h2 class="text-center mb-4">Dành cho tình nguyện viên</h2>
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="step-card">
                <div class="step-number">1</div>
                <h5>Đăng ký</h5>
                <p class="text-muted">Tạo tài khoản và hoàn thiện hồ sơ</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="step-card">
                <div class="step-number">2</div>
                <h5>Chọn sự kiện</h5>
                <p class="text-muted">Tìm sự kiện phù hợp với bạn</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="step-card">
                <div class="step-number">3</div>
                <h5>Đăng ký tham gia</h5>
                <p class="text-muted">Điền form và chờ phê duyệt</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="step-card">
                <div class="step-number">4</div>
                <h5>Tham gia</h5>
                <p class="text-muted">Nhận hướng dẫn và tham gia hoạt động</p>
            </div>
        </div>
    </div>

    <h2 class="text-center mb-4">Dành cho nhà tổ chức</h2>
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="step-card">
                <div class="step-number">1</div>
                <h5>Đăng ký tài khoản</h5>
                <p class="text-muted">Tạo tài khoản Benefactor và xác minh danh tính (KYC)</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="step-card">
                <div class="step-number">2</div>
                <h5>Tạo sự kiện</h5>
                <p class="text-muted">Tạo sự kiện với đầy đủ thông tin và chứng từ</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="step-card">
                <div class="step-number">3</div>
                <h5>Quản lý & Báo cáo</h5>
                <p class="text-muted">Theo dõi quyên góp và báo cáo minh bạch</p>
            </div>
        </div>
    </div>

    <div class="text-center bg-light p-5 rounded">
        <h3>Sẵn sàng bắt đầu?</h3>
        <p class="text-muted mb-4">Tham gia cùng chúng tôi ngay hôm nay</p>
        <a href="<?= BASE_URL ?>/events/events.php" class="btn btn-danger btn-lg me-2">Xem sự kiện</a>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline-primary btn-lg">Đăng ký</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
