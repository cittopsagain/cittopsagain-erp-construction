<?php

namespace Applications\Hr\Modules\Branches\Models;

use Core\Model;

class Branches extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM hr_branches ORDER BY id ASC LIMIT :limit OFFSET :offset";
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM hr_branches");
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
            $stmt = $this->db->query("SELECT * FROM hr_branches WHERE status = 'Active' ORDER BY branch_name ASC");
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

            $sql = "INSERT INTO hr_branches (branch_code, branch_name, address, contact_number, email, status, created_by) 
                    VALUES (:branch_code, :branch_name, :address, :contact_number, :email, :status, :created_by)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':branch_code' => $data['branch_code'] ?? null,
                ':branch_name' => $data['branch_name'],
                ':address' => $data['address'] ?? '',
                ':contact_number' => $data['contact_number'] ?? '',
                ':email' => $data['email'] ?? '',
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
            $sql = "UPDATE hr_branches 
                    SET branch_code = :branch_code,
                        branch_name = :branch_name, 
                        address = :address, 
                        contact_number = :contact_number,
                        email = :email,
                        status = :status 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':branch_code' => $data['branch_code'] ?? null,
                ':branch_name' => $data['branch_name'],
                ':address' => $data['address'] ?? '',
                ':contact_number' => $data['contact_number'] ?? '',
                ':email' => $data['email'] ?? '',
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
            $sql = "DELETE FROM hr_branches WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
