<?php

namespace Applications\Projects\Modules\ProjectTypes\Models;

use Core\Model;

class ProjectTypes extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM project_types 
                    ORDER BY type_id DESC 
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
            $stmt = $this->db->query("SELECT * FROM project_types ORDER BY created_at DESC");
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM project_types");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($type_code)
    {
        try {
            $sql = "SELECT * FROM project_types WHERE type_code = :type_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':type_code' => $type_code]);
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
            if (isset($data['type_id']) && is_numeric($data['type_id'])) {
                return $this->update($data);
            }

            if (!empty($data['type_code'])) {
                $data['type_code'] = strtoupper(str_replace(' ', '', $data['type_code']));
                if ($this->getByCode($data['type_code'])) {
                    throw new \Exception("Project type code already exists.");
                }
            }

            $sql = "INSERT INTO project_types (type_code, description, long_description, created_by) 
                    VALUES (:type_code, :description, :long_description, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':type_code' => $data['type_code'] ?? null,
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
            if (!empty($data['type_code'])) {
                $data['type_code'] = strtoupper(str_replace(' ', '', $data['type_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['type_code']);
                if ($existing && $existing['type_id'] != $data['type_id']) {
                    throw new \Exception("Project type code already exists.");
                }
            }

            $sql = "UPDATE project_types 
                    SET type_code = :type_code,
                        description = :description,
                        long_description = :long_description
                    WHERE type_id = :type_id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':type_code' => $data['type_code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':long_description' => $data['long_description'] ?? null,
                ':type_id' => $data['type_id']
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
            $sql = "DELETE FROM project_types WHERE type_id = :type_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':type_id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
