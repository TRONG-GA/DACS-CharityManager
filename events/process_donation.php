<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/events/events.php');
    exit;
}

requireCSRF();

// Rate limiting
$rateLimit = checkRateLimit('donation', getClientIP(), 10, 3600);
if ($rateLimit !== true) {
    setFlashMessage('error', $rateLimit['message']);
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/events/events.php');
    exit;
}

// Get data
$eventId = intval($_POST['event_id']);
$amountPreset = intval($_POST['amount_preset'] ?? 0);
$amountCustom = intval($_POST['amount_custom'] ?? 0);
$amount = $amountCustom > 0 ? $amountCustom : $amountPreset;
$donorName = trim($_POST['donor_name']);
$donorEmail = trim($_POST['donor_email'] ?? '');
$donorPhone = trim($_POST['donor_phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$paymentMethod = $_POST['payment_method'] ?? 'bank_transfer';
$isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;

// Validation
$errors = [];

if ($amount < 10000) {
    $errors[] = 'Số tiền quyên góp tối thiểu là 10,000 VNĐ';
}

if (empty($donorName)) {
    $errors[] = 'Vui lòng nhập họ tên';
}

// Check event exists
$stmt = $pdo->prepare("SELECT id, title, slug FROM events WHERE id = ? AND status = 'approved'");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    $errors[] = 'Sự kiện không tồn tại';
}

if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Insert donation
try {
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    $stmt = $pdo->prepare("
        INSERT INTO donations (user_id, event_id, donor_name, donor_email, donor_phone, amount, message, payment_method, is_anonymous, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->execute([
        $userId,
        $eventId,
        $donorName,
        $donorEmail ?: null,
        $donorPhone ?: null,
        $amount,
        $message,
        $paymentMethod,
        $isAnonymous
    ]);
    
    $donationId = $pdo->lastInsertId();
    
    // Log
    $logFile = __DIR__ . '/../logs/donations.log';
    $logContent = sprintf(
        "[%s] New Donation - ID: %d | Event: %s | Amount: %d | Method: %s | IP: %s\n",
        date('Y-m-d H:i:s'),
        $donationId,
        $event['title'],
        $amount,
        $paymentMethod,
        getClientIP()
    );
    @file_put_contents($logFile, $logContent, FILE_APPEND);
    
    // Mock payment - auto approve for demo
    $pdo->prepare("UPDATE donations SET status = 'completed' WHERE id = ?")->execute([$donationId]);
    $pdo->prepare("UPDATE events SET current_amount = current_amount + ? WHERE id = ?")->execute([$amount, $eventId]);
    
    clearRateLimit('donation', getClientIP());
    
    setFlashMessage('success', 'Cảm ơn bạn đã quyên góp ' . formatMoney($amount) . '! Đóng góp của bạn rất ý nghĩa.');
    header('Location: ' . BASE_URL . '/events/event_detail.php?slug=' . $event['slug']);
    exit;
    
} catch (PDOException $e) {
    error_log("Donation Error: " . $e->getMessage());
    setFlashMessage('error', 'Đã xảy ra lỗi. Vui lòng thử lại sau.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
