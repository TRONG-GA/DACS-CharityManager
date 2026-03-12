<?php
require_once '../config/db.php';
$pageTitle = 'Về chúng tôi - ' . SITE_NAME;
include '../includes/header.php';
include '../includes/navbar.php';

$stats = getStatistics();
?>



<div class="about-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 mb-4">Về <?= SITE_NAME ?></h1>
                <p class="lead">Nền tảng kết nối từ thiện, lan tỏa yêu thương đến cộng đồng</p>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stats-box">
                <div class="number"><?= $stats['total_events'] ?></div>
                <div class="text-muted">Sự kiện</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <div class="number"><?= formatMoney($stats['total_donations']) ?></div>
                <div class="text-muted">Đã quyên góp</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <div class="number"><?= $stats['total_donors'] ?></div>
                <div class="text-muted">Nhà hảo tâm</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <div class="number"><?= $stats['total_volunteers'] ?></div>
                <div class="text-muted">Tình nguyện viên</div>
            </div>
        </div>
    </div>

    <!-- Story -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <h2 class="text-center mb-4">Câu chuyện của chúng tôi</h2>
            <p class="lead text-center text-muted mb-4">
                <?= SITE_NAME ?> được thành lập với sứ mệnh kết nối những tấm lòng hảo tâm với những hoàn cảnh khó khăn cần giúp đỡ.
            </p>
            <p>
                Khởi đầu từ năm 2020, chúng tôi bắt đầu với những hoạt động từ thiện nhỏ lẻ. Qua 5 năm phát triển, 
                <?= SITE_NAME ?> đã trở thành một trong những nền tảng từ thiện uy tín, kết nối hàng nghìn nhà hảo tâm 
                và đã hỗ trợ hàng chục nghìn người gặp khó khăn trên khắp cả nước.
            </p>
            <p>
                Chúng tôi tin rằng mỗi đóng góp, dù nhỏ hay lớn, đều có giá trị. Và khi những tấm lòng cùng chung tay, 
                chúng ta có thể tạo nên những thay đổi tích cực cho cộng đồng.
            </p>
        </div>
    </div>

    <!-- Mission & Vision -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="mission-card">
                <div class="text-center mb-3">
                    <i class="fas fa-bullseye fa-3x text-danger"></i>
                </div>
                <h3 class="text-center mb-3">Sứ mệnh</h3>
                <p>
                    Kết nối nguồn lực từ thiện một cách minh bạch và hiệu quả, đảm bảo mỗi đồng quyên góp 
                    đều đến đúng người, đúng nơi cần được giúp đỡ.
                </p>
                <ul>
                    <li>Minh bạch 100% trong mọi hoạt động</li>
                    <li>Hỗ trợ nhanh chóng, kịp thời</li>
                    <li>Lan tỏa yêu thương đến cộng đồng</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mission-card">
                <div class="text-center mb-3">
                    <i class="fas fa-eye fa-3x text-primary"></i>
                </div>
                <h3 class="text-center mb-3">Tầm nhìn</h3>
                <p>
                    Trở thành nền tảng từ thiện hàng đầu Việt Nam, nơi mọi người có thể dễ dàng tham gia 
                    các hoạt động từ thiện và tạo ra những thay đổi tích cực cho xã hội.
                </p>
                <ul>
                    <li>Phủ sóng toàn quốc</li>
                    <li>Công nghệ hiện đại</li>
                    <li>Cộng đồng hàng triệu người</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Values -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Giá trị cốt lõi</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-heart fa-3x text-danger mb-3"></i>
                        <h4>Yêu thương</h4>
                        <p class="text-muted">Đặt tình yêu thương lên hàng đầu trong mọi hoạt động</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                        <h4>Minh bạch</h4>
                        <p class="text-muted">Công khai, minh bạch trong mọi giao dịch</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                        <h4>Trách nhiệm</h4>
                        <p class="text-muted">Cam kết hoàn thành sứ mệnh với tinh thần trách nhiệm cao</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="text-center bg-light p-5 rounded">
        <h3>Cùng chúng tôi lan tỏa yêu thương</h3>
        <p class="text-muted mb-4">Tham gia ngay để trở thành một phần của cộng đồng từ thiện</p>
        <a href="<?= BASE_URL ?>/events/events.php" class="btn btn-danger btn-lg me-2">
            <i class="fas fa-hand-holding-heart me-2"></i>Quyên góp ngay
        </a>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline-primary btn-lg">
            <i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
