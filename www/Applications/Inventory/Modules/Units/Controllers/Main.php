<?php

namespace Applications\Inventory\Modules\Units\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Units";
        $data['content_xtype'] = 'units-grid';
        $this->layout('Inventory', 'Units', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $unitModel = $this->model('Inventory', 'Units', 'Units');
        $units = $unitModel->getPaged($start, $limit);
        $total = $unitModel->getTotal();

        $this->json([
            'total' => $total,
            'data' => $units
        ]);
    }

    public function all()
    {
        $unitModel = $this->model('Inventory', 'Units', 'Units');
        $units = $unitModel->getAll();
        $this->json([
            'success' => true,
            'data' => $units
        ]);
    }

    public function save()
    {
        $data = $_POST;

        $requiredFields = [
            'unit_code' => 'Unit code',
            'description' => 'Description'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $unitModel = $this->model('Inventory', 'Units', 'Units');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);

            $id = $unitModel->save($data);
            if ($id) {
                $message = $isUpdate ? 'Unit updated successfully.' : 'Unit added successfully.';
                $this->json([
                    'success' => true,
                    'message' => $message,
                    'id' => $id,
                    'unit_code' => $data['unit_code'] ?? null
                ]);
            } else {
                $message = $isUpdate ? 'Failed to update unit.' : 'Failed to add unit.';
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

        $unitModel = $this->model('Inventory', 'Units', 'Units');
        try {
            if ($unitModel->delete($id)) {
                $this->json(['success' => true, 'message' => 'Unit deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete unit.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
