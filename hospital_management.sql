-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2026 at 03:41 PM
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
-- Database: `hospital_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`) VALUES
(3, 3, 1, '2026-03-13', '13:00:00', 'Scheduled'),
(4, 3, 1, '2026-03-13', '15:00:00', 'Scheduled'),
(5, 3, 2, '2026-03-14', '19:00:00', 'Scheduled'),
(6, 5, 5, '2026-03-16', '11:00:00', 'Completed'),
(7, 4, 5, '2026-03-16', '16:30:00', 'Completed'),
(8, 5, 5, '2026-03-16', '13:00:00', 'Completed'),
(9, 5, 5, '2026-03-17', '11:00:00', 'Completed'),
(10, 7, 5, '2026-04-06', '11:30:00', 'Completed'),
(15, 7, 5, '2026-04-06', '12:30:00', 'Scheduled'),
(16, 7, 5, '2026-04-07', '11:00:00', 'Scheduled'),
(17, 3, 5, '2026-04-06', '13:00:00', 'Scheduled'),
(18, 3, 10, '2026-09-14', '13:00:00', 'Scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_requests`
--

CREATE TABLE `appointment_requests` (
  `request_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` time DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_requests`
--

INSERT INTO `appointment_requests` (`request_id`, `patient_id`, `doctor_id`, `preferred_date`, `preferred_time`, `status`, `created_at`) VALUES
(1, 9, 1, '2026-03-11', '14:00:00', 'Rejected', '2026-03-11 07:19:18'),
(3, 3, 1, '2026-03-13', '15:00:00', 'Approved', '2026-03-13 07:31:04'),
(4, 3, 2, '2026-03-14', '19:00:00', 'Approved', '2026-03-14 12:42:22'),
(5, 3, 1, '2026-03-16', '11:00:00', 'Rejected', '2026-03-14 12:42:48'),
(6, 5, 5, '2026-03-16', '11:00:00', 'Approved', '2026-03-15 14:14:58'),
(7, 4, 5, '2026-03-16', '16:30:00', 'Approved', '2026-03-16 14:43:36'),
(8, 5, 5, '2026-03-16', '13:00:00', 'Approved', '2026-03-16 15:06:21'),
(9, 5, 5, '2026-03-17', '11:00:00', 'Approved', '2026-03-16 15:06:37'),
(10, 7, 5, '2026-04-06', '12:30:00', 'Approved', '2026-04-04 17:56:44'),
(11, 3, 5, '2026-04-06', '13:00:00', 'Approved', '2026-04-06 07:18:32'),
(12, 3, 10, '2026-09-14', '13:00:00', 'Approved', '2026-09-12 16:41:04'),
(13, 3, 5, '2026-09-15', '12:30:00', 'Pending', '2026-09-14 13:01:51');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`log_id`, `user_id`, `action`, `table_name`, `record_id`, `description`, `ip_address`, `created_at`) VALUES
(1, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-03-15 15:53:25'),
(2, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-03-15 15:53:33'),
(3, 16, 'LOGIN', 'users', 16, 'User logged into system', '127.0.0.1', '2026-03-16 05:51:44'),
(4, 19, 'LOGIN', 'users', 19, 'User logged into system', '127.0.0.1', '2026-03-16 05:52:31'),
(5, 19, 'LOGIN', 'users', 19, 'User logged into system', '127.0.0.1', '2026-03-16 14:41:35'),
(6, 16, 'LOGIN', 'users', 16, 'User logged into system', '127.0.0.1', '2026-03-16 14:49:25'),
(7, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-03-16 14:54:35'),
(8, 19, 'LOGIN', 'users', 19, 'User logged into system', '127.0.0.1', '2026-03-16 15:08:38'),
(9, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-03-16 15:19:51'),
(10, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-03-20 13:35:02'),
(11, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-03-20 14:04:18'),
(12, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-03-21 13:57:50'),
(13, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-03-21 15:26:10'),
(14, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-03-21 15:48:40'),
(15, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-04 14:51:12'),
(16, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-04 14:55:29'),
(17, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 14:56:05'),
(18, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 14:56:24'),
(19, 16, 'LOGIN', 'users', 16, 'User logged into system', '127.0.0.1', '2026-04-04 14:56:52'),
(20, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 14:57:13'),
(21, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-04 14:58:30'),
(22, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 15:00:19'),
(23, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 15:04:05'),
(24, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 15:10:46'),
(25, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 15:15:01'),
(26, 10, 'APPROVE', 'patient', 6, 'Receptionist devil approve patient #6', '127.0.0.1', '2026-04-04 15:31:36'),
(27, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 15:32:58'),
(28, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 15:36:48'),
(29, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 16:40:28'),
(30, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-04 16:59:16'),
(31, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 16:59:37'),
(32, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 17:08:31'),
(33, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 17:31:54'),
(34, 10, 'APPROVE', 'patient', 7, 'Receptionist devil approve patient #7', '127.0.0.1', '2026-04-04 17:31:58'),
(35, 21, 'LOGIN', 'users', 21, 'User logged into system', '127.0.0.1', '2026-04-04 17:32:07'),
(36, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 17:37:03'),
(37, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-04 17:51:14'),
(38, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 17:55:23'),
(39, 21, 'LOGIN', 'users', 21, 'User logged into system', '127.0.0.1', '2026-04-04 17:56:18'),
(40, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-04 17:56:58'),
(41, 10, 'Appointment approve', NULL, NULL, 'Receptionist devil approve request #10', NULL, '2026-04-04 18:17:10'),
(42, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-04 18:19:23'),
(43, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-05 05:34:40'),
(44, 2, 'LOGIN', 'users', 2, 'User logged into system', '::1', '2026-04-05 05:35:34'),
(45, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-05 05:42:07'),
(46, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-05 05:42:19'),
(47, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-05 05:42:29'),
(48, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-05 05:42:55'),
(49, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-05 05:46:24'),
(50, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-05 05:58:47'),
(51, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-05 06:34:03'),
(52, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-05 06:38:57'),
(53, 19, 'LOGIN', 'users', 19, 'User logged into system', '127.0.0.1', '2026-04-05 06:46:53'),
(54, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-05 06:47:34'),
(55, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-04-05 07:00:47'),
(56, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-05 07:07:07'),
(57, 19, 'LOGIN', 'users', 19, 'User logged into system', '127.0.0.1', '2026-04-05 07:12:58'),
(58, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-05 07:16:18'),
(59, 16, 'LOGIN', 'users', 16, 'User logged into system', '127.0.0.1', '2026-04-05 07:16:53'),
(60, 16, 'LOGIN', 'users', 16, 'User logged into system', '127.0.0.1', '2026-04-06 07:18:11'),
(61, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-06 07:18:51'),
(62, 10, 'Appointment approve', NULL, NULL, 'Receptionist devil approve request #11', NULL, '2026-04-06 07:18:58'),
(63, 19, 'LOGIN', 'users', 19, 'User logged into system', '127.0.0.1', '2026-04-06 07:19:22'),
(64, 19, 'LOGIN', 'users', 19, 'User logged into system', '127.0.0.1', '2026-04-05 12:53:43'),
(65, 19, 'LOGIN', 'users', 19, 'User logged into system', '127.0.0.1', '2026-04-06 12:54:39'),
(66, 19, 'INSERT', 'visit', 10, 'Doctor completed visit and created medical record', '127.0.0.1', '2026-04-06 13:20:14'),
(67, 10, 'LOGIN', 'users', 10, 'User logged into system', '127.0.0.1', '2026-04-06 13:30:52'),
(68, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-09-12 13:36:35'),
(69, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-09-12 16:11:32'),
(70, 16, 'LOGIN', 'users', 16, 'User logged into system', '127.0.0.1', '2026-09-12 16:40:29'),
(71, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-09-12 16:41:26'),
(72, 31, 'LOGIN', 'users', 31, 'User logged into system', '127.0.0.1', '2026-09-12 16:42:27'),
(73, 31, 'Appointment approve', NULL, NULL, 'Receptionist reception1 approve request #12', NULL, '2026-09-12 16:42:34'),
(74, 24, 'LOGIN', 'users', 24, 'User logged into system', '127.0.0.1', '2026-09-12 16:43:36'),
(75, 30, 'LOGIN', 'users', 30, 'User logged into system', '127.0.0.1', '2026-09-12 16:44:00'),
(76, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-09-14 12:57:19'),
(77, 16, 'LOGIN', 'users', 16, 'User logged into system', '127.0.0.1', '2026-09-14 12:57:45'),
(78, 3, 'LOGIN', 'users', 3, 'User logged into system', '127.0.0.1', '2026-09-14 12:57:57'),
(79, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-09-14 12:58:51'),
(80, 32, 'LOGIN', 'users', 32, 'User logged into system', '127.0.0.1', '2026-09-14 12:59:22'),
(81, 2, 'LOGIN', 'users', 2, 'User logged into system', '127.0.0.1', '2026-09-14 13:00:07'),
(82, 30, 'LOGIN', 'users', 30, 'User logged into system', '127.0.0.1', '2026-09-14 13:00:37'),
(83, 16, 'LOGIN', 'users', 16, 'User logged into system', '127.0.0.1', '2026-09-14 13:01:06'),
(84, 19, 'LOGIN', 'users', 19, 'User logged into system', '127.0.0.1', '2026-09-14 13:02:09');

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `bill_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('Pending','Paid') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing`
--

INSERT INTO `billing` (`bill_id`, `patient_id`, `doctor_id`, `total_amount`, `status`, `created_at`) VALUES
(1, 4, NULL, 0.40, 'Pending', '2026-03-21 14:17:29'),
(2, 3, NULL, 0.50, 'Paid', '2026-03-21 14:22:08'),
(3, 3, NULL, 9.00, 'Paid', '2026-03-21 14:26:12'),
(4, 4, NULL, 100.40, 'Pending', '2026-03-21 14:28:56'),
(5, 5, NULL, 100.00, 'Pending', '2026-03-21 15:23:59'),
(6, 4, NULL, 100.00, 'Paid', '2026-03-21 15:30:34'),
(7, 4, NULL, 250.00, 'Paid', '2026-03-21 15:44:22'),
(8, 5, NULL, 0.13, 'Pending', '2026-03-21 15:55:01'),
(9, 5, NULL, 0.10, 'Pending', '2026-03-21 15:55:39'),
(10, 6, NULL, 1610.00, 'Pending', '2026-04-06 13:25:04');

-- --------------------------------------------------------

--
-- Table structure for table `billing_items`
--

CREATE TABLE `billing_items` (
  `item_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_items`
--

INSERT INTO `billing_items` (`item_id`, `bill_id`, `item_name`, `quantity`, `price`, `total`) VALUES
(1, 1, 'Paracetemol', 2, 0.20, 0.40),
(2, 2, 'Paracetemol', 1, 0.50, 0.50),
(3, 3, 'Paracetemol', 3, 3.00, 9.00),
(4, 4, 'Paractemol', 2, 0.20, 0.40),
(5, 4, 'Blood Test', 1, 100.00, 100.00),
(6, 5, 'Consultation', 1, 100.00, 100.00),
(7, 6, 'Consultation', 1, 100.00, 100.00),
(8, 7, 'Paracetemol ', 5, 50.00, 250.00),
(9, 8, 'Item1', 1, 0.13, 0.13),
(10, 9, 'item2', 1, 0.10, 0.10),
(11, 10, 'Consultation', 1, 100.00, 100.00),
(12, 10, 'Blood Test', 1, 1100.00, 1100.00),
(13, 10, 'Medicine1', 1, 410.00, 410.00);

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `doctor_id` int(11) NOT NULL,
  `doctor_code` varchar(10) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`doctor_id`, `doctor_code`, `name`, `specialization`, `contact_number`, `email`, `is_active`, `user_id`) VALUES
(1, 'DOC-000001', '', 'Legs', '+36123451234', '', 1, 3),
(2, 'DOC-000002', '', 'Limbs', '+911234123423', '', 1, 4),
(3, 'DOC-000003', '', 'Liver', '+917652526356', '', 0, 5),
(4, 'DOC-000004', NULL, 'Heart', '+918352526434', NULL, 1, 6),
(5, 'DOC-000005', NULL, 'Kidney', '+911231569353', NULL, 1, 19),
(6, 'DOC-000006', NULL, 'Neurology', '+911234123122', NULL, 1, 24),
(7, 'DOC-000007', '', 'Orthopedic', '+9198723413712', '', 1, 27),
(8, 'DOC-000008', NULL, 'ENT', '+91 12312442341', NULL, 1, 28),
(9, 'DOC-000009', NULL, 'Pediatrics', '+919537282738', NULL, 1, 29),
(10, 'DOC-R34WZD', NULL, 'cardiology', '+913523423424', NULL, 1, 30);

-- --------------------------------------------------------

--
-- Table structure for table `doctor_leave`
--

CREATE TABLE `doctor_leave` (
  `leave_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `leave_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor_schedule`
--

CREATE TABLE `doctor_schedule` (
  `schedule_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slot_duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_schedule`
--

INSERT INTO `doctor_schedule` (`schedule_id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `slot_duration`) VALUES
(1, 4, 'Monday', '10:00:00', '17:00:00', 30),
(2, 4, 'Wednesday', '10:00:00', '17:00:00', 30),
(3, 4, 'Friday', '10:00:00', '17:00:00', 30),
(4, 5, 'Monday', '10:30:00', '17:30:00', 30),
(5, 5, 'Tuesday', '10:30:00', '17:30:00', 30),
(6, 5, 'Wednesday', '10:30:00', '17:30:00', 30),
(7, 5, 'Thursday', '10:30:00', '17:30:00', 30),
(8, 5, 'Friday', '10:30:00', '17:30:00', 30),
(9, 5, 'Saturday', '10:30:00', '17:30:00', 30),
(10, 6, 'Monday', '10:30:00', '17:30:00', 30),
(11, 6, 'Tuesday', '10:30:00', '17:30:00', 30),
(12, 6, 'Wednesday', '10:30:00', '17:30:00', 30),
(13, 6, 'Saturday', '10:30:00', '17:30:00', 30),
(14, 8, 'Monday', '11:00:00', '17:00:00', 40),
(15, 8, 'Tuesday', '11:00:00', '17:00:00', 40),
(16, 8, 'Wednesday', '11:00:00', '17:00:00', 40),
(17, 8, 'Thursday', '11:00:00', '17:00:00', 40),
(18, 8, 'Friday', '11:00:00', '17:00:00', 40),
(19, 9, 'Monday', '09:30:00', '17:00:00', 30),
(20, 9, 'Wednesday', '09:30:00', '17:00:00', 30),
(21, 9, 'Friday', '09:30:00', '17:00:00', 30),
(22, 10, 'Monday', '12:00:00', '20:00:00', 30),
(23, 10, 'Thursday', '12:00:00', '20:00:00', 30),
(24, 10, 'Saturday', '12:00:00', '20:00:00', 30);

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `log_id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('SUCCESS','FAILED') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`log_id`, `username`, `user_id`, `status`, `ip_address`, `login_time`) VALUES
(1, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-15 13:07:55'),
(2, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-15 13:12:53'),
(3, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-15 13:13:36'),
(4, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-15 13:27:38'),
(5, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-15 13:29:54'),
(6, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-15 13:35:27'),
(7, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-15 13:48:38'),
(8, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-15 14:10:37'),
(9, 'patient3', 18, 'SUCCESS', '127.0.0.1', '2026-03-15 14:12:44'),
(10, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-15 14:18:19'),
(11, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-15 14:19:33'),
(12, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-15 14:19:52'),
(13, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-16 14:33:44'),
(14, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-16 14:34:07'),
(15, 'patient2', 17, 'SUCCESS', '127.0.0.1', '2026-03-16 14:43:15'),
(16, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-16 14:43:47'),
(17, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-16 14:44:07'),
(18, 'doctor1', 3, 'SUCCESS', '127.0.0.1', '2026-03-16 14:48:06'),
(19, 'doctor2', 4, 'SUCCESS', '127.0.0.1', '2026-03-16 14:48:57'),
(20, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-16 15:02:18'),
(21, 'patient4', NULL, 'FAILED', '127.0.0.1', '2026-03-16 15:05:26'),
(22, 'patient1', 16, 'SUCCESS', '127.0.0.1', '2026-03-16 15:05:34'),
(23, 'patient3', 18, 'SUCCESS', '127.0.0.1', '2026-03-16 15:05:52'),
(24, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-16 15:06:50'),
(25, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-16 15:07:06'),
(26, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-16 15:07:29'),
(27, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-17 15:08:42'),
(28, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-15 15:37:29'),
(29, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-15 15:44:25'),
(30, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-15 15:48:36'),
(31, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-15 15:48:47'),
(32, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-15 15:49:37'),
(33, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-15 15:53:25'),
(34, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-15 15:53:33'),
(35, 'patient1', 16, 'SUCCESS', '127.0.0.1', '2026-03-16 05:51:44'),
(36, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-16 05:52:31'),
(37, 'doctor5', 19, 'FAILED', '127.0.0.1', '2026-03-16 14:41:27'),
(38, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-16 14:41:35'),
(39, 'patient1', 16, 'SUCCESS', '127.0.0.1', '2026-03-16 14:49:25'),
(40, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-16 14:54:35'),
(41, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-03-16 15:08:38'),
(42, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-03-16 15:19:51'),
(43, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-20 13:35:02'),
(44, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-20 14:04:18'),
(45, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-21 13:57:50'),
(46, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-21 15:26:10'),
(47, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-03-21 15:48:40'),
(48, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-04 14:51:12'),
(49, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-04 14:55:29'),
(50, 'yash', NULL, 'FAILED', '127.0.0.1', '2026-04-04 14:55:55'),
(51, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 14:56:05'),
(52, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 14:56:24'),
(53, 'patient', NULL, 'FAILED', '127.0.0.1', '2026-04-04 14:56:45'),
(54, 'patient1', 16, 'SUCCESS', '127.0.0.1', '2026-04-04 14:56:52'),
(55, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 14:57:13'),
(56, 'yash', NULL, 'FAILED', '127.0.0.1', '2026-04-04 14:58:22'),
(57, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-04 14:58:30'),
(58, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 15:00:19'),
(59, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 15:04:05'),
(60, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 15:10:46'),
(61, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 15:15:01'),
(62, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 15:32:58'),
(63, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 15:36:48'),
(64, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 16:40:28'),
(65, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-04 16:59:16'),
(66, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 16:59:37'),
(67, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 17:08:31'),
(68, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 17:31:54'),
(69, 'patient69', 21, 'SUCCESS', '127.0.0.1', '2026-04-04 17:32:07'),
(70, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 17:37:03'),
(71, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-04 17:51:14'),
(72, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 17:55:23'),
(73, 'patient69', 21, 'SUCCESS', '127.0.0.1', '2026-04-04 17:56:18'),
(74, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-04 17:56:58'),
(75, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-04 18:19:23'),
(76, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-05 05:34:40'),
(77, 'admin', 2, 'SUCCESS', '::1', '2026-04-05 05:35:34'),
(78, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-05 05:42:07'),
(79, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-05 05:42:19'),
(80, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-05 05:42:29'),
(81, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-05 05:42:55'),
(82, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-05 05:46:24'),
(83, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-05 05:58:47'),
(84, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-05 06:34:03'),
(85, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-05 06:38:57'),
(86, 'doctor6', NULL, 'FAILED', '127.0.0.1', '2026-04-05 06:46:43'),
(87, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-04-05 06:46:53'),
(88, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-05 06:47:34'),
(89, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-04-05 07:00:47'),
(90, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-05 07:07:06'),
(91, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-04-05 07:12:58'),
(92, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-05 07:16:18'),
(93, 'patient99', NULL, 'FAILED', '127.0.0.1', '2026-04-05 07:16:44'),
(94, 'patient1', 16, 'SUCCESS', '127.0.0.1', '2026-04-05 07:16:53'),
(95, 'patient1', 16, 'FAILED', '127.0.0.1', '2026-04-06 07:17:59'),
(96, 'patient1', 16, 'SUCCESS', '127.0.0.1', '2026-04-06 07:18:11'),
(97, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-06 07:18:51'),
(98, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-04-06 07:19:22'),
(99, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-04-05 12:53:43'),
(100, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-04-06 12:54:39'),
(101, 'devil', 10, 'SUCCESS', '127.0.0.1', '2026-04-06 13:30:52'),
(102, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-09-12 13:36:35'),
(103, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-09-12 16:11:32'),
(104, 'patient1', 16, 'SUCCESS', '127.0.0.1', '2026-09-12 16:40:29'),
(105, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-09-12 16:41:26'),
(106, 'reception1', 31, 'SUCCESS', '127.0.0.1', '2026-09-12 16:42:27'),
(107, 'prince1', NULL, 'FAILED', '127.0.0.1', '2026-09-12 16:43:14'),
(108, 'prince', NULL, 'FAILED', '127.0.0.1', '2026-09-12 16:43:22'),
(109, 'kumar123', 24, 'SUCCESS', '127.0.0.1', '2026-09-12 16:43:36'),
(110, 'prince123', 30, 'SUCCESS', '127.0.0.1', '2026-09-12 16:44:00'),
(111, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-09-14 12:57:19'),
(112, 'patient1', 16, 'SUCCESS', '127.0.0.1', '2026-09-14 12:57:45'),
(113, 'doctor1', 3, 'SUCCESS', '127.0.0.1', '2026-09-14 12:57:57'),
(114, 'receipt1', NULL, 'FAILED', '127.0.0.1', '2026-09-14 12:58:14'),
(115, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-09-14 12:58:51'),
(116, 'angel1', 32, 'SUCCESS', '127.0.0.1', '2026-09-14 12:59:22'),
(117, 'prince1', NULL, 'FAILED', '127.0.0.1', '2026-09-14 12:59:42'),
(118, 'prince', NULL, 'FAILED', '127.0.0.1', '2026-09-14 12:59:50'),
(119, 'admin', 2, 'SUCCESS', '127.0.0.1', '2026-09-14 13:00:07'),
(120, 'prince123', 30, 'FAILED', '127.0.0.1', '2026-09-14 13:00:28'),
(121, 'prince123', 30, 'SUCCESS', '127.0.0.1', '2026-09-14 13:00:37'),
(122, 'patient1', 16, 'SUCCESS', '127.0.0.1', '2026-09-14 13:01:06'),
(123, 'doctor5', 19, 'SUCCESS', '127.0.0.1', '2026-09-14 13:02:09');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `record_id` int(11) NOT NULL,
  `visit_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `record_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_record_history`
--

CREATE TABLE `medical_record_history` (
  `history_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `modified_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `patient_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `user_id` int(11) NOT NULL,
  `verification_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patient_id`, `full_name`, `gender`, `date_of_birth`, `contact_number`, `address`, `created_at`, `is_active`, `user_id`, `verification_status`) VALUES
(3, 'patient1', 'Male', '2026-03-11', '123123133', 'agawtagd', '2026-03-13 06:30:42', 1, 16, 'Approved'),
(4, 'patient2', 'Female', '2026-03-03', '121212121', 'akasjashdagsuf', '2026-03-14 14:25:43', 1, 17, 'Approved'),
(5, 'patient3', 'Male', '2026-03-03', '12512541254', 'cgashgfag', '2026-03-14 14:51:02', 1, 18, 'Approved'),
(6, 'patient90', 'Male', '2004-04-04', '3791275852', 'dsgfgsyugfy', '2026-04-04 15:14:26', 1, 20, 'Approved'),
(7, 'patient69', 'Female', '2005-05-05', '615', '44465', '2026-04-04 17:31:46', 1, 21, 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Receptionist','Doctor','Patient') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `created_at`, `is_active`, `verified`) VALUES
(2, 'admin', '$2y$10$8MTluSyydRvpQZ2ayIJYtO..Mx5nZUhQ91Y/LriJHjME9f9utmoDa', 'Admin', '2026-03-11 06:12:04', 1, 1),
(3, 'doctor1', '$2y$10$A8k.25ta1eV1DM1T/wwppu6FMSJGiZmqmUwlT2k.nsb7qAmkB4sBm', 'Doctor', '2026-03-11 06:32:27', 1, 1),
(4, 'doctor2', '$2y$10$5eWmE0FFf3sdKfVRZc.Qm.JE47LlaZ6hqQG5QVIOT41DF4po6qYcm', 'Doctor', '2026-03-11 06:36:46', 1, 1),
(5, 'doctor3', '$2y$10$rt5TL5ieBJGA9jYyqCP/c.ymDkwvdvsbEr7nsHLXL45fbTM.ECPxO', 'Doctor', '2026-03-11 06:36:57', 1, 1),
(6, 'doctor4', '$2y$10$tdilfZD6ngDFPdCUTsJMj.bhc74gbJoGFEoc82pjEks2WQKN8DLeu', 'Doctor', '2026-03-11 06:55:22', 1, 1),
(10, 'devil', '$2y$10$9NLLGac184rcEX35kU72SezDWq0.FVBpsMj76v8n5boEz0E3r5SRW', 'Receptionist', '2026-03-11 07:19:55', 1, 1),
(16, 'patient1', '$2y$10$vODclMBoW10UC0TN68H4Fe6hcsPuHFmzETOor7WQRNfpkcM2h6ksC', 'Patient', '2026-03-13 06:30:42', 1, 0),
(17, 'patient2', '$2y$10$wUDrzQxPbTqhIxLSGsRFn.0QIP95d0Zx1dam2fhvkaZVH71W46ZGm', 'Patient', '2026-03-14 14:25:43', 1, 0),
(18, 'patient3', '$2y$10$U2r2tU.kjHyUTGfLZFI9quaVxBoHgQaYAnhW5ZEPLx//Yb2i9Ej/G', 'Patient', '2026-03-14 14:51:02', 1, 0),
(19, 'doctor5', '$2y$10$96CSP4yY9fFgUg50A.5FIuJ6kXY7SAQRyvz1SicolhN1x5Csrp6fK', 'Doctor', '2026-03-15 13:37:26', 1, 0),
(20, 'patient90', '$2y$10$sibZcwPsD8KuG1cYkWUZee88mtBQ/eVSktySuQPeK.wFcec7ysR22', 'Patient', '2026-04-04 15:14:26', 1, 0),
(21, 'patient69', '$2y$10$sj3ro6bVbYojf/x4V9DsDO2QRDblfg5NvVfIJ2w.VNSg.62V5tGIq', 'Patient', '2026-04-04 17:31:46', 1, 0),
(24, 'kumar123', '$2y$10$51AmEAH6tFxjQZDEycUj4e3bc4Zg6Dio0SvToH.45TFRu5V7ZmKii', 'Doctor', '2026-09-12 13:41:11', 1, 0),
(27, 'vicky123', '$2y$10$cALBYU0BEE4VZpyjp7SJRuJHcAGQ9WhrUULUrQFZ0wfuNDcxN6q/y', 'Doctor', '2026-09-12 13:51:14', 1, 0),
(28, 'amit123', '$2y$10$W5GiCKJ8BZvv/i4w7HP1p.7fcGdO4ODWtUwem4maKR7beVqMDyJq.', 'Doctor', '2026-09-12 13:53:14', 1, 0),
(29, 'puja123', '$2y$10$oDr8I1/09athWwVLwk/vbexYQo7y86qHeoE4ARdW8Lvel8J8h/SJy', 'Doctor', '2026-09-12 14:26:26', 1, 0),
(30, 'prince123', '$2y$10$sbYTseqXUpNvuhq0vqxd2Of0ddEqOx5TrvapLa7/TeG42uqQUamcy', 'Doctor', '2026-09-12 15:34:02', 1, 0),
(31, 'reception1', '$2y$10$H4nrNPES9b31yn9b3OM8ze2RU/.j0Mbs9yJQf6.OLmrEC/LWN9Ke2', 'Receptionist', '2026-09-12 16:42:12', 1, 0),
(32, 'angel1', '$2y$10$ZYJs7JfFv8tm2z9VZys6e.KSa/B5IPTqhWj4r8Stp.kEUbBRasngi', 'Receptionist', '2026-09-14 12:59:07', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `visit`
--

CREATE TABLE `visit` (
  `visit_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `visit_date` datetime DEFAULT current_timestamp(),
  `diagnosis` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visit`
--

INSERT INTO `visit` (`visit_id`, `appointment_id`, `patient_id`, `doctor_id`, `visit_date`, `diagnosis`, `prescription`, `notes`, `completed_by`) VALUES
(2, 6, 5, 5, '2026-03-16 20:33:02', 'Low Sugar', 'Eat sugar', 'exercise dailt', 19),
(3, 7, 4, 5, '2026-03-16 20:34:24', 'Kidney Stone', 'Drink more water', 'Daily atleast 3 litres water drink', 19),
(4, 8, 5, 5, '2026-03-16 20:38:17', 'eye', 'high power', 'nothing serious', 19),
(5, 9, 5, 5, '2026-03-17 20:39:07', 'heart', 'heart beat fast', '...', 19),
(6, 10, 7, 5, '2026-04-06 18:50:14', 'Kidney Stone', 'Medicine1, Medicine2', 'Drink more than 1 litre water in a day', 19);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appointment_id`),
  ADD UNIQUE KEY `unique_doctor_slot` (`doctor_id`,`appointment_date`,`appointment_time`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `appointment_requests`
--
ALTER TABLE `appointment_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`bill_id`),
  ADD KEY `fk_billing_patient` (`patient_id`),
  ADD KEY `fk_billing_doctor` (`doctor_id`);

--
-- Indexes for table `billing_items`
--
ALTER TABLE `billing_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_billing_items_bill` (`bill_id`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `uq_doctor_code` (`doctor_code`);

--
-- Indexes for table `doctor_leave`
--
ALTER TABLE `doctor_leave`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `doctor_schedule`
--
ALTER TABLE `doctor_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `visit_id` (`visit_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `medical_record_history`
--
ALTER TABLE `medical_record_history`
  ADD PRIMARY KEY (`history_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`patient_id`),
  ADD KEY `fk_patient_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `visit`
--
ALTER TABLE `visit`
  ADD PRIMARY KEY (`visit_id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`),
  ADD UNIQUE KEY `unique_appointment_visit` (`appointment_id`),
  ADD KEY `idx_visit_doctor` (`doctor_id`),
  ADD KEY `idx_visit_patient` (`patient_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `appointment_requests`
--
ALTER TABLE `appointment_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `billing_items`
--
ALTER TABLE `billing_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `doctor`
--
ALTER TABLE `doctor`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `doctor_leave`
--
ALTER TABLE `doctor_leave`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctor_schedule`
--
ALTER TABLE `doctor_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_record_history`
--
ALTER TABLE `medical_record_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `visit`
--
ALTER TABLE `visit`
  MODIFY `visit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON DELETE CASCADE;

--
-- Constraints for table `billing`
--
ALTER TABLE `billing`
  ADD CONSTRAINT `billing_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `billing_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_billing_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`),
  ADD CONSTRAINT `fk_billing_patient` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`);

--
-- Constraints for table `billing_items`
--
ALTER TABLE `billing_items`
  ADD CONSTRAINT `billing_items_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `billing` (`bill_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_billing_items_bill` FOREIGN KEY (`bill_id`) REFERENCES `billing` (`bill_id`);

--
-- Constraints for table `doctor_leave`
--
ALTER TABLE `doctor_leave`
  ADD CONSTRAINT `doctor_leave_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_schedule`
--
ALTER TABLE `doctor_schedule`
  ADD CONSTRAINT `doctor_schedule_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`visit_id`) REFERENCES `visit` (`visit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`),
  ADD CONSTRAINT `medical_records_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `fk_patient_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `visit`
--
ALTER TABLE `visit`
  ADD CONSTRAINT `fk_visit_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointment` (`appointment_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
