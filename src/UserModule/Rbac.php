<?php

/**
 * This file is part of the Piko user module
 *
 * @package Piko\UserModule
 * @copyright 2025 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/user-module
 *
 */

namespace Piko\UserModule;

use PDO;

/**
 * Rbac class
 *
 * Utility to manage roles and permissions (RBAC)
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Rbac
{
    /**
     * Connexion PDO partagée
     *
     * @var PDO
     */
    protected static PDO $db;

    public static function setPDO(PDO $db): void
    {
        static::$db = $db;
    }

    /**
     * Create a role
     *
     * @param string $name The role name
     * @param string $description The role description
     * @return int The role Id
     */
    public static function createRole($name, $description = ''): int
    {
        $query = 'INSERT INTO `auth_role` (`name`, `description`) VALUES (?, ?)';

        static::$db->beginTransaction();
        $st = static::$db->prepare($query);
        $st->execute([$name, $description]);
        $id = static::$db->lastInsertId();
        static::$db->commit();

        return (int) $id;
    }

    /**
     * Check if the role exists
     *
     * @param string $name The role name
     * @return bool
     */
    public static function roleExists($name): bool
    {
        $st = static::$db->prepare('SELECT COUNT(`id`) FROM `auth_role` WHERE `name` = :name');
        $st->execute(['name' => $name]);

        return ((int) $st->fetchColumn() > 0) ? true : false;
    }

    /**
     * Get the role Id
     *
     * @param string $name The role name
     * @return int The role Id (0 if the role is not found)
     */
    public static function getRoleId($name)
    {
        $st = static::$db->prepare('SELECT `id` FROM `auth_role` WHERE `name` = :name');
        $st->execute(['name' => $name]);

        return (int) $st->fetchColumn();
    }

    /**
     * Assign a role to an user
     *
     * @param int $userId The user Id
     * @param string $roleName The role name
     * @throws \RuntimeException If the role doesn't exists
     */
    public static function assignRole($userId, $roleName): void
    {
        $roleId = static::getRoleId($roleName);

        if (!$roleId) {
            throw new \RuntimeException("Role $roleName doesn't exists");
        }

        $query = 'INSERT INTO `auth_assignment` (`role_id`, `user_id`) VALUES (?, ?)';

        static::$db->beginTransaction();
        $st = static::$db->prepare($query);
        $st->execute([$roleId, $userId]);
        static::$db->commit();
    }

    /**
     * Get user roles
     *
     * @param int $userId The user Id
     * @return array An array containing user roles
     */
    public static function getUserRoles($userId): array
    {
        $query = 'SELECT `auth_role`.`name` FROM `auth_role` '
               . 'INNER JOIN `auth_assignment` ON `auth_assignment`.`role_id` = `auth_role`.`id` '
               . 'WHERE `auth_assignment`.`user_id` = :user_id '
               . 'GROUP BY role_id';
        $st = static::$db->prepare($query);
        $st->execute(['user_id' => $userId]);

        return $st->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get user roles ids
     *
     * @param int $userId The user Id
     * @return array An array containing user role ids
     */
    public static function getUserRoleIds($userId): array
    {
        $query = 'SELECT role_id FROM `auth_assignment` WHERE user_id = :user_id';
        $sth = static::$db->prepare($query);
        $sth->execute(['user_id' => $userId]);

        return $sth->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get user permissions
     *
     * @param int $userId The user Id
     * @return array An array containing user permissions
     */
    public static function getUserPermissions($userId): array
    {
        $query = 'SELECT p.`name` FROM `auth_permission` AS p '
               . 'INNER JOIN `auth_role_has_permission` AS ap ON ap.`permission_id` = p.`id` '
               . 'INNER JOIN `auth_assignment` AS aa ON aa.`role_id` = ap.`role_id` '
               . 'WHERE aa.`user_id` = :user_id '
               . 'GROUP BY permission_id';

        $st = static::$db->prepare($query);
        $st->execute(['user_id' => $userId]);

        return $st->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get role permissions
     *
     * @param string $roleName The role name
     * @return array An array of permissions as string
     */
    public static function getRolePermissions($roleName): array
    {
        $roleId = static::getRoleId($roleName);

        if (!$roleId) {
            throw new \RuntimeException("Role $roleName doesn't exists");
        }

        $query = 'SELECT p.`name` FROM `auth_permission` AS p '
            . 'INNER JOIN `auth_role_has_permission` AS ap ON ap.`permission_id` = p.`id` '
            . 'WHERE ap.`role_id` = :role_id '
            . 'GROUP BY permission_id';
        $st = static::$db->prepare($query);
        $st->execute(['role_id' => $roleId]);

        return $st->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get role permission ids
     *
     * @param string $roleName The role name
     * @return array An array of permission ids
     */
    public static function getRolePermissionIds($roleName): array
    {
        $roleId = static::getRoleId($roleName);

        if (!$roleId) {
            throw new \RuntimeException("Role $roleName doesn't exists");
        }

        $query = 'SELECT permission_id FROM `auth_role_has_permission` WHERE role_id = :role_id';
        $sth = static::$db->prepare($query);
        $sth->execute(['role_id' => $roleId]);

        return $sth->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Create a permission
     *
     * @param string $name The permission name
     * @return int The permission id
     */
    public static function createPermission($name): int
    {
        $query = 'INSERT INTO `auth_permission` (`name`) VALUES (?)';

        static::$db->beginTransaction();
        $st = static::$db->prepare($query);
        $st->execute([$name]);
        $id = static::$db->lastInsertId();
        static::$db->commit();

        return (int) $id;
    }

    /**
     * Check if the permission exists
     *
     * @param string $name The permission name
     * @return boolean
     */
    public static function permissionExists($name): bool
    {
        $st = static::$db->prepare('SELECT COUNT(`id`) FROM `auth_permission` WHERE `name` = :name');
        $st->execute(['name' => $name]);

        return ((int) $st->fetchColumn() > 0) ? true : false;
    }

    /**
     * Get the permission Id
     *
     * @param string $name The permission name
     * @return int The permission id (0 if the permission is not found)
     */
    public static function getPermissionId($name): int
    {
        $st = static::$db->prepare('SELECT `id` FROM `auth_permission` WHERE `name` = :name');
        $st->execute(['name' => $name]);

        return (int) $st->fetchColumn();
    }

    /**
     * Assign a permission to a role
     *
     * @param string $roleName The role name
     * @param string $permissionName The permission name
     * @throws \RuntimeException If the role or the permission doesn't exists
     */
    public static function assignPermission($roleName, $permissionName): void
    {
        $roleId = static::getRoleId($roleName);
        $permissionId = static::getPermissionId($permissionName);

        if (!$roleId) {
            throw new \RuntimeException("Role $roleName doesn't exists");
        }

        if (!$permissionId) {
            throw new \RuntimeException("Permission $permissionName doesn't exists");
        }

        $query = 'INSERT INTO `auth_role_has_permission` (`role_id`, `permission_id`) VALUES (?, ?)';

        static::$db->beginTransaction();
        $st = static::$db->prepare($query);
        $st->execute([$roleId, $permissionId]);
        static::$db->commit();
    }
}
