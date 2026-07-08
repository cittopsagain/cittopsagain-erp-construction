<?php

namespace Applications\Inventory\Modules\ItemCategory\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Item Category";
        $data['content_xtype'] = 'item-category-grid';
        $this->layout('Inventory', 'ItemCategory', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Inventory', 'ItemCategory', 'ItemCategory');
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
            'item_cat_code' => 'Category code',
            'item_cat_name' => 'Category name'
        ];

        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Inventory', 'ItemCategory', 'ItemCategory');
        try {
            $isUpdate = isset($data['item_cat_id']) && is_numeric($data['item_cat_id']);

            if ($model->save($data)) {
                $message = $isUpdate ? 'Category updated successfully.' : 'Category added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update category.' : 'Failed to add category.';
                $this->json(['success' => false, 'message' => $message]);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $item_cat_id = $_POST['item_cat_id'] ?? null;
        if (!$item_cat_id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Inventory', 'ItemCategory', 'ItemCategory');
        try {
            if ($model->delete($item_cat_id)) {
                $this->json(['success' => true, 'message' => 'Category deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete category.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
