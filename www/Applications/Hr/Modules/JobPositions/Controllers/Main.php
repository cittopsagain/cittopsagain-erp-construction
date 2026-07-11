<?php

namespace Applications\Hr\Modules\JobPositions\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Job Positions";
        $data['content_xtype'] = 'job-position-grid';
        $this->layout('Hr', 'JobPositions', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Hr', 'JobPositions', 'JobPositions');
        $positions = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $positions
        ]);
    }

    public function departments()
    {
        $deptModel = $this->model('Hr', 'Departments', 'Departments');
        $departments = $deptModel->getAll();
        $this->json(['data' => $departments]);
    }

    public function reportsTo()
    {
        $model = $this->model('Hr', 'JobPositions', 'JobPositions');
        $positions = $model->getAll();
        $this->json(['data' => $positions]);
    }

    public function save()
    {
        $data = $_POST;

        if (empty($data['pos_name'])) {
            $this->json(['success' => false, 'message' => 'Name is required.']);
            return;
        }

        $model = $this->model('Hr', 'JobPositions', 'JobPositions');
        try {
            $isUpdate = isset($data['pos_id']) && is_numeric($data['pos_id']);
            if ($model->save($data)) {
                $message = $isUpdate ? 'Job Position updated successfully.' : 'Job Position added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update Job Position.' : 'Failed to add Job Position.';
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

        $model = $this->model('Hr', 'JobPositions', 'JobPositions');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Job Position deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete Job Position.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
