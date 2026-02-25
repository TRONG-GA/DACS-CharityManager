<?php
/**
 * Security Functions
 * CSRF Protection, XSS Prevention, Rate Limiting, Input Validation
 */

// =====================================================
// CSRF PROTECTION
// =====================================================

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    // Regenerate token if expired
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRY) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($token)) {
        return false;
    }
    
    // Check if token expired
    if (isset($_SESSION['csrf_token_time']) && time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRY) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF Field for Forms
 */
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Require CSRF Token (use in POST handlers)
 */
function requireCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            http_response_code(403);
            die('CSRF token validation failed. Please refresh and try again.');
        }
    }
}

// =====================================================
// XSS PREVENTION
// =====================================================

/**
 * Advanced XSS Cleaning (in addition to sanitize())
 */
function cleanXSS($data) {
    // Remove null bytes
    $data = str_replace(chr(0), '', $data);
    
    // Remove carriage returns
    $data = str_replace("\r", '', $data);
    
    // Replace tabs with spaces
    $data = str_replace("\t", ' ', $data);
    
    // Remove potentially dangerous HTML tags
    $data = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data);
    $data = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $data);
    $data = preg_replace('/on\w+\s*=\s*["\'].*?["\']/i', '', $data);
    
    return $data;
}

/**
 * Sanitize HTML for rich text (allows safe tags)
 */
function sanitizeHTML($html) {
    $allowed_tags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><table><tr><td><th>';
    return strip_tags($html, $allowed_tags);
}

// =====================================================
// INPUT VALIDATION
// =====================================================

/**
 * Validate Email
 */
function validateEmail($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate Phone Number (Vietnam format)
 */
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^(0[3|5|7|8|9])+([0-9]{8})$/', $phone);
}

/**
 * Validate Password Strength
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        $errors[] = 'Mật khẩu phải có ít nhất ' . MIN_PASSWORD_LENGTH . ' ký tự';
    }
    
    if (REQUIRE_PASSWORD_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 chữ cái viết hoa';
    }
    
    if (REQUIRE_PASSWORD_LOWERCASE && !preg_match('/[a-z]/', $password)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 chữ cái viết thường';
    }
    
    if (REQUIRE_PASSWORD_NUMBER && !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 chữ số';
    }
    
    if (REQUIRE_PASSWORD_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate URL
 */
function validateURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validate Date
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// =====================================================
// RATE LIMITING
// =====================================================

/**
 * Check Rate Limit
 * Prevents brute force attacks
 */
function checkRateLimit($action, $identifier, $maxAttempts = 5, $timeWindow = 900) {
    global $pdo;
    
    $key = $action . '_' . $identifier;
    $currentTime = time();
    
    // Create rate_limits table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rate_limits (
            id INT PRIMARY KEY AUTO_INCREMENT,
            action_key VARCHAR(255) UNIQUE NOT NULL,
            attempts INT DEFAULT 1,
            first_attempt_at INT NOT NULL,
            last_attempt_at INT NOT NULL,
            INDEX idx_action_key (action_key)
        ) ENGINE=InnoDB
    ");
    
    // Check existing record
    $stmt = $pdo->prepare("SELECT * FROM rate_limits WHERE action_key = ?");
    $stmt->execute([$key]);
    $record = $stmt->fetch();
    
    if (!$record) {
        // First attempt
        $stmt = $pdo->prepare("INSERT INTO rate_limits (action_key, first_attempt_at, last_attempt_at) VALUES (?, ?, ?)");
        $stmt->execute([$key, $currentTime, $currentTime]);
        return true;
    }
    
    // Check if time window has passed
    if ($currentTime - $record['first_attempt_at'] > $timeWindow) {
        // Reset counter
        $stmt = $pdo->prepare("UPDATE rate_limits SET attempts = 1, first_attempt_at = ?, last_attempt_at = ? WHERE action_key = ?");
        $stmt->execute([$currentTime, $currentTime, $key]);
        return true;
    }
    
    // Check if max attempts exceeded
    if ($record['attempts'] >= $maxAttempts) {
        $remainingTime = $timeWindow - ($currentTime - $record['first_attempt_at']);
        $remainingMinutes = ceil($remainingTime / 60);
        return [
            'allowed' => false,
            'message' => "Bạn đã vượt quá số lần thử. Vui lòng thử lại sau {$remainingMinutes} phút."
        ];
    }
    
    // Increment attempts
    $stmt = $pdo->prepare("UPDATE rate_limits SET attempts = attempts + 1, last_attempt_at = ? WHERE action_key = ?");
    $stmt->execute([$currentTime, $key]);
    
    return true;
}

/**
 * Clear Rate Limit (after successful action)
 */
function clearRateLimit($action, $identifier) {
    global $pdo;
    $key = $action . '_' . $identifier;
    $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE action_key = ?");
    $stmt->execute([$key]);
}

// =====================================================
// IP & USER AGENT TRACKING
// =====================================================

/**
 * Get Client IP Address
 */
function getClientIP() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/**
 * Get User Agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Detect Suspicious Activity
 */
function isSuspiciousRequest() {
    $userAgent = getUserAgent();
    $ip = getClientIP();
    
    // Common bot user agents
    $botPatterns = [
        'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python'
    ];
    
    foreach ($botPatterns as $pattern) {
        if (stripos($userAgent, $pattern) !== false) {
            return true;
        }
    }
    
    // Check for suspicious IP patterns
    if ($ip === '0.0.0.0' || empty($ip)) {
        return true;
    }
    
    return false;
}

// =====================================================
// FILE UPLOAD SECURITY
// =====================================================

/**
 * Validate Uploaded Image
 */
function validateUploadedImage($file) {
    $errors = [];
    
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Lỗi upload file';
        return ['valid' => false, 'errors' => $errors];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'File quá lớn. Tối đa ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB';
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        $errors[] = 'Định dạng file không hợp lệ. Chỉ chấp nhận: JPG, PNG, GIF, WEBP';
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_EXT)) {
        $errors[] = 'Phần mở rộng file không hợp lệ';
    }
    
    // Check if image is valid
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $errors[] = 'File không phải là hình ảnh hợp lệ';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'mime_type' => $mimeType ?? null,
        'extension' => $extension ?? null
    ];
}

// =====================================================
// SESSION SECURITY
// =====================================================

/**
 * Regenerate Session ID (call after login)
 */
function regenerateSession() {
    session_regenerate_id(true);
    $_SESSION['session_regenerated'] = true;
    $_SESSION['last_activity'] = time();
}

/**
 * Check Session Timeout
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Verify Session Fingerprint
 */
function verifySessionFingerprint() {
    $currentFingerprint = hash('sha256', getUserAgent() . getClientIP());
    
    if (!isset($_SESSION['fingerprint'])) {
        $_SESSION['fingerprint'] = $currentFingerprint;
    }
    
    if ($_SESSION['fingerprint'] !== $currentFingerprint) {
        session_unset();
        session_destroy();
        return false;
    }
    
    return true;
}

// =====================================================
// AUTO-CHECK LOGGED IN USERS
// =====================================================

if (isLoggedIn()) {
    checkSessionTimeout();
    verifySessionFingerprint();
}
?>
