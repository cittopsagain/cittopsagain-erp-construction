<?php

namespace Core;

use PDO;
use PDOException;

/**
 * Base Model Class.
 * Handles database connectivity using PDO.
 */
class Model
{
    // The database connection object
    protected $db;

    /**
     * Get the current logged in user ID.
     *
     * @return mixed|null
     */
    protected function getCurrentUserId()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }

    protected function getCurrentUser()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_name'] ?? null;
    }

    public function __construct()
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Return results as associative arrays
            PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
        ];

        try {
            // Establish the database connection
            $this->db = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log this error
            \Core\Logger::logException($e);

            if (SHOW_ERRORS) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }
}
