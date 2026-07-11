<?php

namespace Applications\Inventory\Modules\Items\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Items";
        $data['content_xtype'] = 'items-grid';

        // Load Categories for dropdown
        $catModel = $this->model('Inventory', 'ItemCategory', 'ItemCategory');
        $data['categories'] = $catModel->getPaged(0, 1000); // Get all categories

        // Load Material Groups for dropdown
        $mgModel = $this->model('Inventory', 'MaterialGroups', 'MaterialGroup');
        $data['material_groups'] = $mgModel->getPaged(0, 1000);

        // Load Item Types for dropdown
        $itModel = $this->model('Inventory', 'ItemTypes', 'ItemType');
        $data['item_types'] = $itModel->getPaged(0, 1000);

        // Load Units for dropdown
        $unitModel = $this->model('Inventory', 'Units', 'Units');
        $data['units'] = $unitModel->getPaged(0, 1000); // Get all units

        $this->layout('Inventory', 'Items', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;
        $search = $_GET['query'] ?? '';

        $model = $this->model('Inventory', 'Items', 'Items');
        $items = $model->getPaged($start, $limit, $search);
        $total = $model->getTotal($search);

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function save()
    {
        $data = $_POST;

        $requiredFields = [
            'item_code' => 'Item code',
            'item_desc' => 'Item Description',
            'item_cat' => 'Item Category',
            'qty' => 'Quantity',
            'unit' => 'Unit',
            'reorder_level' => 'Reorder level',
            'maximum_stock' => 'Maximum stock',
            'default_purchase_cost' => 'Default Purchase Cost'
        ];

        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Inventory', 'Items', 'Items');
        try {
            $isUpdate = isset($data['item_id']) && is_numeric($data['item_id']);

            if ($model->save($data)) {
                $message = $isUpdate ? 'Item updated successfully.' : 'Item added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update item.' : 'Failed to add item.';
                $this->json(['success' => false, 'message' => $message]);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $item_id = $_POST['item_id'] ?? null;
        if (!$item_id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Inventory', 'Items', 'Items');
        try {
            if ($model->delete($item_id)) {
                $this->json(['success' => true, 'message' => 'Item deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete item.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function all()
    {
        $model = $this->model('Inventory', 'Items', 'Items');
        $items = $model->getAll();
        $this->json([
            'success' => true,
            'data' => $items
        ]);
    }
}
