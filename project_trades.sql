CREATE TABLE IF NOT EXISTS `project_trades` (
    `trade_id` INT AUTO_INCREMENT PRIMARY KEY,
    `trade_code` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NOT NULL,
    `long_description` TEXT DEFAULT NULL,
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `project_trades` (`trade_code`, `description`) VALUES 
('ELECTRICAL', 'Electrical'),
('FDAS', 'FDAS'),
('CCTV', 'CCTV'),
('CABLING', 'Structured Cabling'),
('SOLAR', 'Solar');
