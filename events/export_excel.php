<?php
require_once '../config/db.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    die("Không tìm thấy sự kiện!");
}

// 1. LẤY THÔNG TIN SỰ KIỆN
$stmt = $pdo->prepare("SELECT * FROM events WHERE slug = ? AND status = 'approved'");
$stmt->execute([$slug]);
$event = $stmt->fetch();

if (!$event) {
    die("Sự kiện không tồn tại!");
}

// 2. LẤY DỮ LIỆU THU (Quyên góp)
$stmtThu = $pdo->prepare("SELECT created_at, donor_name, amount, message, payment_method, is_anonymous FROM donations WHERE event_id = ? AND status = 'completed' ORDER BY created_at ASC");
$stmtThu->execute([$event['id']]);
$listThu = $stmtThu->fetchAll();

// 3. LẤY DỮ LIỆU CHI (Giải ngân)
$stmtChi = $pdo->prepare("SELECT expense_date as created_at, 'Ban Tổ Chức' as donor_name, amount, description as message, 'Chi tiêu' as payment_method FROM event_expenses WHERE event_id = ? ORDER BY expense_date ASC");
$stmtChi->execute([$event['id']]);
$listChi = $stmtChi->fetchAll();

// 4. CẤU HÌNH HEADER ĐỂ ÉP TRÌNH DUYỆT TẢI FILE EXCEL
$filename = "Sao_Ke_Minh_Bach_" . date('Ymd_His') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Fix lỗi font tiếng Việt trong Excel
echo "\xEF\xBB\xBF"; 
?>
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th colspan="5" style="font-size: 24px; font-weight: bold; background-color: #28a745; color: white; text-align: center; padding: 15px;">
                BÁO CÁO TÀI CHÍNH CHI TIẾT
            </th>
        </tr>
        <tr>
            <th colspan="5" style="font-size: 16px; text-align: center; padding: 10px;">Chiến dịch: <?= htmlspecialchars($event['title']) ?></th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center; font-style: italic;">Thời điểm xuất báo cáo: <?= date('d/m/Y H:i:s') ?></th>
        </tr>
        <tr><th colspan="5"></th></tr>
        
        <tr style="background-color: #f8f9fa; font-weight: bold; text-align: center;">
            <th style="width: 50px;">STT</th>
            <th style="width: 150px;">Ngày/Giờ</th>
            <th style="width: 100px;">Loại giao dịch</th>
            <th style="width: 400px;">Người gửi / Nội dung</th>
            <th style="width: 150px;">Số tiền (VNĐ)</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $stt = 1;
        $tongThu = 0;
        
        // IN DANH SÁCH THU
        foreach ($listThu as $thu) {
            $tongThu += $thu['amount'];
            $tenNguoiGui = $thu['is_anonymous'] ? 'Nhà hảo tâm ẩn danh' : htmlspecialchars($thu['donor_name']);
            $noiDung = htmlspecialchars($thu['message']);
            
            echo "<tr>";
            echo "<td style='text-align: center;'>" . $stt++ . "</td>";
            echo "<td style='text-align: center;'>" . date('d/m/Y H:i', strtotime($thu['created_at'])) . "</td>";
            echo "<td style='color: #28a745; font-weight: bold; text-align: center;'>THU</td>";
            echo "<td><strong>{$tenNguoiGui}</strong>" . ($noiDung ? " - {$noiDung}" : "") . "</td>";
            // Ép Excel định dạng số bằng mso-number-format
            echo "<td style='text-align: right; color: #28a745; font-weight: bold; mso-number-format:\"\#\,\#\#0\";'>" . $thu['amount'] . "</td>";
            echo "</tr>";
        }
        
        // IN DANH SÁCH CHI
        $tongChi = 0;
        foreach ($listChi as $chi) {
            $tongChi += $chi['amount'];
            echo "<tr>";
            echo "<td style='text-align: center;'>" . $stt++ . "</td>";
            echo "<td style='text-align: center;'>" . date('d/m/Y', strtotime($chi['created_at'])) . "</td>";
            echo "<td style='color: #dc3545; font-weight: bold; text-align: center;'>CHI</td>";
            echo "<td>" . htmlspecialchars($chi['message']) . "</td>";
            // Ép Excel định dạng số
            echo "<td style='text-align: right; color: #dc3545; font-weight: bold; mso-number-format:\"\#\,\#\#0\";'>-" . $chi['amount'] . "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr><th colspan="5"></th></tr>
        <tr style="font-size: 14px;">
            <td colspan="4" style="text-align: right; font-weight: bold;">TỔNG THU:</td>
            <td style="text-align: right; color: #28a745; font-weight: bold; mso-number-format:\"\#\,\#\#0\";"><?= $tongThu ?></td>
        </tr>
        <tr style="font-size: 14px;">
            <td colspan="4" style="text-align: right; font-weight: bold;">TỔNG CHI:</td>
            <td style="text-align: right; color: #dc3545; font-weight: bold; mso-number-format:\"\#\,\#\#0\";"><?= $tongChi ?></td>
        </tr>
        <tr style="font-size: 16px; background-color: #fff9e6;">
            <td colspan="4" style="text-align: right; font-weight: bold;">SỐ DƯ HIỆN TẠI:</td>
            <td style="text-align: right; font-weight: bold; mso-number-format:\"\#\,\#\#0\";"><?= $tongThu - $tongChi ?></td>
        </tr>
    </tfoot>
</table>