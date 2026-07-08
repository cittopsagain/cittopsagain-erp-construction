<?php

namespace Core;

/**
 * Logger class for saving messages to a text file.
 */
class Logger
{
    /**
     * Log levels
     */
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const DEBUG = 'DEBUG';

    /**
     * Log a message to the log file defined in config.php.
     *
     * @param string $message The message to log.
     * @param string $level The log level (INFO, WARNING, ERROR, DEBUG).
     * @return bool True if successful, false otherwise.
     */
    public static function log($message, $level = self::INFO)
    {
        if (!defined('LOG_FILE')) {
            return false;
        }

        $date = date('Y-m-d H:i:s');
        $message = self::sanitize($message);
        $formattedMessage = "[$date] [$level] $message" . PHP_EOL;

        // Ensure the directory exists
        $dir = dirname(LOG_FILE);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return false;
            }
            chmod($dir, 0777);
        }

        if (file_exists(LOG_FILE)) {
            if (!is_writable(LOG_FILE)) {
                @chmod(LOG_FILE, 0666);
            }
        }

        $result = @file_put_contents(LOG_FILE, $formattedMessage, FILE_APPEND);

        if ($result !== false && file_exists(LOG_FILE) && (fileperms(LOG_FILE) & 0777) !== 0666) {
            @chmod(LOG_FILE, 0666);
        }

        return $result !== false;
    }

    /**
     * Shortcut for logging an error message.
     */
    public static function error($message)
    {
        return self::log($message, self::ERROR);
    }

    /**
     * Shortcut for logging an info message.
     */
    public static function info($message)
    {
        return self::log($message, self::INFO);
    }

    /**
     * Shortcut for logging a warning message.
     */
    public static function warning($message)
    {
        return self::log($message, self::WARNING);
    }

    /**
     * Shortcut for logging a debug message.
     */
    public static function debug($message)
    {
        return self::log($message, self::DEBUG);
    }

    /**
     * Log an exception.
     *
     * @param \Throwable $e The exception to log.
     * @return bool
     */
    public static function logException(\Throwable $e)
    {
        $message = "Uncaught exception: '" . get_class($e) . "'";
        $message .= " with message '" . $e->getMessage() . "'";
        $message .= "\nStack trace: " . $e->getTraceAsString();
        $message .= "\nThrown in '" . $e->getFile() . "' on line " . $e->getLine();

        return self::error($message);
    }

    /**
     * Sanitize sensitive information from a string.
     *
     * @param string $string
     * @return string
     */
    private static function sanitize($string)
    {
        // Patterns to match sensitive data in stack traces and messages
        $patterns = [
            // Matches authenticate('user', 'pass') or authenticate("user", "pass")
            '/(authenticate\([\'"])(.*)([\'"]\s*,\s*[\'"])(.*)([\'"]\))/U' => '$1[REDACTED]$3[REDACTED]$5',

            // Matches password in various formats (e.g. "password" => "value")
            '/([\'"](password|pword|pass)[\'"]\s*(=>|:)\s*[\'"])(.*)([\'"])/Ui' => '$1[REDACTED]$5',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $string);
    }
}
