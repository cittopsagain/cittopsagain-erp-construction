<?php

namespace Applications\Hr\Modules\EmployeeMasterlist\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Employee Masterlist";
        $data['content_xtype'] = 'employeemasterlist-grid';
        $this->layout('Hr', 'EmployeeMasterlist', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;
        $query = $_GET['query'] ?? '';

        $model = $this->model('Hr', 'EmployeeMasterlist', 'EmployeeMasterlist');
        $employees = $model->getPaged($start, $limit, $query);
        $total = $model->getTotal($query);

        $this->json([
            'total' => $total,
            'data' => $employees
        ]);
    }

    public function save()
    {
        $data = $_POST;

        if (empty($data['employee_no']) || empty($data['employee_name'])) {
            $this->json(['success' => false, 'message' => 'Employee No and Name are required.']);
            return;
        }

        $model = $this->model('Hr', 'EmployeeMasterlist', 'EmployeeMasterlist');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);
            if ($model->save($data)) {
                $message = $isUpdate ? 'Employee updated successfully.' : 'Employee added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update Employee.' : 'Failed to add Employee.';
                $this->json(['success' => false, 'message' => $message]);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Hr', 'EmployeeMasterlist', 'EmployeeMasterlist');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Employee deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete Employee.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function lookup_positions()
    {
        $model = $this->model('Hr', 'JobPositions', 'JobPositions');
        $this->json(['data' => $model->getAll()]);
    }

    public function lookup_departments()
    {
        $model = $this->model('Hr', 'Departments', 'Departments');
        $this->json(['data' => $model->getAll()]);
    }

    public function lookup_branches()
    {
        $model = $this->model('Hr', 'Branches', 'Branches');
        $this->json(['data' => $model->getAll()]);
    }

    public function lookup_employment_types()
    {
        $model = $this->model('Hr', 'EmploymentTypes', 'EmploymentTypes');
        $this->json(['data' => $model->getAll()]);
    }

    public function lookup_work_schedules()
    {
        $model = $this->model('Hr', 'WorkSchedules', 'WorkSchedules');
        $this->json(['data' => $model->getAll()]);
    }

    public function lookup_supervisors()
    {
        $model = $this->model('Hr', 'EmployeeMasterlist', 'EmployeeMasterlist');
        $this->json(['data' => $model->getAll()]);
    }
}
