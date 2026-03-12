<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/contact.php');
    exit;
}

requireCSRF();

// Rate limiting
$rateLimit = checkRateLimit('contact', getClientIP(), 3, 3600);
if ($rateLimit !== true) {
    $error = urlencode($rateLimit['message']);
    header('Location: ' . BASE_URL . '/pages/contact.php?error=' . $error);
    exit;
}

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone'] ?? '');
$subject = $_POST['subject'] ?? '';
$message = trim($_POST['message']);

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
    $errorMsg = urlencode(implode('<br>', $errors));
    header('Location: ' . BASE_URL . '/pages/contact.php?error=' . $errorMsg);
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
    
    // Send confirmation email
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
    
    clearRateLimit('contact', getClientIP());
    
    $success = urlencode('Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.');
    header('Location: ' . BASE_URL . '/pages/contact.php?success=' . $success);
    exit;
    
} catch (PDOException $e) {
    error_log("Contact Error: " . $e->getMessage());
    $error = urlencode('Đã xảy ra lỗi. Vui lòng thử lại sau.');
    header('Location: ' . BASE_URL . '/pages/contact.php?error=' . $error);
    exit;
}
?>
