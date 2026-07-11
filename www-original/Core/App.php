<?php

namespace Core;

/**
 * Main App Class.
 * Handles routing by parsing the URL and calling the appropriate module, controller, and method.
 * URL Pattern: /Module/Controller/Method/Params
 */
class App
{
    // Default routing values
    protected $application = 'Projects';
    protected $module = 'Quotations';
    protected $controller = 'Main';
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        // Set error and exception handlers
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);

        // Add security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' * data:; font-src 'self';");

        // Parse the URL into an array
        $url = $this->parseUrl();

        // 1. Check for Application (e.g., /Hr/...)
        if (isset($url[0])) {
            $appName = $url[0];
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $appName)) {
                $this->error404();
                return;
            }

            if (file_exists('Applications/' . $appName)) {
                $this->application = $appName;
                unset($url[0]);
            } else {
                $appName = ucfirst(strtolower($appName));
                if (file_exists('Applications/' . $appName)) {
                    $this->application = $appName;
                    unset($url[0]);
                } else {
                    $this->error404();
                    return;
                }
            }
        }

        // 2. Check for Module (e.g., /Hr/Employees/...)
        if (isset($url[1])) {
            $moduleName = $url[1];
            // Try as is, then with ucfirst
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $moduleName)) {
                $this->error404();
                return;
            }

            if (file_exists('Applications/' . $this->application . '/Modules/' . $moduleName)) {
                $this->module = $moduleName;
                unset($url[1]);
            } else {
                $moduleName = ucfirst(strtolower($moduleName));
                if (file_exists('Applications/' . $this->application . '/Modules/' . $moduleName)) {
                    $this->module = $moduleName;
                    unset($url[1]);
                } else {
                    $this->error404();
                    return;
                }
            }
        }

        // 3. Check for Controller (e.g., /Hr/Employees/Main/...)
        if (isset($url[2])) {
            $controllerName = ucfirst($url[2]);
            if (preg_match('/^[a-zA-Z0-9_]+$/', $controllerName) && file_exists('Applications/' . $this->application . '/Modules/' . $this->module . '/Controllers/' . $controllerName . '.php')) {
                $this->controller = $controllerName;
                unset($url[2]);
            } else {
                $this->error404();
                return;
            }
        }

        // Require the controller file
        $controllerFile = 'Applications/' . $this->application . '/Modules/' . $this->module . '/Controllers/' . $this->controller . '.php';
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
        } else {
            $this->error404();
            return;
        }

        // Instantiate the controller with its full namespace
        $fullControllerName = "\\Applications\\" . $this->application . "\\Modules\\" . $this->module . "\\Controllers\\" . $this->controller;
        $this->controller = new $fullControllerName;

        // 4. Check for Method (e.g., /Hr/Employees/Main/index/...)
        if (isset($url[3])) {
            if (method_exists($this->controller, $url[3])) {
                $this->method = $url[3];
                unset($url[3]);
            } else {
                $this->error404();
                return;
            }
        }

        // Check authentication and RBAC
        $this->controller->checkAuth($this->application, $this->module, $this->method);

        // 5. Remaining URL parts are parameters
        $this->params = $url ? array_values($url) : [];

        // Execute the method on the controller with parameters
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Handles 404 Error Routing
     */
    protected function error404()
    {
        http_response_code(404);
        $controller = new \Core\Controllers\Errors();
        $controller->notFound();
    }

    /**
     * Parses the 'url' GET parameter.
     * Example: 'Hr/Dashboard/index' becomes ['Hr', 'Dashboard', 'index']
     */
    public function parseUrl()
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }

        // Fallback for Nginx or servers where "url" parameter is not set
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            // Remove BASE_URL from the beginning of the path
            if (defined('BASE_URL') && BASE_URL !== '/') {
                $baseUrl = rtrim(BASE_URL, '/');
                $url = preg_replace('/^' . preg_quote($baseUrl, '/') . '/', '', $url);
            }

            $url = trim($url, '/');
            if ($url !== '') {
                return explode('/', filter_var($url, FILTER_SANITIZE_URL));
            }
        }

        return [];
    }

    /**
     * Error handler. Convert all errors to Exceptions by throwing an ErrorException.
     */
    public function errorHandler($level, $message, $file, $line)
    {
        if (error_reporting() !== 0) {  // to keep the @ operator working
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Exception handler.
     */
    public function exceptionHandler($exception)
    {
        // Log the exception regardless of SHOW_ERRORS
        \Core\Logger::logException($exception);

        // Code is 404 (not found) or 500 (general error)
        $code = $exception->getCode();
        if ($code != 404) {
            $code = 500;
        }
        http_response_code($code);

        if (SHOW_ERRORS) {
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
        } else {
            if ($code == 404) {
                $this->error404();
            } else {
                echo "<h1>An error occurred</h1>";
                echo "<p>Sorry, an unexpected error occurred. Please try again later.</p>";
            }
        }
    }
}
