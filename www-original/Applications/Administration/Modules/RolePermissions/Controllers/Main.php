<?php

namespace Applications\Administration\Modules\RolePermissions\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Role Permissions";
        $data['content_xtype'] = 'role-permissions-panel';
        $this->layout('Administration', 'RolePermissions', 'index', $data);
    }

    public function roles()
    {
        $model = $this->model('Administration', 'RolePermissions', 'RolePermission');
        $this->json(['success' => true, 'data' => $model->getRoles()]);
    }

    public function permissions()
    {
        $model = $this->model('Administration', 'RolePermissions', 'RolePermission');
        $this->json(['success' => true, 'data' => $model->getPermissions()]);
    }

    public function role_permissions()
    {
        $roleId = $_GET['role_id'] ?? null;
        if (!$roleId) {
            $this->json(['success' => false, 'message' => 'Role ID is required.']);
            return;
        }

        $model = $this->model('Administration', 'RolePermissions', 'RolePermission');
        $this->json(['success' => true, 'data' => $model->getRolePermissions($roleId)]);
    }

    public function save()
    {
        $roleId = $_POST['role_id'] ?? null;
        $permissionIds = $_POST['permission_ids'] ?? [];

        if (is_string($permissionIds)) {
            $permissionIds = json_decode($permissionIds, true);
        }

        if (!$roleId) {
            $this->json(['success' => false, 'message' => 'Role ID is required.']);
            return;
        }

        $model = $this->model('Administration', 'RolePermissions', 'RolePermission');
        if ($model->saveRolePermissions($roleId, $permissionIds)) {
            $this->json(['success' => true, 'message' => 'Permissions saved successfully.']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to save permissions.']);
        }
    }
}
