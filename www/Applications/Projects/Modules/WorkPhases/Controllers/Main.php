<?php

namespace Applications\Projects\Modules\WorkPhases\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Work Phases";
        $data['content_xtype'] = 'project-work-phases-grid';
        $this->layout('Projects', 'WorkPhases', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'WorkPhases', 'WorkPhases');
        $items = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function all()
    {
        $model = $this->model('Projects', 'WorkPhases', 'WorkPhases');
        $items = $model->getAll();

        $this->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function save()
    {
        $data = $_POST;

        $requiredFields = [
            'phase_code' => 'Work Phase code',
            'description' => 'Description'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Projects', 'WorkPhases', 'WorkPhases');
        try {
            $isUpdate = isset($data['phase_id']) && is_numeric($data['phase_id']);

            if ($model->save($data)) {
                $message = $isUpdate ? 'Work Phase updated successfully.' : 'Work Phase added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update work phase.' : 'Failed to add work phase.';
                $this->json(['success' => false, 'message' => $message]);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = $_POST['phase_id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'WorkPhases', 'WorkPhases');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Work Phase deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete work phase.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
