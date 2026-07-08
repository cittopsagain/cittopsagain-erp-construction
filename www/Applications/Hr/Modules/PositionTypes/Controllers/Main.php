<?php

namespace Applications\Hr\Modules\PositionTypes\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Position Types";
        $data['content_xtype'] = 'position-type-grid';
        // Render the 'index' view using the centralized layout.
        $this->layout('Hr', 'PositionTypes', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $positionModel = $this->model('Hr', 'PositionTypes', 'PositionTypes');
        $positions = $positionModel->getPaged($start, $limit);
        $total = $positionModel->getTotal();

        $this->json([
            'total' => $total,
            'data' => $positions
        ]);
    }

    public function save()
    {
        $data = $_POST;

        // Basic validation
        if (empty($data['pos_name'])) {
            $this->json(['success' => false, 'message' => 'Name is required.']);
            return;
        }

        $positionModel = $this->model('Hr', 'PositionTypes', 'PositionTypes');
        try {
            $isUpdate = isset($data['pos_id']) && is_numeric($data['pos_id']);
            if ($positionModel->save($data)) {
                $message = $isUpdate ? 'Position Type updated successfully.' : 'Position Type added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update Position Type.' : 'Failed to add Position Type.';
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

        $positionModel = $this->model('Hr', 'PositionTypes', 'PositionTypes');
        try {
            if ($positionModel->delete($id)) {
                $this->json(['success' => true, 'message' => 'Position Type deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete Position Type.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
