<?php

namespace Applications\Hr\Modules\Employees\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Employee Management";
        $data['content_xtype'] = 'employee-grid';
        // Render the 'index' view using the centralized layout.
        $this->layout('Hr', 'Employees', 'index', $data);
        $this->logToConsole(json_encode(['Test', 'Test 2']));
    }

    public function data()
    {
        $employeeModel = $this->model('Hr', 'Employees', 'Employee');
        $employees = $employeeModel->getAll();
        $this->json($employees);
    }

    public function save()
    {
        $data = $_POST;
        // Basic validation of mandatory fields
        if (empty($data['employee_id']) || empty($data['first_name']) || empty($data['last_name']) || empty($data['date_hired'])) {
            $this->json(['success' => false, 'message' => 'Employee ID, Name, and Date Hired are required.']);
            return;
        }

        $employeeModel = $this->model('Hr', 'Employees', 'Employee');
        if ($employeeModel->save($data)) {
            $this->json(['success' => true, 'message' => 'Employee added successfully.']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to add employee. Check logs for details.']);
        }
    }

    public function departments()
    {
        $model = $this->model('Hr', 'Employees', 'Employee');
        $this->json(['success' => true, 'data' => $model->getDepartments()]);
    }

    public function positions()
    {
        $model = $this->model('Hr', 'Employees', 'Employee');
        $this->json(['success' => true, 'data' => $model->getPositions()]);
    }

    public function statuses()
    {
        $model = $this->model('Hr', 'Employees', 'Employee');
        $this->json(['success' => true, 'data' => $model->getEmploymentStatuses()]);
    }

    public function salary_types()
    {
        $model = $this->model('Hr', 'Employees', 'Employee');
        $this->json(['success' => true, 'data' => $model->getSalaryTypes()]);
    }

    public function supervisors()
    {
        $model = $this->model('Hr', 'Employees', 'Employee');
        $this->json(['success' => true, 'data' => $model->getSupervisors()]);
    }

    public function save_row()
    {
        $data = $_POST;
        if (empty($data['id'])) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $employeeModel = $this->model('Hr', 'Employees', 'Employee');
        if ($employeeModel->update($data)) {
            $this->json(['success' => true, 'message' => 'Employee updated successfully.']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update employee.']);
        }
    }

    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $employeeModel = $this->model('Hr', 'Employees', 'Employee');
        if ($employeeModel->delete($id)) {
            $this->json(['success' => true, 'message' => 'Employee deleted successfully.']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete employee.']);
        }
    }
}
