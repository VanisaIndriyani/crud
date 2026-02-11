-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 11, 2026 at 09:14 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `validation_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `software` varchar(100) NOT NULL,
  `version` varchar(50) DEFAULT NULL,
  `description` text,
  `status` enum('Draft','In Progress','Completed') DEFAULT 'Draft',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `max_rpn` int DEFAULT '0',
  `risk_level` enum('Low','Medium','High','Non-GxP') DEFAULT NULL,
  `next_revalidation_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `software`, `version`, `description`, `status`, `created_by`, `created_at`, `max_rpn`, `risk_level`, `next_revalidation_date`) VALUES
(6, 'Sistem ERP SAP S/4HANA v2.0', 'SAP S/4HANA', '2.0.5', 'Validation of the new ERP system module for Inventory and Finance.', 'In Progress', 1, '2026-01-28 10:43:38', 126, 'High', '2027-02-11'),
(8, 'fghjkl;', 'ghjkl', 'ghjkl', 'hjnk', 'Draft', 2, '2026-01-28 12:35:34', 216, 'High', '2027-02-11');

-- --------------------------------------------------------

--
-- Table structure for table `project_stages`
--

CREATE TABLE `project_stages` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('Not Started','In Progress','Completed') DEFAULT 'Not Started',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completion_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `project_stages`
--

INSERT INTO `project_stages` (`id`, `project_id`, `name`, `status`, `updated_at`, `completion_date`) VALUES
(26, 6, 'User Request Specification', 'Completed', '2026-01-28 10:43:38', NULL),
(27, 6, 'IQ - Installation Qualification', 'Completed', '2026-01-28 10:43:38', NULL),
(28, 6, 'OQ - Operational Qualification', 'In Progress', '2026-01-28 10:43:38', NULL),
(29, 6, 'PQ - Performance Qualification', 'Not Started', '2026-01-28 10:43:38', NULL),
(30, 6, 'Laporan Validasi', 'Not Started', '2026-01-28 10:43:38', NULL),
(36, 8, 'User Request Specification', 'Not Started', '2026-01-28 12:35:34', NULL),
(37, 8, 'IQ - Installation Qualification', 'Not Started', '2026-01-28 12:35:34', NULL),
(38, 8, 'OQ - Operational Qualification', 'Not Started', '2026-01-28 12:35:34', NULL),
(39, 8, 'PQ - Performance Qualification', 'Not Started', '2026-01-28 12:35:34', NULL),
(40, 8, 'Laporan Validasi', 'Not Started', '2026-01-28 12:35:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `risk_assessments`
--

CREATE TABLE `risk_assessments` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `process_name` varchar(255) NOT NULL,
  `failure_mode` varchar(255) DEFAULT NULL,
  `effect_of_failure` varchar(255) DEFAULT NULL,
  `cause_of_failure` varchar(255) DEFAULT NULL,
  `severity` int NOT NULL DEFAULT '1',
  `occurrence` int NOT NULL DEFAULT '1',
  `detection` int NOT NULL DEFAULT '1',
  `rpn` int NOT NULL DEFAULT '1',
  `corrective_actions` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `risk_assessments`
--

INSERT INTO `risk_assessments` (`id`, `project_id`, `process_name`, `failure_mode`, `effect_of_failure`, `cause_of_failure`, `severity`, `occurrence`, `detection`, `rpn`, `corrective_actions`, `created_at`, `updated_at`) VALUES
(1, 8, 'fghjk', 'tyui', 'gyhuj', 'ghj', 3, 8, 9, 216, 'hujik', '2026-02-11 07:28:55', '2026-02-11 07:28:55'),
(2, 6, 'ghjkl', 'ghjk', 'ihiu', 'jij', 3, 7, 6, 126, '', '2026-02-11 07:46:29', '2026-02-11 07:46:29'),
(3, 6, 'ghjkl', 'ghjk', 'ihiu', 'jij', 1, 1, 1, 1, 'sjksn', '2026-02-11 07:49:01', '2026-02-11 07:49:01');

-- --------------------------------------------------------

--
-- Table structure for table `stage_documents`
--

CREATE TABLE `stage_documents` (
  `id` int NOT NULL,
  `stage_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stage_iq_details`
--

CREATE TABLE `stage_iq_details` (
  `id` int NOT NULL,
  `stage_id` int NOT NULL,
  `installation_date` date DEFAULT NULL,
  `hardware_verification` text,
  `software_verification` text,
  `documentation` text,
  `iq_result` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stage_iq_details`
--

INSERT INTO `stage_iq_details` (`id`, `stage_id`, `installation_date`, `hardware_verification`, `software_verification`, `documentation`, `iq_result`) VALUES
(4, 27, '2024-02-01', 'Verified server specifications match requirements.', 'Software installed successfully on production server.', 'Installation manual v1.0, Configuration guide.', 'Pass'),
(6, 27, '2024-02-01', 'Verified server specifications match requirements.', 'Software installed successfully on production server.', 'Installation manual v1.0, Configuration guide.', 'Pass');

-- --------------------------------------------------------

--
-- Table structure for table `stage_oq_details`
--

CREATE TABLE `stage_oq_details` (
  `id` int NOT NULL,
  `stage_id` int NOT NULL,
  `test_date` date DEFAULT NULL,
  `main_function_test` text,
  `interface_test` text,
  `security_test` text,
  `oq_result` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stage_oq_details`
--

INSERT INTO `stage_oq_details` (`id`, `stage_id`, `test_date`, `main_function_test`, `interface_test`, `security_test`, `oq_result`) VALUES
(2, 28, '2024-02-15', 'Testing core modules: Finance, Inventory, Sales.', 'Testing API integration with legacy systems.', 'Penetration testing and role-based access control.', 'Pending'),
(3, 28, '2024-02-15', 'Testing core modules: Finance, Inventory, Sales.', 'Testing API integration with legacy systems.', 'Penetration testing and role-based access control.', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `stage_pq_details`
--

CREATE TABLE `stage_pq_details` (
  `id` int NOT NULL,
  `stage_id` int NOT NULL,
  `test_date` date DEFAULT NULL,
  `test_scenario` text,
  `test_data` text,
  `performance_result` text,
  `pq_conclusion` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stage_urs_details`
--

CREATE TABLE `stage_urs_details` (
  `id` int NOT NULL,
  `stage_id` int NOT NULL,
  `requestor_name` varchar(255) DEFAULT NULL,
  `requestor_department` varchar(255) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `software_purpose` text,
  `functional_requirements` text,
  `technical_requirements` text,
  `user_requirements` text,
  `acceptance_criteria` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stage_urs_details`
--

INSERT INTO `stage_urs_details` (`id`, `stage_id`, `requestor_name`, `requestor_department`, `request_date`, `software_purpose`, `functional_requirements`, `technical_requirements`, `user_requirements`, `acceptance_criteria`) VALUES
(4, 26, 'Budi Santoso', 'IT Department', '2024-01-15', 'To manage inventory and finance processes efficiently.', 'Must handle 10,000 transactions per day.', 'Server with 32GB RAM, 1TB SSD.', 'User friendly interface, Indonesian language support.', 'All functional tests passed with 0 critical defects.'),
(6, 26, 'Budi Santoso', 'IT Department', '2024-01-15', 'To manage inventory and finance processes efficiently.', 'Must handle 10,000 transactions per day.', 'Server with 32GB RAM, 1TB SSD.', 'User friendly interface, Indonesian language support.', 'All functional tests passed with 0 critical defects.');

-- --------------------------------------------------------

--
-- Table structure for table `stage_validation_report_details`
--

CREATE TABLE `stage_validation_report_details` (
  `id` int NOT NULL,
  `stage_id` int NOT NULL,
  `executive_summary` text,
  `overall_result` text,
  `deviation` text,
  `recommendation` text,
  `approval` text,
  `prepared_by` int DEFAULT NULL,
  `prepared_date` datetime DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_date` datetime DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `profile_picture`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'assets/uploads/profiles/user_1_1769596926.jpeg', '2026-01-28 09:14:45');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `details` text COLLATE utf8mb4_general_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Login', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15', '2026-02-05 04:49:11'),
(2, 1, 'Login', NULL, '192.168.1.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', '2026-01-29 20:21:11'),
(3, 1, 'Failed Login', NULL, '172.16.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', '2026-01-31 14:56:11'),
(4, 1, 'Logout', NULL, '10.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2026-01-14 20:18:11'),
(5, 1, 'Failed Login', NULL, '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2026-01-16 02:44:11'),
(6, 1, 'Login', NULL, '172.16.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2026-01-29 07:38:11'),
(7, 1, 'Login', NULL, '172.16.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2026-01-24 17:33:11'),
(8, 1, 'Failed Login', NULL, '10.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', '2026-02-02 12:59:11'),
(9, 1, 'Failed Login', NULL, '10.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2026-01-30 14:07:11'),
(10, 1, 'Login', NULL, '172.16.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', '2026-01-25 02:38:11'),
(11, 1, 'Failed Login', NULL, '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', '2026-02-01 03:24:11'),
(12, 1, 'Logout', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2026-01-31 04:02:11'),
(13, 1, 'Failed Login', NULL, '10.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2026-01-24 00:17:11'),
(14, 1, 'Logout', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2026-02-05 15:18:11'),
(15, 1, 'Login', NULL, '172.16.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2026-01-20 04:24:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_stages`
--
ALTER TABLE `project_stages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `risk_assessments`
--
ALTER TABLE `risk_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `stage_documents`
--
ALTER TABLE `stage_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stage_id` (`stage_id`);

--
-- Indexes for table `stage_iq_details`
--
ALTER TABLE `stage_iq_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stage_id` (`stage_id`);

--
-- Indexes for table `stage_oq_details`
--
ALTER TABLE `stage_oq_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stage_id` (`stage_id`);

--
-- Indexes for table `stage_pq_details`
--
ALTER TABLE `stage_pq_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stage_id` (`stage_id`);

--
-- Indexes for table `stage_urs_details`
--
ALTER TABLE `stage_urs_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stage_id` (`stage_id`);

--
-- Indexes for table `stage_validation_report_details`
--
ALTER TABLE `stage_validation_report_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stage_id` (`stage_id`),
  ADD KEY `prepared_by` (`prepared_by`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `project_stages`
--
ALTER TABLE `project_stages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `risk_assessments`
--
ALTER TABLE `risk_assessments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stage_documents`
--
ALTER TABLE `stage_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stage_iq_details`
--
ALTER TABLE `stage_iq_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `stage_oq_details`
--
ALTER TABLE `stage_oq_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stage_pq_details`
--
ALTER TABLE `stage_pq_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stage_urs_details`
--
ALTER TABLE `stage_urs_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stage_validation_report_details`
--
ALTER TABLE `stage_validation_report_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `project_stages`
--
ALTER TABLE `project_stages`
  ADD CONSTRAINT `project_stages_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `risk_assessments`
--
ALTER TABLE `risk_assessments`
  ADD CONSTRAINT `risk_assessments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stage_documents`
--
ALTER TABLE `stage_documents`
  ADD CONSTRAINT `stage_documents_ibfk_1` FOREIGN KEY (`stage_id`) REFERENCES `project_stages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stage_iq_details`
--
ALTER TABLE `stage_iq_details`
  ADD CONSTRAINT `stage_iq_details_ibfk_1` FOREIGN KEY (`stage_id`) REFERENCES `project_stages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stage_oq_details`
--
ALTER TABLE `stage_oq_details`
  ADD CONSTRAINT `stage_oq_details_ibfk_1` FOREIGN KEY (`stage_id`) REFERENCES `project_stages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stage_pq_details`
--
ALTER TABLE `stage_pq_details`
  ADD CONSTRAINT `stage_pq_details_ibfk_1` FOREIGN KEY (`stage_id`) REFERENCES `project_stages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stage_urs_details`
--
ALTER TABLE `stage_urs_details`
  ADD CONSTRAINT `stage_urs_details_ibfk_1` FOREIGN KEY (`stage_id`) REFERENCES `project_stages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stage_validation_report_details`
--
ALTER TABLE `stage_validation_report_details`
  ADD CONSTRAINT `stage_validation_report_details_ibfk_1` FOREIGN KEY (`stage_id`) REFERENCES `project_stages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stage_validation_report_details_ibfk_2` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stage_validation_report_details_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stage_validation_report_details_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
