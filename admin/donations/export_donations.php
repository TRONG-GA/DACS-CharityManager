<?php
/**
 * Admin - Export Donations to CSV
 * Xuất danh sách quyên góp ra CSV
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

// Get filters
$status = $_GET['status'] ?? '';
$eventId = $_GET['event_id'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Build query
$where = ['1=1'];
$params = [];

if ($status) {
    $where[] = "d.status = ?";
    $params[] = $status;
}

if ($eventId) {
    $where[] = "d.event_id = ?";
    $params[] = $eventId;
}

if ($startDate) {
    $where[] = "DATE(d.created_at) >= ?";
    $params[] = $startDate;
}

if ($endDate) {
    $where[] = "DATE(d.created_at) <= ?";
    $params[] = $endDate;
}

$whereClause = implode(' AND ', $where);

// Get donations
$query = "SELECT d.id, 
          COALESCE(u.fullname, d.donor_name, 'Ẩn danh') as donor_name,
          d.donor_email,
          d.donor_phone,
          e.title as event_title,
          d.amount,
          d.payment_method,
          d.transaction_id,
          d.status,
          d.message,
          d.created_at
          FROM donations d
          LEFT JOIN users u ON d.user_id = u.id
          JOIN events e ON d.event_id = e.id
          WHERE $whereClause
          ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$donations = $stmt->fetchAll();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="donations_export_' . date('Y-m-d_His') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Headers
fputcsv($output, [
    'ID',
    'Người quyên góp',
    'Email',
    'Số điện thoại',
    'Sự kiện',
    'Số tiền',
    'Phương thức',
    'Mã giao dịch',
    'Trạng thái',
    'Lời nhắn',
    'Ngày quyên góp'
]);

// Data rows
foreach ($donations as $donation) {
    $statusLabels = [
        'pending' => 'Chờ xác nhận',
        'completed' => 'Thành công',
        'failed' => 'Thất bại',
        'refunded' => 'Đã hoàn'
    ];
    
    $methodLabels = [
        'bank_transfer' => 'Chuyển khoản',
        'momo' => 'MoMo',
        'vnpay' => 'VNPay',
        'zalopay' => 'ZaloPay',
        'cash' => 'Tiền mặt'
    ];
    
    fputcsv($output, [
        $donation['id'],
        $donation['donor_name'],
        $donation['donor_email'] ?? '',
        $donation['donor_phone'] ?? '',
        $donation['event_title'],
        number_format($donation['amount'], 0, ',', '.'),
        $methodLabels[$donation['payment_method']] ?? $donation['payment_method'],
        $donation['transaction_id'] ?? '',
        $statusLabels[$donation['status']] ?? $donation['status'],
        $donation['message'] ?? '',
        date('d/m/Y H:i:s', strtotime($donation['created_at']))
    ]);
}

fclose($output);
exit;
