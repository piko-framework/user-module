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

use PDO;

/**
 * AbstractCommand Class
 *
 * The base Command class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class AbstractCommand
{
    /**
     * A PDO instance
     *
     * @var PDO
     */
    protected PDO $db;

    public function setPDO(PDO $db): void
    {
        $this->db = $db;
    }

    public function successMsg(string $message): string
    {
        $green = "\033[32m";
        $reset = "\033[0m";

        return $green . $message . $reset;
    }

    public function errorMsg(string $message): string
    {
        $red = "\033[31m";
        $reset = "\033[0m";

        return $red . $message . $reset;
    }
}
