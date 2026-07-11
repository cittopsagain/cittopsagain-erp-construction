<?php

namespace Applications\Hr\Modules\SalaryGrades\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Salary Grades";
        $data['content_xtype'] = 'salary-grade-grid';
        $this->layout('Hr', 'SalaryGrades', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Hr', 'SalaryGrades', 'SalaryGrades');
        $grades = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $grades
        ]);
    }

    public function save()
    {
        $data = $_POST;

        if (empty($data['grade_name'])) {
            $this->json(['success' => false, 'message' => 'Grade Name is required.']);
            return;
        }

        $model = $this->model('Hr', 'SalaryGrades', 'SalaryGrades');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);
            if ($model->save($data)) {
                $message = $isUpdate ? 'Salary Grade updated successfully.' : 'Salary Grade added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update Salary Grade.' : 'Failed to add Salary Grade.';
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

        $model = $this->model('Hr', 'SalaryGrades', 'SalaryGrades');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Salary Grade deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete Salary Grade.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
