<?php
require_once '../config/db.php';
require_once '../includes/security.php'; // THIẾU DÒNG NÀY

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/contact.php');
    exit;
}

requireCSRF();

// Rate limiting - chỉ bật trên production
if ($_SERVER['HTTP_HOST'] !== 'localhost' && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false) {
    $rateLimit = checkRateLimit('contact', getClientIP(), 3, 300);
    if ($rateLimit !== true) {
        $error = urlencode($rateLimit['message']);
        header('Location: ' . BASE_URL . '/pages/contact.php?error=' . $error);
        exit;
    }
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$subject = $_POST['subject'] ?? '';
$message = trim($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Vui lòng nhập họ tên';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ';
}

if (empty($subject)) {
    $errors[] = 'Vui lòng chọn chủ đề';
}

if (empty($message)) {
    $errors[] = 'Vui lòng nhập nội dung';
}

if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    header('Location: ' . BASE_URL . '/pages/contact.php');
    exit;
}

// Insert contact
try {
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    $stmt = $pdo->prepare("
        INSERT INTO contacts (user_id, name, email, phone, subject, message, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())
    ");
    
    $stmt->execute([$userId, $name, $email, $phone ?: null, $subject, $message]);
    
    $contactId = $pdo->lastInsertId();
    
    // Log
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logDir . '/contacts.log', 
        date('Y-m-d H:i:s') . " - New Contact - ID: $contactId | Name: $name | Subject: $subject\n",
        FILE_APPEND
    );
    
    // Send confirmation email (nếu có hàm sendEmail)
    if (function_exists('sendEmail')) {
        try {
            sendEmail($email, 'Xác nhận liên hệ - ' . SITE_NAME, "
Xin chào $name,

Cảm ơn bạn đã liên hệ với chúng tôi!

Chúng tôi đã nhận được tin nhắn của bạn và sẽ phản hồi trong thời gian sớm nhất.

Thông tin liên hệ:
- Họ tên: $name
- Email: $email
- Chủ đề: $subject

Trân trọng,
Đội ngũ " . SITE_NAME);
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            // Không dừng quá trình, chỉ log lỗi
        }
    }
    
    // Clear rate limit sau khi thành công
    if (function_exists('clearRateLimit')) {
        clearRateLimit('contact', getClientIP());
    }
    
    setFlashMessage('success', 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.');
    header('Location: ' . BASE_URL . '/pages/contact.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Contact Error: " . $e->getMessage());
    setFlashMessage('error', 'Đã xảy ra lỗi. Vui lòng thử lại sau.');
    header('Location: ' . BASE_URL . '/pages/contact.php');
    exit;
}