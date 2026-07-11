<?php

namespace Applications\Projects\Modules\Systems\Models;

use Core\Model;

class Systems extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT s.*, t.description as trade_name, t.trade_code
                    FROM project_systems s
                    LEFT JOIN project_trades t ON s.trade_id = t.trade_id
                    ORDER BY s.system_id DESC 
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
            $sql = "SELECT s.*, t.description as trade_name, t.trade_code
                    FROM project_systems s
                    LEFT JOIN project_trades t ON s.trade_id = t.trade_id
                    ORDER BY s.created_at DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    public function getByTrade($trade_id)
    {
        try {
            $sql = "SELECT s.*, t.description as trade_name, t.trade_code
                    FROM project_systems s
                    LEFT JOIN project_trades t ON s.trade_id = t.trade_id
                    WHERE s.trade_id = :trade_id
                    ORDER BY s.description ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':trade_id' => $trade_id]);
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM project_systems");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($system_code)
    {
        try {
            $sql = "SELECT s.*, t.description as trade_name, t.trade_code
                    FROM project_systems s
                    LEFT JOIN project_trades t ON s.trade_id = t.trade_id
                    WHERE s.system_code = :system_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':system_code' => $system_code]);
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
            if (isset($data['system_id']) && is_numeric($data['system_id']) && $data['system_id'] > 0) {
                return $this->update($data);
            }

            if (!empty($data['system_code'])) {
                $data['system_code'] = strtoupper(str_replace(' ', '', $data['system_code']));
                if ($this->getByCode($data['system_code'])) {
                    throw new \Exception("System code already exists.");
                }
            }

            $sql = "INSERT INTO project_systems (system_code, description, long_description, trade_id, created_by) 
                    VALUES (:system_code, :description, :long_description, :trade_id, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':system_code' => $data['system_code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':long_description' => $data['long_description'] ?? null,
                ':trade_id' => $data['trade_id'] ?? null,
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
            if (!empty($data['system_code'])) {
                $data['system_code'] = strtoupper(str_replace(' ', '', $data['system_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['system_code']);
                if ($existing && $existing['system_id'] != $data['system_id']) {
                    throw new \Exception("System code already exists.");
                }
            }

            $sql = "UPDATE project_systems 
                    SET system_code = :system_code,
                        description = :description,
                        long_description = :long_description,
                        trade_id = :trade_id
                    WHERE system_id = :system_id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':system_code' => $data['system_code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':long_description' => $data['long_description'] ?? null,
                ':trade_id' => $data['trade_id'] ?? null,
                ':system_id' => $data['system_id']
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
            $sql = "DELETE FROM project_systems WHERE system_id = :system_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':system_id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
