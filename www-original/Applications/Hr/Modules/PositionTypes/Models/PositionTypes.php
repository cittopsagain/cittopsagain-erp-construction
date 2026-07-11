<?php

namespace Applications\Hr\Modules\PositionTypes\Models;

use Core\Model;

class PositionTypes extends Model
{

    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM hr_position_types ORDER BY pos_id ASC LIMIT :limit OFFSET :offset";
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM hr_position_types");
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
            $stmt = $this->db->query("SELECT * FROM hr_position_types ORDER BY pos_name ASC");
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
            if (isset($data['pos_id']) && is_numeric($data['pos_id'])) {
                return $this->update($data);
            }

            $sql = "INSERT INTO hr_position_types (pos_name, pos_desc, status, created_by) 
                VALUES (:name, :description, :status, :created_by)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':name' => $data['pos_name'],
                ':description' => $data['pos_desc'] ?? '',
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
            $sql = "UPDATE hr_position_types 
                SET pos_name = :name, 
                    pos_desc = :description, 
                    status = :status 
                WHERE pos_id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':name' => $data['pos_name'],
                ':description' => $data['pos_desc'] ?? '',
                ':status' => $data['status'] ?? 'Active',
                ':id' => $data['pos_id']
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM hr_position_types WHERE pos_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
