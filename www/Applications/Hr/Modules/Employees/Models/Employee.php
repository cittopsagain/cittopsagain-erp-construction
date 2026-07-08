<?php

namespace Applications\Hr\Modules\Employees\Models;

use Core\Model;

class Employee extends Model
{
    public function getAll()
    {
        try {
            $sql = "SELECT m.id, 
                           CONCAT(m.first_name, ' ', COALESCE(m.middle_name, ''), ' ', m.last_name, ' ', COALESCE(m.suffix, '')) as name,
                           e.job_title as position,
                           d.department_name as department,
                           m.active_flag as status
                    FROM employee_master m
                    LEFT JOIN employee_employment e ON m.id = e.employee_master_id
                    LEFT JOIN ref_departments d ON e.department_id = d.id
                    ORDER BY m.id DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function save($data)
    {
        $this->db->beginTransaction();
        try {
            // 1. Insert into employee_master
            $sqlMaster = "INSERT INTO employee_master (
                employee_id, employee_number, biometric_id, first_name, middle_name, last_name, suffix,
                gender, dob, pob, civil_status, nationality, mobile_number, email_address,
                current_address, permanent_address, emergency_contact_name, emergency_contact_number, emergency_contact_relationship,
                created_by
            ) VALUES (
                :employee_id, :employee_number, :biometric_id, :first_name, :middle_name, :last_name, :suffix,
                :gender, :dob, :pob, :civil_status, :nationality, :mobile_number, :email_address,
                :current_address, :permanent_address, :emergency_contact_name, :emergency_contact_number, :emergency_contact_relationship,
                :created_by
            )";
            $stmtMaster = $this->db->prepare($sqlMaster);
            $stmtMaster->execute([
                ':employee_id' => $data['employee_id'],
                ':employee_number' => $data['employee_number'] ?? null,
                ':biometric_id' => $data['biometric_id'] ?? null,
                ':first_name' => $data['first_name'],
                ':middle_name' => $data['middle_name'] ?? null,
                ':last_name' => $data['last_name'],
                ':suffix' => $data['suffix'] ?? null,
                ':gender' => $data['gender'] ?? null,
                ':dob' => $data['dob'] ?? null,
                ':pob' => $data['pob'] ?? null,
                ':civil_status' => $data['civil_status'] ?? null,
                ':nationality' => $data['nationality'] ?? null,
                ':mobile_number' => $data['mobile_number'] ?? null,
                ':email_address' => $data['email_address'] ?? null,
                ':current_address' => $data['current_address'] ?? null,
                ':permanent_address' => $data['permanent_address'] ?? null,
                ':emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                ':emergency_contact_number' => $data['emergency_contact_number'] ?? null,
                ':emergency_contact_relationship' => $data['emergency_contact_relationship'] ?? null,
                ':created_by' => $this->getCurrentUserId()
            ]);
            $employeeMasterId = $this->db->lastInsertId();

            // 2. Insert into employee_employment
            $sqlEmp = "INSERT INTO employee_employment (
                employee_master_id, date_hired, employment_status_id, employment_type, department_id,
                position_id, supervisor_id, employment_start_date, employment_end_date, job_title,
                job_level, position_classification, employment_category, work_location, cost_center
            ) VALUES (
                :employee_master_id, :date_hired, :employment_status_id, :employment_type, :department_id,
                :position_id, :supervisor_id, :employment_start_date, :employment_end_date, :job_title,
                :job_level, :position_classification, :employment_category, :work_location, :cost_center
            )";
            $stmtEmp = $this->db->prepare($sqlEmp);
            $stmtEmp->execute([
                ':employee_master_id' => $employeeMasterId,
                ':date_hired' => $data['date_hired'],
                ':employment_status_id' => $data['employment_status_id'] ?: null,
                ':employment_type' => $data['employment_type'] ?: null,
                ':department_id' => $data['department_id'] ?: null,
                ':position_id' => $data['position_id'] ?: null,
                ':supervisor_id' => $data['supervisor_id'] ?: null,
                ':employment_start_date' => $data['employment_start_date'] ?: null,
                ':employment_end_date' => $data['employment_end_date'] ?: null,
                ':job_title' => $data['job_title'] ?: null,
                ':job_level' => $data['job_level'] ?: null,
                ':position_classification' => $data['position_classification'] ?: null,
                ':employment_category' => $data['employment_category'] ?: null,
                ':work_location' => $data['work_location'] ?: null,
                ':cost_center' => $data['cost_center'] ?: null,
            ]);

            // 3. Insert into employee_statutory
            $sqlStat = "INSERT INTO employee_statutory (
                employee_master_id, sss_number, philhealth_number, pagibig_number, tin_number,
                tax_status, rdo_code, withholding_tax_type
            ) VALUES (
                :employee_master_id, :sss_number, :philhealth_number, :pagibig_number, :tin_number,
                :tax_status, :rdo_code, :withholding_tax_type
            )";
            $stmtStat = $this->db->prepare($sqlStat);
            $stmtStat->execute([
                ':employee_master_id' => $employeeMasterId,
                ':sss_number' => $data['sss_number'] ?? null,
                ':philhealth_number' => $data['philhealth_number'] ?? null,
                ':pagibig_number' => $data['pagibig_number'] ?? null,
                ':tin_number' => $data['tin_number'] ?? null,
                ':tax_status' => $data['tax_status'] ?? null,
                ':rdo_code' => $data['rdo_code'] ?? null,
                ':withholding_tax_type' => $data['withholding_tax_type'] ?? null,
            ]);

            // 4. Insert into employee_compensation
            $sqlComp = "INSERT INTO employee_compensation (
                employee_master_id, salary_type_id, basic_salary_rate, rice_allowance,
                transport_allowance, meal_allowance, pay_frequency, payroll_group,
                bank_name, bank_account_number, payment_method
            ) VALUES (
                :employee_master_id, :salary_type_id, :basic_salary_rate, :rice_allowance,
                :transport_allowance, :meal_allowance, :pay_frequency, :payroll_group,
                :bank_name, :bank_account_number, :payment_method
            )";
            $stmtComp = $this->db->prepare($sqlComp);
            $stmtComp->execute([
                ':employee_master_id' => $employeeMasterId,
                ':salary_type_id' => $data['salary_type_id'] ?: null,
                ':basic_salary_rate' => $data['basic_salary_rate'] ?: 0,
                ':rice_allowance' => $data['rice_allowance'] ?: 0,
                ':transport_allowance' => $data['transport_allowance'] ?: 0,
                ':meal_allowance' => $data['meal_allowance'] ?: 0,
                ':pay_frequency' => $data['pay_frequency'] ?: null,
                ':payroll_group' => $data['payroll_group'] ?? null,
                ':bank_name' => $data['bank_name'] ?? null,
                ':bank_account_number' => $data['bank_account_number'] ?? null,
                ':payment_method' => $data['payment_method'] ?: null,
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Update an employee.
     */
    public function update($data)
    {
        $sql = "UPDATE hr_employees 
                SET fname = :fname, mname = :mname, lname = :lname, dsgntion = :dsgntion 
                WHERE emp_id = :id";

        $stmt = $this->db->prepare($sql);

        $nameParts = explode(' ', $data['name'] ?? '');
        $fname = $nameParts[0] ?? '';
        $mname = (count($nameParts) > 2) ? $nameParts[1] : '';
        $lname = (count($nameParts) > 2) ? implode(' ', array_slice($nameParts, 2)) : ($nameParts[1] ?? '');

        return $stmt->execute([
            ':fname' => $fname,
            ':mname' => $mname,
            ':lname' => $lname,
            ':dsgntion' => $data['position'] ?? '',
            ':id' => $data['id']
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM employee_master WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getDepartments()
    {
        return $this->db->query("SELECT id, department_name as name FROM ref_departments ORDER BY name")->fetchAll();
    }

    public function getPositions()
    {
        return $this->db->query("SELECT id, position_name as name FROM ref_positions ORDER BY name")->fetchAll();
    }

    public function getEmploymentStatuses()
    {
        return $this->db->query("SELECT id, status_name as name FROM ref_employment_status ORDER BY name")->fetchAll();
    }

    public function getSalaryTypes()
    {
        return $this->db->query("SELECT id, type_name as name FROM ref_salary_types ORDER BY name")->fetchAll();
    }

    public function getSupervisors()
    {
        $sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name FROM employee_master WHERE active_flag = 'Active' ORDER BY first_name";
        return $this->db->query($sql)->fetchAll();
    }
}
