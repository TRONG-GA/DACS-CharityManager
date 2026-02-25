-- =====================================================
-- SEED SAMPLE DATA FOR CHARITY EVENT
-- Chạy file này sau khi đã có database schema
-- =====================================================

USE charity_event;

-- =====================================================
-- 1. TẠO USER MẪU
-- =====================================================

-- Tạo 1 benefactor đã verified
INSERT INTO users (fullname, email, password, phone, role, benefactor_status, status) VALUES
('Nguyễn Văn Hảo', 'benefactor1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 'benefactor', 'approved', 'active'),
('Trần Thị Tâm', 'benefactor2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'benefactor', 'approved', 'active'),
('Lê Văn Nghĩa', 'benefactor3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0923456789', 'benefactor', 'approved', 'active');
-- Password: password

-- Tạo một số user thường
INSERT INTO users (fullname, email, password, phone, role, status) VALUES
('Phạm Minh Anh', 'user1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0934567890', 'user', 'active'),
('Hoàng Thị Lan', 'user2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0945678901', 'user', 'active'),
('Đặng Quốc Huy', 'user3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0956789012', 'user', 'active');

-- =====================================================
-- 2. TẠO SỰ KIỆN MẪU (15 events)
-- =====================================================

-- Medical events
INSERT INTO events (user_id, title, slug, description, content, thumbnail, category, location, province, district, target_amount, current_amount, volunteer_needed, volunteer_registered, start_date, end_date, status, is_featured, is_urgent, views) VALUES
(2, 'Hỗ trợ phẫu thuật tim cho bé Minh An', 'ho-tro-phau-thuat-tim-be-minh-an', 'Bé Minh An 5 tuổi bị tim bẩm sinh, cần phẫu thuật gấp nhưng gia đình không đủ chi phí', '<h3>Hoàn cảnh</h3><p>Bé Minh An sinh ra đã mắc bệnh tim bẩm sinh. Gia đình nghèo, bố mẹ làm công nhân, không đủ tiền phẫu thuật...</p><h3>Chi phí cần thiết</h3><ul><li>Phẫu thuật: 80 triệu</li><li>Điều trị sau phẫu thuật: 20 triệu</li></ul>', 'event1.jpg', 'medical', 'Bệnh viện Tim Hà Nội', 'Hà Nội', 'Đống Đa', 100000000, 65000000, 5, 3, '2026-02-01', '2026-03-15', 'approved', 1, 1, 324),

(2, 'Xây nhà tình thương cho gia đình chị Hồng', 'xay-nha-tinh-thuong-gia-dinh-chi-hong', 'Chị Hồng nuôi 3 con nhỏ, sống trong căn nhà dột nát cần được xây mới', '<h3>Hoàn cảnh</h3><p>Chị Hồng góa chồng, một mình nuôi 3 con nhỏ. Căn nhà cũ đã xuống cấp nghiêm trọng...</p>', 'event2.jpg', 'community', 'Xã Hòa Bình, Huyện Mỹ Đức', 'Hà Nội', 'Mỹ Đức', 50000000, 32000000, 15, 12, '2026-02-10', '2026-04-30', 'approved', 1, 0, 215),

(3, 'Khám chữa bệnh miễn phí cho người nghèo', 'kham-chua-benh-mien-phi-nguoi-ngheo', 'Chương trình khám bệnh, phát thuốc miễn phí cho 500 người dân nghèo vùng cao', '<h3>Nội dung chương trình</h3><p>Đoàn y tế sẽ lên vùng cao khám bệnh, phát thuốc miễn phí cho bà con...</p>', 'event3.jpg', 'medical', 'Huyện Mường Lát', 'Thanh Hóa', 'Mường Lát', 30000000, 28000000, 20, 18, '2026-02-15', '2026-02-17', 'approved', 0, 0, 189),

-- Education events
(2, 'Trao học bổng cho học sinh nghèo vượt khó', 'trao-hoc-bong-hoc-sinh-ngheo-vuot-kho', 'Trao 100 suất học bổng cho học sinh có hoàn cảnh khó khăn nhưng học giỏi', '<h3>Mục tiêu</h3><p>Hỗ trợ 100 học sinh nghèo vượt khó với mỗi suất 2 triệu đồng/năm...</p>', 'event4.jpg', 'education', 'Trường THCS Lý Tự Trọng', 'Hà Nội', 'Hoàng Mai', 200000000, 145000000, 10, 8, '2026-02-20', '2026-03-31', 'approved', 1, 0, 412),

(3, 'Xây dựng thư viện cho trường học vùng cao', 'xay-dung-thu-vien-truong-hoc-vung-cao', 'Xây dựng thư viện với 2000 đầu sách cho học sinh vùng cao', '<h3>Dự án</h3><p>Xây phòng thư viện 50m2, trang bị 2000 đầu sách phù hợp...</p>', 'event5.jpg', 'education', 'Trường Tiểu học Tà Lèng', 'Lai Châu', 'Mường Tè', 80000000, 54000000, 8, 5, '2026-02-25', '2026-05-30', 'approved', 0, 0, 167),

-- Disaster relief
(2, 'Cứu trợ đồng bào lũ lụt miền Trung', 'cuu-tro-dong-bao-lu-lut-mien-trung', 'Hỗ trợ lương thực, nhu yếu phẩm cho 1000 hộ dân bị lũ lụt', '<h3>Tình hình</h3><p>Mưa lũ kéo dài khiến hàng nghìn gia đình mất nhà cửa, mất mùa...</p>', 'event6.jpg', 'disaster', 'Huyện Hương Khê', 'Hà Tĩnh', 'Hương Khê', 150000000, 127000000, 30, 28, '2026-01-15', '2026-02-28', 'approved', 1, 1, 523),

(3, 'Hỗ trợ người dân vùng hạn hán', 'ho-tro-nguoi-dan-vung-han-han', 'Cung cấp nước sạch và lương thực cho vùng hạn hán', '<h3>Tình trạng</h3><p>Hạn hán kéo dài, thiếu nước sinh hoạt trầm trọng...</p>', 'event7.jpg', 'disaster', 'Huyện Krông Pắc', 'Đắk Lắk', 'Krông Pắc', 100000000, 89000000, 25, 22, '2026-02-01', '2026-03-20', 'approved', 0, 1, 298),

-- Children support
(2, 'Tết ấm cho trẻ em mồ côi', 'tet-am-cho-tre-em-mo-coi', 'Tổ chức Tết Trung thu cho 200 trẻ em mồ côi tại trại trẻ', '<h3>Chương trình</h3><p>Tổ chức đêm trung thu với bánh kẹo, quà tặng, văn nghệ...</p>', 'event8.jpg', 'children', 'Trại trẻ mồ côi Hà Cầu', 'Hà Nội', 'Hà Đông', 40000000, 38000000, 15, 15, '2026-02-10', '2026-02-28', 'approved', 1, 0, 345),

(3, 'Khám tim miễn phí cho trẻ em nghèo', 'kham-tim-mien-phi-tre-em-ngheo', 'Khám sàng lọc tim bẩm sinh cho 500 trẻ em vùng nghèo', '<h3>Mục đích</h3><p>Phát hiện sớm các bệnh lý tim bẩm sinh ở trẻ em...</p>', 'event9.jpg', 'children', 'Bệnh viện Nhi Trung ương', 'Hà Nội', 'Đống Đa', 60000000, 42000000, 12, 9, '2026-02-18', '2026-03-10', 'approved', 0, 0, 201),

-- Elderly support
(2, 'Tặng quà Tết cho người già neo đơn', 'tang-qua-tet-nguoi-gia-neo-don', 'Tặng 300 phần quà Tết cho người cao tuổi neo đơn', '<h3>Nội dung</h3><p>Mỗi phần quà gồm: gạo, dầu ăn, bánh kẹo, tiền mặt...</p>', 'event10.jpg', 'elderly', 'Phường Khương Trung', 'Hà Nội', 'Thanh Xuân', 45000000, 45000000, 10, 10, '2026-01-20', '2026-02-10', 'completed', 0, 0, 178),

(3, 'Chăm sóc sức khỏe người cao tuổi', 'cham-soc-suc-khoe-nguoi-cao-tuoi', 'Khám bệnh, tặng quà cho 200 cụ già tại viện dưỡng lão', '<h3>Hoạt động</h3><p>Khám bệnh, tư vấn sức khỏe, tặng quà, văn nghệ...</p>', 'event11.jpg', 'elderly', 'Viện dưỡng lão Hà Nội', 'Hà Nội', 'Đống Đa', 35000000, 31000000, 8, 7, '2026-02-22', '2026-03-05', 'approved', 0, 0, 145),

-- Environment
(2, 'Trồng cây xanh - Phủ xanh Việt Nam', 'trong-cay-xanh-phu-xanh-viet-nam', 'Trồng 10,000 cây xanh tại khu vực bị phá rừng', '<h3>Mục tiêu</h3><p>Khôi phục rừng, cải thiện môi trường sống...</p>', 'event12.jpg', 'environment', 'Huyện Hoàng Su Phì', 'Hà Giang', 'Hoàng Su Phì', 70000000, 48000000, 50, 42, '2026-03-01', '2026-04-30', 'approved', 1, 0, 267),

(3, 'Dọn rác biển - Bảo vệ đại dương', 'don-rac-bien-bao-ve-dai-duong', 'Chiến dịch thu gom rác thải nhựa trên bãi biển', '<h3>Kế hoạch</h3><p>Tổ chức 3 đợt dọn rác tại các bãi biển Đà Nẵng...</p>', 'event13.jpg', 'environment', 'Bãi biển Mỹ Khê', 'Đà Nẵng', 'Sơn Trà', 25000000, 23000000, 100, 95, '2026-02-28', '2026-03-15', 'approved', 0, 0, 312),

-- Other
(2, 'Trao tặng xe lăn cho người khuyết tật', 'trao-tang-xe-lan-nguoi-khuyet-tat', 'Tặng 50 chiếc xe lăn cho người khuyết tật có hoàn cảnh khó khăn', '<h3>Đối tượng</h3><p>Người khuyết tật nghèo, không có phương tiện di chuyển...</p>', 'event14.jpg', 'other', 'Trung tâm Phục hồi chức năng', 'TP.HCM', 'Quận 1', 120000000, 95000000, 5, 4, '2026-02-12', '2026-03-25', 'approved', 0, 0, 198),

(3, 'Mổ mắt miễn phí cho người nghèo', 'mo-mat-mien-phi-nguoi-ngheo', 'Phẫu thuật đục thủy tinh thể miễn phí cho 100 bệnh nhân', '<h3>Chương trình</h3><p>Phẫu thuật đục thủy tinh thể, tặng kính mắt...</p>', 'event15.jpg', 'medical', 'Bệnh viện Mắt Trung ương', 'Hà Nội', 'Ba Đình', 90000000, 72000000, 8, 6, '2026-02-05', '2026-03-18', 'approved', 0, 0, 234);

-- =====================================================
-- 3. TẠO DONATIONS MẪU
-- =====================================================

-- Donations cho event 1 (Phẫu thuật tim)
INSERT INTO donations (user_id, event_id, donor_name, donor_email, donor_phone, amount, message, payment_method, status, is_anonymous, created_at) VALUES
(5, 1, 'Phạm Minh Anh', 'user1@test.com', '0934567890', 5000000, 'Chúc bé mau khỏe!', 'bank_transfer', 'completed', 0, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6, 1, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', 10000000, 'Cầu mong bé sớm bình phục', 'momo', 'completed', 0, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(NULL, 1, 'Ẩn danh', NULL, NULL, 20000000, '', 'bank_transfer', 'completed', 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7, 1, 'Đặng Quốc Huy', 'user3@test.com', '0956789012', 15000000, 'Chúc bé may mắn!', 'vnpay', 'completed', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(NULL, 1, 'Nguyễn Văn Tùng', 'tungvn@gmail.com', '0967890123', 15000000, 'Một chút tấm lòng', 'bank_transfer', 'completed', 0, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Donations cho event 2 (Xây nhà)
INSERT INTO donations (user_id, event_id, donor_name, amount, payment_method, status, created_at) VALUES
(5, 2, 'Phạm Minh Anh', 3000000, 'bank_transfer', 'completed', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(6, 2, 'Hoàng Thị Lan', 5000000, 'momo', 'completed', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(NULL, 2, 'Ẩn danh', 10000000, 'bank_transfer', 'completed', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(7, 2, 'Đặng Quốc Huy', 7000000, 'bank_transfer', 'completed', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(NULL, 2, 'Ẩn danh', 7000000, 'vnpay', 'completed', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Donations cho các event khác (ngẫu nhiên)
INSERT INTO donations (user_id, event_id, donor_name, amount, payment_method, status, created_at) VALUES
(5, 3, 'Phạm Minh Anh', 2000000, 'bank_transfer', 'completed', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(6, 4, 'Hoàng Thị Lan', 5000000, 'momo', 'completed', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(7, 5, 'Đặng Quốc Huy', 3000000, 'vnpay', 'completed', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(5, 6, 'Phạm Minh Anh', 10000000, 'bank_transfer', 'completed', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(NULL, 7, 'Ẩn danh', 15000000, 'bank_transfer', 'completed', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(6, 8, 'Hoàng Thị Lan', 2000000, 'momo', 'completed', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7, 9, 'Đặng Quốc Huy', 4000000, 'vnpay', 'completed', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 11, 'Phạm Minh Anh', 3000000, 'bank_transfer', 'completed', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- =====================================================
-- 4. TẠO VOLUNTEERS MẪU
-- =====================================================

INSERT INTO event_volunteers (user_id, event_id, fullname, email, phone, gender, occupation, skills, motivation, status, created_at) VALUES
(5, 1, 'Phạm Minh Anh', 'user1@test.com', '0934567890', 'female', 'Nhân viên văn phòng', '["Kế toán", "Tin học văn phòng"]', 'Muốn giúp đỡ trẻ em có hoàn cảnh khó khăn', 'approved', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(6, 1, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', 'female', 'Giáo viên', '["Giảng dạy", "Tổ chức sự kiện"]', 'Yêu trẻ em, muốn lan tỏa yêu thương', 'approved', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(7, 1, 'Đặng Quốc Huy', 'user3@test.com', '0956789012', 'male', 'Kỹ sư', '["Xây dựng", "Sửa chữa"]', 'Có kinh nghiệm làm tình nguyện', 'approved', DATE_SUB(NOW(), INTERVAL 8 DAY)),

(5, 2, 'Phạm Minh Anh', 'user1@test.com', '0934567890', 'female', 'Nhân viên văn phòng', '["Kế toán", "Tổ chức"]', 'Muốn giúp gia đình khó khăn có nhà ở', 'approved', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(6, 2, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', 'female', 'Giáo viên', '["Nấu ăn", "Chăm sóc trẻ"]', 'Có thời gian rảnh, muốn giúp đỡ', 'approved', DATE_SUB(NOW(), INTERVAL 11 DAY)),

(7, 3, 'Đặng Quốc Huy', 'user3@test.com', '0956789012', 'male', 'Kỹ sư', '["Y tế cơ bản"]', 'Muốn đến vùng cao giúp bà con', 'approved', DATE_SUB(NOW(), INTERVAL 10 DAY)),

(5, 8, 'Phạm Minh Anh', 'user1@test.com', '0934567890', 'female', 'Nhân viên văn phòng', '["Văn nghệ", "Tổ chức"]', 'Yêu trẻ con, muốn mang niềm vui đến các em', 'approved', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6, 8, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', 'female', 'Giáo viên', '["Múa", "Hát"]', 'Muốn tổ chức chương trình vui cho trẻ', 'approved', DATE_SUB(NOW(), INTERVAL 4 DAY));

-- =====================================================
-- 5. CẬP NHẬT STATISTICS
-- =====================================================

UPDATE statistics SET
    total_events = (SELECT COUNT(*) FROM events WHERE status = 'approved'),
    total_donations = (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'completed'),
    total_donors = (SELECT COUNT(DISTINCT COALESCE(user_id, donor_email)) FROM donations WHERE status = 'completed'),
    total_volunteers = (SELECT COUNT(*) FROM event_volunteers WHERE status = 'approved'),
    total_benefactors = (SELECT COUNT(*) FROM users WHERE benefactor_status = 'approved')
WHERE id = 1;

-- =====================================================
-- DONE!
-- =====================================================

SELECT 'Sample data inserted successfully!' as message;
SELECT 
    (SELECT COUNT(*) FROM events WHERE status = 'approved') as total_events,
    (SELECT COUNT(*) FROM donations WHERE status = 'completed') as total_donations,
    (SELECT COUNT(*) FROM event_volunteers WHERE status = 'approved') as total_volunteers;
