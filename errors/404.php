<?php
require_once __DIR__ . '/../config/db.php';
http_response_code(404);
$pageTitle = '404 - Không tìm thấy trang';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="error-page">
                <h1 class="display-1 fw-bold text-danger">404</h1>
                <h2 class="mb-4">Không tìm thấy trang</h2>
                <p class="text-muted mb-4">
                    Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="<?= BASE_URL ?>/index.php" class="btn btn-danger">
                        <i class="fas fa-home me-2"></i>Về trang chủ
                    </a>
                    <a href="<?= BASE_URL ?>/events.php" class="btn btn-outline-danger">
                        <i class="fas fa-calendar me-2"></i>Xem sự kiện
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
