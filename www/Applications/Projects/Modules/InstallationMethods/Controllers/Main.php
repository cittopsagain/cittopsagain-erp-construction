<?php

namespace Applications\Projects\Modules\InstallationMethods\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Installation Methods";
        $data['content_xtype'] = 'installation-methods-grid';
        $this->layout('Projects', 'InstallationMethods', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'InstallationMethods', 'InstallationMethods');
        $items = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function all()
    {
        $model = $this->model('Projects', 'InstallationMethods', 'InstallationMethods');
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
            'installation_method_code' => 'Installation method code',
            'installation_method_name' => 'Installation method'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Projects', 'InstallationMethods', 'InstallationMethods');
        try {
            $isUpdate = isset($data['installation_method_id']) && is_numeric($data['installation_method_id']);

            if ($model->save($data)) {
                $message = $isUpdate ? 'Installation method updated successfully.' : 'Installation method added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update installation method.' : 'Failed to add installation method.';
                $this->json(['success' => false, 'message' => $message]);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = $_POST['installation_method_id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'InstallationMethods', 'InstallationMethods');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Installation method deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete installation method.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
