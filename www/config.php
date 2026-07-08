<?php

/**
 * Application Configuration
 */

define('APP_NAME', 'J&C Obenita Construction OPC');
define('COMPANY_SERVICES_OFFERED', 'Auxiliary Airconditioning & Maintenance Rentals');
define('COMPANY_ADDRESS', 'Sacred Homes Subdivision, San Isidro, Talisay City, Cebu');
define('COMPANY_EMAIL', 'jeeconstructionandservices@gmail.com');
define('COMPANY_CONTACT', '+63 925 680 2797');

// Database configuration
define('DB_HOST', 'mariadb');
define('DB_NAME', 'app_db');
define('DB_USER', 'app_user');
define('DB_PASS', 'app_password');
define('DB_CHARSET', 'utf8mb4');

define('INACTIVITY_TIME', 30 * 60 * 1000); // 30 minutes
define('INACTIVITY_THROTTLE', 2000); // 2 seconds throttle

// URL configuration
define('BASE_URL', '/erp'); // Change this to '/' if the app is at the root, or '/another-path'

// Error reporting (set to false in production)
define('SHOW_ERRORS', false);

// Logging
define('LOG_FILE', __DIR__ . '/Logs/app.log');
