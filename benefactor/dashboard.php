<?php
/**
 * BENEFACTOR DASHBOARD - UPDATED VERSION
 * Dùng events table thay vì charity_registrations
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
 // benefactor/dashboard.php 
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
 
// Yêu cầu benefactor đã verified
requireBenefactorVerified();
 
$user_id = $_SESSION['user_id'];
 
// XỬ LÝ POST REQUEST - CẬP NHẬT SỰ KIỆN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireCSRF();
    
    if ($_POST['action'] === 'edit_event') {
        $id = (int)$_POST['event_id'];
        $title = sanitize($_POST['title'] ?? '');
        $target_amount = str_replace(['.', ','], '', $_POST['target_amount']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $category = $_POST['category'] ?? 'community';
        $location = trim($_POST['location'] ?? '');
        
        // Kiểm tra quyền sở hữu
        $stmtCheck = $pdo->prepare("
            SELECT e.*, 
                   COALESCE(SUM(d.amount), 0) as raised_amount 
            FROM events e
            LEFT JOIN donations d ON e.id = d.event_id AND d.status = 'completed'
            WHERE e.id = ? AND e.user_id = ?
            GROUP BY e.id
        ");
        $stmtCheck->execute([$id, $user_id]);
        $event = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($event) {
            $can_edit_title = ($event['raised_amount'] == 0);
            
            if ($can_edit_title && $title !== '') {
                $sql = "UPDATE events 
                        SET title = ?, target_amount = ?, start_date = ?, end_date = ?, 
                            category = ?, location = ? 
                        WHERE id = ? AND user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $target_amount, $start_date, $end_date, 
                               $category, $location, $id, $user_id]);
            } else {
                $sql = "UPDATE events 
                        SET target_amount = ?, start_date = ?, end_date = ?, 
                            category = ?, location = ? 
                        WHERE id = ? AND user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$target_amount, $start_date, $end_date, 
                               $category, $location, $id, $user_id]);
            }
            
            setFlashMessage('success', 'Cập nhật sự kiện thành công!');
        }
        header("Location: dashboard.php");
        exit;
    }
    
    if ($_POST['action'] === 'update_description') {
        $id = (int)$_POST['event_id'];
        $description = $_POST['description'] ?? '';
        
        $sql = "UPDATE events SET description = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$description, $id, $user_id]);
        
        setFlashMessage('success', 'Cập nhật mô tả thành công!');
        header("Location: dashboard.php");
        exit;
    }
}
 
try {
    // 1. LẤY DANH SÁCH SỰ KIỆN
    $sql = "SELECT 
                e.*,
                COALESCE(SUM(d.amount), 0) AS current_amount,
                COUNT(DISTINCT d.id) as donor_count,
                COUNT(DISTINCT v.id) as volunteer_count
            FROM events e
            LEFT JOIN donations d ON e.id = d.event_id AND d.status = 'completed'
            LEFT JOIN event_volunteers v ON e.id = v.event_id
            WHERE e.user_id = ?
            GROUP BY e.id 
            ORDER BY e.id DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    // 2. TÍNH TOÁN THỐNG KÊ
    $total_events = count($events);
    $total_raised = 0;
    $active_events = 0;
    $today = date('Y-m-d');
 
    foreach ($events as $event) {
        $total_raised += (float)$event['current_amount'];
        if (!empty($event['start_date']) && !empty($event['end_date'])) {
            if ($event['start_date'] <= $today && $event['end_date'] >= $today) {
                $active_events++;
            }
        }
    }
 
    // 3. DỮ LIỆU BIỂU ĐỒ THU/CHI
    $donationLabels = [];
    $donationThuDict = [];
    $donationChiDict = [];
 
    for ($i = 0; $i <= 5; $i++) {
        $monthStr = date('m/Y', strtotime("+$i months"));
        $donationLabels[] = $monthStr;
        $donationThuDict[$monthStr] = 0;
        $donationChiDict[$monthStr] = 0;
    }
 
    // THU từ donations
    $sqlDonations = "
        SELECT DATE_FORMAT(d.created_at, '%m/%Y') as month_year, SUM(d.amount) as total
        FROM donations d
        INNER JOIN events e ON d.event_id = e.id
        WHERE e.user_id = ? AND d.status = 'completed'
        AND d.created_at >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')
        GROUP BY month_year
    ";
    $stmtDon = $pdo->prepare($sqlDonations);
    $stmtDon->execute([$user_id]);
    $donData = $stmtDon->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($donData as $row) {
        if (isset($donationThuDict[$row['month_year']])) {
            $donationThuDict[$row['month_year']] = (float)$row['total'];
        }
    }
 
    // CHI từ event_expenses
    $sqlExpenses = "
        SELECT DATE_FORMAT(ee.expense_date, '%m/%Y') as month_year, SUM(ee.amount) as total
        FROM event_expenses ee
        INNER JOIN events e ON ee.event_id = e.id
        WHERE e.user_id = ?
        AND ee.expense_date >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')
        GROUP BY month_year
    ";
    $stmtExp = $pdo->prepare($sqlExpenses);
    $stmtExp->execute([$user_id]);
    $expData = $stmtExp->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($expData as $row) {
        if (isset($donationChiDict[$row['month_year']])) {
            $donationChiDict[$row['month_year']] = (float)$row['total'];
        }
    }
 
    $donationThuValues = array_values($donationThuDict);
    $donationChiValues = array_values($donationChiDict);
 
    // 4. BIỂU ĐỒ TÌNH NGUYỆN VIÊN
    $sqlVol = "
        SELECT ev.status, COUNT(*) as count 
        FROM event_volunteers ev 
        INNER JOIN events e ON ev.event_id = e.id 
        WHERE e.user_id = ? 
        GROUP BY ev.status
    ";
    $stmtVol = $pdo->prepare($sqlVol);
    $stmtVol->execute([$user_id]);
    $volData = $stmtVol->fetchAll(PDO::FETCH_ASSOC);
 
    $volPending = 0; $volApproved = 0; $volRejected = 0;
    foreach ($volData as $row) {
        if ($row['status'] == 'pending') $volPending = $row['count'];
        if ($row['status'] == 'approved') $volApproved = $row['count'];
        if ($row['status'] == 'rejected') $volRejected = $row['count'];
    }
 
} catch (Exception $e) {
    die("Lỗi Database: " . $e->getMessage());
}
 
$pageTitle = 'Dashboard Quản lý Sự kiện';
?>
 
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="../public/css/benefactor/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .ck-editor__editable_inline { min-height: 350px; }
        .action-links { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
        .action-links a { 
            padding: 5px 10px; border-radius: 4px; text-decoration: none !important; 
            font-size: 13px; font-weight: bold; color: white !important; 
            transition: 0.2s; display: inline-block; 
        }
        .action-links a.btn-edit { background-color: #007bff; }
        .action-links a.btn-edit:hover { background-color: #0056b3; }
        .action-links a.btn-desc { background-color: #17a2b8; }
        .action-links a.btn-desc:hover { background-color: #138496; }
        .action-links a.btn-ledger { background-color: #28a745; }
        .action-links a.btn-ledger:hover { background-color: #1e7e34; }
        .charts-wrapper { display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
        .chart-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); flex: 1; min-width: 300px; }
        .chart-box.main-chart { flex: 2; }
        .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #ffc107; color: #000; }
        .status-approved { background: #17a2b8; color: #fff; }
        .status-completed { background: #28a745; color: #fff; }
        .status-rejected { background: #dc3545; color: #fff; }
        .status-closed { background: #6c757d; color: #fff; }
    </style>
</head>
<body>
 
<div class="dashboard-wrapper">
    <aside class="sidebar">
    <a href="../index.php" style="text-decoration: none;">
        <div class="sidebar-logo">Charity Events</div>
    </a>
    <ul class="sidebar-menu">
        <li class="active"><a href="dashboard.php">📊 Tổng quan</a></li>
        <li><a href="create_campaign.php">+ Tạo sự kiện mới</a></li>
        <li><a href="manage_news.php">📝 Đăng tin tức</a></li>
        <li><a href="tinhnguyen.php">👥 Quản lý tình nguyện viên</a></li>
        <li><a href="settings.php">⚙️ Cài đặt tài khoản</a></li>
        <li><a href="../auth/logout.php">🚪 Đăng xuất</a></li>
         <li style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
            <a href="../index.php" style="background: rgba(255,255,255,0.1); border-radius: 6px;">
                <i class="fas fa-home"></i>  Về trang chủ
            </a>
        </li>
    </ul>
</aside>
 
    <main class="main-content">
        <?php if ($flash = getFlashMessage()): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>
 
        <div class="dash-header">
            <h1 class="dash-title">Tổng quan sự kiện</h1>
            <a href="create_campaign.php" class="btn-create">+ Tạo sự kiện mới</a>
        </div>
 
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-title">Tổng tiền đã quyên góp</div>
                <div class="stat-value"><?= formatMoney($total_raised) ?></div>
            </div>
            <div class="stat-card blue">
                <div class="stat-title">Tổng số sự kiện</div>
                <div class="stat-value"><?= $total_events ?></div>
            </div>
            <div class="stat-card green">
                <div class="stat-title">Sự kiện đang chạy</div>
                <div class="stat-value"><?= $active_events ?></div>
            </div>
        </div>
 
        <div class="charts-wrapper">
            <div class="chart-box main-chart">
                <h3 style="margin-top: 0; color: #333; font-size: 16px; margin-bottom: 15px;">
                    📊 Biểu đồ Thu / Chi theo tháng
                </h3>
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="donationsChart"></canvas>
                </div>
            </div>
            <div class="chart-box">
                <h3 style="margin-top: 0; color: #333; font-size: 16px; margin-bottom: 15px;">
                    👥 Thống kê Tình nguyện viên
                </h3>
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="volunteersChart"></canvas>
                </div>
            </div>
        </div>
 
        <div class="table-container">
            <div class="table-header">
                <h3>Danh sách sự kiện gần đây</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Tên sự kiện</th>
                        <th>Tiến độ quyên góp</th>
                        <th>Địa điểm</th>
                        <th>Phân loại</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th style="text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($events) > 0): ?>
                        <?php foreach($events as $event): 
                            $target = (float)$event['target_amount'];
                            $current = (float)$event['current_amount'];
                            
                            $percent = 0;
                            if($target > 0) $percent = round(($current / $target) * 100);
                            $bar_percent = min($percent, 100);
 
                            // Xác định trạng thái hiển thị
                            $statusClass = 'status-' . $event['status'];
                            $statusText = [
                                'pending' => 'Chờ duyệt',
                                'approved' => 'Đang chạy',
                                'rejected' => 'Từ chối',
                                'completed' => 'Hoàn thành',
                                'closed' => 'Đã đóng'
                            ][$event['status']] ?? 'Chờ xử lý';
                            
                            if ($event['status'] == 'approved' && !empty($event['end_date']) && $today > $event['end_date']) {
                                $statusClass = 'status-closed';
                                $statusText = 'Đã hết hạn';
                            }
                            
                            if ($percent >= 100 && $event['status'] == 'approved') {
                                $statusClass = 'status-completed';
                                $statusText = 'Hoàn thành';
                            }
                            
                            $can_edit_title = ($current == 0);
                            
                            $categoryNames = [
                                'medical' => 'Y tế',
                                'education' => 'Giáo dục',
                                'disaster' => 'Cứu trợ',
                                'children' => 'Trẻ em',
                                'elderly' => 'Người già',
                                'environment' => 'Môi trường',
                                'community' => 'Cộng đồng',
                                'other' => 'Khác'
                            ];
                        ?>
                        <tr>
                            <td style="font-weight: 500; max-width: 200px;">
                                <?= htmlspecialchars($event['title'] ?: 'Chưa đặt tên') ?>
                            </td>
                            <td style="width: 220px;">
                                <div style="font-size: 12px; margin-bottom: 5px;">
                                    <?= formatMoney($current) ?> / <?= formatMoney($target) ?> (<?= $percent ?>%)
                                </div>
                                <div style="height: 8px; background: #eee; border-radius: 4px; overflow: hidden;">
                                    <div style="width: <?= $bar_percent ?>%; height: 100%; background: <?= $percent >= 100 ? '#28a745' : '#007bff' ?>; transition: 0.3s;"></div>
                                </div>
                            </td>
                            <td style="font-size: 13px; max-width: 150px;">
                                <i class="fas fa-map-marker-alt" style="color: #d32f2f; margin-right: 5px;"></i>
                                <?= htmlspecialchars($event['location'] ?: 'Chưa cập nhật') ?>
                            </td>
                            <td>
                                <span style="font-size: 12px; background: #e9ecef; color: #495057; padding: 4px 8px; border-radius: 12px; border: 1px solid #dee2e6;">
                                    <?= $categoryNames[$event['category']] ?? 'Khác' ?>
                                </span>
                            </td>
                            <td style="font-size: 13px;">
                                <div>Từ: <?= formatDate($event['start_date']) ?></div>
                                <div style="margin-top: 4px;">Đến: <?= formatDate($event['end_date']) ?></div>
                            </td>
                            <td>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td class="action-links">
                                <?php if ($event['status'] !== 'rejected'): ?>
                                <a href="javascript:void(0)" class="btn-edit" 
                                   onclick="openEditModal(<?= $event['id'] ?>, '<?= htmlspecialchars(addslashes($event['title'] ?? '')) ?>', <?= $target ?>, '<?= $event['start_date'] ?>', '<?= $event['end_date'] ?>', <?= $can_edit_title ? 'true' : 'false' ?>, '<?= $event['category'] ?>', '<?= htmlspecialchars(addslashes($event['location'] ?? '')) ?>')">
                                    ✏️ Sửa
                                </a>
                                <a href="javascript:void(0)" class="btn-desc" 
                                   onclick="openDescModal(<?= $event['id'] ?>, '<?= htmlspecialchars(addslashes($event['title'] ?? '')) ?>')">
                                    ✍️ Viết mô tả
                                </a>
                                <textarea id="raw_desc_<?= $event['id'] ?>" style="display:none;"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                                <a href="ledger.php?id=<?= $event['id'] ?>" class="btn-ledger">💰 Thu/Chi</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #888;">
                                Chưa có sự kiện nào! <a href="create_campaign.php">Tạo sự kiện mới</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
 
<!-- Modal sửa sự kiện -->
<div id="editModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background: #fff; margin: 5% auto; padding: 20px; border-radius: 8px; width: 450px;">
        <span class="close" onclick="closeEditModal()" style="float: right; font-size: 28px; cursor: pointer;">&times;</span>
        <h2 style="margin-top: 0;">Sửa sự kiện</h2>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="edit_event">
            <input type="hidden" id="edit_event_id" name="event_id">
            
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px; font-weight: bold;">Tên sự kiện:</label>
                <input type="text" id="edit_title" name="title" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <small id="title_warning" style="color:#dc3545; display:none;">Không thể sửa tên vì đã có tiền ủng hộ.</small>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px; font-weight: bold;">Phân loại:</label>
                <select id="edit_category" name="category" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="medical">Y tế</option>
                    <option value="education">Giáo dục</option>
                    <option value="disaster">Cứu trợ</option>
                    <option value="children">Trẻ em</option>
                    <option value="elderly">Người già</option>
                    <option value="environment">Môi trường</option>
                    <option value="community">Cộng đồng</option>
                    <option value="other">Khác</option>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px; font-weight: bold;">Địa điểm:</label>
                <input type="text" id="edit_location" name="location" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px; font-weight: bold;">Mục tiêu (VNĐ):</label>
                <input type="number" id="edit_target_amount" name="target_amount" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            
            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:5px; font-weight: bold;">Ngày bắt đầu:</label>
                    <input type="date" id="edit_start_date" name="start_date" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:5px; font-weight: bold;">Ngày kết thúc:</label>
                    <input type="date" id="edit_end_date" name="end_date" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>
            
            <button type="submit" style="background: #28a745; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold;">
                Lưu thay đổi
            </button>
        </form>
    </div>
</div>
 
<!-- Modal viết mô tả -->
<div id="descModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6);">
    <div class="modal-content" style="background: #fff; margin: 3% auto; padding: 25px; border-radius: 8px; width: 800px; max-width: 95%;">
        <span class="close" onclick="closeDescModal()" style="float: right; font-size: 28px; cursor: pointer;">&times;</span>
        <h2 style="margin-top: 0; color: #d32f2f;">✍️ Cập nhật mô tả sự kiện</h2>
        <p id="desc_event_title" style="color: #555; font-style: italic; margin-bottom: 20px;"></p>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_description">
            <input type="hidden" id="desc_event_id" name="event_id">
            <textarea id="desc_editor" name="description"></textarea>
            <button type="submit" style="background: #d32f2f; color: white; border: none; padding: 12px 25px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; margin-top: 20px;">
                Lưu mô tả
            </button>
        </form>
    </div>
</div>
 
<script>
function openEditModal(id, title, target, start, end, canEdit, category, location) {
    document.getElementById('edit_event_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_target_amount').value = target;
    document.getElementById('edit_start_date').value = start;
    document.getElementById('edit_end_date').value = end;
    document.getElementById('edit_category').value = category;
    document.getElementById('edit_location').value = location;
    
    if (!canEdit) {
        document.getElementById('edit_title').readOnly = true;
        document.getElementById('edit_title').style.backgroundColor = '#e9ecef';
        document.getElementById('title_warning').style.display = 'block';
    } else {
        document.getElementById('edit_title').readOnly = false;
        document.getElementById('edit_title').style.backgroundColor = '#fff';
        document.getElementById('title_warning').style.display = 'none';
    }
    document.getElementById('editModal').style.display = 'block';
}
 
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
 
let editorInstance;
ClassicEditor.create(document.querySelector('#desc_editor'), {
    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo']
}).then(editor => {
    editorInstance = editor;
}).catch(error => {
    console.error(error);
});
 
function openDescModal(id, title) {
    document.getElementById('desc_event_id').value = id;
    document.getElementById('desc_event_title').innerText = "Sự kiện: " + title;
    editorInstance.setData(document.getElementById('raw_desc_' + id).value);
    document.getElementById('descModal').style.display = 'block';
}
 
function closeDescModal() {
    document.getElementById('descModal').style.display = 'none';
}
 
window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) closeEditModal();
    if (event.target == document.getElementById('descModal')) closeDescModal();
}
 
// Charts
document.addEventListener("DOMContentLoaded", function() {
    const ctxDonations = document.getElementById('donationsChart').getContext('2d');
    new Chart(ctxDonations, {
        type: 'bar',
        data: {
            labels: <?= json_encode($donationLabels) ?>,
            datasets: [
                {
                    label: 'Tiền thu vào',
                    data: <?= json_encode($donationThuValues) ?>,
                    backgroundColor: '#28a745',
                    borderRadius: 4,
                    maxBarThickness: 40
                },
                {
                    label: 'Tiền chi ra',
                    data: <?= json_encode($donationChiValues) ?>,
                    backgroundColor: '#dc3545',
                    borderRadius: 4,
                    maxBarThickness: 40
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + new Intl.NumberFormat('vi-VN', {style: 'currency', currency: 'VND'}).format(context.raw || 0);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000000) return (value/1000000000) + ' Tỷ';
                            if (value >= 1000000) return (value/1000000) + ' Tr';
                            if (value >= 1000) return (value/1000) + 'K';
                            return value + 'đ';
                        }
                    }
                }
            }
        }
    });
 
    const ctxVol = document.getElementById('volunteersChart').getContext('2d');
    let volPending = <?= $volPending ?>;
    let volApproved = <?= $volApproved ?>;
    let volRejected = <?= $volRejected ?>;
    let totalVol = volPending + volApproved + volRejected;
 
    new Chart(ctxVol, {
        type: 'doughnut',
        data: {
            labels: totalVol === 0 ? ['Chưa có'] : ['Chờ duyệt', 'Đã duyệt', 'Từ chối'],
            datasets: [{
                data: totalVol === 0 ? [1] : [volPending, volApproved, volRejected],
                backgroundColor: totalVol === 0 ? ['#e9ecef'] : ['#ffc107', '#28a745', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '65%'
        }
    });
});
</script>
 
</body>
</html>