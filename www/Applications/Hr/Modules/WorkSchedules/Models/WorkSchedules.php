<?php

namespace Applications\Hr\Modules\WorkSchedules\Models;

use Core\Model;

class WorkSchedules extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM hr_work_schedules ORDER BY id ASC LIMIT :limit OFFSET :offset";
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM hr_work_schedules");
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
            $stmt = $this->db->query("SELECT * FROM hr_work_schedules WHERE status = 'Active' ORDER BY schedule_name ASC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    public function save($data)
    {
        try {
            if (isset($data['id']) && is_numeric($data['id'])) {
                return $this->update($data);
            }

            $sql = "INSERT INTO hr_work_schedules (code, schedule_name, days, time_range, status, created_by) 
                    VALUES (:code, :schedule_name, :days, :time_range, :status, :created_by)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':code' => $data['code'] ?? null,
                ':schedule_name' => $data['schedule_name'],
                ':days' => $data['days'] ?? '',
                ':time_range' => $data['time_range'] ?? '',
                ':status' => $data['status'] ?? 'Active',
                ':created_by' => $this->getCurrentUserId()
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    public function update($data)
    {
        try {
            $sql = "UPDATE hr_work_schedules 
                    SET code = :code,
                        schedule_name = :schedule_name, 
                        days = :days, 
                        time_range = :time_range,
                        status = :status 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':code' => $data['code'] ?? null,
                ':schedule_name' => $data['schedule_name'],
                ':days' => $data['days'] ?? '',
                ':time_range' => $data['time_range'] ?? '',
                ':status' => $data['status'] ?? 'Active',
                ':id' => $data['id']
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM hr_work_schedules WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
