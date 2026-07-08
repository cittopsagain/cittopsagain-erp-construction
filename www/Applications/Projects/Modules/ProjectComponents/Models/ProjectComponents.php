<?php

namespace Applications\Projects\Modules\ProjectComponents\Models;

use Core\Model;

class ProjectComponents extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM project_components 
                    ORDER BY display_order ASC, component_id DESC 
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM project_components");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($component_code)
    {
        try {
            $sql = "SELECT * FROM project_components WHERE component_code = :component_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':component_code' => $component_code]);
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
            if (isset($data['component_id']) && is_numeric($data['component_id'])) {
                return $this->update($data);
            }

            if (!empty($data['component_code'])) {
                $data['component_code'] = strtoupper(str_replace(' ', '', $data['component_code']));
                if ($this->getByCode($data['component_code'])) {
                    throw new \Exception("Component code already exists.");
                }
            }

            $sql = "INSERT INTO project_components (component_code, description, display_order, created_by) 
                    VALUES (:component_code, :description, :display_order, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':component_code' => $data['component_code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':display_order' => $data['display_order'] ?? 0,
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
            if (!empty($data['component_code'])) {
                $data['component_code'] = strtoupper(str_replace(' ', '', $data['component_code']));

                // Check if code exists for other records
                $existing = $this->getByCode($data['component_code']);
                if ($existing && $existing['component_id'] != $data['component_id']) {
                    throw new \Exception("Component code already exists.");
                }
            }

            $sql = "UPDATE project_components 
                    SET component_code = :component_code,
                        description = :description,
                        display_order = :display_order
                    WHERE component_id = :component_id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':component_code' => $data['component_code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':display_order' => $data['display_order'] ?? 0,
                ':component_id' => $data['component_id']
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
            $sql = "DELETE FROM project_components WHERE component_id = :component_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':component_id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    // --- Project Component Items Methods ---

    public function getItemsPaged($start, $limit, $component_id = null)
    {
        try {
            $sql = "SELECT pci.*, pc.component_code, pc.description as component_description 
                    FROM project_component_items pci
                    JOIN project_components pc ON pci.component_id = pc.component_id";

            $params = [];
            if ($component_id) {
                $sql .= " WHERE pci.component_id = :component_id";
                $params[':component_id'] = $component_id;
            }

            $sql .= " ORDER BY pci.id DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            if ($component_id) {
                $stmt->bindValue(':component_id', $component_id);
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

    public function getItemsTotal($component_id = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM project_component_items";
            $params = [];
            if ($component_id) {
                $sql .= " WHERE component_id = :component_id";
                $params[':component_id'] = $component_id;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function saveItem($data)
    {
        try {
            if (isset($data['id']) && is_numeric($data['id'])) {
                $sql = "UPDATE project_component_items 
                        SET item_code = :item_code,
                            description = :description,
                            unit = :unit,
                            price = :price
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    ':item_code' => $data['item_code'],
                    ':description' => $data['description'],
                    ':unit' => $data['unit'] ?? null,
                    ':price' => $data['price'] ?? 0,
                    ':id' => $data['id']
                ]);
            } else {
                $sql = "INSERT INTO project_component_items (component_id, item_code, description, unit, price, created_by) 
                        VALUES (:component_id, :item_code, :description, :unit, :price, :created_by)";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    ':component_id' => $data['component_id'],
                    ':item_code' => $data['item_code'],
                    ':description' => $data['description'],
                    ':unit' => $data['unit'] ?? null,
                    ':price' => $data['price'] ?? 0,
                    ':created_by' => $this->getCurrentUserId()
                ]);
            }
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }

    public function deleteItem($id)
    {
        try {
            $sql = "DELETE FROM project_component_items WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
