-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Dec 11, 2024 at 06:31 AM
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
-- Database: `crs`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `password`) VALUES
('admin@123.com', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `campuses`
--

CREATE TABLE `campuses` (
  `id` int(11) NOT NULL,
  `campus_name` text DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campuses`
--

INSERT INTO `campuses` (`id`, `campus_name`, `address`) VALUES
(1, 'Southern Leyte State University - Main Campus', '9XRH+RX8, Concepcion St, Sogod, Southern Leyte'),
(2, 'Southern Leyte State University - Tomas Oppus Campus', '7X2M+6QW, Tomas Oppus, Southern Leyte'),
(3, 'Southern Leyte State University - Bontoc Campus', '9X48+4F2, Bontoc, Southern Leyte'),
(4, 'Southern Leyte State University - San Juan cabilian campus', '757G+XC7, Unnamed Road, San Juan, Katimugang Leyte'),
(5, 'Southern Leyte State University - Maasin City Campus\r\n', 'Enage Street, Maasin City, Southern Leyte'),
(6, 'Southern Leyte State University - Hinunangan Campus\r\n', 'C53P+4GG, Barangay, Hinunangan, 6608 Southern Leyte');

-- --------------------------------------------------------

--
-- Table structure for table `campus_registration`
--

CREATE TABLE `campus_registration` (
  `id` int(11) NOT NULL,
  `usn` varchar(50) NOT NULL,
  `campus_name` varchar(255) NOT NULL,
  `campus_address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campus_registration`
--

INSERT INTO `campus_registration` (`id`, `usn`, `campus_name`, `campus_address`) VALUES
(1, 'Jovet123', 'Southern Leyte State University - Main Campus', '9XRH+RX8, Concepcion St, Sogod, Southern Leyte');

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

CREATE TABLE `registration` (
  `id` int(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `USN` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `id_picture_path` varchar(255) DEFAULT NULL,
  `brgy_cert_path` varchar(255) DEFAULT NULL,
  `income_cert_path` varchar(255) DEFAULT NULL,
  `psa_cert_path` varchar(255) DEFAULT NULL,
  `applicant_type` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `grade11_cert` text DEFAULT NULL,
  `grade12_cert` text DEFAULT NULL,
  `tor` text DEFAULT NULL,
  `form137` text DEFAULT NULL,
  `als_cert` text DEFAULT NULL,
  `eligibility_cert` text DEFAULT NULL,
  `pwd_id` text DEFAULT NULL,
  `ip_id` text DEFAULT NULL,
  `employment_cert` text DEFAULT NULL,
  `award_cert` text DEFAULT NULL,
  `solo_parent_id` text DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration`
--

INSERT INTO `registration` (`id`, `fname`, `lname`, `dob`, `USN`, `email`, `phone`, `id_picture_path`, `brgy_cert_path`, `income_cert_path`, `psa_cert_path`, `applicant_type`, `password`, `grade11_cert`, `grade12_cert`, `tor`, `form137`, `als_cert`, `eligibility_cert`, `pwd_id`, `ip_id`, `employment_cert`, `award_cert`, `solo_parent_id`, `approved`, `comments`, `created_at`, `updated_at`) VALUES
(1, 'Jovet', 'Langam', '2005-06-19', 'Jovet123', 'Jovetlangam@gmail.com', '09276032163', 'uploads/Jovet123/461435575_1101302734945898_312966449634781932_n.jpg', 'uploads/Jovet123/USE CASE DIAGRAM - Radaza.pdf', 'uploads/Jovet123/Activity-on-Gender-Analysis-Deadline-October-10-2024.pdf', '', 'transferee', '1af62cb8f2de806b3037bce7866abb59', '', '', 'uploads/Jovet123/Activity-on-Gender-Analysis-Deadline-October-10-2024.pdf', '', '', '', '', '', '', '', '', 0, 'Your PSA Certificate is missing. The other Documents are not clear please resubmit them all.', '2024-11-24 09:21:55', '2024-12-10 02:52:31'),
(2, 'Rhea', 'Bohol', '2002-12-24', 'Rhea123', '123@gmail.com', '09123456789', 'uploads/Rhea123/id_picture_1732438817.jpg', 'uploads/Rhea123/brgy_cert_1732438817.pdf', 'uploads/Rhea123/income_cert_1732438817.pdf', 'uploads/Rhea123/psa_cert_1732438817.pdf', 'solo_parent', '5c6287c3c985f950ab1eb544ec0190c5', '', '', '', '', '', '', '', '', '', '', 'uploads/Rhea123/solo_parent_id_1732438817.jpg', 0, 'Test', '2024-11-24 09:21:55', '2024-11-24 10:11:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `campuses`
--
ALTER TABLE `campuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `campus_registration`
--
ALTER TABLE `campus_registration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registration`
--
ALTER TABLE `registration`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usn` (`USN`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_applicant_type` (`applicant_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `campuses`
--
ALTER TABLE `campuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `campus_registration`
--
ALTER TABLE `campus_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `registration`
--
ALTER TABLE `registration`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `campus_registration`
--
ALTER TABLE `campus_registration`
  ADD CONSTRAINT `campus_registration_ibfk_1` FOREIGN KEY (`usn`) REFERENCES `registration` (`USN`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
