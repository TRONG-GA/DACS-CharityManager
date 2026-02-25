<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentURI = $_SERVER['REQUEST_URI'];

// Get user info if logged in
$userInfo = null;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userInfo = $stmt->fetch();
}
?>

<!-- Top Bar -->
<div class="top-bar bg-danger text-white py-2">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <small>
                    <i class="fas fa-phone me-2"></i>
                    <a href="tel:19001234" class="text-white text-decoration-none">Hotline: 1900 1234</a>
                    <span class="ms-4">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:contact@charityevent.vn" class="text-white text-decoration-none">contact@charityevent.vn</a>
                    </span>
                </small>
            </div>
            <div class="col-md-4 text-end">
                <small>
                    <?php if (isLoggedIn()): ?>
                        <span class="me-3">
                            <i class="fas fa-user me-1"></i>
                            <?= sanitize($userInfo['fullname']) ?>
                        </span>
                        <?php if (isAdmin()): ?>
                            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-white text-decoration-none me-3">
                                <i class="fas fa-cog me-1"></i>Admin
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/auth/logout.php" class="text-white text-decoration-none">
                            <i class="fas fa-sign-out-alt me-1"></i>Đăng xuất
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/auth/login.php" class="text-white text-decoration-none me-3">
                            <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                        </a>
                        <a href="<?= BASE_URL ?>/auth/register.php" class="text-white text-decoration-none">
                            <i class="fas fa-user-plus me-1"></i>Đăng ký
                        </a>
                    <?php endif; ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="main-header bg-white py-3 border-bottom">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4">
                <a href="<?= BASE_URL ?>/index.php" class="logo-link text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="logo-icon me-3">
                            <i class="fas fa-heart text-danger" style="font-size: 2.5rem;"></i>
                        </div>
                        <div class="logo-text">
                            <h1 class="mb-0 text-danger fw-bold" style="font-size: 1.8rem;">CHARITY EVENT</h1>
                            <p class="mb-0 text-muted small">Nền tảng kết nối từ thiện minh bạch</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-5">
                <!-- Search Bar -->
                <form action="<?= BASE_URL ?>/search.php" method="GET" class="search-form">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Tìm kiếm sự kiện, tin tức..." 
                               value="<?= isset($_GET['q']) ? sanitize($_GET['q']) : '' ?>">
                        <button class="btn btn-danger" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="col-md-3 text-end">
                <!-- Social Links -->
                <div class="social-links mb-2">
                    <a href="#" class="text-primary me-2" title="Facebook">
                        <i class="fab fa-facebook fa-lg"></i>
                    </a>
                    <a href="#" class="text-dark me-2" title="TikTok">
                        <i class="fab fa-tiktok fa-lg"></i>
                    </a>
                    <a href="#" class="text-danger me-2" title="YouTube">
                        <i class="fab fa-youtube fa-lg"></i>
                    </a>
                </div>
                <div class="language-switch">
                    <small>
                        <a href="#" class="text-decoration-none me-2">
                            <img src="https://flagcdn.com/16x12/vn.png" alt="VI" class="me-1"> VI
                        </a>
                        <a href="#" class="text-decoration-none text-muted">
                            <img src="https://flagcdn.com/16x12/gb.png" alt="EN" class="me-1"> EN
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Navigation Bar (Sticky) -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top shadow-sm">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Trang chủ -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'index' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">
                        <i class="fas fa-home me-1"></i>TRANG CHỦ
                    </a>
                </li>
                
                <!-- Giới thiệu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['about', 'mission', 'team']) ? 'active' : '' ?>" 
                       href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-info-circle me-1"></i>GIỚI THIỆU
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/about.php">
                            <i class="fas fa-building me-2"></i>Về Charity Event
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/mission.php">
                            <i class="fas fa-bullseye me-2"></i>Sứ mệnh & Tầm nhìn
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/how-it-works.php">
                            <i class="fas fa-cogs me-2"></i>Cách thức hoạt động
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/team.php">
                            <i class="fas fa-users me-2"></i>Đội ngũ phát triển
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/faq.php">
                            <i class="fas fa-question-circle me-2"></i>Câu hỏi thường gặp
                        </a></li>
                    </ul>
                </li>
                
                <!-- Sự kiện -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['events', 'event_detail', 'my_participated']) ? 'active' : '' ?>" 
                       href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-calendar-alt me-1"></i>SỰ KIỆN
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/events.php">
                            <i class="fas fa-fire text-danger me-2"></i>Đang diễn ra
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/events.php?status=upcoming">
                            <i class="fas fa-clock text-warning me-2"></i>Sắp diễn ra
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/events.php?status=completed">
                            <i class="fas fa-check-circle text-success me-2"></i>Đã hoàn thành
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header">Danh mục</li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/events.php?category=medical">
                            <i class="fas fa-heartbeat text-danger me-2"></i>Y tế & Sức khỏe
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/events.php?category=education">
                            <i class="fas fa-graduation-cap text-primary me-2"></i>Giáo dục
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/events.php?category=disaster">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>Thiên tai & Khẩn cấp
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/events.php?category=children">
                            <i class="fas fa-child text-info me-2"></i>Trẻ em & Người già
                        </a></li>
                        <?php if (isLoggedIn()): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/my_participated.php">
                            <i class="fas fa-history me-2"></i>Đã tham gia
                        </a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                
                <!-- Tổ chức sự kiện (Benefactor only) -->
                <?php if (isLoggedIn()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($currentURI, '/benefactor/') !== false ? 'active' : '' ?>" 
                       href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-hands-helping me-1"></i>TỔ CHỨC SỰ KIỆN
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (!isBenefactorVerified() && !isAdmin()): ?>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/benefactor/apply.php">
                            <i class="fas fa-user-check text-warning me-2"></i>Đăng ký Nhà hảo tâm
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <?php if (isBenefactorVerified() || isAdmin()): ?>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/benefactor/create_event.php">
                            <i class="fas fa-plus-circle text-success me-2"></i>Tạo sự kiện mới
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/benefactor/my_events.php">
                            <i class="fas fa-folder-open me-2"></i>Sự kiện của tôi
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/benefactor/donations.php">
                            <i class="fas fa-donate me-2"></i>Quản lý quyên góp
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/benefactor/volunteers.php">
                            <i class="fas fa-user-friends me-2"></i>Danh sách tình nguyện viên
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/benefactor/reports.php">
                            <i class="fas fa-chart-bar me-2"></i>Báo cáo & Thống kê
                        </a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Tin tức -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'news' ? 'active' : '' ?>" href="<?= BASE_URL ?>/news.php">
                        <i class="fas fa-newspaper me-1"></i>TIN TỨC
                    </a>
                </li>
                
                <!-- Hồ sơ (User only) -->
                <?php if (isLoggedIn()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['profile', 'my_donations', 'my_volunteers']) ? 'active' : '' ?>" 
                       href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>HỒ SƠ
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php">
                            <i class="fas fa-id-card me-2"></i>Dashboard cá nhân
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/my_donations.php">
                            <i class="fas fa-heart text-danger me-2"></i>Lịch sử quyên góp
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/my_volunteers.php">
                            <i class="fas fa-hands-helping text-success me-2"></i>Sự kiện tình nguyện
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/settings.php">
                            <i class="fas fa-cog me-2"></i>Cài đặt tài khoản
                        </a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Liên hệ -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'contact' ? 'active' : '' ?>" href="<?= BASE_URL ?>/contact.php">
                        <i class="fas fa-phone me-1"></i>LIÊN HỆ
                    </a>
                </li>
                
                <!-- Admin (Admin only) -->
                <?php if (isAdmin()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-danger <?= strpos($currentURI, '/admin/') !== false ? 'active' : '' ?>" 
                       href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-shield-alt me-1"></i>ADMIN
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/approve_benefactors.php">
                            <i class="fas fa-user-check me-2"></i>Duyệt Nhà hảo tâm
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/approve_events.php">
                            <i class="fas fa-calendar-check me-2"></i>Duyệt Sự kiện
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/users.php">
                            <i class="fas fa-users me-2"></i>Quản lý Users
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/statistics.php">
                            <i class="fas fa-chart-line me-2"></i>Báo cáo Hệ thống
                        </a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            
            <!-- Quick Donate Button -->
            <?php if (isLoggedIn() && (isBenefactorVerified() || isAdmin())): ?>
            <a href="<?= BASE_URL ?>/benefactor/create_event.php" class="btn btn-danger">
                <i class="fas fa-plus me-2"></i>Tạo sự kiện
            </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<?php
$flash = getFlashMessage();
if ($flash):
    $alertClass = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ][$flash['type']] ?? 'alert-info';
?>
<div class="container mt-3">
    <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?= $flash['type'] == 'success' ? 'check-circle' : ($flash['type'] == 'error' ? 'exclamation-circle' : 'info-circle') ?> me-2"></i>
        <?= sanitize($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- Running News Ticker -->
<?php
$runningNews = $pdo->query("
    SELECT title, slug FROM news 
    WHERE status = 'published' AND is_breaking = 1 
    ORDER BY created_at DESC LIMIT 5
")->fetchAll();

if (!empty($runningNews)):
?>
<div class="news-ticker bg-light border-bottom py-2">
    <div class="container">
        <div class="d-flex align-items-center">
            <div class="ticker-label bg-danger text-white px-3 py-1 me-3">
                <i class="fas fa-bullhorn me-2"></i>TIN NỔI BẬT
            </div>
            <div class="ticker-content flex-grow-1">
                <marquee behavior="scroll" direction="left" scrollamount="5">
                    <?php foreach ($runningNews as $news): ?>
                        <a href="<?= BASE_URL ?>/news_detail.php?slug=<?= $news['slug'] ?>" 
                           class="text-decoration-none text-dark me-5">
                            <i class="fas fa-circle text-danger me-2" style="font-size: 0.5rem;"></i>
                            <?= sanitize($news['title']) ?>
                        </a>
                    <?php endforeach; ?>
                </marquee>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>