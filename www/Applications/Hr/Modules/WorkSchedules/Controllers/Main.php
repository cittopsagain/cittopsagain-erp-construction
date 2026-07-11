<?php

namespace Applications\Hr\Modules\WorkSchedules\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Work Schedules";
        $data['content_xtype'] = 'work-schedule-grid';
        $this->layout('Hr', 'WorkSchedules', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Hr', 'WorkSchedules', 'WorkSchedules');
        $schedules = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $schedules
        ]);
    }

    public function save()
    {
        $data = $_POST;

        if (empty($data['schedule_name'])) {
            $this->json(['success' => false, 'message' => 'Schedule Name is required.']);
            return;
        }

        $model = $this->model('Hr', 'WorkSchedules', 'WorkSchedules');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);
            if ($model->save($data)) {
                $message = $isUpdate ? 'Work Schedule updated successfully.' : 'Work Schedule added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update Work Schedule.' : 'Failed to add Work Schedule.';
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

        $model = $this->model('Hr', 'WorkSchedules', 'WorkSchedules');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Work Schedule deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete Work Schedule.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
