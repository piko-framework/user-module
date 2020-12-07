<?php
/**
 * This file is part of the Piko user module
 *
 * @copyright 2020 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko-user
 */
namespace piko\user;

use piko\Piko;

/**
 * Rbac utility class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Rbac
{
    /**
     * Create a role
     *
     * @param string $name The role name
     * @param string $description The role description
     * @return int The role Id
     */
    public static function createRole($name, $description = '')
    {
        $db = Piko::get('db');
        $query = 'INSERT INTO `auth_role` (`name`, `description`) VALUES (?, ?)';

        $db->beginTransaction();
        $st = $db->prepare($query);
        $st->execute([$name, $description]);
        $db->commit();

        return $db->lastInsertId();
    }

    /**
     * Check if the role exists
     *
     * @param string $name The role name
     * @return boolean
     */
    public static function roleExists($name)
    {
        $db = Piko::get('db');
        $st = $db->prepare('SELECT COUNT(`id`) FROM `auth_role` WHERE `name` = :name');
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
        $db = Piko::get('db');
        $st = $db->prepare('SELECT `id` FROM `auth_role` WHERE `name` = :name');
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
    public static function assignRole($userId, $roleName)
    {
        $roleId = static::getRoleId($roleName);

        if (!$roleId) {
            throw new \RuntimeException("Role $roleName doesn't exists");
        }

        $db = Piko::get('db');
        $query = 'INSERT INTO `auth_assignment` (`role_id`, `user_id`) VALUES (?, ?)';

        $db->beginTransaction();
        $st = $db->prepare($query);
        $st->execute([$roleId, $userId]);
        $db->commit();
    }

    /**
     * Get user roles
     *
     * @param int $userId The user Id
     * @return array An array containing user roles
     */
    public static function getUserRoles($userId)
    {
        $db = Piko::get('db');
        $query = 'SELECT `auth_role`.`name` FROM `auth_role` '
               . 'INNER JOIN `auth_assignment` ON `auth_assignment`.`role_id` = `auth_role`.`id` '
               . 'WHERE `auth_assignment`.`user_id` = :user_id '
               . 'GROUP BY role_id';
        $st = $db->prepare($query);
        $st->execute(['user_id' => $userId]);

        return $st->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get user roles ids
     *
     * @param int $userId The user Id
     * @return array An array containing user role ids
     */
    public static function getUserRoleIds($userId)
    {
        $db = Piko::get('db');
        $query = 'SELECT role_id FROM `auth_assignment` WHERE user_id = :user_id';
        $sth = $db->prepare($query);
        $sth->execute(['user_id' => $userId]);

        return $sth->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get user permissions
     *
     * @param int $userId The user Id
     * @return array An array containing user permissions
     */
    public static function getUserPermissions($userId)
    {
        $db = Piko::get('db');
        $query = 'SELECT p.`name` FROM `auth_permission` AS p '
               . 'INNER JOIN `auth_role_has_permission` AS ap ON ap.`permission_id` = p.`id` '
               . 'INNER JOIN `auth_assignment` AS aa ON aa.`role_id` = ap.`role_id` '
               . 'WHERE aa.`user_id` = :user_id '
               . 'GROUP BY permission_id';

        $st = $db->prepare($query);
        $st->execute(['user_id' => $userId]);

        return $st->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get role permissions
     *
     * @param string $roleName The role name
     * @return array An array of permissions as string
     */
    public static function getRolePermissions($roleName)
    {
        $roleId = static::getRoleId($roleName);

        if (!$roleId) {
            throw new \RuntimeException("Role $roleName doesn't exists");
        }

        /* @var $db \piko\Db */
        $db = Piko::get('db');
        $query = 'SELECT p.`name` FROM `auth_permission` AS p '
            . 'INNER JOIN `auth_role_has_permission` AS ap ON ap.`permission_id` = p.`id` '
            . 'WHERE ap.`role_id` = :role_id '
            . 'GROUP BY permission_id';
        $st = $db->prepare($query);
        $st->execute(['role_id' => $roleId]);

        return $st->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get role permission ids
     *
     * @param string $roleName The role name
     * @return array An array of permission ids
     */
    public static function getRolePermissionIds($roleName)
    {
        $roleId = static::getRoleId($roleName);

        if (!$roleId) {
            throw new \RuntimeException("Role $roleName doesn't exists");
        }

        /* @var $db \piko\Db */
        $db = Piko::get('db');

        $query = 'SELECT permission_id FROM `auth_role_has_permission` WHERE role_id = :role_id';
        $sth = $db->prepare($query);
        $sth->execute(['role_id' => $roleId]);

        return $sth->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Create a permission
     *
     * @param string $name The permission name
     * @return int The permission id
     */
    public static function createPermission($name)
    {
        $db = Piko::get('db');
        $query = 'INSERT INTO `auth_permission` (`name`) VALUES (?)';

        $db->beginTransaction();
        $st = $db->prepare($query);
        $st->execute([$name]);
        $db->commit();

        return (int) $db->lastInsertId();
    }

    /**
     * Check if the permission exists
     *
     * @param string $name The permission name
     * @return boolean
     */
    public static function permissionExists($name)
    {
        $db = Piko::get('db');
        $st = $db->prepare('SELECT COUNT(`id`) FROM `auth_permission` WHERE `name` = :name');
        $st->execute(['name' => $name]);

        return ((int) $st->fetchColumn() > 0) ? true : false;
    }

    /**
     * Get the permission Id
     *
     * @param string $name The permission name
     * @return int The permission id (0 if the permission is not found)
     */
    public static function getPermissionId($name)
    {
        $db = Piko::get('db');
        $st = $db->prepare('SELECT `id` FROM `auth_permission` WHERE `name` = :name');
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
    public static function assignPermission($roleName, $permissionName)
    {
        $roleId = static::getRoleId($roleName);
        $permissionId = static::getPermissionId($permissionName);

        if (!$roleId) {
            throw new \RuntimeException("Role $roleName doesn't exists");
        }

        if (!$permissionId) {
            throw new \RuntimeException("Permission $permissionName doesn't exists");
        }

        $db = Piko::get('db');
        $query = 'INSERT INTO `auth_role_has_permission` (`role_id`, `permission_id`) VALUES (?, ?)';

        $db->beginTransaction();
        $st = $db->prepare($query);
        $st->execute([$roleId, $permissionId]);
        $db->commit();
    }
}
