<?php

namespace Applications\Administration\Modules\Roles\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Roles Management";
        $data['content_xtype'] = 'roles-grid';
        $this->layout('Administration', 'Roles', 'index', $data);
    }

    public function data()
    {
        $roleModel = $this->model('Administration', 'Roles', 'Role');
        $roles = $roleModel->getAll();
        $this->json($roles);
    }

    public function save()
    {
        $data = $_POST;
        $roleModel = $this->model('Administration', 'Roles', 'Role');
        if ($roleModel->save($data)) {
            $this->json(['success' => true, 'message' => 'Role saved successfully.']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to save role.']);
        }
    }

    public function delete()
    {
        $id = $_POST['id'] ?? null;
        $roleModel = $this->model('Administration', 'Roles', 'Role');
        if ($roleModel->delete($id)) {
            $this->json(['success' => true, 'message' => 'Role deleted successfully.']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete role.']);
        }
    }
}
