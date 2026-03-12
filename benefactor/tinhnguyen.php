<?php
// Khởi tạo session an toàn để lấy user_id
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bắt buộc đăng nhập và phải có quyền nhà hảo tâm
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'benefactor') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Thông tin kết nối CSDL
$host = '127.0.0.1';
$dbname = 'charity_event'; 
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

// 1. XỬ LÝ GET REQUEST: DUYỆT / TỪ CHỐI / ĐIỂM DANH
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // Kiểm tra bảo mật: TNV này có thuộc sự kiện của user đang đăng nhập không?
    $checkSql = "SELECT ev.id FROM event_volunteers ev 
                 INNER JOIN events e ON ev.event_id = e.id 
                 WHERE ev.id = :id AND e.user_id = :user_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute(['id' => $id, 'user_id' => $user_id]);
    
    if ($checkStmt->fetch()) {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE event_volunteers SET status = 'approved' WHERE id = :id");
            $stmt->execute(['id' => $id]);
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE event_volunteers SET status = 'rejected' WHERE id = :id");
            $stmt->execute(['id' => $id]);
        } elseif ($action === 'checkin') {
            // Xử lý ĐIỂM DANH: Gán attendance_confirmed = 1 và lưu thời gian
            $stmt = $pdo->prepare("UPDATE event_volunteers SET attendance_confirmed = 1, checked_in_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
    }
    header("Location: tinhnguyen.php");
    exit;
}

// 2. XỬ LÝ POST REQUEST: LƯU ĐÁNH GIÁ (RATING & FEEDBACK)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_feedback') {
    $vol_id = (int)$_POST['volunteer_id'];
    $rating = (int)$_POST['rating'];
    $feedback = trim($_POST['organizer_feedback']);

    // Kiểm tra bảo mật
    $checkSql = "SELECT ev.id FROM event_volunteers ev INNER JOIN events e ON ev.event_id = e.id WHERE ev.id = :id AND e.user_id = :user_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute(['id' => $vol_id, 'user_id' => $user_id]);

    if ($checkStmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE event_volunteers SET rating = :rating, organizer_feedback = :feedback WHERE id = :id");
        $stmt->execute(['rating' => $rating, 'feedback' => $feedback, 'id' => $vol_id]);
    }
    header("Location: tinhnguyen.php");
    exit;
}

// LẤY DỮ LIỆU TÌNH NGUYỆN VIÊN (Của các sự kiện do user này tổ chức)
$sql = "SELECT ev.*, e.title as event_title 
        FROM event_volunteers ev 
        INNER JOIN events e ON ev.event_id = e.id 
        WHERE e.user_id = :user_id
        ORDER BY ev.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// TÍNH TOÁN THỐNG KÊ
$totalVolunteers = count($volunteers);
$pendingCount = 0;
$approvedCount = 0;

foreach ($volunteers as $v) {
    $st = $v['status'] ?? 'pending';
    if ($st == 'pending') $pendingCount++;
    if ($st == 'approved') $approvedCount++;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tình Nguyện Viên</title>
    <link rel="stylesheet" href="../public/css/benefactor/dashboard.css?v=<?= time() ?>">
    <style>
        /* Style cho Popup Chi tiết */
        .modal-custom {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0; 
            width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);
        }
        .modal-content-custom {
            background-color: #fff; margin: 5% auto; padding: 30px; 
            border-radius: 8px; width: 600px; max-width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); max-height: 85vh; overflow-y: auto;
        }
        .modal-header { display: flex; justify-content: space-between; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 20px;}
        .modal-header h2 { margin: 0; color: #d32f2f; font-size: 22px; }
        .close-btn { font-size: 28px; font-weight: bold; color: #aaa; cursor: pointer; line-height: 1;}
        .close-btn:hover { color: #333; }
        
        .info-group { margin-bottom: 15px; font-size: 15px;}
        .info-group label { font-weight: 600; color: #555; display: inline-block; width: 140px; vertical-align: top;}
        .info-group span { display: inline-block; width: calc(100% - 150px); color: #222; }
        .info-section-title { background: #f8f9fa; padding: 10px; font-weight: bold; margin: 20px 0 15px 0; border-left: 4px solid #007bff; color: #333;}
        
        /* Style cho nút Thao tác */
        .btn-action-sm { 
            padding: 6px 12px; border-radius: 4px; text-decoration: none !important; 
            font-size: 13px; font-weight: bold; color: white !important; 
            display: inline-block; margin: 2px; transition: 0.2s; border: none; cursor: pointer;
        }
        .bg-blue { background-color: #007bff; } .bg-blue:hover { background-color: #0056b3; }
        .bg-orange { background-color: #fd7e14; } .bg-orange:hover { background-color: #e26b0a; }
        .bg-green { background-color: #28a745; } .bg-green:hover { background-color: #1e7e34; }
        .bg-gray { background-color: #6c757d; } .bg-gray:hover { background-color: #5a6268; }
        .bg-danger { background-color: #dc3545; } .bg-danger:hover { background-color: #bd2130; }

        .star-rating { color: #ffc107; font-size: 16px; letter-spacing: 2px; }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <aside class="sidebar">
        <a href="../index.php" style="text-decoration: none;"><div class="sidebar-logo">Charity Events</div></a>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Tổng quan</a></li>
            <li><a href="#">💸 Báo cáo minh bạch (Thu/Chi)</a></li>
            <li><a href="manage_news.php">📝 Đăng tin tức</a></li>
            <li class="active"><a href="tinhnguyen.php">👥 Quản lý tình nguyện viên</a></li>
            <li><a href="settings.php">⚙️ Cài đặt tài khoản</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="dash-header">
            <h1 class="dash-title">Quản lý tình nguyện viên</h1>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-title">TỔNG SỐ TÌNH NGUYỆN VIÊN</div>
                <div class="stat-value"><?= $totalVolunteers ?></div>
            </div>
            <div class="stat-card blue">
                <div class="stat-title">ĐANG CHỜ DUYỆT</div>
                <div class="stat-value"><?= $pendingCount ?></div>
            </div>
            <div class="stat-card green">
                <div class="stat-title">ĐÃ DUYỆT</div>
                <div class="stat-value"><?= $approvedCount ?></div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3>Danh sách tình nguyện viên gần đây</h3>
            </div>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Họ tên / Sự kiện</th>
                        <th>Liên hệ</th>
                        <th>Thời gian đăng ký</th>
                        <th>Trạng thái</th>
                        <th style="text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($volunteers) > 0): ?>
                        <?php foreach ($volunteers as $row): 
                            $status = $row['status'] ?? 'pending';
                            $checked_in = $row['attendance_confirmed'] == 1;
                            $rating = $row['rating'];
                        ?>
                            <tr>
                                <td>
                                    <a href="javascript:void(0)" 
                                       onclick="showVolunteerModal(this)" 
                                       data-info='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'
                                       style="color: #007bff; font-weight: 600; text-decoration: none; font-size: 15px;">
                                        <?= htmlspecialchars($row['fullname'] ?? 'N/A') ?>
                                    </a>
                                    <br>
                                    <small style="color: #666;">Sự kiện: <?= htmlspecialchars($row['event_title'] ?? 'Không rõ') ?></small>
                                </td>
                                <td>
                                    <div style="font-size: 13px;">📞 <?= htmlspecialchars($row['phone'] ?? '') ?></div>
                                    <div style="font-size: 13px;">✉️ <?= htmlspecialchars($row['email'] ?? '') ?></div>
                                </td>
                                <td style="font-size: 13px;">
                                    <?php 
                                        $date = date_create($row['created_at']);
                                        echo date_format($date, "d/m/Y H:i");
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        // Hiển thị Nhãn Trạng Thái
                                        if ($status == 'rejected') {
                                            echo '<span class="badge" style="background:#6c757d; color:#fff;">Đã từ chối</span>';
                                        } elseif ($status == 'pending') {
                                            echo '<span class="badge" style="background:#ffc107; color:#212529;">Chờ duyệt</span>';
                                        } elseif ($status == 'approved') {
                                            if ($checked_in && $rating > 0) {
                                                echo '<span class="badge" style="background:#28a745; color:#fff;">Hoàn thành</span>';
                                            } elseif ($checked_in) {
                                                echo '<span class="badge" style="background:#17a2b8; color:#fff;">Đã có mặt</span>';
                                            } else {
                                                echo '<span class="badge" style="background:#007bff; color:#fff;">Đã duyệt</span>';
                                            }
                                        }
                                    ?>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    
                                    <?php if ($status == 'pending'): ?>
                                        <a href="?action=approve&id=<?= $row['id'] ?>" class="btn-action-sm bg-green" onclick="return confirm('Xác nhận duyệt tình nguyện viên này?');">✅ Duyệt</a>
                                        <a href="?action=reject&id=<?= $row['id'] ?>" class="btn-action-sm bg-danger" onclick="return confirm('Xác nhận từ chối?');">❌ Từ chối</a>
                                    
                                    <?php elseif ($status == 'approved'): ?>
                                        
                                        <?php if (!$checked_in): ?>
                                            <a href="?action=checkin&id=<?= $row['id'] ?>" class="btn-action-sm bg-blue" onclick="return confirm('Xác nhận TNV này đã có mặt tại sự kiện?');">📍 Điểm danh</a>
                                        
                                        <?php elseif (empty($rating)): ?>
                                            <button onclick="openFeedbackModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['fullname'])) ?>')" class="btn-action-sm bg-orange">⭐ Đánh giá</button>
                                        
                                        <?php else: ?>
                                            <div class="star-rating"><?= str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) ?></div>
                                            <button onclick="viewFeedback(this)" data-info='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>' class="btn-action-sm bg-gray" style="margin-top: 5px; font-size: 11px;">👁️ Xem nhận xét</button>
                                        <?php endif; ?>

                                    <?php elseif ($status == 'rejected'): ?>
                                        <span style="color: #999; font-size: 13px; font-style: italic;">Không có thao tác</span>
                                    <?php endif; ?>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #888;">Chưa có tình nguyện viên nào đăng ký chiến dịch của bạn.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="volunteerModal" class="modal-custom">
    <div class="modal-content-custom">
        <div class="modal-header">
            <h2>Chi tiết Tình nguyện viên</h2>
            <span class="close-btn" onclick="document.getElementById('volunteerModal').style.display='none'">&times;</span>
        </div>
        <div id="modalBody">
            <div class="info-section-title">Thông tin cá nhân</div>
            <div class="info-group"><label>Họ và tên:</label> <span id="m_fullname"></span></div>
            <div class="info-group"><label>Sự kiện tham gia:</label> <span id="m_event" style="font-weight:bold; color:#d32f2f;"></span></div>
            <div class="info-group"><label>Giới tính:</label> <span id="m_gender"></span></div>
            <div class="info-group"><label>Ngày sinh:</label> <span id="m_dob"></span></div>
            <div class="info-group"><label>Số điện thoại:</label> <span id="m_phone"></span></div>
            <div class="info-group"><label>Email:</label> <span id="m_email"></span></div>
            <div class="info-group"><label>Nghề nghiệp:</label> <span id="m_occupation"></span></div>

            <div class="info-section-title">Kỹ năng & Kinh nghiệm</div>
            <div class="info-group"><label>Kỹ năng:</label> <span id="m_skills"></span></div>
            <div class="info-group"><label>Kinh nghiệm cũ:</label> <span id="m_experience"></span></div>
            <div class="info-group"><label>Lý do tham gia:</label> <span id="m_motivation"></span></div>
        </div>
    </div>
</div>

<div id="feedbackModal" class="modal-custom">
    <div class="modal-content-custom" style="width: 500px;">
        <div class="modal-header">
            <h2 style="color: #fd7e14;">⭐ Đánh giá Tình nguyện viên</h2>
            <span class="close-btn" onclick="document.getElementById('feedbackModal').style.display='none'">&times;</span>
        </div>
        <form method="POST" action="tinhnguyen.php">
            <input type="hidden" name="action" value="submit_feedback">
            <input type="hidden" name="volunteer_id" id="fb_volunteer_id">
            
            <p style="margin-top:0; color: #555;">Đang đánh giá: <strong id="fb_fullname" style="color:#000;"></strong></p>
            
            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight: bold; margin-bottom: 5px;">Mức độ hoàn thành (1-5 Sao):</label>
                <select name="rating" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; font-size: 15px;" required>
                    <option value="5">⭐⭐⭐⭐⭐ (Xuất sắc - Rất nhiệt tình)</option>
                    <option value="4">⭐⭐⭐⭐ (Tốt - Hoàn thành nhiệm vụ)</option>
                    <option value="3">⭐⭐⭐ (Khá - Cần cố gắng hơn)</option>
                    <option value="2">⭐⭐ (Trung bình - Đi muộn/Thiếu tập trung)</option>
                    <option value="1">⭐ (Kém - Vi phạm kỷ luật sự kiện)</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight: bold; margin-bottom: 5px;">Nhận xét chi tiết (organizer_feedback):</label>
                <textarea name="organizer_feedback" rows="4" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; font-family: inherit; box-sizing: border-box;" placeholder="Vd: Bạn làm việc rất tốt, hòa đồng và hỗ trợ ban tổ chức nhiệt tình..."></textarea>
            </div>

            <button type="submit" class="btn-action-sm bg-orange" style="width: 100%; padding: 12px; font-size: 16px;">LƯU ĐÁNH GIÁ</button>
        </form>
    </div>
</div>

<div id="viewFeedbackModal" class="modal-custom">
    <div class="modal-content-custom" style="width: 500px;">
        <div class="modal-header">
            <h2 style="color: #28a745;">Phiếu Đánh Giá</h2>
            <span class="close-btn" onclick="document.getElementById('viewFeedbackModal').style.display='none'">&times;</span>
        </div>
        <div>
            <div class="info-group"><label>Tình nguyện viên:</label> <span id="vf_fullname" style="font-weight: bold;"></span></div>
            <div class="info-group"><label>Số sao:</label> <span id="vf_rating" class="star-rating"></span></div>
            <div class="info-group" style="margin-top: 15px;">
                <label style="display: block; width: 100%; margin-bottom: 5px;">Lời nhận xét từ Ban tổ chức:</label>
                <div id="vf_feedback" style="background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #eee; min-height: 80px; font-style: italic;"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Mở Modal Xem chi tiết (Đã có sẵn)
function showVolunteerModal(element) {
    const data = JSON.parse(element.getAttribute('data-info'));
    document.getElementById('m_fullname').innerText = data.fullname || 'Chưa cập nhật';
    document.getElementById('m_event').innerText = data.event_title || 'Không rõ';
    document.getElementById('m_phone').innerText = data.phone || 'Chưa cập nhật';
    document.getElementById('m_email').innerText = data.email || 'Chưa cập nhật';
    document.getElementById('m_occupation').innerText = data.occupation || 'Chưa cập nhật';
    document.getElementById('m_experience').innerText = data.experience || 'Chưa cập nhật';
    document.getElementById('m_motivation').innerText = data.motivation || 'Chưa cập nhật';
    
    let gender = 'Chưa cập nhật';
    if(data.gender === 'male') gender = 'Nam';
    else if(data.gender === 'female') gender = 'Nữ';
    else if(data.gender === 'other') gender = 'Khác';
    document.getElementById('m_gender').innerText = gender;

    let dob = data.birth_date || data.date_of_birth || 'Chưa cập nhật';
    if(dob !== 'Chưa cập nhật') {
        let d = new Date(dob);
        dob = d.toLocaleDateString('vi-VN');
    }
    document.getElementById('m_dob').innerText = dob;

    let skills = 'Không có';
    if(data.skills) {
        try {
            let skillArray = JSON.parse(data.skills);
            if(Array.isArray(skillArray)) skills = skillArray.join(', ');
            else skills = data.skills; 
        } catch(e) { skills = data.skills; }
    }
    document.getElementById('m_skills').innerText = skills;
    document.getElementById('volunteerModal').style.display = 'block';
}

// Mở Modal Nhập Đánh giá
function openFeedbackModal(id, fullname) {
    document.getElementById('fb_volunteer_id').value = id;
    document.getElementById('fb_fullname').innerText = fullname;
    document.getElementById('feedbackModal').style.display = 'block';
}

// Mở Modal Xem Đánh giá
function viewFeedback(element) {
    const data = JSON.parse(element.getAttribute('data-info'));
    document.getElementById('vf_fullname').innerText = data.fullname;
    
    // Tạo sao
    let rating = parseInt(data.rating);
    let stars = '★'.repeat(rating) + '☆'.repeat(5 - rating);
    document.getElementById('vf_rating').innerText = stars;
    
    document.getElementById('vf_feedback').innerText = data.organizer_feedback || 'Không có nhận xét chi tiết.';
    
    document.getElementById('viewFeedbackModal').style.display = 'block';
}

// Đóng modal khi bấm nền đen
window.onclick = function(event) {
    if (event.target.className === 'modal-custom') {
        event.target.style.display = "none";
    }
}
</script>

</body>
</html>