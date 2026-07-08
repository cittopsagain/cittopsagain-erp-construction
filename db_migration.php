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

            'sales_overhead_types' => "CREATE TABLE IF NOT EXISTS `sales_overhead_types` (
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
