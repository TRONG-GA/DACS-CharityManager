-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3309
<<<<<<< HEAD
-- Generation Time: Feb 25, 2026 at 04:34 PM
=======
-- Generation Time: Mar 09, 2026 at 08:01 AM
>>>>>>> 31c9649 (update local changes)
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

<<<<<<< HEAD
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

=======
DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `update_statistics_cache` ()   BEGIN
    -- Update main statistics table
    UPDATE statistics SET
        total_events = (SELECT COUNT(*) FROM events WHERE status = 'approved'),
        total_donations = (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'completed'),
        total_donors = (SELECT COUNT(DISTINCT user_id) FROM donations WHERE status = 'completed'),
        total_volunteers = (SELECT COUNT(*) FROM event_volunteers WHERE status = 'approved'),
        total_benefactors = (SELECT COUNT(*) FROM users WHERE benefactor_status = 'approved'),
        updated_at = NOW()
    WHERE id = 1;
    
    -- Cache for quick dashboard loading
    INSERT INTO statistics_cache (cache_key, cache_data, expires_at)
    VALUES (
        'dashboard_stats',
        (SELECT JSON_OBJECT(
            'total_events', total_events,
            'total_donations', total_donations,
            'total_donors', total_donors,
            'total_volunteers', total_volunteers,
            'total_benefactors', total_benefactors
        ) FROM statistics WHERE id = 1),
        DATE_ADD(NOW(), INTERVAL 1 HOUR)
    )
    ON DUPLICATE KEY UPDATE
        cache_data = VALUES(cache_data),
        expires_at = VALUES(expires_at),
        updated_at = NOW();
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `admin_dashboard_stats`
-- (See below for the actual view)
--
CREATE TABLE `admin_dashboard_stats` (
`total_users` bigint(21)
,`total_benefactors` bigint(21)
,`total_events` bigint(21)
,`pending_events` bigint(21)
,`total_donations` decimal(37,2)
,`total_donors` bigint(21)
,`pending_donations` bigint(21)
,`pending_benefactors` bigint(21)
,`pending_volunteers` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action_type` enum('login','logout','user_edit','user_ban','user_unban','user_delete','benefactor_approve','benefactor_reject','event_approve','event_reject','event_close','event_delete','donation_verify','donation_refund','volunteer_approve','volunteer_reject','news_create','news_edit','news_delete','settings_update','backup_create') NOT NULL,
  `action_description` text DEFAULT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `notification_type` enum('pending_benefactor','pending_event','pending_donation','pending_volunteer','user_report','system_alert','low_storage','high_traffic') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `related_type` varchar(50) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_by` int(11) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `auto_dismiss_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `can_manage_users` tinyint(1) DEFAULT 1,
  `can_manage_benefactors` tinyint(1) DEFAULT 1,
  `can_manage_events` tinyint(1) DEFAULT 1,
  `can_manage_donations` tinyint(1) DEFAULT 1,
  `can_manage_volunteers` tinyint(1) DEFAULT 1,
  `can_manage_news` tinyint(1) DEFAULT 1,
  `can_manage_reports` tinyint(1) DEFAULT 1,
  `can_manage_settings` tinyint(1) DEFAULT 1,
  `can_delete_users` tinyint(1) DEFAULT 0,
  `can_delete_events` tinyint(1) DEFAULT 0,
  `can_refund_donations` tinyint(1) DEFAULT 0,
  `can_backup_database` tinyint(1) DEFAULT 0,
  `can_manage_admins` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_permissions`
--

INSERT INTO `admin_permissions` (`id`, `admin_id`, `can_manage_users`, `can_manage_benefactors`, `can_manage_events`, `can_manage_donations`, `can_manage_volunteers`, `can_manage_news`, `can_manage_reports`, `can_manage_settings`, `can_delete_users`, `can_delete_events`, `can_refund_donations`, `can_backup_database`, `can_manage_admins`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '2026-03-01 04:58:47', '2026-03-01 04:58:47');

-- --------------------------------------------------------

--
-- Stand-in structure for view `admin_recent_activities`
-- (See below for the actual view)
--
CREATE TABLE `admin_recent_activities` (
`activity_type` varchar(8)
,`activity_id` int(11)
,`actor_name` varchar(100)
,`target_name` varchar(255)
,`value` decimal(15,2)
,`activity_time` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `backup_history`
--

CREATE TABLE `backup_history` (
  `id` int(11) NOT NULL,
  `backup_type` enum('full','partial','tables_only') NOT NULL,
  `backup_file` varchar(255) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `tables_included` text DEFAULT NULL,
  `compression_type` varchar(20) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banned_ips`
--

CREATE TABLE `banned_ips` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `reason` text NOT NULL,
  `banned_by` int(11) NOT NULL,
  `ban_type` enum('temporary','permanent') DEFAULT 'temporary',
  `expires_at` timestamp NULL DEFAULT NULL,
  `attempt_count` int(11) DEFAULT 0,
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Triggers `benefactor_applications`
--
DELIMITER $$
CREATE TRIGGER `after_benefactor_approved` AFTER UPDATE ON `benefactor_applications` FOR EACH ROW BEGIN
    IF NEW.status = 'approved' AND OLD.status != 'approved' THEN
        INSERT INTO admin_logs (
            admin_id, action_type, action_description, 
            target_type, target_id, ip_address
        ) VALUES (
            NEW.reviewed_by, 
            'benefactor_approve',
            CONCAT('Approved benefactor application for user ID ', NEW.user_id),
            'benefactor_application',
            NEW.id,
            NULL
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `charity_registrations`
--

CREATE TABLE `charity_registrations` (
  `id` int(11) NOT NULL,
  `account_type` varchar(20) DEFAULT 'personal',
  `fullname` varchar(255) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `social_link` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `club_name` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `organization` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `achievements_path` text DEFAULT NULL,
  `purposes` text DEFAULT NULL,
  `commitment` varchar(50) DEFAULT NULL,
  `target_name` varchar(255) DEFAULT NULL,
  `target_amount` varchar(50) DEFAULT NULL,
  `start_date` varchar(20) DEFAULT NULL,
  `end_date` varchar(20) DEFAULT NULL,
  `platforms` text DEFAULT NULL,
  `laws` text DEFAULT NULL,
  `channel` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `website` varchar(255) DEFAULT NULL,
  `main_field` varchar(255) DEFAULT NULL,
  `rep_name` varchar(100) DEFAULT NULL,
  `rep_phone` varchar(20) DEFAULT NULL,
  `rep_email` varchar(100) DEFAULT NULL,
  `founding_date` varchar(50) DEFAULT NULL,
  `org_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `charity_registrations`
--

INSERT INTO `charity_registrations` (`id`, `account_type`, `fullname`, `dob`, `phone`, `email`, `social_link`, `address`, `role`, `club_name`, `logo_path`, `organization`, `description`, `achievements_path`, `purposes`, `commitment`, `target_name`, `target_amount`, `start_date`, `end_date`, `platforms`, `laws`, `channel`, `created_at`, `website`, `main_field`, `rep_name`, `rep_phone`, `rep_email`, `founding_date`, `org_name`) VALUES
(1, 'personal', 'Khương Kim', '5/12/2004', '00000000', 'kimkhuong10a2@gmail.com', 'facebook.com', 'nhà', 'Sáng lập', 'nhóm từ thiện', '/DACS-CharityManager-main/public/uploads/1771928406_logo_Thiết kế chưa có tên (28).png', 'Xã hội', 'ádfasd.com', '[\"\\/DACS-CharityManager-main\\/public\\/uploads\\/1771928406_ach_0_Thiết kế chưa có tên (29).png\"]', '[\"voluntary\"]', 'agree', 'nuôi em', '100000000000', '2026-02-24', '2026-02-27', '[\"momo\"]', '[\"nd93_2019\"]', 'media', '2026-02-24 10:20:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'personal', 'Khương Kim', '5/12/2004', '0869468600', 'kimkhuong10a2@gmail.com', 'facebook.com', 'nhà', 'Sáng lập', 'nhóm từ thiện', '/DACS-CharityManager-main/public/uploads/1772185759_logo_ChatGPT Image 16_58_10 21 thg 2, 2026.png', 'Chính trị xã hội', 'https://www.facebook.com/', '[\"\\/DACS-CharityManager-main\\/public\\/uploads\\/1772185759_ach_0_Thiết kế chưa có tên (29).png\"]', '[\"voluntary\"]', 'agree', 'nuôi em', '100000000000', '2026-02-27', '2026-02-28', '[\"kickstarter\"]', '[\"nd45_2010\"]', 'media', '2026-02-27 09:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'personal', 'trọng ', '5/12/2004', '00000000', 'kimkhuong10a2@gmail.com', 'facebook.com', 'nhà', 'Sáng lập', 'nhóm từ thiện', '/DACS-CharityManager-main/public/uploads/1772265703_logo_Thiết kế chưa có tên (28).png', 'Chính trị xã hội', 'https://www.facebook.com/', '[\"\\/DACS-CharityManager-main\\/public\\/uploads\\/1772265703_ach_0_Thiết kế chưa có tên (29).png\"]', '[\"voluntary\"]', 'agree', 'nuôi em', '100000000000', '2026-02-28', '2026-03-08', '[\"kickstarter\"]', '[\"nd93_2021\"]', 'media', '2026-02-28 08:01:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'organization', NULL, NULL, NULL, NULL, NULL, 'phố hà mã', NULL, NULL, '', NULL, NULL, '[\"\\/DACS-CharityManager-main\\/public\\/uploads\\/1772265990_ach_0_Thiết kế chưa có tên (27).png\"]', '[\"voluntary\"]', 'agree', 'Nuôi Anh', '100000000000', '2026-02-28', '2026-03-08', '[\"momo\"]', '[\"nd45_2010\"]', 'media', '2026-02-28 08:06:30', 'ádasdasd.com', 'giáo dục', 'Trọng', '0000000000', 'kimkhuong10a2@gmail.com', '2/1/2026', NULL),
(7, 'organization', NULL, NULL, NULL, NULL, NULL, 'nhà', NULL, NULL, '', NULL, NULL, '[\"\\/DACS-CharityManager-main\\/public\\/uploads\\/1772266028_ach_0_Thiết kế chưa có tên (26).png\"]', '[\"voluntary\"]', 'agree', 'nuôi em', '500000000000', '2026-02-28', '2026-04-08', '[\"momo\"]', '[\"nd93_2021\"]', 'media', '2026-02-28 08:07:08', 'https://www.facebook.com/', 'giáo dục', 'Trọng', '0000000000', 'kimkhuong10a2@gmail.com', '2/1/2026', NULL),
(13, 'personal', 'trong', '19/06/2005', '0974972206', 'test1@gmail.com', 'Trọng Gà', '1', 'Sáng lập', 'Gà', '/DACS-CharityManager-main/public/uploads/1772433687_logo_hero-bg.png', 'Chính trị xã hội', 'trong.com', '[\"\\/DACS-CharityManager-main\\/public\\/uploads\\/1772433687_ach_0_hero-bg.png\"]', '[\"voluntary\"]', 'agree', 'jasdajk', '10000000', '2026-03-02', '2026-03-18', '[\"momo\"]', '[\"nd93_2021\"]', 'media', '2026-03-02 06:41:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
-- Table structure for table `dashboard_widgets`
--

CREATE TABLE `dashboard_widgets` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `widget_type` varchar(50) NOT NULL,
  `widget_config` text DEFAULT NULL,
  `position_order` int(11) DEFAULT 0,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
(18, 5, 11, 'Phạm Minh Anh', NULL, NULL, 3000000.00, NULL, 'bank_transfer', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-24 15:17:28', '2026-02-25 15:17:28'),
(19, 9, 1, 'trong', 'test1@gmail.com', NULL, 100000.00, '', 'bank_transfer', NULL, NULL, 'completed', 0, 1, NULL, NULL, '2026-02-26 14:24:33', '2026-02-26 14:24:33'),
(20, 9, 1, 'ẩn danh', 'test1@gmail.com', NULL, 200000.00, 'hi bé', 'momo', NULL, NULL, 'completed', 1, 1, NULL, NULL, '2026-02-26 14:25:15', '2026-02-26 14:25:15'),
(21, 9, 3, 'trong', 'test1@gmail.com', '0987456115', 200000.00, 'tuyệt', 'vnpay', NULL, NULL, 'completed', 1, 1, NULL, NULL, '2026-02-26 19:17:28', '2026-02-26 19:17:28'),
(22, NULL, 1, 'trong', 'test1@gmail.com', '0987456115', 100000.00, 'cos len', 'momo', NULL, NULL, 'completed', 1, 1, NULL, NULL, '2026-03-01 04:27:16', '2026-03-01 04:27:16');

--
-- Triggers `donations`
--
DELIMITER $$
CREATE TRIGGER `after_donation_completed` AFTER UPDATE ON `donations` FOR EACH ROW BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        -- Update event current_amount
        UPDATE events 
        SET current_amount = current_amount + NEW.amount
        WHERE id = NEW.event_id;
        
        -- Call update statistics
        CALL update_statistics_cache();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `template_key` varchar(100) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body_html` longtext NOT NULL,
  `body_text` text DEFAULT NULL,
  `available_variables` text DEFAULT NULL,
  `category` enum('user','benefactor','event','donation','volunteer','system') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `template_key`, `template_name`, `subject`, `body_html`, `body_text`, `available_variables`, `category`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'benefactor_approved', 'Duyệt nhà hảo tâm', 'Đơn đăng ký nhà hảo tâm được duyệt', '<h2>Chúc mừng {{fullname}}!</h2>\r\n<p>Đơn đăng ký nhà hảo tâm của bạn đã được phê duyệt.</p>\r\n<p>Bạn có thể bắt đầu tạo sự kiện từ thiện ngay bây giờ.</p>\r\n<p><a href=\"{{dashboard_url}}\">Vào Dashboard</a></p>', 'Chúc mừng {{fullname}}! Đơn của bạn đã được duyệt.', '[\"fullname\", \"email\", \"dashboard_url\"]', 'benefactor', 1, '2026-03-01 04:58:47', '2026-03-01 04:58:47'),
(2, 'benefactor_rejected', 'Từ chối nhà hảo tâm', 'Đơn đăng ký nhà hảo tâm chưa được duyệt', '<h2>Xin chào {{fullname}},</h2>\r\n<p>Rất tiếc, đơn đăng ký nhà hảo tâm của bạn chưa được duyệt.</p>\r\n<p>Lý do: {{reason}}</p>\r\n<p>Bạn có thể nộp đơn lại sau khi hoàn thiện hồ sơ.</p>', 'Đơn của bạn chưa được duyệt. Lý do: {{reason}}', '[\"fullname\", \"email\", \"reason\"]', 'benefactor', 1, '2026-03-01 04:58:47', '2026-03-01 04:58:47'),
(3, 'event_approved', 'Duyệt sự kiện', 'Sự kiện \"{{event_title}}\" được duyệt', '<h2>Sự kiện của bạn đã được duyệt!</h2>\r\n<p>Sự kiện \"{{event_title}}\" đã được phê duyệt và hiển thị công khai.</p>\r\n<p>Mọi người giờ có thể quyên góp cho sự kiện của bạn.</p>\r\n<p><a href=\"{{event_url}}\">Xem sự kiện</a></p>', 'Sự kiện {{event_title}} đã được duyệt!', '[\"fullname\", \"event_title\", \"event_url\"]', 'event', 1, '2026-03-01 04:58:47', '2026-03-01 04:58:47'),
(4, 'event_rejected', 'Từ chối sự kiện', 'Sự kiện \"{{event_title}}\" chưa được duyệt', '<h2>Sự kiện chưa được duyệt</h2>\r\n<p>Sự kiện \"{{event_title}}\" chưa được phê duyệt.</p>\r\n<p>Lý do: {{reason}}</p>\r\n<p>Vui lòng chỉnh sửa và gửi lại.</p>', 'Sự kiện chưa được duyệt. Lý do: {{reason}}', '[\"fullname\", \"event_title\", \"reason\"]', 'event', 1, '2026-03-01 04:58:47', '2026-03-01 04:58:47');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `title`, `slug`, `description`, `content`, `thumbnail`, `gallery`, `video_url`, `category`, `location`, `province`, `district`, `ward`, `specific_address`, `target_amount`, `current_amount`, `volunteer_needed`, `volunteer_registered`, `volunteer_skills`, `start_date`, `end_date`, `registration_deadline`, `status`, `rejection_reason`, `priority`, `is_featured`, `is_urgent`, `views`, `has_report`, `report_uploaded_at`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `admin_notes`) VALUES
(1, 2, 'Hỗ trợ phẫu thuật tim cho bé Minh An', 'ho-tro-phau-thuat-tim-be-minh-an', 'Bé Minh An 5 tuổi bị tim bẩm sinh, cần phẫu thuật gấp nhưng gia đình không đủ chi phí', '<h3>Hoàn cảnh</h3><p>Bé Minh An sinh ra đã mắc bệnh tim bẩm sinh. Gia đình nghèo, bố mẹ làm công nhân, không đủ tiền phẫu thuật...</p><h3>Chi phí cần thiết</h3><ul><li>Phẫu thuật: 80 triệu</li><li>Điều trị sau phẫu thuật: 20 triệu</li></ul>', 'event1.jpg', NULL, NULL, 'medical', 'Bệnh viện Tim Hà Nội', 'Hà Nội', 'Đống Đa', NULL, NULL, 100000000.00, 65400000.00, 5, 3, NULL, '2026-02-01', '2026-03-15', NULL, 'approved', NULL, 0, 1, 1, 341, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-03-02 04:01:34', NULL),
(2, 2, 'Xây nhà tình thương cho gia đình chị Hồng', 'xay-nha-tinh-thuong-gia-dinh-chi-hong', 'Chị Hồng nuôi 3 con nhỏ, sống trong căn nhà dột nát cần được xây mới', '<h3>Hoàn cảnh</h3><p>Chị Hồng góa chồng, một mình nuôi 3 con nhỏ. Căn nhà cũ đã xuống cấp nghiêm trọng...</p>', 'event2.jpg', NULL, NULL, 'community', 'Xã Hòa Bình, Huyện Mỹ Đức', 'Hà Nội', 'Mỹ Đức', NULL, NULL, 50000000.00, 32000000.00, 15, 12, NULL, '2026-02-10', '2026-04-30', NULL, 'approved', NULL, 0, 1, 0, 223, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-03-02 04:15:22', NULL),
(3, 3, 'Khám chữa bệnh miễn phí cho người nghèo', 'kham-chua-benh-mien-phi-nguoi-ngheo', 'Chương trình khám bệnh, phát thuốc miễn phí cho 500 người dân nghèo vùng cao', '<h3>Nội dung chương trình</h3><p>Đoàn y tế sẽ lên vùng cao khám bệnh, phát thuốc miễn phí cho bà con...</p>', 'event3.jpg', NULL, NULL, 'medical', 'Huyện Mường Lát', 'Thanh Hóa', 'Mường Lát', NULL, NULL, 30000000.00, 28200000.00, 20, 18, NULL, '2026-02-15', '2026-02-17', NULL, 'approved', NULL, 0, 0, 0, 193, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-26 19:27:32', NULL),
(4, 2, 'Trao học bổng cho học sinh nghèo vượt khó', 'trao-hoc-bong-hoc-sinh-ngheo-vuot-kho', 'Trao 100 suất học bổng cho học sinh có hoàn cảnh khó khăn nhưng học giỏi', '<h3>Mục tiêu</h3><p>Hỗ trợ 100 học sinh nghèo vượt khó với mỗi suất 2 triệu đồng/năm...</p>', 'event4.jpg', NULL, NULL, 'education', 'Trường THCS Lý Tự Trọng', 'Hà Nội', 'Hoàng Mai', NULL, NULL, 200000000.00, 145000000.00, 10, 8, NULL, '2026-02-20', '2026-03-31', NULL, 'approved', NULL, 0, 1, 0, 421, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-28 08:07:33', NULL),
(5, 3, 'Xây dựng thư viện cho trường học vùng cao', 'xay-dung-thu-vien-truong-hoc-vung-cao', 'Xây dựng thư viện với 2000 đầu sách cho học sinh vùng cao', '<h3>Dự án</h3><p>Xây phòng thư viện 50m2, trang bị 2000 đầu sách phù hợp...</p>', 'event5.jpg', NULL, NULL, 'education', 'Trường Tiểu học Tà Lèng', 'Lai Châu', 'Mường Tè', NULL, NULL, 80000000.00, 54000000.00, 8, 5, NULL, '2026-02-25', '2026-05-30', NULL, 'approved', NULL, 0, 0, 0, 167, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', NULL),
(6, 2, 'Cứu trợ đồng bào lũ lụt miền Trung', 'cuu-tro-dong-bao-lu-lut-mien-trung', 'Hỗ trợ lương thực, nhu yếu phẩm cho 1000 hộ dân bị lũ lụt', '<h3>Tình hình</h3><p>Mưa lũ kéo dài khiến hàng nghìn gia đình mất nhà cửa, mất mùa...</p>', 'event6.jpg', NULL, NULL, 'disaster', 'Huyện Hương Khê', 'Hà Tĩnh', 'Hương Khê', NULL, NULL, 150000000.00, 127000000.00, 30, 28, NULL, '2026-01-15', '2026-02-28', NULL, 'approved', NULL, 0, 1, 1, 528, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-28 07:47:57', NULL),
(7, 3, 'Hỗ trợ người dân vùng hạn hán', 'ho-tro-nguoi-dan-vung-han-han', 'Cung cấp nước sạch và lương thực cho vùng hạn hán', '<h3>Tình trạng</h3><p>Hạn hán kéo dài, thiếu nước sinh hoạt trầm trọng...</p>', 'event7.jpg', NULL, NULL, 'disaster', 'Huyện Krông Pắc', 'Đắk Lắk', 'Krông Pắc', NULL, NULL, 100000000.00, 89000000.00, 25, 22, NULL, '2026-02-01', '2026-03-20', NULL, 'approved', NULL, 0, 0, 1, 298, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', NULL),
(8, 2, 'Tết ấm cho trẻ em mồ côi', 'tet-am-cho-tre-em-mo-coi', 'Tổ chức Tết Trung thu cho 200 trẻ em mồ côi tại trại trẻ', '<h3>Chương trình</h3><p>Tổ chức đêm trung thu với bánh kẹo, quà tặng, văn nghệ...</p>', 'event8.jpg', NULL, NULL, 'children', 'Trại trẻ mồ côi Hà Cầu', 'Hà Nội', 'Hà Đông', NULL, NULL, 40000000.00, 38000000.00, 15, 15, NULL, '2026-02-10', '2026-02-28', NULL, 'approved', NULL, 0, 1, 0, 345, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', NULL),
(9, 3, 'Khám tim miễn phí cho trẻ em nghèo', 'kham-tim-mien-phi-tre-em-ngheo', 'Khám sàng lọc tim bẩm sinh cho 500 trẻ em vùng nghèo', '<h3>Mục đích</h3><p>Phát hiện sớm các bệnh lý tim bẩm sinh ở trẻ em...</p>', 'event9.jpg', NULL, NULL, 'children', 'Bệnh viện Nhi Trung ương', 'Hà Nội', 'Đống Đa', NULL, NULL, 60000000.00, 42000000.00, 12, 9, NULL, '2026-02-18', '2026-03-10', NULL, 'approved', NULL, 0, 0, 0, 203, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-28 08:08:00', NULL),
(10, 2, 'Tặng quà Tết cho người già neo đơn', 'tang-qua-tet-nguoi-gia-neo-don', 'Tặng 300 phần quà Tết cho người cao tuổi neo đơn', '<h3>Nội dung</h3><p>Mỗi phần quà gồm: gạo, dầu ăn, bánh kẹo, tiền mặt...</p>', 'event10.jpg', NULL, NULL, 'elderly', 'Phường Khương Trung', 'Hà Nội', 'Thanh Xuân', NULL, NULL, 45000000.00, 45000000.00, 10, 10, NULL, '2026-01-20', '2026-02-10', NULL, 'completed', NULL, 0, 0, 0, 178, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', NULL),
(11, 3, 'Chăm sóc sức khỏe người cao tuổi', 'cham-soc-suc-khoe-nguoi-cao-tuoi', 'Khám bệnh, tặng quà cho 200 cụ già tại viện dưỡng lão', '<h3>Hoạt động</h3><p>Khám bệnh, tư vấn sức khỏe, tặng quà, văn nghệ...</p>', 'event11.jpg', NULL, NULL, 'elderly', 'Viện dưỡng lão Hà Nội', 'Hà Nội', 'Đống Đa', NULL, NULL, 35000000.00, 31000000.00, 8, 7, NULL, '2026-02-22', '2026-03-05', NULL, 'approved', NULL, 0, 0, 0, 145, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', NULL),
(12, 2, 'Trồng cây xanh - Phủ xanh Việt Nam', 'trong-cay-xanh-phu-xanh-viet-nam', 'Trồng 10,000 cây xanh tại khu vực bị phá rừng', '<h3>Mục tiêu</h3><p>Khôi phục rừng, cải thiện môi trường sống...</p>', 'event12.jpg', NULL, NULL, 'environment', 'Huyện Hoàng Su Phì', 'Hà Giang', 'Hoàng Su Phì', NULL, NULL, 70000000.00, 48000000.00, 50, 42, NULL, '2026-03-01', '2026-04-30', NULL, 'approved', NULL, 0, 1, 0, 267, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', NULL),
(13, 3, 'Dọn rác biển - Bảo vệ đại dương', 'don-rac-bien-bao-ve-dai-duong', 'Chiến dịch thu gom rác thải nhựa trên bãi biển', '<h3>Kế hoạch</h3><p>Tổ chức 3 đợt dọn rác tại các bãi biển Đà Nẵng...</p>', 'event13.jpg', NULL, NULL, 'environment', 'Bãi biển Mỹ Khê', 'Đà Nẵng', 'Sơn Trà', NULL, NULL, 25000000.00, 23000000.00, 100, 95, NULL, '2026-02-28', '2026-03-15', NULL, 'approved', NULL, 0, 0, 0, 312, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', NULL),
(14, 2, 'Trao tặng xe lăn cho người khuyết tật', 'trao-tang-xe-lan-nguoi-khuyet-tat', 'Tặng 50 chiếc xe lăn cho người khuyết tật có hoàn cảnh khó khăn', '<h3>Đối tượng</h3><p>Người khuyết tật nghèo, không có phương tiện di chuyển...</p>', 'event14.jpg', NULL, NULL, 'other', 'Trung tâm Phục hồi chức năng', 'TP.HCM', 'Quận 1', NULL, NULL, 120000000.00, 95000000.00, 5, 4, NULL, '2026-02-12', '2026-03-25', NULL, 'approved', NULL, 0, 0, 0, 198, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', NULL),
(15, 3, 'Mổ mắt miễn phí cho người nghèo', 'mo-mat-mien-phi-nguoi-ngheo', 'Phẫu thuật đục thủy tinh thể miễn phí cho 100 bệnh nhân', '<h3>Chương trình</h3><p>Phẫu thuật đục thủy tinh thể, tặng kính mắt...</p>', 'event15.jpg', NULL, NULL, 'medical', 'Bệnh viện Mắt Trung ương', 'Hà Nội', 'Ba Đình', NULL, NULL, 90000000.00, 72000000.00, 8, 6, NULL, '2026-02-05', '2026-03-18', NULL, 'approved', NULL, 0, 0, 0, 234, 0, NULL, NULL, NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', NULL);

--
-- Triggers `events`
--
DELIMITER $$
CREATE TRIGGER `after_event_approved` AFTER UPDATE ON `events` FOR EACH ROW BEGIN
    IF NEW.status = 'approved' AND OLD.status != 'approved' THEN
        INSERT INTO admin_logs (
            admin_id, action_type, action_description,
            target_type, target_id, ip_address
        ) VALUES (
            NEW.approved_by,
            'event_approve',
            CONCAT('Approved event: ', NEW.title),
            'event',
            NEW.id,
            NULL
        );
        
        -- Create admin notification
        INSERT INTO admin_notifications (
            notification_type, title, message, priority,
            related_type, related_id, action_url
        ) VALUES (
            'system_alert',
            'Event Approved',
            CONCAT('Event "', NEW.title, '" has been approved'),
            'low',
            'event',
            NEW.id,
            CONCAT('/admin/events/event_detail.php?id=', NEW.id)
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `event_updates`
--

>>>>>>> 31c9649 (update local changes)
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
<<<<<<< HEAD
=======
  `birth_date` date DEFAULT NULL,
>>>>>>> 31c9649 (update local changes)
  `occupation` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `availability` text DEFAULT NULL,
  `experience` text DEFAULT NULL,
  `motivation` text NOT NULL,
<<<<<<< HEAD
=======
  `available_weekday` tinyint(1) DEFAULT 0,
  `available_weekend` tinyint(1) DEFAULT 0,
  `available_evening` tinyint(1) DEFAULT 0,
>>>>>>> 31c9649 (update local changes)
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

<<<<<<< HEAD
INSERT INTO `event_volunteers` (`id`, `user_id`, `event_id`, `fullname`, `email`, `phone`, `date_of_birth`, `gender`, `occupation`, `skills`, `availability`, `experience`, `motivation`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relation`, `status`, `rejection_reason`, `checked_in_at`, `checked_out_at`, `attendance_confirmed`, `volunteer_feedback`, `organizer_feedback`, `rating`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 'Phạm Minh Anh', 'user1@test.com', '0934567890', NULL, 'female', 'Nhân viên văn phòng', '[\"Kế toán\", \"Tin học văn phòng\"]', NULL, NULL, 'Muốn giúp đỡ trẻ em có hoàn cảnh khó khăn', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-15 15:17:28', '2026-02-25 15:17:28'),
(2, 6, 1, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', NULL, 'female', 'Giáo viên', '[\"Giảng dạy\", \"Tổ chức sự kiện\"]', NULL, NULL, 'Yêu trẻ em, muốn lan tỏa yêu thương', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-16 15:17:28', '2026-02-25 15:17:28'),
(3, 7, 1, 'Đặng Quốc Huy', 'user3@test.com', '0956789012', NULL, 'male', 'Kỹ sư', '[\"Xây dựng\", \"Sửa chữa\"]', NULL, NULL, 'Có kinh nghiệm làm tình nguyện', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-17 15:17:28', '2026-02-25 15:17:28'),
(4, 5, 2, 'Phạm Minh Anh', 'user1@test.com', '0934567890', NULL, 'female', 'Nhân viên văn phòng', '[\"Kế toán\", \"Tổ chức\"]', NULL, NULL, 'Muốn giúp gia đình khó khăn có nhà ở', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-13 15:17:28', '2026-02-25 15:17:28'),
(5, 6, 2, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', NULL, 'female', 'Giáo viên', '[\"Nấu ăn\", \"Chăm sóc trẻ\"]', NULL, NULL, 'Có thời gian rảnh, muốn giúp đỡ', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-14 15:17:28', '2026-02-25 15:17:28'),
(6, 7, 3, 'Đặng Quốc Huy', 'user3@test.com', '0956789012', NULL, 'male', 'Kỹ sư', '[\"Y tế cơ bản\"]', NULL, NULL, 'Muốn đến vùng cao giúp bà con', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-15 15:17:28', '2026-02-25 15:17:28'),
(7, 5, 8, 'Phạm Minh Anh', 'user1@test.com', '0934567890', NULL, 'female', 'Nhân viên văn phòng', '[\"Văn nghệ\", \"Tổ chức\"]', NULL, NULL, 'Yêu trẻ con, muốn mang niềm vui đến các em', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-20 15:17:28', '2026-02-25 15:17:28'),
(8, 6, 8, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', NULL, 'female', 'Giáo viên', '[\"Múa\", \"Hát\"]', NULL, NULL, 'Muốn tổ chức chương trình vui cho trẻ', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-21 15:17:28', '2026-02-25 15:17:28');
=======
INSERT INTO `event_volunteers` (`id`, `user_id`, `event_id`, `fullname`, `email`, `phone`, `date_of_birth`, `gender`, `birth_date`, `occupation`, `skills`, `availability`, `experience`, `motivation`, `available_weekday`, `available_weekend`, `available_evening`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relation`, `status`, `rejection_reason`, `checked_in_at`, `checked_out_at`, `attendance_confirmed`, `volunteer_feedback`, `organizer_feedback`, `rating`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 'Phạm Minh Anh', 'user1@test.com', '0934567890', NULL, 'female', NULL, 'Nhân viên văn phòng', '[\"Kế toán\", \"Tin học văn phòng\"]', NULL, NULL, 'Muốn giúp đỡ trẻ em có hoàn cảnh khó khăn', 0, 0, 0, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-15 15:17:28', '2026-02-25 15:17:28'),
(2, 6, 1, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', NULL, 'female', NULL, 'Giáo viên', '[\"Giảng dạy\", \"Tổ chức sự kiện\"]', NULL, NULL, 'Yêu trẻ em, muốn lan tỏa yêu thương', 0, 0, 0, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-16 15:17:28', '2026-02-25 15:17:28'),
(3, 7, 1, 'Đặng Quốc Huy', 'user3@test.com', '0956789012', NULL, 'male', NULL, 'Kỹ sư', '[\"Xây dựng\", \"Sửa chữa\"]', NULL, NULL, 'Có kinh nghiệm làm tình nguyện', 0, 0, 0, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-17 15:17:28', '2026-02-25 15:17:28'),
(4, 5, 2, 'Phạm Minh Anh', 'user1@test.com', '0934567890', NULL, 'female', NULL, 'Nhân viên văn phòng', '[\"Kế toán\", \"Tổ chức\"]', NULL, NULL, 'Muốn giúp gia đình khó khăn có nhà ở', 0, 0, 0, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-13 15:17:28', '2026-02-25 15:17:28'),
(5, 6, 2, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', NULL, 'female', NULL, 'Giáo viên', '[\"Nấu ăn\", \"Chăm sóc trẻ\"]', NULL, NULL, 'Có thời gian rảnh, muốn giúp đỡ', 0, 0, 0, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-14 15:17:28', '2026-02-25 15:17:28'),
(6, 7, 3, 'Đặng Quốc Huy', 'user3@test.com', '0956789012', NULL, 'male', NULL, 'Kỹ sư', '[\"Y tế cơ bản\"]', NULL, NULL, 'Muốn đến vùng cao giúp bà con', 0, 0, 0, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-15 15:17:28', '2026-02-25 15:17:28'),
(7, 5, 8, 'Phạm Minh Anh', 'user1@test.com', '0934567890', NULL, 'female', NULL, 'Nhân viên văn phòng', '[\"Văn nghệ\", \"Tổ chức\"]', NULL, NULL, 'Yêu trẻ con, muốn mang niềm vui đến các em', 0, 0, 0, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-20 15:17:28', '2026-02-25 15:17:28'),
(8, 6, 8, 'Hoàng Thị Lan', 'user2@test.com', '0945678901', NULL, 'female', NULL, 'Giáo viên', '[\"Múa\", \"Hát\"]', NULL, NULL, 'Muốn tổ chức chương trình vui cho trẻ', 0, 0, 0, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-21 15:17:28', '2026-02-25 15:17:28'),
(9, NULL, 1, 'users', 'test1@gmail.com', '0952122222', NULL, 'male', '2005-06-19', 'Sinh viên', '[\"Chăm sóc trẻ em\"]', NULL, 'cos', 'no', 0, 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-28 06:53:18', '2026-02-28 06:53:18'),
(10, NULL, 6, 'users', 'test1@gmail.com', '0952122222', NULL, 'male', '2005-06-19', 'Sinh viên', '[\"Nấu ăn\",\"Y tế cơ bản\"]', NULL, 'no', 'no', 0, 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-28 07:35:06', '2026-02-28 07:35:06'),
(11, NULL, 6, 'trong', 'tronglen10123456@gmail.com', '0974972206', NULL, 'male', '2005-06-19', 'Sinh viên', '[\"Y tế cơ bản\"]', NULL, 'no', 'no', 0, 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-28 07:38:06', '2026-02-28 07:38:06'),
(12, NULL, 6, 'Nguyễn Văn Trọng', 'trong@gmal.com', '0974544545', NULL, 'male', '2005-06-19', 'Sinh viên', '[\"Lái xe\",\"Xây dựng\"]', NULL, 'no', 'no', 0, 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-28 07:47:57', '2026-02-28 07:47:57'),
(13, NULL, 1, 'Nguyễn Văn Trọng', 'trong@gmal.com', '0974544545', NULL, 'male', '2005-06-19', 'Sinh viên', '[\"Lái xe\",\"Xây dựng\"]', NULL, 'no', 'no', 0, 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-28 07:49:35', '2026-02-28 07:49:35'),
(14, NULL, 2, 'Nguyễn Văn Trọng', 'trong@gmal.com', '0974544545', NULL, 'male', '2005-06-19', 'Sinh viên', '[\"Nấu ăn\"]', NULL, 'no', 'no', 0, 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-28 07:54:15', '2026-02-28 07:54:15'),
(15, NULL, 2, 'users', 'test1@gmail.com', '0952122222', NULL, 'male', '2015-06-09', 'Sinh viên', '[\"Y tế cơ bản\"]', NULL, 'no', 'no', 0, 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-28 07:58:00', '2026-02-28 07:58:00'),
(16, NULL, 4, 'users', 'test1@gmail.com', '0952122222', NULL, 'male', '2015-06-09', 'Sinh viên', '[\"Y tế cơ bản\"]', NULL, 'no', 'no', 0, 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-28 08:01:07', '2026-02-28 08:01:07'),
(17, NULL, 4, 'trong', 'tronglen10123456@gmail.com', '0987846512', NULL, 'male', NULL, '', '[\"Chăm sóc người già\"]', NULL, 'no', 'no', 0, 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-28 08:01:54', '2026-02-28 08:01:54'),
(18, NULL, 4, 'Nguyễn Văn Trọng', '23010817@st.phenikaa-uni.edu.vn', '0911224115', NULL, 'male', '2016-06-07', 'Sinh viên', '[\"Kế toán\"]', NULL, 'no', 'no', 0, 0, 0, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-28 08:05:41', '2026-03-01 06:45:20'),
(19, NULL, 9, 'Nguyễn Văn Trọng', '23010817@st.phenikaa-uni.edu.vn', '0911224115', NULL, 'male', '2016-06-07', 'Sinh viên', '[\"Y tế cơ bản\"]', NULL, 'no', 'no', 0, 0, 0, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-02-28 08:08:00', '2026-03-01 06:41:45');
>>>>>>> 31c9649 (update local changes)

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
<<<<<<< HEAD
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
=======
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `summary` text DEFAULT NULL
>>>>>>> 31c9649 (update local changes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

<<<<<<< HEAD
INSERT INTO `news` (`id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `thumbnail`, `gallery`, `category`, `is_featured`, `is_breaking`, `priority`, `views`, `meta_description`, `meta_keywords`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ra mắt nền tảng Charity Event - Kết nối yêu thương', 'ra-mat-nen-tang-charity-event', 'Charity Event chính thức ra mắt với sứ mệnh kết nối những tấm lòng nhân ái, mang đến sự minh bạch và hiệu quả cho hoạt động từ thiện.', '<p>Ngày 01/01/2026, nền tảng <strong>Charity Event</strong> chính thức ra mắt cộng đồng...</p>', 'news-1.jpg', NULL, 'announcement', 1, 0, 0, 0, NULL, NULL, 'published', '2026-02-25 15:17:06', '2026-02-25 15:17:06', '2026-02-25 15:17:06'),
(2, 1, 'Hướng dẫn quyên góp trên Charity Event', 'huong-dan-quyen-gop', 'Quy trình quyên góp đơn giản, nhanh chóng và an toàn trên nền tảng Charity Event.', '<p>Bài viết hướng dẫn chi tiết cách quyên góp...</p>', 'news-2.jpg', NULL, 'news', 0, 0, 0, 0, NULL, NULL, 'published', '2026-02-25 15:17:06', '2026-02-25 15:17:06', '2026-02-25 15:17:06');
=======
INSERT INTO `news` (`id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `thumbnail`, `gallery`, `category`, `is_featured`, `is_breaking`, `priority`, `views`, `meta_description`, `meta_keywords`, `status`, `published_at`, `created_at`, `updated_at`, `summary`) VALUES
(3, 1, 'Chương trình \"Mùa đông ấm áp\" quyên góp được 500 triệu đồng', 'chuong-trinh-mua-dong-am-ap-quyen-gop-duoc-500-trieu-dong', NULL, '<p>Chương trình từ thiện \"Mùa đông ấm áp\" do Trung tâm Từ thiện phối hợp cùng các nhà hảo tâm tổ chức đã kết thúc thành công tốt đẹp với tổng số tiền quyên góp lên tới 500 triệu đồng.</p>\r\n <p>Số tiền này sẽ được sử dụng để mua 2000 áo ấm, 1500 chăn và 500 suất quà Tết cho bà con vùng cao tại các tỉnh miền núi phía Bắc.</p>\r\n <h3>Những con số ấn tượng</h3>\r\n <ul>\r\n <li>Tổng số người quyên góp: 1,234 người</li>\r\n <li>Số tiền quyên góp trung bình: 405,000 VNĐ</li>\r\n <li>Số tình nguyện viên tham gia: 89 người</li>\r\n </ul>\r\n <p>Ban tổ chức xin chân thành cảm ơn sự đóng góp của tất cả mọi người!</p>', 'news1.jpg', NULL, '', 0, 0, 0, 525, NULL, NULL, 'published', '2026-02-26 08:19:45', '2026-02-25 08:19:45', '2026-02-28 16:30:24', 'Sau 1 tháng triển khai, chương trình đã nhận được sự ủng hộ nhiệt tình từ cộng đồng với tổng số tiền quyên góp lên tới 500 triệu đồng.'),
(4, 1, 'Trao 100 suất học bổng cho học sinh nghèo vượt khó', 'trao-100-suat-hoc-bong-cho-hoc-sinh-ngheo-vuot-kho', NULL, '<p>Sáng ngày 25/2/2026, tại Trường THCS Lý Tự Trọng (Hà Nội), Quỹ Học bổng đã tổ chức buổi lễ trao 100 suất học bổng cho các em học sinh nghèo vượt khó với tổng giá trị 200 triệu đồng.</p>\r\n <p>Đây là những em học sinh có hoàn cảnh gia đình khó khăn nhưng vẫn đạt thành tích học tập xuất sắc. Mỗi suất học bổng trị giá 2 triệu đồng sẽ giúp các em trang trải chi phí học tập trong năm học 2025-2026.</p>\r\n <h3>Xúc động trước hoàn cảnh các em</h3>\r\n <p>Em Nguyễn Văn A, học sinh lớp 9A chia sẻ: \"Em rất vui và xúc động khi nhận được học bổng này. Đây là động lực lớn để em tiếp tục cố gắng học tập.\"</p>', 'news2.jpg', NULL, '', 0, 0, 0, 414, NULL, NULL, 'published', '2026-02-23 08:19:45', '2026-02-22 08:19:45', '2026-03-02 04:42:13', 'Trong buổi lễ sáng ngày 25/2, Quỹ Học bổng đã trao 100 suất học bổng trị giá 2 triệu đồng/suất cho các em học sinh có hoàn cảnh khó khăn.'),
(5, 1, 'Khởi động chiến dịch \"Trồng 10,000 cây xanh\" tại Hà Giang', 'khoi-dong-chien-dich-trong-10000-cay-xanh-tai-ha-giang', NULL, '<p>Ngày 20/2/2026, Ban tổ chức chiến dịch \"Trồng 10,000 cây xanh\" đã họp báo công bố chính thức khởi động dự án tại Hà Giang.</p>\r\n <p>Chiến dịch sẽ kéo dài 2 tháng (từ tháng 3 đến tháng 4) với sự tham gia của 500 tình nguyện viên và người dân địa phương.</p>\r\n <h3>Mục tiêu của chiến dịch</h3>\r\n <ul>\r\n <li>Trồng 10,000 cây xanh các loại (cây ăn quả, cây lấy gỗ)</li>\r\n <li>Phủ xanh 50 hecta đất trống tại các xã vùng cao</li>\r\n <li>Nâng cao nhận thức về bảo vệ môi trường</li>\r\n <li>Tạo sinh kế bền vững cho người dân</li>\r\n </ul>\r\n <p>Dự án được tài trợ bởi nhiều doanh nghiệp và cá nhân với tổng kinh phí 700 triệu đồng.</p>', 'news3.jpg', NULL, 'announcement', 0, 0, 0, 678, NULL, NULL, 'published', '2026-02-27 08:19:45', '2026-02-27 08:19:45', '2026-02-28 08:19:45', 'Chiến dịch trồng cây xanh quy mô lớn sẽ được triển khai tại tỉnh Hà Giang từ tháng 3/2026 với mục tiêu phủ xanh 50 hecta đất trống.'),
(6, 1, 'Cứu trợ khẩn cấp cho đồng bào lũ lụt miền Trung', 'cuu-tro-khan-cap-cho-dong-bao-lu-lut-mien-trung', NULL, '<p>Do ảnh hưởng của bão số 3, nhiều khu vực tại Hà Tĩnh và Quảng Bình đã bị ngập lụt nghiêm trọng. Hàng nghìn hộ dân phải di dời, mất nhà cửa và tài sản.</p>\r\n <p>Ngay sau khi nhận được tin, các tổ chức từ thiện đã khẩn trương huy động nguồn lực và tổ chức đoàn cứu trợ đến hiện trường.</p>\r\n <h3>Những gì đã được hỗ trợ</h3>\r\n <ul>\r\n <li>1,000 thùng mì tôm</li>\r\n <li>500 thùng nước uống</li>\r\n <li>300 bộ quần áo</li>\r\n <li>200 chăn màn</li>\r\n <li>Thuốc men và vật tư y tế</li>\r\n </ul>\r\n <p>Tổng giá trị hỗ trợ ước tính 300 triệu đồng. Đoàn vẫn tiếp tục kêu gọi quyên góp để hỗ trợ thêm cho bà con.</p>', 'news4.jpg', NULL, '', 0, 0, 0, 894, NULL, NULL, 'published', '2026-02-28 08:19:45', '2026-02-28 08:19:45', '2026-03-02 04:16:45', 'Đoàn cứu trợ đã đến các vùng bị ảnh hưởng nặng nề bởi lũ lụt tại Hà Tĩnh và Quảng Bình để hỗ trợ lương thực, nước uống và thuốc men.'),
(7, 1, 'Phát động chiến dịch hiến máu nhân đạo \"Giọt hồng yêu thương\"', 'phat-dong-chien-dich-hien-mau-nhan-dao-giot-hong-yeu-thuong', NULL, '<p>Hội Chữ thập đỏ Việt Nam phối hợp cùng các bệnh viện sẽ tổ chức chiến dịch hiến máu tình nguyện \"Giọt hồng yêu thương\" từ ngày 1/3 đến 15/3/2026.</p>\r\n <p>Chiến dịch nhằm kêu gọi cộng đồng tham gia hiến máu tình nguyện, góp phần cứu chữa người bệnh trong tình trạng dự trữ máu đang thiếu hụt.</p>\r\n <h3>Thông tin tham gia</h3>\r\n <p><strong>Điều kiện:</strong> Từ 18-60 tuổi, cân nặng từ 45kg, sức khỏe tốt</p>\r\n <p><strong>Địa điểm:</strong> 10 điểm hiến máu tại Hà Nội, TP.HCM, Đà Nẵng và các tỉnh</p>\r\n <p><strong>Quà tặng:</strong> Mỗi người hiến máu sẽ nhận được giấy chứng nhận, quà lưu niệm và được khám sức khỏe miễn phí</p>', 'news5.jpg', NULL, 'announcement', 0, 0, 0, 448, NULL, NULL, 'published', '2026-02-21 08:19:45', '2026-02-20 08:19:45', '2026-03-02 04:30:58', 'Chiến dịch hiến máu sẽ được tổ chức tại 10 tỉnh thành trên cả nước với mục tiêu tiếp nhận 5,000 đơn vị máu.');
>>>>>>> 31c9649 (update local changes)

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

<<<<<<< HEAD
=======
--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `action_key`, `attempts`, `first_attempt_at`, `last_attempt_at`) VALUES
(35, 'volunteer_::1', 2, 1772266108, 1772266149);

-- --------------------------------------------------------

--
-- Table structure for table `report_schedules`
--

CREATE TABLE `report_schedules` (
  `id` int(11) NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `report_type` enum('daily_summary','weekly_summary','monthly_revenue','event_performance','user_activity','donation_report') NOT NULL,
  `frequency` enum('daily','weekly','monthly') NOT NULL,
  `schedule_time` time DEFAULT NULL,
  `schedule_day` int(11) DEFAULT NULL,
  `recipients` text DEFAULT NULL,
  `report_config` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

>>>>>>> 31c9649 (update local changes)
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
<<<<<<< HEAD
=======
-- Table structure for table `statistics_cache`
--

CREATE TABLE `statistics_cache` (
  `id` int(11) NOT NULL,
  `cache_key` varchar(100) NOT NULL,
  `cache_data` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_public`, `updated_by`, `updated_at`) VALUES
(1, 'site_name', 'Charity Event', 'text', 'general', 'Tên website', 1, NULL, '2026-03-01 04:58:47'),
(2, 'site_tagline', 'Nền tảng kết nối từ thiện minh bạch', 'text', 'general', 'Slogan website', 1, NULL, '2026-03-01 04:58:47'),
(3, 'items_per_page', '20', 'number', 'general', 'Số items mỗi trang', 0, NULL, '2026-03-01 04:58:47'),
(4, 'max_upload_size', '5242880', 'number', 'general', 'Kích thước upload tối đa (bytes)', 0, NULL, '2026-03-01 04:58:47'),
(5, 'enable_registrations', 'true', 'boolean', 'general', 'Cho phép đăng ký mới', 1, NULL, '2026-03-01 04:58:47'),
(6, 'maintenance_mode', 'false', 'boolean', 'general', 'Chế độ bảo trì', 0, NULL, '2026-03-01 04:58:47'),
(7, 'admin_email', 'admin@charityevent.vn', 'text', 'email', 'Email admin nhận thông báo', 0, NULL, '2026-03-01 04:58:47'),
(8, 'smtp_enabled', 'false', 'boolean', 'email', 'Kích hoạt SMTP', 0, NULL, '2026-03-01 04:58:47'),
(9, 'session_lifetime', '7200', 'number', 'security', 'Thời gian session (giây)', 0, NULL, '2026-03-01 04:58:47'),
(10, 'max_login_attempts', '5', 'number', 'security', 'Số lần đăng nhập tối đa', 0, NULL, '2026-03-01 04:58:47');

-- --------------------------------------------------------

--
>>>>>>> 31c9649 (update local changes)
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
<<<<<<< HEAD
=======
  `bio` text DEFAULT NULL,
>>>>>>> 31c9649 (update local changes)
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `role` enum('user','benefactor','admin') DEFAULT 'user',
  `benefactor_status` enum('none','pending','approved','rejected') DEFAULT 'none',
  `benefactor_verified_at` timestamp NULL DEFAULT NULL,
  `benefactor_verified_by` int(11) DEFAULT NULL,
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
<<<<<<< HEAD
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
=======
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_profile` tinyint(1) DEFAULT 1,
  `show_donations` tinyint(1) DEFAULT 1,
  `show_volunteers` tinyint(1) DEFAULT 1,
  `email_notifications` tinyint(1) DEFAULT 1,
  `donation_notifications` tinyint(1) DEFAULT 1,
  `volunteer_notifications` tinyint(1) DEFAULT 1,
  `event_notifications` tinyint(1) DEFAULT 1
>>>>>>> 31c9649 (update local changes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

<<<<<<< HEAD
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
=======
INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `phone`, `avatar`, `address`, `bio`, `city`, `district`, `role`, `benefactor_status`, `benefactor_verified_at`, `benefactor_verified_by`, `status`, `last_login`, `created_at`, `updated_at`, `public_profile`, `show_donations`, `show_volunteers`, `email_notifications`, `donation_notifications`, `volunteer_notifications`, `event_notifications`) VALUES
(1, 'Administrator', 'admin@charityevent.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'default-avatar.png', NULL, NULL, NULL, NULL, 'admin', 'none', NULL, NULL, 'active', '2026-03-02 05:31:16', '2026-02-25 15:17:06', '2026-03-02 05:31:16', 1, 1, 1, 1, 1, 1, 1),
(2, 'Nguyễn Văn Hảo', 'benefactor1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 'default-avatar.png', NULL, NULL, NULL, NULL, 'benefactor', 'approved', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', 1, 1, 1, 1, 1, 1, 1),
(3, 'Trần Thị Tâm', 'benefactor2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'default-avatar.png', NULL, NULL, NULL, NULL, 'benefactor', 'approved', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', 1, 1, 1, 1, 1, 1, 1),
(4, 'Lê Văn Nghĩa', 'benefactor3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0923456789', 'default-avatar.png', NULL, NULL, NULL, NULL, 'benefactor', 'approved', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', 1, 1, 1, 1, 1, 1, 1),
(5, 'Phạm Minh Anh', 'user1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0934567890', 'default-avatar.png', NULL, NULL, NULL, NULL, 'user', 'none', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', 1, 1, 1, 1, 1, 1, 1),
(6, 'Hoàng Thị Lan', 'user2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0945678901', 'default-avatar.png', NULL, NULL, NULL, NULL, 'user', 'none', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', 1, 1, 1, 1, 1, 1, 1),
(7, 'Đặng Quốc Huy', 'user3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0956789012', 'default-avatar.png', NULL, NULL, NULL, NULL, 'user', 'none', NULL, NULL, 'active', NULL, '2026-02-25 15:17:28', '2026-02-25 15:17:28', 1, 1, 1, 1, 1, 1, 1),
(8, 'Nguyễn Văn Trọng', 'tronglen10123456@gmail.com', '$2y$10$JMeV4qlXRsAjk81FR2BLCe99KjcWpKZY19W/v8SyJR90BT6kJMp5O', '0987846512', 'default-avatar.png', NULL, NULL, NULL, NULL, 'user', 'none', NULL, NULL, 'active', '2026-02-25 15:34:13', '2026-02-25 15:21:24', '2026-02-25 15:34:13', 1, 1, 1, 1, 1, 1, 1),
(9, 'trong', 'test1@gmail.com', '$2y$10$pmr1GfXsarynIjPJicV3g.gjEY.IbscPYLtbZhaI0hXbSvOP51hNu', '0987846512', 'default-avatar.png', NULL, NULL, NULL, NULL, 'user', 'none', NULL, NULL, 'active', '2026-03-02 06:40:16', '2026-02-25 15:34:05', '2026-03-02 06:40:16', 1, 1, 1, 1, 1, 1, 1);
>>>>>>> 31c9649 (update local changes)

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

<<<<<<< HEAD
=======
-- --------------------------------------------------------

--
-- Structure for view `admin_dashboard_stats`
--
DROP TABLE IF EXISTS `admin_dashboard_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `admin_dashboard_stats`  AS SELECT (select count(0) from `users` where `users`.`role` = 'user') AS `total_users`, (select count(0) from `users` where `users`.`benefactor_status` = 'approved') AS `total_benefactors`, (select count(0) from `events` where `events`.`status` = 'approved') AS `total_events`, (select count(0) from `events` where `events`.`status` = 'pending') AS `pending_events`, (select coalesce(sum(`donations`.`amount`),0) from `donations` where `donations`.`status` = 'completed') AS `total_donations`, (select count(distinct `donations`.`user_id`) from `donations` where `donations`.`status` = 'completed') AS `total_donors`, (select count(0) from `donations` where `donations`.`status` = 'pending') AS `pending_donations`, (select count(0) from `benefactor_applications` where `benefactor_applications`.`status` = 'pending') AS `pending_benefactors`, (select count(0) from `event_volunteers` where `event_volunteers`.`status` = 'pending') AS `pending_volunteers` ;

-- --------------------------------------------------------

--
-- Structure for view `admin_recent_activities`
--
DROP TABLE IF EXISTS `admin_recent_activities`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `admin_recent_activities`  AS SELECT 'donation' AS `activity_type`, `d`.`id` AS `activity_id`, coalesce(`u`.`fullname`,`d`.`donor_name`,'Ẩn danh') AS `actor_name`, `e`.`title` AS `target_name`, `d`.`amount` AS `value`, `d`.`created_at` AS `activity_time` FROM ((`donations` `d` left join `users` `u` on(`d`.`user_id` = `u`.`id`)) join `events` `e` on(`d`.`event_id` = `e`.`id`)) WHERE `d`.`status` = 'completed'union all select 'event' AS `activity_type`,`e`.`id` AS `activity_id`,`u`.`fullname` AS `actor_name`,`e`.`title` AS `target_name`,`e`.`target_amount` AS `value`,`e`.`created_at` AS `activity_time` from (`events` `e` join `users` `u` on(`e`.`user_id` = `u`.`id`)) where `e`.`status` = 'approved' order by `activity_time` desc limit 20  ;

>>>>>>> 31c9649 (update local changes)
--
-- Indexes for dumped tables
--

--
<<<<<<< HEAD
=======
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_admin_logs_date` (`created_at`),
  ADD KEY `idx_admin_logs_target` (`target_type`,`target_id`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `read_by` (`read_by`),
  ADD KEY `idx_type` (`notification_type`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_admin_permission` (`admin_id`);

--
-- Indexes for table `backup_history`
--
ALTER TABLE `backup_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `banned_ips`
--
ALTER TABLE `banned_ips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `banned_by` (`banned_by`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_expires` (`expires_at`);

--
>>>>>>> 31c9649 (update local changes)
-- Indexes for table `benefactor_applications`
--
ALTER TABLE `benefactor_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user_id` (`user_id`);

--
<<<<<<< HEAD
=======
-- Indexes for table `charity_registrations`
--
ALTER TABLE `charity_registrations`
  ADD PRIMARY KEY (`id`);

--
>>>>>>> 31c9649 (update local changes)
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
<<<<<<< HEAD
=======
-- Indexes for table `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`);

--
>>>>>>> 31c9649 (update local changes)
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
<<<<<<< HEAD
=======
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_key` (`template_key`),
  ADD KEY `idx_key` (`template_key`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_email_templates_active` (`is_active`);

--
>>>>>>> 31c9649 (update local changes)
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
<<<<<<< HEAD
=======
-- Indexes for table `report_schedules`
--
ALTER TABLE `report_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_next_run` (`next_run_at`);

--
>>>>>>> 31c9649 (update local changes)
-- Indexes for table `statistics`
--
ALTER TABLE `statistics`
  ADD PRIMARY KEY (`id`);

--
<<<<<<< HEAD
=======
-- Indexes for table `statistics_cache`
--
ALTER TABLE `statistics_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cache_key` (`cache_key`),
  ADD KEY `idx_key` (`cache_key`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_key` (`setting_key`),
  ADD KEY `idx_settings_public` (`is_public`);

--
>>>>>>> 31c9649 (update local changes)
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
<<<<<<< HEAD
=======
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `backup_history`
--
ALTER TABLE `backup_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banned_ips`
--
ALTER TABLE `banned_ips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
>>>>>>> 31c9649 (update local changes)
-- AUTO_INCREMENT for table `benefactor_applications`
--
ALTER TABLE `benefactor_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
<<<<<<< HEAD
=======
-- AUTO_INCREMENT for table `charity_registrations`
--
ALTER TABLE `charity_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
>>>>>>> 31c9649 (update local changes)
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
<<<<<<< HEAD
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
=======
-- AUTO_INCREMENT for table `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
>>>>>>> 31c9649 (update local changes)

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
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
>>>>>>> 31c9649 (update local changes)

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
>>>>>>> 31c9649 (update local changes)

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
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `report_schedules`
--
ALTER TABLE `report_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
>>>>>>> 31c9649 (update local changes)

--
-- AUTO_INCREMENT for table `statistics`
--
ALTER TABLE `statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
<<<<<<< HEAD
=======
-- AUTO_INCREMENT for table `statistics_cache`
--
ALTER TABLE `statistics_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
>>>>>>> 31c9649 (update local changes)
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
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
>>>>>>> 31c9649 (update local changes)

--
-- AUTO_INCREMENT for table `user_terms_acceptance`
--
ALTER TABLE `user_terms_acceptance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
<<<<<<< HEAD
=======
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_ibfk_1` FOREIGN KEY (`read_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD CONSTRAINT `admin_permissions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `backup_history`
--
ALTER TABLE `backup_history`
  ADD CONSTRAINT `backup_history_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `banned_ips`
--
ALTER TABLE `banned_ips`
  ADD CONSTRAINT `banned_ips_ibfk_1` FOREIGN KEY (`banned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
>>>>>>> 31c9649 (update local changes)
-- Constraints for table `benefactor_applications`
--
ALTER TABLE `benefactor_applications`
  ADD CONSTRAINT `benefactor_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
<<<<<<< HEAD
=======
-- Constraints for table `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  ADD CONSTRAINT `dashboard_widgets_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
>>>>>>> 31c9649 (update local changes)
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
<<<<<<< HEAD
=======
-- Constraints for table `report_schedules`
--
ALTER TABLE `report_schedules`
  ADD CONSTRAINT `report_schedules_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
>>>>>>> 31c9649 (update local changes)
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
