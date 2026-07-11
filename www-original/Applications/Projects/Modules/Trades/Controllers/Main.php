<?php

namespace Applications\Projects\Modules\Trades\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Trades";
        $data['content_xtype'] = 'project-trades-grid';
        $this->layout('Projects', 'Trades', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'Trades', 'Trades');
        $items = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function all()
    {
        $model = $this->model('Projects', 'Trades', 'Trades');
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
            'trade_code' => 'Trade code',
            'description' => 'Description'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Projects', 'Trades', 'Trades');
        try {
            $isUpdate = isset($data['trade_id']) && is_numeric($data['trade_id']);

            if ($model->save($data)) {
                $message = $isUpdate ? 'Trade updated successfully.' : 'Trade added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update trade.' : 'Failed to add trade.';
                $this->json(['success' => false, 'message' => $message]);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = $_POST['trade_id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'Trades', 'Trades');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Trade deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete trade.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
