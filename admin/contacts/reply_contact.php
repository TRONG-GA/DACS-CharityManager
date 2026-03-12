<?php
/**
 * Admin - Reply Contact
 * Trả lời liên hệ
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$contactId = $_GET['id'] ?? 0;

// Get contact info
$stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
$stmt->execute([$contactId]);
$contact = $stmt->fetch();

if (!$contact) {
    setFlashMessage('error', 'Không tìm thấy liên hệ');
    header('Location: contacts.php');
    exit;
}

$errors = [];

// Handle reply
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (empty($subject)) {
        $errors[] = 'Chủ đề không được để trống';
    }
    
    if (empty($message)) {
        $errors[] = 'Nội dung không được để trống';
    }
    
    if (empty($errors)) {
        try {
            // Send email
            $emailSent = sendEmail(
                $contact['email'],
                $subject,
                $message
            );
            
            if ($emailSent) {
                // Update contact status
                $stmt = $pdo->prepare("
                    UPDATE contacts SET 
                        status = 'resolved',
                        replied_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$contactId]);
                
                setFlashMessage('success', 'Đã gửi email trả lời');
                header('Location: contacts.php');
                exit;
            } else {
                $errors[] = 'Không thể gửi email. Vui lòng thử lại.';
            }
            
        } catch (Exception $e) {
            $errors[] = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trả lời liên hệ - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <a href="contacts.php" class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        Trả lời liên hệ
                    </h1>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <!-- Original Message -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-envelope-open"></i> Tin nhắn gốc</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Từ:</strong> <?= htmlspecialchars($contact['name']) ?>
                                    (<?= htmlspecialchars($contact['email']) ?>)
                                </div>
                                <div class="mb-2">
                                    <strong>Chủ đề:</strong> <?= htmlspecialchars($contact['subject']) ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Ngày gửi:</strong> <?= formatDate($contact['created_at'], 'd/m/Y H:i') ?>
                                </div>
                                <hr>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(htmlspecialchars($contact['message'])) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Reply Form -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-reply"></i> Trả lời</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Đến</label>
                                        <input type="text" class="form-control" 
                                               value="<?= htmlspecialchars($contact['email']) ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Chủ đề <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="subject" 
                                               value="Re: <?= htmlspecialchars($contact['subject']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="message" rows="10" 
                                                  placeholder="Nhập nội dung email..." required></textarea>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="contacts.php" class="btn btn-secondary">
                                            <i class="bi bi-x-lg"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-send"></i> Gửi email
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Email Templates -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-file-text"></i> Mẫu trả lời</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-secondary text-start" 
                                            onclick="insertTemplate('thank_you')">
                                        Cảm ơn đã liên hệ
                                    </button>
                                    <button class="btn btn-outline-secondary text-start" 
                                            onclick="insertTemplate('more_info')">
                                        Cần thêm thông tin
                                    </button>
                                    <button class="btn btn-outline-secondary text-start" 
                                            onclick="insertTemplate('resolved')">
                                        Đã giải quyết
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const templates = {
        thank_you: `Xin chào <?= htmlspecialchars($contact['name']) ?>,

Cảm ơn bạn đã liên hệ với chúng tôi!

Chúng tôi đã nhận được tin nhắn của bạn và sẽ xử lý trong thời gian sớm nhất.

Nếu có bất kỳ thắc mắc nào, đừng ngần ngại liên hệ lại.

Trân trọng,
<?= SITE_NAME ?>`,
        
        more_info: `Xin chào <?= htmlspecialchars($contact['name']) ?>,

Cảm ơn bạn đã liên hệ!

Để chúng tôi có thể hỗ trợ bạn tốt hơn, vui lòng cung cấp thêm một số thông tin:

- [Thông tin cần bổ sung]

Chúng tôi mong nhận được phản hồi từ bạn.

Trân trọng,
<?= SITE_NAME ?>`,
        
        resolved: `Xin chào <?= htmlspecialchars($contact['name']) ?>,

Chúng tôi đã xử lý yêu cầu của bạn.

[Chi tiết giải quyết]

Nếu còn bất kỳ thắc mắc nào, đừng ngần ngại liên hệ lại.

Trân trọng,
<?= SITE_NAME ?>`
    };

    function insertTemplate(type) {
        const textarea = document.querySelector('[name="message"]');
        textarea.value = templates[type];
    }
    </script>
</body>
</html>
