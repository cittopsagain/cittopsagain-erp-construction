<?php

namespace Applications\Projects\Modules\Boq\Models;

use Core\Model;

class Boq extends Model
{
    protected $table = 'boq_headers';

    public function getPaged($start, $limit, $query = null)
    {
        try {
            $whereClause = "";
            $params = [];
            if ($query) {
                $whereClause = " WHERE h.boq_no LIKE :query1 OR h.project_name LIKE :query2 OR c.client_name LIKE :query3 ";
                $params[':query1'] = '%' . $query . '%';
                $params[':query2'] = '%' . $query . '%';
                $params[':query3'] = '%' . $query . '%';
            }

            $sql = "SELECT h.*, c.client_name
                    FROM {$this->table} h
                    LEFT JOIN sales_client c ON h.client_code = c.client_code
                    $whereClause
                    ORDER BY h.id DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$start, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    public function getTotal($query = null)
    {
        try {
            $whereClause = "";
            $params = [];
            if ($query) {
                $whereClause = " LEFT JOIN sales_client c ON h.client_code = c.client_code 
                                 WHERE h.boq_no LIKE :query1 OR h.project_name LIKE :query2 OR c.client_name LIKE :query3 ";
                $params[':query1'] = '%' . $query . '%';
                $params[':query2'] = '%' . $query . '%';
                $params[':query3'] = '%' . $query . '%';
            }

            $sql = "SELECT COUNT(*) FROM {$this->table} h $whereClause";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getById($id)
    {
        try {
            $sql = "SELECT h.*, c.client_name FROM {$this->table} h 
                    LEFT JOIN sales_client c ON h.client_code = c.client_code
                    WHERE h.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $header = $stmt->fetch();

            if ($header) {
                $header['details'] = $this->getDetails($id);
            }

            return $header;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return false;
        }
    }

    public function getDetails($boq_id)
    {
        try {
            $sql = "SELECT d.*, 
                           ct.template_name as composition_template_name, 
                           ct.template_code as composition_template_code,
                           pl.name as location_name,
                           psrv.description as service_name,
                           pt.description as trade_name,
                           ps.description as system_name,
                           im.installation_method_name
                    FROM boq_details d
                    LEFT JOIN composition_templates ct ON d.composition_template_id = ct.id
                    LEFT JOIN project_locations pl ON d.location_id = pl.id
                    LEFT JOIN project_services psrv ON d.service_id = psrv.service_id
                    LEFT JOIN project_trades pt ON d.trade_id = pt.trade_id
                    LEFT JOIN project_systems ps ON d.system_id = ps.system_id
                    LEFT JOIN installation_methods im ON d.installation_method_id = im.installation_method_id
                    WHERE d.boq_id = :boq_id
                    ORDER BY ct.template_code ASC, d.id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':boq_id' => $boq_id]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    public function save($data)
    {
        try {
            $this->db->beginTransaction();

            // Auto-generate boq_no if not provided or set to empty/placeholder
            if (empty($data['boq_no']) || $data['boq_no'] === 'Auto-generated if blank') {
                $year = date('Y');
                $stmt = $this->db->query("SELECT MAX(boq_no) FROM {$this->table} WHERE boq_no LIKE 'BOQ-$year%'");
                $max_no = $stmt->fetchColumn();
                if ($max_no) {
                    $parts = explode('-', $max_no);
                    $series = (int)end($parts);
                    $data['boq_no'] = sprintf("BOQ-%s-%06d", $year, $series + 1);
                } else {
                    $data['boq_no'] = sprintf("BOQ-%s-000001", $year);
                }
            }

            if (isset($data['id']) && is_numeric($data['id'])) {
                $success = $this->update($data);
                $boq_id = $data['id'];
            } else {
                $sql = "INSERT INTO {$this->table} (
                            boq_no, project_name, client_code, location, revision, status, remarks,
                            created_by
                        ) VALUES (
                            :boq_no, :project_name, :client_code, :location, :revision, :status, :remarks,
                            :created_by
                        )";

                $stmt = $this->db->prepare($sql);

                $success = $stmt->execute([
                    ':boq_no' => $data['boq_no'],
                    ':project_name' => $data['project_name'] ?? null,
                    ':client_code' => $data['client_code'] ?? null,
                    ':location' => $data['location'] ?? null,
                    ':revision' => $data['revision'] ?? 'Rev. 0',
                    ':status' => $data['status'] ?? 'Draft',
                    ':remarks' => $data['remarks'] ?? null,
                    ':created_by' => $this->getCurrentUserId()
                ]);

                if ($success) {
                    $boq_id = $this->db->lastInsertId();
                }
            }

            if ($success && isset($data['details'])) {
                $this->saveDetails($boq_id, $data['details']);
            }

            if ($success && isset($data['locations'])) {
                $this->saveLocations($boq_id, $data['locations']);
            }

            $this->db->commit();
            \Core\Logger::debug('Boq::save committed for boq_id: ' . $boq_id);
            return [
                'success' => true,
                'id' => $boq_id,
                'boq_no' => $data['boq_no'],
                'revision' => $data['revision'] ?? 'Rev. 0'
            ];
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            \Core\Logger::error('Boq::save error: ' . $e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    private function saveLocations($boq_id, $locations)
    {
        if (is_string($locations)) {
            $locations = json_decode($locations, true);
        }

        if (empty($locations)) {
            // If empty, just delete all for this BOQ
            $sql = "DELETE FROM project_locations WHERE boq_id = :boq_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':boq_id' => $boq_id]);
            return;
        }

        // Get existing location IDs to know which ones to delete
        $stmt = $this->db->prepare("SELECT id FROM project_locations WHERE boq_id = :boq_id");
        $stmt->execute([':boq_id' => $boq_id]);
        $existingIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // First pass: Insert/Update records and collect real IDs
        // This is needed to resolve temporary parent IDs
        $tempIdMap = [];
        $newIds = [];
        $pendingParentUpdates = [];

        foreach ($locations as $row) {
            // Skip empty locations
            if (empty($row['code']) && empty($row['name'])) {
                continue;
            }

            $parentId = !empty($row['parent_id']) ? $row['parent_id'] : null;
            $isTempParent = $parentId && !is_numeric($parentId);

            if (isset($row['id']) && !empty($row['id']) && is_numeric($row['id'])) {
                // Update
                $sql = "UPDATE project_locations SET 
                            code = :code, 
                            name = :name, 
                            type_id = :type_id, 
                            parent_id = :parent_id
                        WHERE id = :id AND boq_id = :boq_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':code' => $row['code'] ?? null,
                    ':name' => $row['name'] ?? null,
                    ':type_id' => (!empty($row['type_id']) && is_numeric($row['type_id'])) ? $row['type_id'] : null,
                    ':parent_id' => !$isTempParent ? $parentId : null,
                    ':id' => $row['id'],
                    ':boq_id' => $boq_id
                ]);
                $realId = $row['id'];
                $newIds[] = $realId;
            } else {
                // Insert
                $sql = "INSERT INTO project_locations (code, name, type_id, parent_id, boq_id) 
                        VALUES (:code, :name, :type_id, :parent_id, :boq_id)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':code' => $row['code'] ?? null,
                    ':name' => $row['name'] ?? null,
                    ':type_id' => (!empty($row['type_id']) && is_numeric($row['type_id'])) ? $row['type_id'] : null,
                    ':parent_id' => !$isTempParent ? $parentId : null,
                    ':boq_id' => $boq_id
                ]);
                $realId = $this->db->lastInsertId();
                $newIds[] = $realId;

                // Map temporary ID to real ID if provided
                if (isset($row['id']) && !empty($row['id'])) {
                    $tempIdMap[$row['id']] = $realId;
                }
            }

            if ($isTempParent) {
                $pendingParentUpdates[] = [
                    'child_id' => $realId,
                    'temp_parent_id' => $parentId
                ];
            }
        }

        // Second pass: Resolve temporary parent IDs
        foreach ($pendingParentUpdates as $update) {
            if (isset($tempIdMap[$update['temp_parent_id']])) {
                $sql = "UPDATE project_locations SET parent_id = :parent_id WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':parent_id' => $tempIdMap[$update['temp_parent_id']],
                    ':id' => $update['child_id']
                ]);
            }
        }

        // Delete locations that are no longer in the list
        $toDelete = array_diff($existingIds, $newIds);
        if (!empty($toDelete)) {
            $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
            $sql = "DELETE FROM project_locations WHERE id IN ($placeholders) AND boq_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge(array_values($toDelete), [$boq_id]));
        }
    }

    private function saveDetails($boq_id, $details)
    {
        if (is_string($details)) {
            $details = json_decode($details, true);
        }

        if (empty($details)) {
            // Delete existing details
            $sql = "DELETE FROM boq_details WHERE boq_id = :boq_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':boq_id' => $boq_id]);
            return;
        }

        // We want to keep existing detail IDs if they are still in the list to avoid breaking other things
        // but boq_details doesn't seem to be referenced elsewhere by ID.
        // However, for consistency with locations, let's just do the delete and re-insert 
        // OR do it like locations. Given that boq_details doesn't have a parent_id, 
        // delete and re-insert is mostly fine, EXCEPT it might be better to keep IDs if possible.
        // BUT the current implementation of saveDetails ALREADY does delete and re-insert.

        // Log detail counts for debugging
        \Core\Logger::debug('Boq::saveDetails for boq_id: ' . $boq_id . ', received details count: ' . count($details));

        // I will keep it simple for now as the original was.
        $sql = "DELETE FROM boq_details WHERE boq_id = :boq_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':boq_id' => $boq_id]);

        // If no details, we're done (deleted all)
        if (empty($details)) {
            return;
        }

        // Insert new details
        $sql = "INSERT INTO boq_details (boq_id, composition_template_id, location_id, service_id, trade_id, system_id, installation_method_id, description, quantity) 
                VALUES (:boq_id, :composition_template_id, :location_id, :service_id, :trade_id, :system_id, :installation_method_id, :description, :quantity)";
        $stmt = $this->db->prepare($sql);

        foreach ($details as $row) {
            $template_id = (!empty($row['composition_template_id']) && is_numeric($row['composition_template_id'])) ? $row['composition_template_id'] : null;

            if (empty($template_id)) {
                continue;
            }

            $stmt->execute([
                ':boq_id' => $boq_id,
                ':composition_template_id' => $template_id,
                ':location_id' => (!empty($row['location_id']) && is_numeric($row['location_id'])) ? $row['location_id'] : null,
                ':service_id' => (!empty($row['service_id']) && is_numeric($row['service_id'])) ? $row['service_id'] : null,
                ':trade_id' => (!empty($row['trade_id']) && is_numeric($row['trade_id'])) ? $row['trade_id'] : null,
                ':system_id' => (!empty($row['system_id']) && is_numeric($row['system_id'])) ? $row['system_id'] : null,
                ':installation_method_id' => (!empty($row['installation_method_id']) && is_numeric($row['installation_method_id'])) ? $row['installation_method_id'] : null,
                ':description' => $row['description'] ?? null,
                ':quantity' => (isset($row['quantity']) && is_numeric($row['quantity'])) ? $row['quantity'] : 0
            ]);
        }
    }

    public function update($data)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                            boq_no = :boq_no,
                            project_name = :project_name,
                            client_code = :client_code,
                            location = :location,
                            revision = :revision,
                            status = :status,
                            remarks = :remarks,
                            updated_at = NOW(),
                            updated_by = :updated_by
                        WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $params = [
                ':boq_no' => $data['boq_no'],
                ':project_name' => $data['project_name'] ?? null,
                ':client_code' => $data['client_code'] ?? null,
                ':location' => $data['location'] ?? null,
                ':revision' => $data['revision'] ?? 'Rev. 0',
                ':status' => $data['status'] ?? 'Draft',
                ':remarks' => $data['remarks'] ?? null,
                ':updated_by' => $this->getCurrentUserId(),
                ':id' => $data['id']
            ];

            \Core\Logger::debug('Boq::update params: ' . json_encode($params));
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            \Core\Logger::error('Boq::update error: ' . $e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $this->db->beginTransaction();

            // Delete project locations
            $sql = "DELETE FROM project_locations WHERE boq_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            // Delete boq details
            $sql = "DELETE FROM boq_details WHERE boq_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            // Delete boq header
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([':id' => $id]);

            $this->db->commit();
            return $success;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    public function revise($id)
    {
        try {
            $this->db->beginTransaction();

            // Get original BOQ
            $original = $this->getById($id);
            if (!$original) {
                throw new \Exception("Original BOQ not found.");
            }

            // 1. Copy current version to Revision Tables (as an audit log of the version being revised)
            // Copy Header to boq_headers_revision
            $headerCols = array_keys($original);
            // Filter out details as it's not a column
            $headerCols = array_filter($headerCols, function ($col) {
                return $col !== 'details' && $col !== 'client_name';
            });
            $colsStr = implode(', ', $headerCols);
            $placeholders = implode(', ', array_map(function ($col) {
                return ":$col";
            }, $headerCols));
            $sql = "INSERT INTO boq_headers_revision ($colsStr) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $headerParams = [];
            foreach ($headerCols as $col) {
                $headerParams[":$col"] = $original[$col];
            }
            $stmt->execute($headerParams);

            // Copy Locations to project_locations_revision
            $locSql = "SELECT * FROM project_locations WHERE boq_id = :boq_id";
            $locStmt = $this->db->prepare($locSql);
            $locStmt->execute([':boq_id' => $id]);
            $oldLocations = $locStmt->fetchAll();
            if (!empty($oldLocations)) {
                $locCols = array_keys($oldLocations[0]);
                $locColsStr = implode(', ', $locCols);
                $locPlaceholders = implode(', ', array_map(function ($col) {
                    return ":$col";
                }, $locCols));
                $sql = "INSERT INTO project_locations_revision ($locColsStr) VALUES ($locPlaceholders)";
                $stmt = $this->db->prepare($sql);
                foreach ($oldLocations as $loc) {
                    $locParams = [];
                    foreach ($locCols as $col) {
                        $locParams[":$col"] = $loc[$col];
                    }
                    $stmt->execute($locParams);
                }
            }

            // Copy Details to boq_details_revision
            $details = $this->getDetails($id);
            if (!empty($details)) {
                $detailCols = ['id', 'boq_id', 'composition_template_id', 'location_id', 'service_id', 'trade_id', 'system_id', 'installation_method_id', 'description', 'quantity'];
                $detailColsStr = implode(', ', $detailCols);
                $detailPlaceholders = implode(', ', array_map(function ($col) {
                    return ":$col";
                }, $detailCols));
                $sql = "INSERT INTO boq_details_revision ($detailColsStr) VALUES ($detailPlaceholders)";
                $stmt = $this->db->prepare($sql);
                foreach ($details as $detail) {
                    $detailParams = [];
                    foreach ($detailCols as $col) {
                        $detailParams[":$col"] = $detail[$col] ?? null;
                    }
                    $stmt->execute($detailParams);
                }
            }

            // 2. Update the existing record in the main tables
            // Prepare new revision string
            $revision = $original['revision'] ?? 'Rev. 0';
            if (preg_match('/Rev\. (\d+)/', $revision, $matches)) {
                $newRevNum = (int)$matches[1] + 1;
                $newRevision = "Rev. " . $newRevNum;
            } else {
                $newRevision = "Rev. 1";
            }

            // Update existing BOQ header
            $sql = "UPDATE {$this->table} SET 
                        revision = :revision, 
                        status = :status,
                        updated_at = NOW(),
                        updated_by = :updated_by
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':revision' => $newRevision,
                ':status' => 'Draft',
                ':updated_by' => $this->getCurrentUserId(),
                ':id' => $id
            ]);

            $this->db->commit();
            return ['success' => true, 'id' => $id, 'revision' => $newRevision, 'message' => 'BOQ revised to ' . $newRevision . ' and set to Draft.'];
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            \Core\Logger::error('Boq::revise error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getRevisions($boq_id)
    {
        $sql = "SELECT * FROM boq_headers_revision WHERE id = :boq_id ORDER BY revision_id DESC";
        \Core\Logger::log($sql);
        \Core\Logger::log($boq_id);
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':boq_id' => $boq_id]);
        return $stmt->fetchAll();
    }
}
