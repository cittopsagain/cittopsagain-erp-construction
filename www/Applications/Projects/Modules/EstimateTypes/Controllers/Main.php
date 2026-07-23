<?php

namespace Applications\Projects\Modules\EstimateTypes\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Estimate Types";
        $data['content_xtype'] = 'project-estimate-types-grid';
        $this->layout('Projects', 'EstimateTypes', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'EstimateTypes', 'EstimateTypes');
        $items = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function all()
    {
        $model = $this->model('Projects', 'EstimateTypes', 'EstimateTypes');
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
            'estimate_type' => 'Estimate Type',
            'purpose' => 'Purpose'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Projects', 'EstimateTypes', 'EstimateTypes');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);

            if ($model->save($data)) {
                $message = $isUpdate ? 'Estimate Type updated successfully.' : 'Estimate Type added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update estimate type.' : 'Failed to add estimate type.';
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

        $model = $this->model('Projects', 'EstimateTypes', 'EstimateTypes');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Estimate Type deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete estimate type.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
