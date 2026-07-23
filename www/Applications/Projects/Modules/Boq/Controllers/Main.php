<?php

namespace Applications\Projects\Modules\Boq\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Bill of Quantities";
        $data['content_xtype'] = 'boq-main';

        $clientModel = $this->model('Sales', 'Clients', 'Clients');
        $data['clients'] = $clientModel->getAll();

        $serviceModel = $this->model('Projects', 'Services', 'Services');
        $data['services'] = $serviceModel->getAll();

        $tradeModel = $this->model('Projects', 'Trades', 'Trades');
        $data['trades'] = $tradeModel->getAll();

        $systemModel = $this->model('Projects', 'Systems', 'Systems');
        $data['systems'] = $systemModel->getAll();

        $installationModel = $this->model('Projects', 'InstallationMethods', 'InstallationMethods');
        $data['installation_methods'] = $installationModel->getAll();

        $templateModel = $this->model('Projects', 'CompositionTemplates', 'CompositionTemplate');
        $data['templates'] = $templateModel->getPaged(0, 1000);

        $locationModel = $this->model('Projects', 'Boq', 'Locations');
        $data['project_locations'] = $locationModel->getAll(1000, 0);

        $locationTypesModel = $this->model('Projects', 'LocationTypes', 'LocationTypes');
        $data['location_types'] = $locationTypesModel->getAll();

        $this->layout('Projects', 'Boq', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;
        $query = $_GET['query'] ?? null;

        $model = $this->model('Projects', 'Boq', 'Boq');
        $items = $model->getPaged($start, $limit, $query);
        $total = $model->getTotal($query);

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function save()
    {
        $data = $_POST;

        $requiredFields = [
            'project_name' => 'Project Name',
            'client_code' => 'Client'
        ];

        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        if (isset($data['details'])) {
            $data['details'] = json_decode($data['details'], true);
        }
        if (isset($data['locations'])) {
            $data['locations'] = json_decode($data['locations'], true);
        }

        $model = $this->model('Projects', 'Boq', 'Boq');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);
            if ($result = $model->save($data)) {
                $message = $isUpdate ? 'BOQ updated successfully.' : 'BOQ added successfully.';
                $this->json(array_merge($result, ['message' => $message]));
            } else {
                $message = $isUpdate ? 'Failed to update BOQ.' : 'Failed to add BOQ.';
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

        $model = $this->model('Projects', 'Boq', 'Boq');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'BOQ deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete BOQ.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function revise()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'Boq', 'Boq');
        try {
            $result = $model->revise($id);
            $this->json($result);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function details()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'data' => []]);
            return;
        }

        $model = $this->model('Projects', 'Boq', 'Boq');
        $items = $model->getDetails($id);
        $this->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function location_data()
    {
        $limit = $_GET['limit'] ?? 25;
        $offset = $_GET['start'] ?? 0;
        $boq_id = $_GET['boq_id'] ?? null;

        $model = $this->model('Projects', 'Boq', 'Locations');
        $data = $model->getAll($limit, $offset, $boq_id);
        $total = $model->getTotal($boq_id);

        $this->json([
            'success' => true,
            'total' => $total,
            'data' => $data
        ]);
    }

    public function location_save()
    {
        $data = $_POST;
        \Core\Logger::debug('location_save data: ' . json_encode($data));
        $model = $this->model('Projects', 'Boq', 'Locations');
        $result = $model->save($data);
        \Core\Logger::debug('location_save result: ' . json_encode($result));
        $this->json($result);
    }

    public function location_delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'Boq', 'Locations');
        $result = $model->delete($id);
        $this->json($result);
    }

    public function revisions()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'data' => []]);
            return;
        }

        $model = $this->model('Projects', 'Boq', 'Boq');
        $items = $model->getRevisions($id);
        \Core\Logger::debug('revisions data: ' . json_encode($items));
        $this->json([
            'success' => true,
            'data' => $items
        ]);
    }
}
