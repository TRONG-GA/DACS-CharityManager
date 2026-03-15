<?php
// events/webhook.php
require_once '../config/db.php'; 

$jsonData = file_get_contents('php://input');
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [BUOC 1] Da nhan JSON: " . $jsonData . PHP_EOL, FILE_APPEND);

$data = json_decode($jsonData, true);

if ($data && isset($data['transferAmount'])) {
    $amount = $data['transferAmount'];
    $content = strtoupper($data['content']); 
    
    // Tìm con số đi kèm chữ UHCD (Bây giờ nó là ID giao dịch)
    if (preg_match('/UHCD\D*(\d+)/', $content, $matches)) {
        $donationId = $matches[1];
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [BUOC 2] Tim thay Don Hang ID: $donationId" . PHP_EOL, FILE_APPEND);
        
        try {
            // CẬP NHẬT LẠI TRẠNG THÁI VÀ SỐ TIỀN THỰC TẾ KHÁCH CHUYỂN
            // Ngay khi Update thành 'completed', Trigger trong DB của bạn sẽ chạy và cộng tiền vào chiến dịch
            $sql = "UPDATE donations SET status = 'completed', amount = ? WHERE id = ? AND status = 'pending'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$amount, $donationId]);

            if ($stmt->rowCount() > 0) {
                file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [THANH CONG] Don hang $donationId da hoan tat!" . PHP_EOL, FILE_APPEND);
                echo json_encode(["status" => "success"]);
            } else {
                file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [LOI] Don hang ko ton tai hoac da duoc xu ly" . PHP_EOL, FILE_APPEND);
                echo json_encode(["status" => "ignored"]);
            }

        } catch (Exception $e) {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [LOI SQL] " . $e->getMessage() . PHP_EOL, FILE_APPEND);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    } else {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [LOI] Khong tim thay ma UHCD" . PHP_EOL, FILE_APPEND);
        echo json_encode(["status" => "ignored"]);
    }
}
?>
