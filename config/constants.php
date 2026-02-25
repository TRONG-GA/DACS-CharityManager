<?php
/**
 * System Constants & Configuration
 * This file contains all system-wide constants and security configurations
 */

// =====================================================
// SESSION SECURITY CONFIGURATION
// =====================================================

// Session cookie parameters (MUST be set before session_start())
ini_set('session.cookie_httponly', 1);  // Prevent JavaScript access to session cookie
ini_set('session.cookie_secure', 0);    // Set to 1 if using HTTPS
ini_set('session.use_only_cookies', 1); // Only use cookies for session
ini_set('session.cookie_samesite', 'Lax'); // CSRF protection

// Session configuration
ini_set('session.gc_maxlifetime', 3600); // Session lifetime: 1 hour
ini_set('session.cookie_lifetime', 0);   // Cookie expires when browser closes

// =====================================================
// FILE UPLOAD SECURITY
// =====================================================

// Allowed file types for images
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp'
]);

// Allowed file extensions
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Allowed document types
define('ALLOWED_DOC_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
]);

// =====================================================
// SECURITY CONSTANTS
// =====================================================

// Maximum login attempts before lockout
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// Password requirements
if (!defined('MIN_PASSWORD_LENGTH')) define('MIN_PASSWORD_LENGTH', 8);
define('REQUIRE_PASSWORD_UPPERCASE', true);
define('REQUIRE_PASSWORD_LOWERCASE', true);
define('REQUIRE_PASSWORD_NUMBER', true);
define('REQUIRE_PASSWORD_SPECIAL', true);

// CSRF token expiry (in seconds)
if (!defined('CSRF_TOKEN_EXPIRY')) define('CSRF_TOKEN_EXPIRY', 3600);// =====================================================
// EMAIL CONFIGURATION
// =====================================================

define('SMTP_ENABLED', false); // Set to true when SMTP is configured
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@charityevent.vn');
define('SMTP_FROM_NAME', 'Charity Event');

// =====================================================
// PAYMENT GATEWAY (Mock for now)
// =====================================================

define('MOMO_ENABLED', false);
define('MOMO_PARTNER_CODE', '');
define('MOMO_ACCESS_KEY', '');
define('MOMO_SECRET_KEY', '');

define('VNPAY_ENABLED', false);
define('VNPAY_TMN_CODE', '');
define('VNPAY_HASH_SECRET', '');
define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');

// =====================================================
// RECAPTCHA (Optional)
// =====================================================

define('RECAPTCHA_ENABLED', false);
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

// =====================================================
// SYSTEM LIMITS
// =====================================================

define('MAX_UPLOAD_SIZE_MB', 5);
define('MAX_IMAGES_PER_EVENT', 10);
define('MAX_EVENT_DESCRIPTION_LENGTH', 5000);
define('MAX_COMMENT_LENGTH', 1000);

// =====================================================
// DATE & TIME
// =====================================================

date_default_timezone_set('Asia/Ho_Chi_Minh');
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');

// =====================================================
// CACHE SETTINGS
// =====================================================

define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 300); // 5 minutes

// =====================================================
// DEBUG MODE (Set to false in production)
// =====================================================

define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
}

// =====================================================
// MAINTENANCE MODE
// =====================================================

define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'Hệ thống đang bảo trì. Vui lòng quay lại sau.');
define('MAINTENANCE_ALLOWED_IPS', ['127.0.0.1', '::1']); // IPs allowed during maintenance

// Check maintenance mode
if (MAINTENANCE_MODE && !in_array($_SERVER['REMOTE_ADDR'], MAINTENANCE_ALLOWED_IPS)) {
    http_response_code(503);
    die('
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bảo trì hệ thống</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 100px; }
            h1 { color: #dc3545; }
        </style>
    </head>
    <body>
        <h1>🔧 Hệ thống đang bảo trì</h1>
        <p>' . MAINTENANCE_MESSAGE . '</p>
    </body>
    </html>
    ');
}

// =====================================================
// AUTO-LOAD SECURITY FUNCTIONS
// =====================================================

require_once __DIR__ . '/../includes/security.php';
?>
