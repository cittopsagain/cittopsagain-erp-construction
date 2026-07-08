<?php

namespace Applications\Projects\Modules\ProjectTypes\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Project Types";
        $data['content_xtype'] = 'project-types-grid';
        $this->layout('Projects', 'ProjectTypes', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'ProjectTypes', 'ProjectTypes');
        $items = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function all()
    {
        $model = $this->model('Projects', 'ProjectTypes', 'ProjectTypes');
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
            'type_code' => 'Type code',
            'description' => 'Description'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Projects', 'ProjectTypes', 'ProjectTypes');
        try {
            $isUpdate = isset($data['type_id']) && is_numeric($data['type_id']);

            if ($model->save($data)) {
                $message = $isUpdate ? 'Project type updated successfully.' : 'Project type added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update project type.' : 'Failed to add project type.';
                $this->json(['success' => false, 'message' => $message]);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = $_POST['type_id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'ProjectTypes', 'ProjectTypes');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Project type deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete project type.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
