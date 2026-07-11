<?php

namespace Applications\Projects\Modules\OverheadTypes\Models;

use Core\Model;

class OverheadTypes extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM sales_overhead_types 
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM sales_overhead_types");
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
            $sql = "SELECT * FROM sales_overhead_types WHERE code = :code LIMIT 1";
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
                    throw new \Exception("Overhead code already exists.");
                }
            }

            $sql = "INSERT INTO sales_overhead_types (code, description) VALUES (:code, :description)";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':code' => $data['code'] ?? null,
                ':description' => $data['description'] ?? null
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
                    throw new \Exception("Overhead code already exists.");
                }
            }

            $sql = "UPDATE sales_overhead_types 
                    SET code = :code,
                        description = :description 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':code' => $data['code'] ?? null,
                ':description' => $data['description'] ?? null,
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
            $sql = "DELETE FROM sales_overhead_types WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    public function all()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM sales_overhead_types ORDER BY description ASC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }
}
