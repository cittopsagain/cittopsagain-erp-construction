<?php
require_once 'www/config.php';
require_once 'www/Core/Logger.php';
require_once 'www/Core/Model.php';

class DatabaseFixer
{
    protected $db;

    public function __construct()
    {
        // Try container host first (mariadb)
        $hosts = [DB_HOST, '127.0.0.1'];
        $port = '3308';
        $connected = false;

        foreach ($hosts as $host) {
            $dsn = "mysql:host=$host;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            if ($host === '127.0.0.1') {
                $dsn .= ";port=$port";
            }

            try {
                $this->db = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                $connected = true;
                echo "Connected to database via $host\n";
                break;
            } catch (PDOException $e) {
                // Silently try next host
            }
        }

        if (!$connected) {
            die("Database connection failed for both container and host access.\n");
        }
    }

    public function fix()
    {
        $tables = [
            'app_users' => "CREATE TABLE IF NOT EXISTS `app_users` (
                `user_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_uname` varchar(50) NOT NULL,
                `user_pword` varchar(255) NOT NULL,
                `user_name` varchar(100) DEFAULT NULL,
                `blocked` tinyint(1) DEFAULT 0,
                `is_deleted` tinyint(1) DEFAULT 0,
                `date_lastvisted` datetime DEFAULT NULL,
                PRIMARY KEY (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'project_components' => "CREATE TABLE IF NOT EXISTS `project_components` (
                `component_id` int(11) NOT NULL AUTO_INCREMENT,
                `component_code` varchar(50) NOT NULL,
                `description` varchar(255) DEFAULT NULL,
                `display_order` int(11) DEFAULT 0,
                `created_by` int(11) DEFAULT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`component_id`),
                UNIQUE KEY `idx_component_code` (`component_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'project_component_items' => "CREATE TABLE IF NOT EXISTS `project_component_items` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `component_id` int(11) NOT NULL,
                `item_code` varchar(50) DEFAULT NULL,
                `description` varchar(255) NOT NULL,
                `unit` varchar(20) DEFAULT NULL,
                `price` decimal(15,2) DEFAULT 0.00,
                `created_by` int(11) DEFAULT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'project_overhead_categories' => "CREATE TABLE IF NOT EXISTS `project_overhead_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(50) NOT NULL,
                `description` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'project_types' => "CREATE TABLE IF NOT EXISTS `project_types` (
                `type_id` int(11) NOT NULL AUTO_INCREMENT,
                `type_code` varchar(50) NOT NULL,
                `description` varchar(255) DEFAULT NULL,
                `long_description` text DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`type_id`),
                UNIQUE KEY `idx_type_code` (`type_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'sales_client' => "CREATE TABLE IF NOT EXISTS `sales_client` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `client_code` varchar(50) DEFAULT NULL,
                `client_name` varchar(255) NOT NULL,
                `add1` text DEFAULT NULL,
                `add2` text DEFAULT NULL,
                `tel_no` varchar(50) DEFAULT NULL,
                `fax_no` varchar(50) DEFAULT NULL,
                `business_type` varchar(100) DEFAULT NULL,
                `tin_no` varchar(50) DEFAULT NULL,
                `pwd_no` varchar(50) DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,
                `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_client_code` (`client_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'sales_unit' => "CREATE TABLE IF NOT EXISTS `sales_unit` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `unit_code` varchar(20) NOT NULL,
                `description` varchar(100) DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_unit_code` (`unit_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'inv_items' => "CREATE TABLE IF NOT EXISTS `inv_items` (
                `item_id` int(11) NOT NULL AUTO_INCREMENT,
                `item_code` varchar(50) NOT NULL,
                `item_desc` varchar(255) DEFAULT NULL,
                `item_cat` int(11) DEFAULT NULL,
                `material_group` int(11) DEFAULT NULL,
                `item_type` int(11) DEFAULT NULL,
                `currency` varchar(10) DEFAULT 'PHP',
                `qty` decimal(15,4) DEFAULT 0.0000,
                `unit` int(11) DEFAULT NULL,
                `reorder_level` decimal(15,4) DEFAULT 0.0000,
                `maximum_stock` decimal(15,4) DEFAULT 0.0000,
                `default_purchase_cost` decimal(15,2) DEFAULT 0.00,
                `created_by` int(11) DEFAULT NULL,
                `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
                `date_modified` timestamp NULL DEFAULT NULL,
                `modified_by` int(11) DEFAULT NULL,
                PRIMARY KEY (`item_id`),
                UNIQUE KEY `idx_item_code` (`item_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'sales_quotation_header' => "CREATE TABLE IF NOT EXISTS `sales_quotation_header` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_type_code` varchar(50) DEFAULT NULL,
                `project_name` varchar(255) DEFAULT NULL,
                `quot_ctrl_no` varchar(50) NOT NULL,
                `client_code` varchar(50) DEFAULT NULL,
                `contact_person` varchar(100) DEFAULT NULL,
                `terms` text DEFAULT NULL,
                `term_remarks` text DEFAULT NULL,
                `discount` decimal(15,2) DEFAULT 0.00,
                `remarks` text DEFAULT NULL,
                `status` varchar(20) DEFAULT 'SAVED',
                `buildings_data` longtext DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_quot_ctrl_no` (`quot_ctrl_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'sales_quotation_details' => "CREATE TABLE IF NOT EXISTS `sales_quotation_details` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `quot_ctrl_no` varchar(50) NOT NULL,
                `project_component_code` varchar(50) DEFAULT NULL,
                `unit_code` varchar(20) DEFAULT NULL,
                `item_code` varchar(50) DEFAULT NULL,
                `qty` decimal(15,4) DEFAULT 0.0000,
                `item_desc` text DEFAULT NULL,
                `price` decimal(15,2) DEFAULT 0.00,
                `detail_type` varchar(20) DEFAULT 'BOQ',
                `markup_percent` decimal(10,2) DEFAULT 0.00,
                `no_of_men` int(11) DEFAULT 0,
                `days` int(11) DEFAULT 0,
                `hours` int(11) DEFAULT 0,
                `ot_hrs` int(11) DEFAULT 0,
                `ot_rate` decimal(15,2) DEFAULT 0.00,
                `overhead_computation_type` varchar(20) DEFAULT 'Fixed',
                `overhead_value` decimal(15,2) DEFAULT 0.00,
                `total_price` decimal(15,2) DEFAULT 0.00,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'sales_quotation_terms_conditions' => "CREATE TABLE IF NOT EXISTS `sales_quotation_terms_conditions` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `quot_ctrl_no` varchar(50) NOT NULL,
                `section` varchar(100) DEFAULT NULL,
                `description` text DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'project_markup_categories' => "CREATE TABLE IF NOT EXISTS `project_markup_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(50) NOT NULL,
                `description` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'project_markup_types' => "CREATE TABLE IF NOT EXISTS `project_markup_types` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(50) NOT NULL,
                `markup_type` varchar(255) NOT NULL,
                `category` varchar(100) DEFAULT NULL,
                `calculation_method` varchar(255) DEFAULT NULL,
                `purpose` text DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'project_overhead_types' => "CREATE TABLE IF NOT EXISTS `project_overhead_types` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(50) NOT NULL,
                `overhead_type` varchar(255) NOT NULL,
                `category` varchar(100) DEFAULT NULL,
                `calculation_method` varchar(255) DEFAULT NULL,
                `default_rate` decimal(15,2) DEFAULT 0.00,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'boq_headers' => "CREATE TABLE IF NOT EXISTS `boq_headers` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `boq_no` varchar(50) NOT NULL,
                `project_name` varchar(255) DEFAULT NULL,
                `client_code` varchar(50) DEFAULT NULL,
                `location` varchar(255) DEFAULT NULL,
                `revision` varchar(20) DEFAULT 'Rev. 0',
                `status` varchar(50) DEFAULT 'Draft',
                `remarks` text DEFAULT NULL,
                `service_id` int(11) DEFAULT NULL,
                `system_id` int(11) DEFAULT NULL,
                `trade_id` int(11) DEFAULT NULL,
                `installation_method_id` int(11) DEFAULT NULL,
                `phase_id` int(11) DEFAULT NULL,
                `composition_template_id` int(11) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `created_by` int(11) DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
                `updated_by` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `boq_no` (`boq_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'boq_details' => "CREATE TABLE IF NOT EXISTS `boq_details` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `boq_id` int(11) NOT NULL,
                `composition_template_id` int(11) DEFAULT NULL,
                `location_id` int(11) DEFAULT NULL,
                `service_id` int(11) DEFAULT NULL,
                `trade_id` int(11) DEFAULT NULL,
                `system_id` int(11) DEFAULT NULL,
                `installation_method_id` int(11) DEFAULT NULL,
                `description` text DEFAULT NULL,
                `quantity` decimal(15,4) DEFAULT 0.0000,
                PRIMARY KEY (`id`),
                KEY `idx_boq_id` (`boq_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'location_types' => "CREATE TABLE IF NOT EXISTS `location_types` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(50) NOT NULL,
                `name` varchar(100) NOT NULL,
                `parent_allowed` varchar(255) DEFAULT NULL,
                `description` text,
                `active` tinyint(1) DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_location_types_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'project_locations' => "CREATE TABLE IF NOT EXISTS `project_locations` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(50) NOT NULL,
                `name` varchar(100) NOT NULL,
                `type_id` int(11) DEFAULT NULL,
                `parent_id` int(11) DEFAULT NULL,
                `boq_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_project_locations_code_boq` (`code`, `boq_id`),
                KEY `idx_project_locations_type_id` (`type_id`),
                KEY `idx_project_locations_parent_id` (`parent_id`),
                KEY `idx_project_locations_boq_id` (`boq_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'composition_templates' => "CREATE TABLE IF NOT EXISTS `composition_templates` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `template_code` varchar(50) NOT NULL,
                `template_name` varchar(255) NOT NULL,
                `installation_method_id` int(11) DEFAULT NULL,
                `trade_id` int(11) DEFAULT NULL,
                `phase_id` int(11) DEFAULT NULL,
                `system_id` int(11) DEFAULT NULL,
                `service_id` int(11) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `created_by` int(11) DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
                `updated_by` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `template_code` (`template_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            'composition_template_details' => "CREATE TABLE IF NOT EXISTS `composition_template_details` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `template_id` int(11) NOT NULL,
                `detail_type` ENUM('MATERIAL', 'LABOR') DEFAULT 'MATERIAL',
                `inventory_item_id` int(11) DEFAULT NULL,
                `seq` int(11) NOT NULL DEFAULT 0,
                `description` varchar(255) DEFAULT NULL,
                `qty_formula` text DEFAULT NULL,
                `waste_percentage` decimal(10,2) DEFAULT 0.00,
                `remarks` text DEFAULT NULL,
                `role` varchar(255) DEFAULT NULL,
                `hours` decimal(10,2) DEFAULT 0.00,
                `rate` decimal(10,2) DEFAULT 0.00,
                `formula` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `created_by` int(11) DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                `updated_by` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `template_id` (`template_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        ];

        foreach ($tables as $name => $sql) {
            echo "Checking table: $name... ";
            try {
                $this->db->exec($sql);
                echo "OK.\n";
            } catch (\PDOException $e) {
                echo "Error creating table $name: " . $e->getMessage() . "\n";
            }
        }

        // Additional column checks (ALTER TABLE)
        $this->checkAndAddColumn('project_components', 'display_order', "INT DEFAULT 0");
        $this->checkAndAddColumn('project_component_items', 'item_code', "VARCHAR(50) AFTER component_id");
        $this->checkAndAddColumn('sales_quotation_header', 'buildings_data', "LONGTEXT NULL");
        $this->checkAndAddColumn('sales_quotation_header', 'project_name', "VARCHAR(255) NULL AFTER project_type_code");
        $this->checkAndAddColumn('sales_quotation_header', 'status', "VARCHAR(20) DEFAULT 'SAVED'");
        $this->checkAndAddColumn('sales_quotation_details', 'detail_type', "VARCHAR(20) DEFAULT 'BOQ'");
        $this->checkAndAddColumn('sales_quotation_details', 'markup_percent', "DECIMAL(10, 2) DEFAULT 0.00");
        $this->checkAndAddColumn('sales_quotation_details', 'no_of_men', "INT DEFAULT 0");
        $this->checkAndAddColumn('sales_quotation_details', 'days', "INT DEFAULT 0");
        $this->checkAndAddColumn('sales_quotation_details', 'hours', "INT DEFAULT 0");
        $this->checkAndAddColumn('sales_quotation_details', 'ot_hrs', "INT DEFAULT 0");
        $this->checkAndAddColumn('sales_quotation_details', 'ot_rate', "DECIMAL(15, 2) DEFAULT 0.00");
        $this->checkAndAddColumn('sales_quotation_details', 'overhead_computation_type', "VARCHAR(20) DEFAULT 'Fixed'");
        $this->checkAndAddColumn('sales_quotation_details', 'overhead_value', "DECIMAL(15, 2) DEFAULT 0.00");
        $this->checkAndAddColumn('sales_quotation_details', 'total_price', "DECIMAL(15, 2) DEFAULT 0.00");

        $this->checkAndAddColumn('boq_details', 'service_id', "INT DEFAULT NULL");
        $this->checkAndAddColumn('boq_details', 'trade_id', "INT DEFAULT NULL");
        $this->checkAndAddColumn('boq_details', 'system_id', "INT DEFAULT NULL");
        $this->checkAndAddColumn('boq_details', 'installation_method_id', "INT DEFAULT NULL");
        $this->checkAndAddColumn('boq_details', 'location_id', "INT DEFAULT NULL");
        $this->checkAndAddColumn('project_locations', 'boq_id', "INT DEFAULT NULL");

        // Fix unique key for project_locations
        try {
            $this->db->exec("ALTER TABLE `project_locations` DROP INDEX `idx_project_locations_code` ");
        } catch (PDOException $e) {
            // Key might not exist
        }
        try {
            $this->db->exec("ALTER TABLE `project_locations` ADD UNIQUE KEY `idx_project_locations_code_boq` (`code`, `boq_id`) ");
        } catch (PDOException $e) {
            // Key might already exist
        }
    }

    private function checkAndAddColumn($table, $column, $definition)
    {
        echo "Checking column $column in $table... ";
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            if ($stmt->rowCount() == 0) {
                $this->db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
                echo "Added.\n";
            } else {
                echo "Exists.\n";
            }
        } catch (\PDOException $e) {
            echo "Error checking/adding column: " . $e->getMessage() . "\n";
        }
    }
}

$fixer = new DatabaseFixer();
$fixer->fix();
