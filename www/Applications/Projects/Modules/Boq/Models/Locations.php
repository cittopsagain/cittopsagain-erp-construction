<?php

namespace Applications\Projects\Modules\Boq\Models;

use Core\Model;

class Locations extends Model
{
    protected $table = 'project_locations';

    public function getAll($limit = 25, $offset = 0, $boq_id = null)
    {
        $where = "";
        $params = [
            ':limit' => (int)$limit,
            ':offset' => (int)$offset
        ];

        if ($boq_id !== null && $boq_id !== '' && $boq_id !== '0' && $boq_id !== 0) {
            $where = " WHERE l.boq_id = :boq_id";
            $params[':boq_id'] = $boq_id;
        }

        $sql = "SELECT l.*, lt.name as type_name, lt.code as type_code, pl.name as parent_name, pl.code as parent_code
                FROM {$this->table} l
                LEFT JOIN location_types lt ON l.type_id = lt.id
                LEFT JOIN {$this->table} pl ON l.parent_id = pl.id
                {$where}
                ORDER BY l.id ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            if ($key == ':limit' || $key == ':offset') {
                $stmt->bindValue($key, $val, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTotal($boq_id = null)
    {
        $where = "";
        $params = [];
        if ($boq_id !== null && $boq_id !== '' && $boq_id !== '0' && $boq_id !== 0) {
            $where = " WHERE boq_id = :boq_id";
            $params[':boq_id'] = $boq_id;
        }
        $sql = "SELECT COUNT(*) FROM {$this->table} {$where}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function save($data)
    {
        try {
            if (isset($data['id']) && !empty($data['id']) && is_numeric($data['id'])) {
                return $this->update($data);
            }

            $sql = "INSERT INTO {$this->table} (code, name, type_id, parent_id, boq_id) 
                    VALUES (:code, :name, :type_id, :parent_id, :boq_id)";

            $stmt = $this->db->prepare($sql);
            $params = [
                ':code' => $data['code'] ?? null,
                ':name' => $data['name'] ?? null,
                ':type_id' => !empty($data['type_id']) ? $data['type_id'] : null,
                ':parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
                ':boq_id' => !empty($data['boq_id']) ? $data['boq_id'] : null,
            ];

            \Core\Logger::debug('Locations::save params: ' . json_encode($params));
            $success = $stmt->execute($params);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Location saved successfully',
                    'id' => $this->db->lastInsertId()
                ];
            }
        } catch (\PDOException $e) {
            \Core\Logger::error('Locations::save error: ' . $e->getMessage());
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Location code already exists.'];
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function update($data)
    {
        try {
            // Filter data to only include valid columns
            $validData = [
                'code' => $data['code'] ?? null,
                'name' => $data['name'] ?? null,
                'type_id' => !empty($data['type_id']) ? $data['type_id'] : null,
                'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
                'boq_id' => !empty($data['boq_id']) ? $data['boq_id'] : null,
                'id' => $data['id']
            ];

            $sql = "UPDATE {$this->table} SET 
                    code = :code, 
                    name = :name, 
                    type_id = :type_id, 
                    parent_id = :parent_id,
                    boq_id = :boq_id
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $params = [
                ':code' => $validData['code'],
                ':name' => $validData['name'],
                ':type_id' => $validData['type_id'],
                ':parent_id' => $validData['parent_id'],
                ':boq_id' => $validData['boq_id'],
                ':id' => $validData['id']
            ];
            \Core\Logger::debug('Locations::update params: ' . json_encode($params));
            $success = $stmt->execute($params);

            if ($success) {
                return ['success' => true, 'message' => 'Location updated successfully'];
            }
        } catch (\PDOException $e) {
            \Core\Logger::error('Locations::update error: ' . $e->getMessage());
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Location code already exists.'];
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([':id' => $id]);

            if ($success) {
                return ['success' => true, 'message' => 'Location deleted successfully'];
            }
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
