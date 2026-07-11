<?php

namespace Applications\Administration\Modules\Permissions\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Permissions Management";
        $data['content_xtype'] = 'permissions-grid';
        $this->layout('Administration', 'Permissions', 'index', $data);
    }

    public function data()
    {
        $permissionModel = $this->model('Administration', 'Permissions', 'Permission');
        $permissions = $permissionModel->getAll();
        $this->json($permissions);
    }

    public function save()
    {
        $data = $_POST;
        $permissionModel = $this->model('Administration', 'Permissions', 'Permission');
        if ($permissionModel->save($data)) {
            $this->json(['success' => true, 'message' => 'Permission saved successfully.']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to save permission.']);
        }
    }

    public function delete()
    {
        $id = $_POST['id'] ?? null;
        $permissionModel = $this->model('Administration', 'Permissions', 'Permission');
        if ($permissionModel->delete($id)) {
            $this->json(['success' => true, 'message' => 'Permission deleted successfully.']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete permission.']);
        }
    }
}
