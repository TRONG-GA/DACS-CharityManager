<?php
// benefactor register0.php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<link rel="stylesheet" href="/DACS-CharityManager/public/css/benefactor/register0.css">
<main class="register-page">

    <div class="register-title">
        Đăng ký mở Tài khoản thanh toán minh bạch
    </div>
    <div class="register-container">
        <a href="javascript:void(0)" onclick="submitRegister('organization')" class="register-card organization">
            <div class="card-inner">
                <span class="register-btn">
                    Đăng ký tài khoản tổ chức
                </span>

                <img 
                    src="../public/uploads/documents/org_register.png"
                    alt="Đăng ký tổ chức"
                    class="register-image"
                >
            </div>
        </a>
        <a href="javascript:void(0)" onclick="submitRegister('personal')" class="register-card personal">
            <div class="card-inner">
                <span class="register-btn light">
                    Đăng ký tài khoản cá nhân
                </span>

                <img 
                    src="../public/uploads/documents/personal_register.png"
                    alt="Đăng ký cá nhân"
                    class="register-image"
                >
            </div>
        </a>

    </div>

</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
<form id="hiddenForm" action="register.php" method="POST" style="display: none;">
    <input type="hidden" name="form_type" id="formTypeInput" value="">
</form>

<script>
// Hàm JavaScript nhận lệnh khi người dùng click vào thẻ <a>
function submitRegister(type) {
    // Điền loại tài khoản (organization hoặc personal) vào form ẩn
    document.getElementById('formTypeInput').value = type;
    // Tự động bấm nút gửi form
    document.getElementById('hiddenForm').submit();
}
</script>