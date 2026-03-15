<?php
/**
 * Admin - Pending Events admin/events/pending_events.php
 * Danh sách sự kiện chờ duyệt
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Sự kiện chờ duyệt';

// Get pending events - KHÔNG dùng biến toàn cục, dùng biến local
$eventsPending = [];
$totalPending = 0;

try {
    $sql = "
        SELECT e.*, u.fullname as benefactor_name, u.email as benefactor_email,
               COUNT(DISTINCT d.id) as donation_count,
               COALESCE(SUM(d.amount), 0) as raised_amount
        FROM events e
        JOIN users u ON e.user_id = u.id
        LEFT JOIN donations d ON e.id = d.event_id AND d.status = 'completed'
        WHERE e.status = 'pending'
        GROUP BY e.id
        ORDER BY e.created_at DESC
    ";
    
    $stmt = $pdo->query($sql);
    
    if ($stmt !== false) {
        $eventsPending = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Đảm bảo luôn là array
        if (!is_array($eventsPending)) {
            $eventsPending = [];
        }
    }
    
} catch (PDOException $e) {
    error_log("Error in pending_events.php: " . $e->getMessage());
    $eventsPending = [];
}

$totalPending = count($eventsPending);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-clock-history"></i> <?= htmlspecialchars($pageTitle) ?> 
                        <span class="badge bg-warning text-dark"><?= $totalPending ?></span>
                    </h1>
                </div>

                <?php 
                $flash = getFlashMessage();
                if ($flash): 
                ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($totalPending > 0): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Có <strong><?= $totalPending ?></strong> sự kiện đang chờ bạn xem xét và phê duyệt.
                </div>

                <?php foreach ($eventsPending as $event): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <?php if (!empty($event['thumbnail'])): ?>
                                <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($event['thumbnail']) ?>" 
                                     alt="<?= htmlspecialchars($event['title']) ?>" 
                                     class="img-fluid rounded"
                                     onerror="this.src='<?= BASE_URL ?>/public/images/no-image.jpg'">
                                <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h4><?= htmlspecialchars($event['title']) ?></h4>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-person"></i> 
                                    <strong><?= htmlspecialchars($event['benefactor_name']) ?></strong>
                                    (<?= htmlspecialchars($event['benefactor_email']) ?>)
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['location']) ?>
                                    | <i class="bi bi-tag"></i> 
                                    <?php
                                    $categories = [
                                        'education' => 'Giáo dục',
                                        'medical' => 'Y tế',
                                        'disaster' => 'Thiên tai',
                                        'charity' => 'Từ thiện',
                                        'community' => 'Cộng đồng',
                                        'other' => 'Khác'
                                    ];
                                    echo $categories[$event['category']] ?? ucfirst($event['category']);
                                    ?>
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-calendar"></i> 
                                    <?= date('d/m/Y', strtotime($event['start_date'])) ?> 
                                    - 
                                    <?= date('d/m/Y', strtotime($event['end_date'])) ?>
                                </p>
                                <?php if (!empty($event['description'])): ?>
                                <p><?= nl2br(htmlspecialchars(mb_substr($event['description'], 0, 200))) ?><?= mb_strlen($event['description']) > 200 ? '...' : '' ?></p>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <span class="badge bg-info me-2">
                                        Mục tiêu: <?= number_format($event['target_amount'], 0, ',', '.') ?> VNĐ
                                    </span>
                                    <?php if ($event['volunteer_needed'] > 0): ?>
                                    <span class="badge bg-secondary">
                                        Cần <?= $event['volunteer_needed'] ?> tình nguyện viên
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-grid gap-2">
                                    <a href="event_detail.php?id=<?= $event['id'] ?>" 
                                       class="btn btn-info">
                                        <i class="bi bi-eye"></i> Xem chi tiết
                                    </a>
                                    <a href="approve_event.php?id=<?= $event['id'] ?>" 
                                       class="btn btn-success"
                                       onclick="return confirm('Bạn có chắc muốn duyệt sự kiện này?')">
                                        <i class="bi bi-check-lg"></i> Duyệt sự kiện
                                    </a>
                                    <a href="reject_event.php?id=<?= $event['id'] ?>" 
                                       class="btn btn-danger">
                                        <i class="bi bi-x-lg"></i> Từ chối
                                    </a>
                                </div>
                                <div class="mt-3 text-muted small">
                                    <i class="bi bi-clock"></i> 
                                    Đăng <?= date('d/m/Y H:i', strtotime($event['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php else: ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    Tuyệt vời! Không có sự kiện nào chờ duyệt.
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>