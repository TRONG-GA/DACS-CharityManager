<?php
require_once '../config/db.php';
$pageTitle = 'Sứ mệnh & Tầm nhìn - ' . SITE_NAME;
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="text-center mb-5">Sứ mệnh & Tầm nhìn</h1>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card h-100 border-danger">
                        <div class="card-body text-center p-5">
                            <i class="fas fa-bullseye fa-4x text-danger mb-4"></i>
                            <h2>Sứ mệnh</h2>
                            <p class="lead">Kết nối nguồn lực từ thiện một cách minh bạch và hiệu quả</p>
                            <hr>
                            <ul class="text-start">
                                <li class="mb-3">Đảm bảo 100% minh bạch trong mọi giao dịch quyên góp</li>
                                <li class="mb-3">Hỗ trợ nhanh chóng, kịp thời đến những hoàn cảnh khó khăn</li>
                                <li class="mb-3">Xây dựng niềm tin giữa nhà hảo tâm và người nhận</li>
                                <li class="mb-3">Lan tỏa tinh thần yêu thương trong cộng đồng</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border-primary">
                        <div class="card-body text-center p-5">
                            <i class="fas fa-eye fa-4x text-primary mb-4"></i>
                            <h2>Tầm nhìn</h2>
                            <p class="lead">Trở thành nền tảng từ thiện hàng đầu Việt Nam</p>
                            <hr>
                            <ul class="text-start">
                                <li class="mb-3">Phủ sóng toàn quốc với 63 tỉnh thành</li>
                                <li class="mb-3">Cộng đồng hơn 1 triệu người tham gia</li>
                                <li class="mb-3">Công nghệ hiện đại, dễ sử dụng</li>
                                <li class="mb-3">Đối tác chiến lược của chính phủ và tổ chức quốc tế</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="text-center mb-4">Giá trị cốt lõi</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center p-4 bg-light rounded">
                        <i class="fas fa-heart fa-3x text-danger mb-3"></i>
                        <h4>Yêu thương</h4>
                        <p class="text-muted">Đặt tình yêu thương lên hàng đầu, quan tâm đến từng hoàn cảnh khó khăn</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4 bg-light rounded">
                        <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                        <h4>Minh bạch</h4>
                        <p class="text-muted">Công khai, rõ ràng trong mọi hoạt động và giao dịch tài chính</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4 bg-light rounded">
                        <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                        <h4>Trách nhiệm</h4>
                        <p class="text-muted">Cam kết hoàn thành sứ mệnh với tinh thần trách nhiệm cao nhất</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
