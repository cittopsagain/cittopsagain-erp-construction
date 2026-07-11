<?php

namespace Applications\Hr\Modules\JobPositions\Models;

use Core\Model;

class JobPositions extends Model
{

    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT p.*, d.name as dept_name, r.pos_name as reports_to_name 
                    FROM hr_position_types p
                    LEFT JOIN hr_departments d ON p.dept_id = d.id
                    LEFT JOIN hr_position_types r ON p.parent_id = r.pos_id
                    ORDER BY p.pos_id ASC LIMIT :limit OFFSET :offset";
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM hr_position_types");
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
            $stmt = $this->db->query("SELECT * FROM hr_position_types ORDER BY pos_name ASC");
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
            if (isset($data['pos_id']) && is_numeric($data['pos_id'])) {
                return $this->update($data);
            }

            $sql = "INSERT INTO hr_position_types (pos_code, pos_name, pos_desc, dept_id, parent_id, salary_grade, status, created_by) 
                VALUES (:pos_code, :pos_name, :pos_desc, :dept_id, :parent_id, :salary_grade, :status, :created_by)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':pos_code' => $data['pos_code'] ?? null,
                ':pos_name' => $data['pos_name'],
                ':pos_desc' => $data['pos_desc'] ?? '',
                ':dept_id' => !empty($data['dept_id']) ? $data['dept_id'] : null,
                ':parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
                ':salary_grade' => $data['salary_grade'] ?? null,
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
            $sql = "UPDATE hr_position_types 
                SET pos_code = :pos_code,
                    pos_name = :pos_name, 
                    pos_desc = :pos_desc, 
                    dept_id = :dept_id,
                    parent_id = :parent_id,
                    salary_grade = :salary_grade,
                    status = :status 
                WHERE pos_id = :pos_id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':pos_code' => $data['pos_code'] ?? null,
                ':pos_name' => $data['pos_name'],
                ':pos_desc' => $data['pos_desc'] ?? '',
                ':dept_id' => !empty($data['dept_id']) ? $data['dept_id'] : null,
                ':parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
                ':salary_grade' => $data['salary_grade'] ?? null,
                ':status' => $data['status'] ?? 'Active',
                ':pos_id' => $data['pos_id']
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM hr_position_types WHERE pos_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
