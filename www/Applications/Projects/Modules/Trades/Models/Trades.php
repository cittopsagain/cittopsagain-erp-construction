<?php

namespace Applications\Projects\Modules\Trades\Models;

use Core\Model;

class Trades extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT t.*, s.description as service_name, s.service_code
                    FROM project_trades t
                    LEFT JOIN project_services s ON t.service_id = s.service_id
                    ORDER BY t.trade_id DESC 
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
            $sql = "SELECT t.*, s.description as service_name, s.service_code
                    FROM project_trades t
                    LEFT JOIN project_services s ON t.service_id = s.service_id
                    ORDER BY t.created_at DESC";
            $stmt = $this->db->query($sql);
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM project_trades");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($trade_code)
    {
        try {
            $sql = "SELECT t.*, s.description as service_name, s.service_code
                    FROM project_trades t
                    LEFT JOIN project_services s ON t.service_id = s.service_id
                    WHERE t.trade_code = :trade_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':trade_code' => $trade_code]);
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
            if (isset($data['trade_id']) && is_numeric($data['trade_id'])) {
                return $this->update($data);
            }

            if (!empty($data['trade_code'])) {
                $data['trade_code'] = strtoupper(str_replace(' ', '', $data['trade_code']));
                if ($this->getByCode($data['trade_code'])) {
                    throw new \Exception("Trade code already exists.");
                }
            }

            $sql = "INSERT INTO project_trades (trade_code, description, long_description, service_id, created_by) 
                    VALUES (:trade_code, :description, :long_description, :service_id, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':trade_code' => $data['trade_code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':long_description' => $data['long_description'] ?? null,
                ':service_id' => $data['service_id'] ?? null,
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
            if (!empty($data['trade_code'])) {
                $data['trade_code'] = strtoupper(str_replace(' ', '', $data['trade_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['trade_code']);
                if ($existing && $existing['trade_id'] != $data['trade_id']) {
                    throw new \Exception("Trade code already exists.");
                }
            }

            $sql = "UPDATE project_trades 
                    SET trade_code = :trade_code,
                        description = :description,
                        long_description = :long_description,
                        service_id = :service_id
                    WHERE trade_id = :trade_id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':trade_code' => $data['trade_code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':long_description' => $data['long_description'] ?? null,
                ':service_id' => $data['service_id'] ?? null,
                ':trade_id' => $data['trade_id']
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
            $sql = "DELETE FROM project_trades WHERE trade_id = :trade_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':trade_id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
