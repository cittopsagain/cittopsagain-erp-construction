CREATE TABLE IF NOT EXISTS `inv_material_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_code` varchar(50) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_code` (`group_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
