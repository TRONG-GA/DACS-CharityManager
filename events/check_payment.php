<?php
// events/check_payment.php
require_once '../config/db.php';
header('Content-Type: application/json');

$donationId = isset($_GET['donation_id']) ? (int)$_GET['donation_id'] : 0;

if ($donationId > 0) {
    // Chỉ tìm duy nhất đúng 1 cái đơn hàng của người này xem đã 'completed' chưa
    $stmt = $pdo->prepare("SELECT id, amount, donor_name FROM donations WHERE id = ? AND status = 'completed'");
    $stmt->execute([$donationId]);
    $donation = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($donation) {
        // Có tiền rồi báo thành công!
        echo json_encode(["status" => "success", "data" => $donation]);
        exit;
    }
}

// Chưa thấy gì thì đợi tiếp
echo json_encode(["status" => "waiting"]);
?>