<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
// Kiểm tra đã đăng nhập chưa
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Lưu thông tin trước khi logout (để log)
$userId = $_SESSION['user_id'] ?? 0;
$userEmail = $_SESSION['email'] ?? 'Unknown';

// =====================================================
// XÓA REMEMBER ME COOKIE (nếu có)
// =====================================================

if (isset($_COOKIE['remember_token'])) {
    // Xóa token trong database
    try {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL, remember_expiry = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    } catch (PDOException $e) {
        error_log("Logout Error: " . $e->getMessage());
    }
    
    // Xóa cookie
    setcookie('remember_token', '', time() - 3600, '/');
}

// =====================================================
// LOG LOGOUT
// =====================================================

$logFile = dirname(__DIR__) . '/logs/logins.log';
$logContent = sprintf(
    "[%s] Logout - ID: %d | Email: %s | IP: %s\n",
    date('Y-m-d H:i:s'),
    $userId,
    $userEmail,
    getClientIP()
);
file_put_contents($logFile, $logContent, FILE_APPEND);

// =====================================================
// HỦY SESSION VÀ REDIRECT
// =====================================================

// Hủy tất cả session
session_unset();
session_destroy();

// Thông báo
setFlashMessage('success', 'Đăng xuất thành công. Hẹn gặp lại!');

// Redirect về trang chủ
header('Location: ' . BASE_URL . '/index.php');
exit;
?>
