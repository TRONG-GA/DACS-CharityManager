<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/db.php';
}

$pageTitle = $pageTitle ?? 'Charity Event - Nền tảng kết nối từ thiện minh bạch';
$pageDescription = $pageDescription ?? 'Kết nối những tấm lòng nhân ái, mang yêu thương đến mọi miền đất nước';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?></title>
    <meta name="description" content="<?= sanitize($pageDescription) ?>">
    <meta name="keywords" content="từ thiện, quyên góp, charity, volunteer, tình nguyện, nhà hảo tâm">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/public/favicon.ico">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Swiper Slider -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    
    <!-- Additional CSS for specific pages -->
    <?php if (isset($additionalCSS)): ?>
        <?= $additionalCSS ?>
    <?php endif; ?>
