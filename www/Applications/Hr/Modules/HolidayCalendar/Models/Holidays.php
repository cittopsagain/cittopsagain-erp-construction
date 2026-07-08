<?php

namespace Applications\Hr\Modules\HolidayCalendar\Models;

use Core\Model;

class Holidays extends Model
{

    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM hr_holidays ORDER BY holiday_date ASC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$start, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getTotal()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM hr_holidays");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM hr_holidays ORDER BY holiday_date ASC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Fallback to hardcoded data if table doesn't exist or other error
            return [
                ['id' => 1, 'holiday_date' => '2026-01-01', 'description' => 'New Year\'s Day', 'type' => 'Regular'],
                ['id' => 2, 'holiday_date' => '2026-05-01', 'description' => 'Labor Day', 'type' => 'Regular'],
                ['id' => 3, 'holiday_date' => '2026-12-25', 'description' => 'Christmas Day', 'type' => 'Regular']
            ];
        }
    }

    public function save($data)
    {
        try {
            if (isset($data['id']) && is_numeric($data['id'])) {
                return $this->update($data);
            }

            $sql = "INSERT INTO hr_holidays (holiday_date, description, type) 
                VALUES (:holiday_date, :description, :type)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':holiday_date' => $data['holiday_date'],
                ':description' => $data['description'],
                ':type' => $data['type'] ?? 'Regular'
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function update($data)
    {
        try {
            $sql = "UPDATE hr_holidays 
                SET holiday_date = :holiday_date, 
                    description = :description, 
                    type = :type 
                WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':holiday_date' => $data['holiday_date'],
                ':description' => $data['description'],
                ':type' => $data['type'] ?? 'Regular',
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
            $sql = "DELETE FROM hr_holidays WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
}
