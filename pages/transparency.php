<?php
require_once '../config/db.php';
$pageTitle = 'Báo cáo minh bạch - ' . SITE_NAME;

$stats = getStatistics();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <h1 class="text-center mb-2">Báo cáo minh bạch</h1>
    <p class="text-center text-muted mb-5">Công khai tài chính và hoạt động</p>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-center p-4">
                <h2 class="text-danger"><?= formatMoney($stats['total_donations']) ?></h2>
                <p class="text-muted mb-0">Tổng quyên góp</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-4">
                <h2 class="text-success"><?= $stats['total_events'] ?></h2>
                <p class="text-muted mb-0">Sự kiện</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-4">
                <h2 class="text-primary"><?= $stats['total_donors'] ?></h2>
                <p class="text-muted mb-0">Nhà hảo tâm</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-4">
                <h2 class="text-warning"><?= $stats['total_volunteers'] ?></h2>
                <p class="text-muted mb-0">Tình nguyện viên</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h3>Phân bổ ngân sách</h3>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Hỗ trợ trực tiếp</span>
                            <strong>90%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: 90%">90%</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Vận hành hệ thống</span>
                            <strong>8%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-warning" style="width: 8%">8%</div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Marketing & Truyền thông</span>
                            <strong>2%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-info" style="width: 2%">2%</div>
                        </div>
                    </div>
                </div>
            </div>

            <h3>Cam kết của chúng tôi</h3>
            <div class="card">
                <div class="card-body">
                    <ul>
                        <li class="mb-2">✅ Công khai 100% thông tin tài chính</li>
                        <li class="mb-2">✅ Báo cáo định kỳ hàng tháng</li>
                        <li class="mb-2">✅ Kiểm toán độc lập hàng năm</li>
                        <li class="mb-2">✅ Minh bạch nguồn gốc và sử dụng tiền</li>
                        <li class="mb-2">✅ Truy vết được từng khoản quyên góp</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
