al · PHP
Copy

<?php
// benefactor/ledger.php - UPDATED
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
requireBenefactorVerified();
 
$event_id = (int)($_GET['id'] ?? 0);
if (!$event_id) die("Event not found");
 
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
$stmt->execute([$event_id, $_SESSION['user_id']]);
$event = $stmt->fetch();
if (!$event) die("Access denied");
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $amount = str_replace(['.', ','], '', $_POST['amount']);
    $desc = sanitize($_POST['description']);
    $date = $_POST['expense_date'];
    $receipt = null;
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $receipt = uploadImage($_FILES['receipt'], 'receipts');
    }
    $sql = "INSERT INTO event_expenses (event_id, amount, description, expense_date, receipt_file) VALUES (?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$event_id, $amount, $desc, $date, $receipt]);
    header("Location: ledger.php?id=$event_id");
    exit;
}
 
$thu = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as t FROM donations WHERE event_id = ? AND status = 'completed'")->execute([$event_id])->fetch()['t'];
$chi = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as t FROM event_expenses WHERE event_id = ?")->execute([$event_id])->fetch()['t'];
?>
<!DOCTYPE html>
<html>
<head><title>Ledger</title></head>
<body>
<h1>Thu/Chi: <?= $event['title'] ?></h1>
<p>Thu: <?= formatMoney($thu) ?> | Chi: <?= formatMoney($chi) ?> | Tồn: <?= formatMoney($thu - $chi) ?></p>
<form method="POST" enctype="multipart/form-data">
<?= csrfField() ?>
<input name="description" required placeholder="Nội dung">
<input type="number" name="amount" required>
<input type="date" name="expense_date" value="<?= date('Y-m-d') ?>">
<input type="file" name="receipt">
<button>Thêm Chi</button>
</form>
</body>
</html>
 