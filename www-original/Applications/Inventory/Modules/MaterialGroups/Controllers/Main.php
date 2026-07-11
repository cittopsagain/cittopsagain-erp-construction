<?php

namespace Applications\Inventory\Modules\MaterialGroups\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Material Groups";
        $data['content_xtype'] = 'material-groups-grid';
        $this->layout('Inventory', 'MaterialGroups', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Inventory', 'MaterialGroups', 'MaterialGroup');
        $items = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function save()
    {
        $data = $_POST;

        $requiredFields = [
            'group_code' => 'Group code',
            'group_name' => 'Group name'
        ];

        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Inventory', 'MaterialGroups', 'MaterialGroup');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);

            if ($model->save($data)) {
                $message = $isUpdate ? 'Material group updated successfully.' : 'Material group added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update material group.' : 'Failed to add material group.';
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

        $model = $this->model('Inventory', 'MaterialGroups', 'MaterialGroup');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Material group deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete material group.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
