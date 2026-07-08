CREATE TABLE IF NOT EXISTS `inv_item_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_cat_code` varchar(50) NOT NULL,
  `item_cat_name` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_cat_code` (`item_cat_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
