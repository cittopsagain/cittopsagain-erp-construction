<?php

namespace Applications\Projects\Modules\CompositionTemplates\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Composition Templates";
        $data['content_xtype'] = 'composition-template-main';

        $imModel = $this->model('Projects', 'InstallationMethods', 'InstallationMethods');
        $data['installation_methods'] = $imModel->getAll();

        $tradeModel = $this->model('Projects', 'Trades', 'Trades');
        $data['trades'] = $tradeModel->getAll();

        $phaseModel = $this->model('Projects', 'WorkPhases', 'WorkPhases');
        $data['work_phases'] = $phaseModel->getAll();

        $systemModel = $this->model('Projects', 'Systems', 'Systems');
        $data['systems'] = $systemModel->getAll();

        $this->layout('Projects', 'CompositionTemplates', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $model = $this->model('Projects', 'CompositionTemplates', 'CompositionTemplate');
        $items = $model->getPaged($start, $limit);
        $total = $model->getTotal();

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function save()
    {
        $data = $_POST;

        $requiredFields = [
            'template_code' => 'Template code',
            'template_name' => 'Template name',
            'installation_method_id' => 'Installation method',
            'trade_id' => 'Trade',
            'phase_id' => 'Work Phase',
            'system_id' => 'System'
        ];

        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $model = $this->model('Projects', 'CompositionTemplates', 'CompositionTemplate');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);

            if ($result = $model->save($data)) {
                $message = $isUpdate ? 'Template updated successfully.' : 'Template added successfully.';
                $jsonResponse = ['success' => true, 'message' => $message];
                if (!$isUpdate) {
                    $jsonResponse['id'] = $result;
                    // Fetch the created_at for new records
                    $newRecord = $model->getPaged(0, 1); // getPaged(0, 1) should return the latest record as it's ordered by id DESC
                    if (!empty($newRecord) && $newRecord[0]['id'] == $result) {
                        $jsonResponse['created_at'] = $newRecord[0]['created_at'];
                    }
                }
                $this->json($jsonResponse);
            } else {
                $message = $isUpdate ? 'Failed to update template.' : 'Failed to add template.';
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

        $model = $this->model('Projects', 'CompositionTemplates', 'CompositionTemplate');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Template deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete template.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function detailData()
    {
        $template_id = $_GET['template_id'] ?? null;
        $type = $_GET['detail_type'] ?? null;
        if (!$template_id) {
            $this->json(['total' => 0, 'data' => []]);
            return;
        }

        $model = $this->model('Projects', 'CompositionTemplates', 'CompositionTemplateDetail');
        $items = $model->getByTemplateId($template_id, $type);

        $this->json([
            'total' => count($items),
            'data' => $items
        ]);
    }

    public function saveDetail()
    {
        $data = $_POST;

        if (!isset($data['template_id']) || !is_numeric($data['template_id'])) {
            $this->json(['success' => false, 'message' => 'Template ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'CompositionTemplates', 'CompositionTemplateDetail');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);

            if ($result = $model->save($data)) {
                $message = $isUpdate ? 'Component updated successfully.' : 'Component added successfully.';
                $jsonResponse = ['success' => true, 'message' => $message];
                if (!$isUpdate) {
                    $jsonResponse['id'] = $result;
                }
                $this->json($jsonResponse);
            } else {
                $message = $isUpdate ? 'Failed to update component.' : 'Failed to add component.';
                $this->json(['success' => false, 'message' => $message]);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deleteDetail()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'CompositionTemplates', 'CompositionTemplateDetail');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Component removed successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to remove component.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
