<?php
/**
 * Admin Sidebar Navigation admin/includes/admin_sidebar.php
 * Thanh điều hướng bên trái của trang admin, bao gồm các link đến các phần quản lý khác nhau như người dùng, sự kiện, báo cáo, cài đặt...
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" style="min-height: 100vh;">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-1 text-muted">
                    <span>QUẢN LÝ NGƯỜI DÙNG</span>
                </h6>
            </li>
            
            <!-- Users Management -->
            <li class="nav-item">
                <a class="nav-link <?= $currentDir == 'users' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/users/users.php">
                    <i class="bi bi-people"></i> Người dùng
                </a>
            </li>
            
            <!-- Benefactors -->
            <li class="nav-item">
                <a class="nav-link <?= $currentDir == 'benefactors' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/benefactors/benefactors.php">
                    <i class="bi bi-award"></i> Nhà hảo tâm
                    <?php 
                    $pendingApps = $pdo->query("SELECT COUNT(*) FROM benefactor_applications WHERE status = 'pending'")->fetchColumn();
                    if ($pendingApps > 0): 
                    ?>
                    <span class="badge bg-warning text-dark ms-2"><?= $pendingApps ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-1 text-muted">
                    <span>QUẢN LÝ SỰ KIỆN</span>
                </h6>
            </li>
            
            <!-- Events -->
            <li class="nav-item">
                <a class="nav-link <?= $currentDir == 'events' && $currentPage == 'all_events.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/events/all_events.php">
                    <i class="bi bi-calendar-event"></i> Tất cả sự kiện
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'pending_events.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/events/pending_events.php">
                    <i class="bi bi-clock-history"></i> Chờ duyệt
                    <?php 
                    $pendingEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'pending'")->fetchColumn();
                    if ($pendingEvents > 0): 
                    ?>
                    <span class="badge bg-warning text-dark ms-2"><?= $pendingEvents ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            
          
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-1 text-muted">
                    <span>QUYÊN GÓP & TÌNH NGUYỆN</span>
                </h6>
            </li>
            
            <!-- Donations -->
            <li class="nav-item">
                <a class="nav-link <?= $currentDir == 'donations' && $currentPage == 'donations.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/donations/donations.php">
                    <i class="bi bi-currency-dollar"></i> Quyên góp
                </a>
            </li>
            
        
            
            <!-- Volunteers -->
            <li class="nav-item">
                <a class="nav-link <?= $currentDir == 'volunteers' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/volunteers/volunteers.php">
                    <i class="bi bi-person-heart"></i> Tình nguyện viên
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-1 text-muted">
                    <span>BÁO CÁO & THỐNG KÊ</span>
                </h6>
            </li>
            
            <!-- Reports -->
            <li class="nav-item">
                <a class="nav-link <?= $currentDir == 'reports' && $currentPage == 'statistics.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/reports/statistics.php">
                    <i class="bi bi-graph-up"></i> Thống kê tổng quan
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'revenue_report.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/reports/revenue_report.php">
                    <i class="bi bi-bar-chart"></i> Báo cáo doanh thu
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'event_report.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/reports/event_report.php">
                    <i class="bi bi-clipboard-data"></i> Báo cáo sự kiện
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'user_report.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/reports/user_report.php">
                    <i class="bi bi-file-earmark-person"></i> Báo cáo người dùng
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-1 text-muted">
                    <span>NỘI DUNG</span>
                </h6>
            </li>
            
            <!-- News -->
            <li class="nav-item">
                <a class="nav-link <?= $currentDir == 'news' && $currentPage == 'news_list.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/news/news_list.php">
                    <i class="bi bi-newspaper"></i> Tin tức
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'create_news.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/news/create_news.php">
                    <i class="bi bi-plus-circle"></i> Tạo tin mới
                </a>
            </li>
            
            <!-- Contacts -->
            <li class="nav-item">
                <a class="nav-link <?= $currentDir == 'contacts' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/contacts/contacts.php">
                    <i class="bi bi-envelope"></i> Liên hệ
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-1 text-muted">
                    <span>CÀI ĐẶT</span>
                </h6>
            </li>
            
            <!-- Settings -->
            <li class="nav-item">
                <a class="nav-link <?= $currentDir == 'settings' && $currentPage == 'general.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/settings/general.php">
                    <i class="bi bi-gear"></i> Cài đặt chung
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'terms.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/settings/terms.php">
                    <i class="bi bi-file-text"></i> Điều khoản
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'partners.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/settings/partners.php">
                    <i class="bi bi-building"></i> Đối tác
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'backup.php' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/settings/backup.php">
                    <i class="bi bi-database"></i> Sao lưu
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.sidebar {
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.sidebar .nav-link {
    color: #333;
    padding: 10px 15px;
    border-radius: 5px;
    margin: 2px 10px;
    transition: all 0.3s;
}

.sidebar .nav-link:hover {
    background-color: #e9ecef;
}

.sidebar .nav-link.active {
    background-color: #0d6efd;
    color: white;
}

.sidebar .nav-link i {
    margin-right: 8px;
    width: 20px;
    text-align: center;
}

.sidebar-heading {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
