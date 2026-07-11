<?php

namespace Applications\Projects\Modules\Quotations\Controllers;

use Core\Controller;

/**
 * Quotations Controller
 *
 * This controller serves as the primary API interface for the Quotations module.
 * It handles all incoming requests from the ExtJS frontend and coordinates
 * with the Quotations Model to perform CRUD operations.
 */
class Main extends Controller
{
    /**
     * Main entry point for the Quotations module.
     * Renders the index view within the application layout.
     */
    public function index()
    {
        $data['title'] = "Quotations";
        $data['content_xtype'] = 'quotations-grid';
        $this->layout('Projects', 'Quotations', 'index', $data);
    }

    /**
     * Fetch paginated quotation data for the main grid.
     */
    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;
        $query = $_GET['query'] ?? null;

        $model = $this->model('Projects', 'Quotations', 'Quotations');
        $data = $model->getPaged($start, $limit, $query);
        $total = $model->getTotal($query);

        $this->json([
            'total' => $total,
            'data' => $data
        ]);
    }

    /**
     * Fetch quotation detail items for a specific quotation.
     */
    public function details()
    {
        $header_id = $_GET['header_id'] ?? null;
        if (!$header_id) {
            $this->json(['success' => false, 'data' => []]);
            return;
        }

        $model = $this->model('Projects', 'Quotations', 'Quotations');
        $details = $model->getDetails($header_id);

        // Map database fields back to what UI expects if necessary
        foreach ($details as &$detail) {
            $detail['component_code'] = $detail['project_component_code'];
        }

        $this->json([
            'success' => true,
            'data' => $details
        ]);
    }

    /**
     * Fetch terms and conditions for a specific quotation.
     */
    public function terms()
    {
        $header_id = $_GET['header_id'] ?? null;
        if (!$header_id) {
            $this->json(['success' => false, 'data' => []]);
            return;
        }

        $model = $this->model('Projects', 'Quotations', 'Quotations');
        $terms = $model->getTerms($header_id);

        // Map database fields back to what UI expects
        foreach ($terms as &$term) {
            $term['content'] = $term['description'];
        }

        $this->json([
            'success' => true,
            'data' => $terms
        ]);
    }

    /**
     * Save quotation data (Header, Details, and Terms).
     * Decodes JSON data from POST and calls the model save method.
     */
    public function save()
    {
        // Decode JSON objects from the request
        $header = json_decode($_POST['header'] ?? '{}', true);
        $details = json_decode($_POST['details'] ?? '[]', true);
        $terms = json_decode($_POST['terms'] ?? '[]', true);
        $buildings = json_decode($_POST['buildings'] ?? '{}', true);

        // Validation
        if (empty($header['client_code'])) {
            $this->json(['success' => false, 'message' => 'Client is required.']);
            return;
        }

        if (!isset($header['discount']) || $header['discount'] === '') {
            $this->json(['success' => false, 'message' => 'Discount is required.']);
            return;
        }

        if (empty($details)) {
            $this->json(['success' => false, 'message' => 'At least one item is required.']);
            return;
        }

        $model = $this->model('Projects', 'Quotations', 'Quotations');
        try {
            $id = $model->save($header, $details, $terms, $buildings);
            $this->json(['success' => true, 'message' => 'Quotation saved successfully.', 'id' => $id]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Fetch building and floor data for a specific quotation.
     */
    public function buildings()
    {
        $header_id = $_GET['header_id'] ?? null;
        if (!$header_id) {
            $this->json(['success' => false, 'data' => null]);
            return;
        }

        $model = $this->model('Projects', 'Quotations', 'Quotations');
        $buildings = $model->getBuildings($header_id);

        $this->json([
            'success' => true,
            'data' => $buildings
        ]);
    }

    /**
     * Delete a quotation.
     */
    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID is required.']);
            return;
        }

        $model = $this->model('Projects', 'Quotations', 'Quotations');
        try {
            if ($model->delete($id)) {
                $this->json(['success' => true, 'message' => 'Quotation deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete quotation.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
