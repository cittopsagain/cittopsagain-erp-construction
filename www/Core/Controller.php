<?php

namespace Core;

use Core\Logger;

/**
 * Base Controller Class.
 * All controllers in the application should extend this class.
 */
class Controller
{
    /**
     * Start the session if not already started.
     */
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

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

    /**
     * Check if the user is logged in.
     */
    public function isLoggedIn()
    {
        return !empty($this->getCurrentUserId());
    }

    /**
     * Check if the request is an AJAX request.
     *
     * @return bool
     */
    public function isAjax()
    {
        return (isset($_POST['ajax']) || isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
    }

    /**
     * Check authentication and redirect to login if not authenticated.
     * Excludes the Auth application.
     */
    public function checkAuth($application, $module = null, $action = null)
    {
        if ($application !== 'Auth' && !$this->isLoggedIn()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Session expired. Please login again.', 'session_expired' => true]);
                exit;
            }
            // Redirect to login page for regular requests
            $redirectUrl = (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/Auth/Login';
            header('Location: ' . $redirectUrl);
            exit;
        }

        // Redirect to home if logged in and trying to access login page
        if ($application === 'Auth' && $module === 'Login' && ($action === 'index' || $action === null) && $this->isLoggedIn()) {
            $redirectUrl = (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/';
            if ($redirectUrl === '') $redirectUrl = '/';
            header('Location: ' . $redirectUrl);
            exit;
        }

        // RBAC check
        if ($application !== 'Auth' && $this->isLoggedIn()) {
            if (!$this->hasPermission($application, $module, $action)) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Access Denied. You do not have permission to perform this action.']);
                    exit;
                } else {
                    // For regular requests, maybe show 403 or redirect
                    die("Access Denied.");
                }
            }
        }
    }

    /**
     * Check if the current user has permission for a specific application/module/action.
     */
    public function hasPermission($application, $module = null, $action = null)
    {
        // 1. If user_type is 1 (Admin), allow everything.
        if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1) {
            return true;
        }

        $permissions = $_SESSION['permissions'] ?? [];

        // Check for specific permission "App.Module.Action"
        $permKey = $application;
        if ($module) $permKey .= "." . $module;
        if ($action) $permKey .= "." . $action;

        if (in_array($permKey, $permissions)) {
            return true;
        }

        // Check for wildcard permission "App.Module.*"
        if ($module) {
            if (in_array($application . "." . $module . ".*", $permissions)) {
                return true;
            }
        }

        // Check for wildcard permission "App.*"
        if (in_array($application . ".*", $permissions)) {
            return true;
        }

        // For now, allow everything except Administration for non-admins if no permissions are set
        // This maintains backward compatibility with existing users who might not have permissions assigned yet.
        if (empty($permissions)) {
            if ($application === 'Administration') {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Escape HTML output to prevent XSS.
     */
    public function escape($string)
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escape for use in JavaScript strings.
     */
    public function escapeJs($string)
    {
        return json_encode($string ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Load a model from a specific application and module.
     */
    public function model($application, $module, $model)
    {
        // Validate application, module and model names
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $application) || !preg_match('/^[a-zA-Z0-9_]+$/', $module) || !preg_match('/^[a-zA-Z0-9_]+$/', $model)) {
            die("Invalid model reference.");
        }
        $fullModelName = "\\Applications\\" . $application . "\\Modules\\" . $module . "\\Models\\" . $model;
        return new $fullModelName();
    }

    /**
     * Get the application display name map.
     */
    protected function getAppMap()
    {
        return [
            'Hr' => 'HR & Payroll',
            'Po' => 'Purchase Order',
            'Accounting' => 'Accounting',
            'Sales' => 'Sales',
            'Administration' => 'Administration',
            'Admin' => 'Administration',
            'Costing' => 'Costing'
        ];
    }

    /**
     * Get the display name for an application.
     */
    protected function getAppDisplayName($app)
    {
        $map = $this->getAppMap();
        return $map[$app] ?? $app;
    }

    /**
     * Get the icon class for an application.
     */
    protected function getAppIcon($app)
    {
        $icons = [
            'Accounting' => 'x-fa fa-calculator',
            'Administration' => 'x-fa fa-cogs',
            'Hr' => 'x-fa fa-users',
            'Inventory' => 'x-fa fa-archive',
            'Projects' => 'x-fa fa-tasks',
            'Sales' => 'x-fa fa-shopping-cart'
        ];

        return $icons[$app] ?? 'x-fa fa-folder';
    }

    /**
     * Get the icon class for a module group.
     */
    protected function getGroupIcon($group)
    {
        $icons = [
            'Masterlist' => 'x-fa fa-list',
            'Security' => 'x-fa fa-lock',
            'Organization' => 'x-fa fa-sitemap',
            'Employee Management' => 'x-fa fa-list',
            'Quotations' => 'x-fa fa-file-text-o'
        ];

        return $icons[$group] ?? 'x-fa fa-folder';
    }

    /**
     * Get the display name for a module.
     */
    protected function getModuleDisplayName($app, $module)
    {
        $configPath = 'Applications/' . $app . '/Modules/' . $module . '/config.php';
        if (file_exists($configPath)) {
            $config = include $configPath;
            if (isset($config['display_name'])) {
                return $config['display_name'];
            }
        }

        return $module;
    }

    /**
     * Get applications and their modules for navigation.
     */
    protected function getNavigationData()
    {
        $nav = [];
        $appsPath = 'Applications';
        if (is_dir($appsPath)) {
            $apps = array_diff(scandir($appsPath), ['.', '..']);
            foreach ($apps as $app) {
                // Ensure the app name is alphanumeric to prevent directory traversal
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $app)) {
                    continue;
                }
                // Do not show the Auth in the navigation
                if ($app === 'Auth') {
                    continue;
                }
                if (is_dir($appsPath . '/' . $app)) {
                    $modulesPath = $appsPath . '/' . $app . '/Modules';
                    $groupedModules = [];
                    if (is_dir($modulesPath)) {
                        $moduleDirs = array_diff(scandir($modulesPath), ['.', '..']);
                        foreach ($moduleDirs as $module) {
                            if (preg_match('/^[a-zA-Z0-9_]+$/', $module) && is_dir($modulesPath . '/' . $module)) {
                                $configPath = $modulesPath . '/' . $module . '/config.php';
                                $parent = '';
                                $text = $this->getModuleDisplayName($app, $module);
                                $iconCls = 'x-fa fa-file-o';

                                if (file_exists($configPath)) {
                                    $config = include $configPath;
                                    $parent = $config['parent'] ?? '';
                                    if (isset($config['iconCls'])) {
                                        $iconCls = $config['iconCls'];
                                    }
                                }

                                $moduleData = [
                                    'id' => $module,
                                    'text' => $text,
                                    'iconCls' => $iconCls
                                ];

                                if (!empty($parent)) {
                                    if (!isset($groupedModules[$parent])) {
                                        $groupedModules[$parent] = [
                                            'text' => $parent,
                                            'isGroup' => true,
                                            'iconCls' => $this->getGroupIcon($parent),
                                            'children' => []
                                        ];
                                    }
                                    $groupedModules[$parent]['children'][] = $moduleData;
                                } else {
                                    $groupedModules[$module] = $moduleData;
                                }
                            }
                        }
                    }

                    $nav[] = [
                        'app' => $app,
                        'text' => $this->getAppDisplayName($app),
                        'iconCls' => $this->getAppIcon($app),
                        'modules' => array_values($groupedModules)
                    ];
                }
            }
        }
        return $nav;
    }

    /**
     * Load a view file from a specific application and module and pass data to it.
     */
    public function view($application, $module, $view, $data = [], $withHeaderFooter = true)
    {
        // Add CSRF token to all views
        $data['csrf_token'] = $this->generateCsrfToken();
        $data['navigation'] = $this->getNavigationData();

        // Validate application and module names
        if ($application !== 'Core' && (!preg_match('/^[a-zA-Z0-9_]+$/', $application) || !preg_match('/^[a-zA-Z0-9_]+$/', $module))) {
            die("Invalid application or module name.");
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $view)) {
            die("Invalid view name.");
        }

        extract($data);

        // If title is not set, provide a default
        if (!isset($title)) {
            $title = "ERP System";
        }

        if ($withHeaderFooter) {
            require_once 'Core/Views/header.php';
        }

        // Require the view file so it can be rendered.
        // The $data array is now accessible within the view file.
        if ($application === 'Core') {
            $viewPath = 'Core/Views/' . $view . '.php';
            if (file_exists($viewPath)) {
                require_once $viewPath;
            }
        } else {
            $viewPath = 'Applications/' . $application . '/Modules/' . $module . '/Views/' . $view . '.php';
            if (file_exists($viewPath)) {
                require_once $viewPath;
            }
        }

        if ($withHeaderFooter) {
            require_once 'Core/Views/footer.php';
        }
    }

    /**
     * Render the centralized layout.
     */
    public function layout($application, $module, $view, $data = [])
    {
        // Validate application, module and view names
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $application) || !preg_match('/^[a-zA-Z0-9_]+$/', $module)) {
            die("Invalid application or module name.");
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $view)) {
            die("Invalid view name.");
        }

        if ($this->isAjax()) {
            // Capture view output
            ob_start();
            $viewPath = 'Applications/' . $application . '/Modules/' . $module . '/Views/' . $view . '.php';
            if (file_exists($viewPath)) {
                extract($data);
                require $viewPath;
            }
            $html = ob_get_clean();

            if (!isset($data['content_html'])) {
                $data['content_html'] = $html;
            }

            if (isset($data['content_xtype'])) {
                $this->json($data);
                return;
            }

            $this->json($data);
            return;
        }

        // Add CSRF token
        $data['csrf_token'] = $this->generateCsrfToken();
        $data['navigation'] = $this->getNavigationData();
        $data['app_map'] = $this->getAppMap();
        $data['current_app'] = $application;
        $data['current_module'] = $module;

        extract($data);

        if (!isset($title)) {
            $title = "ERP System";
        }

        require_once 'Core/Views/header.php';

        // Instead of requiring the view directly, we might want to capture its output if it's HTML
        // or just let it define an Ext JS component.

        // For simplicity, we'll let the view define an xtype or some content
        // and then require the main layout.

        $viewPath = 'Applications/' . $application . '/Modules/' . $module . '/Views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        }

        require_once 'Core/Views/main_layout.php';
        require_once 'Core/Views/footer.php';
    }

    /**
     * Return JSON response.
     */
    public function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function logToConsole($data)
    {
        echo '<script>console.log(' . json_encode($data) . ');</script>';
    }

    /**
     * Generate a CSRF token and store it in the session.
     */
    protected function generateCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate a CSRF token.
     */
    protected function validateCsrfToken($token)
    {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        return false;
    }

    /**
     * Log a message to the text file.
     *
     * @param string $message
     * @param string $level
     */
    protected function log($message, $level = Logger::INFO)
    {
        Logger::log($message, $level);
    }
}
