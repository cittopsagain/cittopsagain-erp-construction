<?php

namespace Applications\Hr\Modules\SalaryGrades\Models;

use Core\Model;

class SalaryGrades extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM hr_salary_grades ORDER BY id ASC LIMIT :limit OFFSET :offset";
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM hr_salary_grades");
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
            $stmt = $this->db->query("SELECT * FROM hr_salary_grades WHERE status = 'Active' ORDER BY grade_code ASC");
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

            $sql = "INSERT INTO hr_salary_grades (grade_code, grade_name, min_salary, max_salary, description, status, created_by) 
                    VALUES (:grade_code, :grade_name, :min_salary, :max_salary, :description, :status, :created_by)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':grade_code' => $data['grade_code'] ?? null,
                ':grade_name' => $data['grade_name'],
                ':min_salary' => $data['min_salary'] ?? 0.00,
                ':max_salary' => $data['max_salary'] ?? 0.00,
                ':description' => $data['description'] ?? '',
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
            $sql = "UPDATE hr_salary_grades 
                    SET grade_code = :grade_code,
                        grade_name = :grade_name, 
                        min_salary = :min_salary, 
                        max_salary = :max_salary,
                        description = :description,
                        status = :status 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':grade_code' => $data['grade_code'] ?? null,
                ':grade_name' => $data['grade_name'],
                ':min_salary' => $data['min_salary'] ?? 0.00,
                ':max_salary' => $data['max_salary'] ?? 0.00,
                ':description' => $data['description'] ?? '',
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
            $sql = "DELETE FROM hr_salary_grades WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
