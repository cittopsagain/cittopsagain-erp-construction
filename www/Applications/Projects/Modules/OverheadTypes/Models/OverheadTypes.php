<?php

namespace Applications\Projects\Modules\OverheadTypes\Models;

use Core\Model;

class OverheadTypes extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM project_overhead_types 
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

    public function getTotal()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM project_overhead_types");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($code)
    {
        try {
            $sql = "SELECT * FROM project_overhead_types WHERE code = :code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':code' => $code]);
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

            if (!empty($data['code'])) {
                $data['code'] = strtoupper(str_replace(' ', '', $data['code']));
                if ($this->getByCode($data['code'])) {
                    throw new \Exception("Overhead type code already exists.");
                }
            }

            $sql = "INSERT INTO project_overhead_types (code, overhead_type, category, calculation_method, default_rate) 
                    VALUES (:code, :overhead_type, :category, :calculation_method, :default_rate)";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':code' => $data['code'] ?? null,
                ':overhead_type' => $data['overhead_type'] ?? null,
                ':category' => $data['category'] ?? null,
                ':calculation_method' => $data['calculation_method'] ?? null,
                ':default_rate' => $data['default_rate'] ?? 0
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
            if (!empty($data['code'])) {
                $data['code'] = strtoupper(str_replace(' ', '', $data['code']));
                $existing = $this->getByCode($data['code']);
                if ($existing && $existing['id'] != $data['id']) {
                    throw new \Exception("Overhead type code already exists.");
                }
            }

            $sql = "UPDATE project_overhead_types 
                    SET code = :code,
                        overhead_type = :overhead_type,
                        category = :category,
                        calculation_method = :calculation_method,
                        default_rate = :default_rate 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':code' => $data['code'] ?? null,
                ':overhead_type' => $data['overhead_type'] ?? null,
                ':category' => $data['category'] ?? null,
                ':calculation_method' => $data['calculation_method'] ?? null,
                ':default_rate' => $data['default_rate'] ?? 0,
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
            $sql = "DELETE FROM project_overhead_types WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
