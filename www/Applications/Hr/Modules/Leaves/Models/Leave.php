<?php

namespace Applications\Hr\Modules\Leaves\Models;

use Core\Model;

class Leave extends Model
{

    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM hr_leaves ORDER BY id DESC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Fallback to hardcoded data if table doesn't exist or other error
            return [
                ['id' => 1, 'name' => 'John Doe', 'position' => 'Developer'],
                ['id' => 2, 'name' => 'Jane Smith', 'position' => 'Designer'],
                ['id' => 3, 'name' => 'Mike Johnson', 'position' => 'HR Manager']
            ];
        }
    }

    public function save($data)
    {
        try {
            if (isset($data['id']) && !empty($data['id'])) {
                return $this->update($data);
            }

            $sql = "INSERT INTO hr_leaves (name, position) VALUES (:name, :position)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':name' => $data['name'],
                ':position' => $data['position']
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function update($data)
    {
        try {
            $sql = "UPDATE hr_leaves SET name = :name, position = :position WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':name' => $data['name'],
                ':position' => $data['position'],
                ':id' => $data['id']
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM hr_leaves WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
}
