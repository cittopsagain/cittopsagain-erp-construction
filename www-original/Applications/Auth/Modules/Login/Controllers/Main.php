<?php

namespace Applications\Auth\Modules\Login\Controllers;

use Core\Controller;
use Core\Models\User;

// Note: No longer used. The login functionality has been integrated into the main layout with a modal.

class Main extends Controller
{
    public function index()
    {
        // If already logged in, redirect to home (default app/module)
        if (isset($_SESSION['user_id'])) {
            $redirectUrl = (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/';
            if ($redirectUrl === '') $redirectUrl = '/';
            header('Location: ' . $redirectUrl);
            exit;
        }

        $this->view('Auth', 'Login', 'index', [
            'title' => 'Login'
        ], false);
    }

    public function authenticate()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->json(['success' => false, 'message' => 'Username and password are required.']);
            return;
        }

        try {
            $userModel = new User();
            $user = $userModel->authenticate($username, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_uname'] = $user['user_uname'];
                $_SESSION['user_type'] = $user['user_type'];

                // Load permissions into session
                $_SESSION['permissions'] = $userModel->getPermissions($user['user_id']);

                $userModel->updateLastVisited($user['user_id']);

                $this->json(['success' => true]);
            } else {
                $this->json(['success' => false, 'message' => 'Invalid username or password.']);
            }
        } catch (\Exception $e) {
            \Core\Logger::logException($e);
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function logout()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        $redirectUrl = (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/Auth/Login';
        header('Location: ' . $redirectUrl);
        exit;
    }

    public function getUser()
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $_SESSION['user_name'];
    }
}
