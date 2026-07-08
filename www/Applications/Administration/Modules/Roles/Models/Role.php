<?php

namespace Applications\Administration\Modules\Roles\Models;

use Core\Model;
use PDO;

class Role extends Model
{
    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM app_roles");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Fallback to mock if table doesn't exist yet or other error
            return [
                ['id' => 1, 'name' => 'Administrator', 'description' => 'Full access to all modules'],
                ['id' => 2, 'name' => 'HR Manager', 'description' => 'Access to HR modules'],
                ['id' => 3, 'name' => 'Employee', 'description' => 'Basic access']
            ];
        }
    }

    public function save($data)
    {
        // Mock save
        return true;
    }

    public function update($data)
    {
        // Mock update
        return true;
    }

    public function delete($id)
    {
        // Mock delete
        return true;
    }
}
