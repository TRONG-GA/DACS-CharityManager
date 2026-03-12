<?php
require_once '../config/db.php';
$pageTitle = 'Chính sách bảo mật - ' . SITE_NAME;
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4">Chính sách bảo mật</h1>
            <p class="text-muted">Cập nhật lần cuối: <?= date('d/m/Y') ?></p>

            <hr class="my-4">

            <h3>1. Thông tin chúng tôi thu thập</h3>
            <p>Khi sử dụng dịch vụ, chúng tôi có thể thu thập:</p>
            <ul>
                <li><strong>Thông tin cá nhân:</strong> Họ tên, email, số điện thoại, địa chỉ</li>
                <li><strong>Thông tin tài khoản:</strong> Username, mật khẩu (đã mã hóa)</li>
                <li><strong>Thông tin giao dịch:</strong> Lịch sử quyên góp, số tiền</li>
                <li><strong>Thông tin kỹ thuật:</strong> IP, trình duyệt, thiết bị</li>
            </ul>

            <h3>2. Mục đích sử dụng thông tin</h3>
            <p>Chúng tôi sử dụng thông tin để:</p>
            <ul>
                <li>Cung cấp và cải thiện dịch vụ</li>
                <li>Xử lý giao dịch quyên góp</li>
                <li>Liên hệ và hỗ trợ người dùng</li>
                <li>Gửi thông báo về hoạt động từ thiện</li>
                <li>Phòng chống gian lận</li>
                <li>Tuân thủ pháp luật</li>
            </ul>

            <h3>3. Chia sẻ thông tin</h3>
            <p>Chúng tôi KHÔNG bán thông tin cá nhân. Thông tin có thể được chia sẻ với:</p>
            <ul>
                <li><strong>Nhà tổ chức sự kiện:</strong> Thông tin người quyên góp (nếu không ẩn danh)</li>
                <li><strong>Đối tác thanh toán:</strong> Thông tin cần thiết để xử lý giao dịch</li>
                <li><strong>Cơ quan có thẩm quyền:</strong> Khi có yêu cầu hợp pháp</li>
            </ul>

            <h3>4. Bảo mật thông tin</h3>
            <p>Chúng tôi áp dụng các biện pháp bảo mật:</p>
            <ul>
                <li>Mã hóa SSL/TLS cho kết nối</li>
                <li>Mã hóa mật khẩu bằng bcrypt</li>
                <li>Firewall và hệ thống phát hiện xâm nhập</li>
                <li>Kiểm tra bảo mật định kỳ</li>
                <li>Giới hạn quyền truy cập nội bộ</li>
            </ul>

            <h3>5. Cookie</h3>
            <p>Chúng tôi sử dụng cookie để:</p>
            <ul>
                <li>Duy trì phiên đăng nhập</li>
                <li>Ghi nhớ tùy chọn người dùng</li>
                <li>Phân tích lưu lượng truy cập</li>
            </ul>
            <p>Bạn có thể tắt cookie trong trình duyệt nhưng có thể ảnh hưởng đến trải nghiệm.</p>

            <h3>6. Quyền của người dùng</h3>
            <p>Bạn có quyền:</p>
            <ul>
                <li>Truy cập và xem thông tin cá nhân</li>
                <li>Yêu cầu chỉnh sửa thông tin sai</li>
                <li>Yêu cầu xóa tài khoản và dữ liệu</li>
                <li>Từ chối nhận email marketing</li>
                <li>Khiếu nại về xử lý dữ liệu</li>
            </ul>

            <h3>7. Lưu trữ dữ liệu</h3>
            <p>Dữ liệu được lưu trữ tại:</p>
            <ul>
                <li>Server đặt tại Việt Nam</li>
                <li>Thời gian lưu trữ: Trong thời gian cần thiết hoặc theo quy định pháp luật</li>
                <li>Dữ liệu được sao lưu định kỳ</li>
            </ul>

            <h3>8. Trẻ em</h3>
            <p>Dịch vụ không dành cho người dưới 13 tuổi. Chúng tôi không cố ý thu thập thông tin của trẻ em. Nếu phát hiện, chúng tôi sẽ xóa ngay.</p>

            <h3>9. Thay đổi chính sách</h3>
            <p>Chính sách này có thể được cập nhật. Chúng tôi sẽ thông báo qua email hoặc trên website về những thay đổi quan trọng.</p>

            <h3>10. Liên hệ</h3>
            <p>Về vấn đề bảo mật, liên hệ:</p>
            <ul>
                <li>Email: privacy@charityevent.vn</li>
                <li>Địa chỉ: Hà Nội, Việt Nam</li>
            </ul>

            <hr class="my-4">
            
            <div class="text-center">
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">Quay lại trang chủ</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
