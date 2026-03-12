<?php
require_once '../config/db.php';
$pageTitle = 'Tìm kiếm sự kiện - ' . SITE_NAME;

$keyword = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';
$province = $_GET['province'] ?? '';

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$whereCount = ["status = 'approved'"];
$whereSelect = ["e.status = 'approved'"];
$params = [];

if (!empty($keyword)) {
    $whereCount[] = "(title LIKE ? OR description LIKE ?)";
    $whereSelect[] = "(e.title LIKE ? OR e.description LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

if (!empty($category)) {
    $whereCount[] = "category = ?";
    $whereSelect[] = "e.category = ?";
    $params[] = $category;
}

if (!empty($province)) {
    $whereCount[] = "province = ?";
    $whereSelect[] = "e.province = ?";
    $params[] = $province;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE " . implode(' AND ', $whereCount));
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("
    SELECT e.*, u.fullname as organizer_name,
           DATEDIFF(e.end_date, NOW()) as days_left,
           (SELECT COUNT(*) FROM donations WHERE event_id = e.id) as donor_count
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE " . implode(' AND ', $whereSelect) . "
    ORDER BY e.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$events = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Tìm kiếm sự kiện</h1>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" placeholder="Tìm kiếm..." value="<?= sanitize($keyword) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">Tất cả danh mục</option>
                            <option value="medical" <?= $category === 'medical' ? 'selected' : '' ?>>Y tế</option>
                            <option value="education" <?= $category === 'education' ? 'selected' : '' ?>>Giáo dục</option>
                            <option value="disaster" <?= $category === 'disaster' ? 'selected' : '' ?>>Thiên tai</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="province" class="form-control" placeholder="Tỉnh/TP" value="<?= sanitize($province) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-danger w-100"><i class="fas fa-search"></i> Tìm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <p><strong>Tìm thấy <?= $total ?> kết quả</strong></p>

    <?php if (empty($events)): ?>
        <div class="alert alert-info">Không tìm thấy sự kiện nào</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($events as $event): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <img src="<?= BASE_URL ?>/public/uploads/events/<?= $event['thumbnail'] ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5><?= sanitize($event['title']) ?></h5>
                        <p class="text-muted small"><?= sanitize(substr($event['description'], 0, 100)) ?>...</p>
                        <a href="<?= BASE_URL ?>/events/event_detail.php?slug=<?= $event['slug'] ?>" class="btn btn-outline-primary btn-sm">Xem chi tiết</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
