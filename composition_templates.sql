CREATE TABLE IF NOT EXISTS `composition_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_code` varchar(50) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `installation_method_id` int(11) DEFAULT NULL,
  `item_type_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_code` (`template_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `composition_template_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `seq` int(11) NOT NULL DEFAULT 0,
  `description` varchar(255) DEFAULT NULL,
  `formula` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  CONSTRAINT `composition_template_details_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `composition_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
