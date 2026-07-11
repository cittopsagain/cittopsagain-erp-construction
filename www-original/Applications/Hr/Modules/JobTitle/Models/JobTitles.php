<?php

namespace Applications\Hr\Modules\JobTitle\Models;

use Core\Model;

class JobTitles extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM hr_job_title ORDER BY name ASC LIMIT :limit OFFSET :offset";
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM hr_job_title");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM hr_job_title ORDER BY name ASC");
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

            $sql = "INSERT INTO hr_job_title (name, description, status, created_by) 
                VALUES (:name, :description, :status, :created_by)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'] ?? '',
                ':status' => $data['status'] ?? 'Active',
                ':created_by' => $this->getCurrentUserId()
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function update($data)
    {
        try {
            $sql = "UPDATE hr_job_title 
                SET name = :name, 
                    description = :description, 
                    status = :status 
                WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'] ?? '',
                ':status' => $data['status'] ?? 'Active',
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
            $sql = "DELETE FROM hr_job_title WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
}
