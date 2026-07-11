<?php

namespace Applications\Projects\Modules\CompositionTemplates\Models;

use Core\Model;

class CompositionTemplateDetail extends Model
{
    protected $table = 'composition_template_details';

    public function getByTemplateId($template_id)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE template_id = :template_id ORDER BY seq ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':template_id' => $template_id]);
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

            $sql = "INSERT INTO {$this->table} (template_id, seq, description, formula, created_by) 
                    VALUES (:template_id, :seq, :description, :formula, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':template_id' => $data['template_id'],
                ':seq' => $data['seq'] ?? 0,
                ':description' => $data['description'] ?? null,
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
                        description = :description,
                        formula = :formula,
                        updated_at = NOW(),
                        updated_by = :modified_by
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':seq' => $data['seq'] ?? 0,
                ':description' => $data['description'] ?? null,
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
