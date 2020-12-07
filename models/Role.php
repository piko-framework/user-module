<?php
/**
 * This file is part of the Piko user module
 *
 * @copyright 2020 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko-user
 */
namespace piko\user\models;

use piko\Piko;
use piko\user\Rbac;

/**
 * This is the model class for table "auth_role.
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string  $name;
 * @property string  $description;
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Role extends \piko\DbRecord
{
    const SCENARIO_ADMIN = 'admin';

    /**
     * The table name
     *
     * @var string
     */
    protected $tableName = 'auth_role';

    /**
     * The model scenario
     *
     * @var string
     */
    public $scenario = '';

    /**
     * The model errors
     *
     * @var array
     */
    public $errors = [];

    /**
     * The role permissions
     *
     * @var array
     */
    public $permissions = [];

    /**
     * The table schema
     *
     * @var array
     */
    protected $schema = [
        'id'          => self::TYPE_INT,
        'name'        => self::TYPE_STRING,
        'description' => self::TYPE_STRING,
    ];

    /**
     * {@inheritDoc}
     * @see \piko\Component::init()
     */
    protected function init()
    {
        if (!empty($this->name)) {
            $this->permissions = Rbac::getRolePermissionIds($this->name);
        }
    }

    /**
     * {@inheritDoc}
     * @see \piko\Model::bind()
     */
    public function bind($data)
    {
        if (isset($data['permissions'])) {
            $this->permissions = $data['permissions'];
            unset($data['permissions']);
        }

        parent::bind($data);
    }

    /**
     * {@inheritDoc}
     * @see \piko\DbRecord::afterSave()
     */
    protected function afterSave()
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
     * @see \piko\Model::validate()
     */
    public function validate()
    {
        if (empty($this->name)) {
            $this->errors['name'] = Piko::t('user', 'Role name must be filled in.');
        } else {
            $st = $this->db->prepare('SELECT COUNT(`id`) FROM `auth_role` WHERE name = :name');
            $st->execute(['name' => $this->name]);

            $count = (int) $st->fetchColumn();

            if ($count) {
                $this->errors['name'] = Piko::t('user', 'Role already exists.');
            }
        }

        if (empty($this->errors)) {
            return true;
        }

        return false;
    }

    /**
     * Get roles
     *
     * @param string $order The order condition
     * @param number $start The offset start
     * @param number $limit The offset limit
     *
     * @return array An array of role rows
     */
    public static function find($order = '', $start = 0, $limit = 0)
    {
        /* @var $db \piko\Db */
        $db = Piko::get('db');
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
