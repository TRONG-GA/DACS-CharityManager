<?php
/**
 * Admin - Statistics & Reports
 * Thống kê tổng quan với biểu đồ
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$pageTitle = 'Thống kê & Báo cáo';

// Get date range
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Overall statistics
$stats = getStatistics();

// Donations by month (last 12 months)
$donationsByMonth = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
           COUNT(*) as count,
           SUM(amount) as total
    FROM donations
    WHERE status = 'completed'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY month
    ORDER BY month
")->fetchAll();

// Events by category
$eventsByCategory = $pdo->query("
    SELECT category, COUNT(*) as count
    FROM events
    WHERE status IN ('approved', 'ongoing', 'completed')
    GROUP BY category
    ORDER BY count DESC
")->fetchAll();

// Top benefactors
$topBenefactors = $pdo->query("
    SELECT u.id, u.fullname,
           COUNT(DISTINCT e.id) as event_count,
           COALESCE(SUM(d.amount), 0) as total_raised
    FROM users u
    LEFT JOIN events e ON u.id = e.user_id AND e.status IN ('approved', 'ongoing', 'completed')
    LEFT JOIN donations d ON e.id = d.event_id AND d.status = 'completed'
    WHERE u.benefactor_status = 'approved'
    GROUP BY u.id
    ORDER BY total_raised DESC
    LIMIT 10
")->fetchAll();

// Recent growth stats
$thisMonth = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())) as new_users,
        (SELECT COUNT(*) FROM events WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())) as new_events,
        (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'completed' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())) as donations_this_month
")->fetch();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-graph-up"></i> <?= $pageTitle ?></h1>
                    <div class="btn-toolbar">
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> In báo cáo
                        </button>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1">Tổng quyên góp</h6>
                                        <h3 class="text-success mb-0"><?= formatMoney($stats['total_donations']) ?></h3>
                                        <small class="text-success">
                                            +<?= formatMoney($thisMonth['donations_this_month']) ?> tháng này
                                        </small>
                                    </div>
                                    <i class="bi bi-currency-dollar text-success" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1">Sự kiện</h6>
                                        <h3 class="text-primary mb-0"><?= number_format($stats['total_events']) ?></h3>
                                        <small class="text-primary">
                                            +<?= $thisMonth['new_events'] ?> tháng này
                                        </small>
                                    </div>
                                    <i class="bi bi-calendar-event text-primary" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1">Người quyên góp</h6>
                                        <h3 class="text-info mb-0"><?= number_format($stats['total_donors']) ?></h3>
                                        <small class="text-muted">Tổng số người</small>
                                    </div>
                                    <i class="bi bi-people text-info" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1">Nhà hảo tâm</h6>
                                        <h3 class="text-warning mb-0"><?= number_format($stats['total_benefactors']) ?></h3>
                                        <small class="text-muted">Đã xác minh</small>
                                    </div>
                                    <i class="bi bi-award text-warning" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <!-- Donations Chart -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Quyên góp 12 tháng gần đây</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="donationsChart" height="80"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Events by Category -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Sự kiện theo danh mục</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Benefactors -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 10 nhà hảo tâm</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Hạng</th>
                                        <th>Họ tên</th>
                                        <th>Số sự kiện</th>
                                        <th>Tổng quyên góp</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topBenefactors as $index => $b): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index < 3): ?>
                                            <span class="badge bg-<?= ['warning', 'secondary', 'danger'][$index] ?>">
                                                #<?= $index + 1 ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="text-muted">#<?= $index + 1 ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($b['fullname']) ?></strong>
                                        </td>
                                        <td><?= number_format($b['event_count']) ?> sự kiện</td>
                                        <td><strong class="text-success"><?= formatMoney($b['total_raised']) ?></strong></td>
                                        <td>
                                            <a href="../users/user_detail.php?id=<?= $b['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Xem
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Donations Chart
    const donationsCtx = document.getElementById('donationsChart').getContext('2d');
    new Chart(donationsCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($donationsByMonth, 'month')) ?>,
            datasets: [{
                label: 'Số tiền (đồng)',
                data: <?= json_encode(array_column($donationsByMonth, 'total')) ?>,
                borderColor: 'rgb(25, 135, 84)',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND'
                            }).format(context.parsed.y);
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000000) {
                                return (value / 1000000000).toFixed(1) + ' tỷ';
                            } else if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + ' tr';
                            }
                            return value;
                        }
                    }
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($eventsByCategory, 'category')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($eventsByCategory, 'count')) ?>,
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)',
                    'rgb(255, 159, 64)',
                    'rgb(201, 203, 207)',
                    'rgb(255, 99, 255)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    </script>
</body>
</html>
