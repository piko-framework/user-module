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
use Nette\Mail\Message;
use Nette\Utils\Random;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string  $name;
 * @property string  $username;
 * @property string  $email;
 * @property string  $password;
 * @property string  $auth_key;
 * @property integer $confirmed_at;
 * @property integer $blocked_at;
 * @property string  $registration_ip;
 * @property integer $created_at;
 * @property integer $updated_at;
 * @property integer $last_login_at;
 * @property string  $timezone;
 * @property string  $profil;
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class User extends \piko\DbRecord implements \piko\IdentityInterface
{
    const SCENARIO_ADMIN = 'admin';
    const SCENARIO_REGISTER = 'register';
    const SCENARIO_RESET = 'reset';

    /**
     * The table name
     * @var string
     */
    protected $tableName = 'user';

    /**
     * The model errors
     *
     * @var array
     */
    public $errors = [];

    /**
     * The model scenario
     *
     * @var string
     */
    public $scenario = '';

    /**
     * The user role ids
     *
     * @var array
     */
    protected $roleIds = [];

    /**
     * The confirmation password
     *
     * @var string
     */
    protected $password2 = '';

    /**
     * Reset password state
     *
     * @var boolean
     */
    protected $resetPassword = false;

    /**
     * The table schema
     *
     * @var array
     */
    protected $schema = [
        'id'              => self::TYPE_INT,
        'name'            => self::TYPE_STRING,
        'username'        => self::TYPE_STRING,
        'email'           => self::TYPE_STRING,
        'password'        => self::TYPE_STRING,
        'auth_key'        => self::TYPE_STRING,
        'confirmed_at'    => self::TYPE_INT,
        'blocked_at'      => self::TYPE_INT,
        'registration_ip' => self::TYPE_STRING,
        'created_at'      => self::TYPE_INT,
        'updated_at'      => self::TYPE_INT,
        'last_login_at'   => self::TYPE_INT,
        'is_admin'        => self::TYPE_INT,
        'timezone'        => self::TYPE_STRING,
        'profil'          => self::TYPE_STRING,
    ];

    /**
     * {@inheritDoc}
     * @see \piko\DbRecord::beforeSave()
     */
    protected function beforeSave($isNew)
    {
        if ($isNew) {
            $this->name = $this->username;
            $this->password = sha1($this->password);
            $this->created_at = time();
            $this->auth_key = sha1(Random::generate(10));
        } else {
            $this->updated_at = time();

            if ($this->resetPassword) {
                $this->password = sha1($this->password);
            }
        }

        return parent::beforeSave($isNew);
    }

    /**
     * {@inheritDoc}
     * @see \piko\DbRecord::afterSave()
     */
    protected function afterSave()
    {
        if ($this->scenario === self::SCENARIO_ADMIN) {

            // Don't allow admin user to remove its admin role
            if ($this->id == Piko::get('user')->getId()) {

                $adminRole = Piko::get('userModule')->adminRole;
                $adminRoleId = Rbac::getRoleId($adminRole);

                if (!in_array($adminRoleId, $this->roleIds)) {
                    $this->roleIds[] = $adminRoleId;
                }
            }

            if (!empty($this->roleIds)) {

                $roleIds = Rbac::getUserRoleIds($this->id);

                $idsToRemove = array_diff($roleIds, $this->roleIds);
                $idsToAdd = array_diff($this->roleIds, $roleIds);

                if (!empty($idsToRemove)) {
                    $query = 'DELETE FROM `auth_assignment` WHERE user_id = :user_id AND role_id IN('
                           . implode(',', $idsToRemove) . ')';
                    $st = $this->db->prepare($query);
                    $st->execute(['user_id' => $this->id]);
                }

                if (!empty($idsToAdd)) {
                    $values = [];
                    foreach ($idsToAdd as $id) {
                        $values[] = '(' . (int) $this->id . ',' . (int) $id . ')';
                    }

                    $query = 'INSERT INTO `auth_assignment` (user_id, role_id) VALUES ' . implode(', ', $values);

                    $this->db->beginTransaction();
                    $st = $this->db->prepare($query);
                    $st->execute();
                    $this->db->commit();
                }
            } else {

                $st = $this->db->prepare('DELETE FROM `auth_assignment` WHERE user_id = :user_id');
                $st->execute(['user_id' => $this->id]);
            }
        }

        parent::afterSave();
    }

    /**
     * {@inheritDoc}
     * @see \piko\Model::bind()
     */
    public function bind($data)
    {
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }

        if (isset($data['password2'])) {
            $this->password2 = $data['password2'];
            unset($data['password2']);
        }

        if (!empty($data['password']) && !$this->validatePassword($data['password'])) {
            $this->resetPassword = true;
        }

        if (!empty($data['profil']) && is_array($data['profil'])) {
            $data['profil'] = json_encode($data['profil']);
        }

        if (isset($data['roles']) && $this->scenario == self::SCENARIO_ADMIN) {
            $this->roleIds = $data['roles'];
            unset($data['roles']);
        }

        parent::bind($data);
    }

    /**
     * {@inheritDoc}
     * @see \piko\Model::validate()
     */
    public function validate()
    {
        if (empty($this->email)) {
            $this->errors['email'] = Piko::t('user', 'Email must be filled in.');
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = Piko::t(
                'user',
                '{email} is not a valid email address.',
                ['email' => $this->data['email']]
            );
        }

        if (($this->scenario == self::SCENARIO_REGISTER || $this->scenario == self::SCENARIO_ADMIN)
            && empty($this->username)) {
            $this->errors['username'] = Piko::t('user', 'Username must be filled in.') ;
        } elseif (($this->scenario == self::SCENARIO_REGISTER || $this->scenario == self::SCENARIO_ADMIN)
            && !ctype_alnum($this->username)) {
            $this->errors['username'] = Piko::t('user', 'The username should only contain alphanumeric characters.');
        }

        // New user
        if (($this->scenario == self::SCENARIO_REGISTER || $this->scenario == self::SCENARIO_ADMIN)
            && empty($this->id)) {

            $st = $this->db->prepare('SELECT id FROM user WHERE email = ?');
            $st->execute([$this->email]);
            $id = $st->fetchColumn();

            if ($id) {
                $this->errors['email'] = Piko::t('user', 'This email is already used.');
            }

            $st = $this->db->prepare('SELECT id FROM user WHERE username = ?');
            $st->execute([$this->username]);
            $id = $st->fetchColumn();

            if ($id) {
                $this->errors['username'] = Piko::t('user', 'This username is already used.');
            }
        }

        if (($this->scenario == self::SCENARIO_REGISTER || $this->scenario == self::SCENARIO_RESET)
            && empty($this->password)) {
            $this->errors['password'] = Piko::t('user', 'Password must be filled in.');

        } elseif (($this->scenario == self::SCENARIO_REGISTER || $this->scenario == self::SCENARIO_RESET) &&
                strlen($this->password) < piko::get('userModule')->passwordMinLength) {
            $this->errors['password'] =  Piko::t(
                'user',
                'Password is to short. Minimum {num}: characters.',
                ['num' => piko::get('userModule')->passwordMinLength]
            );
        }

        if (($this->scenario == self::SCENARIO_REGISTER || $this->scenario == self::SCENARIO_RESET)  &&
            $this->password != $this->password2) {
                $this->errors['password2'] = Piko::t('user', 'Passwords are not the same.');
        }

        if (empty($this->errors)) {
            return true;
        }

        return false;
    }

    /**
     * Get user role ids
     *
     * @return array An array containg role ids
     */
    public function getRoleIds()
    {
        return Rbac::getUserRoleIds($this->id);
    }

    /**
     * Activate an user
     *
     * @return boolean
     */
    public function activate()
    {
        $this->confirmed_at = time();
        return $this->save();
    }

    /**
     * Check if the user is activated
     * @return boolean
     */
    public function isActivated()
    {
        return empty($this->confirmed_at) ? false : true;
    }

    /**
     * Send Registration confirmation email
     *
     * @return boolean Return false if fail to send email
     */
    public function sendRegistrationConfirmation()
    {
        /* @var $router \Piko\Router */
        $router = Piko::get('router');

        $siteName = getenv('SITE_NAME');
        $baseUrl = $this->getAbsoluteBaseUrl();

        $message = Piko::t('user', 'confirmation_mail_body', [
            'site_name' => $siteName,
            'link' => $baseUrl . $router->getUrl('user/default/confirmation', ['token' => $this->auth_key]),
            'base_url' => $baseUrl,
            'username' => $this->username,
        ]);

        $subject = Piko::t('user', 'Registration confirmation on {site_name}', ['site_name' => $siteName]);

        $mail = new Message();
        $mail->setFrom($siteName . ' <' . getenv('NO_REPLY_EMAIL') . '>')
             ->addTo($this->email)
             ->setSubject($subject)
             ->setBody($message);

        /* @var $mailer \Nette\Mail\SmtpMailer */
        $mailer = Piko::get('mailer');

        try {
            $mailer->send($mail);
            return true;

        } catch (\Exception $e) {
            $this->errors['sendmail'] = $e->getMessage();
        }

        return false;
    }

    /**
     * Send reset password email
     *
     * @return boolean Return false if fail to send email
     */
    public function sendResetPassword()
    {
        /* @var $router \Piko\Router */
        $router = Piko::get('router');
        $siteName = getenv('SITE_NAME');

        $baseUrl = $this->getAbsoluteBaseUrl();

        $message = Piko::t('user', 'reset_password_mail_body', [
            'site_name' => $siteName,
            'link' => $baseUrl . $router->getUrl('user/default/reset-password', ['token' => $this->auth_key]),
            'username' => $this->username,
        ]);

        $subject = Piko::t('user', 'Password change request on {site_name}', ['site_name' => $siteName]);

        $mail = new Message();
        $mail->setFrom($siteName . ' <' . getenv('NO_REPLY_EMAIL') . '>')
             ->addTo($this->email)
             ->setSubject($subject)
             ->setBody($message);

        /* @var $mailer \Nette\Mail\SmtpMailer */
        $mailer = Piko::get('mailer');

        try {
            $mailer->send($mail);
            return true;

        } catch (\Exception $e) {
            $this->errors['sendmail'] = $e->getMessage();
        }

        return false;
    }

    /**
     * Get users
     *
     * @param array $filters Array of filter conditions (['name' => ''])
     * @param string $order The order condition
     * @param number $start The offset start
     * @param number $limit The offset limit
     *
     * @return array An array of user rows
     */
    public static function find($filters = [], $order = '', $start = 0, $limit = 0)
    {
        /* @var $db \piko\Db */
        $db = Piko::get('db');
        $query = 'SELECT * FROM `user`';
        $where = [];

        if (!empty($filters['name'])) {
            $where[] = '`name` LIKE :search';
        }

        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        $query .= ' ORDER BY ' . (empty($order) ? '`id` DESC' : $order);

        if (!empty($start)) {
            $query .= ' OFFSET ' . (int) $start;
        }

        if (!empty($limit)) {
            $query .= ' LIMIT ' . (int) $limit;
        }

        $sth = $db->prepare($query);

        $sth->execute($filters);

        return $sth->fetchAll();
    }


    /**
     * Find user by username
     *
     * @param string $username
     * @return User|NULL
     */
    public static function findByUsername($username)
    {
        $db = \piko\Piko::get('db');

        $st = $db->prepare('SELECT id FROM user WHERE username = ?');
        $st->bindParam(1, $username, \PDO::PARAM_STR);

        if ($st->execute()) {
            $id = $st->fetchColumn();

            if ($id) {
                $user = new static();
                $user->load($id);

                return $user;
            }
        }

        return null;
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|NULL
     */
    public static function findByEmail($email)
    {
        $db = \piko\Piko::get('db');

        $st = $db->prepare('SELECT id FROM user WHERE email = ?');
        $st->bindParam(1, $email, \PDO::PARAM_STR);

        if ($st->execute()) {
            $id = $st->fetchColumn();

            if ($id) {
                $user = new static();
                $user->load($id);

                return $user;
            }
        }

        return null;
    }

    /**
     * Find user by auth key
     *
     * @param string $token
     * @return User|NULL
     */
    public static function findByAuthKey($token)
    {
        $db = \piko\Piko::get('db');

        $st = $db->prepare('SELECT id FROM `user` WHERE `auth_key` = ?');

        if ($st->execute([$token])) {
            $id = $st->fetchColumn();

            if ($id) {
                $user = new static();
                $user->load($id);

                return $user;
            }
        }

        return null;
    }

    /**
     * Validate password
     *
     * @param string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
        return $this->password == sha1($password);
    }

    /**
     * Find user by Id
     *
     * @param int $id
     * @return User|NULL
     */
    public static function findIdentity($id)
    {
        try {
            $user = new static($id);
            return $user;
        } catch (\RuntimeException $e) {

        }

        return null;
    }

    /**
     * {@inheritDoc}
     * @see \piko\IdentityInterface::getId()
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get absolute base Url
     *
     * @return string
     */
    protected function getAbsoluteBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

        return "$protocol://{$_SERVER['HTTP_HOST']}";
    }
}
