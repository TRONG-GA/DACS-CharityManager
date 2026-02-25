<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'charity_event');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL Configuration
define('BASE_URL', '/DACS-CharityManager');
define('SITE_NAME', 'Charity Event');
define('SITE_TAGLINE', 'Nền tảng kết nối từ thiện minh bạch');
// CSRF Security
define('CSRF_TOKEN_EXPIRY', 3600);
define('MIN_PASSWORD_LENGTH', 8);  // minimum password length

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL', BASE_URL . '/public/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Pagination
define('ITEMS_PER_PAGE', 12);

// PDO Connection
require_once __DIR__ . '/constants.php';
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=3309;dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// =====================================================
// SECURITY FUNCTIONS
// =====================================================

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// =====================================================
// STRING & URL FUNCTIONS
// =====================================================

function generateSlug($string) {
    // Convert Vietnamese to ASCII
    $string = removeVietnamese($string);
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Ensure uniqueness
    return $slug . '-' . time();
}

function removeVietnamese($str) {
    $vietnamese = [
        'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
        'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
        'ì','í','ị','ỉ','ĩ',
        'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
        'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
        'ỳ','ý','ỵ','ỷ','ỹ',
        'đ',
        'À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ',
        'È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ',
        'Ì','Í','Ị','Ỉ','Ĩ',
        'Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ',
        'Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ',
        'Ỳ','Ý','Ỵ','Ỷ','Ỹ',
        'Đ'
    ];
    
    $ascii = [
        'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
        'e','e','e','e','e','e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
        'u','u','u','u','u','u','u','u','u','u','u',
        'y','y','y','y','y',
        'd',
        'A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A',
        'E','E','E','E','E','E','E','E','E','E','E',
        'I','I','I','I','I',
        'O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O',
        'U','U','U','U','U','U','U','U','U','U','U',
        'Y','Y','Y','Y','Y',
        'D'
    ];
    
    return str_replace($vietnamese, $ascii, $str);
}

// =====================================================
// FORMAT FUNCTIONS
// =====================================================

function formatMoney($amount) {
    if ($amount >= 1000000000) {
        return number_format($amount / 1000000000, 1) . ' tỷ';
    } elseif ($amount >= 1000000) {
        return number_format($amount / 1000000, 1) . ' triệu';
    } elseif ($amount >= 1000) {
        return number_format($amount / 1000, 0) . 'k';
    }
    return number_format($amount, 0, ',', '.') . ' đ';
}

function formatMoneyFull($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}

function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    if ($diff < 604800) return floor($diff / 86400) . ' ngày trước';
    if ($diff < 2592000) return floor($diff / 604800) . ' tuần trước';
    
    return date('d/m/Y', $time);
}

function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

// =====================================================
// FILE UPLOAD FUNCTIONS
// =====================================================

function uploadImage($file, $subfolder = '') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return null;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return null;
    }
    
    $targetDir = UPLOAD_DIR . ($subfolder ? $subfolder . '/' : '');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ($subfolder ? $subfolder . '/' : '') . $filename;
    }
    
    return null;
}

function uploadFile($file, $subfolder = 'documents') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png'
    ];
    
    if (!in_array($file['type'], $allowedTypes)) {
        return null;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return null;
    }
    
    $targetDir = UPLOAD_DIR . $subfolder . '/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $subfolder . '/' . $filename;
    }
    
    return null;
}

function deleteFile($filename) {
    $filepath = UPLOAD_DIR . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

// =====================================================
// AUTHENTICATION & SESSION FUNCTIONS
// =====================================================

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isBenefactor() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'benefactor';
}

function isBenefactorVerified() {
    return isset($_SESSION['benefactor_status']) && $_SESSION['benefactor_status'] === 'approved';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function requireBenefactor() {
    requireLogin();
    if (!isBenefactor() && !isAdmin()) {
        header('Location: ' . BASE_URL . '/benefactor/apply.php');
        exit;
    }
}

function requireBenefactorVerified() {
    requireBenefactor();
    if (!isBenefactorVerified() && !isAdmin()) {
        header('Location: ' . BASE_URL . '/benefactor/apply.php?status=pending');
        exit;
    }
}

function login($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['benefactor_status'] = $user['benefactor_status'];
    $_SESSION['avatar'] = $user['avatar'];
    
    // Update last login
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
}

function logout() {
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// =====================================================
// NOTIFICATION FUNCTIONS
// =====================================================

function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type; // success, error, warning, info
    $_SESSION['flash_message'] = $message;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_type'], $_SESSION['flash_message']);
        return ['type' => $type, 'message' => $message];
    }
    return null;
}

function createNotification($userId, $type, $title, $message, $link = null) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, link) 
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$userId, $type, $title, $message, $link]);
}

// =====================================================
// STATISTICS FUNCTIONS
// =====================================================

function updateStatistics() {
    global $pdo;
    
    $stats = [
        'total_events' => $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'approved'")->fetchColumn(),
        'total_donations' => $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'completed'")->fetchColumn(),
        'total_donors' => $pdo->query("SELECT COUNT(DISTINCT user_id) FROM donations WHERE status = 'completed'")->fetchColumn(),
        'total_volunteers' => $pdo->query("SELECT COUNT(*) FROM event_volunteers WHERE status = 'approved'")->fetchColumn(),
        'total_benefactors' => $pdo->query("SELECT COUNT(*) FROM users WHERE benefactor_status = 'approved'")->fetchColumn()
    ];
    
    $stmt = $pdo->prepare("
        UPDATE statistics SET 
        total_events = ?, 
        total_donations = ?, 
        total_donors = ?, 
        total_volunteers = ?,
        total_benefactors = ?
        WHERE id = 1
    ");
    
    $stmt->execute(array_values($stats));
    
    return $stats;
}

function getStatistics() {
    global $pdo;
    return $pdo->query("SELECT * FROM statistics WHERE id = 1")->fetch();
}

// =====================================================
// TERMS & CONDITIONS FUNCTIONS
// =====================================================

function getTermsByType($type) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM terms_conditions 
        WHERE type = ? AND is_active = 1 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$type]);
    return $stmt->fetch();
}

function logTermsAcceptance($userId, $termsId, $actionType, $referenceId = null) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO user_terms_acceptance 
        (user_id, terms_id, action_type, reference_id, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $userId, 
        $termsId, 
        $actionType, 
        $referenceId,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
}

// =====================================================
// EMAIL FUNCTIONS (Mock - Replace with real SMTP)
// =====================================================

function sendEmail($to, $subject, $body) {
    // TODO: Implement real email sending with PHPMailer or SMTP
    // For now, just log to file
    $logFile = __DIR__ . '/../logs/emails.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logContent = sprintf(
        "[%s] TO: %s | SUBJECT: %s\n%s\n\n",
        date('Y-m-d H:i:s'),
        $to,
        $subject,
        $body
    );
    
    file_put_contents($logFile, $logContent, FILE_APPEND);
    return true;
}

// =====================================================
// PAGINATION HELPER
// =====================================================

function paginate($query, $page = 1, $perPage = ITEMS_PER_PAGE) {
    global $pdo;
    
    $page = max(1, (int)$page);
    $offset = ($page - 1) * $perPage;
    
    // Get total count
    $countQuery = preg_replace('/SELECT .+ FROM/i', 'SELECT COUNT(*) FROM', $query);
    $total = $pdo->query($countQuery)->fetchColumn();
    
    // Get paginated data
    $dataQuery = $query . " LIMIT $perPage OFFSET $offset";
    $data = $pdo->query($dataQuery)->fetchAll();
    
    return [
        'data' => $data,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => ceil($total / $perPage)
    ];
}

?>
