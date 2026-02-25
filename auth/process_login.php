<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Verify CSRF Token
requireCSRF();

// Thu thập dữ liệu
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validate input
if (empty($email) || empty($password)) {
    setFlashMessage('error', 'Vui lòng nhập đầy đủ email và mật khẩu');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// =====================================================
// RATE LIMITING - CHỐNG BRUTE FORCE
// =====================================================

$rateLimit = checkRateLimit('login', $email, MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME);
if ($rateLimit !== true) {
    setFlashMessage('error', $rateLimit['message']);
    
    // Log suspicious activity
    $logFile = dirname(__DIR__) . '/logs/failed_logins.log';
    $logContent = sprintf(
        "[%s] Too many attempts - Email: %s | IP: %s | User Agent: %s\n",
        date('Y-m-d H:i:s'),
        $email,
        getClientIP(),
        getUserAgent()
    );
    file_put_contents($logFile, $logContent, FILE_APPEND);
    
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// =====================================================
// KIỂM TRA THÔNG TIN ĐĂNG NHẬP
// =====================================================

try {
    // Tìm user theo email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Kiểm tra user tồn tại và mật khẩu đúng
    if (!$user || !verifyPassword($password, $user['password'])) {
        // Sai email hoặc mật khẩu
        
        // Log failed login
        $logFile = dirname(__DIR__) . '/logs/failed_logins.log';
        $logContent = sprintf(
            "[%s] Failed Login - Email: %s | IP: %s | Reason: %s\n",
            date('Y-m-d H:i:s'),
            $email,
            getClientIP(),
            $user ? 'Wrong password' : 'Email not found'
        );
        file_put_contents($logFile, $logContent, FILE_APPEND);
        
        setFlashMessage('error', 'Email hoặc mật khẩu không chính xác');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
    
    // =====================================================
    // KIỂM TRA TRẠNG THÁI TÀI KHOẢN
    // =====================================================
    
    if ($user['status'] === 'inactive') {
        setFlashMessage('error', 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
    
    if ($user['status'] === 'banned') {
        setFlashMessage('error', 'Tài khoản của bạn đã bị khóa do vi phạm điều khoản sử dụng.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
    
    // =====================================================
    // ĐĂNG NHẬP THÀNH CÔNG
    // =====================================================
    
    // Clear rate limit
    clearRateLimit('login', $email);
    
    // Set session
    login($user);
    
    // Handle "Remember Me"
    if ($remember) {
        // Tạo remember token
        $rememberToken = bin2hex(random_bytes(32));
        $rememberExpiry = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days
        
        // Lưu token vào database
        $stmt = $pdo->prepare("
            UPDATE users 
            SET remember_token = ?, remember_expiry = ? 
            WHERE id = ?
        ");
        $stmt->execute([$rememberToken, $rememberExpiry, $user['id']]);
        
        // Set cookie
        setcookie(
            'remember_token',
            $rememberToken,
            time() + (86400 * 30), // 30 days
            '/',
            '',
            false, // Set to true if using HTTPS
            true   // HttpOnly
        );
    }
    
    // Log successful login
    $logFile = dirname(__DIR__) . '/logs/logins.log';
    $logContent = sprintf(
        "[%s] Successful Login - ID: %d | Email: %s | IP: %s | User Agent: %s\n",
        date('Y-m-d H:i:s'),
        $user['id'],
        $email,
        getClientIP(),
        getUserAgent()
    );
    file_put_contents($logFile, $logContent, FILE_APPEND);
    
    // =====================================================
    // REDIRECT
    // =====================================================
    
    // Thông báo thành công
    setFlashMessage('success', 'Đăng nhập thành công! Chào mừng ' . $user['fullname']);
    
    // Redirect về trang trước đó hoặc trang chủ
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
    } else {
        // Redirect theo role
        if ($user['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
        } elseif ($user['role'] === 'benefactor') {
            header('Location: ' . BASE_URL . '/benefactor/my_events.php');
        } else {
            header('Location: ' . BASE_URL . '/index.php');
        }
    }
    exit;
    
} catch (PDOException $e) {
    // Log error
    error_log("Login Error: " . $e->getMessage());
    
    setFlashMessage('error', 'Đã xảy ra lỗi trong quá trình đăng nhập. Vui lòng thử lại sau.');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
?>
