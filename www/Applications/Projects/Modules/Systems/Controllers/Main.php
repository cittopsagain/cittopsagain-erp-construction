<?php

namespace Applications\Projects\Modules\Systems\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Systems";
        $data['content_xtype'] = 'project-systems-grid';
        $this->layout('Projects', 'Systems', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'Systems', 'Systems');
        $items = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function all()
    {
        $trade_id = $_GET['trade_id'] ?? null;
        $model = $this->model('Projects', 'Systems', 'Systems');

        if ($trade_id) {
            $items = $model->getByTrade($trade_id);
        } else {
            $items = $model->getAll();
        }

        $this->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function save()
    {
        $data = $_POST;

        $requiredFields = [
            'system_code' => 'System code',
            'description' => 'Description',
            'trade_id' => 'Trade'
        ];

        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Projects', 'Systems', 'Systems');
        try {
            $isUpdate = isset($data['system_id']) && is_numeric($data['system_id']) && $data['system_id'] > 0;

            if ($model->save($data)) {
                $message = $isUpdate ? 'System updated successfully.' : 'System added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update system.' : 'Failed to add system.';
                $this->json(['success' => false, 'message' => $message]);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = $_POST['system_id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'Systems', 'Systems');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'System deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete system.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
