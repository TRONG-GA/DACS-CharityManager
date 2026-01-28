CREATE DATABASE IF NOT EXISTS charity_event CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE charity_event;

-- =====================================================
-- 1. USERS TABLE (3 Roles: user, benefactor, admin)
-- =====================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    address TEXT,
    city VARCHAR(100),
    district VARCHAR(100),
    
    -- Role Management
    role ENUM('user', 'benefactor', 'admin') DEFAULT 'user',
    
    -- Benefactor Status (Trạng thái nhà hảo tâm)
    benefactor_status ENUM('none', 'pending', 'approved', 'rejected') DEFAULT 'none',
    benefactor_verified_at TIMESTAMP NULL,
    benefactor_verified_by INT NULL, -- Admin ID
    
    -- Account Status
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_role (role),
    INDEX idx_benefactor_status (benefactor_status),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 2. BENEFACTOR APPLICATIONS (Đăng ký nhà hảo tâm - KYC)
-- =====================================================
CREATE TABLE benefactor_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    
    -- Personal Information
    full_legal_name VARCHAR(100) NOT NULL,
    id_card_number VARCHAR(20) NOT NULL, -- CCCD/CMND
    id_card_front VARCHAR(255) NOT NULL, -- Ảnh mặt trước
    id_card_back VARCHAR(255) NOT NULL, -- Ảnh mặt sau
    date_of_birth DATE NOT NULL,
    place_of_birth VARCHAR(255),
    
    -- Contact Information
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    ward VARCHAR(100),
    permanent_address TEXT,
    
    -- Organization Information (Optional)
    organization_name VARCHAR(255),
    organization_type VARCHAR(100), -- Cá nhân/Tổ chức/Doanh nghiệp
    tax_code VARCHAR(50),
    business_license VARCHAR(255), -- Giấy phép kinh doanh
    
    -- Financial Information
    bank_account VARCHAR(50) NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    bank_branch VARCHAR(100),
    account_holder VARCHAR(100) NOT NULL,
    financial_proof VARCHAR(255), -- Sao kê tài khoản
    
    -- Motivation
    motivation TEXT NOT NULL,
    previous_experience TEXT, -- Kinh nghiệm tổ chức từ thiện
    expected_activities TEXT, -- Hoạt động dự kiến
    
    -- Application Status
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    rejection_reason TEXT,
    admin_notes TEXT,
    
    -- Review Information
    reviewed_by INT, -- Admin ID
    reviewed_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 3. EVENTS TABLE (Sự kiện từ thiện)
-- =====================================================
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL, -- Benefactor ID
    
    -- Basic Information
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    content LONGTEXT NOT NULL,
    
    -- Media
    thumbnail VARCHAR(255),
    gallery TEXT, -- JSON array of images
    video_url VARCHAR(255), -- YouTube/Vimeo embed
    
    -- Category & Location
    category ENUM('medical', 'education', 'disaster', 'children', 'elderly', 'community', 'environment', 'other') DEFAULT 'other',
    location VARCHAR(255) NOT NULL,
    province VARCHAR(100) NOT NULL,
    district VARCHAR(100),
    ward VARCHAR(100),
    specific_address TEXT,
    
    -- Financial Information
    target_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    current_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    
    -- Volunteer Information
    volunteer_needed INT DEFAULT 0, -- Số tình nguyện viên cần
    volunteer_registered INT DEFAULT 0, -- Số đã đăng ký
    volunteer_skills TEXT, -- Kỹ năng cần thiết (JSON)
    
    -- Timeline
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    registration_deadline DATE, -- Hạn đăng ký tình nguyện
    
    -- Status Management
    status ENUM('pending', 'approved', 'rejected', 'ongoing', 'completed', 'closed') DEFAULT 'pending',
    rejection_reason TEXT,
    
    -- Display Settings
    priority INT DEFAULT 0, -- Thứ tự ưu tiên hiển thị
    is_featured BOOLEAN DEFAULT FALSE, -- Sự kiện nổi bật
    is_urgent BOOLEAN DEFAULT FALSE, -- Khẩn cấp
    views INT DEFAULT 0,
    
    -- Transparency Report
    has_report BOOLEAN DEFAULT FALSE,
    report_uploaded_at TIMESTAMP NULL,
    
    -- Admin Review
    approved_by INT,
    approved_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_featured (is_featured),
    INDEX idx_end_date (end_date),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 4. DONATIONS TABLE (Quyên góp)
-- =====================================================
CREATE TABLE donations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT, -- NULL if anonymous
    event_id INT NOT NULL,
    
    -- Donor Information (for non-logged users)
    donor_name VARCHAR(100),
    donor_email VARCHAR(100),
    donor_phone VARCHAR(20),
    
    -- Donation Details
    amount DECIMAL(15,2) NOT NULL,
    message TEXT,
    
    -- Payment Information
    payment_method ENUM('bank_transfer', 'momo', 'vnpay', 'zalopay', 'cash') DEFAULT 'bank_transfer',
    transaction_id VARCHAR(100),
    payment_proof VARCHAR(255), -- Ảnh chụp chuyển khoản
    
    -- Status
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    
    -- Privacy
    is_anonymous BOOLEAN DEFAULT FALSE,
    show_amount BOOLEAN DEFAULT TRUE,
    
    -- Tracking
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 5. EVENT VOLUNTEERS (Đăng ký tình nguyện viên)
-- =====================================================
CREATE TABLE event_volunteers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    event_id INT NOT NULL,
    
    -- Personal Information
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    
    -- Additional Information
    occupation VARCHAR(100),
    skills TEXT, -- JSON array: ["y tế", "nấu ăn", "vận chuyển"]
    availability TEXT, -- Thời gian rảnh
    experience TEXT, -- Kinh nghiệm tình nguyện
    motivation TEXT NOT NULL, -- Lý do tham gia
    
    -- Emergency Contact
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relation VARCHAR(50),
    
    -- Status
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    rejection_reason TEXT,
    
    -- Check-in/out
    checked_in_at TIMESTAMP NULL,
    checked_out_at TIMESTAMP NULL,
    attendance_confirmed BOOLEAN DEFAULT FALSE,
    
    -- Feedback
    volunteer_feedback TEXT, -- Phản hồi của tình nguyện viên
    organizer_feedback TEXT, -- Đánh giá của nhà tổ chức
    rating INT, -- 1-5 stars
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 6. TRANSPARENCY REPORTS (Báo cáo minh bạch)
-- =====================================================
CREATE TABLE transparency_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL UNIQUE,
    
    -- Financial Summary
    total_income DECIMAL(15,2) NOT NULL,
    total_expense DECIMAL(15,2) NOT NULL,
    remaining_amount DECIMAL(15,2) NOT NULL,
    
    -- Detailed Breakdown (JSON)
    expense_breakdown TEXT, -- JSON: [{"category": "Y tế", "amount": 1000000, "description": "..."}]
    income_sources TEXT, -- JSON: [{"source": "Quyên góp", "amount": 5000000}]
    
    -- Beneficiaries
    beneficiary_count INT DEFAULT 0,
    beneficiary_list TEXT, -- JSON: [{"name": "Nguyễn Văn A", "amount": 500000}]
    
    -- Evidence
    images TEXT, -- JSON array of image URLs
    documents TEXT, -- JSON array of document URLs
    excel_file VARCHAR(255), -- File Excel chi tiết
    
    -- Additional Information
    summary TEXT NOT NULL, -- Tóm tắt báo cáo
    impact_description TEXT, -- Mô tả tác động
    lessons_learned TEXT, -- Bài học kinh nghiệm
    
    -- Status
    status ENUM('draft', 'published') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 7. EVENT UPDATES (Cập nhật tiến độ sự kiện)
-- =====================================================
CREATE TABLE event_updates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL, -- Benefactor
    
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    images TEXT, -- JSON array
    
    update_type ENUM('progress', 'milestone', 'completion', 'urgent') DEFAULT 'progress',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 8. NEWS/ARTICLES (Tin tức & Hoạt động)
-- =====================================================
CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    author_id INT NOT NULL,
    
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    
    thumbnail VARCHAR(255),
    gallery TEXT, -- JSON array
    
    category ENUM('news', 'activity', 'story', 'announcement', 'media') DEFAULT 'news',
    
    -- Display Settings
    is_featured BOOLEAN DEFAULT FALSE,
    is_breaking BOOLEAN DEFAULT FALSE, -- Tin nóng
    priority INT DEFAULT 0,
    views INT DEFAULT 0,
    
    -- SEO
    meta_description TEXT,
    meta_keywords TEXT,
    
    -- Status
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 9. TERMS & CONDITIONS (Điều khoản sử dụng)
-- =====================================================
CREATE TABLE terms_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    type ENUM('donation', 'volunteer', 'benefactor', 'event_creation') NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    
    version VARCHAR(20) NOT NULL, -- v1.0, v1.1, etc.
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 10. USER TERMS ACCEPTANCE (Log chấp nhận điều khoản)
-- =====================================================
CREATE TABLE user_terms_acceptance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    terms_id INT NOT NULL,
    
    action_type ENUM('donation', 'volunteer', 'benefactor_apply', 'event_create') NOT NULL,
    reference_id INT, -- ID của donation/volunteer/event
    
    -- Tracking
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    accepted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (terms_id) REFERENCES terms_conditions(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 11. CONTACTS (Liên hệ)
-- =====================================================
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    status ENUM('new', 'processing', 'resolved', 'closed') DEFAULT 'new',
    admin_notes TEXT,
    replied_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 12. PARTNERS (Đối tác)
-- =====================================================
CREATE TABLE partners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    name VARCHAR(100) NOT NULL,
    logo VARCHAR(255),
    website VARCHAR(255),
    description TEXT,
    
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 13. SYSTEM STATISTICS (Thống kê hệ thống)
-- =====================================================
CREATE TABLE statistics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    total_events INT DEFAULT 0,
    total_donations DECIMAL(15,2) DEFAULT 0,
    total_donors INT DEFAULT 0,
    total_volunteers INT DEFAULT 0,
    total_benefactors INT DEFAULT 0,
    
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 14. NOTIFICATIONS (Thông báo)
-- =====================================================
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    
    type ENUM('donation', 'volunteer', 'event', 'system', 'admin') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    link VARCHAR(255), -- URL liên quan
    
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERT DEFAULT DATA
-- =====================================================

-- Default Admin
INSERT INTO users (fullname, email, password, role, status) VALUES
('Administrator', 'admin@charityevent.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');
-- Password: password

-- Initial Statistics
INSERT INTO statistics (total_events, total_donations, total_donors, total_volunteers, total_benefactors) VALUES 
(0, 0, 0, 0, 0);

-- Default Terms & Conditions
INSERT INTO terms_conditions (type, title, content, version) VALUES
('donation', 'Điều khoản quyên góp', 
'<h3>ĐIỀU KHOẢN QUYÊN GÓP</h3>
<p>Khi thực hiện quyên góp trên nền tảng Charity Event, bạn đồng ý với các điều khoản sau:</p>
<ol>
<li>Số tiền quyên góp không thể hoàn lại sau khi đã xác nhận.</li>
<li>Toàn bộ số tiền sẽ được chuyển đến người tổ chức sự kiện.</li>
<li>Bạn có quyền yêu cầu báo cáo minh bạch sau khi sự kiện kết thúc.</li>
<li>Thông tin cá nhân của bạn sẽ được bảo mật theo chính sách.</li>
<li>Charity Event không chịu trách nhiệm về việc sử dụng tiền của người tổ chức.</li>
</ol>', 
'1.0'),

('volunteer', 'Điều khoản tình nguyện viên',
'<h3>ĐIỀU KHOẢN TÌNH NGUYỆN VIÊN</h3>
<p>Khi đăng ký làm tình nguyện viên, bạn cam kết:</p>
<ol>
<li>Tham gia đầy đủ theo lịch trình đã đăng ký.</li>
<li>Tuân thủ quy định của người tổ chức sự kiện.</li>
<li>Chịu trách nhiệm về an toàn bản thân.</li>
<li>Không được sử dụng hoạt động tình nguyện cho mục đích thương mại.</li>
<li>Có thể bị hủy đăng ký nếu vi phạm quy định.</li>
</ol>',
'1.0'),

('benefactor', 'Điều khoản nhà hảo tâm',
'<h3>ĐIỀU KHOẢN NHÀ HẢO TÂM</h3>
<p>Khi đăng ký trở thành nhà hảo tâm, bạn cam kết:</p>
<ol>
<li>Cung cấp thông tin chính xác và trung thực.</li>
<li>Tuân thủ pháp luật Việt Nam về hoạt động từ thiện.</li>
<li>Chịu trách nhiệm về tính minh bạch của các sự kiện.</li>
<li>Xuất báo cáo chi tiết sau khi sự kiện kết thúc.</li>
<li>Có thể bị thu hồi quyền nếu vi phạm nghiêm trọng.</li>
</ol>',
'1.0'),

('event_creation', 'Điều khoản tạo sự kiện',
'<h3>ĐIỀU KHOẢN TẠO SỰ KIỆN</h3>
<p>Khi tạo sự kiện từ thiện, bạn đồng ý:</p>
<ol>
<li>Sự kiện phải có mục đích từ thiện rõ ràng và hợp pháp.</li>
<li>Thông tin sự kiện phải chính xác và đầy đủ.</li>
<li>Phải cập nhật tiến độ định kỳ cho người quyên góp.</li>
<li>Xuất báo cáo minh bạch trong vòng 30 ngày sau khi kết thúc.</li>
<li>Chịu trách nhiệm trước pháp luật về việc sử dụng tiền quyên góp.</li>
<li>Tuân thủ hướng dẫn và quy định của Charity Event.</li>
</ol>',
'1.0');

-- Sample News
INSERT INTO news (author_id, title, slug, excerpt, content, thumbnail, category, status, is_featured, published_at) VALUES
(1, 'Ra mắt nền tảng Charity Event - Kết nối yêu thương', 'ra-mat-nen-tang-charity-event', 
'Charity Event chính thức ra mắt với sứ mệnh kết nối những tấm lòng nhân ái, mang đến sự minh bạch và hiệu quả cho hoạt động từ thiện.',
'<p>Ngày 01/01/2026, nền tảng <strong>Charity Event</strong> chính thức ra mắt cộng đồng...</p>',
'news-1.jpg', 'announcement', 'published', TRUE, NOW()),

(1, 'Hướng dẫn quyên góp trên Charity Event', 'huong-dan-quyen-gop',
'Quy trình quyên góp đơn giản, nhanh chóng và an toàn trên nền tảng Charity Event.',
'<p>Bài viết hướng dẫn chi tiết cách quyên góp...</p>',
'news-2.jpg', 'news', 'published', FALSE, NOW());
