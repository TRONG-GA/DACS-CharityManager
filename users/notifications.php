<?php
/**
 * User Notifications - user/notifications.php
 * Quản lý tất cả thông báo của người dùng
 */
require_once '../config/db.php';
require_once '../includes/security.php';

requireLogin();

$userId = $_SESSION['user_id'];

// Mark as read if requested
if (isset($_GET['mark_read']) && isset($_GET['id'])) {
    $notifId = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notifId, $userId]);
    
    // Redirect to link if provided
    $stmt = $pdo->prepare("SELECT link FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notifId, $userId]);
    $notif = $stmt->fetch();
    
    if ($notif && $notif['link']) {
        header('Location: ' . $notif['link']);
        exit;
    }
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$userId]);
    header('Location: notifications.php?success=1');
    exit;
}

// Delete notification
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $notifId = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notifId, $userId]);
    header('Location: notifications.php?deleted=1');
    exit;
}

// Filters
$filter = $_GET['filter'] ?? 'all'; // all, unread, read
$type = $_GET['type'] ?? '';

// Build query
$where = ['user_id = ?'];
$params = [$userId];

if ($filter === 'unread') {
    $where[] = 'is_read = 0';
} elseif ($filter === 'read') {
    $where[] = 'is_read = 1';
}

if ($type) {
    $where[] = 'type = ?';
    $params[] = $type;
}

$whereClause = implode(' AND ', $where);

// Get notifications
$query = "SELECT * FROM notifications WHERE $whereClause ORDER BY created_at DESC LIMIT 50";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$notifications = $stmt->fetchAll();

// Get counts
$unreadCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$unreadCount->execute([$userId]);
$unreadCount = $unreadCount->fetchColumn();

$pageTitle = 'Thông báo';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <!-- Sidebar Filters -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="notifications.php?filter=all" 
                       class="list-group-item list-group-item-action <?= $filter === 'all' ? 'active' : '' ?>">
                        <i class="fas fa-bell me-2"></i>Tất cả
                    </a>
                    <a href="notifications.php?filter=unread" 
                       class="list-group-item list-group-item-action <?= $filter === 'unread' ? 'active' : '' ?>">
                        <i class="fas fa-envelope me-2"></i>Chưa đọc
                        <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger float-end"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="notifications.php?filter=read" 
                       class="list-group-item list-group-item-action <?= $filter === 'read' ? 'active' : '' ?>">
                        <i class="fas fa-envelope-open me-2"></i>Đã đọc
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-tag me-2"></i>Loại thông báo</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="notifications.php" 
                       class="list-group-item list-group-item-action <?= empty($type) ? 'active' : '' ?>">
                        Tất cả loại
                    </a>
                    <a href="notifications.php?type=admin" 
                       class="list-group-item list-group-item-action <?= $type === 'admin' ? 'active' : '' ?>">
                        <i class="fas fa-user-shield me-2"></i>Quản trị
                    </a>
                    <a href="notifications.php?type=donation" 
                       class="list-group-item list-group-item-action <?= $type === 'donation' ? 'active' : '' ?>">
                        <i class="fas fa-hand-holding-heart me-2"></i>Quyên góp
                    </a>
                    <a href="notifications.php?type=event" 
                       class="list-group-item list-group-item-action <?= $type === 'event' ? 'active' : '' ?>">
                        <i class="fas fa-calendar me-2"></i>Sự kiện
                    </a>
                    <a href="notifications.php?type=volunteer" 
                       class="list-group-item list-group-item-action <?= $type === 'volunteer' ? 'active' : '' ?>">
                        <i class="fas fa-hands-helping me-2"></i>Tình nguyện
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell me-2"></i>Thông báo</h2>
                <div>
                    <?php if ($unreadCount > 0): ?>
                    <a href="notifications.php?mark_all_read" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>Đã đánh dấu tất cả thông báo là đã đọc
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-trash me-2"></i>Đã xóa thông báo
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Notifications List -->
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notif): ?>
                <div class="card mb-3 border-0 shadow-sm <?= $notif['is_read'] ? 'bg-light' : 'border-start border-4 border-danger' ?>">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <?php
                                $iconMap = [
                                    'admin' => 'fa-user-shield text-danger',
                                    'donation' => 'fa-hand-holding-heart text-success',
                                    'event' => 'fa-calendar text-primary',
                                    'volunteer' => 'fa-hands-helping text-info',
                                    'default' => 'fa-bell text-secondary'
                                ];
                                $icon = $iconMap[$notif['type']] ?? $iconMap['default'];
                                ?>
                                <i class="fas <?= $icon ?> fa-2x"></i>
                            </div>
                            <div class="col">
                                <h6 class="mb-1 <?= !$notif['is_read'] ? 'fw-bold' : '' ?>">
                                    <?= htmlspecialchars($notif['title']) ?>
                                </h6>
                                <p class="mb-1 text-muted"><?= nl2br(htmlspecialchars($notif['message'])) ?></p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i><?= timeAgo($notif['created_at']) ?>
                                </small>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group" role="group">
                                    <?php if ($notif['link']): ?>
                                    <a href="notifications.php?mark_read&id=<?= $notif['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!$notif['is_read']): ?>
                                    <a href="notifications.php?mark_read&id=<?= $notif['id'] ?>" 
                                       class="btn btn-sm btn-outline-success" title="Đánh dấu đã đọc">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="notifications.php?delete&id=<?= $notif['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger" title="Xóa"
                                       onclick="return confirm('Xóa thông báo này?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">Không có thông báo</h4>
                    <p class="text-muted">
                        <?php if ($filter === 'unread'): ?>
                        Bạn không có thông báo chưa đọc nào
                        <?php else: ?>
                        Bạn sẽ nhận được thông báo về hoạt động của mình tại đây
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
