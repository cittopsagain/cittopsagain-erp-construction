<?php

namespace Applications\Hr\Modules\Departments\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Departments";
        $data['content_xtype'] = 'departments-grid';
        $this->layout('Hr', 'Departments', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $deptModel = $this->model('Hr', 'Departments', 'Departments');
        $departments = $deptModel->getPaged($start, $limit);
        $total = $deptModel->getTotal();

        $this->json([
            'total' => $total,
            'data' => $departments
        ]);
    }

    public function all()
    {
        $deptModel = $this->model('Hr', 'Departments', 'Departments');
        $departments = $deptModel->getAll();
        $this->json([
            'success' => true,
            'data' => $departments
        ]);
    }

    public function save()
    {
        $data = $_POST;

        if (empty($data['name'])) {
            $this->json(['success' => false, 'message' => 'Department name is required.']);
            return;
        }

        $deptModel = $this->model('Hr', 'Departments', 'Departments');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);

            // Handle parent_id if it's a new name instead of an ID
            if (!empty($data['parent_id']) && !is_numeric($data['parent_id'])) {
                $data['parent_id'] = $deptModel->findOrCreateByName($data['parent_id']);
            }

            // Prevent self-parenting if updating
            if ($isUpdate && isset($data['parent_id']) && $data['parent_id'] == $data['id']) {
                $this->json(['success' => false, 'message' => 'Department cannot be its own parent.']);
                return;
            }

            if ($deptModel->save($data)) {
                $message = $isUpdate ? 'Department updated successfully.' : 'Department added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update department.' : 'Failed to add department.';
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

        $deptModel = $this->model('Hr', 'Departments', 'Departments');
        try {
            if ($deptModel->delete($id)) {
                $this->json(['success' => true, 'message' => 'Department deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete department.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
