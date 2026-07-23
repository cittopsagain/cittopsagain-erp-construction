<?php

namespace Applications\Projects\Modules\MarkupCategories\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Markup Categories";
        $data['content_xtype'] = 'markup-categories-grid';
        $this->layout('Projects', 'MarkupCategories', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'MarkupCategories', 'MarkupCategories');
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

        $model = $this->model('Projects', 'MarkupCategories', 'MarkupCategories');
        try {
            if ($model->save($data)) {
                $this->json(['success' => true, 'message' => 'Markup category saved successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to save markup category.']);
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

        $model = $this->model('Projects', 'MarkupCategories', 'MarkupCategories');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Markup category deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete markup category.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function all()
    {
        $model = $this->model('Projects', 'MarkupCategories', 'MarkupCategories');
        $data = $model->all();
        $this->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
