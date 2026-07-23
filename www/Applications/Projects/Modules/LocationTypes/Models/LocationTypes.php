<?php

namespace Applications\Projects\Modules\LocationTypes\Models;

use Core\Model;

class LocationTypes extends Model
{
    protected $table = 'location_types';

    public function getAll()
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
            return $this->db->query($sql)->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getByCode($code)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE code = :code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':code' => $code]);
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

            if (!empty($data['code'])) {
                $data['code'] = strtoupper(trim($data['code']));
                if ($this->getByCode($data['code'])) {
                    throw new \Exception("Location type code already exists.");
                }
            }

            $sql = "INSERT INTO {$this->table} (code, name, parent_allowed, description, active) 
                    VALUES (:code, :name, :parent_allowed, :description, :active)";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':code' => $data['code'] ?? null,
                ':name' => $data['name'] ?? null,
                ':parent_allowed' => $data['parent_allowed'] ?? null,
                ':description' => $data['description'] ?? null,
                ':active' => isset($data['active']) ? ($data['active'] === 'true' || $data['active'] === true || $data['active'] === 1 || $data['active'] === '1' || $data['active'] === 'Active' ? 1 : 0) : 1
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
            if (!empty($data['code'])) {
                $data['code'] = strtoupper(trim($data['code']));
                $existing = $this->getByCode($data['code']);
                if ($existing && $existing['id'] != $data['id']) {
                    throw new \Exception("Location type code already exists.");
                }
            }

            $sql = "UPDATE {$this->table} 
                    SET code = :code,
                        name = :name,
                        parent_allowed = :parent_allowed,
                        description = :description,
                        active = :active 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':code' => $data['code'] ?? null,
                ':name' => $data['name'] ?? null,
                ':parent_allowed' => $data['parent_allowed'] ?? null,
                ':description' => $data['description'] ?? null,
                ':active' => isset($data['active']) ? ($data['active'] === 'true' || $data['active'] === true || $data['active'] === 1 || $data['active'] === '1' || $data['active'] === 'Active' ? 1 : 0) : 1,
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
