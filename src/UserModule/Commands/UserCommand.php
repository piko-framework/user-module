<?php

/**
 * This file is part of the Piko user module
 *
 * @package Piko\UserModule
 * @copyright 2025 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/user-module
 */

declare(strict_types=1);

namespace Piko\UserModule\Commands;

use Piko\UserModule\Models\User;
use Piko\UserModule\Rbac;
use Piko\I18n;

/**
 * UserCommand Class
 *
 * Command used to manage users
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class UserCommand extends AbstractCommand
{
    public function __construct()
    {
        $i18n = new I18n();
        I18n::setInstance($i18n);
    }

    public function create(array $options = []): int
    {
        $interactive = (bool) ($options['interactive'] ?? false);

        if ($interactive) {
            echo "=== Create a new admin user ===\n";

            $options['name'] = readline("Enter Full Name: ");
            $options['email'] = readline("Enter Email Address: ");
            $options['username'] = readline("Choose a Username: ");
            $options['password'] = readline("Choose a Password: ");

            echo "\nThank you. Creating your admin user...\n\n";
        }

        Rbac::setPDO($this->db);

        if (!Rbac::roleExists('admin')) {
            echo "Admin role does not exist. Creating 'admin' role...\n";
            Rbac::createRole('admin');
        }

        $user = new User($this->db);
        $user->scenario = User::SCENARIO_ADMIN;
        $user->bind($options);

        if ($user->isValid() && $user->save()) {

            Rbac::assignRole($user->id, 'admin');
            echo $this->successMsg("Success! The admin user '{$user->username}' was created.\n
            You can now log in with this account.\n");

            return 0;
        }

        echo $this->errorMsg("User creation failed for the following reasons:
        \n" . implode("\n- ", $user->getErrors()) . PHP_EOL);

        return 1;
    }
}
