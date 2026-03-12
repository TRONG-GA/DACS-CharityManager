<?php
require_once '../config/db.php';
$pageTitle = 'Câu hỏi thường gặp - ' . SITE_NAME;
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <h1 class="text-center mb-5">Câu hỏi thường gặp</h1>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="accordion" id="faqAccordion">
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            <?= SITE_NAME ?> là gì?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <?= SITE_NAME ?> là nền tảng kết nối từ thiện trực tuyến, giúp kết nối những người có nhu cầu hỗ trợ với các nhà hảo tâm và tình nguyện viên trên khắp cả nước.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Làm sao để quyên góp?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <ol>
                                <li>Truy cập trang Sự kiện</li>
                                <li>Chọn sự kiện bạn muốn ủng hộ</li>
                                <li>Click "Quyên góp ngay"</li>
                                <li>Điền thông tin và số tiền</li>
                                <li>Chọn phương thức thanh toán và hoàn tất</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Tiền quyên góp có được sử dụng đúng mục đích không?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Chúng tôi cam kết 100% minh bạch. Mọi khoản quyên góp đều được theo dõi và công khai. Nhà tổ chức sự kiện phải báo cáo chi tiết về việc sử dụng tiền.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Làm sao để trở thành tình nguyện viên?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Bạn có thể đăng ký làm tình nguyện viên cho các sự kiện đang cần hỗ trợ. Vào trang chi tiết sự kiện và click "Đăng ký tình nguyện", sau đó điền thông tin của bạn.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            Tôi có thể tạo sự kiện từ thiện không?
                        </button>
                    </h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Có! Bạn cần đăng ký tài khoản Nhà hảo tâm (Benefactor), cung cấp thông tin định danh (KYC) để xác minh. Sau khi được duyệt, bạn có thể tạo và quản lý các sự kiện từ thiện.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                            Phí dịch vụ của nền tảng là bao nhiêu?
                        </button>
                    </h2>
                    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Hiện tại, <?= SITE_NAME ?> hoàn toàn MIỄN PHÍ cho tất cả người dùng. Chúng tôi không thu bất kỳ khoản phí nào từ các khoản quyên góp.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                            Tôi có thể quyên góp ẩn danh không?
                        </button>
                    </h2>
                    <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Có, bạn có thể chọn tùy chọn "Quyên góp ẩn danh" khi điền form quyên góp. Tên của bạn sẽ không hiển thị công khai trong danh sách nhà hảo tâm.
                        </div>
                    </div>
                </div>

            </div>

            <div class="text-center mt-5">
                <p class="text-muted">Không tìm thấy câu trả lời? <a href="<?= BASE_URL ?>/pages/contact.php">Liên hệ với chúng tôi</a></p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
