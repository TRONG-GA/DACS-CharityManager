<?php
// benefactor/ledger.php - FIXED with REQUIRED RECEIPT
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';

requireBenefactorVerified();
 
$event_id = (int)($_GET['id'] ?? 0);
if (!$event_id) {
    die("Event not found");
}
 
// Get event info
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
$stmt->execute([$event_id, $_SESSION['user_id']]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Access denied");
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    // KIỂM TRA BẮT BUỘC PHẢI CÓ BIÊN LAI
    if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Vui lòng tải lên biên lai/hóa đơn để ghi nhận khoản chi!';
    } else {
        $amount = str_replace(['.', ','], '', $_POST['amount']);
        $desc = sanitize($_POST['description']);
        $date = $_POST['expense_date'];
        
        // Upload receipt
        $receipt = uploadImage($_FILES['receipt'], 'receipts');
        
        if ($receipt) {
            $sql = "INSERT INTO event_expenses (event_id, amount, description, expense_date, receipt_file) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $pdo->prepare($sql);
            $stmtInsert->execute([$event_id, $amount, $desc, $date, $receipt]);
            
            $success = 'Đã thêm khoản chi thành công!';
            
            // Redirect to refresh page
            header("Location: ledger.php?id=$event_id&success=1");
            exit;
        } else {
            $error = 'Có lỗi khi tải lên biên lai. Vui lòng thử lại!';
        }
    }
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $success = 'Đã thêm khoản chi thành công!';
}
 
// Get total income (Thu)
$stmtThu = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM donations WHERE event_id = ? AND status = 'completed'");
$stmtThu->execute([$event_id]);
$resultThu = $stmtThu->fetch(PDO::FETCH_ASSOC);
$thu = $resultThu['total'] ?? 0;

// Get total expenses (Chi)
$stmtChi = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM event_expenses WHERE event_id = ?");
$stmtChi->execute([$event_id]);
$resultChi = $stmtChi->fetch(PDO::FETCH_ASSOC);
$chi = $resultChi['total'] ?? 0;

// Get expense list
$stmtExpenses = $pdo->prepare("SELECT * FROM event_expenses WHERE event_id = ? ORDER BY expense_date DESC, created_at DESC");
$stmtExpenses->execute([$event_id]);
$expenses = $stmtExpenses->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sổ Thu Chi - <?= htmlspecialchars($event['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .required-asterisk {
            color: red;
            font-weight: bold;
        }
        .receipt-preview {
            max-width: 100px;
            max-height: 100px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-receipt"></i> Sổ Thu Chi: <?= htmlspecialchars($event['title']) ?></h4>
                    </div>
                    <div class="card-body">
                        <!-- Alert Messages -->
                        <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Summary -->
                        <div class="row text-center mb-4">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6>Thu</h6>
                                        <h4><?= number_format($thu, 0, ',', '.') ?> VNĐ</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6>Chi</h6>
                                        <h4><?= number_format($chi, 0, ',', '.') ?> VNĐ</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6>Tồn</h6>
                                        <h4><?= number_format($thu - $chi, 0, ',', '.') ?> VNĐ</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Expense Form -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Thêm Chi Tiêu</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="bi bi-info-circle"></i> 
                                    <strong>Lưu ý:</strong> Bắt buộc phải có biên lai/hóa đơn kèm theo để đảm bảo tính minh bạch.
                                </div>
                                
                                <form method="POST" enctype="multipart/form-data" id="expenseForm">
                                    <?= csrfField() ?>
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Nội dung chi tiêu <span class="required-asterisk">*</span>
                                        </label>
                                        <input type="text" name="description" class="form-control" required 
                                               placeholder="VD: Mua vật tư y tế, chi phí vận chuyển, thuê địa điểm...">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                Số tiền (VNĐ) <span class="required-asterisk">*</span>
                                            </label>
                                            <input type="number" name="amount" class="form-control" required 
                                                   placeholder="0" min="1">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                Ngày chi <span class="required-asterisk">*</span>
                                            </label>
                                            <input type="date" name="expense_date" class="form-control" 
                                                   value="<?= date('Y-m-d') ?>" required max="<?= date('Y-m-d') ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-file-earmark-image"></i> 
                                            Biên lai/Hóa đơn <span class="required-asterisk">*</span>
                                        </label>
                                        <input type="file" name="receipt" class="form-control" 
                                               accept="image/*,.pdf" required id="receiptInput">
                                        <small class="text-muted">
                                            Chấp nhận: JPG, PNG, PDF (Tối đa 5MB). <strong class="text-danger">BẮT BUỘC</strong>
                                        </small>
                                        <div id="receiptPreview"></div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Thêm Chi Tiêu
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i> Hủy
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Expense List -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Danh Sách Chi Tiêu</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($expenses) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Ngày</th>
                                                <th>Nội dung</th>
                                                <th class="text-end">Số tiền</th>
                                                <th class="text-center">Biên lai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($expenses as $exp): ?>
                                            <tr>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y', strtotime($exp['expense_date'])) ?>
                                                    </small>
                                                </td>
                                                <td><?= htmlspecialchars($exp['description']) ?></td>
                                                <td class="text-end text-danger">
                                                    <strong>-<?= number_format($exp['amount'], 0, ',', '.') ?> VNĐ</strong>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (!empty($exp['receipt_file'])): ?>
                                                    <a href="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($exp['receipt_file']) ?>" 
                                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-file-earmark-text"></i> Xem
                                                    </a>
                                                    <?php else: ?>
                                                    <span class="text-danger">
                                                        <i class="bi bi-exclamation-triangle"></i> Thiếu
                                                    </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-active">
                                                <td colspan="2"><strong>Tổng Chi</strong></td>
                                                <td class="text-end text-danger">
                                                    <strong>-<?= number_format($chi, 0, ',', '.') ?> VNĐ</strong>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-muted text-center py-4">
                                    <i class="bi bi-inbox"></i> Chưa có khoản chi nào
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="http://localhost/DACS-CharityManager/benefactor/dashboard.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview receipt image before upload
        document.getElementById('receiptInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('receiptPreview');
            
            if (file) {
                const fileType = file.type;
                const fileSize = file.size / 1024 / 1024; // MB
                
                // Check file size
                if (fileSize > 5) {
                    alert('File quá lớn! Vui lòng chọn file nhỏ hơn 5MB.');
                    e.target.value = '';
                    preview.innerHTML = '';
                    return;
                }
                
                // Preview image
                if (fileType.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = `<img src="${e.target.result}" class="receipt-preview" alt="Preview">`;
                    };
                    reader.readAsDataURL(file);
                } else if (fileType === 'application/pdf') {
                    preview.innerHTML = '<p class="text-success mt-2"><i class="bi bi-file-pdf"></i> File PDF đã chọn: ' + file.name + '</p>';
                } else {
                    preview.innerHTML = '';
                }
            }
        });

        // Confirm before submit
        document.getElementById('expenseForm').addEventListener('submit', function(e) {
            const amount = document.querySelector('input[name="amount"]').value;
            const description = document.querySelector('input[name="description"]').value;
            const receipt = document.querySelector('input[name="receipt"]').files[0];
            
            if (!receipt) {
                e.preventDefault();
                alert('Vui lòng tải lên biên lai/hóa đơn!');
                return false;
            }
            
            if (!confirm(`Xác nhận thêm khoản chi:\n${description}\nSố tiền: ${parseInt(amount).toLocaleString('vi-VN')} VNĐ`)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>