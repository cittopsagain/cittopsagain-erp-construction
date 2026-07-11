<?php

namespace Applications\Sales\Modules\Clients\Controllers;

use Core\Controller;

class Main extends Controller
{
    public function index()
    {
        $data['title'] = "Clients";
        $data['content_xtype'] = 'clients-grid';
        $this->layout('Sales', 'Clients', 'index', $data);
    }

    public function data()
    {
        $start = $_GET['start'] ?? 0;
        $limit = $_GET['limit'] ?? 25;

        $clientModel = $this->model('Sales', 'Clients', 'Clients');
        $clients = $clientModel->getPaged($start, $limit);
        $total = $clientModel->getTotal();

        $this->json([
            'total' => $total,
            'data' => $clients
        ]);
    }

    public function all()
    {
        $clientModel = $this->model('Sales', 'Clients', 'Clients');
        $clients = $clientModel->getAll();
        $this->json([
            'success' => true,
            'data' => $clients
        ]);
    }

    public function save()
    {
        $data = $_POST;

        $requiredFields = [
            'client_code' => 'Client code',
            'client_name' => 'Client name',
            'add1' => 'Address 1',
            'tel_no' => 'Tel No',
            'tin_no' => 'TIN No'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'message' => "$label is required."]);
                return;
            }
        }

        $clientModel = $this->model('Sales', 'Clients', 'Clients');
        try {
            $isUpdate = isset($data['id']) && is_numeric($data['id']);

            if ($clientModel->save($data)) {
                $message = $isUpdate ? 'Client updated successfully.' : 'Client added successfully.';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $message = $isUpdate ? 'Failed to update client.' : 'Failed to add client.';
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

        $clientModel = $this->model('Sales', 'Clients', 'Clients');
        try {
            if ($clientModel->delete($id)) {
                $this->json(['success' => true, 'message' => 'Client deleted successfully.']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete client.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
