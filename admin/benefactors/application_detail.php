<?php
/**
 * Admin - Benefactor Application Detail
 * Xem chi tiết đơn đăng ký nhà hảo tâm (KYC)
 */
require_once '../../config/db.php';
require_once '../../includes/security.php';

requireAdmin();

$appId = $_GET['id'] ?? 0;

// Get application info
$stmt = $pdo->prepare("
    SELECT ba.*, u.fullname, u.email, u.phone, u.avatar, u.benefactor_status,
           ua.fullname as reviewed_by_name
    FROM benefactor_applications ba
    JOIN users u ON ba.user_id = u.id
    LEFT JOIN users ua ON ba.reviewed_by = ua.id
    WHERE ba.id = ?
");
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app) {
    setFlashMessage('error', 'Không tìm thấy đơn đăng ký');
    header('Location: benefactors.php?tab=pending');
    exit;
}

$pageTitle = 'Chi tiết đơn đăng ký';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .document-img {
            max-width: 100%;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .document-img:hover {
            transform: scale(1.02);
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <a href="benefactors.php?tab=<?= $app['status'] === 'pending' ? 'pending' : 'rejected' ?>" 
                           class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <?= $pageTitle ?>
                    </h1>
                    <div class="btn-toolbar">
                        <?php if ($app['status'] === 'pending'): ?>
                        <a href="approve_benefactor.php?id=<?= $appId ?>" class="btn btn-success me-2">
                            <i class="bi bi-check-lg"></i> Duyệt đơn
                        </a>
                        <a href="reject_benefactor.php?id=<?= $appId ?>" class="btn btn-danger">
                            <i class="bi bi-x-lg"></i> Từ chối
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php 
                $flash = getFlashMessage();
                if ($flash): 
                ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- User Info -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-person"></i> Thông tin người đăng ký</h5>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= $app['avatar'] ?>" 
                                             alt="Avatar" class="rounded-circle" 
                                             style="width: 80px; height: 80px; object-fit: cover;"
                                             onerror="this.src='<?= BASE_URL ?>/public/uploads/avatars/default-avatar.png'">
                                    </div>
                                    <div class="col">
                                        <h5 class="mb-1"><?= htmlspecialchars($app['fullname']) ?></h5>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($app['email']) ?>
                                        </p>
                                        <?php if ($app['phone']): ?>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($app['phone']) ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Thông tin cá nhân</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Họ tên đầy đủ:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['full_legal_name']) ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>CCCD/CMND:</strong></div>
                                    <div class="col-md-8"><code><?= htmlspecialchars($app['id_card_number']) ?></code></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Ngày sinh:</strong></div>
                                    <div class="col-md-8"><?= formatDate($app['date_of_birth']) ?></div>
                                </div>
                                <?php if ($app['place_of_birth']): ?>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Nơi sinh:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['place_of_birth']) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- CCCD Images -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-card-image"></i> Ảnh CCCD/CMND</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <h6>Mặt trước</h6>
                                        <a href="<?= BASE_URL ?>/public/uploads/documents/<?= $app['id_card_front'] ?>" 
                                           target="_blank">
                                            <img src="<?= BASE_URL ?>/public/uploads/documents/<?= $app['id_card_front'] ?>" 
                                                 alt="CCCD mặt trước" class="document-img">
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6>Mặt sau</h6>
                                        <a href="<?= BASE_URL ?>/public/uploads/documents/<?= $app['id_card_back'] ?>" 
                                           target="_blank">
                                            <img src="<?= BASE_URL ?>/public/uploads/documents/<?= $app['id_card_back'] ?>" 
                                                 alt="CCCD mặt sau" class="document-img">
                                        </a>
                                    </div>
                                </div>
                                <small class="text-muted">Click vào ảnh để xem kích thước đầy đủ</small>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Địa chỉ liên hệ</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Địa chỉ:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['address']) ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Phường/Xã:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['ward']) ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Quận/Huyện:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['district']) ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Tỉnh/TP:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['city']) ?></div>
                                </div>
                                <?php if ($app['permanent_address']): ?>
                                <hr>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Địa chỉ thường trú:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['permanent_address']) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Organization Info (if any) -->
                        <?php if ($app['organization_name']): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-building"></i> Thông tin tổ chức</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Tên tổ chức:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['organization_name']) ?></div>
                                </div>
                                <?php if ($app['organization_type']): ?>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Loại hình:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['organization_type']) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if ($app['tax_code']): ?>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Mã số thuế:</strong></div>
                                    <div class="col-md-8"><code><?= htmlspecialchars($app['tax_code']) ?></code></div>
                                </div>
                                <?php endif; ?>
                                <?php if ($app['business_license']): ?>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Giấy phép KD:</strong></div>
                                    <div class="col-md-8">
                                        <a href="<?= BASE_URL ?>/public/uploads/documents/<?= $app['business_license'] ?>" 
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file-pdf"></i> Xem file
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Financial Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-bank"></i> Thông tin ngân hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Ngân hàng:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['bank_name']) ?></div>
                                </div>
                                <?php if ($app['bank_branch']): ?>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Chi nhánh:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['bank_branch']) ?></div>
                                </div>
                                <?php endif; ?>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Số tài khoản:</strong></div>
                                    <div class="col-md-8"><code><?= htmlspecialchars($app['bank_account']) ?></code></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Chủ tài khoản:</strong></div>
                                    <div class="col-md-8"><?= htmlspecialchars($app['account_holder']) ?></div>
                                </div>
                                <?php if ($app['financial_proof']): ?>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Sao kê:</strong></div>
                                    <div class="col-md-8">
                                        <a href="<?= BASE_URL ?>/public/uploads/documents/<?= $app['financial_proof'] ?>" 
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file-image"></i> Xem file
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Motivation -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-heart"></i> Động lực & Kinh nghiệm</h5>
                            </div>
                            <div class="card-body">
                                <h6>Lý do đăng ký:</h6>
                                <p><?= nl2br(htmlspecialchars($app['motivation'])) ?></p>
                                
                                <?php if ($app['previous_experience']): ?>
                                <hr>
                                <h6>Kinh nghiệm trước đây:</h6>
                                <p><?= nl2br(htmlspecialchars($app['previous_experience'])) ?></p>
                                <?php endif; ?>
                                
                                <?php if ($app['expected_activities']): ?>
                                <hr>
                                <h6>Hoạt động dự kiến:</h6>
                                <p><?= nl2br(htmlspecialchars($app['expected_activities'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div class="col-lg-4">
                        <!-- Status Card -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Trạng thái</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger'
                                ];
                                $statusLabels = [
                                    'pending' => 'Chờ duyệt',
                                    'approved' => 'Đã duyệt',
                                    'rejected' => 'Đã từ chối'
                                ];
                                ?>
                                <div class="text-center mb-3">
                                    <span class="badge bg-<?= $statusColors[$app['status']] ?> fs-5">
                                        <?= $statusLabels[$app['status']] ?>
                                    </span>
                                </div>
                                
                                <div class="mb-2">
                                    <strong>Ngày nộp:</strong><br>
                                    <small><?= formatDate($app['created_at'], 'd/m/Y H:i') ?></small>
                                </div>
                                
                                <?php if ($app['reviewed_at']): ?>
                                <div class="mb-2">
                                    <strong>Ngày xét duyệt:</strong><br>
                                    <small><?= formatDate($app['reviewed_at'], 'd/m/Y H:i') ?></small>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($app['reviewed_by_name']): ?>
                                <div class="mb-2">
                                    <strong>Người xét duyệt:</strong><br>
                                    <small><?= htmlspecialchars($app['reviewed_by_name']) ?></small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Admin Notes -->
                        <?php if ($app['admin_notes'] || $app['rejection_reason']): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-sticky"></i> Ghi chú</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($app['rejection_reason']): ?>
                                <div class="alert alert-danger mb-2">
                                    <strong>Lý do từ chối:</strong><br>
                                    <?= nl2br(htmlspecialchars($app['rejection_reason'])) ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($app['admin_notes']): ?>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($app['admin_notes'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Actions -->
                        <?php if ($app['status'] === 'pending'): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-gear"></i> Thao tác</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="approve_benefactor.php?id=<?= $appId ?>" class="btn btn-success">
                                        <i class="bi bi-check-circle"></i> Duyệt đơn
                                    </a>
                                    <a href="reject_benefactor.php?id=<?= $appId ?>" class="btn btn-danger">
                                        <i class="bi bi-x-circle"></i> Từ chối đơn
                                    </a>
                                    <a href="../users/user_detail.php?id=<?= $app['user_id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-person"></i> Xem hồ sơ user
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
