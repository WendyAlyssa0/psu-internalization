
CREATE DATABASE IF NOT EXISTS `internalization_management` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `internalization_management`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `extension_name` varchar(50) DEFAULT NULL,
  `email` varchar(190) NOT NULL,
  `user_role` varchar(50) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `first_name`, `last_name`, `extension_name`, `email`, `contact`, `user_role`, `birthdate`, `status`, `password_hash`, `created_at`) VALUES
(1, 'Admin', 'User', '', 'lucasgraham@gmail.com', '', 'admin', '2000-01-01', 'active', '$2y$12$KHzHJ25q/.eju7zxXndOreLy7n046mMX4vs6NkxBXBylFcSxAZgTW', '2026-05-24 14:23:05');


ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`user_role`),
  ADD KEY `idx_users_status` (`status`);


ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- -----------------------------------------------------------------------------
-- Applications (Academic-style)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `applications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,

  -- References the applicant (users.id)
  `applicant_id` INT(11) NOT NULL,

  -- Academic details
  `program` VARCHAR(150) DEFAULT NULL,
  `department` VARCHAR(150) DEFAULT NULL,

  -- Optional details
  `application_reference` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,

  -- Workflow status
  `status` VARCHAR(30) NOT NULL DEFAULT 'submitted',

  -- Timestamps
  `submitted_at` TIMESTAMP NULL DEFAULT NULL,
  `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),

  KEY `fk_applicant_id` (`applicant_id`),

  CONSTRAINT `fk_applicant`
    FOREIGN KEY (`applicant_id`) REFERENCES `users`(`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    institution_name VARCHAR(255) NOT NULL,
    country VARCHAR(100),
    contact_person VARCHAR(255),
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_name VARCHAR(255),
    action VARCHAR(255),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);