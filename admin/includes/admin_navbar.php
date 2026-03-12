<?php
/**
 * Admin Navigation Bar admin/includes/admin_navbar.php
 * Thanh điều hướng trên cùng của trang admin, bao gồm logo, link xem trang chính, thông báo và menu người dùng
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>/admin/dashboard.php">
            <i class="bi bi-shield-lock"></i> <?= SITE_NAME ?> - Admin
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- View Site -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php" target="_blank">
                        <i class="bi bi-box-arrow-up-right"></i> Xem trang chính
                    </a>
                </li>
                
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <?php 
                        $unreadCount = $pdo->query("
                            SELECT COUNT(*) FROM notifications 
                            WHERE user_id = {$_SESSION['user_id']} AND is_read = 0
                        ")->fetchColumn();
                        if ($unreadCount > 0): 
                        ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $unreadCount ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                        <li><h6 class="dropdown-header">Thông báo</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php
                        $notifications = $pdo->query("
                            SELECT * FROM notifications 
                            WHERE user_id = {$_SESSION['user_id']} 
                            ORDER BY created_at DESC LIMIT 5
                        ")->fetchAll();
                        
                        if (count($notifications) > 0):
                            foreach ($notifications as $notif):
                        ?>
                        <li>
                            <a class="dropdown-item <?= !$notif['is_read'] ? 'bg-light' : '' ?>" href="<?= $notif['link'] ?? '#' ?>">
                                <small class="text-muted"><?= timeAgo($notif['created_at']) ?></small>
                                <div class="fw-bold"><?= htmlspecialchars($notif['title']) ?></div>
                                <small><?= truncate($notif['message'], 50) ?></small>
                            </a>
                        </li>
                        <?php 
                            endforeach;
                        else: 
                        ?>
                        <li><span class="dropdown-item text-muted">Không có thông báo mới</span></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">Xem tất cả</a></li>
                    </ul>
                </li>
                
                <!-- User Profile -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= $_SESSION['avatar'] ?? 'default-avatar.png' ?>" 
                             alt="Avatar" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
                        <?= htmlspecialchars($_SESSION['fullname']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/profile.php">
                            <i class="bi bi-person"></i> Hồ sơ
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/settings/general.php">
                            <i class="bi bi-gear"></i> Cài đặt
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Đăng xuất
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
