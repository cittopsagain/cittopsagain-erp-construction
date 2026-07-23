<?php

namespace Applications\Projects\Modules\OverheadTypes\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Overhead Types";
        $data['content_xtype'] = 'overhead-types-grid';
        $this->layout('Projects', 'OverheadTypes', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'OverheadTypes', 'OverheadTypes');
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

        if (empty($data['code'])) {
            $this->json(['success' => false, 'message' => 'Code is required.']);
            return;
        }

        if (empty($data['overhead_type'])) {
            $this->json(['success' => false, 'message' => 'Overhead type is required.']);
            return;
        }

        $model = $this->model('Projects', 'OverheadTypes', 'OverheadTypes');
        try {
            if ($model->save($data)) {
                $this->json(['success' => true, 'message' => 'Overhead type saved successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to save overhead type.']);
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

        $model = $this->model('Projects', 'OverheadTypes', 'OverheadTypes');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Overhead type deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete overhead type.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
