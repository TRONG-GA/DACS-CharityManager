<?php
/**
 * Admin - Pending Events admin/pending_events.php
 * Danh sách sự kiện chờ duyệt
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Sự kiện chờ duyệt';

// Get pending events
$pendingEvents = $pdo->query("
    SELECT e.*, u.fullname as benefactor_name, u.email as benefactor_email,
           COUNT(DISTINCT d.id) as donation_count,
           COALESCE(SUM(d.amount), 0) as raised_amount
    FROM events e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN donations d ON e.id = d.event_id AND d.status = 'completed'
    WHERE e.status = 'pending'
    GROUP BY e.id
    ORDER BY e.created_at DESC
")->fetchAll();

$totalPending = count($pendingEvents);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
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
                        <i class="bi bi-clock-history"></i> <?= $pageTitle ?> 
                        <span class="badge bg-warning text-dark"><?= $totalPending ?></span>
                    </h1>
                </div>

                <?php 
                $flash = getFlashMessage();
                if ($flash): 
                ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($totalPending > 0): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Có <strong><?= $totalPending ?></strong> sự kiện đang chờ bạn xem xét và phê duyệt.
                </div>

                <?php foreach ($pendingEvents as $event): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <?php if ($event['thumbnail']): ?>
                                <img src="<?= BASE_URL ?>/public/uploads/events/<?= $event['thumbnail'] ?>" 
                                     alt="Thumbnail" class="img-fluid rounded">
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
                                    | <i class="bi bi-tag"></i> <?= ucfirst($event['category']) ?>
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-calendar"></i> 
                                    <?= formatDate($event['start_date']) ?> - <?= formatDate($event['end_date']) ?>
                                </p>
                                <p><?= truncate(strip_tags($event['description']), 200) ?></p>
                                
                                <div class="mt-3">
                                    <span class="badge bg-info me-2">
                                        Mục tiêu: <?= formatMoney($event['target_amount']) ?>
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
                                <div class="mt-3 text-muted">
                                    <small>
                                        <i class="bi bi-clock"></i> 
                                        Đăng <?= timeAgo($event['created_at']) ?>
                                    </small>
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
