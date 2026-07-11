<?php

namespace Applications\Sales\Modules\Invoices\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Sales Invoices";
        $data['content_html'] = "Welcome to the Sales application, Invoices module.";
        $this->layout('Sales', 'Invoices', 'index', $data);
    }
}
