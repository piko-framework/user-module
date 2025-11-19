<?php

/**
 * This file is part of the Piko user module
 *
 * @package   Piko\UserModule
 * @copyright 2025 Sylvain PHILIP.
 * @license   LGPL-3.0; see LICENSE.txt
 * @link      https://github.com/piko-framework/user-module
 */

namespace Piko\UserModule\Controllers;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Piko\UserModule;
use Piko\HttpException;
use Piko\User as PikoUser;
use Piko\UserModule\Models\User;
use Nette\Mail\Mailer;

use function Piko\I18n\__;

/**
 * DefaultController Class
 *
 * Default user controller
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class DefaultController extends \Piko\Controller
{
    public function __construct(protected PikoUser $user, protected PDO $db, protected Mailer $mailer)
    {
    }

    /**
     * Render and process user registration
     *
     * @return string
     */
    public function registerAction()
    {
        if (!$this->user->isGuest()) {
            return $this->redirect('/');
        }

        $message = false;
        $post = $this->request->getParsedBody();

        if (!empty($post)) {

            $module = $this->module;
            assert($module instanceof UserModule);

            $user = new User($this->db);
            $user->scenario = User::SCENARIO_REGISTER;
            $user->passwordMinLength = $module->passwordMinLength;

            $user->bind($post);

            if ($user->isValid() && $user->save()) {
                $user->sendRegistrationConfirmation($this->mailer, [$this, 'getUrl']);
                $message['type'] = 'success';
                $message['content'] = __(
                    'user',
                    'Your account was created. Please activate it through the confirmation email that was sent to you.'
                );
            } else {
                $message['type'] = 'danger';
                $message['content'] = implode(', ', $user->errors);
            }
        }

        return $this->render('register', [
            'message' => $message,
        ]);
    }

    /**
     * Validate registration (AJAX)
     *
     * @return ResponseInterface
     */
    public function checkRegistrationAction(): ResponseInterface
    {
        $errors = [];
        $this->layout = false;
        $post = $this->request->getParsedBody();

        if (!empty($post)) {

            $module = $this->module;
            assert($module instanceof UserModule);

            $user = new User($this->db);
            $user->scenario = 'register';
            $user->passwordMinLength = $module->passwordMinLength;
            $user->bind($post);
            $user->isValid();
            $errors = $user->getErrors();
        }

        return $this->jsonResponse($errors);
    }

    /**
     * Render user activation confirmation
     *
     * @param string $token The auth token
     *
     * @throws HttpException
     * @return string
     */
    public function confirmationAction($token): string
    {
        $user = User::findByAuthKey($token);

        if (!$user) {
            throw new HttpException('Not found.', 404);
        }

        $message = false;

        if (!$user->isActivated()) {

            if ($user->activate()) {
                $message['type'] = 'success';
                $message['content'] = __('user', 'Your account has been activated. You can now log in.');
            } else {
                $message['type'] = 'danger';
                $message['content'] = __(
                    'user',
                    'Unable to activate your account. Please contact the site manager.'
                );
            }
        } else {
            $message['type'] = 'warning';
            $message['content'] = __('user', 'Your account has already been activated.');
        }

        return $this->render('login', ['message' => $message]);
    }

    /**
     * Render reminder password form and send email to change password
     *
     * @return string
     */
    public function reminderAction()
    {
        $message = false;
        $post = $this->request->getParsedBody();

        $reminder = $post['reminder'] ?? '';

        if (!empty($reminder)) {

            $user = User::findByUsername($reminder);

            if (!$user) {
                $user = User::findByEmail($reminder);
            }

            if ($user) {
                $user->sendResetPassword($this->mailer, [$this, 'getUrl']);
                $message['type'] = 'success';
                $message['content'] = __(
                    'user',
                    'A link has been sent to you by email ({email}). It will allow you to recreate your password.',
                    ['email' => $user->email]
                );
                $reminder = '';
            } else {
                $message['type'] = 'danger';
                $message['content'] = __('user', 'Account not found.');
            }
        }

        return $this->render('reminder', [
            'message' => $message,
            'reminder' => $reminder,
        ]);
    }

    /**
     * Render and process reset password
     *
     * @param string $token The auth token
     *
     * @throws HttpException
     * @return string
     */
    public function resetPasswordAction($token)
    {
        $user = User::findByAuthKey($token);

        if (!$user) {
            throw new HttpException('User not found', 404);
        }

        $message = false;
        $post = $this->request->getParsedBody();

        if (!empty($post)) {
            $user->scenario = 'reset';

            $user->bind($post);

            if ($user->isValid() && $user->save()) {
                $message['type'] = 'success';
                $message['content'] = __('user', 'Your password has been successfully updated.');
            } else {
                $message['type'] = 'danger';
                $message['content'] = implode(', ', $user->errors);
            }
        }

        return $this->render('reset', [
            'message' => $message,
            'user' => $user,
        ]);
    }

    /**
     * Render user form and update changes
     *
     * @throws HttpException
     * @return string
     */
    public function editAction()
    {
        if ($this->user->isGuest()) {
            throw new HttpException(__('user', 'You must be logged to access this page.'), 401);
        }

        $identity = $this->user->getIdentity();

        assert($identity instanceof User);

        $message = false;
        $post = $this->request->getParsedBody();

        if (!empty($post)) {
            $identity->bind($post);

            if ($identity->isValid() && $identity->save()) {
                $message['type'] = 'success';
                $message['content'] = __('user', 'Changes saved!');
            } else {
                $message['type'] = 'danger';
                $message['content'] = implode(', ', $identity->getErrors());
            }
        }

        return $this->render('edit', [
            'user' => $identity,
            'message' => $message,
        ]);
    }

    /**
     * Render login form and process login
     *
     * @return string
     */
    public function loginAction()
    {
        $message = false;
        $post = $this->request->getParsedBody();

        if (!empty($post)) {
            $identity = User::findByUsername($post['username']);

            if ($identity instanceof User && $identity->validatePassword($post['password'])) {

                $this->user->login($identity);
                $identity->saveLoginTime();

                return $this->redirect('/');

            } else {
                $message['type'] = 'danger';
                $message['content'] = __('user', 'Authentication failure');
            }
        }

        assert($this->module instanceof UserModule);

        return $this->render('login', [
            'message' => $message,
            'canRegister' => $this->module->allowUserRegistration
        ]);
    }

    /**
     * User logout
     *
     * @return ResponseInterface
     */
    public function logoutAction(): ResponseInterface
    {
        $this->user->logout();

        return $this->redirect('/');
    }
}
