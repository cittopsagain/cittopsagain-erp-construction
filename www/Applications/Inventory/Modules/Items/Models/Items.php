<?php

namespace Applications\Inventory\Modules\Items\Models;

use Core\Model;

class Items extends Model
{
    public function getPaged($start, $limit, $search = '')
    {
        try {
            $where = "";
            $params = [
                ':limit' => (int)$limit,
                ':offset' => (int)$start
            ];

            if ($search) {
                $where = " WHERE i.item_code LIKE :search1 OR i.item_desc LIKE :search2 ";
                $params[':search1'] = '%' . $search . '%';
                $params[':search2'] = '%' . $search . '%';
            }

            $sql = "SELECT i.*, c.item_cat_name, u.unit_code, u.description AS unit_description,
                           mg.group_name AS material_group_name, it.type_name AS item_type_name
                    FROM inv_items i
                    LEFT JOIN inv_item_categories c ON i.item_cat = c.item_cat_id
                    LEFT JOIN sales_unit u ON i.unit = u.id
                    LEFT JOIN inv_material_groups mg ON i.material_group = mg.id
                    LEFT JOIN inv_item_types it ON i.item_type = it.id
                    $where
                    ORDER BY i.item_id DESC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $val) {
                $type = is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $val, $type);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    public function getTotal($search = '')
    {
        try {
            $where = "";
            $params = [];
            if ($search) {
                $where = " WHERE item_code LIKE :search1 OR item_desc LIKE :search2 ";
                $params[':search1'] = '%' . $search . '%';
                $params[':search2'] = '%' . $search . '%';
            }
            $sql = "SELECT COUNT(*) FROM inv_items" . $where;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($item_code)
    {
        try {
            $sql = "SELECT * FROM inv_items WHERE item_code = :item_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':item_code' => $item_code]);
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
            if (isset($data['item_id']) && is_numeric($data['item_id'])) {
                return $this->update($data);
            }

            if (!empty($data['item_code'])) {
                $data['item_code'] = strtoupper(trim($data['item_code']));
                if ($this->getByCode($data['item_code'])) {
                    throw new \Exception("Item code already exists.");
                }
            }

            $sql = "INSERT INTO inv_items (item_code, item_desc, item_cat, material_group, item_type, currency, qty, unit, reorder_level, maximum_stock, default_purchase_cost, created_by) 
                    VALUES (:item_code, :item_desc, :item_cat, :material_group, :item_type, :currency, :qty, :unit, :reorder_level, :maximum_stock, :default_purchase_cost, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':item_code' => $data['item_code'] ?? null,
                ':item_desc' => $data['item_desc'] ?? null,
                ':item_cat' => $data['item_cat'] ?? null,
                ':material_group' => $data['material_group'] ?? null,
                ':item_type' => $data['item_type'] ?? null,
                ':currency' => $data['currency'] ?? 'PHP',
                ':qty' => $data['qty'] ?? 0,
                ':unit' => $data['unit'] ?? null,
                ':reorder_level' => $data['reorder_level'] ?? 0,
                ':maximum_stock' => $data['maximum_stock'] ?? 0,
                ':default_purchase_cost' => $data['default_purchase_cost'] ?? 0,
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
            if (!empty($data['item_code'])) {
                $data['item_code'] = strtoupper(trim($data['item_code']));

                $existing = $this->getByCode($data['item_code']);
                if ($existing && $existing['item_id'] != $data['item_id']) {
                    throw new \Exception("Item code already exists.");
                }
            }

            $sql = "UPDATE inv_items 
                    SET item_code = :item_code,
                        item_desc = :item_desc,
                        item_cat = :item_cat,
                        material_group = :material_group,
                        item_type = :item_type,
                        currency = :currency,
                        qty = :qty,
                        unit = :unit,
                        reorder_level = :reorder_level,
                        maximum_stock = :maximum_stock,
                        default_purchase_cost = :default_purchase_cost,
                        date_modified = NOW(),
                        modified_by = :modified_by
                    WHERE item_id = :item_id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':item_code' => $data['item_code'] ?? null,
                ':item_desc' => $data['item_desc'] ?? null,
                ':item_cat' => $data['item_cat'] ?? null,
                ':material_group' => $data['material_group'] ?? null,
                ':item_type' => $data['item_type'] ?? null,
                ':currency' => $data['currency'] ?? 'PHP',
                ':qty' => $data['qty'] ?? 0,
                ':unit' => $data['unit'] ?? null,
                ':reorder_level' => $data['reorder_level'] ?? 0,
                ':maximum_stock' => $data['maximum_stock'] ?? 0,
                ':default_purchase_cost' => $data['default_purchase_cost'] ?? 0,
                ':modified_by' => $this->getCurrentUserId(),
                ':item_id' => $data['item_id']
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
            $sql = "DELETE FROM inv_items WHERE item_id = :id";
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
            $sql = "SELECT i.*, c.item_cat_name, u.unit_code, u.description AS unit_description,
                           mg.group_name AS material_group_name, it.type_name AS item_type_name
                    FROM inv_items i
                    LEFT JOIN inv_item_categories c ON i.item_cat = c.item_cat_id
                    LEFT JOIN sales_unit u ON i.unit = u.id
                    LEFT JOIN inv_material_groups mg ON i.material_group = mg.id
                    LEFT JOIN inv_item_types it ON i.item_type = it.id
                    ORDER BY i.item_code ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }
}
