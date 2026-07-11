<?php

namespace Applications\Projects\Modules\Services\Models;

use Core\Model;

class Services extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM project_services 
                    ORDER BY service_id DESC 
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
            $stmt = $this->db->query("SELECT * FROM project_services ORDER BY created_at DESC");
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM project_services");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($service_code)
    {
        try {
            $sql = "SELECT * FROM project_services WHERE service_code = :service_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':service_code' => $service_code]);
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
            if (isset($data['service_id']) && is_numeric($data['service_id'])) {
                return $this->update($data);
            }

            if (!empty($data['service_code'])) {
                $data['service_code'] = strtoupper(str_replace(' ', '', $data['service_code']));
                if ($this->getByCode($data['service_code'])) {
                    throw new \Exception("Service code already exists.");
                }
            }

            $sql = "INSERT INTO project_services (service_code, description, long_description, created_by) 
                    VALUES (:service_code, :description, :long_description, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':service_code' => $data['service_code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':long_description' => $data['long_description'] ?? null,
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
            if (!empty($data['service_code'])) {
                $data['service_code'] = strtoupper(str_replace(' ', '', $data['service_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['service_code']);
                if ($existing && $existing['service_id'] != $data['service_id']) {
                    throw new \Exception("Service code already exists.");
                }
            }

            $sql = "UPDATE project_services 
                    SET service_code = :service_code,
                        description = :description,
                        long_description = :long_description
                    WHERE service_id = :service_id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':service_code' => $data['service_code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':long_description' => $data['long_description'] ?? null,
                ':service_id' => $data['service_id']
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
            $sql = "DELETE FROM project_services WHERE service_id = :service_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':service_id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
