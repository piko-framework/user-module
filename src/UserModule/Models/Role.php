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
use Piko\DbRecord;
use Piko\UserModule\Rbac;
use Piko\DbRecord\Attribute\Table;
use Piko\DbRecord\Attribute\Column;

use function Piko\I18n\__;

/**
 * Role class
 *
 * This class represents a role in the user management system.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
#[Table(name:'auth_role')]
class Role extends DbRecord
{
    /**
     * Admin scenario
     *
     * @var string
     */
    public const SCENARIO_ADMIN = 'admin';

    /**
     * Curent model scenario
     *
     * @var string
     */
    public $scenario = '';

    /**
     * Role permissions
     *
     * @var array
     */
    public $permissions = [];

    /**
     * Role primary key id
     *
     * @var integer|null
     */
    #[Column(primaryKey: true)]
    public ?int $id = null;

    /**
     * Role name
     *
     * @var string
     */
    #[Column]
    public string $name = '';

    /**
     * Role description
     *
     * @var string
     */
    #[Column]
    public string $description = '';

    /**
     * {@inheritDoc}
     * @see \Piko\DbRecord::load()
     */
    public function load($id = 0): DbRecord
    {
        $record = parent::load($id);

        if (!empty($this->name)) {
            $this->permissions = Rbac::getRolePermissionIds($this->name);
        }

        return $record;
    }

    /**
     * {@inheritDoc}
     * @see \Piko\DbRecord::bind()
     */
    public function bind($data): void
    {
        if (isset($data['permissions'])) {
            $this->permissions = $data['permissions'];
            unset($data['permissions']);
        }

        if (isset($data['id'])) {
            $data['id'] = (int) $data['id'];
        }

        parent::bind($data);
    }

    /**
     * {@inheritDoc}
     * @see \Piko\DbRecord::afterSave()
     */
    protected function afterSave(): void
    {
        if ($this->scenario === self::SCENARIO_ADMIN) {

            $st = $this->db->prepare('DELETE FROM `auth_role_has_permission` WHERE role_id = :role_id');

            if (!$st->execute(['role_id' => $this->id])) {
                throw new \RuntimeException(
                    "Error while trying to delete role id {$this->id} in auth_role_has_permission table"
                );
            }

            if (!empty($this->permissions)) {

                $values = [];

                foreach ($this->permissions as $id) {
                    $values[] = '(' . (int) $this->id . ',' . (int) $id . ')';
                }

                $query = 'INSERT INTO `auth_role_has_permission` (role_id, permission_id) VALUES '
                       . implode(', ', $values);

                $this->db->beginTransaction();

                $st = $this->db->prepare($query);
                $st->execute();
                $this->db->commit();
            }
        }

        parent::afterSave();
    }

    /**
     * {@inheritDoc}
     * @see \Piko\ModelTrait::validate()
     */
    protected function validate(): void
    {
        if (empty($this->name)) {
            $this->setError('name', __('user', 'Role name must be filled in.'));
        } elseif (!$this->id) {
            $st = $this->db->prepare('SELECT COUNT(`id`) FROM `auth_role` WHERE name = :name');
            $st->execute(['name' => $this->name]);
            $count = (int) $st->fetchColumn();

            if ($count) {
                $this->setError('name', __('user', 'Role already exists.'));
            }
        }
    }

    /**
     * Get roles
     *
     * @param PDO $db a PDO connexion
     * @param string $order The order condition
     * @param number $start The offset start
     * @param number $limit The offset limit
     *
     * @return array An array of role rows
     */
    public static function find(PDO $db, $order = '', $start = 0, $limit = 0)
    {
        $query = 'SELECT * FROM `auth_role`';

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
