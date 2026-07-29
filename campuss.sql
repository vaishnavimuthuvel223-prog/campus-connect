-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2026 at 05:11 PM
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
-- Database: `campuss`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_logs`
--

CREATE TABLE `admin_login_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `action` varchar(20) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_login_logs`
--

INSERT INTO `admin_login_logs` (`log_id`, `user_id`, `email`, `action`, `ip_address`, `user_agent`, `timestamp`) VALUES
(1, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 03:48:35'),
(2, 1, 'admin@college.edu', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 04:31:31'),
(3, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 15:02:46'),
(4, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 15:15:18'),
(5, 1, 'admin@college.edu', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 15:16:14'),
(6, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 03:26:15'),
(7, 1, 'admin@college.edu', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 03:29:29'),
(8, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 07:17:53'),
(9, 1, 'admin@college.edu', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 07:19:36'),
(10, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 07:26:12'),
(11, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 08:06:29'),
(12, 1, 'admin@college.edu', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 08:25:51'),
(13, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 08:38:35'),
(14, 1, 'admin@college.edu', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:04:06'),
(15, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:12:27'),
(16, 1, 'admin@college.edu', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:15:40'),
(17, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:24:52'),
(18, 1, 'admin@college.edu', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:29:51'),
(19, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:51:40'),
(20, 1, 'admin@college.edu', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 11:09:34'),
(21, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:50:46'),
(22, 1, 'admin@college.edu', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:53:56'),
(23, 1, 'admin@college.edu', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:55:09'),
(24, 1, 'admin@college.edu', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:55:41');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `target` enum('all','students','staff') NOT NULL DEFAULT 'all',
  `priority` enum('normal','important','urgent') NOT NULL DEFAULT 'normal',
  `created_by` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `title`, `message`, `target`, `priority`, `created_by`, `is_active`, `created_at`) VALUES
(2, 'get ready,next drive is coming soon', 'company google is hiring...be prepared', 'all', 'important', 1, 1, '2026-03-05 05:15:16'),
(3, 'hiring', 'be ready', 'all', 'important', 1, 1, '2026-03-06 05:08:34'),
(4, 'placement', 'get ready for amazon drive and zoho', 'students', 'urgent', 1, 1, '2026-04-10 14:55:37');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `drive_id` int(11) NOT NULL,
  `round1_status` enum('pending','pass','fail') NOT NULL DEFAULT 'pending',
  `round2_status` enum('pending','pass','fail') NOT NULL DEFAULT 'pending',
  `final_status` enum('pending','selected','rejected') NOT NULL DEFAULT 'pending',
  `staff_approval` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`application_id`, `student_id`, `drive_id`, `round1_status`, `round2_status`, `final_status`, `applied_at`) VALUES
(3, 13, 7, 'pass', 'pass', 'selected', '2026-03-05 05:03:06'),
(4, 13, 8, 'fail', 'fail', 'rejected', '2026-03-09 17:06:28'),
(5, 13, 9, 'pending', 'pending', 'pending', '2026-04-10 03:31:45');

-- --------------------------------------------------------

--
-- Table structure for table `area_of_interest`
--

CREATE TABLE `area_of_interest` (
  `interest_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `interest_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `area_of_interest`
--

INSERT INTO `area_of_interest` (`interest_id`, `student_id`, `interest_name`, `description`, `created_at`) VALUES
(1, 13, 'DBMS', 'I am interested in understanding how databases are designed using concepts such as entity–relationship modeling, normalization, indexing, and query optimization. DBMS also helps in ensuring data integrity, security, and consistency while handling large volumes of information.\r\n\r\nThrough my learning and projects, I explore working with relational databases using SQL, MySQL, and database design techniques. I am particularly interested in building real-world systems such as management systems, web applications, and data-driven platforms that rely on efficient database structures', '2026-03-09 17:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `package` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `skill_category` varchar(32) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `founded` year(4) DEFAULT NULL,
  `ceo` varchar(255) DEFAULT NULL,
  `branches` text DEFAULT NULL,
  `skills` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`company_id`, `company_name`, `package`, `description`, `skill_category`, `location`, `founded`, `ceo`, `branches`, `skills`) VALUES
(1, 'TCS', '7.5 LPA', 'Tata Consultancy Services is an Indian multinational IT services company providing IT services, consulting, and business solutions across 150+ countries.', NULL, 'Mumbai, Maharashtra, India', '1968', 'K. Krithivasan', 'Mumbai, Bangalore, Hyderabad, Delhi, Pune, Chennai, Kolkata, Ahmedabad', 'Java, Python, .NET, Mainframe, Cloud, Cybersecurity, Analytics'),
(2, 'Infosys', '8 LPA', 'Infosys is a global leader in digital services and consulting, delivering IT solutions and transformation services to businesses worldwide since 1981.', NULL, 'Bangalore, Karnataka, India', '1981', 'Salil Parekh', 'Bangalore, Hyderabad, Pune, Noida, Mumbai, Chennai, Kolkata', 'Java, Python, Cloud Services, SAP, COBOL, Microservices, Docker'),
(3, 'Wipro', '7 LPA', 'Wipro is a leading global information technology, consulting, and business process services company serving clients across 167+ countries.', NULL, 'Bangalore, Karnataka, India', '1980', 'Thierry Delaporte', 'Bangalore, Hyderabad, Pune, Chennai, Kolkata, Delhi, Mumbai, Noida', 'Java, Python, Cloud Computing, Cybersecurity, Digital, AI/ML'),
(4, 'Cognizant', '8.5 LPA', 'Cognizant Technology Solutions is an American multinational IT services and consulting company providing digital transformation and IT solutions.', NULL, 'New Jersey, USA (Bangalore, India operations)', '1994', 'Ravi Kumar S.', 'New Jersey, Mumbai, Bangalore, Hyderabad, Pune, Chennai, Kolkata', 'Java, Python, Cloud, DevOps, Salesforce, Digital Transformation, RPA'),
(6, 'Google', '25 LPA', 'Google is a leading technology company specializing in search, cloud computing, AI/ML, and digital advertising. Famous for innovation and engineering excellence.', NULL, 'Mountain View, California, USA', '1998', 'Sundar Pichai', 'Mountain View, New York, London, Bangalore, Hyderabad, Pune', 'C++, Python, JavaScript, Data Structures, Algorithms, Machine Learning'),
(7, 'Amazon', '18 LPA', 'Amazon is one of the world\'s most innovative companies, operating global e-commerce, cloud computing, AI, and digital streaming services. Known for customer obsession and leadership principles.', NULL, 'Seattle, Washington, USA', '1994', 'Andy Jassy', 'Seattle, Mumbai, Bangalore, Pune, Hyderabad, Delhi', 'Java, Python, AWS, SQL, Problem Solving, System Design'),
(9, 'cartech', '25', 'we are hiring', NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'LTmintree', '25', 'hiring', 'elite category', 'pune', NULL, NULL, NULL, NULL),
(11, 'zoho', '10', 'hiring', 'elite category', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company_reviews`
--

CREATE TABLE `company_reviews` (
  `review_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `difficulty` enum('easy','medium','hard') NOT NULL DEFAULT 'medium',
  `interview_experience` text DEFAULT NULL,
  `tips` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `dept_id` int(11) NOT NULL,
  `dept_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dept_id`, `dept_name`) VALUES
(9, 'Artificial Intelligence and Data Science'),
(8, 'Artificial Intelligence and Machine Learning'),
(4, 'Civil Engineering'),
(1, 'Computer Science Engineering'),
(7, 'Electrical and Electronics Engineering'),
(2, 'Electronics and Communication Engineering'),
(5, 'Information Technology'),
(3, 'Mechanical Engineering'),
(6, 'VLSI');

-- --------------------------------------------------------

--
-- Table structure for table `department_staff`
--

CREATE TABLE `department_staff` (
  `staff_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `dept_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `profile_completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `staff_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table `staff_notifications`

CREATE TABLE `staff_notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  FOREIGN KEY (`staff_id`) REFERENCES department_staff(`staff_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `department_staff`
--

INSERT INTO `department_staff` (`staff_id`, `name`, `dept_id`, `email`, `phone`, `password`, `profile_pic`, `profile_completed`, `created_at`, `staff_code`) VALUES
(1, '', 1, '', NULL, '$2b$10$0jGY8dxIMUss0ySXOkZWu.BQU/sJ5mZ09Tu81boaSgtcIMrW1UI/G', NULL, 0, '2026-04-02 00:00:00', 'CSE-001'),
(2, '', 2, '', NULL, '$2b$10$qU6r2NBro4AJlOB7SIW1Bu5M9MW.0Eg8OMeosVv0CgSQMawxd9A..', NULL, 0, '2026-04-02 00:00:00', 'ECE-002'),
(3, '', 3, '', NULL, '$2b$10$ZheSHoT60HQmXFAWsF96genFEGCtoyCGpcHx3K5OpfRbDOkwVPhfq', NULL, 0, '2026-04-02 00:00:00', 'ME-003'),
(4, '', 4, '', NULL, '$2b$10$uWjvBsSsVOrj2oDoTn4zU.MsNcHFGatnTXsz.u8EeqYKEfp1ZXqd6', NULL, 0, '2026-04-02 00:00:00', 'CE-004'),
(5, 'C Thilagavathi', 5, 'thilagavathi@gmail.com', NULL, '$2b$10$Vr8b2T41qiBoZOPohQtuI.WBJuZPdVNqQAi2poPtwADo2pQVlAi76', NULL, 1, '2026-04-02 00:00:00', 'IT-005'),
(6, '', 6, '', NULL, '$2b$10$DGx/WJ3499KyxiOW8WCji.SGougOEK1wOWDM5NOt4iLd9TKnlGjnS', NULL, 0, '2026-04-02 00:00:00', 'VLSI-006'),
(7, '', 7, '', NULL, '$2b$10$BEBCOnkpUehtgtlkocY.v.QEcP.NkeFR0FIzHHcDMMbyM1QaemCPe', NULL, 0, '2026-04-02 00:00:00', 'EEE-007'),
(8, '', 8, '', NULL, '$2b$10$nabuwFFUqATSp14nAuWC0eoNc9LPBHldPUSUrZN587/ceFV5xt3fy', NULL, 0, '2026-04-02 00:00:00', 'AIML-008'),
(9, '', 9, '', NULL, '$2b$10$tXucvGvcbtfeW1TqLJ/Ldew7Q90ezC0lbtF5Lx/pe4OSHBS/MEVCe', NULL, 0, '2026-04-02 00:00:00', 'AIDS-009');

-- --------------------------------------------------------

--
-- Table structure for table `drives`
--

CREATE TABLE `drives` (
  `drive_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `min_cgpa` decimal(4,2) NOT NULL DEFAULT 6.00,
  `role` varchar(255) DEFAULT NULL,
  `drive_date` date NOT NULL,
  `last_date` date DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed') NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `min_tenth` decimal(5,2) DEFAULT NULL,
  `min_twelfth` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drives`
--

INSERT INTO `drives` (`drive_id`, `company_id`, `min_cgpa`, `role`, `drive_date`, `last_date`, `status`, `created_at`, `min_tenth`, `min_twelfth`) VALUES
(1, 1, 6.00, NULL, '2025-03-15', '2025-03-13', 'upcoming', '2026-03-03 06:33:12', NULL, NULL),
(2, 2, 6.50, NULL, '2025-03-20', '2025-03-18', 'upcoming', '2026-03-03 06:33:12', NULL, NULL),
(3, 3, 6.00, NULL, '2025-04-01', '2025-03-30', 'upcoming', '2026-03-03 06:33:12', NULL, NULL),
(4, 6, 8.00, NULL, '2025-04-10', '2025-04-08', 'upcoming', '2026-03-03 06:33:12', NULL, NULL),
(5, 7, 7.50, NULL, '2025-04-15', '2025-04-13', 'upcoming', '2026-03-03 06:33:12', NULL, NULL),
(6, 7, 6.00, NULL, '2026-03-05', '2026-03-03', 'completed', '2026-03-03 07:06:11', NULL, NULL),
(7, 3, 6.00, NULL, '2026-03-07', '2026-03-05', 'upcoming', '2026-03-05 04:59:20', NULL, NULL),
(8, 9, 8.00, NULL, '2026-03-14', '2026-03-10', 'ongoing', '2026-03-09 17:01:25', NULL, NULL),
(9, 10, 6.00, 'software engineer', '2026-04-15', '2026-04-11', 'upcoming', '2026-04-10 03:28:37', 80.00, 70.00),
(10, 11, 6.00, 'software engineer', '2026-04-15', '2026-04-12', 'upcoming', '2026-04-10 14:53:50', 80.00, 80.00);

-- --------------------------------------------------------

--
-- Table structure for table `drive_departments`
--

CREATE TABLE `drive_departments` (
  `id` int(11) NOT NULL,
  `drive_id` int(11) NOT NULL,
  `dept_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drive_departments`
--

INSERT INTO `drive_departments` (`id`, `drive_id`, `dept_id`) VALUES
(1, 1, 1),
(2, 1, 5),
(3, 2, 1),
(4, 2, 2),
(5, 2, 5),
(6, 3, 1),
(7, 3, 2),
(8, 3, 3),
(9, 3, 4),
(10, 3, 5),
(11, 4, 1),
(12, 5, 1),
(13, 5, 5),
(14, 6, 5),
(15, 7, 5),
(19, 8, 1),
(20, 8, 2),
(21, 8, 5),
(22, 9, 5),
(23, 10, 5);

-- --------------------------------------------------------

--
-- Table structure for table `internships`
--

CREATE TABLE `internships` (
  `internship_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `stipend` decimal(10,2) DEFAULT NULL,
  `project_title` varchar(255) DEFAULT NULL,
  `project_description` text DEFAULT NULL,
  `technologies_used` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internships`
--

INSERT INTO `internships` (`internship_id`, `student_id`, `company_name`, `role`, `duration`, `stipend`, `project_title`, `project_description`, `technologies_used`, `start_date`, `end_date`, `created_at`) VALUES
(1, 13, 'cognizant', 'web developer', '3 months', 15000.00, 'safe steps', 'safesteps is web based application to provide guidance and safety to all womens out there.', 'python,html,css', '2025-03-09', '2026-06-09', '2026-03-09 17:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `student_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(4, 13, 'success', 'Account Approved ✅', 'Great news, VAISHNAVI MUTHUVEL! Your account has been verified and approved. You can now apply for placement drives.', '/campus/student/drives.php', 1, '2026-03-05 10:32:41'),
(5, 13, 'info', 'Application Submitted — Wipro', 'Dear VAISHNAVI MUTHUVEL, your application for Wipro (Drive: 07 Mar 2026) has been submitted successfully. Stay tuned for updates!', '/campus/student/results.php', 1, '2026-03-05 10:33:06'),
(6, 13, 'success', 'Round 1 Result ✅ — Wipro', 'Dear VAISHNAVI MUTHUVEL, your Round 1 result for Wipro: Passed. Prepare well for the next round!', '/campus/student/results.php', 1, '2026-03-05 10:33:33'),
(7, 13, 'success', 'Round 2 Result ✅ — Wipro', 'Dear VAISHNAVI MUTHUVEL, your Round 2 result for Wipro: Passed. Prepare well for the next round!', '/campus/student/results.php', 1, '2026-03-05 10:33:36'),
(8, 13, 'success', '🎉 Selected by Wipro!', 'Congratulations VAISHNAVI MUTHUVEL! You have been selected by Wipro with a package of 3.8 LPA. Check your results for details.', '/campus/student/results.php', 1, '2026-03-05 10:33:42'),
(18, 13, 'success', 'Round 1 Result ✅ — Wipro', 'Dear VAISHNAVI MUTHUVEL, your Round 1 result for Wipro: Passed. Prepare well for the next round!', '/campus/student/results.php', 1, '2026-03-06 09:48:57'),
(24, 13, 'info', 'Application Submitted — cartech', 'Dear VAISHNAVI MUTHUVEL, your application for cartech (Drive: 14 Mar 2026) has been submitted successfully. Stay tuned for updates!', '/campus/student/results.php', 1, '2026-03-09 22:36:28'),
(25, 13, 'danger', 'Round 1 Result ❌ — cartech', 'Dear VAISHNAVI MUTHUVEL, your Round 1 result for cartech: Not Cleared. Keep your spirits up and continue applying!', '/campus/student/results.php', 1, '2026-03-09 22:40:30'),
(26, 13, 'danger', 'Round 2 Result ❌ — cartech', 'Dear VAISHNAVI MUTHUVEL, your Round 2 result for cartech: Not Cleared. Keep your spirits up and continue applying!', '/campus/student/results.php', 1, '2026-03-09 22:40:32'),
(27, 13, 'warning', 'Drive Result — cartech', 'Dear VAISHNAVI MUTHUVEL, your application for cartech was not successful this time. Keep applying to upcoming drives!', '/campus/student/drives.php', 1, '2026-03-09 22:40:35'),
(28, 13, 'info', 'Application Submitted — LTmintree', 'Dear VAISHNAVI MUTHUVEL, your application for LTmintree (Drive: 15 Apr 2026) has been submitted successfully. Stay tuned for updates!', '/campuss/student/results.php', 1, '2026-04-10 09:01:45');

-- --------------------------------------------------------

--
-- Table structure for table `placement_admin`
--

CREATE TABLE `placement_admin` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `placement_admin`
--

INSERT INTO `placement_admin` (`admin_id`, `name`, `email`, `password`, `profile_pic`) VALUES
(1, 'Admin User', 'admin@college.edu', '$2y$10$1QiK8eRucPQG0g4ddrIKj.tpGdkkF24JbleuECtRbap5R6cJWdaxS', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `project_title` varchar(255) NOT NULL,
  `project_description` text DEFAULT NULL,
  `technologies_used` text DEFAULT NULL,
  `project_link` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `skill_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `skill_name` varchar(255) NOT NULL,
  `skill_type` enum('technical','soft') NOT NULL DEFAULT 'technical',
  `proficiency_level` enum('beginner','intermediate','advanced','expert') NOT NULL DEFAULT 'beginner',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`skill_id`, `student_id`, `skill_name`, `skill_type`, `proficiency_level`, `created_at`) VALUES
(1, 13, 'java', 'technical', 'beginner', '2026-03-09 16:45:33'),
(2, 13, 'python', 'technical', 'beginner', '2026-03-09 17:23:39'),
(3, 13, 'communication', 'soft', 'advanced', '2026-03-09 17:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `staff_login_logs`
--

CREATE TABLE `staff_login_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `action` varchar(20) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_login_logs`
--

INSERT INTO `staff_login_logs` (`log_id`, `user_id`, `email`, `action`, `ip_address`, `user_agent`, `timestamp`) VALUES
(1, 5, '', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 03:46:54'),
(2, 5, '', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 03:48:02'),
(3, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 04:31:41'),
(4, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 04:32:13'),
(5, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 04:32:56'),
(6, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 14:57:13'),
(7, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 14:57:28'),
(8, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 15:00:46'),
(9, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 15:02:32'),
(10, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 15:14:16'),
(11, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 15:14:59'),
(12, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 15:16:45'),
(13, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 15:24:14'),
(14, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 04:17:45'),
(15, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 04:32:17'),
(16, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 06:19:24'),
(17, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 06:37:29'),
(18, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 06:40:49'),
(19, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 07:17:00'),
(20, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 07:17:41'),
(21, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 08:26:08'),
(22, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 08:30:48'),
(23, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 08:37:07'),
(24, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 08:38:12'),
(25, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 11:15:58'),
(26, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 11:17:49'),
(27, 5, 'thilagavathi@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:57:28'),
(28, 5, 'thilagavathi@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 15:00:17');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `cgpa` decimal(4,2) NOT NULL DEFAULT 0.00,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `batch_year` int(11) DEFAULT NULL,
  `backlogs` int(11) DEFAULT 0,
  `cleared_backlogs` int(11) DEFAULT 0,
  `dob` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mentor_name` varchar(255) DEFAULT NULL,
  `class_advisor_name` varchar(255) DEFAULT NULL,
  `father_phone` varchar(20) DEFAULT NULL,
  `mother_phone` varchar(20) DEFAULT NULL,
  `current_semester` int(11) DEFAULT 1,
  `skill_category` varchar(32) DEFAULT NULL,
  `tenth_percentage` decimal(5,2) DEFAULT NULL,
  `twelfth_percentage` decimal(5,2) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `mother_occupation` varchar(255) DEFAULT NULL,
  `father_occupation` varchar(255) DEFAULT NULL,
  `parents_phone` varchar(20) DEFAULT NULL,
  `github_link` varchar(255) DEFAULT NULL,
  `linkedin_link` varchar(255) DEFAULT NULL,
  `hackerrank_score` int(11) DEFAULT NULL,
  `cgpa_sem8` decimal(4,2) DEFAULT NULL,
  `cgpa_sem7` decimal(4,2) DEFAULT NULL,
  `cgpa_sem6` decimal(4,2) DEFAULT NULL,
  `cgpa_sem5` decimal(4,2) DEFAULT NULL,
  `cgpa_sem4` decimal(4,2) DEFAULT NULL,
  `cgpa_sem3` decimal(4,2) DEFAULT NULL,
  `cgpa_sem2` decimal(4,2) DEFAULT NULL,
  `cgpa_sem1` decimal(4,2) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `resume` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `verification_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `name`, `reg_no`, `dept_id`, `cgpa`, `email`, `phone`, `batch_year`, `backlogs`, `cleared_backlogs`, `dob`, `address`, `father_name`, `mother_name`, `mentor_name`, `class_advisor_name`, `father_phone`, `mother_phone`, `current_semester`, `skill_category`, `tenth_percentage`, `twelfth_percentage`, `school_name`, `mother_occupation`, `father_occupation`, `parents_phone`, `github_link`, `linkedin_link`, `hackerrank_score`, `cgpa_sem8`, `cgpa_sem7`, `cgpa_sem6`, `cgpa_sem5`, `cgpa_sem4`, `cgpa_sem3`, `cgpa_sem2`, `cgpa_sem1`, `password`, `resume`, `profile_pic`, `verification_status`, `created_at`) VALUES
(13, 'VAISHNAVI MUTHUVEL', '927624BIT119', 5, 8.10, 'vaishnavimuthuvel223@gmail.com', '9345685502', 2024, 0, 0, '2007-03-24', 'North Street\r\nPonnapuram,Ayakudi,Palani.', 'Muthuvel.P', 'Karthika M', 'Mrs.Subha', 'Mrs.Thilagavathi', '9751455620', '6369650018', 7, 'elite category', 86.00, 76.00, 'Bhaarath Public school -CBSE', 'House wife', 'Farmer', '9751455620', 'https://github.com/VaishnaviMuthuvel', 'https://www.linkedin.com/in/vaishnavi-muthuvel-b87b37334', 9411, 8.10, 8.30, 8.10, 8.20, 8.20, 8.10, 8.24, 8.34, '$2y$10$Wp9BZY6HFb6spq8Zi54M9O07WoZmlvMozVAxCHHdpLTgPVImosHvu', 'resume_13_1772555409.pdf', 'profile_13_1772729527.jpeg', 'approved', '2026-03-03 15:56:41');

-- --------------------------------------------------------

--
-- Table structure for table `student_login_logs`
--

CREATE TABLE `student_login_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `action` varchar(20) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_login_logs`
--

INSERT INTO `student_login_logs` (`log_id`, `user_id`, `email`, `action`, `ip_address`, `user_agent`, `timestamp`) VALUES
(1, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 04:32:33'),
(2, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 04:32:51'),
(3, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 14:58:28'),
(4, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 15:00:26'),
(5, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 15:05:20'),
(6, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 15:13:59'),
(7, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 15:28:03'),
(8, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 15:34:09'),
(9, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 03:30:30'),
(10, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 03:57:22'),
(11, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 04:04:36'),
(12, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 04:05:04'),
(13, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 04:42:37'),
(14, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 04:59:58'),
(15, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 05:00:15'),
(16, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 06:02:28'),
(17, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 06:03:25'),
(18, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 06:19:05'),
(19, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 06:23:51'),
(20, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 06:37:05'),
(21, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 06:41:02'),
(22, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 07:16:46'),
(23, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 08:31:28'),
(24, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 08:31:29'),
(25, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 08:36:34'),
(26, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:16:01'),
(27, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:17:34'),
(28, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:18:30'),
(29, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:24:39'),
(30, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:30:06'),
(31, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 09:51:27'),
(32, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 11:10:20'),
(33, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 11:15:42'),
(34, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 11:18:03'),
(35, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 13:19:48'),
(36, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 13:24:43'),
(37, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 13:27:00'),
(38, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 13:27:21'),
(39, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 13:30:36'),
(40, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 13:34:26'),
(41, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:49:33'),
(42, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:50:11'),
(43, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:50:33'),
(44, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:54:09'),
(45, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:54:59'),
(46, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:55:55'),
(47, 13, 'vaishnavimuthuvel223@gmail.com', 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 14:56:55'),
(48, 13, 'vaishnavimuthuvel223@gmail.com', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 15:00:54');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_applications`
-- (See below for the actual view)
--
CREATE TABLE `v_applications` (
`application_id` int(11)
,`student_name` varchar(100)
,`reg_no` varchar(50)
,`dept_name` varchar(100)
,`company_name` varchar(150)
,`drive_date` date
,`round1_status` enum('pending','pass','fail')
,`round2_status` enum('pending','pass','fail')
,`final_status` enum('pending','selected','rejected')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_drives`
-- (See below for the actual view)
--
CREATE TABLE `v_drives` (
`drive_id` int(11)
,`company_name` varchar(150)
,`package` varchar(50)
,`min_cgpa` decimal(4,2)
,`drive_date` date
,`status` enum('upcoming','ongoing','completed')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_students`
-- (See below for the actual view)
--
CREATE TABLE `v_students` (
`student_id` int(11)
,`name` varchar(100)
,`reg_no` varchar(50)
,`dept_name` varchar(100)
,`cgpa` decimal(4,2)
,`email` varchar(100)
,`resume` varchar(255)
,`verification_status` enum('pending','approved','rejected')
);

-- --------------------------------------------------------

--
-- Structure for view `v_applications`
--
DROP TABLE IF EXISTS `v_applications`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_applications`  AS SELECT `a`.`application_id` AS `application_id`, `s`.`name` AS `student_name`, `s`.`reg_no` AS `reg_no`, `d`.`dept_name` AS `dept_name`, `c`.`company_name` AS `company_name`, `dr`.`drive_date` AS `drive_date`, `a`.`round1_status` AS `round1_status`, `a`.`round2_status` AS `round2_status`, `a`.`final_status` AS `final_status` FROM ((((`applications` `a` join `students` `s` on(`a`.`student_id` = `s`.`student_id`)) join `departments` `d` on(`s`.`dept_id` = `d`.`dept_id`)) join `drives` `dr` on(`a`.`drive_id` = `dr`.`drive_id`)) join `companies` `c` on(`dr`.`company_id` = `c`.`company_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_drives`
--
DROP TABLE IF EXISTS `v_drives`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_drives`  AS SELECT `dr`.`drive_id` AS `drive_id`, `c`.`company_name` AS `company_name`, `c`.`package` AS `package`, `dr`.`min_cgpa` AS `min_cgpa`, `dr`.`drive_date` AS `drive_date`, `dr`.`status` AS `status` FROM (`drives` `dr` join `companies` `c` on(`dr`.`company_id` = `c`.`company_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_students`
--
DROP TABLE IF EXISTS `v_students`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_students`  AS SELECT `s`.`student_id` AS `student_id`, `s`.`name` AS `name`, `s`.`reg_no` AS `reg_no`, `d`.`dept_name` AS `dept_name`, `s`.`cgpa` AS `cgpa`, `s`.`email` AS `email`, `s`.`resume` AS `resume`, `s`.`verification_status` AS `verification_status` FROM (`students` `s` join `departments` `d` on(`s`.`dept_id` = `d`.`dept_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD UNIQUE KEY `unique_application` (`student_id`,`drive_id`),
  ADD KEY `drive_id` (`drive_id`);

--
-- Indexes for table `area_of_interest`
--
ALTER TABLE `area_of_interest`
  ADD PRIMARY KEY (`interest_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `company_reviews`
--
ALTER TABLE `company_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`company_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`dept_id`),
  ADD UNIQUE KEY `dept_name` (`dept_name`);

--
-- Indexes for table `department_staff`
--
ALTER TABLE `department_staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `staff_code` (`staff_code`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `drives`
--
ALTER TABLE `drives`
  ADD PRIMARY KEY (`drive_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `drive_departments`
--
ALTER TABLE `drive_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_drive_dept` (`drive_id`,`dept_id`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `internships`
--
ALTER TABLE `internships`
  ADD PRIMARY KEY (`internship_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `email_time` (`email`,`attempt_time`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `placement_admin`
--
ALTER TABLE `placement_admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `staff_login_logs`
--
ALTER TABLE `staff_login_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `reg_no` (`reg_no`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `student_login_logs`
--
ALTER TABLE `student_login_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `area_of_interest`
--
ALTER TABLE `area_of_interest`
  MODIFY `interest_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `company_reviews`
--
ALTER TABLE `company_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `department_staff`
--
ALTER TABLE `department_staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `drives`
--
ALTER TABLE `drives`
  MODIFY `drive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `drive_departments`
--
ALTER TABLE `drive_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `internships`
--
ALTER TABLE `internships`
  MODIFY `internship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `placement_admin`
--
ALTER TABLE `placement_admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff_login_logs`
--
ALTER TABLE `staff_login_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `student_login_logs`
--
ALTER TABLE `student_login_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`drive_id`) REFERENCES `drives` (`drive_id`) ON DELETE CASCADE;

--
-- Constraints for table `area_of_interest`
--
ALTER TABLE `area_of_interest`
  ADD CONSTRAINT `area_of_interest_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `company_reviews`
--
ALTER TABLE `company_reviews`
  ADD CONSTRAINT `company_reviews_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `company_reviews_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `department_staff`
--
ALTER TABLE `department_staff`
  ADD CONSTRAINT `department_staff_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE CASCADE;

--
-- Constraints for table `drives`
--
ALTER TABLE `drives`
  ADD CONSTRAINT `drives_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `drive_departments`
--
ALTER TABLE `drive_departments`
  ADD CONSTRAINT `drive_departments_ibfk_1` FOREIGN KEY (`drive_id`) REFERENCES `drives` (`drive_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `drive_departments_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE CASCADE;

--
-- Constraints for table `internships`
--
ALTER TABLE `internships`
  ADD CONSTRAINT `internships_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- --------------------------------------------------------

--
-- Table structure for table `preparation_hub`
--

CREATE TABLE `preparation_hub` (
  `company_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `founded_year` int(11) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `ceo_name` varchar(255) DEFAULT NULL,
  `headquarters` varchar(255) DEFAULT NULL,
  `industry` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `company_objectives` text DEFAULT NULL,
  `growth_achievements` text DEFAULT NULL,
  `skills_required` text DEFAULT NULL,
  `learning_resources` text DEFAULT NULL,
  `interview_prep_tips` text DEFAULT NULL,
  `recruitment_months` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`company_id`),
  UNIQUE KEY `company_name` (`company_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `preparation_hub`
--

INSERT INTO `preparation_hub` (`company_id`, `company_name`, `founded_year`, `owner`, `ceo_name`, `headquarters`, `industry`, `description`, `website`, `company_objectives`, `growth_achievements`, `skills_required`, `learning_resources`, `interview_prep_tips`, `recruitment_months`) VALUES
(1, 'Tata Consultancy Services (TCS)', 1968, 'Tata Group', 'K. Krithivasan', 'Mumbai, India', 'IT Services & Consulting', 'TCS is a global IT services and consulting company providing business agility to global enterprises. Operating in 50+ countries with a workforce of 600,000+ professionals, TCS delivers world-class technology and digital transformation solutions.', 'www.tcs.com', 'Transform enterprises through digital innovation, deliver sustainable value creation, and empower global customers to thrive in an interconnected world.', 'Market leader with 30+ years of consistent growth | Revenue: $28.5B+ (FY2023) | Fortune 500 company | ISO certified across all processes | Recognized for top workplace culture', 'Java, Python, C++, JavaScript, Cloud (AWS/Azure), Kubernetes, Docker, SQL, Data Analytics, AI/ML, Agile & DevOps', 'TCS iON Learning, Udemy partnerships, Internal certification programs, Technical workshops, Cloud training academy', 'Focus on core CS fundamentals, data structures, system design. Prepare for technical round (coding + system design) and HR round emphasizing team culture fit.', 'June-October'),
(2, 'Infosys', 1981, 'N.R. Narayana Murthy & co-founders', 'Salil Parekh', 'Bangalore, India', 'IT Services & Digital Transformation', 'Infosys is a global leader in next-generation digital services and consulting with a presence across 50+ countries. Serving 1000+ enterprise clients, Infosys specializes in digital transformation through AI, cloud, and automation.', 'www.infosys.com', 'Drive digital transformation with cloud-native solutions, harness AI and automation for business value, create an equitable, inclusive workplace, deliver sustainable growth.', 'Consistent annual growth of 12-15% | Market cap: $60B+ | Ranked among top 3 Indian IT companies | 312,000+ employees globally | ISO 27001 certified', 'Java, Python, Go, Node.js, Kubernetes, AWS, Azure, GCP, Terraform, Python for ML, Big Data (Hadoop/Spark), Microservices architecture', 'Infosys Springboard, Azure training, AWS certifications, Internal university, GitHub learning resources', 'Study algorithms, system design, OOPS concepts. Emphasis on client communication and collaborative problem-solving in interviews.', 'July-November'),
(3, 'Wipro', 1980, 'Wipro Limited (public company)', 'Thierry Delaporte', 'Bangalore, India', 'IT Services & Consulting', 'Wipro is a leading global information technology services company delivering innovative solutions to enterprises worldwide. With presence in 60+ countries, Wipro employs 200,000+ professionals solving complex business challenges.', 'www.wipro.com', 'Enable businesses to operate at speed with intelligent digital solutions, accelerate time-to-value through cloud and automation, foster innovation and entrepreneurship.', 'Annual revenue $12B+ | 200,000+ employees | NASDAQ listed | Presence in 60+ countries | Recognized Great Place to Work | Carbon neutral by 2030 commitment', 'Java, C++, Python, Node.js, React, Angular, Cloud platforms (AWS/Azure), Kubernetes, DevOps, Testing automation, Database design, APIs', 'Wipro Academy, Linux Foundation courses, Cloud certifications, Project exposure, Hackathons and innovation labs', 'Strong focus on problem-solving and coding. Practice medium-level LeetCode problems. Behavioral questions focus on teamwork and customer service.', 'August-December'),
(4, 'HCL Technologies', 1976, 'HCL Enterprises', 'C. Vijayakumar', 'Noida, India', 'IT Services & Solutions', 'HCL Technologies is a leading global technology company with presence in 50+ countries. Serving 1000+ global enterprises, HCL specializes in IT modernization, infrastructure services, and digital transformation.', 'www.hcltech.com', 'Empower enterprises with technology solutions, modernize IT infrastructure, accelerate digital transformation, deliver IT as a service with superior customer outcomes.', 'Revenue $13B+ annually | 218,000+ employees globally | 50+ countries | Recognized as Top Employer | Acquisition strategy strengthening market position', 'Java, Python, JavaScript, Go, Cloud platforms, Kubernetes, Docker, AWS/Azure, Terraform, CI/CD tools, Testing frameworks, SQL, NoSQL databases', 'HCL Campus Connect, Online training modules, Cloud certifications, Coding challenges, Tech forums', 'Practice basic to intermediate DSA. Focus on core concepts in OOPS and database design. Technical round followed by HR discussion on growth aspirations.', 'September-January'),
(5, 'Cognizant', 1994, 'Cognizant Technology Solutions Corporation', 'Reuben Roy (Interim CEO)', 'Jersey City, USA', 'IT Services & Digital Transformation', 'Cognizant is a multinational IT services company with 300,000+ employees across 40+ countries. Delivering digital strategy, cloud, and data analytics solutions to Fortune 500 companies.', 'www.cognizant.com', 'Drive digital transformation through emerging technologies, empower organizations to thrive in digital age, create sustainable business value, support ESG commitments.', 'Revenue $19.4B (2023) | 300,000+ employees | 40+ countries presence | Ranked in Fortune 500 | Winner of multiple innovation awards', 'Java, Python, Salesforce, AWS, Azure, Kubernetes, React, Angular, Microservices, API development, Data warehouse (Snowflake), Testing automation', 'Cognizant Academy, Salesforce training, Cloud certifications, Technical mentorship, Coursera partnerships', 'Focus on algorithm problem-solving and system design. Culture fit important - they highly value collaboration. Practice behavioral questions on teamwork.', 'July-November'),
(6, 'Accenture', 1989, 'Accenture plc (public)', 'Julie Sweet', 'Dublin, Ireland', 'Management Consulting & Technology Services', 'Accenture is a global professional services company with 750,000+ employees serving clients in 120+ countries. Specializing in digital consulting, technology services, and outsourcing solutions.', 'www.accenture.com', 'Create competitive advantage through digital innovation, drive client success through technology, build an inclusive, high-performing workforce, advance sustainability goals.', 'Revenue $64.9B annually | 750,000+ employees globally | NYSE listed | Top consulting firm | 120+ countries presence | Industry thought leader', 'Cloud platforms (AWS/Azure/GCP), Java, Python, JavaScript, Salesforce CRM, SAP, Business analytics, Tableau, Power BI, Kubernetes, DevOps practices', 'Accenture Academy, Udacity nanodegrees, Cloud certifications, Internal mentorship programs, Case study analysis platforms', 'Prepare for case interviews and technical assessments. Problem-solving approach highly valued. Focus on business impact and critical thinking.', 'August-December'),
(7, 'IBM', 1911, 'IBM Corporation (public)', 'Arvind Krishna', 'Armonk, New York, USA', 'IT Infrastructure, Cloud & AI Services', 'IBM is a global technology and consulting company with 280,000+ employees. Leader in cloud computing, artificial intelligence, and enterprise infrastructure solutions.', 'www.ibm.com', 'Lead in hybrid cloud and AI innovation, help clients accelerate digital transformation, drive sustainable technology solutions, foster IT workforce development.', 'Revenue $60.5B+ annually | 280,000+ employees | 170+ countries | NYSE listed | 110+ years of innovation | Red Hat acquisition strengthened cloud portfolio', 'Python, Java, Go, Node.js, Cloud (AWS/Azure), Kubernetes, AI/ML frameworks, Ansible, Linux, Data engineering, Cloud infrastructure, API development', 'IBM Cloud Learn, Linux Foundation, AI/ML courses, Cloud certifications, Technical tutorials on developer portal', 'Study cloud computing concepts and system design. Focus on emerging technologies like AI/ML. Problem-solving and analytical thinking emphasized.', 'September-January'),
(8, 'Tech Mahindra', 1986, 'Mahindra Group', 'CP Gurnani', 'Bangalore, India', 'IT Services & Digital Transformation', 'Tech Mahindra is a global IT services company with 150,000+ employees delivering digital transformation and enterprise solutions. Operating in 50+ countries across multiple industries.', 'www.techmahindra.com', 'Accelerate customer digital transformation, build tomorrow\'s workforce through innovation and learning, deliver sustainable business growth, champion responsible technology.', 'Revenue $7.1B+ annually | 150,000+ employees | 50+ countries coverage | NASDAQ listed | Recognized for strong engineering practices', 'Java, Python, C++, Cloud platforms, Docker, Kubernetes, AWS, Azure, Testing frameworks, Database design, API architecture, DevOps tools', 'Tech Mahindra Learning Center, Coding contests and hackathons, Cloud certifications, Project mentorship, Innovation labs', 'Practice DSA and system design problem-solving. Culture fit assessment includes teamwork and innovation mindset. Behavioral questions on technical growth.', 'October-February'),
(9, 'Capgemini', 1967, 'Capgemini SE (public)', 'Aiman Ezzat', 'Paris, France', 'Consulting & IT Services', 'Capgemini is a global leader in consulting, digital transformation, and technology services. Serving 270,000+ employees across 50+ countries delivering intelligent, sustainable change.', 'www.capgemini.com', 'Drive digital transformation through intelligent technologies, create sustainable value for stakeholders, empower talent through continuous learning, enable responsible innovation.', 'Revenue €21B+ annually | 320,000+ employees | 50+ countries presence | Euronext listed | Industry pioneers in sustainable technology', 'Java, C#, Python, JavaScript, Cloud (AWS/Azure/GCP), Kubernetes, Docker, Microservices, API development, Data analytics, Testing automation, DevOps practices', 'Capgemini Learning Resources, Digital workshops, Industry certifications, Mentorship programs, Innovation labs and case competitions', 'Focus on full-stack development and digital transformation concepts. Problem-solving approach and client-thinking paradigm highly valued.', 'August-November'),
(10, 'Goldman Sachs', 1869, 'Goldman Sachs Group Inc (public)', 'David Solomon', 'New York, USA', 'Financial Services & Investment Banking', 'Goldman Sachs is a leading global investment banking, securities and investment management firm. Pioneering technology and fintech innovation in financial services.', 'www.goldmansachs.com', 'Lead in digital transformation of financial services, innovate fintech solutions for clients, build world-class technology teams, drive inclusive economic growth.', 'Revenue $50B+ annually | 45,000+ employees | 70+ countries | NYSE listed | Fintech leader | Largest IPO underwriter', 'Java, Python, C++, Go, High-frequency trading systems, Low-latency programming, Distributed systems, Database optimization, Cloud platforms, Data science, Financial modeling', 'GS internal training, Algorithm repositories, Financial modeling courses, Interview prep guides, Fintech innovation programs', 'Extremely competitive selection process. Master advanced algorithms and system design. Financial domain knowledge beneficial. Focus on problem-solving excellence.', 'September-December'),
(11, 'Amazon', 1994, 'Amazon.com Inc (public)', 'Andy Jassy', 'Seattle, Washington, USA', 'E-commerce & Cloud Services (AWS)', 'Amazon is a global e-commerce and cloud computing giant. AWS (Amazon Web Services) is the leading cloud platform. Employs 1.5M+ people across 190+ countries.', 'www.amazon.com', 'Invent, innovate, and inspire change in customer experience, scale cloud infrastructure for global businesses, expand AWS services, drive sustainable operations.', 'Revenue $575B+ annually | 1.5M+ employees | 190+ countries | AWS 30%+ market share | Fastest growing major tech company | Industry disruptor', 'Java, Python, C++, Go, AWS services (EC2, S3, Lambda, RDS), Microservices, System design, Database optimization, Big data (Spark), DevOps, Linux', 'AWS training platform, LeetCode premium, System design resources, GitHub repositories, Technical blogs and whitepapers', 'Extremely competitive. Master data structures and algorithms. System design is crucial. Amazon Leadership Principles emphasized in behavioral rounds.', 'July-October'),
(12, 'Microsoft', 1975, 'Microsoft Corporation (public)', 'Satya Nadella', 'Redmond, Washington, USA', 'Software & Cloud Services', 'Microsoft is a global software giant with 220,000+ employees. Pioneering cloud computing through Azure, productivity software, gaming, and artificial intelligence.', 'www.microsoft.com', 'Empower every person and organization globally to achieve more, lead in cloud and AI innovation, transform digital experiences, drive responsible technology.', 'Revenue $198B+ annually | 220,000+ employees | 190+ countries | NASDAQ listed | Market cap $3T+ | Azure 30%+ cloud market share', 'C#, Java, Python, JavaScript, TypeScript, Azure services, .NET, Docker, Kubernetes, DevOps, SQL, NoSQL, Cloud architecture, AI/ML frameworks', 'Microsoft Learn platform, Azure certifications, GitHub learning resources, Coursera partnerships, Internal training academies', 'Focus on problem-solving and communication. System design questions common. Leadership qualities and growth mindset assessed in interviews.', 'August-November'),
(13, 'Google', 1998, 'Alphabet Inc (public)', 'Sundar Pichai', 'Mountain View, California, USA', 'Search, Advertising & Cloud Services', 'Google is the world\'s leading search engine and advertising platform. Alphabet Inc employs 190,000+ people globally across diverse technology domains.', 'www.google.com', 'Organize world\'s information and make it universally accessible, advance AI and ML capabilities, lead in cloud infrastructure, drive responsible innovation.', 'Revenue $307B+ annually | 190,000+ employees | 190+ countries | NASDAQ listed | Search market leader 90%+ | Pioneering AI with Bard/Gemini', 'C++, Java, Python, Go, JavaScript, System design, Distributed algorithms, Machine learning, TensorFlow, Kubernetes, Protocol buffers, Large-scale systems', 'Google Foobar challenge, Interview university resources, LeetCode hard problems, GitHub learning, Technical blog posts and research papers', 'Most competitive hiring process. Master advanced algorithms and system design. Multiple interview rounds with different focus areas. Excellence in all dimensions required.', 'September-December');
