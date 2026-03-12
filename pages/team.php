<?php
require_once '../config/db.php';
$pageTitle = 'Đội ngũ - ' . SITE_NAME;
include '../includes/header.php';
include '../includes/navbar.php';
?>



<div class="container my-5">
    <h1 class="text-center mb-2">Đội ngũ của chúng tôi</h1>
    <p class="text-center text-muted mb-5">Những người đứng sau <?= SITE_NAME ?></p>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="team-member">
                <img src="https://ui-avatars.com/api/?name=Nguyen+Van+A&size=150&background=dc3545&color=fff" alt="CEO">
                <h5>Nguyễn Văn A</h5>
                <p class="text-danger mb-2">CEO & Founder</p>
                <p class="text-muted small">15 năm kinh nghiệm trong lĩnh vực từ thiện xã hội</p>
                <div>
                    <a href="#" class="text-primary me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-info me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-danger"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="team-member">
                <img src="https://ui-avatars.com/api/?name=Tran+Thi+B&size=150&background=28a745&color=fff" alt="COO">
                <h5>Trần Thị B</h5>
                <p class="text-danger mb-2">COO</p>
                <p class="text-muted small">Chuyên gia quản trị và vận hành phi lợi nhuận</p>
                <div>
                    <a href="#" class="text-primary me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-info me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-danger"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="team-member">
                <img src="https://ui-avatars.com/api/?name=Le+Van+C&size=150&background=0d6efd&color=fff" alt="CTO">
                <h5>Lê Văn C</h5>
                <p class="text-danger mb-2">CTO</p>
                <p class="text-muted small">Kỹ sư phần mềm với 10 năm kinh nghiệm</p>
                <div>
                    <a href="#" class="text-primary me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-info me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-danger"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="team-member">
                <img src="https://ui-avatars.com/api/?name=Pham+Thi+D&size=150&background=ffc107&color=000" alt="Marketing">
                <h5>Phạm Thị D</h5>
                <p class="text-danger mb-2">Marketing Director</p>
                <p class="text-muted small">Chuyên gia marketing và truyền thông xã hội</p>
                <div>
                    <a href="#" class="text-primary me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-info me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-danger"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="team-member">
                <img src="https://ui-avatars.com/api/?name=Hoang+Van+E&size=150&background=6f42c1&color=fff" alt="Finance">
                <h5>Hoàng Văn E</h5>
                <p class="text-danger mb-2">Finance Manager</p>
                <p class="text-muted small">Chuyên viên tài chính và kế toán</p>
                <div>
                    <a href="#" class="text-primary me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-info me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-danger"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="team-member">
                <img src="https://ui-avatars.com/api/?name=Vo+Thi+F&size=150&background=e83e8c&color=fff" alt="Community">
                <h5>Võ Thị F</h5>
                <p class="text-danger mb-2">Community Manager</p>
                <p class="text-muted small">Quản lý cộng đồng và quan hệ đối tác</p>
                <div>
                    <a href="#" class="text-primary me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-info me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-danger"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 bg-light p-5 rounded">
        <h3>Tham gia cùng chúng tôi</h3>
        <p class="text-muted mb-4">Chúng tôi luôn tìm kiếm những người tài năng và nhiệt huyết</p>
        <a href="<?= BASE_URL ?>/pages/contact.php" class="btn btn-danger btn-lg">Liên hệ ngay</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
