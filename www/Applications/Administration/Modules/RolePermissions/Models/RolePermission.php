<?php

namespace Applications\Administration\Modules\RolePermissions\Models;

use Core\Model;
use PDO;

class RolePermission extends Model
{
    public function getRoles()
    {
        $stmt = $this->db->query("SELECT id, name, description FROM app_roles ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getPermissions()
    {
        $this->syncPermissionsFromConfigs();
        $stmt = $this->db->query("SELECT id, name, description FROM app_permissions ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    private function syncPermissionsFromConfigs()
    {
        $configs = $this->getAllModuleConfigs();
        $configPermissions = [];

        foreach ($configs as $configPath) {
            $config = include $configPath;
            if (isset($config['permissions']['module'])) {
                foreach ($config['permissions']['module'] as $moduleData) {
                    foreach ($moduleData as $moduleName => $actions) {
                        foreach ($actions as $actionData) {
                            if (isset($actionData['action'])) {
                                $configPermissions[] = [
                                    'name' => $actionData['action'],
                                    'description' => $moduleName . ' module action'
                                ];
                            }
                        }
                    }
                }
            }
        }

        if (empty($configPermissions)) {
            return;
        }

        try {
            $this->db->beginTransaction();

            // Get existing permissions to avoid duplicates
            $stmt = $this->db->query("SELECT name FROM app_permissions");
            $existingNames = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            $stmt = $this->db->prepare("INSERT INTO app_permissions (name, description) VALUES (:name, :description)");

            foreach ($configPermissions as $perm) {
                if (!in_array($perm['name'], $existingNames)) {
                    $stmt->execute([
                        'name' => $perm['name'],
                        'description' => $perm['description']
                    ]);
                    $existingNames[] = $perm['name']; // Add to existing names to avoid duplicates in the same run
                }
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            \Core\Logger::logException($e);
        }
    }

    private function getAllModuleConfigs()
    {
        $configs = [];
        $appsDir = __DIR__ . '/../../../../';
        $appsDir = realpath($appsDir);

        if (!$appsDir) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($appsDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === 'config.php') {
                $configs[] = $file->getRealPath();
            }
        }

        return $configs;
    }

    public function getRolePermissions($roleId)
    {
        $stmt = $this->db->prepare("SELECT permission_id FROM app_role_permissions WHERE role_id = :role_id");
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function saveRolePermissions($roleId, $permissionIds)
    {
        try {
            $this->db->beginTransaction();

            // Delete existing permissions for this role
            $stmt = $this->db->prepare("DELETE FROM app_role_permissions WHERE role_id = :role_id");
            $stmt->execute(['role_id' => $roleId]);

            // Insert new permissions
            if (!empty($permissionIds)) {
                $stmt = $this->db->prepare("INSERT INTO app_role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
                foreach ($permissionIds as $permId) {
                    $stmt->execute([
                        'role_id' => $roleId,
                        'permission_id' => $permId
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            \Core\Logger::logException($e);
            return false;
        }
    }
}
