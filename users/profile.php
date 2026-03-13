<?php
require_once '../config/db.php';

// Check login
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get user info
$userQuery = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userQuery->execute([$userId]);
$user = $userQuery->fetch();

if (!$user) {
    session_destroy();
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Get user statistics
$stats = [];

// Total donations
$donationStats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_donations,
        COALESCE(SUM(amount), 0) as total_amount,
        COUNT(DISTINCT event_id) as events_donated
    FROM donations 
    WHERE user_id = ? AND status = 'completed'
");
$donationStats->execute([$userId]);
$stats['donations'] = $donationStats->fetch();

// Total volunteer registrations
$volunteerStats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_volunteers,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM event_volunteers 
    WHERE user_id = ?
");
$volunteerStats->execute([$userId]);
$stats['volunteers'] = $volunteerStats->fetch();

// Created events (if organizer)
$eventsStats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_events,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM events 
    WHERE user_id = ?
");
$eventsStats->execute([$userId]);
$stats['events'] = $eventsStats->fetch();

// Recent donations
$recentDonations = $pdo->prepare("
    SELECT d.*, e.title as event_title, e.slug as event_slug, e.thumbnail
    FROM donations d
    JOIN events e ON d.event_id = e.id
    WHERE d.user_id = ? AND d.status = 'completed'
    ORDER BY d.created_at DESC
    LIMIT 5
");
$recentDonations->execute([$userId]);
$donations = $recentDonations->fetchAll();

// Recent volunteer activities
$recentVolunteers = $pdo->prepare("
    SELECT ev.*, e.title as event_title, e.slug as event_slug, e.thumbnail, e.start_date
    FROM event_volunteers ev
    JOIN events e ON ev.event_id = e.id
    WHERE ev.user_id = ?
    ORDER BY ev.created_at DESC
    LIMIT 5
");
$recentVolunteers->execute([$userId]);
$volunteers = $recentVolunteers->fetchAll();

// Upcoming events (for volunteers)
$upcomingEvents = $pdo->prepare("
    SELECT e.*, ev.status as volunteer_status
    FROM events e
    JOIN event_volunteers ev ON e.id = ev.event_id
    WHERE ev.user_id = ? 
    AND ev.status = 'approved'
    AND e.start_date >= CURDATE()
    ORDER BY e.start_date ASC
    LIMIT 3
");
$upcomingEvents->execute([$userId]);
$upcoming = $upcomingEvents->fetchAll();

$pageTitle = 'Trang cá nhân - ' . $user['fullname'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if ($user['avatar']): ?>
                        <?php

$thumb = $user['avatar'];
if (strpos($thumb, 'avatars/') !== 0) {
    $thumb = 'avatars/' . $thumb;
}
?>
<img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($thumb) ?>"
                             class="rounded-circle" 
                             width="120" height="120"
                             style="object-fit: cover;">
                        <?php else: ?>
                        <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px; font-size: 3rem;">
                            <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <h5 class="mb-1"><?= sanitize($user['fullname']) ?></h5>
                    <p class="text-muted small mb-3">
                        <i class="fas fa-envelope me-1"></i><?= sanitize($user['email']) ?>
                    </p>
                    
                    <div class="d-grid gap-2">
                        <a href="edit_profile.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa
                        </a>
                        <a href="change_password.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-key me-2"></i>Đổi mật khẩu
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Menu -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-tachometer-alt me-2"></i>Tổng quan
                    </a>
                    <a href="my_donations.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-hand-holding-heart me-2"></i>Quyên góp
                        <?php if ($stats['donations']['total_donations'] > 0): ?>
                        <span class="badge bg-success float-end"><?= $stats['donations']['total_donations'] ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="my_volunteers.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-hands-helping me-2"></i>Tình nguyện
                        <?php if ($stats['volunteers']['total_volunteers'] > 0): ?>
                        <span class="badge bg-primary float-end"><?= $stats['volunteers']['total_volunteers'] ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if ($stats['events']['total_events'] > 0): ?>
                    <a href="my_events.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt me-2"></i>Sự kiện của tôi
                        <span class="badge bg-warning float-end"><?= $stats['events']['total_events'] ?></span>
                    </a>
                    <?php endif; ?>
                    <a href="settings.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog me-2"></i>Cài đặt
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Welcome Banner -->
            <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                <div class="card-body text-white p-4">
                    <h4 class="mb-2">Xin chào, <?= sanitize($user['fullname']) ?>! 👋</h4>
                    <p class="mb-0">Cảm ơn bạn đã đồng hành cùng chúng tôi trong hành trình lan tỏa yêu thương.</p>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-donate fa-2x"></i>
                            </div>
                            <h3 class="text-success mb-1"><?= formatMoney($stats['donations']['total_amount']) ?></h3>
                            <p class="text-muted small mb-0">Tổng quyên góp</p>
                            <small class="text-muted"><?= $stats['donations']['total_donations'] ?> lần</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-hands-helping fa-2x"></i>
                            </div>
                            <h3 class="text-primary mb-1"><?= $stats['volunteers']['total_volunteers'] ?></h3>
                            <p class="text-muted small mb-0">Hoạt động tình nguyện</p>
                            <small class="text-muted"><?= $stats['volunteers']['approved'] ?> đã duyệt</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                            <h3 class="text-warning mb-1"><?= $stats['donations']['events_donated'] ?></h3>
                            <p class="text-muted small mb-0">Chiến dịch hỗ trợ</p>
                            <small class="text-muted">Đã tham gia</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Events -->
            <?php if (!empty($upcoming)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt text-danger me-2"></i>
                        Sự kiện sắp tới
                    </h5>
                </div>
                <div class="card-body">
                    <?php foreach ($upcoming as $event): ?>
                    <div class="d-flex mb-3 pb-3 border-bottom">
                        <?php
                        $thumb = $event['thumbnail'];
                        if (strpos($thumb, 'events/') !== 0) {
                            $thumb = 'events/' . $thumb;
                        }
                        ?>
                        <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($thumb) ?>" 
                             class="rounded me-3"
                             style="width: 80px; height: 80px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <a href="<?= BASE_URL ?>/events/event_detail.php?slug=<?= $event['slug'] ?>" 
                                   class="text-dark text-decoration-none">
                                    <?= sanitize($event['title']) ?>
                                </a>
                            </h6>
                            <p class="text-muted small mb-1">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d/m/Y', strtotime($event['start_date'])) ?>
                            </p>
                            <span class="badge bg-success">Đã duyệt tình nguyện</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Recent Activities -->
            <div class="row">
                <!-- Recent Donations -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-hand-holding-heart text-success me-2"></i>
                                Quyên góp gần đây
                            </h6>
                            <a href="my_donations.php" class="btn btn-sm btn-outline-success">Xem tất cả</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($donations)): ?>
                                <?php foreach ($donations as $donation): ?>
                                <div class="d-flex mb-3 pb-3 border-bottom">
                                    <?php
                                    $thumb = $donation['thumbnail'];
                                    if (strpos($thumb, 'events/') !== 0) {
                                        $thumb = 'events/' . $thumb;
                                    }
                                    ?>
                                    <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($thumb) ?>" 
                                         class="rounded me-3"
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 small"><?= sanitize(substr($donation['event_title'], 0, 40)) ?>...</h6>
                                        <p class="text-success fw-bold mb-0"><?= formatMoney($donation['amount']) ?></p>
                                        <small class="text-muted"><?= timeAgo($donation['created_at']) ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center mb-0">Chưa có quyên góp nào</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Volunteers -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-hands-helping text-primary me-2"></i>
                                Hoạt động tình nguyện
                            </h6>
                            <a href="my_volunteers.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($volunteers)): ?>
                                <?php foreach ($volunteers as $vol): ?>
                                <div class="d-flex mb-3 pb-3 border-bottom">
                                    <?php
                                    $thumb = $vol['thumbnail'];
                                    if (strpos($thumb, 'events/') !== 0) {
                                        $thumb = 'events/' . $thumb;
                                    }
                                    ?>
                                    <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($thumb) ?>" 
                                         class="rounded me-3"
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 small"><?= sanitize(substr($vol['event_title'], 0, 40)) ?>...</h6>
                                        <span class="badge bg-<?= $vol['status'] == 'approved' ? 'success' : ($vol['status'] == 'pending' ? 'warning' : 'danger') ?>">
                                            <?= ucfirst($vol['status']) ?>
                                        </span>
                                        <small class="text-muted d-block"><?= timeAgo($vol['created_at']) ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center mb-0">Chưa có hoạt động tình nguyện</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
