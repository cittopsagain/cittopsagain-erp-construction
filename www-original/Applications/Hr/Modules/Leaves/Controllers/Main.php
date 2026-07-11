<?php

namespace Applications\Hr\Modules\Leaves\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Leaves Management";
        $data['content_xtype'] = 'leave-grid';
        // Render the 'index' view using the centralized layout.
        $this->layout('Hr', 'Leaves', 'index', $data);
    }

    public function data()
    {
        $leaveModel = $this->model('Hr', 'Leaves', 'Leave');
        $leaves = $leaveModel->getAll();
        $this->json($leaves);
    }

    public function save()
    {
        $data = $_POST;
        if (empty($data['name']) || empty($data['position'])) {
            $this->json(['success' => false, 'message' => 'Name and Position are required.']);
            return;
        }

        $leaveModel = $this->model('Hr', 'Leaves', 'Leave');
        try {
            if ($leaveModel->save($data)) {
                $this->json(['success' => true, 'message' => 'Record saved successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to save record.']);
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

        $leaveModel = $this->model('Hr', 'Leaves', 'Leave');
        try {
            if ($leaveModel->delete($id)) {
                $this->json(['success' => true, 'message' => 'Record deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete record.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
