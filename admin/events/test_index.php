<?php
/**
 * Test database connection and query
 */
require_once '../../config/db.php';

echo "<h3>Test Kết nối Database</h3>";

// Test 1: Kiểm tra biến $pdo
echo "<p><strong>Test 1:</strong> Biến \$pdo tồn tại? ";
if (isset($pdo)) {
    echo "✅ CÓ</p>";
} else {
    die("❌ KHÔNG - Lỗi trong file config/db.php</p>");
}

// Test 2: Kiểm tra kết nối
try {
    $pdo->query("SELECT 1");
    echo "<p><strong>Test 2:</strong> Kết nối database: ✅ THÀNH CÔNG</p>";
} catch (PDOException $e) {
    die("<p><strong>Test 2:</strong> Kết nối database: ❌ LỖI - " . $e->getMessage() . "</p>");
}

// Test 3: Kiểm tra bảng events
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM events");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Test 3:</strong> Tổng số sự kiện trong database: " . $result['total'] . "</p>";
} catch (PDOException $e) {
    echo "<p><strong>Test 3:</strong> ❌ Lỗi khi đọc bảng events - " . $e->getMessage() . "</p>";
}

// Test 4: Kiểm tra sự kiện pending
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM events WHERE status = 'pending'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Test 4:</strong> Số sự kiện chờ duyệt: " . $result['total'] . "</p>";
} catch (PDOException $e) {
    echo "<p><strong>Test 4:</strong> ❌ Lỗi - " . $e->getMessage() . "</p>";
}

// Test 5: Query đầy đủ
echo "<h3>Test 5: Query đầy đủ với JOIN</h3>";
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
    
    echo "<p><strong>SQL Query:</strong></p>";
    echo "<pre>" . htmlspecialchars($sql) . "</pre>";
    
    $stmt = $pdo->query($sql);
    
    if ($stmt === false) {
        $errorInfo = $pdo->errorInfo();
        echo "<p>❌ Query thất bại:</p>";
        echo "<pre>" . print_r($errorInfo, true) . "</pre>";
    } else {
        echo "<p>✅ Query thành công!</p>";
        
        $pendingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Kiểu dữ liệu kết quả:</strong> " . gettype($pendingEvents) . "</p>";
        echo "<p><strong>Số lượng:</strong> " . count($pendingEvents) . "</p>";
        
        if (is_array($pendingEvents) && count($pendingEvents) > 0) {
            echo "<p>✅ Có dữ liệu!</p>";
            echo "<h4>Dữ liệu sự kiện đầu tiên:</h4>";
            echo "<pre>" . print_r($pendingEvents[0], true) . "</pre>";
        } else {
            echo "<p>⚠️ Không có sự kiện nào với status='pending' hoặc có lỗi trong query</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Lỗi PDO: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='pending_events.php'>← Quay lại trang chờ duyệt</a></p>";
?>
```

Lưu file này với tên `test_db.php` trong cùng thư mục `admin/events/`, sau đó truy cập:
```
localhost/DACS-CharityManager/admin/events/test_db.php