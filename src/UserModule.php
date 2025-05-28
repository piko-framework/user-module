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

namespace Piko;

use PDO;
use Piko\UserModule\Models\User;
use Piko\UserModule\AccessChecker;
use Piko\UserModule\Rbac;
use Piko\I18n;
use RuntimeException;

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

    public static function install(PDO $db)
    {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        $sql = \file_get_contents(__DIR__ . '/../sql/install-' . $driver . '.sql');

        if ($db->exec($sql) === false) {
            $error = $db->errorInfo();
            throw new RuntimeException("Query failed with error : {$error[2]}");
        }
    }

    public static function createUser(PDO $db, string $name, string $email, string $username, string $password)
    {
        Rbac::setPDO($db);

        $user = new User($db);
        $user->bind([
            'name' =>  $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]);

        if (!$user->save()) {
            throw new RuntimeException('User creation failed: ' . implode("\n", $user->getErrors()));
        }

        if (!Rbac::roleExists('admin')) {
            Rbac::createRole('admin');
        }

        Rbac::assignRole($user->id, 'admin');
    }
}
