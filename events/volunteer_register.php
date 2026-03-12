<?php
require_once '../config/db.php';

$eventId = $_GET['event_id'] ?? 0;

// Get event info
$stmt = $pdo->prepare("
    SELECT e.*, 
           (SELECT COUNT(*) FROM event_volunteers WHERE event_id = e.id) as registered
    FROM events e
    WHERE e.id = ? AND e.status = 'approved'
");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event || $event['volunteer_needed'] <= 0) {
    header('Location: ' . BASE_URL . '/events/events.php');
    exit;
}

// Check if full
if ($event['registered'] >= $event['volunteer_needed']) {
    setFlashMessage('error', 'Sự kiện đã đủ số lượng tình nguyện viên!');
    header('Location: ' . BASE_URL . '/events/event_detail.php?slug=' . $event['slug']);
    exit;
}

$pageTitle = 'Đăng ký tình nguyện - ' . sanitize($event['title']);

// FIX: Đổi tên biến để tránh conflict
$alertMessage = null;
if (isset($_GET['error'])) {
    $alertMessage = ['type' => 'error', 'message' => urldecode($_GET['error'])];
} elseif (isset($_GET['success'])) {
    $alertMessage = ['type' => 'success', 'message' => urldecode($_GET['success'])];
} else {
    $alertMessage = getFlashMessage(); // Fallback to session
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
.volunteer-page { min-height: 100vh; padding: 60px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.volunteer-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); padding: 40px; max-width: 700px; margin: 0 auto; }
.skill-badge { display: inline-block; background: #e7f3ff; color: #0d6efd; padding: 5px 15px; border-radius: 20px; margin: 5px; font-size: 0.9rem; cursor: pointer; border: 2px solid transparent; }
.skill-badge.selected { background: #0d6efd; color: white; border-color: #0d6efd; }
</style>

<!-- DEBUG: Kiểm tra URL parameters -->


<div class="volunteer-page">
    <div class="container">
        <div class="volunteer-card">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="fas fa-hands-helping fa-3x text-primary"></i>
                </div>
                <h2>Đăng ký tình nguyện viên</h2>
                <h5 class="text-muted"><?= sanitize($event['title']) ?></h5>
                <p class="text-muted">
                    <i class="fas fa-users me-2"></i>
                    Đã có <?= $event['registered'] ?>/<?= $event['volunteer_needed'] ?> người đăng ký
                </p>
            </div>
            
            <?php if ($alertMessage): ?>
            <div class="alert alert-<?= $alertMessage['type'] == 'error' ? 'danger' : $alertMessage['type'] ?> alert-dismissible fade show">
                <?= $alertMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form action="<?= BASE_URL ?>/events/process_volunteer.php" method="POST" id="volunteerForm">
                <?= csrfField() ?>
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Vui lòng điền đầy đủ thông tin để chúng tôi có thể liên hệ và sắp xếp công việc phù hợp.
                </div>
                
                <!-- Personal Info -->
                <h5 class="mb-3">
                    <i class="fas fa-user text-primary me-2"></i>Thông tin cá nhân
                </h5>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Họ và tên <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="fullname" 
                           class="form-control" 
                           placeholder="Nguyễn Văn A"
                           required
                           value="<?= isLoggedIn() ? sanitize($_SESSION['fullname']) : '' ?>">
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="email@example.com"
                               required
                               value="<?= isLoggedIn() ? sanitize($_SESSION['email']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Số điện thoại <span class="text-danger">*</span>
                        </label>
                        <input type="tel" 
                               name="phone" 
                               class="form-control" 
                               placeholder="0987654321"
                               required
                               pattern="[0-9]{10}">
                    </div>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Giới tính <span class="text-danger">*</span>
                        </label>
                        <select name="gender" class="form-select" required>
                            <option value="">Chọn giới tính</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Ngày sinh
                        </label>
                        <input type="date" 
                               name="birth_date" 
                               class="form-control"
                               max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Nghề nghiệp / Học sinh - Sinh viên
                    </label>
                    <input type="text" 
                           name="occupation" 
                           class="form-control" 
                           placeholder="VD: Sinh viên, Nhân viên văn phòng...">
                </div>
                
                <!-- Skills -->
                <h5 class="mb-3 mt-4">
                    <i class="fas fa-star text-primary me-2"></i>Kỹ năng & Kinh nghiệm
                </h5>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Kỹ năng bạn có (chọn nhiều)
                    </label>
                    <div id="skillsContainer">
                        <?php
                        $skillsList = [
                            'Nấu ăn', 'Y tế cơ bản', 'Sơ cấp cứu', 
                            'Dạy học', 'Tiếng Anh', 'Tin học',
                            'Lái xe', 'Xây dựng', 'Sửa chữa điện',
                            'Chăm sóc trẻ em', 'Chăm sóc người già',
                            'Tổ chức sự kiện', 'Nhiếp ảnh', 'Quay phim',
                            'Văn nghệ', 'MC', 'Kế toán'
                        ];
                        
                        foreach ($skillsList as $skill):
                        ?>
                        <span class="skill-badge" onclick="toggleSkill(this)">
                            <?= $skill ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="skills" id="selectedSkills">
                    <small class="text-muted">Click để chọn kỹ năng</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Kinh nghiệm tình nguyện (nếu có)
                    </label>
                    <textarea name="experience" 
                              class="form-control" 
                              rows="3"
                              placeholder="VD: Đã tham gia tình nguyện tại..."></textarea>
                </div>
                
                <!-- Motivation -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Lý do bạn muốn tham gia <span class="text-danger">*</span>
                    </label>
                    <textarea name="motivation" 
                              class="form-control" 
                              rows="4"
                              required
                              placeholder="Chia sẻ lý do bạn muốn tham gia hoạt động tình nguyện này..."></textarea>
                </div>
                
                <!-- Availability -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Thời gian bạn có thể tham gia
                    </label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="available_weekday" id="weekday">
                        <label class="form-check-label" for="weekday">
                            Ngày thường (Thứ 2-6)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="available_weekend" id="weekend">
                        <label class="form-check-label" for="weekend">
                            Cuối tuần (Thứ 7, CN)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="available_evening" id="evening">
                        <label class="form-check-label" for="evening">
                            Buổi tối
                        </label>
                    </div>
                </div>
                
                <!-- Agree Terms -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="agree_terms" id="agreeTerms" required>
                        <label class="form-check-label" for="agreeTerms">
                            Tôi cam kết tuân thủ quy định và hoàn thành công việc được giao
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>
                
                <!-- Submit -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-hand-paper me-2"></i>Đăng ký làm tình nguyện viên
                    </button>
                    <a href="<?= BASE_URL ?>/events/event_detail.php?slug=<?= $event['slug'] ?>" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Skills selection
function toggleSkill(element) {
    element.classList.toggle('selected');
    updateSelectedSkills();
}

function updateSelectedSkills() {
    const selected = [];
    document.querySelectorAll('.skill-badge.selected').forEach(badge => {
        selected.push(badge.textContent.trim());
    });
    document.getElementById('selectedSkills').value = JSON.stringify(selected);
}

// Phone validation
document.querySelector('input[name="phone"]').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

<?php include '../includes/footer.php'; ?>