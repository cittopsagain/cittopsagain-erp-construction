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
            $stmt = $this->db->prepare("SELECT * FROM app_users WHERE user_uname = :username AND blocked = 0 AND is_deleted = 0 LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user) {
                $storedPassword = $user['user_pword'];

                // Check if it's a modern hash or a legacy MD5 hash
                if (password_verify($password, $storedPassword)) {
                    // Modern hash matches
                    return $user;
                } elseif ($storedPassword === md5($password)) {
                    // Legacy MD5 matches, upgrade it to modern hash
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $this->db->prepare("UPDATE app_users SET user_pword = :new_hash WHERE user_id = :user_id");
                    $updateStmt->execute([
                        'new_hash' => $newHash,
                        'user_id' => $user['user_id']
                    ]);

                    // Return user data as authentication was successful
                    return $user;
                }
            }

            return false;
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
