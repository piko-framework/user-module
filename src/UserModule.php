<?php

/**
 * This file is part of the Piko user module
 *
 * @package Piko\UserModule
 * @copyright 2025 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/user-module
 *
 * Routes :
 * /user/default/login : Process login
 * /user/default/logout : Process logout
 * /user/default/register : Process user registration
 * /user/default/edit : User account form
 * /user/admin/users : Manage users, roles, permissions
 */

declare(strict_types=1);

namespace Piko;

use PDO;
use Piko\UserModule\Models\User;
use Piko\UserModule\AccessChecker;
use Piko\UserModule\Rbac;
use Piko\I18n;

/**
 * User Module class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class UserModule extends Module
{
    /**
     * Controller namespace
     *
     * @var string
     */
    public $controllerNamespace = 'Piko\\UserModule\\Controllers';

    /**
     * Admin role name
     *
     * @var string
     */
    public $adminRole = 'admin';

    /**
     * Allow user registration
     *
     * @var boolean
     */
    public $allowUserRegistration = false;

    /**
     * Minimum length of the user password
     *
     * @var integer
     */
    public $passwordMinLength = 8;

    /**
     * URL to redirect to after successful login
     *
     * @var string
     */
    public $redirectUrlAfterLogin = '/';

    /**
     * URL to redirect to after logout
     *
     * @var string
     */
    public $redirectUrlAfterLogout = '/';

    public function bootstrap()
    {
        $pdo = $this->application->getComponent('PDO');
        assert($pdo instanceof PDO);

        $i18n =  $this->application->getComponent('Piko\I18n');
        assert($i18n instanceof I18n);
        $i18n->addTranslation('user', __DIR__ . '/messages');

        Rbac::setPDO($pdo);
        User::setPDO($pdo);

        AccessChecker::setAdminRole($this->adminRole);
    }
}
