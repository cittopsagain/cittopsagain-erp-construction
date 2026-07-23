<?php

namespace Applications\Projects\Modules\CostEstimate\Models;

use Core\Logger;
use Core\Model;

class CostEstimate extends Model
{
    public function getPaged($start, $limit, $query = null)
    {
        $sql = "SELECT e.*, t.estimate_type as type_name, 
                       CASE 
                           WHEN e.source_boq_revision_id = 'Revision' THEN (SELECT revision FROM boq_headers_revision WHERE id = e.source_boq_id LIMIT 1)
                           ELSE (SELECT revision FROM boq_headers WHERE id = e.source_boq_id LIMIT 1)
                       END as boq_revision,
                       CASE 
                           WHEN e.source_boq_revision_id = 'Revision' THEN (SELECT boq_no FROM boq_headers_revision WHERE id = e.source_boq_id LIMIT 1)
                           ELSE (SELECT boq_no FROM boq_headers WHERE id = e.source_boq_id LIMIT 1)
                       END as boq_no
                FROM project_cost_estimates e
                LEFT JOIN project_estimate_types t ON e.estimate_type_id = t.id";
        $params = [];
        if (!empty($query)) {
            $q = "%" . trim($query) . "%";
            $sql = "SELECT * FROM ($sql) as combined 
                    WHERE combined.estimate_no LIKE :q1 
                       OR combined.estimate_name LIKE :q2 
                       OR combined.project_name LIKE :q3 
                       OR combined.boq_no LIKE :q4";
            $params[':q1'] = $q;
            $params[':q2'] = $q;
            $params[':q3'] = $q;
            $params[':q4'] = $q;
        }
        $sql .= " ORDER BY id DESC LIMIT :start, :limit";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':start', (int)$start, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotal($query = null)
    {
        $sql = "SELECT e.*, 
                       CASE 
                           WHEN e.source_boq_revision_id = 'Revision' THEN (SELECT boq_no FROM boq_headers_revision WHERE id = e.source_boq_id LIMIT 1)
                           ELSE (SELECT boq_no FROM boq_headers WHERE id = e.source_boq_id LIMIT 1)
                       END as boq_no
                FROM project_cost_estimates e";
        $params = [];
        if (!empty($query)) {
            $q = "%" . trim($query) . "%";
            $sql = "SELECT COUNT(*) FROM ($sql) as combined 
                    WHERE combined.estimate_no LIKE :q1 
                       OR combined.estimate_name LIKE :q2 
                       OR combined.project_name LIKE :q3
                       OR combined.boq_no LIKE :q4";
            $params[':q1'] = $q;
            $params[':q2'] = $q;
            $params[':q3'] = $q;
            $params[':q4'] = $q;
        } else {
            $sql = "SELECT COUNT(*) FROM ($sql) as combined";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function create($data)
    {
        $this->db->beginTransaction();
        try {
            // Auto-generate estimate number if not provided
            if (empty($data['estimate_no'])) {
                $stmt = $this->db->query("SELECT MAX(id) FROM project_cost_estimates");
                $maxId = $stmt->fetchColumn() ?: 0;
                $data['estimate_no'] = 'EST-' . date('Y') . '-' . str_pad($maxId + 1, 6, '0', STR_PAD_LEFT);
            }

            $sql = "INSERT INTO project_cost_estimates (
                        estimate_no, estimate_name, project_name, client_code, 
                        estimate_type_id, revision, currency, costing_date, 
                        remarks, status, source_boq_id, source_boq_revision_id, 
                        created_by, created_at
                    )
                    VALUES (
                        :estimate_no, :estimate_name, :project_name, :client_code, 
                        :estimate_type_id, :revision, :currency, :costing_date, 
                        :remarks, :status, :source_boq_id, :source_boq_revision_id, 
                        :created_by, NOW()
                    )";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':estimate_no' => $data['estimate_no'],
                ':estimate_name' => $data['estimate_name'] ?? null,
                ':project_name' => $data['project_name'] ?? null,
                ':client_code' => $data['client_code'] ?? null,
                ':estimate_type_id' => $data['estimate_type_id'] ?? null,
                ':revision' => $data['revision'] ?? 'Rev.0',
                ':currency' => $data['currency'] ?? 'PHP',
                ':costing_date' => $data['costing_date'] ?? null,
                ':remarks' => $data['remarks'] ?? null,
                ':status' => $data['status'] ?? 'Draft',
                ':source_boq_id' => $data['source_boq_id'] ?? null,
                ':source_boq_revision_id' => $data['source_boq_revision_id'] ?? null,
                ':created_by' => $this->getCurrentUserId()
            ]);
            $estimate_id = $this->db->lastInsertId();

            if (!empty($data['source_boq_id'])) {
                \Core\Logger::log('Copying BOQ data from source BOQ ID: ' . $data['source_boq_id']);
                $is_revision = ($data['source'] ?? '') === 'Revision';
                $record_id = $is_revision ? ($data['source_boq_revision_id'] ?? $data['source_boq_id']) : $data['source_boq_id'];
                \Core\Logger::log('Is Revision: ' . ($is_revision ? 'Yes' : 'No'));
                \Core\Logger::log('Record Id: ' . $record_id);
                \Core\Logger::debug(json_encode($data));
                $this->copyBoqData($estimate_id, $record_id, $is_revision);
            }

            $this->db->commit();
            return [
                'id' => $estimate_id,
                'estimate_no' => $data['estimate_no']
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function copyBoqData($estimate_id, $record_id, $is_revision)
    {
        $table = $is_revision ? 'boq_details_revision' : 'boq_details';
        $idField = $is_revision ? 'revision_id' : 'boq_id';

        // Copy BOQ details to estimate lines
        $sql = "INSERT INTO project_estimate_lines (
                    estimate_id, boq_line_id, composition_template_id, 
                    location_id, description, quantity
                )
                SELECT 
                    :estimate_id, id, composition_template_id, 
                    location_id, description, quantity
                FROM $table
                WHERE $idField = :record_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':estimate_id' => $estimate_id,
            ':record_id' => $record_id
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE project_cost_estimates SET 
                estimate_no = :estimate_no,
                estimate_name = :estimate_name,
                project_name = :project_name,
                client_code = :client_code,
                estimate_type_id = :estimate_type_id,
                revision = :revision,
                currency = :currency,
                costing_date = :costing_date,
                remarks = :remarks,
                status = :status,
                source_boq_id = :source_boq_id,
                source_boq_revision_id = :source_boq_revision_id,
                updated_at = NOW()
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':estimate_no' => $data['estimate_no'],
            ':estimate_name' => $data['estimate_name'] ?? null,
            ':project_name' => $data['project_name'] ?? null,
            ':client_code' => $data['client_code'] ?? null,
            ':estimate_type_id' => $data['estimate_type_id'] ?? null,
            ':revision' => $data['revision'] ?? 'Rev.0',
            ':currency' => $data['currency'] ?? 'PHP',
            ':costing_date' => $data['costing_date'] ?? null,
            ':remarks' => $data['remarks'] ?? null,
            ':status' => $data['status'] ?? 'Draft',
            ':source_boq_id' => $data['source_boq_id'] ?? null,
            ':source_boq_revision_id' => $data['source_boq_revision_id'] ?? null,
            ':id' => $id
        ]);
    }

    public function getBoqSummary($boq_id, $is_revision = false)
    {
        $table = $is_revision ? 'boq_details_revision' : 'boq_details';
        $project_location_table = $is_revision ? 'project_locations_revision' : 'project_locations';
        $idField = $is_revision ? 'revision_id' : 'boq_id';

        // Get BOQ Lines count
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM $table WHERE $idField = :boq_id");
        $stmt->execute([':boq_id' => $boq_id]);
        $lines = $stmt->fetchColumn();

        // Get Unique Composition Templates count
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT composition_template_id) FROM $table WHERE $idField = :boq_id");
        $stmt->execute([':boq_id' => $boq_id]);
        $templates = $stmt->fetchColumn();

        // Get Locations count
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM $project_location_table WHERE $idField = :boq_id");
        $stmt->execute([':boq_id' => $boq_id]);
        $locations = $stmt->fetchColumn();

        // Estimate components (Rough estimate)
        // Usually Composition Template Details count * BOQ Lines
        $stmt = $this->db->prepare("
            SELECT COUNT(td.id) 
            FROM $table bd
            JOIN composition_template_details td ON bd.composition_template_id = td.template_id
            WHERE bd.$idField = :boq_id
        ");
        $stmt->execute([':boq_id' => $boq_id]);
        $components = $stmt->fetchColumn();

        return [
            'lines' => $lines,
            'templates' => $templates,
            'locations' => $locations,
            'components' => $components
        ];
    }

    public function getEstimateLines($estimate_id)
    {
        $sql = "SELECT l.*, 
                       COALESCE((SELECT SUM(hours * rate) FROM composition_template_details WHERE template_id = l.composition_template_id AND detail_type = 'LABOR'), 0) as labor_cost,
                       COALESCE((SELECT SUM(qty_formula * rate) FROM composition_template_details WHERE template_id = l.composition_template_id AND detail_type = 'MATERIAL'), 0) as material_cost
                FROM project_estimate_lines l
                WHERE l.estimate_id = :estimate_id
                ORDER BY l.id ASC";
        \Core\Logger::log($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':estimate_id' => $estimate_id]);
        $lines = $stmt->fetchAll();

        foreach ($lines as &$line) {
            $line['unit_cost'] = (float)($line['labor_cost'] ?? 0) + (float)($line['material_cost'] ?? 0);
            $line['total_cost'] = (float)$line['unit_cost'] * (float)$line['quantity'];
        }

        return $lines;
    }

    public function getApprovedBoqs($start = 0, $limit = 25, $query = null)
    {
        $sql_base = "SELECT id, boq_no, project_name, client_code, revision, status, created_at, 'Current' as source, id as record_id
                FROM boq_headers
                WHERE status = 'Approved'
                UNION ALL
                SELECT id, boq_no, project_name, client_code, revision, status, created_at, 'Revision' as source, revision_id as record_id
                FROM boq_headers_revision
                WHERE status = 'Approved'";

        $sql = "SELECT main.*, sc.client_name FROM ($sql_base) AS main LEFT JOIN sales_client sc ON main.client_code = sc.client_code";

        $params = [];
        if (!empty($query)) {
            $q = "%" . trim($query) . "%";
            $sql = "SELECT * FROM ($sql) as combined 
                    WHERE combined.boq_no LIKE :q1 
                       OR combined.project_name LIKE :q2 
                       OR combined.client_name LIKE :q3 
                       OR combined.client_code LIKE :q4";
            $params[':q1'] = $q;
            $params[':q2'] = $q;
            $params[':q3'] = $q;
            $params[':q4'] = $q;
        }

        $sql .= " ORDER BY boq_no DESC, revision DESC LIMIT " . (int)$start . ", " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getTotalApprovedBoqs($query = null)
    {
        $sql_base = "SELECT id, boq_no, project_name, client_code, revision, status, created_at, 'Current' as source
                FROM boq_headers
                WHERE status = 'Approved'
                UNION ALL
                SELECT id, boq_no, project_name, client_code, revision, status, created_at, 'Revision' as source
                FROM boq_headers_revision
                WHERE status = 'Approved'";

        $sql = "SELECT main.*, sc.client_name FROM ($sql_base) AS main LEFT JOIN sales_client sc ON main.client_code = sc.client_code";

        $params = [];
        if (!empty($query)) {
            $q = "%" . trim($query) . "%";
            $sql = "SELECT COUNT(*) FROM ($sql) as combined 
                    WHERE combined.boq_no LIKE :q1 
                       OR combined.project_name LIKE :q2 
                       OR combined.client_name LIKE :q3 
                       OR combined.client_code LIKE :q4";
            $params[':q1'] = $q;
            $params[':q2'] = $q;
            $params[':q3'] = $q;
            $params[':q4'] = $q;
        } else {
            $sql = "SELECT COUNT(*) FROM ($sql) as combined";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
