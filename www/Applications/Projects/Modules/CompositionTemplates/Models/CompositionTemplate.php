<?php

namespace Applications\Projects\Modules\CompositionTemplates\Models;

use Core\Model;

class CompositionTemplate extends Model
{
    protected $table = 'composition_templates';

    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT t.*, i.installation_method_name, pt.description as trade_name, pwp.description as phase_name, ps.description as system_name, pt.service_id, psrv.description as service_name
                    FROM {$this->table} t
                    LEFT JOIN installation_methods i ON t.installation_method_id = i.installation_method_id
                    LEFT JOIN project_trades pt ON t.trade_id = pt.trade_id
                    LEFT JOIN project_work_phases pwp ON t.phase_id = pwp.phase_id
                    LEFT JOIN project_systems ps ON t.system_id = ps.system_id
                    LEFT JOIN project_services psrv ON pt.service_id = psrv.service_id
                    ORDER BY t.id DESC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
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

    public function getTotal()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($template_code)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE template_code = :template_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':template_code' => $template_code]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return false;
        }
    }

    public function save($data)
    {
        try {
            if (isset($data['id']) && is_numeric($data['id'])) {
                return $this->update($data);
            }

            if (!empty($data['template_code'])) {
                $data['template_code'] = strtoupper(trim($data['template_code']));
                if ($this->getByCode($data['template_code'])) {
                    throw new \Exception("Template code already exists.");
                }
            }

            $sql = "INSERT INTO {$this->table} (template_code, template_name, installation_method_id, trade_id, phase_id, system_id, created_by) 
                    VALUES (:template_code, :template_name, :installation_method_id, :trade_id, :phase_id, :system_id, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':template_code' => $data['template_code'] ?? null,
                ':template_name' => $data['template_name'] ?? null,
                ':installation_method_id' => $data['installation_method_id'] ?? null,
                ':trade_id' => $data['trade_id'] ?? null,
                ':phase_id' => $data['phase_id'] ?? null,
                ':system_id' => $data['system_id'] ?? null,
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
            if (!empty($data['template_code'])) {
                $data['template_code'] = strtoupper(trim($data['template_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['template_code']);
                if ($existing && $existing['id'] != $data['id']) {
                    throw new \Exception("Template code already exists.");
                }
            }

            $sql = "UPDATE {$this->table} 
                    SET template_code = :template_code,
                        template_name = :template_name,
                        installation_method_id = :installation_method_id,
                        trade_id = :trade_id,
                        phase_id = :phase_id,
                        system_id = :system_id,
                        updated_at = NOW(),
                        updated_by = :modified_by
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':template_code' => $data['template_code'] ?? null,
                ':template_name' => $data['template_name'] ?? null,
                ':installation_method_id' => $data['installation_method_id'] ?? null,
                ':trade_id' => $data['trade_id'] ?? null,
                ':phase_id' => $data['phase_id'] ?? null,
                ':system_id' => $data['system_id'] ?? null,
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
