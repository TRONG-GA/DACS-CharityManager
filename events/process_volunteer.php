<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/events/events.php');
    exit;
}

requireCSRF();

// Rate limiting
$rateLimit = checkRateLimit('volunteer', getClientIP(), 5, 3600);
if ($rateLimit !== true) {
    setFlashMessage('error', $rateLimit['message']);
    session_write_close(); // FIX: Đảm bảo session được lưu
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/events/events.php');
    exit;
}

// Get data
$eventId = intval($_POST['event_id']);
$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$gender = $_POST['gender'] ?? '';
$birthDate = $_POST['birth_date'] ?? null;
$occupation = trim($_POST['occupation'] ?? '');
$skills = $_POST['skills'] ?? '[]';
$experience = trim($_POST['experience'] ?? '');
$motivation = trim($_POST['motivation']);

$availableWeekday = isset($_POST['available_weekday']) ? 1 : 0;
$availableWeekend = isset($_POST['available_weekend']) ? 1 : 0;
$availableEvening = isset($_POST['available_evening']) ? 1 : 0;

$agreeTerms = isset($_POST['agree_terms']);

// Validation
$errors = [];

if (empty($fullname)) {
    $errors[] = 'Vui lòng nhập họ tên';
}

// FIX: Dùng filter_var thay vì validateEmail()
if (empty($email)) {
    $errors[] = 'Vui lòng nhập email';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ';
}

// FIX: Dùng preg_match thay vì validatePhone()
if (empty($phone)) {
    $errors[] = 'Vui lòng nhập số điện thoại';
} elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
    $errors[] = 'Số điện thoại phải có 10 chữ số';
}

if (empty($gender)) {
    $errors[] = 'Vui lòng chọn giới tính';
}

if (empty($motivation)) {
    $errors[] = 'Vui lòng chia sẻ lý do bạn muốn tham gia';
}

if (!$agreeTerms) {
    $errors[] = 'Bạn phải đồng ý với các điều khoản';
}

// Check event
$stmt = $pdo->prepare("
    SELECT e.id, e.title, e.slug, e.volunteer_needed,
           (SELECT COUNT(*) FROM event_volunteers WHERE event_id = e.id) as registered
    FROM events e
    WHERE e.id = ? AND e.status = 'approved'
");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    $errors[] = 'Sự kiện không tồn tại';
} elseif ($event['registered'] >= $event['volunteer_needed']) {
    $errors[] = 'Sự kiện đã đủ số lượng tình nguyện viên';
}

// Check duplicate
if (empty($errors)) {
    $checkStmt = $pdo->prepare("
        SELECT id FROM event_volunteers 
        WHERE event_id = ? AND (email = ? OR phone = ?)
    ");
    $checkStmt->execute([$eventId, $email, $phone]);
    if ($checkStmt->fetch()) {
        $errors[] = 'Bạn đã đăng ký cho sự kiện này rồi';
    }
}

if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    session_write_close(); // FIX: Đảm bảo session được lưu trước khi redirect
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Insert
try {
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    $stmt = $pdo->prepare("
        INSERT INTO event_volunteers (
            user_id, event_id, fullname, email, phone, gender, birth_date,
            occupation, skills, experience, motivation, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $result = $stmt->execute([
        $userId,
        $eventId,
        $fullname,
        $email,
        $phone,
        $gender,
        $birthDate ?: null,
        $occupation,
        $skills,
        $experience,
        $motivation
    ]);
    
    if (!$result) {
        throw new Exception('Insert failed: ' . print_r($stmt->errorInfo(), true));
    }
    
    $volunteerId = $pdo->lastInsertId();
    
    // Log success
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logDir . '/volunteers.log', 
        date('Y-m-d H:i:s') . " - SUCCESS - ID: $volunteerId | Event: {$event['title']} | Name: $fullname\n",
        FILE_APPEND
    );
    
    // Send email
    $emailSubject = 'Xác nhận đăng ký tình nguyện - ' . SITE_NAME;
    $emailBody = "
Xin chào $fullname,

Cảm ơn bạn đã đăng ký làm tình nguyện viên cho sự kiện: {$event['title']}

Đơn đăng ký của bạn đang được xem xét. Chúng tôi sẽ liên hệ với bạn qua email hoặc số điện thoại trong thời gian sớm nhất.

Thông tin đăng ký:
- Họ tên: $fullname
- Email: $email
- Số điện thoại: $phone
- Trạng thái: Chờ duyệt

Bạn có thể kiểm tra trạng thái đơn đăng ký trong tài khoản cá nhân.

Trân trọng,
Đội ngũ " . SITE_NAME;
    
    sendEmail($email, $emailSubject, $emailBody);
    
    clearRateLimit('volunteer', getClientIP());
    
    setFlashMessage('success', 'Đăng ký thành công! Đơn của bạn đang chờ duyệt. Chúng tôi sẽ liên hệ với bạn sớm nhất. Cảm ơn bạn đã tham gia!');
    header('Location: ' . BASE_URL . '/events/event_detail.php?slug=' . $event['slug']);
    exit;
    
} catch (Exception $e) {
    error_log("Volunteer Registration Error: " . $e->getMessage());
    setFlashMessage('error', 'Đã xảy ra lỗi. Vui lòng thử lại sau.');
    session_write_close(); // FIX: Đảm bảo session được lưu
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>