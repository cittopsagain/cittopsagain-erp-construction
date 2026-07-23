<?php

namespace Applications\Projects\Modules\OverheadCategories\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Overhead Categories";
        $data['content_xtype'] = 'overhead-categories-grid';
        $this->layout('Projects', 'OverheadCategories', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'OverheadCategories', 'OverheadCategories');
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

        if (empty($data['description'])) {
            $this->json(['success' => false, 'message' => 'Category name is required.']);
            return;
        }

        $model = $this->model('Projects', 'OverheadCategories', 'OverheadCategories');
        try {
            if ($model->save($data)) {
                $this->json(['success' => true, 'message' => 'Overhead category saved successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to save overhead category.']);
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

        $model = $this->model('Projects', 'OverheadCategories', 'OverheadCategories');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Overhead category deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete overhead category.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function all()
    {
        $model = $this->model('Projects', 'OverheadCategories', 'OverheadCategories');
        $data = $model->all();
        $this->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
