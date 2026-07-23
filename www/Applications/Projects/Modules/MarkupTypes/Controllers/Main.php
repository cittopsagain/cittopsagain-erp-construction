<?php

namespace Applications\Projects\Modules\MarkupTypes\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Markup Types";
        $data['content_xtype'] = 'markup-types-grid';
        $this->layout('Projects', 'MarkupTypes', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'MarkupTypes', 'MarkupTypes');
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

        if (empty($data['markup_type'])) {
            $this->json(['success' => false, 'message' => 'Markup type name is required.']);
            return;
        }

        $model = $this->model('Projects', 'MarkupTypes', 'MarkupTypes');
        try {
            if ($model->save($data)) {
                $this->json(['success' => true, 'message' => 'Markup type saved successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to save markup type.']);
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

        $model = $this->model('Projects', 'MarkupTypes', 'MarkupTypes');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Markup type deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete markup type.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function all()
    {
        $model = $this->model('Projects', 'MarkupTypes', 'MarkupTypes');
        $data = $model->all();
        $this->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
