<?php

/**
 * This file is part of the Piko user module
 *
 * @package Piko\UserModule
 * @copyright 2025 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/user-module
 */

namespace Piko\UserModule;

use Piko\UserModule\Models\User;

/**
 * AccessChecker class
 *
 * Checks a user's access rights
 *
 * @author Sylvain PHILIP <sylvain.philip@piko-framework.org>
 */
class AccessChecker
{
    /**
     * Admin role name
     *
     * @var string
     */
    private static $adminRole;

    /**
     * User roles
     *
     * @var null|array
     */
    private static $roles = null;

    /**
     * User permissions
     *
     * @var null|array
     */
    private static $permissions = null;

    public static function setAdminRole(string $role): void
    {
        static::$adminRole = $role;
    }

    /**
     * Check Permission or role access
     *
     * @param int $userId The user Id
     * @param string $permission The permission or role name
     * @return bool
     *
     * @see \piko\User
     */
    public static function checkAccess($userId, string $permission): bool
    {
        $identity = User::findIdentity($userId);

        if ($identity !== null) {

            if (static::$roles === null) {
                static::$roles = Rbac::getUserRoles($identity->id);
            }

            if (in_array(static::$adminRole, static::$roles)) {
                return true;
            }

            if (in_array($permission, static::$roles)) {
                return true;
            }

            if (static::$permissions === null) {
                static::$permissions = Rbac::getUserPermissions($identity->id);
            }

            if (in_array($permission, static::$permissions)) {
                return true;
            }
        }

        return false;
    }
}
