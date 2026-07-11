<?php

namespace Applications\Projects\Modules\Quotations\Models;

use Core\Model;

/**
 * Quotations Model
 * Handles database operations for Quotation Header, Details, and Terms.
 */
class Quotations extends Model
{
    /**
     * Get paginated list of quotations for the main grid.
     *
     * @param int $start Starting record offset
     * @param int $limit Number of records to return
     * @param string|null $query Search query
     * @return array List of quotations with client names
     */
    public function getPaged($start, $limit, $query = null)
    {
        try {
            $whereClause = "";
            $params = [];
            if ($query) {
                $whereClause = " WHERE h.quot_ctrl_no LIKE :query1 OR c.client_name LIKE :query2 ";
                $params[':query1'] = '%' . $query . '%';
                $params[':query2'] = '%' . $query . '%';
            }

            $sql = "SELECT h.*, c.client_name, h.project_name, (
                        SELECT description FROM project_services WHERE service_code = h.service_code
                    ) AS service_desc
                    FROM sales_quotation_header h
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

    /**
     * Get the total count of quotations for pagination.
     *
     * @param string|null $query Search query
     * @return int Total number of quotations
     */
    public function getTotal($query = null)
    {
        try {
            $whereClause = "";
            $params = [];
            if ($query) {
                $whereClause = " LEFT JOIN sales_client c ON h.client_code = c.client_code 
                                 WHERE h.quot_ctrl_no LIKE :query1 OR c.client_name LIKE :query2 ";
                $params[':query1'] = '%' . $query . '%';
                $params[':query2'] = '%' . $query . '%';
            }

            $sql = "SELECT COUNT(*) FROM sales_quotation_header h $whereClause";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    /**
     * Get a specific quotation header by its ID.
     *
     * @param int $header_id The unique ID of the quotation header.
     * @return array|false The header record with joined client and service info, or false if not found.
     */
    public function getHeader($header_id)
    {
        try {
            $sql = "SELECT h.*, c.client_name, c.add1, c.add2, h.project_name, (
                        SELECT description FROM project_services WHERE service_code = h.service_code
                    ) AS service_desc,
                    (
                        SELECT long_description FROM project_services WHERE service_code = h.service_code
                    ) AS service_long_desc,
                    (
                        SELECT user_name FROM app_users u WHERE u.user_id = h.created_by
                    ) AS prepared_by
                    FROM sales_quotation_header h
                    LEFT JOIN sales_client c ON h.client_code = c.client_code
                    WHERE h.id = :header_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':header_id' => $header_id]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return false;
        }
    }

    /**
     * Get quotation details (items) for a specific header.
     *
     * @param int $header_id The ID of the quotation header
     * @return array List of detail items with unit and item descriptions
     */
    public function getDetails($header_id)
    {
        try {
            $sql = "SELECT d.*, u.description as unit_description, i.item_desc as original_item_desc, 
                    pc.description as component_description, pc.component_id,
                    CASE 
                        WHEN d.detail_type = 'LABOR' THEN 
                            (d.no_of_men * d.days * d.hours * d.price) + (d.no_of_men * d.ot_hrs * d.ot_rate)
                        WHEN d.detail_type = 'OVERHEAD' THEN
                            CASE 
                                WHEN d.overhead_computation_type = '%' THEN 
                                    -- This is tricky because it depends on other items. 
                                    -- For now we return what's in the DB or calculate in JS.
                                    -- The save() method should probably pre-calculate total_price for DB if needed,
                                    -- or we calculate it here if we have the base.
                                    d.total_price 
                                ELSE 
                                    (d.qty * d.overhead_value)
                            END
                        ELSE 
                            (d.qty * d.price * (1 + COALESCE(d.markup_percent, 0) / 100))
                    END AS total_price
                    FROM sales_quotation_details d
                    LEFT JOIN sales_unit u ON d.unit_code = u.unit_code
                    LEFT JOIN inv_items i ON d.item_code = i.item_code
                    LEFT JOIN project_components pc ON d.project_component_code = pc.component_code
                    WHERE d.quot_ctrl_no = (SELECT quot_ctrl_no FROM sales_quotation_header WHERE id = :header_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':header_id' => $header_id]);
            $details = $stmt->fetchAll();

            // Default detail_type to BOQ if it doesn't exist in the database yet
            foreach ($details as &$detail) {
                if (!isset($detail['detail_type']) || empty($detail['detail_type'])) {
                    $detail['detail_type'] = 'BOQ';
                }
            }
            return $details;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    /**
     * Get terms and conditions for a specific quotation.
     *
     * @param int $header_id The ID of the quotation header
     * @return array List of terms sorted by sort_order
     */
    public function getTerms($header_id)
    {
        try {
            $sql = "SELECT * FROM sales_quotation_terms_conditions 
                    WHERE quot_ctrl_no = (SELECT quot_ctrl_no FROM sales_quotation_header WHERE id = :header_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':header_id' => $header_id]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    /**
     * Get building and floor data for a specific quotation.
     *
     * @param int $header_id The ID of the quotation header
     * @return array|null Building and floor data
     */
    public function getBuildings($header_id)
    {
        try {
            $sql = "SELECT buildings_data FROM sales_quotation_header WHERE id = :header_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':header_id' => $header_id]);
            $data = $stmt->fetchColumn();
            return $data ? json_decode($data, true) : null;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return null;
        }
    }

    /**
     * Get the current user from the session using the base model's utility.
     *
     * @return array|null User session data.
     */
    public function getCurrentUserFromSession()
    {
        return $this->getCurrentUser();
    }

    /**
     * Save quotation (Insert or Update).
     * Handles transaction for header, details, and terms.
     *
     * @param array $header Header data
     * @param array $details List of detail items
     * @param array $terms List of terms and conditions
     * @param array $buildings Building and floor data
     * @return int|bool The header ID on success, false otherwise
     */
    public function save($header, $details, $terms = [], $buildings = [])
    {
        try {
            $this->db->beginTransaction();

            if (isset($header['id']) && is_numeric($header['id'])) {
                // Update existing quotation header
                $sql = "UPDATE sales_quotation_header SET 
                            service_code = :service_code,
                            project_name = :project_name,
                            quot_ctrl_no = :quot_ctrl_no,
                            client_code = :client_code,
                            contact_person = :contact_person,
                            terms = :terms,
                            term_remarks = :term_remarks,
                            discount = :discount,
                            remarks = :remarks,
                            status = :status,
                            buildings_data = :buildings_data
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':service_code' => $header['service_code'],
                    ':project_name' => $header['project_name'] ?? null,
                    ':quot_ctrl_no' => $header['quot_ctrl_no'],
                    ':client_code' => $header['client_code'],
                    ':contact_person' => $header['contact_person'],
                    ':terms' => $header['terms'],
                    ':term_remarks' => $header['term_remarks'],
                    ':discount' => $header['discount'],
                    ':remarks' => $header['remarks'],
                    ':status' => $header['status'] ?? 'SAVED',
                    ':buildings_data' => !empty($buildings) ? json_encode($buildings) : null,
                    ':id' => $header['id']
                ]);
                $header_id = $header['id'];

                // Delete old details and terms before re-inserting
                $this->db->prepare("DELETE FROM sales_quotation_details WHERE quot_ctrl_no = ?")->execute([$header['quot_ctrl_no']]);
                $this->db->prepare("DELETE FROM sales_quotation_terms_conditions WHERE quot_ctrl_no = ?")->execute([$header['quot_ctrl_no']]);
            } else {
                // Auto-generate quot_ctrl_no if not provided
                if (empty($header['quot_ctrl_no'])) {
                    $year = date('Y');
                    $stmt = $this->db->query("SELECT MAX(quot_ctrl_no) FROM sales_quotation_header WHERE quot_ctrl_no LIKE 'Q$year%'");
                    $max_ctrl_no = $stmt->fetchColumn();
                    \Core\Logger::debug('Max Ctrl No: ' . $max_ctrl_no);
                    if ($max_ctrl_no) {
                        $series = (int)substr($max_ctrl_no, -6);
                        $header['quot_ctrl_no'] = sprintf("Q%s%06d", $year, $series + 1);
                    } else {
                        $header['quot_ctrl_no'] = sprintf("Q%s000001", $year);
                    }
                }

                // Insert new quotation header
                $sql = "INSERT INTO sales_quotation_header (service_code, project_name, quot_ctrl_no, client_code, contact_person, terms, term_remarks, discount, remarks, status, created_by, buildings_data) 
                        VALUES (:service_code, :project_name, :quot_ctrl_no, :client_code, :contact_person, :terms, :term_remarks, :discount, :remarks, :status, :created_by, :buildings_data)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':service_code' => $header['service_code'],
                    ':project_name' => $header['project_name'] ?? null,
                    ':quot_ctrl_no' => $header['quot_ctrl_no'],
                    ':client_code' => $header['client_code'],
                    ':contact_person' => $header['contact_person'],
                    ':terms' => $header['terms'],
                    ':term_remarks' => $header['term_remarks'],
                    ':discount' => $header['discount'],
                    ':remarks' => $header['remarks'],
                    ':status' => $header['status'] ?? 'SAVED',
                    ':created_by' => $this->getCurrentUserId(),
                    ':buildings_data' => !empty($buildings) ? json_encode($buildings) : null
                ]);
                $header_id = $this->db->lastInsertId();
            }

            // Insert quotation details
            $sqlDetail = "INSERT INTO sales_quotation_details (quot_ctrl_no, project_component_code, unit_code, item_code, qty, item_desc, price, detail_type, markup_percent, no_of_men, days, hours, ot_hrs, ot_rate, overhead_computation_type, overhead_value, total_price) 
                          VALUES (:quot_ctrl_no, :project_component_code, :unit_code, :item_code, :qty, :item_desc, :price, :detail_type, :markup_percent, :no_of_men, :days, :hours, :ot_hrs, :ot_rate, :overhead_computation_type, :overhead_value, :total_price)";
            $stmtDetail = $this->db->prepare($sqlDetail);

            foreach ($details as $detail) {
                $stmtDetail->execute([
                    ':quot_ctrl_no' => $header['quot_ctrl_no'],
                    ':project_component_code' => $detail['project_component_code'] ?? ($detail['component_code'] ?? null),
                    ':unit_code' => $detail['unit_code'] ?? null,
                    ':item_code' => $detail['item_code'] ?? null,
                    ':qty' => $detail['qty'] ?? 0,
                    ':item_desc' => $detail['item_desc'] ?? '',
                    ':price' => $detail['price'] ?? 0,
                    ':detail_type' => $detail['detail_type'] ?? 'BOQ',
                    ':markup_percent' => $detail['markup_percent'] ?? 0,
                    ':no_of_men' => $detail['no_of_men'] ?? 0,
                    ':days' => $detail['days'] ?? 0,
                    ':hours' => $detail['hours'] ?? 0,
                    ':ot_hrs' => $detail['ot_hrs'] ?? 0,
                    ':ot_rate' => $detail['ot_rate'] ?? 0,
                    ':overhead_computation_type' => $detail['overhead_computation_type'] ?? 'Fixed',
                    ':overhead_value' => $detail['overhead_value'] ?? 0,
                    ':total_price' => $detail['total_price'] ?? 0
                ]);
            }

            // Insert terms and conditions
            if (!empty($terms)) {
                $sqlTerms = "INSERT INTO sales_quotation_terms_conditions (quot_ctrl_no, section, description) 
                             VALUES (:quot_ctrl_no, :section, :description)";
                $stmtTerms = $this->db->prepare($sqlTerms);
                foreach ($terms as $index => $term) {
                    $stmtTerms->execute([
                        ':quot_ctrl_no' => $header['quot_ctrl_no'],
                        ':section' => $term['section'],
                        ':description' => $term['description'] ?? ''
                    ]);
                }
            }

            $this->db->commit();
            return $header_id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    /**
     * Delete a quotation and its associated details and terms.
     *
     * @param int $id The ID of the quotation header to delete.
     * @return bool True on success, false otherwise.
     * @throws \Exception If deletion fails.
     */
    public function delete($id)
    {
        try {
            $this->db->beginTransaction();
            // Header and details deletion (Assumes ON DELETE CASCADE is not used or to be safe)
            $this->db->prepare("DELETE FROM sales_quotation_details WHERE quot_ctrl_no = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM sales_quotation_header WHERE quot_ctrl_no = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM sales_quotation_terms_conditions WHERE quot_ctrl_no = ?")->execute([$id]);
            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
