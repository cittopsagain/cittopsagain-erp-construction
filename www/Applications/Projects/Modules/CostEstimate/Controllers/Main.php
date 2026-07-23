<?php

namespace Applications\Projects\Modules\CostEstimate\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Cost Estimate";
        $data['content_xtype'] = 'cost-estimate-main';

        $this->layout('Projects', 'CostEstimate', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;
        $query = $_GET['query'] ?? null;

        $model = $this->model('Projects', 'CostEstimate', 'CostEstimate');

        try {
            $items = $model->getPaged($start, $limit, $query);
            $total = $model->getTotal($query);
        } catch (\Exception $e) {
            $items = [];
            $total = 0;
        }

        $this->json([
            'total' => $total,
            'data' => $items
        ]);
    }

    public function save()
    {
        $id = $_POST['id'] ?? null;
        $data = [
            'estimate_no' => $_POST['estimate_no'] ?? null,
            'estimate_name' => $_POST['estimate_name'] ?? null,
            'project_name' => $_POST['project_name'] ?? null,
            'client_code' => $_POST['client_code'] ?? null,
            'estimate_type_id' => $_POST['estimate_type_id'] ?? null,
            'revision' => $_POST['revision'] ?? 'Rev.0',
            'currency' => $_POST['currency'] ?? 'PHP',
            'costing_date' => $_POST['costing_date'] ?? null,
            'remarks' => $_POST['remarks'] ?? null,
            'status' => $_POST['status'] ?? 'Draft',
            'source_boq_id' => $_POST['source_boq_id'] ?? null,
            'source_boq_revision_id' => $_POST['source_boq_revision_id'] ?? null
        ];

        $model = $this->model('Projects', 'CostEstimate', 'CostEstimate');

        try {
            if ($id) {
                $model->update($id, $data);
                $message = "Estimate updated successfully.";
                $estimate_no = $data['estimate_no'];
            } else {
                $result = $model->create($data);
                $id = $result['id'];
                $estimate_no = $result['estimate_no'];
                $message = "Estimate created successfully.";
            }

            $this->json([
                'success' => true,
                'message' => $message,
                'id' => $id,
                'estimate_no' => $estimate_no
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function boq_summary()
    {
        $boq_id = $_REQUEST['boq_id'] ?? null;
        $is_revision = ($_REQUEST['source'] ?? '') === 'Revision';
        $record_id = $is_revision ? ($_REQUEST['record_id'] ?? $boq_id) : $boq_id;

        \Core\Logger::log('BOQ Id: ' . $boq_id);
        \Core\Logger::log('Record Id: ' . $record_id);
        \Core\Logger::log('Is Revision: ' . ($is_revision));

        if (!$boq_id) {
            $this->json(['success' => false, 'message' => 'BOQ ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'CostEstimate', 'CostEstimate');
        try {
            $summary = $model->getBoqSummary($record_id, $is_revision);
            $this->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function lines()
    {
        \Core\Logger::debug('Estimate ID: ' . $_REQUEST['estimate_id']);
        $estimate_id = $_REQUEST['estimate_id'] ?? null;
        if (!$estimate_id) {
            $this->json(['success' => false, 'message' => 'Estimate ID is required.', 'data' => []]);
            return;
        }

        $model = $this->model('Projects', 'CostEstimate', 'CostEstimate');
        try {
            $lines = $model->getEstimateLines($estimate_id);
            $this->json([
                'success' => true,
                'data' => $lines,
                'total' => count($lines)
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function approved_boqs()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;
        $query = $_GET['query'] ?? null;

        $model = $this->model('Projects', 'CostEstimate', 'CostEstimate');

        try {
            $items = $model->getApprovedBoqs($start, $limit, $query);
            $total = $model->getTotalApprovedBoqs($query);

            $this->json([
                'success' => true,
                'total' => $total,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage(),
                'total' => 0,
                'data' => []
            ]);
        }
    }
}
