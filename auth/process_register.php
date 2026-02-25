<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

// Verify CSRF Token
requireCSRF();

// Rate limiting - Chống spam đăng ký
$ip = getClientIP();
$rateLimit = checkRateLimit('register', $ip, 20, 3600); // 5 lần/giờ
if ($rateLimit !== true) {
    setFlashMessage('error', $rateLimit['message']);
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

// Thu thập dữ liệu từ form
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$agree_terms = isset($_POST['agree_terms']);

// Mảng lưu lỗi
$errors = [];

// =====================================================
// VALIDATION
// =====================================================

// 1. Validate Fullname
if (empty($fullname)) {
    $errors[] = 'Vui lòng nhập họ tên';
} elseif (mb_strlen($fullname) < 3) {
    $errors[] = 'Họ tên phải có ít nhất 3 ký tự';
} elseif (mb_strlen($fullname) > 100) {
    $errors[] = 'Họ tên không được quá 100 ký tự';
}

// 2. Validate Email
if (empty($email)) {
    $errors[] = 'Vui lòng nhập email';
} elseif (!validateEmail($email)) {
    $errors[] = 'Email không hợp lệ';
} else {
    // Kiểm tra email đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email đã được sử dụng. Vui lòng chọn email khác.';
    }
}

// 3. Validate Phone (optional)
if (!empty($phone)) {
    if (!validatePhone($phone)) {
        $errors[] = 'Số điện thoại không hợp lệ (phải có 10 chữ số)';
    }
}

// 4. Validate Password
if (empty($password)) {
    $errors[] = 'Vui lòng nhập mật khẩu';
} else {
    $passwordValidation = validatePassword($password);
    if (!$passwordValidation['valid']) {
        $errors = array_merge($errors, $passwordValidation['errors']);
    }
}

// 5. Validate Password Confirmation
if (empty($password_confirm)) {
    $errors[] = 'Vui lòng xác nhận mật khẩu';
} elseif ($password !== $password_confirm) {
    $errors[] = 'Mật khẩu xác nhận không khớp';
}

// 6. Validate Terms Agreement
if (!$agree_terms) {
    $errors[] = 'Bạn phải đồng ý với Điều khoản sử dụng và Chính sách bảo mật';
}

// =====================================================
// XỬ LÝ LỖI
// =====================================================

if (!empty($errors)) {
    // Lưu lỗi vào session để hiển thị
    setFlashMessage('error', implode('<br>', $errors));
    
    // Giữ lại dữ liệu đã nhập (trừ password)
    $_SESSION['register_data'] = [
        'fullname' => $fullname,
        'email' => $email,
        'phone' => $phone
    ];
    
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

// =====================================================
// TẠO TÀI KHOẢN
// =====================================================

try {
    // Hash password
    $passwordHash = hashPassword($password);
    
    // Default avatar
    $defaultAvatar = 'default-avatar.png';
    
    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (fullname, email, phone, password, avatar, role, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'user', 'active', NOW())
    ");
    
    $stmt->execute([
        $fullname,
        $email,
        $phone ?: null,
        $passwordHash,
        $defaultAvatar
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // =====================================================
    // GHI LOG ĐĂNG KÝ
    // =====================================================
    
    $logFile = dirname(__DIR__) . '/logs/registrations.log';
    $logContent = sprintf(
        "[%s] New Registration - ID: %d | Email: %s | IP: %s | User Agent: %s\n",
        date('Y-m-d H:i:s'),
        $userId,
        $email,
        $ip,
        getUserAgent()
    );
    file_put_contents($logFile, $logContent, FILE_APPEND);
    
    // =====================================================
    // GỬI EMAIL CHÀO MỪNG (Mock - ghi vào file)
    // =====================================================
    
    $emailSubject = 'Chào mừng bạn đến với ' . SITE_NAME;
    $emailBody = "
    Xin chào $fullname,
    
    Cảm ơn bạn đã đăng ký tài khoản tại " . SITE_NAME . "!
    
    Thông tin tài khoản:
    - Email: $email
    - Họ tên: $fullname
    
    Bạn có thể đăng nhập và bắt đầu tham gia các hoạt động từ thiện ngay bây giờ.
    
    Trân trọng,
    Đội ngũ " . SITE_NAME . "
    ";
    
    sendEmail($email, $emailSubject, $emailBody);
    
    // =====================================================
    // TỰ ĐỘNG ĐĂNG NHẬP SAU KHI ĐĂNG KÝ
    // =====================================================
    
    // Lấy thông tin user vừa tạo
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    // Đăng nhập
    login($user);
    
    // Clear rate limit
    clearRateLimit('register', $ip);
    
    // Clear register data
    unset($_SESSION['register_data']);
    
    // Thông báo thành công
    setFlashMessage('success', 'Đăng ký thành công! Chào mừng bạn đến với ' . SITE_NAME);
    
    // =====================================================
    // REDIRECT
    // =====================================================
    
    // Nếu có trang redirect sau khi login
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
    } else {
        // Mặc định về trang chủ
        header('Location: ' . BASE_URL . '/index.php');
    }
    exit;
    
} catch (PDOException $e) {
    // Log lỗi
    error_log("Registration Error: " . $e->getMessage());
    
    // Thông báo lỗi chung
    setFlashMessage('error', 'Đã xảy ra lỗi trong quá trình đăng ký. Vui lòng thử lại sau.');
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}
?>
