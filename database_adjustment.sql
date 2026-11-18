-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2026 at 06:00 AM
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
-- Database: `surong_guisad_drrm`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `category` enum('advisory','evacuation_instruction','emergency','preparedness') NOT NULL,
  `posted_by` int(11) NOT NULL,
  `posted_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evacuation_centers`
--

CREATE TABLE `evacuation_centers` (
  `center_id` int(11) NOT NULL,
  `center_name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` enum('open','full','closed') NOT NULL DEFAULT 'closed',
  `contact_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evacuees`
--

CREATE TABLE `evacuees` (
  `evacuee_id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `household_id` int(11) DEFAULT NULL,
  `incident_id` int(11) DEFAULT NULL,
  `evacuee_name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` enum('M','F') DEFAULT NULL,
  `time_in` datetime DEFAULT current_timestamp(),
  `time_out` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE `households` (
  `household_id` int(11) NOT NULL,
  `household_head` varchar(100) NOT NULL,
  `member_count` int(11) NOT NULL DEFAULT 1,
  `address` varchar(255) NOT NULL,
  `location_description` varchar(255) DEFAULT NULL,
  `vulnerability_class` enum('none','senior_citizen','pwd','pregnant','infant','low_income','multiple') NOT NULL DEFAULT 'none',
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incidents`
--

CREATE TABLE `incidents` (
  `incident_id` int(11) NOT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `reporter_name` varchar(100) DEFAULT NULL,
  `reporter_contact` varchar(20) DEFAULT NULL,
  `incident_type` enum('flood','fire','landslide','road_obstruction','other') NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `reported_at` datetime DEFAULT current_timestamp(),
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `response_team` varchar(100) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `action_taken` text DEFAULT NULL,
  `status` enum('reported','verified','responding','resolved','false_report') NOT NULL DEFAULT 'reported',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `relief_distributions`
--

CREATE TABLE `relief_distributions` (
  `distribution_id` int(11) NOT NULL,
  `household_id` int(11) NOT NULL,
  `incident_id` int(11) DEFAULT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `received_by` varchar(100) NOT NULL,
  `distributed_by` int(11) NOT NULL,
  `distribution_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `relief_items`
--

CREATE TABLE `relief_items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` enum('food_pack','drinking_water','medicine','blanket','other') NOT NULL,
  `unit` varchar(20) NOT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `movement_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `movement_type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `remarks` varchar(255) DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `movement_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','bdrrmc','official','responder') NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_affected_households`
-- (See below for the actual view)
--
CREATE TABLE `v_affected_households` (
`incident_id` int(11)
,`incident_type` enum('flood','fire','landslide','road_obstruction','other')
,`affected_households` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_center_occupancy`
-- (See below for the actual view)
--
CREATE TABLE `v_center_occupancy` (
`center_id` int(11)
,`center_name` varchar(100)
,`capacity` int(11)
,`current_occupants` bigint(21)
,`available_slots` bigint(22)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_incident_stats`
-- (See below for the actual view)
--
CREATE TABLE `v_incident_stats` (
`incident_type` enum('flood','fire','landslide','road_obstruction','other')
,`status` enum('reported','verified','responding','resolved','false_report')
,`total_incidents` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_relief_distribution_report`
-- (See below for the actual view)
--
CREATE TABLE `v_relief_distribution_report` (
`distribution_date` datetime
,`household_head` varchar(100)
,`address` varchar(255)
,`item_name` varchar(100)
,`quantity` int(11)
,`unit` varchar(20)
,`received_by` varchar(100)
);

-- --------------------------------------------------------

--
-- Structure for view `v_affected_households`
--
DROP TABLE IF EXISTS `v_affected_households`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_affected_households`  AS SELECT `i`.`incident_id` AS `incident_id`, `i`.`incident_type` AS `incident_type`, count(distinct `d`.`household_id`) AS `affected_households` FROM (`incidents` `i` left join `relief_distributions` `d` on(`d`.`incident_id` = `i`.`incident_id`)) GROUP BY `i`.`incident_id`, `i`.`incident_type` ;

-- --------------------------------------------------------

--
-- Structure for view `v_center_occupancy`
--
DROP TABLE IF EXISTS `v_center_occupancy`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_center_occupancy`  AS SELECT `c`.`center_id` AS `center_id`, `c`.`center_name` AS `center_name`, `c`.`capacity` AS `capacity`, count(`e`.`evacuee_id`) AS `current_occupants`, `c`.`capacity`- count(`e`.`evacuee_id`) AS `available_slots` FROM (`evacuation_centers` `c` left join `evacuees` `e` on(`e`.`center_id` = `c`.`center_id` and `e`.`time_out` is null)) GROUP BY `c`.`center_id`, `c`.`center_name`, `c`.`capacity` ;

-- --------------------------------------------------------

--
-- Structure for view `v_incident_stats`
--
DROP TABLE IF EXISTS `v_incident_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_incident_stats`  AS SELECT `incidents`.`incident_type` AS `incident_type`, `incidents`.`status` AS `status`, count(0) AS `total_incidents` FROM `incidents` GROUP BY `incidents`.`incident_type`, `incidents`.`status` ;

-- --------------------------------------------------------

--
-- Structure for view `v_relief_distribution_report`
--
DROP TABLE IF EXISTS `v_relief_distribution_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_relief_distribution_report`  AS SELECT `d`.`distribution_date` AS `distribution_date`, `h`.`household_head` AS `household_head`, `h`.`address` AS `address`, `r`.`item_name` AS `item_name`, `d`.`quantity` AS `quantity`, `r`.`unit` AS `unit`, `d`.`received_by` AS `received_by` FROM ((`relief_distributions` `d` join `households` `h` on(`h`.`household_id` = `d`.`household_id`)) join `relief_items` `r` on(`r`.`item_id` = `d`.`item_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `evacuation_centers`
--
ALTER TABLE `evacuation_centers`
  ADD PRIMARY KEY (`center_id`);

--
-- Indexes for table `evacuees`
--
ALTER TABLE `evacuees`
  ADD PRIMARY KEY (`evacuee_id`),
  ADD KEY `center_id` (`center_id`),
  ADD KEY `household_id` (`household_id`),
  ADD KEY `incident_id` (`incident_id`);

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`household_id`);

--
-- Indexes for table `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`incident_id`),
  ADD KEY `reported_by` (`reported_by`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `relief_distributions`
--
ALTER TABLE `relief_distributions`
  ADD PRIMARY KEY (`distribution_id`),
  ADD KEY `household_id` (`household_id`),
  ADD KEY `incident_id` (`incident_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `distributed_by` (`distributed_by`);

--
-- Indexes for table `relief_items`
--
ALTER TABLE `relief_items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evacuation_centers`
--
ALTER TABLE `evacuation_centers`
  MODIFY `center_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evacuees`
--
ALTER TABLE `evacuees`
  MODIFY `evacuee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `household_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `relief_distributions`
--
ALTER TABLE `relief_distributions`
  MODIFY `distribution_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `relief_items`
--
ALTER TABLE `relief_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `evacuees`
--
ALTER TABLE `evacuees`
  ADD CONSTRAINT `evacuees_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `evacuation_centers` (`center_id`),
  ADD CONSTRAINT `evacuees_ibfk_2` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `evacuees_ibfk_3` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`incident_id`) ON DELETE SET NULL;

--
-- Constraints for table `households`
--
--
-- Constraints for table `incidents`
--
ALTER TABLE `incidents`
  ADD CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`reported_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `incidents_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `incidents_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `relief_distributions`
--
ALTER TABLE `relief_distributions`
  ADD CONSTRAINT `relief_distributions_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`),
  ADD CONSTRAINT `relief_distributions_ibfk_2` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`incident_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `relief_distributions_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `relief_items` (`item_id`),
  ADD CONSTRAINT `relief_distributions_ibfk_4` FOREIGN KEY (`distributed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `relief_items` (`item_id`),
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
