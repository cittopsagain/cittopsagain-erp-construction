<?php

namespace Applications\Projects\Modules\EstimateTypes\Models;

use Core\Model;

class EstimateTypes extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM project_estimate_types 
                    ORDER BY id DESC 
                    LIMIT :limit OFFSET :offset";
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

    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM project_estimate_types ORDER BY id ASC");
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM project_estimate_types");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByTypeName($estimate_type)
    {
        try {
            $sql = "SELECT * FROM project_estimate_types WHERE estimate_type = :estimate_type LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':estimate_type' => $estimate_type]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return false;
        }
    }

    public function save($data)
    {
        try {
            if (isset($data['id']) && is_numeric($data['id'])) {
                return $this->update($data);
            }

            if (!empty($data['estimate_type'])) {
                if ($this->getByTypeName($data['estimate_type'])) {
                    throw new \Exception("Estimate Type already exists.");
                }
            }

            $sql = "INSERT INTO project_estimate_types (estimate_type, purpose, can_generate_quotation, created_by) 
                    VALUES (:estimate_type, :purpose, :can_generate_quotation, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':estimate_type' => $data['estimate_type'] ?? null,
                ':purpose' => $data['purpose'] ?? null,
                ':can_generate_quotation' => isset($data['can_generate_quotation']) ? (int)$data['can_generate_quotation'] : 0,
                ':created_by' => $this->getCurrentUserId()
            ]);

            if ($success) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    public function update($data)
    {
        try {
            if (!empty($data['estimate_type'])) {
                $existing = $this->getByTypeName($data['estimate_type']);
                if ($existing && $existing['id'] != $data['id']) {
                    throw new \Exception("Estimate Type already exists.");
                }
            }

            $sql = "UPDATE project_estimate_types 
                    SET estimate_type = :estimate_type,
                        purpose = :purpose,
                        can_generate_quotation = :can_generate_quotation
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':estimate_type' => $data['estimate_type'] ?? null,
                ':purpose' => $data['purpose'] ?? null,
                ':can_generate_quotation' => isset($data['can_generate_quotation']) ? (int)$data['can_generate_quotation'] : 0,
                ':id' => $data['id']
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM project_estimate_types WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
