<?php

namespace Applications\Inventory\Modules\Units\Models;

use Core\Model;

class Units extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM sales_unit 
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM sales_unit");
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
            $stmt = $this->db->query("SELECT * FROM sales_unit ORDER BY description ASC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    public function getByCode($unit_code)
    {
        try {
            $sql = "SELECT * FROM sales_unit WHERE unit_code = :unit_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':unit_code' => $unit_code]);
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

            if (!empty($data['unit_code'])) {
                $data['unit_code'] = strtoupper(str_replace(' ', '', $data['unit_code']));
                if ($this->getByCode($data['unit_code'])) {
                    throw new \Exception("Unit code already exists.");
                }
            }

            $sql = "INSERT INTO sales_unit (unit_code, description, created_by) 
                    VALUES (:unit_code, :description, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':unit_code' => $data['unit_code'] ?? null,
                ':description' => $data['description'],
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
            if (!empty($data['unit_code'])) {
                $data['unit_code'] = strtoupper(str_replace(' ', '', $data['unit_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['unit_code']);
                if ($existing && $existing['id'] != $data['id']) {
                    throw new \Exception("Unit code already exists.");
                }
            }

            $sql = "UPDATE sales_unit 
                    SET unit_code = :unit_code,
                        description = :description
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':unit_code' => $data['unit_code'] ?? null,
                ':description' => $data['description'],
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
            $sql = "DELETE FROM sales_unit WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
