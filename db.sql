-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3309
-- Generation Time: Feb 25, 2026 at 04:34 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `charity_event`
--

-- --------------------------------------------------------

--
-- Table structure for table `benefactor_applications`
--

CREATE TABLE `benefactor_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_legal_name` varchar(100) NOT NULL,
  `id_card_number` varchar(20) NOT NULL,
  `id_card_front` varchar(255) NOT NULL,
  `id_card_back` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `ward` varchar(100) DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `organization_name` varchar(255) DEFAULT NULL,
  `organization_type` varchar(100) DEFAULT NULL,
  `tax_code` varchar(50) DEFAULT NULL,
  `business_license` varchar(255) DEFAULT NULL,
  `bank_account` varchar(50) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_branch` varchar(100) DEFAULT NULL,
  `account_holder` varchar(100) NOT NULL,
  `financial_proof` varchar(255) DEFAULT NULL,
  `motivation` text NOT NULL,
  `previous_experience` text DEFAULT NULL,
  `expected_activities` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','processing','resolved','closed') DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `donor_name` varchar(100) DEFAULT NULL,
  `donor_email` varchar(100) DEFAULT NULL,
  `donor_phone` varchar(20) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `message` text DEFAULT NULL,
  `payment_method` enum('bank_transfer','momo','vnpay','zalopay','cash') DEFAULT 'bank_transfer',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `is_anonymous` tinyint(1) DEFAULT 0,
  `show_amount` tinyint(1) DEFAULT 1,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `user_id`, `event_id`, `donor_name`, `donor_email`, `donor_phone`, `amount`, `message`, `payment_method`, `transaction_id`, `payment_proof`, `status`, `is_anonymous`, `show_amount`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 'Phạm Minh Anh', 'user1@test.com', '0934567890', 5000000.00, 'Chúc bé mau khỏe!', 'bank_transfer', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-20 15:17:28', '2026-02-25 15:17:28'),
(2, 6, 1, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', 10000000.00, 'Cầu mong bé sớm bình phục', 'momo', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-21 15:17:28', '2026-02-25 15:17:28'),
(3, NULL, 1, 'Ẩn danh', NULL, NULL, 20000000.00, '', 'bank_transfer', NULL, NULL, 'completed', 1, 1, NULL, NULL, '2026-02-22 15:17:28', '2026-02-25 15:17:28'),
(4, 7, 1, 'Đặng Quốc Huy', 'user3@test.com', '0956789012', 15000000.00, 'Chúc bé may mắn!', 'vnpay', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-23 15:17:28', '2026-02-25 15:17:28'),
(5, NULL, 1, 'Nguyễn Văn Tùng', 'tungvn@gmail.com', '0967890123', 15000000.00, 'Một chút tấm lòng', 'bank_transfer', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-24 15:17:28', '2026-02-25 15:17:28'),
(6, 5, 2, 'Phạm Minh Anh', NULL, NULL, 3000000.00, NULL, 'bank_transfer', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-18 15:17:28', '2026-02-25 15:17:28'),
(7, 6, 2, 'Hoàng Thị Lan', NULL, NULL, 5000000.00, NULL, 'momo', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-19 15:17:28', '2026-02-25 15:17:28'),
(8, NULL, 2, 'Ẩn danh', NULL, NULL, 10000000.00, NULL, 'bank_transfer', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-20 15:17:28', '2026-02-25 15:17:28'),
(9, 7, 2, 'Đặng Quốc Huy', NULL, NULL, 7000000.00, NULL, 'bank_transfer', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-21 15:17:28', '2026-02-25 15:17:28'),
(10, NULL, 2, 'Ẩn danh', NULL, NULL, 7000000.00, NULL, 'vnpay', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-23 15:17:28', '2026-02-25 15:17:28'),
(11, 5, 3, 'Phạm Minh Anh', NULL, NULL, 2000000.00, NULL, 'bank_transfer', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-17 15:17:28', '2026-02-25 15:17:28'),
(12, 6, 4, 'Hoàng Thị Lan', NULL, NULL, 5000000.00, NULL, 'momo', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-18 15:17:28', '2026-02-25 15:17:28'),
(13, 7, 5, 'Đặng Quốc Huy', NULL, NULL, 3000000.00, NULL, 'vnpay', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-19 15:17:28', '2026-02-25 15:17:28'),
(14, 5, 6, 'Phạm Minh Anh', NULL, NULL, 10000000.00, NULL, 'bank_transfer', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-20 15:17:28', '2026-02-25 15:17:28'),
(15, NULL, 7, 'Ẩn danh', NULL, NULL, 15000000.00, NULL, 'bank_transfer', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-21 15:17:28', '2026-02-25 15:17:28'),
(16, 6, 8, 'Hoàng Thị Lan', NULL, NULL, 2000000.00, NULL, 'momo', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-22 15:17:28', '2026-02-25 15:17:28'),
(17, 7, 9, 'Đặng Quốc Huy', NULL, NULL, 4000000.00, NULL, 'vnpay', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-23 15:17:28', '2026-02-25 15:17:28'),
(18, 5, 11, 'Phạm Minh Anh', NULL, NULL, 3000000.00, NULL, 'bank_transfer', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-24 15:17:28', '2026-02-25 15:17:28');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `content` longtext NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `category` enum('medical','education','disaster','children','elderly','community','environment','other') DEFAULT 'other',
  `location` varchar(255) NOT NULL,
  `province` varchar(100) NOT NULL,
  `district` varchar(100) DEFAULT NULL,
  `ward` varchar(100) DEFAULT NULL,
  `specific_address` text DEFAULT NULL,
  `target_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `current_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `volunteer_needed` int(11) DEFAULT 0,
  `volunteer_registered` int(11) DEFAULT 0,
  `volunteer_skills` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `registration_deadline` date DEFAULT NULL,
  `status` enum('pending','approved','rejected','ongoing','completed','closed') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `priority` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_urgent` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `has_report` tinyint(1) DEFAULT 0,
  `report_uploaded_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `title`, `slug`, `description`, `content`, `thumbnail`, `gallery`, `video_url`, `category`, `location`, `province`, `district`, `ward`, `specific_address`, `target_amount`, `current_amount`, `volunteer_needed`, `volunteer_registered`, `volunteer_skills`, `start_date`, `end_date`, `registration_deadline`, `status`, `rejection_reason`, `priority`, `is_featured`, `is_urgent`, `views`, `has_report`, `report_uploaded_at`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'Hỗ trợ phẫu thuật tim cho bé Minh An', 'ho-tro-phau-thuat-tim-be-minh-an', 'Bé Minh An 5 tuổi bị tim bẩm sinh, cần phẫu thuật gấp nhưng gia đình không đủ chi phí', '<h3>Hoàn cảnh</h3><p>Bé Minh An sinh ra đã mắc bệnh tim bẩm sinh. Gia đình nghèo, bố mẹ làm công nhân, không đủ tiền phẫu thuật...</p><h3>Chi phí cần thiết</h3><ul><li>Phẫu thuật: 80 triệu</li><li>Điều trị sau phẫu thuật: 20 triệu</li></ul>', 'event1.jpg', NULL, NULL, 'medical', 'Bệnh viện Tim Hà Nội', 'Hà Nội', 'Đống Đa', NULL, NULL, 100000000.00, 65000000.00, 5, 3, NULL, '2026-02-01', '2026-03-15', NULL, 'approved', NULL, 0, 1, 1, 324, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(2, 2, 'Xây nhà tình thương cho gia đình chị Hồng', 'xay-nha-tinh-thuong-gia-dinh-chi-hong', 'Chị Hồng nuôi 3 con nhỏ, sống trong căn nhà dột nát cần được xây mới', '<h3>Hoàn cảnh</h3><p>Chị Hồng góa chồng, một mình nuôi 3 con nhỏ. Căn nhà cũ đã xuống cấp nghiêm trọng...</p>', 'event2.jpg', NULL, NULL, 'community', 'Xã Hòa Bình, Huyện Mỹ Đức', 'Hà Nội', 'Mỹ Đức', NULL, NULL, 50000000.00, 32000000.00, 15, 12, NULL, '2026-02-10', '2026-04-30', NULL, 'approved', NULL, 0, 1, 0, 215, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(3, 3, 'Khám chữa bệnh miễn phí cho người nghèo', 'kham-chua-benh-mien-phi-nguoi-ngheo', 'Chương trình khám bệnh, phát thuốc miễn phí cho 500 người dân nghèo vùng cao', '<h3>Nội dung chương trình</h3><p>Đoàn y tế sẽ lên vùng cao khám bệnh, phát thuốc miễn phí cho bà con...</p>', 'event3.jpg', NULL, NULL, 'medical', 'Huyện Mường Lát', 'Thanh Hóa', 'Mường Lát', NULL, NULL, 30000000.00, 28000000.00, 20, 18, NULL, '2026-02-15', '2026-02-17', NULL, 'approved', NULL, 0, 0, 0, 189, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(4, 2, 'Trao học bổng cho học sinh nghèo vượt khó', 'trao-hoc-bong-hoc-sinh-ngheo-vuot-kho', 'Trao 100 suất học bổng cho học sinh có hoàn cảnh khó khăn nhưng học giỏi', '<h3>Mục tiêu</h3><p>Hỗ trợ 100 học sinh nghèo vượt khó với mỗi suất 2 triệu đồng/năm...</p>', 'event4.jpg', NULL, NULL, 'education', 'Trường THCS Lý Tự Trọng', 'Hà Nội', 'Hoàng Mai', NULL, NULL, 200000000.00, 145000000.00, 10, 8, NULL, '2026-02-20', '2026-03-31', NULL, 'approved', NULL, 0, 1, 0, 412, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(5, 3, 'Xây dựng thư viện cho trường học vùng cao', 'xay-dung-thu-vien-truong-hoc-vung-cao', 'Xây dựng thư viện với 2000 đầu sách cho học sinh vùng cao', '<h3>Dự án</h3><p>Xây phòng thư viện 50m2, trang bị 2000 đầu sách phù hợp...</p>', 'event5.jpg', NULL, NULL, 'education', 'Trường Tiểu học Tà Lèng', 'Lai Châu', 'Mường Tè', NULL, NULL, 80000000.00, 54000000.00, 8, 5, NULL, '2026-02-25', '2026-05-30', NULL, 'approved', NULL, 0, 0, 0, 167, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(6, 2, 'Cứu trợ đồng bào lũ lụt miền Trung', 'cuu-tro-dong-bao-lu-lut-mien-trung', 'Hỗ trợ lương thực, nhu yếu phẩm cho 1000 hộ dân bị lũ lụt', '<h3>Tình hình</h3><p>Mưa lũ kéo dài khiến hàng nghìn gia đình mất nhà cửa, mất mùa...</p>', 'event6.jpg', NULL, NULL, 'disaster', 'Huyện Hương Khê', 'Hà Tĩnh', 'Hương Khê', NULL, NULL, 150000000.00, 127000000.00, 30, 28, NULL, '2026-01-15', '2026-02-28', NULL, 'approved', NULL, 0, 1, 1, 523, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(7, 3, 'Hỗ trợ người dân vùng hạn hán', 'ho-tro-nguoi-dan-vung-han-han', 'Cung cấp nước sạch và lương thực cho vùng hạn hán', '<h3>Tình trạng</h3><p>Hạn hán kéo dài, thiếu nước sinh hoạt trầm trọng...</p>', 'event7.jpg', NULL, NULL, 'disaster', 'Huyện Krông Pắc', 'Đắk Lắk', 'Krông Pắc', NULL, NULL, 100000000.00, 89000000.00, 25, 22, NULL, '2026-02-01', '2026-03-20', NULL, 'approved', NULL, 0, 0, 1, 298, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(8, 2, 'Tết ấm cho trẻ em mồ côi', 'tet-am-cho-tre-em-mo-coi', 'Tổ chức Tết Trung thu cho 200 trẻ em mồ côi tại trại trẻ', '<h3>Chương trình</h3><p>Tổ chức đêm trung thu với bánh kẹo, quà tặng, văn nghệ...</p>', 'event8.jpg', NULL, NULL, 'children', 'Trại trẻ mồ côi Hà Cầu', 'Hà Nội', 'Hà Đông', NULL, NULL, 40000000.00, 38000000.00, 15, 15, NULL, '2026-02-10', '2026-02-28', NULL, 'approved', NULL, 0, 1, 0, 345, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(9, 3, 'Khám tim miễn phí cho trẻ em nghèo', 'kham-tim-mien-phi-tre-em-ngheo', 'Khám sàng lọc tim bẩm sinh cho 500 trẻ em vùng nghèo', '<h3>Mục đích</h3><p>Phát hiện sớm các bệnh lý tim bẩm sinh ở trẻ em...</p>', 'event9.jpg', NULL, NULL, 'children', 'Bệnh viện Nhi Trung ương', 'Hà Nội', 'Đống Đa', NULL, NULL, 60000000.00, 42000000.00, 12, 9, NULL, '2026-02-18', '2026-03-10', NULL, 'approved', NULL, 0, 0, 0, 201, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(10, 2, 'Tặng quà Tết cho người già neo đơn', 'tang-qua-tet-nguoi-gia-neo-don', 'Tặng 300 phần quà Tết cho người cao tuổi neo đơn', '<h3>Nội dung</h3><p>Mỗi phần quà gồm: gạo, dầu ăn, bánh kẹo, tiền mặt...</p>', 'event10.jpg', NULL, NULL, 'elderly', 'Phường Khương Trung', 'Hà Nội', 'Thanh Xuân', NULL, NULL, 45000000.00, 45000000.00, 10, 10, NULL, '2026-01-20', '2026-02-10', NULL, 'completed', NULL, 0, 0, 0, 178, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(11, 3, 'Chăm sóc sức khỏe người cao tuổi', 'cham-soc-suc-khoe-nguoi-cao-tuoi', 'Khám bệnh, tặng quà cho 200 cụ già tại viện dưỡng lão', '<h3>Hoạt động</h3><p>Khám bệnh, tư vấn sức khỏe, tặng quà, văn nghệ...</p>', 'event11.jpg', NULL, NULL, 'elderly', 'Viện dưỡng lão Hà Nội', 'Hà Nội', 'Đống Đa', NULL, NULL, 35000000.00, 31000000.00, 8, 7, NULL, '2026-02-22', '2026-03-05', NULL, 'approved', NULL, 0, 0, 0, 145, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(12, 2, 'Trồng cây xanh - Phủ xanh Việt Nam', 'trong-cay-xanh-phu-xanh-viet-nam', 'Trồng 10,000 cây xanh tại khu vực bị phá rừng', '<h3>Mục tiêu</h3><p>Khôi phục rừng, cải thiện môi trường sống...</p>', 'event12.jpg', NULL, NULL, 'environment', 'Huyện Hoàng Su Phì', 'Hà Giang', 'Hoàng Su Phì', NULL, NULL, 70000000.00, 48000000.00, 50, 42, NULL, '2026-03-01', '2026-04-30', NULL, 'approved', NULL, 0, 1, 0, 267, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(13, 3, 'Dọn rác biển - Bảo vệ đại dương', 'don-rac-bien-bao-ve-dai-duong', 'Chiến dịch thu gom rác thải nhựa trên bãi biển', '<h3>Kế hoạch</h3><p>Tổ chức 3 đợt dọn rác tại các bãi biển Đà Nẵng...</p>', 'event13.jpg', NULL, NULL, 'environment', 'Bãi biển Mỹ Khê', 'Đà Nẵng', 'Sơn Trà', NULL, NULL, 25000000.00, 23000000.00, 100, 95, NULL, '2026-02-28', '2026-03-15', NULL, 'approved', NULL, 0, 0, 0, 312, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(14, 2, 'Trao tặng xe lăn cho người khuyết tật', 'trao-tang-xe-lan-nguoi-khuyet-tat', 'Tặng 50 chiếc xe lăn cho người khuyết tật có hoàn cảnh khó khăn', '<h3>Đối tượng</h3><p>Người khuyết tật nghèo, không có phương tiện di chuyển...</p>', 'event14.jpg', NULL, NULL, 'other', 'Trung tâm Phục hồi chức năng', 'TP.HCM', 'Quận 1', NULL, NULL, 120000000.00, 95000000.00, 5, 4, NULL, '2026-02-12', '2026-03-25', NULL, 'approved', NULL, 0, 0, 0, 198, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(15, 3, 'Mổ mắt miễn phí cho người nghèo', 'mo-mat-mien-phi-nguoi-ngheo', 'Phẫu thuật đục thủy tinh thể miễn phí cho 100 bệnh nhân', '<h3>Chương trình</h3><p>Phẫu thuật đục thủy tinh thể, tặng kính mắt...</p>', 'event15.jpg', NULL, NULL, 'medical', 'Bệnh viện Mắt Trung ương', 'Hà Nội', 'Ba Đình', NULL, NULL, 90000000.00, 72000000.00, 8, 6, NULL, '2026-02-05', '2026-03-18', NULL, 'approved', NULL, 0, 0, 0, 234, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28');

-- --------------------------------------------------------

--
-- Table structure for table `event_updates`
--

CREATE TABLE `event_updates` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `images` text DEFAULT NULL,
  `update_type` enum('progress','milestone','completion','urgent') DEFAULT 'progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_volunteers`
--

CREATE TABLE `event_volunteers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `availability` text DEFAULT NULL,
  `experience` text DEFAULT NULL,
  `motivation` text NOT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `emergency_contact_relation` varchar(50) DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed','cancelled') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `checked_out_at` timestamp NULL DEFAULT NULL,
  `attendance_confirmed` tinyint(1) DEFAULT 0,
  `volunteer_feedback` text DEFAULT NULL,
  `organizer_feedback` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_volunteers`
--

INSERT INTO `event_volunteers` (`id`, `user_id`, `event_id`, `fullname`, `email`, `phone`, `date_of_birth`, `gender`, `occupation`, `skills`, `availability`, `experience`, `motivation`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relation`, `status`, `rejection_reason`, `checked_in_at`, `checked_out_at`, `attendance_confirmed`, `volunteer_feedback`, `organizer_feedback`, `rating`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 'Phạm Minh Anh', 'user1@test.com', '0934567890', NULL, 'female', 'Nhân viên văn phòng', '[\"Kế toán\", \"Tin học văn phòng\"]', NULL, NULL, 'Muốn giúp đỡ trẻ em có hoàn cảnh khó khăn', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-15 15:17:28', '2026-02-25 15:17:28'),
(2, 6, 1, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', NULL, 'female', 'Giáo viên', '[\"Giảng dạy\", \"Tổ chức sự kiện\"]', NULL, NULL, 'Yêu trẻ em, muốn lan tỏa yêu thương', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-16 15:17:28', '2026-02-25 15:17:28'),
(3, 7, 1, 'Đặng Quốc Huy', 'user3@test.com', '0956789012', NULL, 'male', 'Kỹ sư', '[\"Xây dựng\", \"Sửa chữa\"]', NULL, NULL, 'Có kinh nghiệm làm tình nguyện', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-17 15:17:28', '2026-02-25 15:17:28'),
(4, 5, 2, 'Phạm Minh Anh', 'user1@test.com', '0934567890', NULL, 'female', 'Nhân viên văn phòng', '[\"Kế toán\", \"Tổ chức\"]', NULL, NULL, 'Muốn giúp gia đình khó khăn có nhà ở', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-13 15:17:28', '2026-02-25 15:17:28'),
(5, 6, 2, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', NULL, 'female', 'Giáo viên', '[\"Nấu ăn\", \"Chăm sóc trẻ\"]', NULL, NULL, 'Có thời gian rảnh, muốn giúp đỡ', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-14 15:17:28', '2026-02-25 15:17:28'),
(6, 7, 3, 'Đặng Quốc Huy', 'user3@test.com', '0956789012', NULL, 'male', 'Kỹ sư', '[\"Y tế cơ bản\"]', NULL, NULL, 'Muốn đến vùng cao giúp bà con', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-15 15:17:28', '2026-02-25 15:17:28'),
(7, 5, 8, 'Phạm Minh Anh', 'user1@test.com', '0934567890', NULL, 'female', 'Nhân viên văn phòng', '[\"Văn nghệ\", \"Tổ chức\"]', NULL, NULL, 'Yêu trẻ con, muốn mang niềm vui đến các em', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-20 15:17:28', '2026-02-25 15:17:28'),
(8, 6, 8, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', NULL, 'female', 'Giáo viên', '[\"Múa\", \"Hát\"]', NULL, NULL, 'Muốn tổ chức chương trình vui cho trẻ', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-21 15:17:28', '2026-02-25 15:17:28');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `category` enum('news','activity','story','announcement','media') DEFAULT 'news',
  `is_featured` tinyint(1) DEFAULT 0,
  `is_breaking` tinyint(1) DEFAULT 0,
  `priority` int(11) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `thumbnail`, `gallery`, `category`, `is_featured`, `is_breaking`, `priority`, `views`, `meta_description`, `meta_keywords`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ra mắt nền tảng Charity Event - Kết nối yêu thương', 'ra-mat-nen-tang-charity-event', 'Charity Event chính thức ra mắt với sứ mệnh kết nối những tấm lòng nhân ái, mang đến sự minh bạch và hiệu quả cho hoạt động từ thiện.', '<p>Ngày 01/01/2026, nền tảng <strong>Charity Event</strong> chính thức ra mắt cộng đồng...</p>', 'news-1.jpg', NULL, 'announcement', 1, 0, 0, 0, NULL, NULL, 'published', '2026-02-25 15:17:06', '2026-02-25 15:17:06', '2026-02-25 15:17:06'),
(2, 1, 'Hướng dẫn quyên góp trên Charity Event', 'huong-dan-quyen-gop', 'Quy trình quyên góp đơn giản, nhanh chóng và an toàn trên nền tảng Charity Event.', '<p>Bài viết hướng dẫn chi tiết cách quyên góp...</p>', 'news-2.jpg', NULL, 'news', 0, 0, 0, 0, NULL, NULL, 'published', '2026-02-25 15:17:06', '2026-02-25 15:17:06', '2026-02-25 15:17:06');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('donation','volunteer','event','system','admin') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partners`
--

CREATE TABLE `partners` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `action_key` varchar(255) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `first_attempt_at` int(11) NOT NULL,
  `last_attempt_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statistics`
--

CREATE TABLE `statistics` (
  `id` int(11) NOT NULL,
  `total_events` int(11) DEFAULT 0,
  `total_donations` decimal(15,2) DEFAULT 0.00,
  `total_donors` int(11) DEFAULT 0,
  `total_volunteers` int(11) DEFAULT 0,
  `total_benefactors` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statistics`
--

INSERT INTO `statistics` (`id`, `total_events`, `total_donations`, `total_donors`, `total_volunteers`, `total_benefactors`, `updated_at`) VALUES
(1, 14, 141000000.00, 4, 8, 3, '2026-02-25 15:17:28');

-- --------------------------------------------------------

--
-- Table structure for table `terms_conditions`
--

CREATE TABLE `terms_conditions` (
  `id` int(11) NOT NULL,
  `type` enum('donation','volunteer','benefactor','event_creation') NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `version` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terms_conditions`
--

INSERT INTO `terms_conditions` (`id`, `type`, `title`, `content`, `version`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'donation', 'Điều khoản quyên góp', '<h3>ĐIỀU KHOẢN QUYÊN GÓP</h3>\r\n<p>Khi thực hiện quyên góp trên nền tảng Charity Event, bạn đồng ý với các điều khoản sau:</p>\r\n<ol>\r\n<li>Số tiền quyên góp không thể hoàn lại sau khi đã xác nhận.</li>\r\n<li>Toàn bộ số tiền sẽ được chuyển đến người tổ chức sự kiện.</li>\r\n<li>Bạn có quyền yêu cầu báo cáo minh bạch sau khi sự kiện kết thúc.</li>\r\n<li>Thông tin cá nhân của bạn sẽ được bảo mật theo chính sách.</li>\r\n<li>Charity Event không chịu trách nhiệm về việc sử dụng tiền của người tổ chức.</li>\r\n</ol>', '1.0', 1, '2026-02-25 15:17:06', '2026-02-25 15:17:06'),
(2, 'volunteer', 'Điều khoản tình nguyện viên', '<h3>ĐIỀU KHOẢN TÌNH NGUYỆN VIÊN</h3>\r\n<p>Khi đăng ký làm tình nguyện viên, bạn cam kết:</p>\r\n<ol>\r\n<li>Tham gia đầy đủ theo lịch trình đã đăng ký.</li>\r\n<li>Tuân thủ quy định của người tổ chức sự kiện.</li>\r\n<li>Chịu trách nhiệm về an toàn bản thân.</li>\r\n<li>Không được sử dụng hoạt động tình nguyện cho mục đích thương mại.</li>\r\n<li>Có thể bị hủy đăng ký nếu vi phạm quy định.</li>\r\n</ol>', '1.0', 1, '2026-02-25 15:17:06', '2026-02-25 15:17:06'),
(3, 'benefactor', 'Điều khoản nhà hảo tâm', '<h3>ĐIỀU KHOẢN NHÀ HẢO TÂM</h3>\r\n<p>Khi đăng ký trở thành nhà hảo tâm, bạn cam kết:</p>\r\n<ol>\r\n<li>Cung cấp thông tin chính xác và trung thực.</li>\r\n<li>Tuân thủ pháp luật Việt Nam về hoạt động từ thiện.</li>\r\n<li>Chịu trách nhiệm về tính minh bạch của các sự kiện.</li>\r\n<li>Xuất báo cáo chi tiết sau khi sự kiện kết thúc.</li>\r\n<li>Có thể bị thu hồi quyền nếu vi phạm nghiêm trọng.</li>\r\n</ol>', '1.0', 1, '2026-02-25 15:17:06', '2026-02-25 15:17:06'),
(4, 'event_creation', 'Điều khoản tạo sự kiện', '<h3>ĐIỀU KHOẢN TẠO SỰ KIỆN</h3>\r\n<p>Khi tạo sự kiện từ thiện, bạn đồng ý:</p>\r\n<ol>\r\n<li>Sự kiện phải có mục đích từ thiện rõ ràng và hợp pháp.</li>\r\n<li>Thông tin sự kiện phải chính xác và đầy đủ.</li>\r\n<li>Phải cập nhật tiến độ định kỳ cho người quyên góp.</li>\r\n<li>Xuất báo cáo minh bạch trong vòng 30 ngày sau khi kết thúc.</li>\r\n<li>Chịu trách nhiệm trước pháp luật về việc sử dụng tiền quyên góp.</li>\r\n<li>Tuân thủ hướng dẫn và quy định của Charity Event.</li>\r\n</ol>', '1.0', 1, '2026-02-25 15:17:06', '2026-02-25 15:17:06');

-- --------------------------------------------------------

--
-- Table structure for table `transparency_reports`
--

CREATE TABLE `transparency_reports` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `total_income` decimal(15,2) NOT NULL,
  `total_expense` decimal(15,2) NOT NULL,
  `remaining_amount` decimal(15,2) NOT NULL,
  `expense_breakdown` text DEFAULT NULL,
  `income_sources` text DEFAULT NULL,
  `beneficiary_count` int(11) DEFAULT 0,
  `beneficiary_list` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `documents` text DEFAULT NULL,
  `excel_file` varchar(255) DEFAULT NULL,
  `summary` text NOT NULL,
  `impact_description` text DEFAULT NULL,
  `lessons_learned` text DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default-avatar.png',
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `role` enum('user','benefactor','admin') DEFAULT 'user',
  `benefactor_status` enum('none','pending','approved','rejected') DEFAULT 'none',
  `benefactor_verified_at` timestamp NULL DEFAULT NULL,
  `benefactor_verified_by` int(11) DEFAULT NULL,
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `phone`, `avatar`, `address`, `city`, `district`, `role`, `benefactor_status`, `benefactor_verified_at`, `benefactor_verified_by`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@charityevent.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'default-avatar.png', NULL, NULL, NULL, 'admin', 'none', NULL, NULL, 'active', NULL, '2026-02-25 15:17:06', '2026-02-25 15:17:06'),
(2, 'Nguyễn Văn Hảo', 'benefactor1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 'default-avatar.png', NULL, NULL, NULL, 'benefactor', 'approved', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(3, 'Trần Thị Tâm', 'benefactor2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'default-avatar.png', NULL, NULL, NULL, 'benefactor', 'approved', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(4, 'Lê Văn Nghĩa', 'benefactor3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0923456789', 'default-avatar.png', NULL, NULL, NULL, 'benefactor', 'approved', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(5, 'Phạm Minh Anh', 'user1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0934567890', 'default-avatar.png', NULL, NULL, NULL, 'user', 'none', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(6, 'Hoàng Thị Lan', 'user2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0945678901', 'default-avatar.png', NULL, NULL, NULL, 'user', 'none', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(7, 'Đặng Quốc Huy', 'user3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0956789012', 'default-avatar.png', NULL, NULL, NULL, 'user', 'none', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28'),
(8, 'Nguyễn Văn Trọng', 'tronglen10123456@gmail.com', '$2y$10$JMeV4qlXRsAjk81FR2BLCe99KjcWpKZY19W/v8SyJR90BT6kJMp5O', '0987846512', 'default-avatar.png', NULL, NULL, NULL, 'user', 'none', NULL, NULL, 'active', '2026-02-25 15:34:13', '2026-02-25 15:21:24', '2026-02-25 15:34:13'),
(9, 'trong', 'test1@gmail.com', '$2y$10$pmr1GfXsarynIjPJicV3g.gjEY.IbscPYLtbZhaI0hXbSvOP51hNu', '0987846512', 'default-avatar.png', NULL, NULL, NULL, 'user', 'none', NULL, NULL, 'active', '2026-02-25 15:34:27', '2026-02-25 15:34:05', '2026-02-25 15:34:27');

-- --------------------------------------------------------

--
-- Table structure for table `user_terms_acceptance`
--

CREATE TABLE `user_terms_acceptance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `terms_id` int(11) NOT NULL,
  `action_type` enum('donation','volunteer','benefactor_apply','event_create') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `accepted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `benefactor_applications`
--
ALTER TABLE `benefactor_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_end_date` (`end_date`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `event_updates`
--
ALTER TABLE `event_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_event_id` (`event_id`);

--
-- Indexes for table `event_volunteers`
--
ALTER TABLE `event_volunteers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `action_key` (`action_key`),
  ADD KEY `idx_action_key` (`action_key`);

--
-- Indexes for table `statistics`
--
ALTER TABLE `statistics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `terms_conditions`
--
ALTER TABLE `terms_conditions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `transparency_reports`
--
ALTER TABLE `transparency_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_id` (`event_id`),
  ADD KEY `idx_event_id` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_benefactor_status` (`benefactor_status`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `user_terms_acceptance`
--
ALTER TABLE `user_terms_acceptance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `terms_id` (`terms_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `benefactor_applications`
--
ALTER TABLE `benefactor_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `event_updates`
--
ALTER TABLE `event_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_volunteers`
--
ALTER TABLE `event_volunteers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `statistics`
--
ALTER TABLE `statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `terms_conditions`
--
ALTER TABLE `terms_conditions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transparency_reports`
--
ALTER TABLE `transparency_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_terms_acceptance`
--
ALTER TABLE `user_terms_acceptance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `benefactor_applications`
--
ALTER TABLE `benefactor_applications`
  ADD CONSTRAINT `benefactor_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_updates`
--
ALTER TABLE `event_updates`
  ADD CONSTRAINT `event_updates_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_updates_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_volunteers`
--
ALTER TABLE `event_volunteers`
  ADD CONSTRAINT `event_volunteers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `event_volunteers_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transparency_reports`
--
ALTER TABLE `transparency_reports`
  ADD CONSTRAINT `transparency_reports_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_terms_acceptance`
--
ALTER TABLE `user_terms_acceptance`
  ADD CONSTRAINT `user_terms_acceptance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_terms_acceptance_ibfk_2` FOREIGN KEY (`terms_id`) REFERENCES `terms_conditions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
