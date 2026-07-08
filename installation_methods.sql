CREATE TABLE IF NOT EXISTS `installation_methods` (
  `installation_method_id` INT AUTO_INCREMENT PRIMARY KEY,
  `installation_method_code` VARCHAR(50) NOT NULL UNIQUE,
  `installation_method_name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `installation_methods` (`installation_method_code`, `installation_method_name`, `description`) VALUES
('CLAMP', 'Clamp Support', 'Surface-mounted conduit using clamps'),
('SUSP', 'Suspended', 'Supported by Unistrut and threaded rods'),
('EMB', 'Embedded', 'Concealed inside walls/slabs'),
('ADH', 'Adhesive Mounted', 'Attached using adhesives'),
('RISER_CLAMP', 'Riser - Clamp Mounted', 'Vertical runs with clamps'),
('RISER_UNI', 'Riser - Unistrut Mounted', 'Vertical runs using channels'),
('CABLE_TRAY', 'Cable Tray', 'Cable tray installation'),
('UG', 'Underground Trenching', 'Buried conduit installation'),
('WALL', 'Wall Mounted', 'Mounted directly on wall'),
('CEIL', 'Ceiling Mounted', 'Mounted on ceiling'),
('FLOOR', 'Floor Mounted', 'Mounted on floor/slab'),
('POLE', 'Pole Mounted', 'Mounted on poles');
