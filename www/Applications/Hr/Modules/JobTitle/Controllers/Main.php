<?php

namespace Applications\Hr\Modules\JobTitle\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Job Title";
        $data['content_xtype'] = 'job-title-grid';
        // Render the 'index' view using the centralized layout.
        $this->layout('Hr', 'JobTitle', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Hr', 'JobTitle', 'JobTitles');
        $data = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $data
        ]);
    }

    public function save()
    {
        $data = $_POST;

        // Basic validation
        if (empty($data['name'])) {
            $this->json(['success' => false, 'message' => 'Name is required.']);
            return;
        }

        $model = $this->model('Hr', 'JobTitle', 'JobTitles');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);
            if ($model->save($data)) {
                $message = $isUpdate ? 'Job Title updated successfully.' : 'Job Title added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update Job Title.' : 'Failed to add Job Title.';
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

        $model = $this->model('Hr', 'JobTitle', 'JobTitles');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Job Title deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete Job Title.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function all()
    {
        $model = $this->model('Hr', 'JobTitle', 'JobTitles');
        $data = $model->getAll();
        $this->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
