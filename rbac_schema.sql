-- RBAC Schema for ERP System

-- Roles Table
CREATE TABLE IF NOT EXISTS `app_roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Permissions Table
CREATE TABLE IF NOT EXISTS `app_permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Role Permissions Mapping (which role has which permissions)
CREATE TABLE IF NOT EXISTS `app_role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `app_roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `app_permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Roles Mapping (which user has which roles)
CREATE TABLE IF NOT EXISTS `app_user_roles` (
    `user_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    -- Assuming user_id refers to app_users(user_id)
    FOREIGN KEY (`role_id`) REFERENCES `app_roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial Data
INSERT IGNORE INTO `app_roles` (`id`, `name`, `description`) VALUES
(1, 'Administrator', 'Full access to all modules'),
(2, 'HR Manager', 'Access to HR modules'),
(3, 'Employee', 'Basic access');

INSERT IGNORE INTO `app_permissions` (`id`, `name`, `description`) VALUES
(1, 'Hr.Employees.index', 'View employee list'),
(2, 'Hr.Employees.save', 'Add/Edit employees'),
(3, 'Hr.Employees.delete', 'Delete employees'),
(4, 'Administration.*', 'Manage system roles and permissions');
