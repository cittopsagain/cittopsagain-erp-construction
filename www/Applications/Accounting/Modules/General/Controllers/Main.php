<?php

namespace Applications\Accounting\Modules\General\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Accounting Overview";
        $data['content_html'] = "Welcome to the Accounting application, General module.";
        $this->layout('Accounting', 'General', 'index', $data);
    }
}
