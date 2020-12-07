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

/**
 * This is the model class for table "auth_permission".
 *
 * @property integer $id
 * @property string  $name;
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Permission extends \piko\DbRecord
{
    /**
     * The table name
     *
     * @var string
     */
    protected $tableName = 'auth_permission';

    /**
     * The model errors
     *
     * @var array
     */
    public $errors = [];

    /**
     * The table schema
     *
     * @var array
     */
    protected $schema = [
        'id'              => self::TYPE_INT,
        'name'            => self::TYPE_STRING,
    ];

    /**
     * {@inheritDoc}
     * @see \piko\Model::validate()
     */
    public function validate()
    {
        if (empty($this->name)) {
            $this->errors['name'] = Piko::t('user', 'Permission name must be filled in.');
        } else {
            $st = $this->db->prepare('SELECT COUNT(`id`) FROM `auth_permission` WHERE name = :name');
            $st->execute(['name' => $this->name]);

            $count = (int) $st->fetchColumn();

            if ($count) {
                $this->errors['name'] = Piko::t('user', 'Permission already exists.');
            }
        }

        if (empty($this->errors)) {
            return true;
        }

        return false;
    }

    /**
     * Find permissions
     *
     * @param string $order The order condition
     * @param number $start The offset start
     * @param number $limit The offset limit
     *
     * @return array An array of permission rows
     */
    public static function find($order = '', $start = 0, $limit = 0)
    {
        /* @var $db \piko\Db */
        $db = Piko::get('db');
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
