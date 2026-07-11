<?php

namespace Applications\Hr\Modules\EmploymentTypes\Models;

use Core\Model;

class EmploymentTypes extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM hr_employment_types ORDER BY id ASC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$start, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    public function getTotal()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM hr_employment_types");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM hr_employment_types ORDER BY employment_type ASC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    public function save($data)
    {
        try {
            if (isset($data['id']) && is_numeric($data['id'])) {
                return $this->update($data);
            }

            $sql = "INSERT INTO hr_employment_types (code, employment_type, description, status, created_by) 
                    VALUES (:code, :employment_type, :description, :status, :created_by)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':code' => $data['code'] ?? null,
                ':employment_type' => $data['employment_type'],
                ':description' => $data['description'] ?? '',
                ':status' => $data['status'] ?? 'Active',
                ':created_by' => $this->getCurrentUserId()
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    public function update($data)
    {
        try {
            $sql = "UPDATE hr_employment_types 
                    SET code = :code,
                        employment_type = :employment_type, 
                        description = :description, 
                        status = :status 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':code' => $data['code'] ?? null,
                ':employment_type' => $data['employment_type'],
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
            $sql = "DELETE FROM hr_employment_types WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
