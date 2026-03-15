<?php
// 1. Nhúng file kết nối (đường dẫn dựa trên cấu trúc của bạn)
require_once '../config/db.php'; 

// 2. Ghi log dữ liệu nhận được để theo dõi
$jsonData = file_get_contents('php://input');
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [BUOC 1] Da nhan JSON: " . $jsonData . PHP_EOL, FILE_APPEND);

$data = json_decode($jsonData, true);

if ($data && isset($data['transferAmount'])) {
    $amount = $data['transferAmount'];
    $content = strtoupper($data['content']); 
    
    // 3. Tìm mã UHCD và số ID (Ví dụ: UHCD23)
    if (preg_match('/UHCD\D*(\d+)/', $content, $matches)) {
        $eventId = $matches[1];
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [BUOC 2] Tim thay ID: $eventId" . PHP_EOL, FILE_APPEND);
        
        try {
            // Bước 4: Thêm vào bảng donations với trạng thái 'pending'
            // Sử dụng biến $pdo từ file db.php của bạn
            $sql1 = "INSERT INTO donations (event_id, donor_name, amount, message, payment_method, status, is_anonymous, show_amount) 
                     VALUES (?, 'Mạnh thường quân (SePay)', ?, ?, 'bank_transfer', 'pending', 0, 1)";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute([$eventId, $amount, $content]);
            
            $donationId = $pdo->lastInsertId();
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [BUOC 3] Da tao giao dich ID: $donationId" . PHP_EOL, FILE_APPEND);

            // Bước 5: UPDATE trạng thái sang 'completed' để KÍCH HOẠT TRIGGER
            // Khi lệnh này chạy, Trigger after_donation_completed sẽ tự động cộng tiền vào bảng events
            $sql2 = "UPDATE donations SET status = 'completed' WHERE id = ?";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([$donationId]);

            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [THANH CONG] Trigger da tu dong cap nhat thanh tien do!" . PHP_EOL, FILE_APPEND);
            echo json_encode(["status" => "success"]);

        } catch (Exception $e) {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [LOI SQL] " . $e->getMessage() . PHP_EOL, FILE_APPEND);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    } else {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - [LOI] Khong tim thay ma UHCD trong noi dung" . PHP_EOL, FILE_APPEND);
    }
}