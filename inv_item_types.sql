CREATE TABLE IF NOT EXISTS `inv_item_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_code` varchar(50) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_code` (`type_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
