<?php
// Bật lỗi và kết nối Database
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
// benefactor manage_news.php
// PHÂN QUYỀN
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'benefactor') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

function createSlug($string) {
    $search = array(
        '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#', '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#', '#(ì|í|ị|ỉ|ĩ)#',
        '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#', '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#', '#(ỳ|ý|ỵ|ỷ|ỹ)#', '#(đ)#',
        '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#', '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#', '#(Ì|Í|Ị|Ỉ|Ĩ)#',
        '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#', '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#', '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#', '#(Đ)#'
    );
    $replace = array(
        'a', 'e', 'i', 'o', 'u', 'y', 'd',
        'A', 'E', 'I', 'O', 'U', 'Y', 'D'
    );
    $string = preg_replace($search, $replace, $string);
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]+/', '-', $string);
    return preg_replace('/-+/', '-', $string);
}

// XỬ LÝ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Thêm bản tin mới
    if ($_POST['action'] === 'add_news') {
        $title = $_POST['title'] ?? '';
        $slug = createSlug($title) . '-' . time(); 
        $campaign_id = !empty($_POST['campaign_id']) ? $_POST['campaign_id'] : null;
        $category = $_POST['category'] ?? 'news';
        $excerpt = $_POST['excerpt'] ?? '';
        $content = $_POST['content'] ?? '';
        $status = $_POST['status'] ?? 'published';
        $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;

        // --- BẮT ĐẦU PHẦN THÊM MỚI: XỬ LÝ MÃ QR ĐỘNG HYBRID ---
        if (isset($_POST['include_qr']) && $_POST['include_qr'] == '1') {
            
            // 1. LẤY THÔNG TIN NGÂN HÀNG CỦA NHÀ HẢO TÂM TỪ DB
            $stmtBank = $pdo->prepare("SELECT bank_name, bank_account, bank_owner FROM charity_registrations WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $stmtBank->execute([$user_id]);
            $bankInfo = $stmtBank->fetch(PDO::FETCH_ASSOC);

            // Gán dữ liệu (Cảnh báo nếu chưa điền trong cài đặt)
            $bank_id = !empty($bankInfo['bank_name']) ? $bankInfo['bank_name'] : 'MOMO'; 
            $account_no = !empty($bankInfo['bank_account']) ? $bankInfo['bank_account'] : '0000000000';
            $account_name = !empty($bankInfo['bank_owner']) ? $bankInfo['bank_owner'] : 'CHUA CAP NHAT';

            // 2. TẠO MÃ TRACKING HYBRID (ID + TÊN NGẮN)
            // 2. TẠO MÃ TRACKING HYBRID (ID + VIẾT TẮT CÁC CHỮ CÁI ĐẦU)
            if (!empty($campaign_id)) {
                $stmtCampName = $pdo->prepare("SELECT target_name FROM charity_registrations WHERE id = ?");
                $stmtCampName->execute([$campaign_id]);
                $campData = $stmtCampName->fetch(PDO::FETCH_ASSOC);
                
                if ($campData && !empty($campData['target_name'])) {
                    // Bước A: Chuyển tên thành không dấu (Ví dụ: xay-truong-mam-non)
                    $noAccent = createSlug($campData['target_name']); 
                    
                    // Bước B: Tách các từ ra và lặp qua để lấy chữ cái đầu tiên của mỗi từ
                    $words = explode('-', $noAccent);
                    $acronym = '';
                    foreach ($words as $w) {
                        if (!empty($w)) {
                            $acronym .= strtoupper($w[0]); // Lấy chữ cái đầu và viết hoa
                        }
                    }
                    
                    // Cú pháp cuối cùng: UHCD + ID + Khoảng trắng + Chữ viết tắt (Ví dụ: UHCD15 XTMN)
                    $ma_giao_dich = "UHCD" . $campaign_id . " " . $acronym; 
                } else {
                    $ma_giao_dich = "UHCD" . $campaign_id;
                }
            } else {
                $ma_giao_dich = "UHQ" . $user_id; 
            }
            
            $addInfo = rawurlencode($ma_giao_dich);
            $accName = rawurlencode($account_name);
            
            // 3. TẠO LINK ẢNH VIETQR 
            $qr_url = "https://img.vietqr.io/image/{$bank_id}-{$account_no}-compact2.png?amount=0&addInfo={$addInfo}&accountName={$accName}";
            
            // 4. KHỐI HTML HIỂN THỊ
            $qr_html = "
            <div style='text-align: center; margin-top: 30px; padding: 25px; border: 2px dashed #007bff; border-radius: 8px; background-color: #f8fbff; box-shadow: inset 0 0 10px rgba(0,123,255,0.05);'>
                <h3 style='color: #007bff; margin-top: 0; font-size: 18px; margin-bottom: 5px;'>Mã QR Quyên Góp Đa Năng</h3>
                <p style='color: #555; font-size: 14px; margin-bottom: 15px;'>
                    Sử dụng <strong>App Ngân hàng</strong> hoặc ví điện tử (MoMo, ZaloPay...) để quét mã.<br>
                    <span style='color: #d32f2f; font-weight: bold;'>* Vui lòng tự nhập số tiền và GIỮ NGUYÊN nội dung chuyển khoản.</span>
                </p>
                
                <img src='{$qr_url}' alt='Mã QR Quyên Góp' style='max-width: 250px; height: auto; border: 1px solid #ddd; border-radius: 12px; padding: 5px; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);'>
                
                <div style='margin-top: 20px; display: flex; justify-content: center; gap: 10px; align-items: center;'>
                    <span style='font-size: 13px; color: #666; font-weight: bold;'>Hỗ trợ quét qua:</span>
                    <img src='https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png' alt='MoMo' style='height: 22px;'>
                    <img src='https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png' alt='ZaloPay' style='height: 22px; border-radius: 4px;'>
                    <img src='https://cdn.haitrieu.com/wp-content/uploads/2022/02/Logo-VNPay-V.png' alt='VNPay' style='height: 22px;'>
                </div>
                
                <div style='margin-top: 20px; background: #fff; padding: 10px; border-radius: 6px; border: 1px solid #cce5ff; display: inline-block; min-width: 280px;'>
                    <p style='margin: 0 0 5px 0; font-size: 13px; color: #555;'>Mã Tracking (Nội dung chuyển khoản):</p>
                    <strong style='font-size: 16px; color: #d32f2f; user-select: all;'>{$ma_giao_dich}</strong>
                </div>
            </div>";
            
            $content .= $qr_html; 
        }
        // --- KẾT THÚC PHẦN THÊM MỚI ---

        $thumbnail = '';
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $thumbnail = time() . '_news_' . basename($_FILES['thumbnail']['name']);
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadDir . $thumbnail);
        }

        $sql = "INSERT INTO news (author_id, campaign_id, title, slug, excerpt, content, thumbnail, category, status, published_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $campaign_id, $title, $slug, $excerpt, $content, $thumbnail, $category, $status, $published_at]);
        
        header("Location: manage_news.php");
        exit;
    }

    if ($_POST['action'] === 'delete_news') {
        $news_id = $_POST['news_id'];
        $stmtDel = $pdo->prepare("DELETE FROM news WHERE id = ? AND author_id = ?");
        $stmtDel->execute([$news_id, $user_id]);
        header("Location: manage_news.php");
        exit;
    }
}

try {
    $stmtCamps = $pdo->prepare("SELECT id, target_name FROM charity_registrations WHERE user_id = ? ORDER BY id DESC");
    $stmtCamps->execute([$user_id]);
    $my_campaigns = $stmtCamps->fetchAll(PDO::FETCH_ASSOC);

    $sqlNews = "SELECT n.*, c.target_name as campaign_name 
                FROM news n 
                LEFT JOIN charity_registrations c ON n.campaign_id = c.id 
                WHERE n.author_id = ? 
                ORDER BY n.created_at DESC";
    $stmtNews = $pdo->prepare($sqlNews);
    $stmtNews->execute([$user_id]);
    $news_list = $stmtNews->fetchAll(PDO::FETCH_ASSOC);

    $total_news = count($news_list);
    $total_views = 0;
    foreach($news_list as $n) { $total_views += (int)$n['views']; }
} catch (Exception $e) {
    die("Lỗi Database: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tin Tức & Hoạt Động</title>
    <link rel="stylesheet" href="../public/css/benefactor/dashboard.css?v=<?= time() ?>">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable_inline { min-height: 250px; }
        th.col-action { text-align: center !important; }
        .action-links { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
        .action-links a, .action-links button { padding: 5px 10px; border-radius: 4px; text-decoration: none !important; font-size: 13px; font-weight: bold; color: white !important; border: none; cursor: pointer; transition: 0.2s ease-in-out; display: inline-block; }
        .btn-view { background-color: #17a2b8; } .btn-view:hover { background-color: #138496; }
        .btn-delete { background-color: #dc3545; } .btn-delete:hover { background-color: #c82333; }
        .thumb-img { width: 80px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;}
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <aside class="sidebar">
        <a href="../index.php" style="text-decoration: none;"><div class="sidebar-logo">Charity Events</div></a>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Tổng quan</a></li>
            <li><a href="#">💸 Báo cáo minh bạch (Thu/Chi)</a></li>
            <li class="active"><a href="manage_news.php">📝 Đăng tin tức</a></li>
            <li><a href="tinhnguyen.php">👥 Quản lý tình nguyện viên</a></li>
            <li><a href="settings.php">⚙️ Cài đặt tài khoản</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="dash-header">
            <h1 class="dash-title">Tin tức & Hoạt động</h1>
            <a href="javascript:void(0)" class="btn-create" onclick="openNewsModal()">+ Thêm bản tin mới</a>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-title">Tổng số bài viết</div>
                <div class="stat-value"><?= $total_news ?></div>
            </div>
            <div class="stat-card green">
                <div class="stat-title">Tổng lượt xem</div>
                <div class="stat-value"><?= number_format($total_views) ?></div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header"><h3>Danh sách bài viết đã đăng</h3></div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 100px;">Ảnh bìa</th>
                        <th>Tiêu đề / Chiến dịch</th>
                        <th>Lượt xem</th>
                        <th>Trạng thái</th>
                        <th class="col-action">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($news_list) > 0): ?>
                        <?php foreach($news_list as $news): ?>
                        <tr>
                            <td>
                                <?php if($news['thumbnail']): ?>
                                    <img src="../../public/uploads/<?= htmlspecialchars($news['thumbnail']) ?>" class="thumb-img" alt="thumb">
                                <?php else: ?>
                                    <div class="thumb-img" style="background:#eee; text-align:center; line-height:50px; color:#999; font-size:12px;">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: #333; font-size: 15px; display:block; margin-bottom: 4px;"><?= htmlspecialchars($news['title']) ?></strong>
                                <?php if($news['campaign_name']): ?>
                                    <span style="font-size: 12px; color: #d32f2f; background: #ffebee; padding: 2px 6px; border-radius: 4px;">🏷️ Chiến dịch: <?= htmlspecialchars($news['campaign_name']) ?></span>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: #666; background: #f0f0f0; padding: 2px 6px; border-radius: 4px;">🌐 Tin tức chung</span>
                                <?php endif; ?>
                            </td>
                            <td>👁️ <?= number_format($news['views']) ?></td>
                            <td>
                                <?php if($news['status'] === 'published'): ?>
                                    <span class="badge active">Đã xuất bản</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#6c757d; color:#fff;">Bản nháp</span>
                                <?php endif; ?>
                            </td>
                            <td class="action-links">
                                <a href="../news/news_detail.php?slug=<?= $news['slug'] ?>" target="_blank" class="btn-view">👁️ Xem web</a>
                                <form method="POST" action="manage_news.php" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản tin này không?');">
                                    <input type="hidden" name="action" value="delete_news">
                                    <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                    <button type="submit" class="btn-delete">🗑️ Xóa</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; padding: 30px; color: #888;">Bạn chưa đăng bản tin nào!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="newsModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6);">
    <div class="modal-content" style="background-color: #fff; margin: 2% auto; padding: 25px; width: 850px; max-width: 95%; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">
        <span class="close" onclick="closeNewsModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <h2 style="margin-top: 0; color: #007bff; margin-bottom: 20px;">📰 Đăng tin tức mới</h2>
        
        <form method="POST" action="manage_news.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_news">
            
            <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div style="flex: 2;">
                    <label style="display:block; font-weight: bold; margin-bottom: 5px;">Tiêu đề bài viết <span style="color:red">*</span></label>
                    <input type="text" name="title" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required placeholder="VD: Lễ trao quà cho các em nhỏ vùng cao...">
                </div>
                <div style="flex: 1;">
                    <label style="display:block; font-weight: bold; margin-bottom: 5px;">Chuyên mục</label>
                    <select name="category" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="news">Tin tức</option>
                        <option value="activity">Hoạt động giải ngân</option>
                        <option value="story">Câu chuyện</option>
                        <option value="announcement">Thông báo</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display:block; font-weight: bold; margin-bottom: 5px;">Chọn chiến dịch liên quan (Tùy chọn)</label>
                    <select name="campaign_id" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">-- Không chọn (Tin tức chung) --</option>
                        <?php foreach($my_campaigns as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['target_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label style="display:block; font-weight: bold; margin-bottom: 5px;">Ảnh bìa (Thumbnail)</label>
                    <input type="file" name="thumbnail" accept="image/*" style="width: 100%; padding: 7px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight: bold; margin-bottom: 5px;">Mô tả ngắn gọn (Excerpt)</label>
                <textarea name="excerpt" rows="2" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;" placeholder="Tóm tắt nội dung chính..."></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight: bold; margin-bottom: 5px;">Nội dung chi tiết <span style="color:red">*</span></label>
                <textarea id="news_editor" name="content"></textarea>
            </div>
            
            <div style="margin-bottom: 20px; background: #f4fff6; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px;">
                <label style="display: flex; align-items: center; cursor: pointer; font-weight: bold; color: #155724;">
                    <input type="checkbox" name="include_qr" value="1" style="width: 18px; height: 18px; margin-right: 10px;">
                    Đính kèm mã QR quyên góp vào cuối bài viết này
                </label>
                <p style="margin: 5px 0 0 28px; font-size: 13px; color: #555;">Hệ thống sẽ TỰ ĐỘNG lấy thông tin ngân hàng của bạn (trong Cài đặt) và tạo nội dung chuyển khoản gồm <strong style="color:#d32f2f;">ID + Tên chiến dịch</strong> để ghép thành mã QR đa năng.</p>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeNewsModal()" style="background: #ccc; color: #333; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">Hủy bỏ</button>
                <button type="submit" name="status" value="published" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">Đăng bài ngay</button>
            </div>
        </form>
    </div>
</div>

<script>
ClassicEditor
    .create(document.querySelector('#news_editor'), {
        toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo' ]
    })
    .catch(error => { console.error(error); });

function openNewsModal() { document.getElementById('newsModal').style.display = 'block'; }
function closeNewsModal() { document.getElementById('newsModal').style.display = 'none'; }
window.onclick = function(event) { if (event.target == document.getElementById('newsModal')) { closeNewsModal(); } }
</script>

</body>
</html>