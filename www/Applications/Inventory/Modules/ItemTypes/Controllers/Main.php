<?php

namespace Applications\Inventory\Modules\ItemTypes\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Item Type";
        $data['content_xtype'] = 'item-type-grid';
        $this->layout('Inventory', 'ItemTypes', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Inventory', 'ItemTypes', 'ItemType');
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
            'type_code' => 'Type code',
            'type_name' => 'Type name'
        ];

        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Inventory', 'ItemTypes', 'ItemType');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);

            if ($model->save($data)) {
                $message = $isUpdate ? 'Item type updated successfully.' : 'Item type added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update item type.' : 'Failed to add item type.';
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

        $model = $this->model('Inventory', 'ItemTypes', 'ItemType');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Item type deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete item type.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
