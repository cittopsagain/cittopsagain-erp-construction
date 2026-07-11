<?php

namespace Applications\Hr\Modules\HolidayCalendar\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Holiday Calendar";
        $data['content_xtype'] = 'holidays-grid';
        // Render the 'index' view using the centralized layout.
        $this->layout('Hr', 'HolidayCalendar', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $holidayModel = $this->model('Hr', 'HolidayCalendar', 'Holidays');
        $holidays = $holidayModel->getPaged($start, $limit);
        $total = $holidayModel->getTotal();

        $this->json([
            'total' => $total,
            'data' => $holidays
        ]);
    }

    public function save()
    {
        $data = $_POST;

        // Ensure date is in Y-m-d format if it's coming from Ext JS datefield
        if (!empty($data['holiday_date'])) {
            $data['holiday_date'] = date('Y-m-d', strtotime($data['holiday_date']));
        }

        // Basic validation
        if (empty($data['holiday_date']) || empty($data['description'])) {
            $this->json(['success' => false, 'message' => 'Date and Description are required.']);
            return;
        }

        $holidayModel = $this->model('Hr', 'HolidayCalendar', 'Holidays');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);
            if ($holidayModel->save($data)) {
                $message = $isUpdate ? 'Holiday updated successfully.' : 'Holiday added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update holiday.' : 'Failed to add holiday.';
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

        $holidayModel = $this->model('Hr', 'HolidayCalendar', 'Holidays');
        try {
            if ($holidayModel->delete($id)) {
                $this->json(['success' => true, 'message' => 'Holiday deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete holiday.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
