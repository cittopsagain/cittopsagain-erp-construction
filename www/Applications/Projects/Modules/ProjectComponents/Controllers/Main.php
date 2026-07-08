<?php

namespace Applications\Projects\Modules\ProjectComponents\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Project Components";
        $data['content_xtype'] = 'project-components-main';

        // Load Units for the detail grid dropdown
        $unitModel = $this->model('Inventory', 'Units', 'Units');
        $data['units'] = $unitModel->getAll();

        $this->layout('Projects', 'ProjectComponents', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'ProjectComponents', 'ProjectComponents');
        $items = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function all()
    {
        $model = $this->model('Projects', 'ProjectComponents', 'ProjectComponents');
        $items = $model->getPaged(0, 1000); // Get all

        // Map component_code to project_component_code for compatibility if needed
        foreach ($items as &$item) {
            $item['project_component_code'] = $item['component_code'];
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
            'component_code' => 'Component code',
            'description' => 'Description'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Projects', 'ProjectComponents', 'ProjectComponents');
        try {
            $isUpdate = isset($data['component_id']) && is_numeric($data['component_id']);

            if ($model->save($data)) {
                $message = $isUpdate ? 'Component updated successfully.' : 'Component added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update component.' : 'Failed to add component.';
                $this->json(['success' => false, 'message' => $message]);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = $_POST['component_id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'ProjectComponents', 'ProjectComponents');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Component deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete component.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // --- Project Component Items Actions ---

    public function itemsData()
    {
        $start = $_REQUEST['start'] ?? 0;
        $limit = $_REQUEST['limit'] ?? 25;
        $component_id = $_REQUEST['component_id'] ?? null;

        $model = $this->model('Projects', 'ProjectComponents', 'ProjectComponents');
        $items = $model->getItemsPaged($start, $limit, $component_id);
        $total = $model->getItemsTotal($component_id);

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function saveItem()
    {
        $data = $_POST;

        if (empty($data['component_id']) || empty($data['item_code']) || empty($data['description'])) {
            $this->json(['success' => false, 'message' => 'Component, Item Code and Description are required.']);
            return;
        }

        $model = $this->model('Projects', 'ProjectComponents', 'ProjectComponents');
        try {
            if ($model->saveItem($data)) {
                $this->json(['success' => true, 'message' => 'Saved successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to save.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deleteItem()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'ProjectComponents', 'ProjectComponents');
        try {
            if ($model->deleteItem($id)) {
                $this->json(['success' => true, 'message' => 'Deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
