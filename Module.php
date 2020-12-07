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
use piko\user\models\User;

/**
 * User module class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Module extends \piko\Module
{
    /**
     * The admin role
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
     * Min length of the user password
     *
     * @var integer
     */
    public $passwordMinLength = 8;

    /**
     * Module bootstrap with application
     * @see \piko\Application
     */
    public function bootstrap()
    {
        // Give access to user module everywhere
        Piko::set('userModule', $this);
    }

    /**
     * {@inheritDoc}
     * @see \piko\Module::init()
     */
    protected function init()
    {
        /* @var $i18n \piko\i18n */
        $i18n = Piko::get('i18n');
        $i18n->addTranslation('user', __DIR__ . '/messages');

        parent::init();
    }

    /**
     * Install from the CLI
     *
     * @throws \RuntimeException if fail to install
     */
    public function install()
    {
        if (PHP_SAPI == 'cli') {
            $db = Piko::get('db');
            $query = file_get_contents(__DIR__ . '/sql/install-sqlite.sql');

            if ($db->exec($query) === false) {
                $error = $db->errorInfo();
                throw new \RuntimeException("Query failed with error : {$error[2]}");
            }

            echo "Users table created.\n";
        }
    }

    /**
     * Create an user from the CLI
     */
    public function createUser()
    {
        if (PHP_SAPI == 'cli') {
            echo "Create admin user\n";
            $name = readline("Nom : ");
            $email = readline("Email : ");
            $username = readline("Nom d'utilisateur : ");
            $password = readline("Mot de passe : ");

            $user = new User();
            $user->bind([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => $password,
            ]);

            if ($user->save()) {
                echo "Utilisateur $username créé.\n";
            }

            if (!Rbac::roleExists('admin')) {
                Rbac::createRole('admin');
            }

            Rbac::assignRole($user->id, 'admin');
        }
    }
}
