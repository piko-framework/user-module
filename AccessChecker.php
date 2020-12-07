<?php
/**
 * This file is part of the Piko user module
 *
 * @copyright 2020 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko-user
 */
namespace piko\user;

use piko\user\models\User;

/**
 * Access checker class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class AccessChecker
{
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

    /**
     * Check Permission or role access
     *
     * @param int $userId The user Id
     * @param string $permission The permission or role name
     * @return bool
     *
     * @see \piko\User
     */
    public function checkAccess($userId, string $permission) : bool
    {
        $identity = User::findIdentity($userId);

        if ($identity !== null) {

            if (static::$roles === null) {
                static::$roles = Rbac::getUserRoles($identity->id);
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
