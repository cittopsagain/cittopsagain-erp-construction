<?php

namespace Applications\Administration\Modules\Permissions\Models;

use Core\Model;
use PDO;

class Permission extends Model
{
    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM app_permissions");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Mock data for permissions
            return [
                ['id' => 1, 'name' => 'view_employees', 'description' => 'Can view employee list'],
                ['id' => 2, 'name' => 'edit_employees', 'description' => 'Can edit employee details'],
                ['id' => 3, 'name' => 'manage_roles', 'description' => 'Can manage roles and permissions']
            ];
        }
    }

    public function save($data)
    {
        return true;
    }

    public function update($data)
    {
        return true;
    }

    public function delete($id)
    {
        return true;
    }
}
