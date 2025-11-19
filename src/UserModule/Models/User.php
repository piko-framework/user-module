<?php

/**
 * This file is part of the Piko user module
 *
 * @package   Piko\UserModule
 * @copyright 2025 Sylvain PHILIP.
 * @license   LGPL-3.0; see LICENSE.txt
 * @link      https://github.com/piko-framework/user-module
 */

namespace Piko\UserModule\Models;

use PDO;
use DateTime;
use stdClass;
use Piko\DbRecord;
use RuntimeException;
use Nette\Mail\Message;
use Nette\Utils\Random;
use Piko\UserModule\Rbac;
use Nette\Mail\Mailer;
use Piko\DbRecord\Attribute\Table;
use Piko\DbRecord\Attribute\Column;

use function Piko\I18n\__;

/**
 * User Class
 *
 * This is the model class for table "user".
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
#[Table(name:'user')]
class User extends DbRecord implements \Piko\User\IdentityInterface
{
    /**
     * Administration scenario
     *
     * @var string
     */
    public const SCENARIO_ADMIN = 'admin';

    /**
     * Registration scenario
     *
     * @var string
     */
    public const SCENARIO_REGISTER = 'register';

    /**
     * Reset scenario
     *
     * @var string
     */
    public const SCENARIO_RESET = 'reset';

    /**
     * Shared PDO instance
     *
     * @var PDO
     */
    protected static ?PDO $pdo = null;

    /**
     * Minimum password length (register/reset scenario)
     *
     * @var integer
     */
    public int $passwordMinLength = 8;

    /**
     * Current model scenario
     *
     * @var string
     */
    public $scenario = '';

    /**
     * User role IDs
     *
     * @var array
     */
    protected $roleIds = [];

    /**
     * Password confirmation
     *
     * @var string
     */
    protected $password2 = '';

    /**
     * Indicates if the password should be reset
     *
     * @var boolean
     */
    protected $resetPassword = false;

    /**
     * User ID
     *
     * @var integer|null
     */
    #[Column(primaryKey: true)]
    public ?int $id = null;

    /**
     * User full name
     *
     * @var string
     */
    #[Column]
    public string $name = '';

    /**
     * Username
     *
     * @var string
     */
    #[Column]
    public string $username = '';

    /**
     * Email address
     *
     * @var string
     */
    #[Column]
    public string $email = '';

    /**
     * Hashed password
     *
     * @var string
     */
    #[Column]
    public string $password = '';

    /**
     * Authentication key
     *
     * @var string
     */
    #[Column]
    public string $auth_key = '';

    /**
     * Confirmation date
     *
     * @var string|null
     */
    #[Column]
    public ?string $confirmed_at = null;

    /**
     * Block date
     *
     * @var string|null
     */
    #[Column]
    public ?string $blocked_at = null;

    /**
     * Registration IP address
     *
     * @var string
     */
    #[Column]
    public string $registration_ip = '';

    /**
     * Creation date
     *
     * @var string|null
     */
    #[Column]
    public ?string $created_at = null;

    /**
     * Update date
     *
     * @var string|null
     */
    #[Column]
    public ?string $updated_at = null;

    /**
     * Last login date
     *
     * @var string|null
     */
    #[Column]
    public ?string $last_login_at = null;

    /**
     * Indicates if the user is an administrator
     *
     * @var integer
     */
    #[Column]
    public int $is_admin = 0;

    /**
     * Timezone
     *
     * @var string
     */
    #[Column]
    public string $timezone = '';

    /**
     * User profile (JSON or object)
     *
     * @var string|stdClass|null
     */
    #[Column]
    public string|stdClass|null $profil = null;

    public static function setPDO(PDO $pdo): void
    {
        static::$pdo = $pdo;
    }

    protected function getCurrentDatetime(): string
    {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

        return match ($driver) {
            'sqlite' => (string) time(),
            'mysql' => (new DateTime())->format('Y-m-d H:i:s'),
            'pgsql' => (new DateTime())->format('Y-m-d H:i:s'),
            'sqlsrv' => (new DateTime())->format('Y-m-d H:i:s'),
            default => throw new RuntimeException("Unsupported database driver: $driver"),
        };
    }

    /**
     * {@inheritDoc}
     * @see \Piko\DbRecord::beforeSave()
     */
    protected function beforeSave($isNew): bool
    {
        if ($isNew) {
            if (empty($this->name)) {
                $this->name = $this->username;
            }

            $this->password = sha1($this->password);
            $this->created_at = $this->getCurrentDatetime();
            $this->auth_key = sha1(Random::generate(10));

            if (empty($this->profil)) {
                $this->profil = '{}';
            }
        } else {
            $this->updated_at = $this->getCurrentDatetime();

            if ($this->resetPassword) {
                $this->password = sha1($this->password);
            }
        }

        return parent::beforeSave($isNew);
    }

    /**
     * {@inheritDoc}
     * @see \Piko\DbRecord::afterSave()
     */
    protected function afterSave(): void
    {
        if ($this->scenario === self::SCENARIO_ADMIN) {

            // Don't allow admin user to remove its admin role
            /*
            if ($this->id == Piko::get('user')->getId()) {

                $adminRole = Piko::get('userModule')->adminRole;
                $adminRoleId = Rbac::getRoleId($adminRole);

                if (!in_array($adminRoleId, $this->roleIds)) {
                    $this->roleIds[] = $adminRoleId;
                }
            }
            */

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
     * @see \Piko\DbRecord::bind()
     */
    public function bind($data): void
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
     * @see \Piko\ModeTrait::validate()
     */
    protected function validate(): void
    {
        if (empty($this->email)) {
            $this->errors['email'] = __('user', 'Email must be filled in.');
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = __(
                'user',
                '{email} is not a valid email address.',
                ['email' => $this->data['email']]
            );
        }

        if (
            ($this->scenario == self::SCENARIO_REGISTER || $this->scenario == self::SCENARIO_ADMIN)
            && empty($this->username)
        ) {
            $this->errors['username'] = __('user', 'Username must be filled in.') ;
        }

        // New user
        if (
            ($this->scenario == self::SCENARIO_REGISTER || $this->scenario == self::SCENARIO_ADMIN)
            && empty($this->id)
        ) {

            $st = $this->db->prepare('SELECT id FROM user WHERE email = ?');
            $st->execute([$this->email]);
            $id = $st->fetchColumn();

            if ($id) {
                $this->errors['email'] = __('user', 'This email is already used.');
            }

            $st = $this->db->prepare('SELECT id FROM user WHERE username = ?');
            $st->execute([$this->username]);
            $id = $st->fetchColumn();

            if ($id) {
                $this->errors['username'] = __('user', 'This username is already used.');
            }
        }

        if (
            ($this->scenario == self::SCENARIO_REGISTER || $this->scenario == self::SCENARIO_RESET)
            && empty($this->password)
        ) {
            $this->errors['password'] = __('user', 'Password must be filled in.');

        } elseif (
            ($this->scenario == self::SCENARIO_REGISTER || $this->scenario == self::SCENARIO_RESET) &&
                strlen($this->password) < $this->passwordMinLength
        ) {
            $this->errors['password'] =  __(
                'user',
                'Password is to short. Minimum {num}: characters.',
                ['num' => (string) $this->passwordMinLength]
            );
        }

        if (
            ($this->scenario == self::SCENARIO_REGISTER || $this->scenario == self::SCENARIO_RESET)  &&
            $this->password != $this->password2
        ) {
                $this->errors['password2'] = __('user', 'Passwords are not the same.');
        }
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
     * Save the login time
     *
     * @return bool
     */
    public function saveLoginTime(): bool
    {
        $this->last_login_at = $this->getCurrentDatetime();

        return $this->save();
    }

    /**
     * Activate an user
     *
     * @return bool
     */
    public function activate(): bool
    {
        $this->confirmed_at = $this->getCurrentDatetime();

        return $this->save();
    }

    /**
     * Check if the user is activated
     * @return bool
     */
    public function isActivated(): bool
    {
        return empty($this->confirmed_at) ? false : true;
    }

    /**
     * Send Registration confirmation email
     *
     * @param Mailer $mailer The Nette Mailer to use to send email
     * @param callable $getUrl A callback to get the route url
     *
     * @return bool Return false if fail to send email
     */
    public function sendRegistrationConfirmation(Mailer $mailer, callable $getUrl): bool
    {
        $siteName = getenv('SITE_NAME');
        $baseUrl = $this->getAbsoluteBaseUrl();

        $message = __('user', 'confirmation_mail_body', [
            'site_name' => $siteName,
            'link' => $baseUrl . $getUrl('user/default/confirmation', ['token' => $this->auth_key]),
            'base_url' => $baseUrl,
            'username' => $this->username,
        ]);

        $subject = __('user', 'Registration confirmation on {site_name}', ['site_name' => $siteName]);

        $mail = new Message();
        $mail->setFrom($siteName . ' <' . getenv('NO_REPLY_EMAIL') . '>')
             ->addTo($this->email)
             ->setSubject($subject)
             ->setBody($message);

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
     * @param Mailer $mailer The Nette Mailer to use to send email
     * @param callable $getUrl A callback to get the route url
     *
     * @return boolean Return false if fail to send email
     */
    public function sendResetPassword(Mailer $mailer, callable $getUrl): bool
    {
        $siteName = getenv('SITE_NAME');

        $baseUrl = $this->getAbsoluteBaseUrl();

        $message = __('user', 'reset_password_mail_body', [
            'site_name' => $siteName,
            'link' => $baseUrl . $getUrl('user/default/reset-password', ['token' => $this->auth_key]),
            'username' => $this->username,
        ]);

        $subject = __('user', 'Password change request on {site_name}', ['site_name' => $siteName]);

        $mail = new Message();
        $mail->setFrom(getenv('NO_REPLY_EMAIL'), $siteName)
             ->addTo($this->email)
             ->setSubject($subject)
             ->setBody($message);

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
    public static function find($filters = [], $order = '', $start = 0, $limit = 0): array
    {
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

        $sth = static::$pdo->prepare($query);

        $sth->execute($filters);

        return $sth->fetchAll();
    }


    /**
     * Find user by username
     *
     * @param string $username
     * @return User|NULL
     */
    public static function findByUsername($username): User
    {
        $st = static::$pdo->prepare('SELECT id FROM user WHERE username = ?');
        $st->bindParam(1, $username, \PDO::PARAM_STR);

        if ($st->execute()) {
            $id = $st->fetchColumn();

            if ($id) {
                $user = new static(static::$pdo);
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
    public static function findByEmail($email): ?User
    {

        $st = static::$pdo->prepare('SELECT id FROM user WHERE email = ?');
        $st->bindParam(1, $email, \PDO::PARAM_STR);

        if ($st->execute()) {
            $id = $st->fetchColumn();

            if ($id) {
                $user = new static(static::$pdo);

                return $user->load($id);
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
    public static function findByAuthKey($token): ?User
    {
        $st = static::$pdo->prepare('SELECT id FROM `user` WHERE `auth_key` = ?');

        if ($st->execute([$token])) {
            $id = $st->fetchColumn();

            if ($id) {
                $user = new static(static::$pdo);
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
     * @return bool
     */
    public function validatePassword($password): bool
    {
        return $this->password == sha1($password);
    }

    /**
     * Find user by Id
     *
     * @param int $id
     * @return User|null
     */
    public static function findIdentity($id): ?User
    {
        if (static::$pdo === null) {
            return null;
        }

        try {
            $user = new static(static::$pdo);

            return $user->load($id);
        } catch (\RuntimeException $e) {

        }

        return null;
    }

    /**
     * {@inheritDoc}
     * @see \Piko\IdentityInterface::getId()
     */
    public function getId(): int
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
