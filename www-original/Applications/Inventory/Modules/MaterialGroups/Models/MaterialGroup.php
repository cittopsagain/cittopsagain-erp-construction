<?php

namespace Applications\Inventory\Modules\MaterialGroups\Models;

use Core\Model;

class MaterialGroup extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM inv_material_groups 
                    ORDER BY id DESC 
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM inv_material_groups");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($group_code)
    {
        try {
            $sql = "SELECT * FROM inv_material_groups WHERE group_code = :group_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':group_code' => $group_code]);
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

            if (!empty($data['group_code'])) {
                $data['group_code'] = strtoupper(trim($data['group_code']));
                if ($this->getByCode($data['group_code'])) {
                    throw new \Exception("Group code already exists.");
                }
            }

            $sql = "INSERT INTO inv_material_groups (group_code, group_name, created_by) 
                    VALUES (:group_code, :group_name, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':group_code' => $data['group_code'] ?? null,
                ':group_name' => $data['group_name'] ?? null,
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
            if (!empty($data['group_code'])) {
                $data['group_code'] = strtoupper(trim($data['group_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['group_code']);
                if ($existing && $existing['id'] != $data['id']) {
                    throw new \Exception("Group code already exists.");
                }
            }

            $sql = "UPDATE inv_material_groups 
                    SET group_code = :group_code,
                        group_name = :group_name,
                        date_modified = NOW(),
                        modified_by = :modified_by
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':group_code' => $data['group_code'] ?? null,
                ':group_name' => $data['group_name'] ?? null,
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
            $sql = "DELETE FROM inv_material_groups WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
