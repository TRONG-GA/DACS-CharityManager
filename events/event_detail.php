<?php
require_once '../config/db.php';

// Get slug from URL
$slug = $_GET['slug'] ?? '';
// events/event_detail.php?slug
if (empty($slug)) {
    header('Location: ' . BASE_URL . '/events/events.php');
    exit;
}

// Get event details
$stmt = $pdo->prepare("
    SELECT e.*, u.fullname as organizer_name, u.email as organizer_email
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.slug = ? AND e.status = 'approved'
");
$stmt->execute([$slug]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: ' . BASE_URL . '/errors/404.php');
    exit;
}

// Update views
$pdo->prepare("UPDATE events SET views = views + 1 WHERE id = ?")->execute([$event['id']]);

// Calculate progress
$progress = ($event['target_amount'] > 0) ? ($event['current_amount'] / $event['target_amount'] * 100) : 0;
$daysLeft = max(0, floor((strtotime($event['end_date']) - time()) / 86400));

// Get recent donations
$donationsStmt = $pdo->prepare("
    SELECT donor_name, amount, message, is_anonymous, created_at
    FROM donations
    WHERE event_id = ? AND status = 'completed'
    ORDER BY created_at DESC
    LIMIT 10
");
$donationsStmt->execute([$event['id']]);
$donations = $donationsStmt->fetchAll();

// Count total donors
$donorCount = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE event_id = ? AND status = 'completed'");
$donorCount->execute([$event['id']]);
$totalDonors = $donorCount->fetchColumn();

// Get volunteers info
$volunteerStmt = $pdo->prepare("
    SELECT COUNT(*) as registered, 
           SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
    FROM event_volunteers
    WHERE event_id = ?
");
$volunteerStmt->execute([$event['id']]);
$volunteerInfo = $volunteerStmt->fetch();

$pageTitle = sanitize($event['title']) . ' - ' . SITE_NAME;
$pageDescription = sanitize($event['description']);

// FIX: Đổi tên biến để tránh conflict
$alertMessage = null;
if (isset($_GET['error'])) {
    $alertMessage = ['type' => 'error', 'message' => urldecode($_GET['error'])];
} elseif (isset($_GET['success'])) {
    $alertMessage = ['type' => 'success', 'message' => urldecode($_GET['success'])];
} else {
    $alertMessage = getFlashMessage();
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
.event-detail-page { padding: 40px 0; background: #f8f9fa; }
.event-hero { 
    background: white; 
    border-radius: 15px; 
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.event-hero img { width: 100%; height: 400px; object-fit: cover; }
.event-content { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.sidebar-box { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
.progress-large { height: 15px; font-size: 0.8rem; }
.stat-box { text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px; margin-bottom: 15px; }
.stat-box .number { font-size: 2rem; font-weight: 700; color: #dc3545; }
.stat-box .label { color: #666; font-size: 0.9rem; }
.donation-item { border-bottom: 1px solid #eee; padding: 15px 0; }
.donation-item:last-child { border: none; }
.btn-donate { background: #dc3545; color: white; padding: 15px 40px; font-size: 1.1rem; font-weight: 600; border-radius: 50px; }
.btn-donate:hover { background: #c82333; color: white; transform: translateY(-2px); }
</style>

<div class="event-detail-page">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <?php if ($alertMessage): ?>
                <div class="alert alert-<?= $alertMessage['type'] == 'error' ? 'danger' : $alertMessage['type'] ?> alert-dismissible fade show">
                    <?= $alertMessage['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <div class="event-hero">
                    <?php
$thumb = $event['thumbnail'];
if (strpos($thumb, 'events/') !== 0) {
    $thumb = 'events/' . $thumb;
}
?>
<img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($thumb) ?>"
                         alt="<?= sanitize($event['title']) ?>"
                         onerror="this.src='<?= BASE_URL ?>/public/images/placeholder.jpg'">
                </div>
                
                <div class="event-content">
                    <div class="mb-3">
                        <span class="badge bg-primary"><?= ucfirst($event['category']) ?></span>
                        <?php if ($event['is_urgent']): ?>
                        <span class="badge bg-danger">KHẨN CẤP</span>
                        <?php endif; ?>
                        <?php if ($event['is_featured']): ?>
                        <span class="badge bg-warning text-dark">NỔI BẬT</span>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="mb-3"><?= sanitize($event['title']) ?></h1>
                    
                    <div class="d-flex align-items-center text-muted mb-4">
                        <i class="fas fa-user me-2"></i>
                        <span class="me-4">Bởi: <strong><?= sanitize($event['organizer_name']) ?></strong></span>
                        
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <span class="me-4"><?= sanitize($event['province']) ?></span>
                        
                        <i class="fas fa-eye me-2"></i>
                        <span><?= number_format($event['views']) ?> lượt xem</span>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Mô tả:</strong> <?= sanitize($event['description']) ?>
                    </div>
                    
                    <div class="event-content-body">
                        <h3>Chi tiết sự kiện</h3>
                        <div><?= $event['content'] ?></div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <h5><i class="fas fa-calendar-alt text-danger me-2"></i>Thời gian</h5>
                        <div class="row text-center mt-3">
                            <div class="col-6">
                                <strong>Bắt đầu</strong>
                                <div class="text-muted"><?= date('d/m/Y', strtotime($event['start_date'])) ?></div>
                            </div>
                            <div class="col-6">
                                <strong>Kết thúc</strong>
                                <div class="text-muted"><?= date('d/m/Y', strtotime($event['end_date'])) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <h5><i class="fas fa-map-marker-alt text-danger me-2"></i>Địa điểm</h5>
                        <p class="mb-0"><?= sanitize($event['location']) ?>, <?= sanitize($event['district']) ?>, <?= sanitize($event['province']) ?></p>
                    </div>
                </div>
                
                <div class="event-content mt-3">
                    <h4 class="mb-4">
                        <i class="fas fa-heart text-danger me-2"></i>
                        Danh sách quyên góp gần đây (<?= $totalDonors ?>)
                    </h4>
                    
                    <?php if (empty($donations)): ?>
                        <p class="text-muted text-center py-4">Chưa có người quyên góp. Hãy là người đầu tiên!</p>
                    <?php else: ?>
                        <?php foreach ($donations as $donation): ?>
                        <div class="donation-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>
                                        <?php if ($donation['is_anonymous']): ?>
                                            <i class="fas fa-user-secret"></i> Ẩn danh
                                        <?php else: ?>
                                            <?= sanitize($donation['donor_name']) ?>
                                        <?php endif; ?>
                                    </strong>
                                    <?php if (!empty($donation['message'])): ?>
                                        <div class="text-muted small mt-1">
                                            <i class="fas fa-comment"></i> <?= sanitize($donation['message']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="text-muted small mt-1">
                                        <?= timeAgo($donation['created_at']) ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <strong class="text-success">
                                        <?= formatMoney($donation['amount']) ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="sidebar-box">
                    <h5 class="mb-4">
                        <i class="fas fa-chart-line text-danger me-2"></i>
                        Tiến độ quyên góp
                    </h5>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Đã quyên góp</span>
                            <strong class="text-success"><?= formatMoney($event['current_amount']) ?></strong>
                        </div>
                        <div class="progress progress-large">
                            <div class="progress-bar bg-success" style="width: <?= min($progress, 100) ?>%">
                                <?= number_format($progress, 1) ?>%
                            </div>
                        </div>
                        <div class="text-end mt-2 text-muted small">
                            Mục tiêu: <?= formatMoney($event['target_amount']) ?>
                        </div>
                    </div>
                    
                    <div class="row g-2 mt-4">
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="number"><?= $totalDonors ?></div>
                                <div class="label">Người ủng hộ</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="number"><?= $daysLeft ?></div>
                                <div class="label">Ngày còn lại</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="<?= BASE_URL ?>/events/donate.php?event_id=<?= $event['id'] ?>" 
                           class="btn btn-donate">
                            <i class="fas fa-hand-holding-heart me-2"></i>
                            Quyên góp ngay
                        </a>
                    </div>
                </div>

                <div class="sidebar-box text-center" style="background: #f8f9fa; border: 1px solid #ddd;">
                    <h5 class="fw-bold text-success mb-2"><i class="fas fa-search-dollar"></i> Sao kê minh bạch</h5>
                    <p class="text-muted small mb-3">Toàn bộ lịch sử quyên góp và chi tiêu được cập nhật theo thời gian thực.</p>
                    <a href="export_excel.php?slug=<?= htmlspecialchars($event['slug']) ?>" class="btn btn-outline-success fw-bold w-100">
                        <i class="fas fa-file-excel me-2"></i> Tải sao kê mới nhất
                    </a>
                </div>
                <?php if ($event['volunteer_needed'] > 0): ?>
                <div class="sidebar-box">
                    <h5 class="mb-3">
                        <i class="fas fa-hands-helping text-danger me-2"></i>
                        Tình nguyện viên
                    </h5>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Đã đăng ký</span>
                            <strong><?= $volunteerInfo['registered'] ?>/<?= $event['volunteer_needed'] ?></strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" 
                                 style="width: <?= min(($volunteerInfo['registered'] / $event['volunteer_needed'] * 100), 100) ?>%">
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($volunteerInfo['registered'] < $event['volunteer_needed']): ?>
                    <div class="d-grid">
                        <a href="<?= BASE_URL ?>/events/volunteer_register.php?event_id=<?= $event['id'] ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-hand-paper me-2"></i>
                            Đăng ký tình nguyện
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Đã đủ tình nguyện viên
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="sidebar-box">
                    <h5 class="mb-3">
                        <i class="fas fa-share-alt text-danger me-2"></i>
                        Chia sẻ sự kiện
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                           target="_blank" 
                           class="btn btn-primary flex-fill">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($event['title']) ?>" 
                           target="_blank" 
                           class="btn btn-info flex-fill">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <button onclick="copyLink()" class="btn btn-secondary flex-fill">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/events/events.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách sự kiện
            </a>
        </div>
    </div>
</div>

<script>
function copyLink() {
    navigator.clipboard.writeText(window.location.href);
    alert('Đã copy link sự kiện!');
}
</script>

<?php include '../includes/footer.php'; ?>
