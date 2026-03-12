<?php
require_once '../config/db.php';
$pageTitle = 'Điều khoản sử dụng - ' . SITE_NAME;
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4">Điều khoản sử dụng</h1>
            <p class="text-muted">Cập nhật lần cuối: <?= date('d/m/Y') ?></p>

            <hr class="my-4">

            <h3>1. Chấp nhận điều khoản</h3>
            <p>Bằng việc truy cập và sử dụng <?= SITE_NAME ?>, bạn đồng ý tuân thủ các điều khoản và điều kiện sau. Nếu bạn không đồng ý, vui lòng không sử dụng dịch vụ của chúng tôi.</p>

            <h3>2. Đăng ký tài khoản</h3>
            <p>Khi đăng ký tài khoản, bạn cam kết:</p>
            <ul>
                <li>Cung cấp thông tin chính xác và đầy đủ</li>
                <li>Bảo mật thông tin đăng nhập của bạn</li>
                <li>Chịu trách nhiệm về mọi hoạt động dưới tài khoản của bạn</li>
                <li>Thông báo ngay cho chúng tôi nếu phát hiện việc sử dụng trái phép</li>
            </ul>

            <h3>3. Quyên góp</h3>
            <p>Khi thực hiện quyên góp:</p>
            <ul>
                <li>Bạn tự nguyện đóng góp, không bị ép buộc</li>
                <li>Khoản quyên góp không được hoàn lại sau khi đã xác nhận</li>
                <li>Bạn đồng ý để chúng tôi công khai tên (trừ khi chọn ẩn danh)</li>
                <li>Bạn hiểu rằng tiền sẽ được chuyển đến người tổ chức sự kiện đã xác minh</li>
            </ul>

            <h3>4. Tạo sự kiện từ thiện</h3>
            <p>Nhà tổ chức sự kiện cam kết:</p>
            <ul>
                <li>Cung cấp thông tin chính xác về hoàn cảnh và mục đích</li>
                <li>Sử dụng tiền quyên góp đúng mục đích đã công bố</li>
                <li>Báo cáo minh bạch về việc sử dụng tiền</li>
                <li>Chịu trách nhiệm pháp lý nếu có gian lận</li>
            </ul>

            <h3>5. Nội dung người dùng</h3>
            <p>Khi đăng nội dung, bạn đảm bảo:</p>
            <ul>
                <li>Không vi phạm pháp luật Việt Nam</li>
                <li>Không xâm phạm quyền của bên thứ ba</li>
                <li>Không chứa nội dung khiêu dâm, bạo lực, kỳ thị</li>
                <li>Không spam hoặc quảng cáo trái phép</li>
            </ul>

            <h3>6. Quyền sở hữu trí tuệ</h3>
            <p>Mọi nội dung trên <?= SITE_NAME ?> (logo, thiết kế, mã nguồn) thuộc quyền sở hữu của chúng tôi. Bạn không được sao chép, sửa đổi hoặc phân phối mà không có sự đồng ý.</p>

            <h3>7. Miễn trừ trách nhiệm</h3>
            <p><?= SITE_NAME ?> không chịu trách nhiệm về:</p>
            <ul>
                <li>Tính chính xác của thông tin do người dùng cung cấp</li>
                <li>Tranh chấp giữa người quyên góp và nhà tổ chức</li>
                <li>Thiệt hại gián tiếp phát sinh từ việc sử dụng dịch vụ</li>
            </ul>

            <h3>8. Chấm dứt tài khoản</h3>
            <p>Chúng tôi có quyền tạm ngưng hoặc xóa tài khoản nếu:</p>
            <ul>
                <li>Bạn vi phạm điều khoản sử dụng</li>
                <li>Có dấu hiệu gian lận</li>
                <li>Theo yêu cầu của cơ quan có thẩm quyền</li>
            </ul>

            <h3>9. Thay đổi điều khoản</h3>
            <p>Chúng tôi có quyền cập nhật điều khoản bất cứ lúc nào. Việc tiếp tục sử dụng dịch vụ sau khi có thay đổi đồng nghĩa với việc bạn chấp nhận điều khoản mới.</p>

            <h3>10. Liên hệ</h3>
            <p>Nếu có thắc mắc về điều khoản, vui lòng liên hệ:</p>
            <ul>
                <li>Email: contact@charityevent.vn</li>
                <li>Điện thoại: 1900 1234</li>
            </ul>

            <hr class="my-4">
            
            <div class="text-center">
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">Quay lại trang chủ</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
