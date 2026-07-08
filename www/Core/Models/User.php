<?php

namespace Core\Models;

use Core\Model;

class User extends Model
{
    /**
     * Authenticate a user by username and password.
     *
     * @param string $username
     * @param string $password
     * @return array|bool User data if successful, false otherwise.
     */
    public function authenticate($username, $password)
    {
        try {
            $hashedPassword = md5($password);

            $stmt = $this->db->prepare("SELECT * FROM app_users WHERE user_uname = :username AND user_pword = :password AND blocked = 0 AND is_deleted = 0 LIMIT 1");
            $stmt->execute([
                'username' => $username,
                'password' => $hashedPassword
            ]);

            return $stmt->fetch();
        } catch (\PDOException $e) {
            \Core\Logger::logException($e);
            throw new \Exception("A database error occurred during authentication.");
        }
    }

    /**
     * Update the last visited date for a user.
     *
     * @param int $userId
     */
    public function updateLastVisited($userId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE app_users SET date_lastvisted = NOW() WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
        } catch (\PDOException $e) {
            \Core\Logger::logException($e);
            // We might not want to throw an exception here as it's not critical for login
        }
    }

    /**
     * Get user permissions based on their roles.
     *
     * @param int $userId
     * @return array List of permission names
     */
    public function getPermissions($userId)
    {
        try {
            $sql = "SELECT DISTINCT p.name 
                    FROM app_permissions p
                    JOIN app_role_permissions rp ON p.id = rp.permission_id
                    JOIN app_user_roles ur ON rp.role_id = ur.role_id
                    WHERE ur.user_id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);

            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            // If tables don't exist yet, return empty
            return [];
        }
    }
}
