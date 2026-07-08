<?php

namespace Applications\Inventory\Modules\ItemCategory\Models;

use Core\Model;

class ItemCategory extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM inv_item_categories 
                    ORDER BY item_cat_id DESC 
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM inv_item_categories");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($item_cat_code)
    {
        try {
            $sql = "SELECT * FROM inv_item_categories WHERE item_cat_code = :item_cat_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':item_cat_code' => $item_cat_code]);
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
            if (isset($data['item_cat_id']) && is_numeric($data['item_cat_id'])) {
                return $this->update($data);
            }

            if (!empty($data['item_cat_code'])) {
                $data['item_cat_code'] = strtoupper(trim($data['item_cat_code']));
                if ($this->getByCode($data['item_cat_code'])) {
                    throw new \Exception("Category code already exists.");
                }
            }

            $sql = "INSERT INTO inv_item_categories (item_cat_code, item_cat_name, created_by) 
                    VALUES (:item_cat_code, :item_cat_name, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':item_cat_code' => $data['item_cat_code'] ?? null,
                ':item_cat_name' => $data['item_cat_name'] ?? null,
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
            if (!empty($data['item_cat_code'])) {
                $data['item_cat_code'] = strtoupper(trim($data['item_cat_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['item_cat_code']);
                if ($existing && $existing['item_cat_id'] != $data['item_cat_id']) {
                    throw new \Exception("Category code already exists.");
                }
            }

            $sql = "UPDATE inv_item_categories 
                    SET item_cat_code = :item_cat_code,
                        item_cat_name = :item_cat_name,
                        date_modified = NOW(),
                        modified_by = :modified_by
                    WHERE item_cat_id = :item_cat_id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':item_cat_code' => $data['item_cat_code'] ?? null,
                ':item_cat_name' => $data['item_cat_name'] ?? null,
                ':modified_by' => $this->getCurrentUserId(),
                ':item_cat_id' => $data['item_cat_id']
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
            $sql = "DELETE FROM inv_item_categories WHERE item_cat_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
