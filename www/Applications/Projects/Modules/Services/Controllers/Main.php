<?php

namespace Applications\Projects\Modules\Services\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Services";
        $data['content_xtype'] = 'project-services-grid';
        $this->layout('Projects', 'Services', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'Services', 'Services');
        $items = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function all()
    {
        $model = $this->model('Projects', 'Services', 'Services');
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
            'service_code' => 'Service code',
            'description' => 'Description'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Projects', 'Services', 'Services');
        try {
            $isUpdate = isset($data['service_id']) && is_numeric($data['service_id']);

            if ($model->save($data)) {
                $message = $isUpdate ? 'Service updated successfully.' : 'Service added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update service.' : 'Failed to add service.';
                $this->json(['success' => false, 'message' => $message]);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = $_POST['service_id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'Services', 'Services');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Service deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete service.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
