<?php

namespace Applications\Projects\Modules\WorkPhases\Models;

use Core\Model;

class WorkPhases extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM project_work_phases 
                    ORDER BY phase_id DESC 
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
            $stmt = $this->db->query("SELECT * FROM project_work_phases ORDER BY created_at DESC");
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM project_work_phases");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($phase_code)
    {
        try {
            $sql = "SELECT * FROM project_work_phases WHERE phase_code = :phase_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':phase_code' => $phase_code]);
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
            if (isset($data['phase_id']) && is_numeric($data['phase_id'])) {
                return $this->update($data);
            }

            if (!empty($data['phase_code'])) {
                $data['phase_code'] = strtoupper(str_replace(' ', '', $data['phase_code']));
                if ($this->getByCode($data['phase_code'])) {
                    throw new \Exception("Work Phase code already exists.");
                }
            }

            $sql = "INSERT INTO project_work_phases (phase_code, description, long_description, created_by) 
                    VALUES (:phase_code, :description, :long_description, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':phase_code' => $data['phase_code'] ?? null,
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
            if (!empty($data['phase_code'])) {
                $data['phase_code'] = strtoupper(str_replace(' ', '', $data['phase_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['phase_code']);
                if ($existing && $existing['phase_id'] != $data['phase_id']) {
                    throw new \Exception("Work Phase code already exists.");
                }
            }

            $sql = "UPDATE project_work_phases 
                    SET phase_code = :phase_code,
                        description = :description,
                        long_description = :long_description
                    WHERE phase_id = :phase_id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':phase_code' => $data['phase_code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':long_description' => $data['long_description'] ?? null,
                ':phase_id' => $data['phase_id']
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
            $sql = "DELETE FROM project_work_phases WHERE phase_id = :phase_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':phase_id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
