<?php
/**
 * Admin Index - Redirect to Dashboard
 */
require_once '../config/db.php';
require_once '../includes/security.php';

// Require admin login
requireAdmin();

// Redirect to dashboard
header('Location: ' . BASE_URL . '/admin/dashboard.php');
exit;
?>
