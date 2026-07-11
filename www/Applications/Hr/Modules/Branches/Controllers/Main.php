<?php

namespace Applications\Hr\Modules\Branches\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Branches";
        $data['content_xtype'] = 'branch-grid';
        $this->layout('Hr', 'Branches', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Hr', 'Branches', 'Branches');
        $branches = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $branches
        ]);
    }

    public function save()
    {
        $data = $_POST;

        if (empty($data['branch_name'])) {
            $this->json(['success' => false, 'message' => 'Branch Name is required.']);
            return;
        }

        $model = $this->model('Hr', 'Branches', 'Branches');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);
            if ($model->save($data)) {
                $message = $isUpdate ? 'Branch updated successfully.' : 'Branch added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update Branch.' : 'Failed to add Branch.';
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

        $model = $this->model('Hr', 'Branches', 'Branches');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Branch deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete Branch.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
