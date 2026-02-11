-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 28, 2026 at 12:36 PM
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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `software`, `version`, `description`, `status`, `created_by`, `created_at`) VALUES
(6, 'Sistem ERP SAP S/4HANA v2.0', 'SAP S/4HANA', '2.0.5', 'Validation of the new ERP system module for Inventory and Finance.', 'In Progress', 1, '2026-01-28 10:43:38'),
(8, 'fghjkl;', 'ghjkl', 'ghjkl', 'hjnk', 'Draft', 2, '2026-01-28 12:35:34');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

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
-- Table structure for table `stage_documents`
--

CREATE TABLE `stage_documents` (
  `id` int NOT NULL,
  `stage_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

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
  `approval` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `profile_picture`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'assets/uploads/profiles/user_1_1769596926.jpeg', '2026-01-28 09:14:45');

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
  ADD KEY `stage_id` (`stage_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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
-- Constraints for dumped tables
--

--
-- Constraints for table `project_stages`
--
ALTER TABLE `project_stages`
  ADD CONSTRAINT `project_stages_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `stage_validation_report_details_ibfk_1` FOREIGN KEY (`stage_id`) REFERENCES `project_stages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
