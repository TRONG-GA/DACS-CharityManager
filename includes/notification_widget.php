<!-- ============================================
     UNIVERSAL NOTIFICATION WIDGET
     File: includes/notification_widget.php
     
     SỬ DỤNG:
     - User navbar: <?php include_once '../includes/notification_widget.php'; ?>
     - Benefactor navbar: <?php include_once '../includes/notification_widget.php'; ?>
     - Admin navbar: Đã có sẵn trong admin_navbar.php
     ============================================ -->

<?php if (isLoggedIn()): ?>
<?php
// Get unread notification count
$unreadCount = getUnreadNotificationCount($_SESSION['user_id']);
?>

<!-- Notification Dropdown -->
<li class="nav-item dropdown" style="list-style: none;">
    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationDropdown" 
       role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <?php if ($unreadCount > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
            <span class="visually-hidden">thông báo chưa đọc</span>
        </span>
        <?php endif; ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="min-width: 350px; max-height: 450px; overflow-y: auto;">
        <li>
            <div class="dropdown-header d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0">
                    <i class="fas fa-bell me-2"></i>Thông báo
                </h6>
                <?php if ($unreadCount > 0): ?>
                <span class="badge bg-danger"><?= $unreadCount ?> mới</span>
                <?php endif; ?>
            </div>
        </li>
        <li><hr class="dropdown-divider m-0"></li>
        
        <?php
        // Get latest 8 notifications
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 8
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $recentNotifs = $stmt->fetchAll();
        
        if (count($recentNotifs) > 0):
            foreach ($recentNotifs as $notif):
        ?>
        <li>
            <a class="dropdown-item py-3 <?= !$notif['is_read'] ? 'bg-unread' : '' ?>" 
               href="<?= BASE_URL ?>/users/notifications.php?mark_read&id=<?= $notif['id'] ?>">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <?php
                        $iconMap = [
                            'admin' => ['icon' => 'fa-user-shield', 'color' => 'danger'],
                            'donation' => ['icon' => 'fa-hand-holding-heart', 'color' => 'success'],
                            'event' => ['icon' => 'fa-calendar', 'color' => 'primary'],
                            'volunteer' => ['icon' => 'fa-hands-helping', 'color' => 'info']
                        ];
                        $iconData = $iconMap[$notif['type']] ?? ['icon' => 'fa-bell', 'color' => 'secondary'];
                        ?>
                        <div class="notification-icon bg-<?= $iconData['color'] ?> bg-opacity-10 text-<?= $iconData['color'] ?> rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 40px; height: 40px;">
                            <i class="fas <?= $iconData['icon'] ?>"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold small mb-1">
                            <?= truncate($notif['title'], 50) ?>
                        </div>
                        <div class="text-muted small mb-1">
                            <?= truncate($notif['message'], 100) ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.7rem;">
                            <i class="fas fa-clock me-1"></i><?= timeAgo($notif['created_at']) ?>
                        </div>
                    </div>
                    <?php if (!$notif['is_read']): ?>
                    <div class="flex-shrink-0 ms-2">
                        <span class="badge bg-danger rounded-pill">●</span>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
        </li>
        <li><hr class="dropdown-divider m-0"></li>
        <?php 
            endforeach;
        else: 
        ?>
        <li>
            <div class="text-center py-5">
                <i class="fas fa-bell-slash text-muted" style="font-size: 2.5rem;"></i>
                <p class="text-muted mb-0 mt-3">Không có thông báo</p>
            </div>
        </li>
        <?php endif; ?>
        
        <!-- Footer Actions -->
        <li class="notification-footer">
            <div class="d-grid gap-2 p-2">
                <?php if ($unreadCount > 0): ?>
                <a href="<?= BASE_URL ?>/users/notifications.php?mark_all_read" 
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/users/notifications.php" 
                   class="btn btn-sm btn-primary">
                    <i class="fas fa-list me-1"></i>Xem tất cả thông báo
                </a>
            </div>
        </li>
    </ul>
</li>

<!-- CSS cho notification widget -->
<style>
.notification-dropdown {
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.notification-dropdown .dropdown-item {
    border: none;
    transition: background-color 0.2s;
}

.notification-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.notification-dropdown .bg-unread {
    background-color: #fff8e1 !important;
    border-left: 3px solid #dc3545;
}

.notification-dropdown .dropdown-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.notification-dropdown .notification-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.notification-icon {
    font-size: 1.1rem;
}
</style>

<?php endif; ?>
