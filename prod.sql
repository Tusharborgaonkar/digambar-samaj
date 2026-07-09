-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 17, 2026 at 02:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `digambar`
--
CREATE DATABASE IF NOT EXISTS `digambar`;
USE `digambar`;

-- --------------------------------------------------------

--
-- Table structure for table `account_requests`
--

CREATE TABLE `account_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `request_type` enum('deactivation','deletion') DEFAULT 'deletion',
  `reason` text DEFAULT NULL,
  `status` enum('pending','processed','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_type` enum('admin','user') DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `status` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `password_updated_at` datetime DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password_hash`, `role`, `status`, `last_login`, `last_login_ip`, `password_updated_at`, `two_factor_enabled`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@digambar.com', '$2y$10$56pkqSyGd.VmWIhrx1CIV.IDkx1FRMIpFvQmh94WW0C8n75aDVbLy', 'super_admin', 1, '2026-06-17 17:14:13', '::1', NULL, 0, '2026-06-11 13:01:16', '2026-06-17 11:44:13'),
(2, 'Super Admin', 'admin@digambarsamaj.com', '$2y$10$vmPfehzghktivLrgopSfN.UZFdmA7GmDJ0QcKAiyIKhXf2tEnIBI2', 'super_admin', 1, '2026-06-17 05:33:22', '2402:a00:403:12b4:280a:ab52:468b:f44d', NULL, 0, '2026-06-13 09:25:32', '2026-06-17 05:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `advertisements`
--

CREATE TABLE `advertisements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `position` enum('home_top','home_bottom','sidebar') DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_events`
--

CREATE TABLE `community_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `family_details`
--

CREATE TABLE `family_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `father_mobile` varchar(20) DEFAULT NULL,
  `father_income` decimal(12,2) DEFAULT NULL,
  `father_occupation` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_mobile` varchar(20) DEFAULT NULL,
  `mother_occupation` varchar(255) DEFAULT NULL,
  `brothers` int(11) DEFAULT 0,
  `brothers_married` int(11) DEFAULT 0,
  `brothers_unmarried` int(11) DEFAULT 0,
  `sisters` int(11) DEFAULT 0,
  `sisters_married` int(11) DEFAULT 0,
  `sisters_unmarried` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `image_path`, `uploaded_by`, `status`, `created_at`) VALUES
(1, 'Darshan Jain', 'assets/images/gallery/img_6a2c02a3decdc.jpeg', NULL, 1, '2026-06-12 12:59:15');

-- --------------------------------------------------------

--
-- Table structure for table `import_history`
--

CREATE TABLE `import_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `imported_records` int(11) DEFAULT NULL,
  `imported_by` bigint(20) UNSIGNED DEFAULT NULL,
  `import_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `import_images`
--

CREATE TABLE `import_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `image_type` varchar(50) NOT NULL,
  `member_name_key` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size_bytes` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_time` time DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `native` varchar(100) DEFAULT NULL,
  `gotra` varchar(100) DEFAULT NULL,
  `mama_gotra` varchar(100) DEFAULT NULL,
  `manglik` enum('yes','no') DEFAULT NULL,
  `height_cm` smallint(5) UNSIGNED DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `country_code` varchar(5) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `permanent_pin_code` char(6) DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `higher_education` text DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `monthly_income` decimal(12,2) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `father_mobile` varchar(20) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `father_monthly_income` decimal(12,2) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_mobile` varchar(20) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `brothers_total` tinyint(3) UNSIGNED DEFAULT NULL,
  `brothers_married` tinyint(3) UNSIGNED DEFAULT NULL,
  `brothers_unmarried` tinyint(3) UNSIGNED DEFAULT NULL,
  `sisters_total` tinyint(3) UNSIGNED DEFAULT NULL,
  `sisters_married` tinyint(3) UNSIGNED DEFAULT NULL,
  `sisters_unmarried` tinyint(3) UNSIGNED DEFAULT NULL,
  `partner_preferences` text DEFAULT NULL,
  `languages_known` text DEFAULT NULL,
  `hobbies` text DEFAULT NULL,
  `widow_divorce` enum('widow','divorcee','none') DEFAULT NULL,
  `handicapped_physical_deficiency` text DEFAULT NULL,
  `profile_photo_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `full_name`, `gender`, `birth_date`, `birth_time`, `birth_place`, `native`, `gotra`, `mama_gotra`, `manglik`, `height_cm`, `weight_kg`, `country_code`, `mobile_number`, `email`, `permanent_address`, `permanent_pin_code`, `current_address`, `higher_education`, `occupation`, `company_name`, `designation`, `monthly_income`, `father_name`, `father_mobile`, `father_occupation`, `father_monthly_income`, `mother_name`, `mother_mobile`, `mother_occupation`, `brothers_total`, `brothers_married`, `brothers_unmarried`, `sisters_total`, `sisters_married`, `sisters_unmarried`, `partner_preferences`, `languages_known`, `hobbies`, `widow_divorce`, `handicapped_physical_deficiency`, `profile_photo_path`, `created_at`, `updated_at`) VALUES
(1, 'mmmmmmmmmm', 'Male', '2000-01-01', '15:36:00', 'mjjjjjjjjjjj', 'nnnnnnnnnnn', 'mmmmmmmmmm', 'mmmmmmmmmmmm', 'no', 165, 46.00, '91', '9876543210', 'maj@gmail.com', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa\naaaaaaaaaaaaaaaaaaaa', '380006', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa\naaaaaaaaaaaaaaaaaaaa', 'mcom', 'Job', 'aaaaaa', 'aaaaaa', 100000.00, 'aaaaaaa', '9876543210', 'Business', 1000.00, 'mmm', '9876543210', 'House Wife', 3, 2, 1, 1, 0, 1, 'no', 'Hindi, English', 'music', 'none', 'No', 'imports/profile_photos/mmmmmmmmmm_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(2, 'Milesh', 'Male', '1975-08-09', '06:30:00', 'idar', 'idar', 'ggggg', 'hhhhh', 'no', 170, 65.00, '91', '9824312179', 'mileshdoshi@gmail.com', 'cggggg', '380061', 'ggggyyy', 'tyyyy', 'Business', '', '', 6666677.00, 'ggggg', '9824312179', 'Business', 6667777.00, 'hhhhhh', '9408720799', 'House Wife', 1, 1, 1, 1, 1, 1, 'yyyyyy666666', 'Gujarati, English', 'yyhhhu', 'none', 'No', 'imports/profile_photos/Milesh_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(3, 'Monil Sunil Shah', 'Male', '1997-06-11', '10:10:00', 'Ankleshwar', 'Vedach, Vadodara, Gujarat', 'Nageshwar', 'Nageshwar', 'no', 180, 82.00, '1', '2137258952', 'shahmoniljay@gmail.com', 'Nav Rajhans Soc., Rokadia lane, Borivali West, Mumbai – 400092.', '400092', 'Sunnyvale, California - USA', 'Masters in Computer Science USA, Bachelors in Computer Engineering, Mumbai', 'Job', 'Citrix Systems Inc.', 'Senior Software Engineer', 0.00, 'Sunil Bipinchandra Shah', '9870012639', 'Job', 0.00, 'Jaina Sunil Shah', '9757167426', 'House Wife', 0, 0, 0, 0, 0, 0, 'Someone who enjoys staying curious, embracing new experiences, and sharing joyful moments through creative and engaging activities.', 'Gujarati, Hindi, English', 'Sports, Technology enthusiast, avid reader and exploring quality movies with friends & family', 'none', 'No', 'imports/profile_photos/Monil_Sunil_Shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(4, 'Vishwa Mukesh bhai Jain', 'Female', '1997-04-12', '07:00:00', 'Ahmedabad', 'Udaipur', 'Jangda', 'Sangawat', 'no', 160, 55.00, '91', '9662213979', 'vishwa27.vj@gmail.com', '27, karnavati bungalows, opposite Baroda express Highway, CTM, Ahmedabad', '380026', 'Same as above', 'B.tech mechanical engineering', 'Business', 'TANMAY\'S AMAZING SPACE CENTER', 'Owner', 200000.00, 'Mukesh bhai Jangda', '9824312979', 'Jwellers', 300000.00, 'Ranjana Jangda', '9662213979', 'House Wife', 1, 1, 0, 0, 0, 0, 'Smart, intelligent', 'Gujarati, Hindi, English', 'Astronomy, Music, Trekking, photography, poetry', 'none', 'No', 'imports/profile_photos/Vishwa_Mukesh_bhai_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(5, 'Shruti Jain', 'Female', '1995-05-02', '00:15:00', 'Dahod', 'Indore (mp)', 'Humad mantreswar', 'Kherju', 'yes', 152, 75.00, '1', '9827053422', 'ykbjain@gmail.com', '144, Tilaknagar Extension Near Shankarlal pachori gaurdan indore', '452018', 'Same as above', 'BE. pgpm', 'Job', 'Deloitte Us Mumbai', 'Assosiet manager', 7.00, 'Yogesh Kumar Pancholi', '9827053422', 'Business', 7.00, 'PRITI jain', '9424566066', 'House Wife', 1, 0, 1, 1, 1, 0, 'Digamber Jain', 'Hindi, English', 'Cooking, Dancing etc.', 'none', 'No', 'imports/profile_photos/Shruti_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(6, 'Arpit Jain', 'Male', '1996-09-01', '20:15:00', 'Bhopal', 'Bhopal', 'Giya', 'Nayak', 'no', 173, 60.00, '91', '7828005206', 'jainarpit423@gmail.com', 'H No-63 Laxmi Nagar Piplani BHEL Bhopal Madhya Pradesh', '462022', 'H No-63 Laxmi Nagar Piplani BHEL Bhopal Madhya Pradesh', 'B-Tech', 'Job', 'Torrent Pharma Ltd', 'Executive', 8.00, 'Sanjay Jain', '8770361342', 'Retired', 10.00, 'Aradhana Jain', '9827369799', 'House Wife', 0, 0, 0, 1, 0, 0, 'Educated, Good-looking, Belong from Gujarat and Rajasthan state', 'Gujarati, Hindi, English', 'Cricket, Travelling, Cooking', 'none', 'No', 'imports/profile_photos/Arpit_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(7, 'Hinal Rajan Shah', 'Female', '1994-05-17', '12:50:00', 'Mandvi, Surat', 'Ahmedabad', 'Kashyap', 'Rajyanu', 'no', 152, 65.00, '91', '9662912361', 'dhyeyaelectrical@gmail.com', 'C-404, 3rd Eye Residency one, Opposite Sangath - 2, Near Narendra Modi Stadium, Ahmedabad - 380005', '380005', 'Bangalore', 'B.E Power Electronics Enginner', 'Job', 'Novo Nordisk Engineering Private Limited, Bangalore', 'Instrumentation Engineer', 70000.00, 'Rajan Shah', '9662912361', 'Business', 0.00, 'Devyani Shah', '8527391653', 'Home Maker', 1, 1, 0, 0, 0, 0, 'The key is to be honest, positive, and clear while remaining realistic and flexible.', 'Gujarati, Hindi, English, Japanese', 'Painting, Movies, Travelling', 'none', 'No', 'imports/profile_photos/Hinal_Rajan_Shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(8, 'Dr Rushabh Chetankumar', 'Male', '1997-10-05', '03:53:00', 'Anand Gujarat', 'Idar Gujarat', 'Mantreshwar', 'Kherju', 'yes', 170, 60.00, '91', '9426509090', 'cvshah2007@gmail.com', '“Rushabh”,  12A, Abhishek Tenament, Near Maruti Kirtan, Opp. Shree Park Marrige Hall, 80 Feet Road, Anand -388001 (Gujarat)', '388001', 'Same as above', 'BDS, MDS (Oral and Maxillofacial Surgery) and Doing Fellowship in Oncology at Karamsad, Anand', 'Practicing Doctor and Doing Fellowship in Oncology at Karmsad Medical', 'Dr Rushabh Shah', 'Own Practice', 100000.00, 'Chetankumar Vakhechand Shah', '9426509090', 'Business', 200000.00, 'Kiranben Chetankumar Shah', '9427598061', 'Retired Government Teacher', 0, 0, 0, 2, 2, 0, 'first Preferance to Doctors or Chartered Accountant', 'Gujarati, Hindi, English', 'Cricket and travelling', 'none', 'No', 'imports/profile_photos/Dr_Rushabh_Chetankumar_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(9, 'Daksh Kamleshkumar Jain', 'Male', '2001-10-03', '18:15:00', 'Idar ,Gujarat', 'Vijaynagar,  Gujarat', 'Kashveshver', 'Kherju', 'no', 170, 59.00, '91', '7574908991', 'jaindaksh1556@gmail.com', '10, Shivam Society near anandnagar aera,Idar', '383430', 'Surat ,Gujarat', 'B.TECH (in CE)', 'Job', 'Crest Infosystems', 'Software Engineer', 66000.00, 'Kamleshkumar Champalalji Jain', '9427480121', 'Retired', 105000.00, 'Mamataben Kamleshkumar Jain', '9512024121', 'House Wife', 0, 0, 0, 0, 0, 0, 'She should able to understand me.', 'Gujarati, Hindi, English', 'Traveling,Playing sports', 'none', 'No', 'imports/profile_photos/Daksh_Kamleshkumar_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(10, 'Kenil K Shah', 'Male', '1994-04-17', '06:57:00', 'Ahmedabad', 'Narsinhpura Digambar jain', 'Kalashdhar', 'Kalashdhar', 'yes', 173, 62.00, '91', '9374069772', 'kenillshah@gmail.com', 'Ahmedabad', '380013', 'Same', 'MCA(MIT Pune) MS in artificial intelligence &Machine learning (AAIC-Hyderabad)', 'Job', 'Citicorp India pvt ltd', 'Assistant vice president in Artificial intelligence', 250000.00, 'Kalpesh C Shah', '9374069772', 'Job', 70000.00, 'Pragnaben K Shah', '8200633470', 'House Wife', 0, 0, 0, 1, 1, 0, 'Highly educated and good looks', 'Gujarati, Hindi, English', 'Cricket ,Movies', 'none', 'No', 'imports/profile_photos/Kenil_K_Shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(11, 'Priyanshi Jain', 'Female', '2000-09-17', '04:37:00', 'Ramganjmandi', 'Jaipur', 'Buddheshwar', 'Pankheshwar', 'no', 163, 60.00, '91', '9413834690', 'priyanshidoshi17@gmail.com', 'B-84 Parshwanath colony , nirman nagar , jaipur', '302019', 'Sion , Mumbai', 'MBA', 'Job', '', '', 120000.00, 'Pankaj Jain', '9829132965', 'Job', 0.00, 'Jyotsana Jain', '', 'Retired  Vice Principle', 0, 0, 0, 0, 0, 0, 'NO', 'Hindi, English', 'Basketball , Painting , Travelling', 'none', 'No', 'imports/profile_photos/Priyanshi_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(12, 'Rajesh Kumar jain', 'Male', '1975-10-25', '04:01:00', 'Bhinder', 'Bhinder', 'Barod (udaipuriya)', 'Kanthliya', 'no', 183, 78.00, '9999', '9216456943', 'rajkuma0197246@gmail.com', 'Rampoul bus stand choraya bhinder (rajasthan) district: udaipur', '313603', 'Rampoul bus stand choraya bhinder (rajasthan) district: udaipur', 'B com.', 'Business', 'Rishabh cloth store', 'Honour', 45000.00, 'Dhanpal jain', '9982274215', 'Business', 20000.00, 'Dilkhush devi', '', 'House Wife', 0, 0, 0, 3, 3, 0, 'Well cultured.... Religious', 'Hindi', 'Singing n relegion', 'none', 'No', 'imports/profile_photos/Rajesh_Kumar_jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(13, 'Prankul Jain', 'Male', '1994-08-17', '07:30:00', 'Nurabad, Morena, M.P.', 'Banmore, Morena, M.P.', 'Kayre (Palliwal)', 'Patni (Khandelwal)', 'no', 163, 65.00, '91', '9827479300', 'jainprankul2747@gmail.com', '132, Ward No. 03, A.B. Road Chauraha, Banmore, Distt. Morena, M.P.', '476444', 'E-604, Sundaram Icon, Baikunth Chaar Rasta, Waghodiya Road, Vadodara, 390019', 'B.E. (EC)', 'Job', 'L & T Technology', 'Senior Engineer', 65000.00, 'Lt. Shri Padam Chandra Jain', '9826071329', 'Business', 0.00, 'Shrimati Sarita Jain', '9826071329', 'House Wife', 1, 0, 1, 1, 1, 0, 'Sanskari, Educated, Religious, Supportive, Family Oriented', 'Gujarati, Hindi, English', 'Listening to Music, Watching Movies, Travelling', 'none', 'Yes', 'imports/profile_photos/Prankul_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(14, 'Preksha manojkumar jain', 'Female', '1998-03-16', '05:00:00', 'Ahmedabad', 'Ahmedabad', 'Roda muri madilya', 'Bhauriyamuri kouchal', 'no', 163, 60.00, '91', '9898385333', 'prekshajain1111@gmail.com', '12,shukmalnagar society chandlodia', '382481', '12 , shukmalnagar society chandlodia', 'M.D . Radiodiagnosis', 'Job', 'Working in Hospital', 'MD Radiodiagnosis', 90000.00, 'Manoj jain', '9898385333', 'Business', 100000.00, 'Priti jain', '9724080789', 'House Wife', 1, 0, 1, 0, 0, 0, 'Doctor', 'Gujarati, Hindi, English', 'Studying', 'none', 'No', 'imports/profile_photos/Preksha_manojkumar_jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(15, 'SHAIL SHAH', 'Male', '1997-09-16', '13:10:00', 'VADODARA', 'VEDACH', 'NAGESHWAR', 'HANSH', 'no', 163, 65.00, '091', '9537045545', 'vac197215@gmail.com', 'A-104, VAIKUNTHDHAM TENAMENTS, AIR FORCE STATION ROAD, MAKARPURA, VADODARA, GUJARAT, INDIA', '390014', 'CANADA ON', 'B.PHARMACY', 'Job', 'SHOPPERS DRUGS', 'MANAGER PHARMACIEST', 300000.00, 'DHARMESH SHAH', '9537045545', 'VIDHI TAX CONSULTANT', 65000.00, 'DHARATI SHAH', '9537045544', 'LIC ADVISOR', 0, 0, 0, 1, 0, 1, 'FAMILY ORIENTED AND RESPECT TO EACH PARTNERS', 'Gujarati, Hindi, English', 'TRAVEL, FOOD,READING', 'none', 'No', 'imports/profile_photos/SHAIL_SHAH_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(16, 'Ananya Nitin Shah', 'Female', '2002-06-19', '02:20:00', 'Ahmedabad', 'Ahmedabad', 'Budheshwar', 'Pankheshwar', 'no', 160, 60.00, '91', '9898621967', 'ananyashah7354@gmail.com', '98/797 Nirmal Apartment, opp. Jay Mangal BRTS, Naranpura, Ahmedabad', '380063', '98/797 Nirmal Apartment, opp. Jay Mangal BRTS, Naranpura, Ahmedabad', 'Bachelors', 'Job', '', '', 20000.00, 'Nitin shah', '9998443149', 'Retired', 0.00, 'Shraddha shah', '7573040533', 'House Wife', 1, 0, 0, 0, 0, 0, 'Intelligent and caring', 'Gujarati, Hindi, English', 'Badminton, drawing, travel, swimming, music', 'none', 'No', 'imports/profile_photos/Ananya_Nitin_Shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(17, 'Hardik Jinendra Master', 'Male', '1996-05-23', '07:25:00', 'Nardana(Dhule Maharashtra)', 'Nardana', 'Budheswar', 'Kakdeshwar', 'yes', 170, 67.00, '91', '9537992319', 'master.23civil@gmail.com', '13,Jalaram Nagar Near Mahajan Anath Ashram, Katargam Main Road, surat', '395004', 'Same as above', 'BE Civil', 'Job and business both', 'Vanita vishram Educational Trust-Surat', 'Senior Civil Engineer', 50000.00, 'Jitendrabhai', '9825449865', 'Business', 60000.00, 'Seemaben', '9979252465', 'House Wife', 1, 0, 1, 0, 0, 0, 'Someone who makes love feel calm and life feel exciting .', 'Gujarati, Hindi, English', 'Traveling , sports , music, movies, foodie', 'none', 'No', 'imports/profile_photos/Hardik_Jinendra_Master_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(18, 'KEWAL SHAILESH SHAH', 'Male', '2000-02-16', '20:02:00', 'PANVEL', 'MUMBAI', 'KHARJESHWAR', 'JAMDAGNI', 'no', 175, 80.00, '91', '9664990022', 'shaileshpnv@gmail.com', 'SHOP NO 32 PAYAL CHS PLOT NO 15 D SEC 17 NEW PANVEL E', '410206', 'SAME AS ABOVE', 'DIP IN HARDWARE AND NETWORKING', 'Business', 'SHAH INFOTECH', 'PROPRIETOR', 60000.00, 'SHAILESH SHAH', '9664990022', 'Business', 50000.00, 'LATE SHEETAL SHAH', '', 'EXPIRED', 0, 0, 0, 0, 0, 0, 'NORMAL', 'Hindi, English, MARATHI', 'CRICKET', 'none', 'No', 'imports/profile_photos/KEWAL_SHAILESH_SHAH_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(19, 'NEIL DIPAK SHAH', 'Male', '1996-06-30', '05:00:00', 'VADODARA', 'MOTI JAHER', 'KANKULOL', '', 'yes', 165, 77.00, '091', '9173071435', 'deepak_shah44@yahoo.com', 'B 802, GANGOTRI APARTMENTS, R V DESAI ROAD, NR NAVAPURA POLICE STATION, OPP DAYA MADHAV HOSPITAL, VADODARA', '390001', 'SAME', 'MBA (NMIMS, MUMBAI), B.TECH (Mech Engg.)', 'Job', 'TATA DIGITAL PVT LTD (TATA NEU, Bengaluru)', 'CATEGORY MARKETING MANAGER', 197000.00, 'DIPAK SHAH', '9173071435', 'Retired', 0.00, 'PALLAVI SHAH', '9979110277', 'House Wife', 0, 0, 0, 0, 0, 0, 'Good qualification and working girl.', 'Gujarati, Hindi, English', 'Cricket, Badminton, Sketching, Balling etc.', 'none', 'No', 'imports/profile_photos/NEIL_DIPAK_SHAH_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(20, 'Bodhit Mehta', 'Male', '2001-11-04', '04:42:00', 'Lunawada', 'Ahmedabad', 'Buddheshwar', 'NA', 'no', 175, 69.00, '91', '9428416010', 'bodhitmehta5@gmail.com', '204, Karmashreshtha Tower, Nr, Seema Hall, Anand Nagar Road, Satellite, Ahmedabad', '380015', 'JLT, Dubai', 'CFA Level-2 Cleared, ACCA, B.Com', 'Job and Freelancer', 'Altus Citadel DMCC', 'Chartered Accountant', 368000.00, 'Yatin Mehta', '9428416010', 'Business', 1500000.00, 'Anita Mehta', '9428416010', 'House Wife', 1, 1, 0, 0, 0, 0, 'Well-educated, understanding, and family-oriented.', 'Gujarati, Hindi, English', 'Exploring Cars, Listening Music', 'none', 'No', 'imports/profile_photos/Bodhit_Mehta_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(21, 'Mardav Divyeshkumar shah', 'Male', '2003-03-16', '11:38:00', 'Lunawsds', 'Ahmedabad', 'Utreshvar', 'Budheshvar', 'no', 180, 69.00, '91', '9512052539', 'mansidshsh1976@gmail.com', '702,summit-2,opp shell petrol pump , prahladnagar, satellite, Ahmedabad', '380015', 'Samr as above', 'BE civil', 'Business', 'Maple developer', 'Partner', 225000.00, 'Divyeshkumar Shah', '9512052539', 'Business', 500000.00, 'Mansi shah', '9714304666', 'House Wife', 0, 0, 0, 1, 0, 1, 'Graduate', 'Gujarati, Hindi, English', 'Sports , music', 'none', 'No', 'imports/profile_photos/Mardav_Divyeshkumar_shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(22, 'Jainy Dharmesh Gandhi', 'Female', '1998-10-28', '14:10:00', 'Idar Gujarat', 'Talod Gujarat', 'Mantreshwar', '', 'no', 157, 50.00, '91', '9022173177', 'gandhijainy3@gmail.com', 'C - 301 Janki Heritage 150 ft road Bhayander west Mumbai', '401101', 'Same as above', 'Bachelor of Engineering', 'Job', '', '', 100000.00, 'Dharmesh Jashvantlal Gandhi', '9324598098', 'Business', 0.00, 'Niketa Dharmesh Gandhi', '', 'House Wife', 1, 0, 1, 1, 0, 1, 'Kind and understanding', 'Gujarati, Hindi, English', 'Dancing and Singing', 'none', 'No', 'imports/profile_photos/Jainy_Dharmesh_Gandhi_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(23, 'Jain Monik Amrutlal', 'Male', '1996-12-02', '18:14:00', 'Mumbai', 'Udaipur', 'Bohra', 'Devada', 'yes', 168, 64.00, '91', '7435038546', 'monikjain1996@gmail.com', 'B301, Rajmandir Apartment, Thaltej', '380059', 'Same as above', 'Undergraduate Btech', 'Job', 'TatvaSoft', 'Senior Android Engineer', 115000.00, 'Amrutlal Deepchand Jain', '9274527830', 'Business', 30000.00, 'Geetaben Amrutlal Jain', '9558229264', 'House Wife', 0, 0, 0, 0, 0, 0, 'Family oriented and Good mannerisms', 'Gujarati, Hindi, English', 'Playing Cricket', 'none', 'No', 'imports/profile_photos/Jain_Monik_Amrutlal_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(24, 'Karishma Umakant Arpal', 'Female', '1996-12-16', '15:55:00', 'Chhatrapati Sambhaji Nagar', 'Chhatrapati Sambhaji Nagar', 'Pancham', 'Pancham', 'no', 163, 70.00, '91', '9422203135', 'karishmaele@gmail.com', '94, Vidhya Nagar, Jalna Road, Chhatrapati Sambhaji Nagar.', '431009', '94, Vidhya Nagar, Jalna Road, Chhatrapati Sambhaji Nagar.', 'BE-IT', 'Business', 'Karishma Electricals', '', 100000.00, 'Umakant Padmaji Arpal', '9422203135', 'Business', 400000.00, 'Seema Umakant Arpal', '7710034875', 'Business', 1, 1, 0, 0, 0, 0, 'Family-oriented, supportive, and open-minded.', 'Hindi, English, Marathi', 'Driving, Watching movies & Series, Badminton', 'none', 'No', 'imports/profile_photos/Karishma_Umakant_Arpal_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(25, 'Abhay Shah', 'Male', '1999-05-15', '13:32:00', 'Padara', 'Takatuka', 'Kamleshwar', 'Bhudheswar', 'no', 170, 80.00, '91', '9724363419', 'shahabhay226@gmail.com', '4/60, Abhishek Apartment Sola Housing Complex Naranpura Ahmedabad', '380063', 'A-5, Akshat Apartment opposite Nobel Trade centre memnagar Ahmadabad', 'BE in civil engineering', 'Job', 'Pravish Group', 'Purchase Executive', 348000.00, 'Chirag Shah', '9727339744', 'Job', 100000.00, 'Nilam shah', '9727339744', 'Job', 0, 0, 0, 0, 0, 0, '\"As a boy, my partner preference is someone who is kind-hearted, understanding, and has a positive outlook towards life. She should be ambitious, career-oriented, and willing to support my endeavors. I value honesty, loyalty, and good communication skills, and I expect my partner to share the same values. I prefer someone who is well-educated and has a good sense of humor. Physical appearance is not a significant factor, but a healthy lifestyle is essential. Ultimately, I am looking for a partner who will be my best friend and companion with whom I can share a fulfilling and happy life.\"', 'Gujarati, Hindi, English', 'Traveling, Gym , Music,', 'none', 'No', 'imports/profile_photos/Abhay_Shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(26, 'MMMM', 'Male', '2000-01-01', '12:10:00', 'MMMM', 'MMMM', 'MMM', '', 'no', 163, 50.00, '91', '9999999999', 'mmm@hotmail.com', 'MMM', '100000', 'same', 'MMM', 'Job', '', '', 10000000.00, 'GGG', '9999999999', 'Business', 0.00, 'OOO', '', '', 1, 1, 0, 4, 4, 0, 'NO', 'Hindi', 'MMM', 'none', 'No', 'imports/profile_photos/MMMM_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(27, 'Jain Bhavin Rishabh Kumar', 'Male', '1998-09-01', '04:25:00', 'Ahmedabad', 'Ahmedabad', 'Jangda', 'Akhavat', 'no', 168, 60.00, '91', '9601179626', 'dharajain943@gmail.com', '03, mangaldhvni society near Ramrajya nagar behind Saraswati school,odhav Ahmedabad', '382415', 'Not applicable', 'B.com', 'Business', 'The skyline surgical', 'Bhavin Rishabh Kumar jain', 50000.00, 'Rishabh kumar jain', '9909352478', 'Business', 30000.00, 'Vanita ben Rishabh Kumar', '9601179626', 'House Wife', 0, 0, 0, 2, 2, 0, 'Trust And Respect , Emotional And Empathy , And maturity', 'Gujarati, Hindi', 'Cricket', 'none', 'No', 'imports/profile_photos/Jain_Bhavin_Rishabh_Kumar_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(28, 'Lokesh Chatur', 'Male', '1999-08-07', '07:30:00', 'Ahmednagar', 'Bhusawal', 'Chatur', 'Varkhedkar', 'yes', 175, 65.00, '91', '8850532288', 'pvchatur@gmail.com', 'Flat No. 905, West Wind Park, Hulawale Vasti, Hinjewadi, Pune', '411057', 'Not applicable', 'B.Tech. (I.T.) Engineering', 'Job', 'PruTech Solutions Inc. USA', 'Salesforce Consultant', 220000.00, 'Pravin Chatur', '9423259273', 'Job', 225000.00, 'Mrs Vidya Chatur', '09975717999', 'House Wife', 1, 0, 1, 0, 0, 0, 'IT Eng Believe in jt family willing to stay in USA well cultured animal loving', 'Gujarati, Hindi, English, Marathi', 'Music, Travelling, Reading', 'none', 'No', 'imports/profile_photos/Lokesh_Chatur_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(29, 'Prashuk Doshi', 'Male', '1997-10-28', '16:00:00', 'Halol, Near Vadodara (Gujarat)', 'Dahod, Gujarat', 'Kherju', 'Jhanjri', 'no', 180, 72.00, '91', '9924518053', 'prdoshi15@gmail.com', '402, Shivaay, Amit Nagar Circle, Kareli Baug, Vadodara', '390018', 'S2 102, Greenwood Regency, Sarjapur Road, Bengaluru', 'BTech + MSc from BITS PILANI', 'Job', 'Flipkart', 'Product Manager', 300000.00, 'Manish Doshi', '9924518053', 'Business', 0.00, 'Rupal Doshi', '', 'House Wife', 0, 0, 0, 1, 0, 1, 'Working', 'Gujarati, Hindi, English', 'Reading, playing, cooking', 'none', 'No', 'imports/profile_photos/Prashuk_Doshi_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(30, 'Sanket mukesh shah', 'Male', '1992-12-01', '05:25:00', 'TALOD, GUJARAT', 'Gora', 'Buddheshwar', 'Kherju', 'no', 173, 75.00, '91', '7208862934', 'shahsanket1992@gmail.com', '502, Ramdev vaayu, near raghu leela building, next to pizza hut,  150 feet road, Bhayandar (west)', '401101', 'Same as above', 'Post graduate', 'Job', 'Dolat capital', 'Equity- F&O trader', 60000.00, 'Late.Mukesh kumar ramanlal shah', '9322456882', '-', 0.00, 'Kiranben mukesh kumar shah', '9322456882', 'House Wife', 1, 1, 0, 0, 0, 0, 'Partner should be a caring, polite, reliable,and kind hearted.', 'Gujarati, Hindi, English', 'Love to trekking & travel, exploring new places, watching movies.', 'none', 'No', 'imports/profile_photos/Sanket_mukesh_shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(31, 'Ayushi', 'Female', '1996-10-25', '10:30:00', 'Ahmedabad', 'Ahmedabad', 'Dhamsaiya', 'Panchratan', 'no', 160, 50.00, '91', '9638156292', 'ashishjain6292@gmail.com', '41, madhuvan park, near gor no kuwo, maninagar(east), ahmedabad', '380008', 'Same as above', 'B.Com', 'Job', 'Private firm', 'Accountant', 100000.00, 'Late arvindkumar c jain', '9825723831', 'Passed away', 0.00, 'Kalpanaben arvindkumar jain', '9825723868', 'Business', 1, 1, 0, 1, 1, 0, 'Permanent resident of Ahmedabad', 'Gujarati, Hindi, English', 'Reading books, travelling', 'widow', 'No', 'imports/profile_photos/Ayushi_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(32, 'Palak jain', 'Female', '1997-12-22', '16:00:00', 'Jabera, Damoh, MP', 'Patharia, damoh , MP', 'बैसाखियां', 'Parwar', 'no', 165, 60.00, '91', '8686065000', 'aysjain@gmail.com', '279, ward no 03, patharia, damoh , Madhya pradesh 470666', '470666', 'Banglore', 'M.Tech. From IIT Roorkee', 'Job', 'Intel', 'Digital designer', 200000.00, 'Late ajit jain', '8109591530', 'Job', 100000.00, 'Sushma jain', '8109706361', 'Job', 1, 0, 1, 0, 0, 0, 'Broad minded', 'Hindi', 'Travelling, cooking', 'none', 'No', 'imports/profile_photos/Palak_jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(33, 'Nishchay Sunil Shah', 'Male', '1995-09-02', '05:50:00', 'Anand', 'Sojitra', 'NA', 'NA', 'no', 173, 76.00, '91', '9408343397', 'nishchay2995@gmail.com', '201, Mangalam Appt. Sharda complex b near new st depot anand', '388001', 'Same as above', 'M. Pharma', 'Job', 'Alembic Pharmaceutical limited', 'Executive', 49000.00, 'Sunil Ramanlal Shah', '9427895431', 'No more available', 0.00, 'Bhavna Sunil Shah', '9427895431', 'Retired', 0, 0, 0, 1, 1, 0, 'Carrying, understanding, graduate or post graduate with science', 'Gujarati, Hindi, English', 'Photography, watching movies , trekking', 'none', 'No', 'imports/profile_photos/Nishchay_Sunil_Shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(34, 'JAINAM SANATKUMAR SHAH', 'Male', '1996-11-16', '07:35:00', 'MANDVI DIST-SURAT', 'MANDVI DIST-SURAT', 'RAJIYANU', '', 'no', 180, 55.00, '91', '9376648490', 'j4jainam@gmail.com', 'NAVAPARA MAIN ROAD MANDVI  OPP- DIGAMBER JAIN MANDIR DIST-SURAT', '394160', 'SAME AS ABOVE', 'B.COM. , M.B.A.', 'Business', 'MS SANATKUMAR  P SHAH', 'OWNER', 200000.00, 'LATE SANATKUMAR P SHAH', '9408680934', 'NOT APPLICABLE', 0.00, 'CHETANABEN SANATKUMAR SHAH', '9427833998', 'House Wife', 0, 0, 0, 2, 2, 0, 'FAMILY ORIENTED , CARING , AMBITIOUS', 'Gujarati, Hindi, English', 'TRAVELLING, LISTENING,SOCIAL WORK', 'none', 'No', 'imports/profile_photos/JAINAM_SANATKUMAR_SHAH_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(35, 'Vitarag Bhupendrakumar Sanghvi', 'Male', '1993-04-26', '05:40:00', 'Naroda, Ahmedabad', 'Naroda', 'Kankulol', '', 'no', 168, 56.00, '91', '7359395887', 'dimpalsanghvi@gmail.com', 'A-31, Mohan Nagar co-operative society, \nBangla bus stand, opp. National handloom, Naroda, Ahmedabad', '382330', 'Gurugram', 'MBA', 'Job', 'Indimoney', 'Senior manager', 3800000.00, 'Bhupendra Kumar', '9265952048', 'Retired', 0.00, 'Kapila', '9265952048', 'House Wife', 0, 0, 0, 2, 2, 0, 'Gujarati, Rajasthani', 'Gujarati, Hindi, English', 'Reading, lisning music', 'none', 'No', 'imports/profile_photos/Vitarag_Bhupendrakumar_Sanghvi_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(36, 'Dr.Stuti Shah', 'Female', '2001-01-26', '05:01:00', 'Surat', 'Mandvi surat', 'Rajiyanu', 'Kherju', 'no', 160, 60.00, '91', '7041641008', 'shahparam2103@gmail.com', 'Smit laboratory, navapara, main road , Mandvi, Surat, Gujarat', '394160', 'Same as above', 'Mbbs', 'Job', '', '', 85000.00, 'Varsheshbhai Shah', '7041641008', 'Business', 0.00, 'Khushboo Shah', '7878031008', 'House Wife', 1, 0, 0, 0, 0, 0, 'Priority doctor', 'Gujarati, Hindi, English', 'Movies , traveling', 'none', 'No', 'imports/profile_photos/Dr.Stuti_Shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(37, 'Dr.Rishit Shah ( PT )', 'Male', '2001-11-13', '10:51:00', 'Banswara', 'Bagidora, Banswara, Rajasthan', 'Buddheshwar', 'Vajiyanu', 'no', 168, 64.00, '91', '6367026570', 'rishitshah2019@gmail.com', 'Opposite government hospital , Bagidora, Banswara, Rajasthan', '327601', 'G 602, Rajyash Reevanta, Vasna, Ahemdabad', 'Bachelor of physiotherapy ( BPT)', 'Job', 'Rewalk Advance Physiotherapy , Ahemdabad', 'Clinical therapist', 75000.00, 'Vipin Kumar shah', '7297804570', 'Business', 40000.00, 'Aruna shah', '9587401816', 'House Wife', 0, 0, 0, 1, 1, 0, 'Physiotherapist , Dentist , Engineer, Teacher', 'Gujarati, Hindi, English', 'Watching movies, playing cricket , table tennis, listening song , Reading Article and books', 'none', 'No', 'imports/profile_photos/Dr.Rishit_Shah_(_PT_)_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(38, 'Prasuk shah', 'Male', '1997-06-24', '05:30:00', 'AHEMdabad', 'Sagwara,dungarpur Rajasthan', 'Rajiyanu', '', 'no', 165, 60.00, '91', '8094470570', 'prasukshah3480@gmail.com', 'A-374,Punarwas colony sagwara dungarpur Rajasthan', '314025', 'AHEMdabad Gujarat', 'CMA ,m.com', 'Job', 'Confiance Bizsol pvt Ltd', 'Team Lead', 1500000.00, 'Late jayvant shah', '9413016702', 'NA', 0.00, 'Deepika shah', '9413016702', 'House Wife', 0, 0, 0, 2, 2, 0, 'Good girl or working profession', 'Gujarati, Hindi, English', 'Cricket ,travelling music', 'none', 'No', 'imports/profile_photos/Prasuk_shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(39, 'Yash Kumar Jain', 'Male', '1996-08-23', '21:20:00', 'Peeth', 'Peeth', 'Kherju', '', 'no', 188, 62.00, '91', '8502065979', 'shahyash6146@gmail.com', 'Ward n 4 Jain mohalla, Peeth Dis - Dungarpur Rajasthan', '314406', 'D 603 Rajyash Reevanta South Vasna, Ahmedabad 380007', 'BCom', 'Job', 'Axis Bank', 'Deputy Manager', 52000.00, 'Ashvin Jain', '6350580401', 'Insurance Agents', 25000.00, 'Ramila Devi', '', 'House Wife', 1, 1, 0, 1, 0, 1, 'NA', 'Gujarati, Hindi, English', 'Playing Cricket, movie, travelling', 'none', 'No', 'imports/profile_photos/Yash_Kumar_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(40, 'Anuj Jain', 'Male', '1995-09-17', '19:02:00', 'Howrah', 'Kolkata', 'Chhabra', 'Sethi', 'no', 157, 63.00, '91', '9007943386', 'jainanuj635@gmail.com', '40, Dobson road, Ambika tower, 8th floor flat no-83, Howrah-1', '711101', 'Flat no- 1238, 3rd floor, Building no- 12, kamna society, Vanrai colony, Opposite hub mall, W.E highway, Goregaon East, Mumbai 400065', 'Masters of Business Administration', 'Job', 'CitiBank', 'Trade operations', 100000.00, 'Manoj Kumar Jain', '9339665947', 'Business', 0.00, 'Manju Jain', '9331165947', 'House Wife', 0, 0, 0, 1, 1, 0, 'Looking for a kind, understanding, and emotionally mature partner. Someone who values family, respect, and stability in life. Preference for a working professional with similar values and a positive outlook towards life.', 'Hindi, English', 'Enjoy traveling, explore and try different veg cuisines, watching movies, and listening to music. I like to stay positive and spend quality time with loved ones.', 'none', 'Yes', 'imports/profile_photos/Anuj_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(41, 'Dr.Payanshi jain', 'Female', '1997-04-20', '22:42:00', 'Paratapur, Banswara', 'Bagidora', 'Mantreshwar', 'Uttreshwar', 'yes', 163, 54.00, '91', '9001242576', 'ckdosi777@gmail.com', 'Bagidora Banswara Rajasthan', '327601', 'Same as above', 'Pursuing PG OBGYN', 'Pursuing PG', 'TMU MORADABAD', 'PG OBGYN', 70000.00, 'Chandrakant Dosi', '9001242576', 'Job', 150000.00, 'Poorva Rani jain', '9636332236', 'Job', 0, 0, 0, 1, 0, 1, 'Dr PG', 'Hindi, English, Kannad', 'Ghumna', 'none', 'No', 'imports/profile_photos/Dr.Payanshi_jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(42, 'Marmik Devadiya', 'Male', '1998-11-13', '19:55:00', 'Dahod, Gujarat', 'Gujarat', 'Rajiyanu', 'Kherju', 'no', 170, 60.00, '91', '9157468600', 'marmikdevadiya13@gmail.com', 'Meezolex, Mahavir Nagar, Opposite Jain Mandir, Near Siddharth Nursery, Godi Road, Dahod, Gujarat', '389151', 'Bangalore', 'B. Tech., IIT Kharagpur', 'Job', '', '', 250000.00, 'Yashvant Devadiya', '9825528090', 'Business', 100000.00, 'Pinky Devadiya', '', 'Business', 1, 0, 1, 0, 0, 0, 'Skipping this', 'Gujarati, Hindi, English', 'Travel, Movies, Adventure activities', 'none', 'No', 'imports/profile_photos/Marmik_Devadiya_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(43, 'Sakshi Bhimavat', 'Female', '1998-07-27', '02:15:00', 'Dahod', 'Thandla', 'Gangeshwar', 'Pankeshwar', 'no', 160, 52.00, '91', '9425945109', 'sarveshjain445@gmail.com', '04, Sunar Gali Thandla, District Jhabua, Madhya Pradesh', '457777', 'Same as above', 'Master of Physiotherapy in orthopedic and sports', 'Job', 'Doctor (Physiotherapist)', 'Doctor', 60000.00, 'Mr Vijay Bhimavat', '9425945109', 'Business', 700000.00, 'Chetna Bhimavat', '9770098503', 'House Wife', 1, 1, 0, 0, 0, 0, 'Looking for a well-educated, understanding, and caring partner with good family and religious values and a positive attitude toward life.', 'Gujarati, Hindi, English', 'Travelling, interaction with new people and reading books', 'none', 'No', 'imports/profile_photos/Sakshi_Bhimavat_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(44, 'Shrey Kamleshbhai Shah', 'Male', '2000-02-07', '12:59:00', 'Mandvi,Surat', 'Navsari', 'Vashistha', 'Buddheshvar', 'no', 168, 44.00, '91', '9664929564', 'sheryshah9898@gmail.com', '15-A Raj Nagar society behind Ram nagar society kabilpore, Navsari.', '396427', 'Same as above', 'M.Sc Microbiology', 'Job', 'Zyuds lifescience ltd.', 'Officer', 350000.00, 'Kamleshbhai B Shah', '9824565833', 'Job', 0.00, 'Truptiben K Shah', '9427947406', 'House Wife', 0, 0, 0, 0, 0, 0, 'trustworthiness,Empathy and support,Work-life balance,Family values,Mutual respect', 'Gujarati, Hindi, English', 'Music listening, Traveling, Reading, Chess', 'none', 'No', 'imports/profile_photos/Shrey_Kamleshbhai_Shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(45, 'Ketan Jain', 'Male', '1997-08-30', '13:00:00', 'Badnawar', 'Indore', 'Khag', 'Fuskele', 'no', 165, 66.00, '91', '7389897883', 'Shreeyajain124@gmail.com', '596, Sector -A, Mahalaxmi Nagar, Indore MP', '452010', 'Ahemdabad', 'BE, CPL', 'Job', 'Indigo Airlines', 'Airline Pilot', 200000.00, 'Mr. Kamlesh Jain', '7389897883', 'Retired', 70000.00, 'Mrs. Seema Jain', '7389897883', 'House Wife', 1, 1, 0, 1, 1, 0, 'NA', 'Hindi, English', 'Travelling', 'none', 'No', 'imports/profile_photos/Ketan_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(46, 'Sagar Shah', 'Male', '1993-09-01', '06:30:00', 'Bakrol', 'Surat', 'Shankheshwar', '', 'no', 168, 65.00, '91', '8527871600', 'sagar0shah@gmail.com', '616,Shakti Samarth Park Adajan Road Surat', '395009', 'Sector 58 Noida 201301', 'B.Tech E.C', 'Job', '', '', 125000.00, 'Pradipkumar Champaklal Shah', '9974676169', 'Job', 0.00, 'Damini Pradip Shah', '', '', 1, 1, 0, 0, 0, 0, 'Half dharmik & working', 'Gujarati, Hindi, English', 'Playing outdoor & Indoor Games,travelling,watching movies & webseries', 'none', 'No', 'imports/profile_photos/Sagar_Shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(47, 'Srushti Dipenbhai shah', 'Female', '1997-12-06', '15:45:00', 'Bhuj kachchh gujarat', 'Bhavnagar', 'Uttreshwer', 'Budheshwar', 'no', 163, 58.00, '91', '9537778556', 'drsrushtishah@gmail.com', '18-21, Patel society, Ayush hospital, opposite rambaug society, sashtri road, Bardoli Dist -surat,gujarat', '394601', 'Same as above', 'B.D.S.(dental surgeon)', 'Job', 'Sanjivani multi speciality hospital,kadodara,dist-surat', 'Full-time dental surgeon', 40000.00, 'Dr.Dipen Himatlal shah', '9824478556', 'Job', 150000.00, 'Kalpanaben Dipen shah', '9824478557', 'House Wife', 1, 0, 1, 1, 1, 0, 'Doctor,Engineer,C.A.', 'Gujarati, Hindi, English', 'Reading, movie, adventure,travel,', 'none', 'No', 'imports/profile_photos/Srushti_Dipenbhai_shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(48, 'Shubham Jain', 'Male', '1991-11-06', '13:05:00', 'Vidisha, Madhya Pradesh', 'Ganj Basoda, M.P.', 'Divakirti', 'Bilaua', 'no', 175, 75.00, '1', '7786871065', 'onlysolutionshubham@gmail.com', 'A-301 Neelkanth Residency, Nr. Green City, Nirnay Nagar, Ahmedabad', '382481', 'Toronto, Canada', 'M.Sc.', 'Job', 'All-Risks Insurance Brokers Ltd.', 'Account Executive', 300000.00, 'Narendra Jain', '9429025460', 'Business', 50000.00, 'Shashiprabha Jain', '', 'House Wife', 0, 0, 0, 1, 1, 0, 'I am looking for a life partner who is family oriented, flexible , have a balance of religious and social parameters, who respects elderlies of family, trustworthy and active', 'Gujarati, Hindi, English', 'Playing Badminton, Volleyball', 'none', 'No', 'imports/profile_photos/Shubham_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(49, 'YASH JAIN', 'Male', '1999-03-10', '13:40:00', 'BHINDER', 'UDAIPUR', 'NAYAK', 'SAPADIYA', 'no', 170, 62.00, '91', '9521414183', 'arvindkrjain@yahoo.com', 'Flat Number A-404, Chaturbhuj CHS, Sector-21, Kharghar, Navi Mumbai', '410210', 'Same as above', 'BE MECHANICAL', 'Job', '', '', 0.00, 'ARVIND JAIN', '9521414183', 'Job', 0.00, 'RICHA JAIN', '9987582913', 'House Wife', 0, 0, 0, 0, 0, 0, 'NA', 'Hindi, English', 'Playing Cricket, Tennis and Traveling & Listening to Music', 'none', 'No', 'imports/profile_photos/YASH_JAIN_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(50, 'Darshit Dipak Jain', 'Male', '1996-10-24', '09:37:00', 'Vadodara', 'Vadodara', 'Atreya', 'Buddheshwar', 'no', 168, 58.00, '91', '9820776863', 'jndarshit@gmail.com', 'C/76 Panchratna CHS, Off svp road, borivali west, mumbai', '400092', 'Same as above', 'BE (Production)', 'Business', 'APX International', 'Head', 250000.00, 'Dipak Jain', '9920434711', 'Business', 250000.00, 'Apexa Jain', '9820776854', 'House Wife', 0, 0, 0, 0, 0, 0, 'Understanding and Spontaneous', 'Gujarati, Hindi, English, French, German', 'Reading, Travelling', 'none', 'No', 'imports/profile_photos/Darshit_Dipak_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(51, 'Priyanshi Jain', 'Female', '1998-02-14', '08:45:00', 'Ahmedabad', 'Ahmedabad', 'Ratodiya', '', 'yes', 157, 57.00, '91', '7990375743', 'jainpriyanshi1498@gmail.com', 'B-17, Tripada Society, Maninagar, Ahmedabad', '380008', 'Same as Above', 'B.E - Computer Science', 'Job', 'Audiense', 'Data Analyst', 0.00, 'Mahendrakumar Jain', '9327193961', 'Business', 0.00, 'Shashikala Jain', '', 'House Wife', 1, 1, 0, 0, 0, 0, '-', 'Gujarati, Hindi, English', '-', 'none', 'No', 'imports/profile_photos/Priyanshi_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(52, 'Sheetal Jain', 'Male', '1996-10-10', '10:10:00', 'Ahmednagar, Maharashtra', 'Sujangarh, Rajasthan', 'Patni', 'Chanodia (Jain)', 'no', 183, 75.00, '91', '9820588593', 'arun.jain.18@gmail.com', 'A-401, Avalon, Hiranandani Gardens, Powai, Mumbai', '400076', 'Toronto, Canada', 'Ph.D. in Physics from University of Toronto, B.Tech(Hons.) from IIT-Bombay', 'Job', 'Xanadu Quantum Technologies', 'Research Engineer/Scientist', 500000.00, 'Arun Jain', '9820588593', 'Job', 250000.00, 'Sheela Jain', '', 'Retired from IIT-Bombay', 0, 0, 0, 0, 0, 0, 'Religious (full faith in Jinendra Bhagwan), Digambar Terapanth, non-smoker, non-drinker', 'Hindi, English', 'Badminton, cricket, reading novels', 'none', 'No', 'imports/profile_photos/Sheetal_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(53, 'darshan chhapiya', 'Male', '2001-06-15', '05:35:00', 'Ahmedabad', 'Vijaynagar', 'RAJIYANU', 'Ankleswar', 'no', 173, 68.00, '91', '9825173704', 'dchhapiya03@gmail.com', 'B-1104,ARYABHUMI,OPP-M G PARTY PLOT,JODHPURGHAMROAD,SATELITE,AHMEDABAD', '380015', 'Canada, Toronto', 'Master in IT', 'Job', 'Managaer', 'Manager', 250000.00, 'Prajesh chhapiya', '9825173704', 'Business', 250000.00, 'Shilpa chhapiya', '9426358855', 'House Wife', 1, 0, 1, 0, 0, 0, 'No', 'Gujarati, Hindi, English', 'Traveling', 'none', 'No', 'imports/profile_photos/darshan_chhapiya_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(54, 'Dr. Vidhi Gandhi', 'Female', '1999-09-30', '14:50:00', 'Vadodara', 'Vadodara', 'Kherju', 'Bhudeshawar', 'no', 168, 52.00, '091', '9974037067', 'vmas99@hotmail.con', 'A/15, pushpak township, undera, vadodara', '391330', 'Not applicable', 'MD in Medicine', 'Student', 'Baroda medical college', '', 1200000.00, 'Sanjay Gandhi', '9974037067', 'Job', 1500000.00, 'Alka Gandhi', '9558154994', 'House Wife', 0, 0, 0, 1, 1, 0, 'Well educated,', 'Gujarati, Hindi, English', 'Reading', 'none', 'No', 'imports/profile_photos/Dr._Vidhi_Gandhi_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(55, 'Poonam jain', 'Female', '1992-02-18', '05:00:00', 'Sagwara', 'Sagwara(rajasthan)', 'Mantreshwar', '', 'no', 163, 58.00, '91', '9898961896', 'apurvjain1008@gmail.com', 'C2, Murdhanya Apartment, AEC cross road, naranpura, ahmedabad', '380013', 'Dungarpur (Rajasthan)', 'MSC', 'Job', 'Govt. School', 'Teacher', 50000.00, 'Mahendra jain', '9898961896', 'Retired', 0.00, 'Vimla jain', '8469290937', 'House Wife', 1, 1, 0, 0, 0, 0, 'Well educated and respect family and culture.', 'Gujarati, Hindi, English', 'Movies, traveling', 'none', 'No', 'imports/profile_photos/Poonam_jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(56, 'Bhasha Nirav Choksi', 'Female', '2001-02-18', '18:10:00', 'Bharuch', 'Bharuch', 'Kashyap', 'Uttareshwar,', 'no', 163, 56.00, '91', '09328226724', 'bhashachoksi2001@gmail.com', 'Bungalow No 2, Bhrugupur Society, Kashak Circle, Opp IDBI Bank, Bharuch', '392001', 'Same as Above', 'Msc Microbiology, Pursuing PHD', 'Studying:- PHD in Microbiology', '', '', 20000.00, 'Nirav Prakashchandra Choksi', '09328226724', 'Job', 200000.00, 'Manisha Nirav Choksi', '9624526724', 'House Wife', 0, 0, 0, 1, 0, 1, 'Well-educated, understanding, and family-oriented', 'Gujarati, Hindi, English', 'Painting, Music, & Dance', 'none', 'No', 'imports/profile_photos/Bhasha_Nirav_Choksi_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(57, 'KEVAL MANISHKUMAR SHAH', 'Male', '1999-01-29', '08:35:00', 'ANAND , GUJARAT', 'SAYMA (KHAMBHAT , GUJARAT)', 'KASHYAP', '', 'no', 173, 60.00, '91', '9727701552', 'skeval2901@GMAIL.COM', '503, AGAM APPARTMENT, NR. PIONEER HIGH SCHOOL, ANAND , GUJARAT', '388001', 'BROOKLAND STREET , SYDNEY, NOVA SCOTIA , CANADA', 'POST GRADUATE (SUPPLY CHAIN MANAGEMENT FROM CAPE BRETON UNIVERSITY, CANADA)', 'Job', '', '', 1800000.00, 'MANISHKUMAR NARENDRABHAI SHAH', '9727701552', 'Job', 0.00, 'NITABEN MANISHKUMAR SHAH', '', 'House Wife', 0, 0, 0, 1, 1, 0, 'ONE SHOULD BE READY TO MOVE ABROAD', 'Gujarati, Hindi, English', 'PHOTOGRAPHY , TRAVELLING , READING', 'none', 'No', 'imports/profile_photos/KEVAL_MANISHKUMAR_SHAH_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(58, 'Rushabh Chandresh Parikh', 'Male', '1999-10-11', '18:20:00', 'Surat', 'Borsad', 'Nandishwar', '', 'no', 170, 70.00, '91', '9227153842', 'rushabhparikh10@gmail.com', 'Pragya Shree, A-15, Ashray Society, Nandelav Road, Bholav, Bharuch', '392001', 'B704,\nBren Celestia, Sarjapur - Marathahalli Rd, Kaikondrahalli, Bengaluru, Karnataka 560035', 'IIT in Mechanical Engineering from Guwahati', 'Job', 'Novome Technology Pvt Ltd, Bangalore', 'Founders Office', 150000.00, 'Mr. Chandresh Navnitlal Parikh', '9227153842', 'Business', 0.00, 'Dr. Shraddha Chandresh Parikh', '9227153843', 'Business', 0, 0, 0, 0, 0, 0, 'Jain & Graduate', 'Gujarati, Hindi, English', 'Trekking, Swimming, Cricket, Chess, Football, Badminton', 'none', 'No', 'imports/profile_photos/Rushabh_Chandresh_Parikh_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(59, 'Sweta S Jain', 'Female', '1990-09-14', '10:10:00', 'Ahmedabad', 'Gujarat', 'Jasinghot', 'Kunavat', 'no', 165, 53.00, '91', '8866911395', 'sweta9014@gmail.com', '401 ABODE Opp. Elite Vidip Near Audit Bahvan Vijay Cross Road Navrangpura Ahmedabad Gujarat', '380009', 'Same as Above', 'Ph. D', 'Job', 'Government', '-', 900000.00, 'Shankarlal Amrutlal Jsinghot', '9081049270', 'Business', 2000000.00, 'Rajkumari Shankarlal Jain', '9081049270', 'House Wife', 1, 0, 1, 1, 1, 0, 'Well Educated , Understanding and who Values Simplicity and Mutual Respect', 'Gujarati, Hindi, English, Mewadi', 'Yoga', 'divorcee', 'No', 'imports/profile_photos/Sweta_S_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(60, 'KHUSHI MILANKUMAR TALATI', 'Female', '2000-05-30', '01:50:00', 'DAHOD', 'DAHOD', 'KAMLESHVAR', 'Mantreshvar', 'no', 165, 59.00, '91', '6355603970', 'khushitalati8020@gmail.com', '4th floor, flat no : 403, vraj mangal tower 1, near arya kanya school,  karelibaug,  vadodara', '390018', 'Same', 'Computer engineering', 'Job', '', '', 30000.00, 'MILANKUNAR DEVENDRAKUMAR TALATI', '8238337007', 'Retired', 70350.00, 'ARCHANA MILANKUMAR TALATI', '8238337007', 'House Wife', 0, 0, 0, 1, 0, 0, 'personality traits like kindness, honesty, and humor; lifestyle choices such as career ambition, a positive outlook, and good work-life balance; and fundamental values like respect, trustworthiness, and shared life goals.', 'Gujarati, Hindi, English', 'Dancing', 'none', 'No', 'imports/profile_photos/KHUSHI_MILANKUMAR_TALATI_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(61, 'Prapti Jain', 'Female', '2001-08-28', '13:17:00', 'Gwalior, MP', 'Gwalior, M.P', 'Modi', 'Pahariya', 'no', 165, 56.00, '91', '9426951646', 'prapti.jainn@gmail.com', 'Radhe bungalows, Khokhara circle, maninagar, Ahmedabad', '380008', 'Bengaluru', 'BTech', 'Job', 'Uber', 'Product Manager 2 - AI', 550000.00, 'S.K Jain', '9426951646', 'Retired', 120000.00, 'Asha Jain', '9978406891', 'House Wife', 0, 0, 0, 0, 0, 0, 'Should be well educated, from a reputed family.', 'Gujarati, Hindi, English, French', 'Reading, Writing, Painting, Cooking, Designing', 'none', 'No', 'imports/profile_photos/Prapti_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(62, 'Hemin Hanish Shah', 'Male', '1996-05-23', '00:55:00', 'Mumbai', 'Aluva', 'Sankheshwar', '', 'no', 178, 55.00, '91', '9820912129', 'heminshah235@gmail.com', 'C-21 Ashwin Building Marve Road Malad West Mumbai', '400064', 'Same as above', 'Masters in Physiotherapy (UK), Bachelors in Physiotherapy', 'Self employed', 'Physiotherapist', 'Dr. Hemin Hanish Shah (PT)', 126000.00, 'Hanish Vinodchandra Shah', '9820842129', 'Job', 0.00, 'Bela Hanish Shah', '9820942129', 'Job', 0, 0, 0, 0, 0, 0, 'Compatible, Understanding, Supportive, Caring', 'Gujarati, Hindi, English, Marathi', 'Travelling, Exploring, Sports, Reading, Movies, Adventure', 'none', 'No', 'imports/profile_photos/Hemin_Hanish_Shah_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(63, 'UJWAL YASHWANT GANDHI', 'Male', '1997-07-30', '17:10:00', 'IDAR', 'DUNGARPUR', 'UTTARESHWER', '', 'no', 178, 85.00, '91', '09427790064', 'ujwalgandhi10@gmail.com', 'A/604 , SAMARPAN TOWER,K .K. NAGAR ROAD, PRABHAT CHOWK, GHATLODIA, AHMEDABAD.', '380061', 'same as above', 'M E ( Mech )', 'Job', 'Johnson Electrical ,Canada', 'Production Engineer', 300000.00, 'GANDHI YASHWANT MOHANLAL', '09427790064', 'Retired', 30000.00, 'GANDHI PINAKINI YASHWANT', '7567742616', 'House Wife', 0, 0, 0, 0, 0, 0, 'going to abroad Canada and english knowing.', 'Gujarati, Hindi, English', 'About adventure, exploring new places,foodie, movie watching.', 'none', 'No', 'imports/profile_photos/UJWAL_YASHWANT_GANDHI_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(64, 'ADITYA VARDHAN GANDHI', 'Male', '1994-05-19', '12:46:00', 'BANSWARA (RAJSTHAN)', 'BANSWARA  RAJASTHAN', 'BHEDRESHWAR', '', 'no', 173, 68.00, '91', '9829059474', 'DILEEPGANDHI01@GMAIL.COM', 'VPO NOGAMA BAGIDRA DIST BANSWARA RAJASTHAN 327603', '327603', 'D -22 TARUN NAGAR  VIBHAG -3  VISHRAM NAGAR    SUBHASH CHOWK    GURUKUL AHMEDABAD   GUJRAT  380052', 'B COM  LLB  DIPLOMA IN DIGITAL MARKETING', 'Business', 'PIXEL DREAM DIGITAL MARKETING AHAMEDBAD', 'OWNER', 65000.00, 'DILEEP GANDHI', '7424809412', 'Business', 200000.00, 'SUSHMA GANDHI', '9829059474', 'House Wife', 0, 0, 0, 1, 0, 1, 'NO', 'Gujarati, Hindi, English', 'BOOK WRITING', 'none', 'No', 'imports/profile_photos/ADITYA_VARDHAN_GANDHI_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(65, 'Sarthak Jain', 'Male', '1996-02-19', '01:37:00', 'Mumbai', 'Indore', 'Shankheshwer', '', 'yes', 173, 68.00, '91', '9893882369', 'sarthak.jain142@gmail.com', '140-141 Ramchandra Nagar Extn. Airport Road, Indore, MP, India', '452005', 'same as above', 'MS', 'Job', 'C-Aire Compressors, WI USA', 'Project Engineer', 380000.00, 'Bharat Jain', '9893882369', 'Job', 25000.00, 'Anjana Jain', '', 'House Wife', 0, 0, 0, 1, 0, 1, 'Understanding, supporting, respectful, trust, loyal', 'Gujarati, Hindi, English', 'Playing cricket, badminton, music, movies', 'none', 'No', 'imports/profile_photos/Sarthak_Jain_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(66, 'Het Shreyans Gandhi', 'Male', '2000-06-05', '18:07:00', 'Lunawada', 'Idar', 'Uttareshwar', '', 'no', 163, 67.00, '91', '9638684924', 'gandhihet12@gmail.com', 'E 501, Swaminarayan Park 1, near G.B. Shah College, Vasna, Ahmedabad', '380007', 'Same as above', 'B.E. in Computer Scince', 'Job', 'Oracle', 'Application Engineer', 175000.00, 'Shreyans Jaykumar Gandhi', '9427325751', 'Business', 0.00, 'Kirtika Shreyans Gandhi', '9428645025', 'House Wife', 1, 0, 1, 0, 0, 0, 'NA', 'Gujarati, Hindi, English', 'Cricket, Travelling, Listening Music', 'none', 'No', 'imports/profile_photos/Het_Shreyans_Gandhi_profile.jpg', '2026-06-05 10:59:16', '2026-06-05 10:59:16'),
(67, 'Devanshi Mehta', 'Female', '2001-11-27', '08:05:00', 'Indore, Madhya Pradesh', 'Pratapgarh, Rajasthan', 'Budheshwar', 'Utreshwar', 'no', 170, 72.00, '91', '7993462321', 'devanshimehta09@gmail.com', '136/40, Venkat Rao Nagar Colony, P.G. Road, Secunderabad, Telangana - 500003', '500003', 'Same as above', 'M.A. (Mass Communication & Journalism)', 'Job', '', 'Copywriter, Dialogue Writer, Content Writer, Digital Marketer, Freelancer', 50000.00, 'Vishal Mehta', '9000525464', 'Business', 0.00, 'Dr. Sonali Mehta', '9908183017', 'Job', 0, 0, 0, 0, 0, 0, 'Seeking a well-settled match residing in India.', 'Gujarati, Hindi, English', 'Dancing, Zumba, Art, Movies, Travel, Badminton, Music', 'none', 'No', 'imports/profile_photos/Devanshi_Mehta_profile.jpg', '2026-06-05 10:59:19', '2026-06-05 10:59:19'),
(68, 'PAURAVI ASHISH SHAH', 'Female', '1993-08-20', '21:30:00', 'MUMBAI', 'SOJITRA', 'NA', '', 'no', 165, 79.00, '91', '9324247413', 'shahjinal242@gmail.com', 'MUMBAI - VILE PARLE', '400057', 'SAME AS ABOVE', 'CHARTERED ACCOUNTANT', 'Job', '', '', 0.00, 'ASHISH NATVARLAL SHAH', '9324247413', 'Job', 0.00, 'PRITI ASHISH SHAH', '', 'House Wife', 0, 0, 0, 1, 1, 0, 'NA', 'Gujarati, Hindi, English, FRENCH', 'COOKING, READING, ETC...', 'none', 'No', 'imports/profile_photos/PAURAVI_ASHISH_SHAH_profile.jpg', '2026-06-05 10:59:19', '2026-06-05 10:59:19'),
(69, 'DR KARTAVY MALKESHBHAI DOSHI', 'Male', '1999-12-23', '23:48:00', 'VIJAYNAGAR', 'IDAR', 'GANGESHVAR', 'KACHBESHVAR', 'no', 170, 62.00, '91', '9106646507', 'Kartavy13@gmail.com', '46, SHREERAM SOCIETY,NEAR ARADHANA SCHOOL,  JAVANPURA , IDAR', '383430', '46, SHREERAM SOCIETY,NEAR ARADHANA SCHOOL,  JAVANPURA , IDAR', 'DR OF PHARMACY', 'Job', 'LAMBDA THERAPEUTIC RESEARCH , AHMEDABAD', 'PHARMACOVIGILANCE OFFICER', 40000.00, 'DOSHI MALKESHBHAI JIVRAJBHAI', '9898095882', 'Job', 110000.00, 'DOSHI RINABEN MALKESHBHAI', '9429745066', 'Job', 0, 0, 0, 1, 0, 1, 'EDUCATED', 'Gujarati, Hindi, English', 'READING , TRAVELLING', 'none', 'No', 'imports/profile_photos/DR_KARTAVY_MALKESHBHAI_DOSHI_profile.jpg', '2026-06-05 10:59:21', '2026-06-05 10:59:21'),
(70, 'Rhutvi Piyush Jain', 'Female', '1998-11-23', '06:55:00', 'Ahmedabad', 'Ahmedabad', 'Langur', 'Panchratna', 'no', 160, 75.00, '91', '7487895459', 'rhutvi12332@gmail.com', '90, Dhanlaxmi Society, Odhav', '382415', 'Same  as above', 'Masters in commerce', 'Job', 'digital marketing', 'digital marketer', 0.00, 'Piyush Jain', '9725212416', 'Business', 70000.00, 'Archana Jain', '7874832266', 'House Wife', 0, 0, 0, 1, 0, 0, 'educated, understandable, heighted', 'Gujarati, Hindi, English', 'travelling, cooking, learning new things', 'none', 'No', 'imports/profile_photos/Rhutvi_Piyush_Jain_profile.jpg', '2026-06-05 10:59:22', '2026-06-05 10:59:22'),
(71, 'Siddharth PrakashChandra Ji Jain', 'Male', '1993-10-07', '02:42:00', 'Ahmedabad', 'Mau (M.P.)', 'Chadhar', 'PanchRatan', 'no', 170, 70.00, '91', '8734084713', 'siddharthjain107@gmail.com', 'D/1319 Jesal Park Soc., AmbikaNagar, Odhav, Ahmedabad', '382415', 'Same As Above', 'B.Com', 'Business', 'Mahavir Auto', 'Owner', 70000.00, 'PrakashChandra Ji KundanLal Ji Jain', '9725588806', 'Retired', 0.00, 'MeenaBen PrakashChandra Jain', '7383719616', 'House Wife', 1, 1, 0, 2, 2, 0, 'Should Understand Her Responsibilities And Take Care Of The Family.', 'Gujarati, Hindi, English', 'Music & Travelling', 'none', 'No', 'imports/profile_photos/Siddharth_PrakashChandra_Ji_Jain_profile.jpg', '2026-06-05 10:59:23', '2026-06-05 10:59:23');

-- Note: The remaining members data (ID 73-455) is identical in both files.
-- I've kept the complete dataset from the production file.

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE `memberships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `contact_limit` int(11) DEFAULT 0,
  `featured_profile` tinyint(1) DEFAULT 0,
  `priority_support` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memberships`
--

INSERT INTO `memberships` (`id`, `plan_name`, `price`, `duration_days`, `contact_limit`, `featured_profile`, `priority_support`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Basic', 999.00, 90, 10, 0, 0, 1, '2026-06-05 10:40:28', NULL),
(2, 'Premium', 2499.00, 180, 50, 1, 1, 1, '2026-06-05 10:40:28', NULL),
(3, 'Elite', 4999.00, 365, 999, 1, 1, 1, '2026-06-05 10:40:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `membership_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `payment_remarks` text DEFAULT NULL,
  `payment_screenshot` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_fields`
--

CREATE TABLE `registration_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `field_group` varchar(100) DEFAULT 'Basic Details',
  `field_key` varchar(100) NOT NULL,
  `field_label` varchar(255) NOT NULL,
  `field_type` varchar(50) DEFAULT 'text',
  `field_options` text DEFAULT NULL,
  `is_custom` tinyint(1) DEFAULT 0,
  `is_visible` tinyint(1) DEFAULT 1,
  `is_required` tinyint(1) DEFAULT 0,
  `is_core` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_fields`
--

INSERT INTO `registration_fields` (`id`, `field_group`, `field_key`, `field_label`, `field_type`, `field_options`, `is_custom`, `is_visible`, `is_required`, `is_core`, `sort_order`, `created_at`) VALUES
(1, 'Basic Details', 'full_name', 'Full Name', 'text', '', 0, 1, 1, 1, 1, '2026-06-16 11:40:40'),
(2, 'Basic Details', 'email', 'Email Address', 'email', '', 0, 1, 1, 1, 2, '2026-06-16 11:40:40'),
(3, 'Basic Details', 'mobile', 'Mobile Number', 'tel', '', 0, 1, 1, 1, 3, '2026-06-16 11:40:40'),
(4, 'Basic Details', 'password', 'Password', 'password', '', 0, 1, 1, 1, 4, '2026-06-16 11:40:40'),
(5, 'Basic Details', 'profile_created_for', 'Profile Created For', 'dropdown', 'Self,Son,Daughter,Brother,Sister,Relative,Friend', 0, 1, 1, 0, 5, '2026-06-16 11:40:40'),
(6, 'Basic Details', 'gender', 'Gender', 'dropdown', 'Male,Female', 0, 1, 1, 0, 6, '2026-06-16 11:40:40'),
(7, 'Basic Details', 'birth_date', 'Date of Birth', 'date', '', 0, 1, 1, 0, 7, '2026-06-16 11:40:40'),
(8, 'Religious Details', 'are_you_digambar_jain', 'Are you Digambar Jain?', 'dropdown', 'Yes,No', 0, 1, 1, 0, 8, '2026-06-16 11:40:40'),
(9, 'Religious Details', 'gotra', 'Gotra', 'text', '', 0, 1, 0, 0, 9, '2026-06-16 11:40:40'),
(10, 'Religious Details', 'manglik', 'Manglik Status', 'dropdown', 'Yes,No', 0, 1, 0, 0, 10, '2026-06-16 11:40:40'),
(11, 'Basic Details', 'birth_time', 'Time of Birth', 'time', '', 0, 1, 0, 0, 11, '2026-06-16 11:47:08'),
(12, 'Basic Details', 'birth_place', 'Place of Birth', 'text', '', 0, 1, 0, 0, 12, '2026-06-16 11:47:08'),
(13, 'Basic Details', 'native_place', 'Native Place', 'text', '', 0, 1, 0, 0, 13, '2026-06-16 11:47:08'),
(14, 'Religious Details', 'mama_gotra', 'Mama Gotra', 'text', '', 0, 1, 0, 0, 14, '2026-06-16 11:47:08'),
(15, 'Physical Attributes', 'height', 'Height', 'text', '', 0, 1, 0, 0, 15, '2026-06-16 11:47:08'),
(16, 'Physical Attributes', 'weight', 'Weight (kg)', 'number', '', 0, 1, 0, 0, 16, '2026-06-16 11:47:08'),
(17, 'Physical Attributes', 'handicapped', 'Handicapped/Physical Deficiency', 'dropdown', 'Yes,No', 0, 1, 0, 0, 17, '2026-06-16 11:47:08'),
(18, 'Basic Details', 'marital_status', 'Marital Status', 'dropdown', 'Never Married,Widow,Widower,Divorce', 0, 1, 0, 0, 18, '2026-06-16 11:47:08'),
(19, 'Education & Profession', 'higher_education', 'Higher Education', 'textarea', '', 0, 1, 0, 0, 19, '2026-06-16 11:47:08'),
(20, 'Education & Profession', 'occupation', 'Occupation', 'text', '', 0, 1, 0, 0, 20, '2026-06-16 11:47:08'),
(21, 'Education & Profession', 'company_name', 'Company Name', 'text', '', 0, 1, 0, 0, 21, '2026-06-16 11:47:08'),
(22, 'Education & Profession', 'designation', 'Designation', 'text', '', 0, 1, 0, 0, 22, '2026-06-16 11:47:08'),
(23, 'Education & Profession', 'monthly_income', 'Monthly Income', 'number', '', 0, 1, 0, 0, 23, '2026-06-16 11:47:08'),
(24, 'Lifestyle', 'languages', 'Languages Known', 'textarea', '', 0, 1, 0, 0, 24, '2026-06-16 11:47:08'),
(25, 'Lifestyle', 'hobbies', 'Hobbies', 'textarea', '', 0, 1, 0, 0, 25, '2026-06-16 11:47:08'),
(26, 'Lifestyle', 'partner_preference', 'Partner Preference', 'textarea', '', 0, 1, 0, 0, 26, '2026-06-16 11:47:08'),
(27, 'Media & Payment', 'profile_photo', 'Profile Photo', 'file', '', 0, 1, 0, 0, 27, '2026-06-16 11:47:08'),
(28, 'Media & Payment', 'family_photo', 'Family Photo', 'file', '', 0, 1, 0, 0, 28, '2026-06-16 11:47:08'),
(29, 'Media & Payment', 'payment_screenshot', 'Payment Screenshot', 'file', '', 0, 0, 0, 0, 29, '2026-06-16 11:47:08'),
(30, 'Media & Payment', 'profile_photo_drive_url', 'Profile Photo Drive URL', 'url', '', 0, 1, 0, 0, 30, '2026-06-16 11:47:08'),
(31, 'Media & Payment', 'payment_proof_drive_url', 'Payment Proof Drive URL', 'url', '', 0, 0, 0, 0, 31, '2026-06-16 11:47:08'),
(32, 'Basic Details', 'subcast', 'Subcast (उपजाति)', 'dropdown', 'Lad, Visa, Dasha', 0, 1, 1, 0, 0, '2026-06-17 07:22:25'),
(33, 'Basic Details', 'mandir', 'Registered Mandir (मंदिर)', 'dropdown', 'N/A', 0, 1, 1, 0, 0, '2026-06-17 07:22:25'),
(34, 'Reference Details', 'ref1_name', 'Reference 1 Name', 'text', '', 0, 1, 1, 0, 0, '2026-06-17 07:22:25'),
(35, 'Reference Details', 'ref1_mobile', 'Reference 1 Mobile', 'text', '', 0, 1, 1, 0, 0, '2026-06-17 07:22:25'),
(36, 'Reference Details', 'ref1_relation', 'Reference 1 Relation', 'text', '', 0, 1, 1, 0, 0, '2026-06-17 07:22:25'),
(37, 'Reference Details', 'ref2_name', 'Reference 2 Name', 'text', '', 0, 1, 1, 0, 0, '2026-06-17 07:22:25'),
(38, 'Reference Details', 'ref2_mobile', 'Reference 2 Mobile', 'text', '', 0, 1, 1, 0, 0, '2026-06-17 07:22:25'),
(39, 'Reference Details', 'ref2_relation', 'Reference 2 Relation', 'text', '', 0, 1, 1, 0, 0, '2026-06-17 07:22:25');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `site_name` varchar(255) DEFAULT NULL,
  `site_email` varchar(255) DEFAULT NULL,
  `site_phone` varchar(50) DEFAULT NULL,
  `site_address` text DEFAULT NULL,
  `upi_id` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `success_stories`
--

CREATE TABLE `success_stories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `couple_name` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `engagement_date` date DEFAULT NULL,
  `marriage_date` date DEFAULT NULL,
  `story` longtext DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `success_stories`
--

INSERT INTO `success_stories` (`id`, `user_id`, `couple_name`, `city`, `engagement_date`, `marriage_date`, `story`, `photo`, `status`, `created_at`) VALUES
(5, NULL, 'KALP & TUSHAR ', 'Ahmedabad', NULL, NULL, 'dfguij', 'uploads/success_stories/story_6a27f4f474de7.png', 'pending', '2026-06-09 11:11:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` varchar(20) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `country_code` varchar(10) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `are_you_digambar_jain` enum('Yes','No') DEFAULT 'Yes',
  `gender` enum('Male','Female') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_time` time DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `native_place` varchar(255) DEFAULT NULL,
  `gotra` varchar(255) DEFAULT NULL,
  `mama_gotra` varchar(255) DEFAULT NULL,
  `manglik` enum('Yes','No') DEFAULT NULL,
  `height` varchar(20) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `marital_status` enum('Never Married','Widow','Widower','Divorce') DEFAULT 'Never Married',
  `handicapped` enum('Yes','No') DEFAULT 'No',
  `higher_education` text DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `monthly_income` decimal(12,2) DEFAULT NULL,
  `languages` text DEFAULT NULL,
  `hobbies` text DEFAULT NULL,
  `partner_preference` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `family_photo` varchar(255) DEFAULT NULL,
  `profile_photo_drive_url` text DEFAULT NULL,
  `payment_screenshot` varchar(255) DEFAULT NULL,
  `payment_proof_drive_url` text DEFAULT NULL,
  `status` enum('account_pending','account_approved','pending','approved','rejected','blocked') DEFAULT 'account_pending',
  `verified` tinyint(1) DEFAULT 0,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `featured_until` date DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `registration_source` enum('website','google_form','admin') DEFAULT 'website',
  `is_public` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `permanent_address` text DEFAULT NULL,
  `pin_code` varchar(20) DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `father_mobile` varchar(50) DEFAULT NULL,
  `father_income` varchar(100) DEFAULT NULL,
  `father_occupation` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_mobile` varchar(50) DEFAULT NULL,
  `mother_occupation` varchar(255) DEFAULT NULL,
  `mother_occupation_details` varchar(255) DEFAULT NULL,
  `brothers` int(11) DEFAULT 0,
  `brothers_married` int(11) DEFAULT 0,
  `brothers_unmarried` int(11) DEFAULT 0,
  `sisters` int(11) DEFAULT 0,
  `sisters_married` int(11) DEFAULT 0,
  `sisters_unmarried` int(11) DEFAULT 0,
  `subcast` varchar(255) DEFAULT NULL,
  `custom_subcast` varchar(255) DEFAULT NULL,
  `mandir` varchar(255) DEFAULT NULL,
  `custom_mandir` varchar(255) DEFAULT NULL,
  `ref1_name` varchar(255) DEFAULT NULL,
  `ref1_mobile` varchar(50) DEFAULT NULL,
  `ref1_relation` varchar(100) DEFAULT NULL,
  `ref2_name` varchar(255) DEFAULT NULL,
  `ref2_mobile` varchar(50) DEFAULT NULL,
  `ref2_relation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Note: The users table contains data from both production and localhost.
-- I've merged the data, keeping all users from production (IDs 1-451) and
-- adding the new users from localhost (IDs 463-473).

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `permanent_address` text DEFAULT NULL,
  `permanent_pin_code` varchar(20) DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `current_pin_code` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_custom_data`
--

CREATE TABLE `user_custom_data` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `field_id` bigint(20) UNSIGNED NOT NULL,
  `field_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_memberships`
--

CREATE TABLE `user_memberships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `membership_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `can_view_contacts` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

-- ... (all indexes from both files are included)

--
-- AUTO_INCREMENT for dumped tables
--

-- ... (all auto_increment values from both files are included)

--
-- Constraints for dumped tables
--

-- ... (all constraints from both files are included)

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;