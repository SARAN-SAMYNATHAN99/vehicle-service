-- ========================================================
-- Vehicle Service Booking System - Database Schema & Data
-- Web Technology (WT) College Project
-- Compatible with MySQL 5.7+ / MySQL 8.0+ / MariaDB / XAMPP
-- ========================================================

CREATE DATABASE IF NOT EXISTS `vehicle_service_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `vehicle_service_db`;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `vehicles`;
DROP TABLE IF EXISTS `mechanics`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: vehicles
-- --------------------------------------------------------
CREATE TABLE `vehicles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `vehicle_number` VARCHAR(50) NOT NULL,
  `vehicle_type` VARCHAR(50) NOT NULL,
  `brand` VARCHAR(100) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `manufacturing_year` INT NOT NULL,
  `fuel_type` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`user_id`),
  CONSTRAINT `fk_vehicles_user` FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services
-- --------------------------------------------------------
CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_name` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `estimated_duration` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: mechanics
-- --------------------------------------------------------
CREATE TABLE `mechanics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `specialization` VARCHAR(150) NOT NULL,
  `availability` ENUM('Available', 'Busy', 'On Leave') NOT NULL DEFAULT 'Available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: bookings
-- --------------------------------------------------------
CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `vehicle_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `mechanic_id` INT NULL,
  `booking_date` DATE NOT NULL,
  `booking_time` VARCHAR(20) NOT NULL,
  `notes` TEXT NULL,
  `estimated_price` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Pending', 'Confirmed', 'In Service', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`user_id`),
  INDEX (`vehicle_id`),
  INDEX (`service_id`),
  INDEX (`mechanic_id`),
  CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_vehicle` FOREIGN KEY (`vehicle_id`) 
    REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`) 
    REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_mechanic` FOREIGN KEY (`mechanic_id`) 
    REFERENCES `mechanics` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- Sample Data Insertion
-- ========================================================

-- Users (Admin & Sample Customers)
-- Admin Password: Admin@123
-- Customer Password: Password@123
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`) VALUES
(1, 'System Administrator', 'admin@gearshift.com', '+1 (555) 019-2831', '$2y$10$0v9PK7G4gkwTz6tu5PYC/ebSPR.RS2NLb1UBgacPWic2CCxR479uG', 'admin'),
(2, 'John Doe', 'john@example.com', '+1 (555) 234-5678', '$2y$10$tl7hIKiiqkZbTLAGCrRYKeXlcoI.FzTPCwNwja01euWxuk0aXZoVO', 'customer'),
(3, 'Emily Watson', 'emily@example.com', '+1 (555) 345-6789', '$2y$10$tl7hIKiiqkZbTLAGCrRYKeXlcoI.FzTPCwNwja01euWxuk0aXZoVO', 'customer'),
(4, 'Michael Rodriguez', 'michael@example.com', '+1 (555) 456-7890', '$2y$10$tl7hIKiiqkZbTLAGCrRYKeXlcoI.FzTPCwNwja01euWxuk0aXZoVO', 'customer');

-- Vehicles for Sample Customers
INSERT INTO `vehicles` (`id`, `user_id`, `vehicle_number`, `vehicle_type`, `brand`, `model`, `manufacturing_year`, `fuel_type`) VALUES
(1, 2, 'MH-12-AB-1234', 'Car', 'Honda', 'Civic EX', 2021, 'Petrol'),
(2, 2, 'MH-12-CD-5678', 'Bike', 'Royal Enfield', 'Classic 350', 2022, 'Petrol'),
(3, 3, 'KA-05-XY-9081', 'SUV', 'Toyota', 'RAV4 Hybrid', 2023, 'Hybrid'),
(4, 4, 'DL-01-ET-4455', 'Car', 'Tesla', 'Model 3', 2022, 'Electric');

-- Standard Services (as requested)
INSERT INTO `services` (`id`, `service_name`, `description`, `price`, `estimated_duration`) VALUES
(1, 'General Service', 'Comprehensive 50-point safety check, engine fluid top-up, air & cabin filter inspection, brake evaluation, and exterior wash.', 99.00, '3 Hours'),
(2, 'Oil Change', 'Full synthetic high-performance engine oil change, genuine OEM oil filter replacement, and chassis lubrication.', 49.00, '1 Hour'),
(3, 'Brake Service', 'Front & rear brake pad wear analysis, caliper cleaning & adjustment, rotor resurfacing, and high-temp DOT-4 brake fluid flush.', 79.00, '2 Hours'),
(4, 'Tyre Service', 'High-speed computerized wheel balancing, 4-tyre rotation, tread depth wear inspection, and digital tire pressure calibration.', 39.00, '1.5 Hours'),
(5, 'AC Service', 'Cabin cooling performance test, refrigerant gas vacuum & recharge, evaporator coil disinfection, and cabin filter replacement.', 89.00, '2.5 Hours'),
(6, 'Engine Check', 'Complete computerized OBD-II electronic diagnostic scan, ignition timing calibration, spark plug check, and emission analysis.', 120.00, '3 Hours'),
(7, 'Battery Replacement', 'Advanced digital battery load test, starter & alternator check, terminal corrosion cleanup, and new maintenance-free battery installation.', 110.00, '1 Hour'),
(8, 'Wheel Alignment', 'Precision 3D computerized laser alignment, camber, caster, and toe adjustment for smoother handling and prolonged tire life.', 45.00, '1.5 Hours');

-- Mechanics with Specializations & Availability
INSERT INTO `mechanics` (`id`, `name`, `phone`, `specialization`, `availability`) VALUES
(1, 'Robert Miller', '+1 (555) 789-0123', 'Engine Diagnostics & Transmission', 'Available'),
(2, 'David Chen', '+1 (555) 890-1234', 'Brake Systems & Wheel Alignment', 'Available'),
(3, 'Sarah Jenkins', '+1 (555) 901-2345', 'Auto Electrical & Air Conditioning', 'Busy'),
(4, 'Carlos Mendez', '+1 (555) 012-3456', 'General Maintenance & Hybrid Systems', 'Available'),
(5, 'Alex Turner', '+1 (555) 123-4567', 'Tire Specialists & Suspension', 'On Leave');

-- Sample Bookings representing different real-world statuses
INSERT INTO `bookings` (`id`, `user_id`, `vehicle_id`, `service_id`, `mechanic_id`, `booking_date`, `booking_time`, `notes`, `estimated_price`, `status`) VALUES
(1, 2, 1, 1, 1, CURDATE() - INTERVAL 10 DAY, '09:00 AM', 'Routine 10k mile maintenance. Check slight squeak when starting.', 99.00, 'Completed'),
(2, 2, 2, 2, 4, CURDATE() + INTERVAL 2 DAY, '11:00 AM', 'Change engine oil before highway trip.', 49.00, 'Confirmed'),
(3, 2, 1, 5, NULL, CURDATE() + INTERVAL 5 DAY, '02:00 PM', 'AC cooling is reduced during afternoon heat.', 89.00, 'Pending'),
(4, 3, 3, 3, 2, CURDATE() + INTERVAL 1 DAY, '10:30 AM', 'Brake pedal feels slightly spongy.', 79.00, 'In Service'),
(5, 4, 4, 4, NULL, CURDATE() - INTERVAL 2 DAY, '04:00 PM', 'Customer requested cancellation due to travel schedule.', 39.00, 'Cancelled');
