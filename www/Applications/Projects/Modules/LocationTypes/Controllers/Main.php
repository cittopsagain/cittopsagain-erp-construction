<?php

namespace Applications\Projects\Modules\LocationTypes\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Location Types";
        $data['content_xtype'] = 'location-types-grid';
        $this->layout('Projects', 'LocationTypes', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'LocationTypes', 'LocationTypes');
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

        if (empty($data['name'])) {
            $this->json(['success' => false, 'message' => 'Name is required.']);
            return;
        }

        $model = $this->model('Projects', 'LocationTypes', 'LocationTypes');
        try {
            if ($model->save($data)) {
                $this->json(['success' => true, 'message' => 'Location type saved successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to save location type.']);
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

        $model = $this->model('Projects', 'LocationTypes', 'LocationTypes');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Location type deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete location type.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
