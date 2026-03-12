<?php
require_once '../config/db.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: ' . BASE_URL . '/news/news.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT n.*, u.fullname as author_name
    FROM news n
    JOIN users u ON n.author_id = u.id
    WHERE n.slug = ? AND n.status = 'published'
");
$stmt->execute([$slug]);
$news = $stmt->fetch();

if (!$news) {
    header('Location: ' . BASE_URL . '/news/news.php');
    exit;
}

// Update views
$pdo->prepare("UPDATE news SET views = views + 1 WHERE id = ?")->execute([$news['id']]);

$pageTitle = sanitize($news['title']) . ' - ' . SITE_NAME;

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/news/news.php">Tin tức</a></li>
                    <li class="breadcrumb-item active"><?= sanitize(substr($news['title'], 0, 50)) ?>...</li>
                </ol>
            </nav>

            <article class="bg-white p-4 rounded shadow-sm">
                <h1 class="mb-3"><?= sanitize($news['title']) ?></h1>

                <div class="d-flex justify-content-between text-muted mb-4">
                    <span><i class="fas fa-user me-2"></i><?= sanitize($news['author_name']) ?></span>
                    <span><i class="fas fa-calendar me-2"></i><?= date('d/m/Y H:i', strtotime($news['published_at'])) ?></span>
                    <span><i class="fas fa-eye me-2"></i><?= number_format($news['views']) ?> lượt xem</span>
                </div>

                <img src="<?= BASE_URL ?>/public/uploads/news/<?= $news['thumbnail'] ?>" 
                     class="img-fluid rounded mb-4 w-100"
                     alt="<?= sanitize($news['title']) ?>"
                     style="max-height: 400px; object-fit: cover;">

                <div class="lead mb-4"><?= sanitize($news['summary']) ?></div>

                <div class="news-content">
                    <?= $news['content'] ?>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center">
                    <a href="<?= BASE_URL ?>/news/news.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                    <div>
                        <strong>Chia sẻ:</strong>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']) ?>" target="_blank" class="btn btn-sm btn-primary ms-2">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($news['title']) ?>" target="_blank" class="btn btn-sm btn-info ms-1">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>