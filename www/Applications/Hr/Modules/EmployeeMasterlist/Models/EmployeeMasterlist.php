<?php

namespace Applications\Hr\Modules\EmployeeMasterlist\Models;

use Core\Model;

class EmployeeMasterlist extends Model
{
    public function getPaged($start, $limit, $query = '')
    {
        try {
            $where = "";
            $params = [
                ':limit' => (int)$limit,
                ':offset' => (int)$start
            ];

            if (!empty($query)) {
                $where = " WHERE e.employee_name LIKE :query1 OR e.employee_no LIKE :query2 ";
                $params[':query1'] = '%' . $query . '%';
                $params[':query2'] = '%' . $query . '%';
            }

            $sql = "SELECT e.*, 
                           p.pos_name as position_name, 
                           d.name as department_name, 
                           b.branch_name, 
                           et.employment_type as employment_type_name, 
                           ws.schedule_name as work_schedule_name,
                           s.employee_name as supervisor_name
                    FROM hr_employees e
                    LEFT JOIN hr_position_types p ON e.position_id = p.pos_id
                    LEFT JOIN hr_departments d ON e.department_id = d.id
                    LEFT JOIN hr_branches b ON e.branch_id = b.id
                    LEFT JOIN hr_employment_types et ON e.employment_type_id = et.id
                    LEFT JOIN hr_work_schedules ws ON e.work_schedule_id = ws.id
                    LEFT JOIN hr_employees s ON e.supervisor_id = s.id
                    $where
                    ORDER BY e.id ASC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    public function getTotal($query = '')
    {
        try {
            $where = "";
            $params = [];

            if (!empty($query)) {
                $where = " WHERE employee_name LIKE :query1 OR employee_no LIKE :query2 ";
                $params[':query1'] = '%' . $query . '%';
                $params[':query2'] = '%' . $query . '%';
            }

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM hr_employees $where");
            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }
            $stmt->execute();
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
            $stmt = $this->db->query("SELECT id, employee_name FROM hr_employees WHERE status = 'Active' ORDER BY employee_name ASC");
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

            $sql = "INSERT INTO hr_employees (employee_no, employee_name, position_id, department_id, branch_id, employment_type_id, work_schedule_id, date_hired, supervisor_id, status, created_by) 
                    VALUES (:employee_no, :employee_name, :position_id, :department_id, :branch_id, :employment_type_id, :work_schedule_id, :date_hired, :supervisor_id, :status, :created_by)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':employee_no' => $data['employee_no'],
                ':employee_name' => $data['employee_name'],
                ':position_id' => !empty($data['position_id']) ? $data['position_id'] : null,
                ':department_id' => !empty($data['department_id']) ? $data['department_id'] : null,
                ':branch_id' => !empty($data['branch_id']) ? $data['branch_id'] : null,
                ':employment_type_id' => !empty($data['employment_type_id']) ? $data['employment_type_id'] : null,
                ':work_schedule_id' => !empty($data['work_schedule_id']) ? $data['work_schedule_id'] : null,
                ':date_hired' => !empty($data['date_hired']) ? $data['date_hired'] : null,
                ':supervisor_id' => !empty($data['supervisor_id']) ? $data['supervisor_id'] : null,
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
            $sql = "UPDATE hr_employees 
                    SET employee_no = :employee_no,
                        employee_name = :employee_name, 
                        position_id = :position_id, 
                        department_id = :department_id,
                        branch_id = :branch_id,
                        employment_type_id = :employment_type_id,
                        work_schedule_id = :work_schedule_id,
                        date_hired = :date_hired,
                        supervisor_id = :supervisor_id,
                        status = :status 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':employee_no' => $data['employee_no'],
                ':employee_name' => $data['employee_name'],
                ':position_id' => !empty($data['position_id']) ? $data['position_id'] : null,
                ':department_id' => !empty($data['department_id']) ? $data['department_id'] : null,
                ':branch_id' => !empty($data['branch_id']) ? $data['branch_id'] : null,
                ':employment_type_id' => !empty($data['employment_type_id']) ? $data['employment_type_id'] : null,
                ':work_schedule_id' => !empty($data['work_schedule_id']) ? $data['work_schedule_id'] : null,
                ':date_hired' => !empty($data['date_hired']) ? $data['date_hired'] : null,
                ':supervisor_id' => !empty($data['supervisor_id']) ? $data['supervisor_id'] : null,
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
            $sql = "DELETE FROM hr_employees WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
