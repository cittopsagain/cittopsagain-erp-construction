<?php

namespace Applications\Hr\Modules\Departments\Models;

use Core\Model;

class Departments extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT d1.*, d2.name as parent_name 
                    FROM hr_departments d1 
                    LEFT JOIN hr_departments d2 ON d1.parent_id = d2.id 
                    ORDER BY d1.name ASC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$start, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getTotal()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM hr_departments");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM hr_departments ORDER BY name ASC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function save($data)
    {
        try {
            if (isset($data['id']) && is_numeric($data['id'])) {
                return $this->update($data);
            }

            $sql = "INSERT INTO hr_departments (name, parent_id, created_by) 
                    VALUES (:name, :parent_id, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':name' => $data['name'],
                ':parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
                ':created_by' => $this->getCurrentUserId()
            ]);

            if ($success) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function findOrCreateByName($name)
    {
        try {
            $sql = "SELECT id FROM hr_departments WHERE name = :name LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':name' => $name]);
            $id = $stmt->fetchColumn();

            if ($id) {
                return $id;
            }

            return $this->save(['name' => $name]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function update($data)
    {
        try {
            $sql = "UPDATE hr_departments 
                    SET name = :name, 
                        parent_id = :parent_id 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':name' => $data['name'],
                ':parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
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
            $sql = "DELETE FROM hr_departments WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
}
