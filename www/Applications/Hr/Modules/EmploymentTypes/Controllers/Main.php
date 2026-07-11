<?php

namespace Applications\Hr\Modules\EmploymentTypes\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Employment Types";
        $data['content_xtype'] = 'employment-type-grid';
        $this->layout('Hr', 'EmploymentTypes', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Hr', 'EmploymentTypes', 'EmploymentTypes');
        $types = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $types
        ]);
    }

    public function save()
    {
        $data = $_POST;

        if (empty($data['employment_type'])) {
            $this->json(['success' => false, 'message' => 'Employment Type name is required.']);
            return;
        }

        $model = $this->model('Hr', 'EmploymentTypes', 'EmploymentTypes');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);
            if ($model->save($data)) {
                $message = $isUpdate ? 'Employment Type updated successfully.' : 'Employment Type added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update Employment Type.' : 'Failed to add Employment Type.';
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

        $model = $this->model('Hr', 'EmploymentTypes', 'EmploymentTypes');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Employment Type deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete Employment Type.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
