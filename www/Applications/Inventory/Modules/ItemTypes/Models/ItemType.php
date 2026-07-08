<?php

namespace Applications\Inventory\Modules\ItemTypes\Models;

use Core\Model;

class ItemType extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM inv_item_types 
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM inv_item_types");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($type_code)
    {
        try {
            $sql = "SELECT * FROM inv_item_types WHERE type_code = :type_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':type_code' => $type_code]);
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

            if (!empty($data['type_code'])) {
                $data['type_code'] = strtoupper(trim($data['type_code']));
                if ($this->getByCode($data['type_code'])) {
                    throw new \Exception("Type code already exists.");
                }
            }

            $sql = "INSERT INTO inv_item_types (type_code, type_name, created_by) 
                    VALUES (:type_code, :type_name, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':type_code' => $data['type_code'] ?? null,
                ':type_name' => $data['type_name'] ?? null,
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
            if (!empty($data['type_code'])) {
                $data['type_code'] = strtoupper(trim($data['type_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['type_code']);
                if ($existing && $existing['id'] != $data['id']) {
                    throw new \Exception("Type code already exists.");
                }
            }

            $sql = "UPDATE inv_item_types 
                    SET type_code = :type_code,
                        type_name = :type_name,
                        date_modified = NOW(),
                        modified_by = :modified_by
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':type_code' => $data['type_code'] ?? null,
                ':type_name' => $data['type_name'] ?? null,
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
            $sql = "DELETE FROM inv_item_types WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT id, type_code, type_name FROM inv_item_types ORDER BY type_name ASC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }
}
