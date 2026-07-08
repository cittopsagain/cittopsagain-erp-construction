<?php

namespace Applications\Sales\Modules\Clients\Models;

use Core\Model;

class Clients extends Model
{
    public function getPaged($start, $limit)
    {
        try {
            $sql = "SELECT * FROM sales_client 
                    ORDER BY date_created DESC 
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
            $stmt = $this->db->query("SELECT COUNT(*) FROM sales_client");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return 0;
        }
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM sales_client ORDER BY client_name ASC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            return [];
        }
    }

    public function getByCode($client_code)
    {
        try {
            $sql = "SELECT * FROM sales_client WHERE client_code = :client_code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':client_code' => $client_code]);
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

            if (!empty($data['client_code'])) {
                $data['client_code'] = strtoupper(str_replace(' ', '', $data['client_code']));
                if ($this->getByCode($data['client_code'])) {
                    throw new \Exception("Client code already exists.");
                }
            }

            $sql = "INSERT INTO sales_client (client_code, client_name, add1, add2, tel_no, fax_no, business_type, tin_no, pwd_no, created_by) 
                    VALUES (:client_code, :client_name, :add1, :add2, :tel_no, :fax_no, :business_type, :tin_no, :pwd_no, :created_by)";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':client_code' => $data['client_code'] ?? null,
                ':client_name' => $data['client_name'],
                ':add1' => $data['add1'] ?? '',
                ':add2' => $data['add2'] ?? '',
                ':tel_no' => $data['tel_no'] ?? '',
                ':fax_no' => $data['fax_no'] ?? '',
                ':business_type' => $data['business_type'] ?? '',
                ':tin_no' => $data['tin_no'] ?? '',
                ':pwd_no' => $data['pwd_no'] ?? '',
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
            if (!empty($data['client_code'])) {
                $data['client_code'] = strtoupper(str_replace(' ', '', $data['client_code']));
            }

            $sql = "UPDATE sales_client 
                    SET client_code = :client_code,
                        client_name = :client_name, 
                        add1 = :add1, 
                        add2 = :add2, 
                        tel_no = :tel_no,
                        fax_no = :fax_no,
                        business_type = :business_type,
                        tin_no = :tin_no,
                        pwd_no = :pwd_no
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':client_code' => $data['client_code'] ?? null,
                ':client_name' => $data['client_name'],
                ':add1' => $data['add1'] ?? '',
                ':add2' => $data['add2'] ?? '',
                ':tel_no' => $data['tel_no'] ?? '',
                ':fax_no' => $data['fax_no'] ?? '',
                ':business_type' => $data['business_type'] ?? '',
                ':tin_no' => $data['tin_no'] ?? '',
                ':pwd_no' => $data['pwd_no'] ?? '',
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
            $sql = "DELETE FROM sales_client WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            \Core\Logger::logException($e);
            throw $e;
        }
    }
}
