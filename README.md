# 🎗️ Charity Event 

**Nền tảng quản lý sự kiện từ thiện minh bạch và hiệu quả**

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-orange)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 📋 Mục lục

- [Giới thiệu](#-giới-thiệu)
- [Tính năng](#-tính-năng)
- [Công nghệ sử dụng](#-công-nghệ-sử-dụng)
- [Yêu cầu hệ thống](#-yêu-cầu-hệ-thống)
- [Hướng dẫn cài đặt](#-hướng-dẫn-cài-đặt)
- [Cấu hình](#-cấu-hình)
- [Hướng dẫn sử dụng](#-hướng-dẫn-sử-dụng)
- [Cấu trúc thư mục](#-cấu-trúc-thư-mục)
- [API Documentation](#-api-documentation)
- [Screenshots](#-screenshots)
- [Troubleshooting](#-troubleshooting)
- [Đóng góp](#-đóng-góp)
- [License](#-license)
- [Liên hệ](#-liên-hệ)

---

## 🌟 Giới thiệu

**Charity Event Manager** là một hệ thống quản lý sự kiện từ thiện toàn diện, giúp kết nối giữa các tổ chức từ thiện (Nhà hảo tâm) và người quyên góp một cách minh bạch và hiệu quả.

### Điểm nổi bật:

- ✅ Quản lý sự kiện từ thiện với workflow phê duyệt
- ✅ Tích hợp thanh toán qua ngân hàng (Sepay API)
- ✅ Quản lý tình nguyện viên và phân công công việc
- ✅ Báo cáo thu chi minh bạch theo từng sự kiện
- ✅ Hệ thống thông báo real-time
- ✅ Dashboard analytics với biểu đồ trực quan
- ✅ Hệ thống phân quyền: Admin, Benefactor (Nhà hảo tâm), User
- ✅ Responsive design, tương thích mobile

---

## 🚀 Tính năng

### 👤 Người dùng (User)
- Đăng ký/Đăng nhập tài khoản
- Xem danh sách sự kiện từ thiện
- Quyên góp tiền qua ngân hàng
- Đăng ký tình nguyện viên
- Theo dõi lịch sử quyên góp
- Đọc tin tức và bài viết

### 🎯 Nhà hảo tâm (Benefactor)
- Đăng ký và xác thực làm Nhà hảo tâm
- Tạo và quản lý sự kiện từ thiện
- Quản lý thu chi cho từng sự kiện
- Phê duyệt tình nguyện viên
- Tạo và quản lý tin tức
- Dashboard thống kê chi tiết
- Xuất báo cáo tài chính

### 🛡️ Quản trị viên (Admin)
- Phê duyệt Nhà hảo tâm
- Phê duyệt sự kiện từ thiện
- Quản lý người dùng
- Quản lý toàn bộ tin tức
- Xem thống kê tổng quan hệ thống
- Quản lý phản hồi từ người dùng

---

## 💻 Công nghệ sử dụng

### Backend
- **PHP 8.0+** - Ngôn ngữ lập trình chính
- **MySQL 8.0+** - Cơ sở dữ liệu
- **PDO** - Database abstraction layer

### Frontend
- **HTML5, CSS3, JavaScript**
- **Bootstrap 5** - CSS Framework
- **Chart.js** - Biểu đồ thống kê
- **CKEditor 5** - Rich text editor
- **Font Awesome 6** - Icons

### Third-party Services
- **Sepay API** - Cổng thanh toán ngân hàng
- **Ngrok** - Tunneling cho webhook callback

### Libraries & Tools
- **TinyMCE / CKEditor** - WYSIWYG editor
- **PHPMailer** - Gửi email
- **FPDF** - Xuất file PDF

---

## 📦 Yêu cầu hệ thống

- **XAMPP** hoặc **WAMP** (Apache + MySQL + PHP)
- **PHP** >= 8.0
- **MySQL** >= 8.0
- **Composer** (optional, nếu cần)
- **Ngrok** (bắt buộc cho Sepay webhook)
- **Web Browser** hiện đại (Chrome, Firefox, Edge)

---

## 🔧 Hướng dẫn cài đặt

### Bước 1: Clone Repository

```bash
git clone https://github.com/your-username/DACS-CharityManager.git
cd DACS-CharityManager
```

### Bước 2: Cài đặt XAMPP

1. Download XAMPP từ: https://www.apachefriends.org/
2. Cài đặt và khởi động XAMPP Control Panel
3. Start **Apache** và **MySQL**

### Bước 3: Cấu hình MySQL Port

**Quan trọng:** Dự án này sử dụng MySQL port **3309** thay vì mặc định 3306.

1. Mở `C:\xampp\mysql\bin\my.ini`
2. Tìm và sửa:
   ```ini
   port=3309
   ```
3. Mở XAMPP Control Panel → Config (MySQL) → my.ini
4. Tìm và sửa:
   ```ini
   port=3309
   ```
5. Restart MySQL trong XAMPP

### Bước 4: Tạo Database

1. Truy cập: `http://localhost/phpmyadmin`
2. Chọn port **3309** khi đăng nhập
3. Tạo database mới:
   ```sql
   CREATE DATABASE charity_event CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Import file SQL:
   - Click vào database `charity_event`
   - Chọn tab **Import**
   - Chọn file `database/charity_event.sql`
   - Click **Go**

### Bước 5: Copy Project vào htdocs

```bash
# Windows
copy DACS-CharityManager C:\xampp\htdocs\

# Hoặc di chuyển thủ công thư mục vào C:\xampp\htdocs\
```

### Bước 6: Cấu hình Database Connection

Mở file `config/db.php` và kiểm tra:

```php
<?php
define('DB_HOST', 'localhost:3309'); // Port 3309
define('DB_NAME', 'charity_event');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mật khẩu MySQL (mặc định trống)
define('BASE_URL', 'http://localhost/DACS-CharityManager');
define('SITE_NAME', 'Charity Event');

// PDO Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
```

### Bước 7: Cấu hình Sepay Payment Gateway

#### 7.1. Đăng ký Sepay Account

1. Truy cập: https://my.sepay.vn/
2. Đăng ký tài khoản
3. Lấy API Key từ Dashboard → API Settings

#### 7.2. Cài đặt Ngrok

```bash
# Download ngrok
https://ngrok.com/download

# Giải nén và chạy
ngrok http 80

# Hoặc nếu Apache chạy port khác
ngrok http 8080
```

#### 7.3. Cấu hình Webhook

Mở file `config/payment.php`:

```php
<?php
define('SEPAY_API_KEY', 'your-sepay-api-key-here');
define('SEPAY_ACCOUNT_NUMBER', 'your-bank-account-number');
define('SEPAY_ACCOUNT_NAME', 'YOUR ACCOUNT NAME');
define('SEPAY_BANK_CODE', 'MB'); // MB Bank, ACB, VCB, etc.

// Webhook URL (ngrok URL)
define('SEPAY_WEBHOOK_URL', 'https://your-ngrok-url.ngrok.io/DACS-CharityManager/webhooks/sepay_webhook.php');
```

#### 7.4. Đăng ký Webhook trên Sepay

1. Vào Sepay Dashboard
2. Settings → Webhook
3. Thêm URL: `https://your-ngrok-url.ngrok.io/DACS-CharityManager/webhooks/sepay_webhook.php`
4. Chọn Events: `transaction.created`, `transaction.completed`
5. Save

### Bước 8: Tạo Thư mục Uploads

```bash
# Windows Command Prompt
cd C:\xampp\htdocs\DACS-CharityManager
mkdir public\uploads
mkdir public\uploads\avatars
mkdir public\uploads\events
mkdir public\uploads\news
mkdir public\uploads\documents
mkdir logs
```

Hoặc tạo thủ công các thư mục:
- `public/uploads/avatars/`
- `public/uploads/events/`
- `public/uploads/news/`
- `public/uploads/documents/`
- `logs/`

### Bước 9: Set Permissions (nếu dùng Linux/Mac)

```bash
chmod -R 755 public/uploads
chmod -R 755 logs
chown -R www-data:www-data public/uploads
chown -R www-data:www-data logs
```

### Bước 10: Truy cập Website

Mở trình duyệt và truy cập:

```
http://localhost/DACS-CharityManager
```

---

## 🔐 Tài khoản mặc định

Sau khi import database, bạn có thể đăng nhập với các tài khoản sau:

### Admin
- **Email:** admin@charityevent.vn
- **Password:** admin123

### Benefactor (Nhà hảo tâm đã xác thực)
- **Email:** benefactor@example.com
- **Password:** benefactor123

### User
- **Email:** user@example.com
- **Password:** user123

**⚠️ Lưu ý:** Đổi mật khẩu ngay sau khi đăng nhập lần đầu!

---

## ⚙️ Cấu hình

### Cấu hình Email (PHPMailer)

Mở `config/email.php`:

```php
<?php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password'); // App Password, không phải mật khẩu Gmail
define('SMTP_FROM', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'Charity Event');
```

### Cấu hình Upload

Mở `config/upload.php`:

```php
<?php
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
```

### Cấu hình Security

Mở `config/security.php`:

```php
<?php
define('SESSION_LIFETIME', 3600); // 1 hour
define('CSRF_TOKEN_LIFETIME', 7200); // 2 hours
define('PASSWORD_MIN_LENGTH', 8);
define('RATE_LIMIT_ATTEMPTS', 5);
define('RATE_LIMIT_DURATION', 900); // 15 minutes
```

---

## 📚 Hướng dẫn sử dụng

### Đăng ký làm Nhà hảo tâm

1. Đăng ký tài khoản User bình thường
2. Đăng nhập
3. Vào menu **Tổ chức sự kiện** → **Đăng ký Nhà hảo tâm**
4. Điền đầy đủ thông tin và upload giấy tờ xác thực
5. Chờ Admin phê duyệt

### Tạo sự kiện từ thiện

1. Đăng nhập với tài khoản Benefactor đã được xác thực
2. Vào **Dashboard** → **Tạo sự kiện mới**
3. Điền thông tin:
   - Tên sự kiện
   - Mô tả chi tiết
   - Mục tiêu quyên góp
   - Thời gian bắt đầu/kết thúc
   - Địa điểm
   - Upload ảnh
4. Submit và chờ Admin phê duyệt

### Quyên góp

1. Truy cập trang chi tiết sự kiện
2. Click **Quyên góp ngay**
3. Nhập số tiền và thông tin
4. Chuyển khoản qua ngân hàng theo thông tin hiển thị
5. Hệ thống tự động xác nhận khi nhận được tiền (qua Sepay webhook)

### Quản lý thu chi

1. Vào **Dashboard Benefactor** → **Thu/Chi**
2. Chọn sự kiện cần quản lý
3. Thêm các khoản chi:
   - Loại chi phí
   - Số tiền
   - Mô tả
   - Upload hóa đơn/chứng từ
4. Hệ thống tự động tính toán số dư

---

## 📁 Cấu trúc thư mục

```
DACS-CharityManager/
│
├── admin/                      # Admin dashboard
│   ├── dashboard.php
│   ├── users/                  # Quản lý users
│   ├── news/                   # Quản lý tin tức
│   ├── events/                 # Quản lý sự kiện
│   └── includes/               # Admin includes
│
├── auth/                       # Authentication
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   └── forgot_password.php
│
├── benefactor/                 # Benefactor dashboard
│   ├── dashboard.php
│   ├── create_event.php
│   ├── my_events.php
│   ├── ledger.php              # Thu chi
│   └── volunteers.php
│
├── config/                     # Configuration files
│   ├── db.php                  # Database config
│   ├── payment.php             # Payment config
│   ├── email.php               # Email config
│   └── constants.php
│
├── events/                     # Public event pages
│   ├── events.php              # Danh sách sự kiện
│   ├── event_detail.php        # Chi tiết sự kiện
│   └── donate.php              # Quyên góp
│
├── includes/                   # Shared includes
│   ├── header.php
│   ├── navbar.php
│   ├── footer.php
│   ├── security.php            # Security functions
│   └── functions.php           # Helper functions
│
├── news/                       # News section
│   ├── news.php
│   └── news_detail.php
│
├── pages/                      # Static pages
│   ├── about.php
│   ├── contact.php
│   ├── faq.php
│   └── process_contact.php
│
├── public/                     # Public assets
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/                # User uploads
│       ├── avatars/
│       ├── events/
│       ├── news/
│       └── documents/
│
├── users/                      # User dashboard
│   ├── profile.php
│   ├── my_donations.php
│   └── my_volunteers.php
│
├── webhooks/                   # Payment webhooks
│   └── sepay_webhook.php
│
├── database/                   # Database files
│   └── charity_event.sql       # SQL dump
│
├── logs/                       # Application logs
│   ├── errors.log
│   ├── donations.log
│   └── contacts.log
│
├── .htaccess                   # Apache config
├── index.php                   # Homepage
└── README.md                   # This file
```

---

## 🔌 API Documentation

### Sepay Webhook

**Endpoint:** `/webhooks/sepay_webhook.php`

**Method:** POST

**Headers:**
```
Content-Type: application/json
X-Sepay-Signature: <signature>
```

**Request Body:**
```json
{
  "transaction_id": "TXN123456789",
  "account_number": "1234567890",
  "amount": 100000,
  "description": "DONATE EVENT123",
  "transaction_date": "2024-03-14 10:30:00",
  "status": "completed"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Donation recorded successfully"
}
```

---

## 📸 Screenshots

### Homepage
![Homepage](screenshots/homepage.png)

### Event Listing
![Events](screenshots/events.png)

### Benefactor Dashboard
![Dashboard](screenshots/benefactor-dashboard.png)

### Admin Panel
![Admin](screenshots/admin-panel.png)

---

## 🐛 Troubleshooting

### Lỗi: "Database Connection Failed"

**Nguyên nhân:** MySQL không chạy hoặc sai port

**Giải pháp:**
1. Kiểm tra MySQL đang chạy trong XAMPP
2. Kiểm tra port trong `config/db.php` (phải là 3309)
3. Kiểm tra `my.ini` MySQL config

### Lỗi: "Headers already sent"

**Nguyên nhân:** Output trước khi gọi `header()`

**Giải pháp:**
1. Kiểm tra không có `echo`, `print`, hoặc HTML trước `<?php`
2. Kiểm tra không có khoảng trắng/BOM ở đầu file PHP
3. Enable output buffering: `ob_start()` ở đầu file

### Lỗi: Sepay webhook không hoạt động

**Nguyên nhân:** Ngrok không chạy hoặc webhook URL sai

**Giải pháp:**
1. Chạy ngrok: `ngrok http 80`
2. Copy URL từ ngrok (ví dụ: `https://abc123.ngrok.io`)
3. Cập nhật `SEPAY_WEBHOOK_URL` trong `config/payment.php`
4. Đăng ký lại webhook URL trên Sepay dashboard
5. Test webhook bằng tool: https://webhook.site/

### Lỗi: Upload file không hoạt động

**Nguyên nhân:** Permissions hoặc thư mục không tồn tại

**Giải pháp:**
```bash
# Tạo thư mục nếu chưa có
mkdir -p public/uploads/avatars
mkdir -p public/uploads/events
mkdir -p public/uploads/news

# Set permissions (Linux/Mac)
chmod -R 755 public/uploads

# Check PHP upload settings
php -i | grep upload
# upload_max_filesize = 10M
# post_max_size = 10M
```

### Lỗi: Session không lưu

**Nguyên nhân:** Session path không có quyền ghi

**Giải pháp:**
1. Kiểm tra `php.ini`:
   ```ini
   session.save_path = "C:/xampp/tmp"
   ```
2. Tạo thư mục nếu chưa có
3. Hoặc dùng custom session path trong code:
   ```php
   session_save_path(__DIR__ . '/sessions');
   ```

### Lỗi: CKEditor không load

**Nguyên nhân:** CDN bị block hoặc internet chậm

**Giải pháp:**
1. Kiểm tra kết nối internet
2. Thử CDN khác:
   ```html
   <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
   ```
3. Hoặc download CKEditor về local

---

## 🤝 Đóng góp

Chúng tôi rất hoan nghênh mọi đóng góp! Hãy làm theo các bước sau:

1. Fork repository này
2. Tạo branch mới: `git checkout -b feature/AmazingFeature`
3. Commit changes: `git commit -m 'Add some AmazingFeature'`
4. Push to branch: `git push origin feature/AmazingFeature`
5. Tạo Pull Request

### Coding Standards

- PHP: PSR-12
- JavaScript: ES6+
- Indentation: 4 spaces
- Line endings: LF (Unix)
- Comments: Tiếng Việt cho business logic, English cho technical

---

## 📜 License

Dự án này được phân phối dưới giấy phép MIT License. Xem file [LICENSE](LICENSE) để biết thêm chi tiết.

---

## 📞 Liên hệ

**Developer Team:**
- Email: contact@charityevent.vn
- Facebook: https://facebook.com/charityevent
- Website: https://charityevent.vn

**Project Repository:**
- GitHub: https://github.com/your-username/DACS-CharityManager
- Issues: https://github.com/your-username/DACS-CharityManager/issues

---

## 🙏 Acknowledgments

- [Bootstrap](https://getbootstrap.com/) - CSS Framework
- [Chart.js](https://www.chartjs.org/) - Charts
- [Font Awesome](https://fontawesome.com/) - Icons
- [CKEditor](https://ckeditor.com/) - WYSIWYG Editor
- [Sepay](https://sepay.vn/) - Payment Gateway
- [Ngrok](https://ngrok.com/) - Tunneling Service

---

## 🗺️ Roadmap

### Version 1.1 (Q2 2024)
- [ ] Tích hợp thêm cổng thanh toán: Momo, VNPay
- [ ] Multi-language support (English, Vietnamese)
- [ ] Mobile app (React Native)
- [ ] Real-time chat giữa benefactor và donor

### Version 1.2 (Q3 2024)
- [ ] Blockchain integration cho minh bạch hóa
- [ ] AI-powered fraud detection
- [ ] Social media sharing optimization
- [ ] Advanced analytics dashboard

### Version 2.0 (Q4 2024)
- [ ] Microservices architecture
- [ ] Docker support
- [ ] GraphQL API
- [ ] Progressive Web App (PWA)

---

## ⚡ Quick Start (TL;DR)

```bash
# 1. Clone
git clone https://github.com/your-username/DACS-CharityManager.git

# 2. Copy to htdocs
cp -r DACS-CharityManager /xampp/htdocs/

# 3. Create database
mysql -u root -P 3309 -e "CREATE DATABASE charity_event"
mysql -u root -P 3309 charity_event < database/charity_event.sql

# 4. Configure
# Edit config/db.php, config/payment.php

# 5. Start ngrok
ngrok http 80

# 6. Access
http://localhost/DACS-CharityManager
```

---

<div align="center">

Made with ❤️ by **Charity Event Team**

⭐ Star us on GitHub — it motivates us a lot!

[Report Bug](https://github.com/your-username/DACS-CharityManager/issues) · [Request Feature](https://github.com/your-username/DACS-CharityManager/issues)

</div>
