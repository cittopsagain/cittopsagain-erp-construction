<?php

namespace Core\Controllers;

use Core\Controller;

class Errors extends Controller
{
    public function notFound()
    {
        $data['title'] = "404 Not Found";
        $this->view('Core', '', '404', $data);
    }
}
