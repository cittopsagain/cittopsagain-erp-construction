<?php

namespace Applications\Projects\Modules\CompositionTemplates\Models;

use Core\Model;

class CompositionTemplateDetail extends Model
{
    protected $table = 'composition_template_details';

    public function getByTemplateId($template_id, $type = null)
    {
        try {
            $sql = "SELECT d.*, i.item_code, i.item_desc 
                    FROM {$this->table} d
                    LEFT JOIN inv_items i ON d.inventory_item_id = i.item_id
                    WHERE d.template_id = :template_id";

            $params = [':template_id' => $template_id];

            if ($type) {
                $sql .= " AND d.detail_type = :type";
                $params[':type'] = $type;
            }

            $sql .= " ORDER BY d.seq ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
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
            if (isset($data['id']) && is_numeric($data['id'])) {
                return $this->update($data);
            }

            $sql = "INSERT INTO {$this->table} (template_id, detail_type, inventory_item_id, seq, description, qty_formula, waste_percentage, remarks, role, hours, rate, formula, created_by) 
                    VALUES (:template_id, :detail_type, :inventory_item_id, :seq, :description, :qty_formula, :waste_percentage, :remarks, :role, :hours, :rate, :formula, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':template_id' => $data['template_id'],
                ':detail_type' => $data['detail_type'] ?? 'MATERIAL',
                ':inventory_item_id' => $data['inventory_item_id'] ?? null,
                ':seq' => $data['seq'] ?? 0,
                ':description' => $data['description'] ?? null,
                ':qty_formula' => $data['qty_formula'] ?? null,
                ':waste_percentage' => $data['waste_percentage'] ?? 0,
                ':remarks' => $data['remarks'] ?? null,
                ':role' => $data['role'] ?? null,
                ':hours' => $data['hours'] ?? 0,
                ':rate' => $data['rate'] ?? 0,
                ':formula' => $data['formula'] ?? null,
                ':created_by' => $this->getCurrentUserId()
            ]);

            if ($success) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    public function update($data)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET seq = :seq,
                        inventory_item_id = :inventory_item_id,
                        description = :description,
                        qty_formula = :qty_formula,
                        waste_percentage = :waste_percentage,
                        remarks = :remarks,
                        role = :role,
                        hours = :hours,
                        rate = :rate,
                        formula = :formula,
                        updated_at = NOW(),
                        updated_by = :modified_by
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':seq' => $data['seq'] ?? 0,
                ':inventory_item_id' => $data['inventory_item_id'] ?? null,
                ':description' => $data['description'] ?? null,
                ':qty_formula' => $data['qty_formula'] ?? null,
                ':waste_percentage' => $data['waste_percentage'] ?? 0,
                ':remarks' => $data['remarks'] ?? null,
                ':role' => $data['role'] ?? null,
                ':hours' => $data['hours'] ?? 0,
                ':rate' => $data['rate'] ?? 0,
                ':formula' => $data['formula'] ?? null,
                ':modified_by' => $this->getCurrentUserId(),
                ':id' => $data['id']
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
