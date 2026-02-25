<?php
require_once 'config/db.php';

$pageTitle = 'Trang chủ - Charity Event | Nền tảng kết nối từ thiện minh bạch';
$pageDescription = 'Kết nối những tấm lòng nhân ái, mang yêu thương đến mọi miền đất nước. Quyên góp minh bạch, tình nguyện ý nghĩa.';

// Get statistics
$stats = getStatistics();

// Get featured/breaking news for hero slider (auto-rotate)
$heroNewsQuery = $pdo->prepare("
    SELECT n.*, u.fullname as author_name
    FROM news n
    JOIN users u ON n.author_id = u.id
    WHERE n.status = 'published' 
    AND (n.is_featured = 1 OR n.is_breaking = 1)
    ORDER BY n.is_breaking DESC, n.created_at DESC
    LIMIT 5
");
$heroNewsQuery->execute();
$heroNews = $heroNewsQuery->fetchAll();

// Get urgent/featured events
$urgentEventsQuery = $pdo->prepare("
    SELECT e.*, u.fullname as organizer_name,
           (SELECT COUNT(*) FROM donations WHERE event_id = e.id AND status = 'completed') as donor_count,
           DATEDIFF(e.end_date, CURDATE()) as days_left
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.status = 'approved' 
    AND e.end_date >= CURDATE()
    AND (e.is_featured = 1 OR e.is_urgent = 1)
    ORDER BY e.is_urgent DESC, e.priority DESC, e.created_at DESC
    LIMIT 6
");
$urgentEventsQuery->execute();
$urgentEvents = $urgentEventsQuery->fetchAll();

// Get latest events (ongoing)
$latestEventsQuery = $pdo->prepare("
    SELECT e.*, u.fullname as organizer_name,
           (SELECT COUNT(*) FROM donations WHERE event_id = e.id AND status = 'completed') as donor_count
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.status = 'approved' 
    AND e.start_date <= CURDATE()
    AND e.end_date >= CURDATE()
    ORDER BY e.created_at DESC
    LIMIT 6
");
$latestEventsQuery->execute();
$latestEvents = $latestEventsQuery->fetchAll();

// Get latest news articles
$latestNewsQuery = $pdo->prepare("
    SELECT n.*, u.fullname as author_name
    FROM news n
    JOIN users u ON n.author_id = u.id
    WHERE n.status = 'published'
    ORDER BY n.created_at DESC
    LIMIT 6
");
$latestNewsQuery->execute();
$latestNewsArticles = $latestNewsQuery->fetchAll();

// Get event categories statistics
$categoriesQuery = $pdo->query("
    SELECT category, COUNT(*) as count
    FROM events
    WHERE status = 'approved' AND end_date >= CURDATE()
    GROUP BY category
    ORDER BY count DESC
");
$categories = $categoriesQuery->fetchAll();

// Get recent donors (for transparency)
$recentDonorsQuery = $pdo->prepare("
    SELECT d.*, e.title as event_title, e.slug as event_slug
    FROM donations d
    JOIN events e ON d.event_id = e.id
    WHERE d.status = 'completed' AND d.is_anonymous = 0
    ORDER BY d.created_at DESC
    LIMIT 10
");
$recentDonorsQuery->execute();
$recentDonors = $recentDonorsQuery->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero Section with Auto-Rotating News Slider -->
<section class="hero-slider-section">
    <div class="container-fluid px-0">
        <?php if (!empty($heroNews)): ?>
        <!-- Swiper Slider -->
        <div class="swiper hero-news-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($heroNews as $news): ?>
                <div class="swiper-slide">
                    <div class="hero-slide-item" style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6)), url('<?= BASE_URL ?>/public/uploads/<?= $news['thumbnail'] ?>');">
                        <div class="container h-100">
                            <div class="row h-100 align-items-center">
                                <div class="col-lg-8">
                                    
                                    
                                    <h1 class="display-4 fw-bold text-white mb-3 fade-in">
                                        <?= sanitize($news['title']) ?>
                                    </h1>
                                    
                                    <p class="lead text-white mb-4 fade-in" style="animation-delay: 0.2s;">
                                        <?= sanitize(substr($news['excerpt'] ?? strip_tags($news['content']), 0, 200)) ?>...
                                    </p>
                                    
                                    <div class="fade-in" style="animation-delay: 0.4s;">
                                        <a href="<?= BASE_URL ?>/news_detail.php?slug=<?= $news['slug'] ?>" 
                                           class="btn btn-light btn-lg me-3">
                                            <i class="fas fa-book-open me-2"></i>Đọc thêm
                                        </a>
                                        <a href="<?= BASE_URL ?>/events.php" class="btn btn-danger btn-lg">
                                            <i class="fas fa-heart me-2"></i>Quyên góp ngay
                                        </a>
                                    </div>
                                    
                                    <div class="mt-4 text-white-50 small fade-in" style="animation-delay: 0.6s;">
                                        <i class="fas fa-user me-2"></i><?= sanitize($news['author_name']) ?>
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-calendar me-2"></i><?= timeAgo($news['created_at']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Navigation -->
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            
            <!-- Progress Bar -->
            
        </div>
        <?php else: ?>
        <!-- Fallback hero if no news -->
        <div class="hero-fallback">
            <div class="container text-center text-white py-5">
                <h1 class="display-3 fw-bold mb-4">Chung Tay Vì Cộng Đồng</h1>
                <p class="lead mb-4">Kết nối những tấm lòng nhân ái - Lan tỏa yêu thương khắp Việt Nam</p>
                <a href="<?= BASE_URL ?>/events.php" class="btn btn-light btn-lg">
                    <i class="fas fa-hands-helping me-2"></i>Khám phá các chiến dịch
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats-section py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="stat-box text-center p-4 bg-white rounded shadow-sm h-100">
                    <div class="stat-icon text-danger mb-3">
                        <i class="fas fa-hands-helping fa-3x"></i>
                    </div>
                    <h3 class="stat-number text-danger fw-bold mb-2" data-target="<?= $stats['total_events'] ?>">0</h3>
                    <p class="stat-label text-muted mb-0">Chiến dịch</p>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="stat-box text-center p-4 bg-white rounded shadow-sm h-100">
                    <div class="stat-icon text-success mb-3">
                        <i class="fas fa-donate fa-3x"></i>
                    </div>
                    <h3 class="stat-number text-success fw-bold mb-2"><?= formatMoney($stats['total_donations']) ?></h3>
                    <p class="stat-label text-muted mb-0">Đã quyên góp</p>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="stat-box text-center p-4 bg-white rounded shadow-sm h-100">
                    <div class="stat-icon text-primary mb-3">
                        <i class="fas fa-users fa-3x"></i>
                    </div>
                    <h3 class="stat-number text-primary fw-bold mb-2" data-target="<?= $stats['total_donors'] ?>">0</h3>
                    <p class="stat-label text-muted mb-0">Nhà hảo tâm</p>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="stat-box text-center p-4 bg-white rounded shadow-sm h-100">
                    <div class="stat-icon text-warning mb-3">
                        <i class="fas fa-user-friends fa-3x"></i>
                    </div>
                    <h3 class="stat-number text-warning fw-bold mb-2" data-target="<?= $stats['total_volunteers'] ?>">0</h3>
                    <p class="stat-label text-muted mb-0">Tình nguyện viên</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Urgent Events Section -->
<?php if (!empty($urgentEvents)): ?>
<section class="urgent-events-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title fw-bold">
                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                Chiến dịch khẩn cấp
            </h2>
            <p class="section-subtitle text-muted">Những hoàn cảnh đang rất cần sự giúp đỡ</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($urgentEvents as $event): 
                $progress = ($event['target_amount'] > 0) ? ($event['current_amount'] / $event['target_amount'] * 100) : 0;
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card event-card">
                    <div class="position-relative">
                        <img src="<?= BASE_URL ?>/public/uploads/<?= $event['thumbnail'] ?>" 
                             class="card-img-top" 
                             alt="<?= sanitize($event['title']) ?>">
                        
                        <?php if ($event['is_urgent']): ?>
                        <span class="event-badge badge bg-danger">
                            <i class="fas fa-bolt me-1"></i>KHẨN CẤP
                        </span>
                        <?php endif; ?>
                        
                        <?php if ($event['days_left'] <= 7): ?>
                        <span class="event-badge badge bg-warning text-dark" style="top: 50px;">
                            <i class="fas fa-clock me-1"></i>Còn <?= $event['days_left'] ?> ngày
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <span class="category-badge category-<?= $event['category'] ?>">
                            <?= ucfirst($event['category']) ?>
                        </span>
                        
                        <h5 class="card-title mt-3 mb-3">
                            <a href="<?= BASE_URL ?>/event_detail.php?slug=<?= $event['slug'] ?>" 
                               class="text-dark text-decoration-none text-line-clamp-2">
                                <?= sanitize($event['title']) ?>
                            </a>
                        </h5>
                        
                        <p class="card-text text-muted small text-line-clamp-3">
                            <?= sanitize($event['description']) ?>
                        </p>
                        
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-danger" 
                                 role="progressbar" 
                                 style="width: <?= min($progress, 100) ?>%"
                                 data-progress="<?= $progress ?>">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <small class="text-muted">Đã quyên góp</small>
                                <div class="fw-bold text-danger"><?= formatMoney($event['current_amount']) ?></div>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">Mục tiêu</small>
                                <div class="fw-bold"><?= formatMoney($event['target_amount']) ?></div>
                            </div>
                        </div>
                        
                        <div class="event-meta d-flex justify-content-between align-items-center mb-3">
                            <span><i class="fas fa-users me-1"></i><?= $event['donor_count'] ?> người</span>
                            <span><i class="fas fa-map-marker-alt me-1"></i><?= sanitize($event['location']) ?></span>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-top-0">
                        <a href="<?= BASE_URL ?>/event_detail.php?slug=<?= $event['slug'] ?>" 
                           class="btn btn-danger w-100">
                            <i class="fas fa-hand-holding-heart me-2"></i>Quyên góp ngay
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?= BASE_URL ?>/events.php?urgent=1" class="btn btn-outline-danger btn-lg">
                Xem tất cả chiến dịch khẩn cấp <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Main Content with Sidebar -->
<section class="main-content-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <!-- Main Content (Left) -->
            <div class="col-lg-8">
                <!-- Latest Events -->
                <div class="content-block mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0">
                            <i class="fas fa-calendar-check text-danger me-2"></i>
                            Chiến dịch đang diễn ra
                        </h3>
                        <a href="<?= BASE_URL ?>/events.php" class="btn btn-outline-danger btn-sm">
                            Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    
                    <div class="row g-3">
                        <?php foreach ($latestEvents as $event): 
                            $progress = ($event['target_amount'] > 0) ? ($event['current_amount'] / $event['target_amount'] * 100) : 0;
                        ?>
                        <div class="col-md-6">
                            <div class="card event-card-horizontal h-100">
                                <div class="row g-0">
                                    <div class="col-5">
                                        <img src="<?= BASE_URL ?>/public/uploads/<?= $event['thumbnail'] ?>" 
                                             class="img-fluid h-100 object-fit-cover" 
                                             alt="<?= sanitize($event['title']) ?>">
                                    </div>
                                    <div class="col-7">
                                        <div class="card-body p-3">
                                            <span class="category-badge category-<?= $event['category'] ?> small">
                                                <?= ucfirst($event['category']) ?>
                                            </span>
                                            <h6 class="card-title mt-2 mb-2 text-line-clamp-2">
                                                <a href="<?= BASE_URL ?>/event_detail.php?slug=<?= $event['slug'] ?>" 
                                                   class="text-dark text-decoration-none">
                                                    <?= sanitize($event['title']) ?>
                                                </a>
                                            </h6>
                                            <div class="progress mb-2" style="height: 6px;">
                                                <div class="progress-bar bg-success" style="width: <?= min($progress, 100) ?>%"></div>
                                            </div>
                                            <div class="d-flex justify-content-between small">
                                                <span class="text-success fw-bold"><?= number_format($progress, 0) ?>%</span>
                                                <span class="text-muted"><?= $event['donor_count'] ?> người</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Latest News -->
                <div class="content-block">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0">
                            <i class="fas fa-newspaper text-primary me-2"></i>
                            Tin tức & Hoạt động
                        </h3>
                        <a href="<?= BASE_URL ?>/news.php" class="btn btn-outline-primary btn-sm">
                            Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    
                    <div class="row g-4">
                        <?php foreach ($latestNewsArticles as $news): ?>
                        <div class="col-md-6">
                            <div class="card news-card h-100 border-0 shadow-sm">
                                <img src="<?= BASE_URL ?>/public/uploads/<?= $news['thumbnail'] ?>" 
                                     class="card-img-top" 
                                     alt="<?= sanitize($news['title']) ?>"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <span class="badge bg-info text-dark mb-2"><?= ucfirst($news['category']) ?></span>
                                    <h6 class="card-title text-line-clamp-2">
                                        <a href="<?= BASE_URL ?>/news_detail.php?slug=<?= $news['slug'] ?>" 
                                           class="text-dark text-decoration-none">
                                            <?= sanitize($news['title']) ?>
                                        </a>
                                    </h6>
                                    <p class="card-text text-muted small text-line-clamp-3">
                                        <?= sanitize($news['excerpt'] ?? substr(strip_tags($news['content']), 0, 150)) ?>...
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i><?= timeAgo($news['created_at']) ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-eye me-1"></i><?= $news['views'] ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar (Right) -->
            <div class="col-lg-4">
                <!-- Quick Donate Widget -->
                <div class="sidebar-widget card border-0 shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-heart me-2"></i>Quyên góp nhanh
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Chọn số tiền và quyên góp ngay</p>
                        <div class="quick-donate-amounts">
                            <a href="<?= BASE_URL ?>/donate.php?amount=50000" class="btn btn-outline-danger w-100 mb-2">50.000đ</a>
                            <a href="<?= BASE_URL ?>/donate.php?amount=100000" class="btn btn-outline-danger w-100 mb-2">100.000đ</a>
                            <a href="<?= BASE_URL ?>/donate.php?amount=200000" class="btn btn-outline-danger w-100 mb-2">200.000đ</a>
                            <a href="<?= BASE_URL ?>/donate.php?amount=500000" class="btn btn-outline-danger w-100 mb-2">500.000đ</a>
                            <a href="<?= BASE_URL ?>/donate.php" class="btn btn-danger w-100">Số tiền khác</a>
                        </div>
                    </div>
                </div>
                
                <!-- Categories Widget -->
                <?php if (!empty($categories)): ?>
                <div class="sidebar-widget card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Danh mục
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($categories as $cat): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <a href="<?= BASE_URL ?>/events.php?category=<?= $cat['category'] ?>" 
                                   class="text-decoration-none text-dark">
                                    <i class="fas fa-folder me-2 text-primary"></i>
                                    <?= ucfirst($cat['category']) ?>
                                </a>
                                <span class="badge bg-primary rounded-pill"><?= $cat['count'] ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Recent Donors (Transparency) -->
                <?php if (!empty($recentDonors)): ?>
                <div class="sidebar-widget card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-hands-helping me-2"></i>Quyên góp gần đây
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="recent-donors-list">
                            <?php foreach (array_slice($recentDonors, 0, 5) as $donor): ?>
                            <div class="donor-item mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-start">
                                    <div class="donor-avatar me-3">
                                        <i class="fas fa-user-circle text-success fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small"><?= sanitize($donor['donor_name'] ?? 'Ẩn danh') ?></div>
                                        <div class="text-success small fw-bold"><?= formatMoney($donor['amount']) ?></div>
                                        <a href="<?= BASE_URL ?>/event_detail.php?slug=<?= $donor['event_slug'] ?>" 
                                           class="text-muted small text-decoration-none text-line-clamp-1">
                                            <?= sanitize($donor['event_title']) ?>
                                        </a>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <?= timeAgo($donor['created_at']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="<?= BASE_URL ?>/transparency.php" class="btn btn-outline-success btn-sm w-100">
                            <i class="fas fa-chart-line me-2"></i>Xem báo cáo minh bạch
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Become Volunteer CTA -->
                <div class="sidebar-widget card border-0 shadow-sm bg-warning">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-hands-helping fa-3x text-white mb-3"></i>
                        <h5 class="fw-bold mb-3">Trở thành tình nguyện viên</h5>
                        <p class="mb-3">Tham gia cùng chúng tôi để lan tỏa yêu thương</p>
                        <a href="<?= BASE_URL ?>/volunteer.php" class="btn btn-dark">
                            <i class="fas fa-user-plus me-2"></i>Đăng ký ngay
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Initialize Hero News Swiper with auto-rotate (5 seconds)
const heroSwiper = new Swiper('.hero-news-swiper', {
    loop: true,
    autoplay: {
        delay: 5000, // 5 seconds
        disableOnInteraction: false,
    },
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    effect: 'fade',
    fadeEffect: {
        crossFade: true
    },
    on: {
        autoplayTimeLeft(s, time, progress) {
            const progressCircle = document.querySelector('.autoplay-progress svg circle');
            const progressText = document.querySelector('.autoplay-progress span');
            if (progressCircle) {
                progressCircle.style.strokeDashoffset = 125.6 * (1 - progress);
            }
            if (progressText) {
                progressText.textContent = Math.ceil(time / 1000) + 's';
            }
        }
    }
});

// Counter Animation
const animateCounters = () => {
    const counters = document.querySelectorAll('.stat-number[data-target]');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        updateCounter();
    });
};

// Trigger counter animation when stats section is in viewport
const observerOptions = {
    threshold: 0.5
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateCounters();
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

const statsSection = document.querySelector('.stats-section');
if (statsSection) {
    observer.observe(statsSection);
}

// Animate progress bars
const animateProgressBars = () => {
    const progressBars = document.querySelectorAll('.progress-bar[data-progress]');
    progressBars.forEach(bar => {
        const progress = bar.getAttribute('data-progress');
        setTimeout(() => {
            bar.style.width = Math.min(progress, 100) + '%';
        }, 100);
    });
};

animateProgressBars();
</script>