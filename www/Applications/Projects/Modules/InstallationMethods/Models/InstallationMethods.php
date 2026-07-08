<?php

namespace Applications\Projects\Modules\InstallationMethods\Models;

use Core\Model;

class InstallationMethods extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM installation_methods 
                    ORDER BY installation_method_id DESC 
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
            $stmt = $this->db->query("SELECT * FROM installation_methods ORDER BY created_at DESC");
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM installation_methods");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($installation_method_code)
    {
        try {
            $sql = "SELECT * FROM installation_methods WHERE installation_method_code = :installation_method_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':installation_method_code' => $installation_method_code]);
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
            if (isset($data['installation_method_id']) && is_numeric($data['installation_method_id'])) {
                return $this->update($data);
            }

            if (!empty($data['installation_method_code'])) {
                $data['installation_method_code'] = strtoupper(str_replace(' ', '', $data['installation_method_code']));
                if ($this->getByCode($data['installation_method_code'])) {
                    throw new \Exception("Installation method code already exists.");
                }
            }

            $sql = "INSERT INTO installation_methods (installation_method_code, installation_method_name, description, created_by) 
                    VALUES (:installation_method_code, :installation_method_name, :description, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':installation_method_code' => $data['installation_method_code'] ?? null,
                ':installation_method_name' => $data['installation_method_name'] ?? null,
                ':description' => $data['description'] ?? null,
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
            if (!empty($data['installation_method_code'])) {
                $data['installation_method_code'] = strtoupper(str_replace(' ', '', $data['installation_method_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['installation_method_code']);
                if ($existing && $existing['installation_method_id'] != $data['installation_method_id']) {
                    throw new \Exception("Installation method code already exists.");
                }
            }

            $sql = "UPDATE installation_methods 
                    SET installation_method_code = :installation_method_code,
                        installation_method_name = :installation_method_name,
                        description = :description
                    WHERE installation_method_id = :installation_method_id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':installation_method_code' => $data['installation_method_code'] ?? null,
                ':installation_method_name' => $data['installation_method_name'] ?? null,
                ':description' => $data['description'] ?? null,
                ':installation_method_id' => $data['installation_method_id']
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
            $sql = "DELETE FROM installation_methods WHERE installation_method_id = :installation_method_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':installation_method_id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
