<?php
/**
 * FINANCIAL REPORT - Báo cáo Thu Chi (Có xuất Excel)
 * Thống kê chi tiết thu/chi theo từng sự kiện
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';

requireBenefactorVerified();

$user_id = $_SESSION['user_id'];

// XỬ LÝ XUẤT EXCEL
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    $event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
    $from_date = $_GET['from_date'] ?? '';
    $to_date = $_GET['to_date'] ?? '';
    
    if (!$event_id) {
        die("Vui lòng chọn sự kiện");
    }
    
    // Kiểm tra quyền sở hữu
    $stmtEvent = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
    $stmtEvent->execute([$event_id, $user_id]);
    $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        die("Không tìm thấy sự kiện");
    }
    
    // Lấy dữ liệu THU
    $sqlDonations = "
        SELECT d.*, u.fullname as donor_name
        FROM donations d
        LEFT JOIN users u ON d.user_id = u.id
        WHERE d.event_id = ? AND d.status = 'completed'
    ";
    $paramsDon = [$event_id];
    
    if ($from_date) {
        $sqlDonations .= " AND DATE(d.created_at) >= ?";
        $paramsDon[] = $from_date;
    }
    if ($to_date) {
        $sqlDonations .= " AND DATE(d.created_at) <= ?";
        $paramsDon[] = $to_date;
    }
    
    $sqlDonations .= " ORDER BY d.created_at DESC";
    
    $stmtDon = $pdo->prepare($sqlDonations);
    $stmtDon->execute($paramsDon);
    $donations = $stmtDon->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy dữ liệu CHI
    $sqlExpenses = "
        SELECT *
        FROM event_expenses
        WHERE event_id = ?
    ";
    $paramsExp = [$event_id];
    
    if ($from_date) {
        $sqlExpenses .= " AND DATE(expense_date) >= ?";
        $paramsExp[] = $from_date;
    }
    if ($to_date) {
        $sqlExpenses .= " AND DATE(expense_date) <= ?";
        $paramsExp[] = $to_date;
    }
    
    $sqlExpenses .= " ORDER BY expense_date DESC";
    
    $stmtExp = $pdo->prepare($sqlExpenses);
    $stmtExp->execute($paramsExp);
    $expenses = $stmtExp->fetchAll(PDO::FETCH_ASSOC);
    
    // Tính tổng
    $total_thu = 0;
    $total_chi = 0;
    
    foreach ($donations as $d) {
        $total_thu += (float)$d['amount'];
    }
    
    foreach ($expenses as $e) {
        $total_chi += (float)$e['amount'];
    }
    
    $balance = $total_thu - $total_chi;
    
    // Tạo tên file
    $filename = 'BaoCaoThuChi_' . preg_replace('/[^A-Za-z0-9]/', '_', $event['title']) . '_' . date('Ymd_His') . '.xls';
    
    // Set headers cho Excel
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    // BOM for UTF-8
    echo "\xEF\xBB\xBF";
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 20px; }
        .summary { background-color: #ffffcc; font-weight: bold; }
        .total-row { background-color: #e6e6e6; font-weight: bold; }
        .amount-positive { color: #008000; }
        .amount-negative { color: #ff0000; }
    </style>
</head>
<body>

<div class="header">
    <h1>BÁO CÁO THU CHI</h1>
    <h2><?= htmlspecialchars($event['title']) ?></h2>
    <?php if ($from_date || $to_date): ?>
    <p>
        Từ ngày: <?= $from_date ? date('d/m/Y', strtotime($from_date)) : 'Không giới hạn' ?>
        - Đến ngày: <?= $to_date ? date('d/m/Y', strtotime($to_date)) : 'Không giới hạn' ?>
    </p>
    <?php endif; ?>
    <p>Ngày xuất: <?= date('d/m/Y H:i:s') ?></p>
</div>

<!-- Tóm tắt -->
<table>
    <tr class="summary">
        <td colspan="2"><strong>TÓM TẮT</strong></td>
    </tr>
    <tr>
        <td><strong>Tổng Thu:</strong></td>
        <td class="amount-positive"><?= number_format($total_thu, 0, ',', '.') ?> VNĐ</td>
    </tr>
    <tr>
        <td><strong>Tổng Chi:</strong></td>
        <td class="amount-negative"><?= number_format($total_chi, 0, ',', '.') ?> VNĐ</td>
    </tr>
    <tr>
        <td><strong>Số Dư:</strong></td>
        <td style="<?= $balance >= 0 ? 'color: blue;' : 'color: red;' ?> font-weight: bold;">
            <?= number_format($balance, 0, ',', '.') ?> VNĐ
        </td>
    </tr>
</table>

<br><br>

<!-- Chi tiết THU -->
<table>
    <thead>
        <tr>
            <th colspan="5" style="background-color: #90EE90; text-align: center;">
                <strong>CHI TIẾT THU (QUYÊN GÓP)</strong>
            </th>
        </tr>
        <tr>
            <th>STT</th>
            <th>Ngày</th>
            <th>Người góp</th>
            <th>Phương thức</th>
            <th>Số tiền</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($donations) > 0): ?>
            <?php $stt = 1; foreach ($donations as $d): ?>
            <tr>
                <td><?= $stt++ ?></td>
                <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                <td><?= $d['is_anonymous'] ? 'Ẩn danh' : htmlspecialchars($d['donor_name'] ?? 'Khách') ?></td>
                <td><?= htmlspecialchars($d['payment_method']) ?></td>
                <td class="amount-positive"><?= number_format($d['amount'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="4"><strong>TỔNG THU</strong></td>
                <td class="amount-positive"><strong><?= number_format($total_thu, 0, ',', '.') ?> VNĐ</strong></td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">Chưa có khoản thu nào</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br><br>

<!-- Chi tiết CHI -->
<table>
    <thead>
        <tr>
            <th colspan="5" style="background-color: #FFB6C1; text-align: center;">
                <strong>CHI TIẾT CHI (CHI PHÍ)</strong>
            </th>
        </tr>
        <tr>
            <th>STT</th>
            <th>Ngày chi</th>
            <th>Nội dung</th>
            <th>Số tiền</th>
            <th>Biên lai</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($expenses) > 0): ?>
            <?php $stt = 1; foreach ($expenses as $e): ?>
            <tr>
                <td><?= $stt++ ?></td>
                <td><?= date('d/m/Y', strtotime($e['expense_date'])) ?></td>
                <td><?= htmlspecialchars($e['description']) ?></td>
                <td class="amount-negative"><?= number_format($e['amount'], 0, ',', '.') ?></td>
                <td><?= !empty($e['receipt_file']) ? 'Có' : 'Không' ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3"><strong>TỔNG CHI</strong></td>
                <td class="amount-negative"><strong><?= number_format($total_chi, 0, ',', '.') ?> VNĐ</strong></td>
                <td></td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">Chưa có khoản chi nào</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br><br>

<!-- Chữ ký -->
<table border="0" style="width: 100%;">
    <tr>
        <td style="width: 50%; text-align: center; border: none;">
            <p><strong>Người lập biểu</strong></p>
            <p style="margin-top: 60px;">(Ký, họ tên)</p>
        </td>
        <td style="width: 50%; text-align: center; border: none;">
            <p><strong>Người phê duyệt</strong></p>
            <p style="margin-top: 60px;">(Ký, họ tên)</p>
        </td>
    </tr>
</table>

</body>
</html>
    <?php
    exit; // Dừng ở đây khi xuất Excel
}

// === PHẦN HIỂN THỊ WEB (không phải xuất Excel) ===

// Lấy danh sách sự kiện của benefactor
$stmtEvents = $pdo->prepare("
    SELECT id, title, target_amount, start_date, end_date, status
    FROM events 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmtEvents->execute([$user_id]);
$events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

// Lấy event_id được chọn (nếu có)
$selected_event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$selected_event = null;

// Lấy thời gian lọc
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

// Dữ liệu báo cáo
$donations = [];
$expenses = [];
$total_thu = 0;
$total_chi = 0;

if ($selected_event_id > 0) {
    // Kiểm tra quyền sở hữu
    foreach ($events as $ev) {
        if ($ev['id'] == $selected_event_id) {
            $selected_event = $ev;
            break;
        }
    }
    
    if ($selected_event) {
        // Query THU - Donations
        $sqlDonations = "
            SELECT d.*, u.fullname as donor_name
            FROM donations d
            LEFT JOIN users u ON d.user_id = u.id
            WHERE d.event_id = ? AND d.status = 'completed'
        ";
        $paramsDon = [$selected_event_id];
        
        if ($from_date) {
            $sqlDonations .= " AND DATE(d.created_at) >= ?";
            $paramsDon[] = $from_date;
        }
        if ($to_date) {
            $sqlDonations .= " AND DATE(d.created_at) <= ?";
            $paramsDon[] = $to_date;
        }
        
        $sqlDonations .= " ORDER BY d.created_at DESC";
        
        $stmtDon = $pdo->prepare($sqlDonations);
        $stmtDon->execute($paramsDon);
        $donations = $stmtDon->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($donations as $d) {
            $total_thu += (float)$d['amount'];
        }
        
        // Query CHI - Expenses
        $sqlExpenses = "
            SELECT *
            FROM event_expenses
            WHERE event_id = ?
        ";
        $paramsExp = [$selected_event_id];
        
        if ($from_date) {
            $sqlExpenses .= " AND DATE(expense_date) >= ?";
            $paramsExp[] = $from_date;
        }
        if ($to_date) {
            $sqlExpenses .= " AND DATE(expense_date) <= ?";
            $paramsExp[] = $to_date;
        }
        
        $sqlExpenses .= " ORDER BY expense_date DESC, created_at DESC";
        
        $stmtExp = $pdo->prepare($sqlExpenses);
        $stmtExp->execute($paramsExp);
        $expenses = $stmtExp->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($expenses as $e) {
            $total_chi += (float)$e['amount'];
        }
    }
}

$balance = $total_thu - $total_chi;

$pageTitle = 'Báo cáo Thu Chi';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="../public/css/benefactor/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .filter-row {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .filter-group button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        .filter-group button:hover {
            background: #0056b3;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid;
        }
        .summary-card.thu { border-left-color: #28a745; }
        .summary-card.chi { border-left-color: #dc3545; }
        .summary-card.balance { border-left-color: #007bff; }
        .summary-card h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
            font-weight: 600;
        }
        .summary-card .amount {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .summary-card.thu .amount { color: #28a745; }
        .summary-card.chi .amount { color: #dc3545; }
        .summary-card.balance .amount { color: #007bff; }
        .report-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .report-section h3 {
            margin: 0 0 15px 0;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
        }
        .transaction-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        .transaction-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        .transaction-table tr:hover {
            background: #f8f9fa;
        }
        .amount-positive {
            color: #28a745;
            font-weight: bold;
        }
        .amount-negative {
            color: #dc3545;
            font-weight: bold;
        }
        .btn-export {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-export:hover {
            background: #1e7e34;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #888;
        }
        .receipt-link {
            color: #007bff;
            text-decoration: none;
        }
        .receipt-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <aside class="sidebar">
        <a href="../index.php" style="text-decoration: none;">
            <div class="sidebar-logo">Charity Events</div>
        </a>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Tổng quan</a></li>
            <li><a href="create_campaign.php">+ Tạo sự kiện mới</a></li>
            <li class="active"><a href="financial_report.php">💰 Báo cáo thu chi</a></li>
            <li><a href="tinhnguyen.php">👥 Quản lý tình nguyện viên</a></li>
            <li><a href="settings.php">⚙️ Cài đặt tài khoản</a></li>
            <li><a href="../auth/logout.php">🚪 Đăng xuất</a></li>
            <li style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
                <a href="../index.php" style="background: rgba(255,255,255,0.1); border-radius: 6px;">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="dash-header">
            <h1 class="dash-title">💰 Báo cáo Thu Chi</h1>
        </div>

        <!-- Filter Box -->
        <div class="filter-box">
            <form method="GET" action="financial_report.php">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Chọn sự kiện</label>
                        <select name="event_id" required onchange="this.form.submit()">
                            <option value="">-- Chọn sự kiện --</option>
                            <?php foreach ($events as $ev): ?>
                            <option value="<?= $ev['id'] ?>" <?= $ev['id'] == $selected_event_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['title']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Từ ngày</label>
                        <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>">
                    </div>
                    <div class="filter-group">
                        <label>Đến ngày</label>
                        <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>">
                    </div>
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <button type="submit">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($selected_event): ?>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card thu">
                <h4><i class="fas fa-arrow-down"></i> Tổng Thu</h4>
                <p class="amount"><?= number_format($total_thu, 0, ',', '.') ?> VNĐ</p>
                <small><?= count($donations) ?> giao dịch</small>
            </div>
            <div class="summary-card chi">
                <h4><i class="fas fa-arrow-up"></i> Tổng Chi</h4>
                <p class="amount"><?= number_format($total_chi, 0, ',', '.') ?> VNĐ</p>
                <small><?= count($expenses) ?> giao dịch</small>
            </div>
            <div class="summary-card balance">
                <h4><i class="fas fa-wallet"></i> Số Dư</h4>
                <p class="amount"><?= number_format($balance, 0, ',', '.') ?> VNĐ</p>
                <small><?= $balance >= 0 ? 'Dương' : 'Âm' ?></small>
            </div>
        </div>

        <!-- Export Button -->
        <div style="text-align: right; margin-bottom: 15px;">
            <a href="?export=excel&event_id=<?= $selected_event_id ?>&from_date=<?= urlencode($from_date) ?>&to_date=<?= urlencode($to_date) ?>" 
               class="btn-export">
                <i class="fas fa-file-excel"></i> Xuất Excel
            </a>
        </div>

        <!-- Donations Section -->
        <div class="report-section">
            <h3><i class="fas fa-hand-holding-usd" style="color: #28a745;"></i> Chi tiết THU (Quyên góp)</h3>
            <?php if (count($donations) > 0): ?>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Người góp</th>
                        <th>Phương thức</th>
                        <th style="text-align: right;">Số tiền</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donations as $d): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                        <td>
                            <?php if ($d['is_anonymous']): ?>
                                <i class="fas fa-user-secret"></i> Ẩn danh
                            <?php else: ?>
                                <?= htmlspecialchars($d['donor_name'] ?? 'Khách') ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($d['payment_method']) ?></td>
                        <td class="amount-positive" style="text-align: right;">
                            +<?= number_format($d['amount'], 0, ',', '.') ?> VNĐ
                        </td>
                        <td><?= htmlspecialchars($d['message'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #f8f9fa; font-weight: bold;">
                        <td colspan="3">TỔNG THU</td>
                        <td class="amount-positive" style="text-align: right;">
                            +<?= number_format($total_thu, 0, ',', '.') ?> VNĐ
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <?php else: ?>
            <div class="no-data">
                <i class="fas fa-inbox" style="font-size: 48px; color: #ddd;"></i>
                <p>Chưa có khoản thu nào</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Expenses Section -->
        <div class="report-section">
            <h3><i class="fas fa-receipt" style="color: #dc3545;"></i> Chi tiết CHI (Chi phí)</h3>
            <?php if (count($expenses) > 0): ?>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Ngày chi</th>
                        <th>Nội dung</th>
                        <th style="text-align: right;">Số tiền</th>
                        <th style="text-align: center;">Biên lai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $e): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($e['expense_date'])) ?></td>
                        <td><?= htmlspecialchars($e['description']) ?></td>
                        <td class="amount-negative" style="text-align: right;">
                            -<?= number_format($e['amount'], 0, ',', '.') ?> VNĐ
                        </td>
                        <td style="text-align: center;">
                            <?php if (!empty($e['receipt_file'])): ?>
                            <a href="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($e['receipt_file']) ?>" 
                               target="_blank" class="receipt-link">
                                <i class="fas fa-file-image"></i> Xem
                            </a>
                            <?php else: ?>
                            <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #f8f9fa; font-weight: bold;">
                        <td colspan="2">TỔNG CHI</td>
                        <td class="amount-negative" style="text-align: right;">
                            -<?= number_format($total_chi, 0, ',', '.') ?> VNĐ
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <?php else: ?>
            <div class="no-data">
                <i class="fas fa-inbox" style="font-size: 48px; color: #ddd;"></i>
                <p>Chưa có khoản chi nào</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Balance Summary -->
        <div class="report-section" style="background: <?= $balance >= 0 ? '#d4edda' : '#f8d7da' ?>; border-left: 4px solid <?= $balance >= 0 ? '#28a745' : '#dc3545' ?>;">
            <h3 style="margin: 0; color: #333;">
                <i class="fas fa-calculator"></i> Tổng kết
            </h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 15px;">
                <div>
                    <div style="font-size: 14px; color: #666;">Tổng Thu</div>
                    <div style="font-size: 20px; font-weight: bold; color: #28a745;">
                        +<?= number_format($total_thu, 0, ',', '.') ?> VNĐ
                    </div>
                </div>
                <div>
                    <div style="font-size: 14px; color: #666;">Tổng Chi</div>
                    <div style="font-size: 20px; font-weight: bold; color: #dc3545;">
                        -<?= number_format($total_chi, 0, ',', '.') ?> VNĐ
                    </div>
                </div>
                <div>
                    <div style="font-size: 14px; color: #666;">Số Dư</div>
                    <div style="font-size: 20px; font-weight: bold; color: <?= $balance >= 0 ? '#007bff' : '#dc3545' ?>;">
                        <?= $balance >= 0 ? '+' : '' ?><?= number_format($balance, 0, ',', '.') ?> VNĐ
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <div class="no-data" style="background: white; border-radius: 8px; padding: 60px;">
            <i class="fas fa-chart-line" style="font-size: 64px; color: #ddd;"></i>
            <h3>Chưa chọn sự kiện</h3>
            <p>Vui lòng chọn một sự kiện để xem báo cáo thu chi</p>
        </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>