<?php

/**
 * This file is part of the Piko user module
 *
 * @package Piko\UserModule
 * @copyright 2025 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/user-module
 */

namespace Piko\UserModule\Models;

use PDO;
use Piko\DbRecord\Attribute\Table;
use Piko\DbRecord\Attribute\Column;

use function Piko\I18n\__;

/**
 * Permission class
 *
 * Model representing a user permission
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
#[Table(name:'auth_permission')]
class Permission extends \Piko\DbRecord
{
    /**
     * Permission primary key id
     *
     * @var integer|null
     */
    #[Column(primaryKey: true)]
    public ?int $id = null;

    /**
     * Permission name
     *
     * @var string
     */
    #[Column]
    public string $name = '';

    /**
     * {@inheritDoc}
     * @see \Piko\DbRecord::bind()
     */
    public function bind($data): void
    {
        if (isset($data['id'])) {
            $data['id'] = (int) $data['id'];
        }

        parent::bind($data);
    }

    /**
     * {@inheritDoc}
     * @see \Piko\ModelTrait::validate()
     */
    protected function validate(): void
    {
        if (empty($this->name)) {
            $this->setError('name', __('user', 'Permission name must be filled in.'));
        } elseif (!$this->id) {
            $st = $this->db->prepare('SELECT COUNT(`id`) FROM `auth_permission` WHERE name = :name');
            $st->execute(['name' => $this->name]);

            $count = (int) $st->fetchColumn();

            if ($count) {
                $this->setError('name', __('user', 'Permission already exists.'));
            }
        }
    }

    /**
     * Find permissions
     *
     * @param PDO $db a PDO connexion
     * @param string $order The order condition
     * @param number $start The offset start
     * @param number $limit The offset limit
     *
     * @return array An array of permission rows
     */
    public static function find(PDO $db, $order = '', $start = 0, $limit = 0)
    {
        $query = 'SELECT `id`, `name` FROM `auth_permission`';
        $query .= ' ORDER BY ' . (empty($order) ? '`id` DESC' : $order);

        if (!empty($start)) {
            $query .= ' OFFSET ' . (int) $start;
        }

        if (!empty($limit)) {
            $query .= ' LIMIT ' . (int) $limit;
        }

        $sth = $db->prepare($query);

        $sth->execute();

        return $sth->fetchAll();
    }
}
