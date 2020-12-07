<?php
/**
 * This file is part of the Piko user module
 *
 * @copyright 2020 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko-user
 */
namespace piko\user\controllers;

use piko\Piko;
use piko\HttpException;
use piko\user\models\User;

/**
 * User default controller
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class DefaultController extends \piko\Controller
{
    /**
     * Render and process user registration
     *
     * @return string
     */
    public function registerAction()
    {
        /* @var $user \piko\User */
        $user = Piko::get('user');

        if (!$user->isGuest()) {
            Piko::$app->redirect('/');
        }

        $message = false;

        if (!empty($_POST)) {

            $user = new User();

            $user->scenario = User::SCENARIO_REGISTER;

            $user->bind($_POST);

            if ($user->validate() && $user->save()) {
                $user->sendRegistrationConfirmation();
                $message['type'] = 'success';
                $message['content'] = Piko::t(
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
     * @return string
     */
    public function checkRegistrationAction()
    {
        $errors = [];
        $this->layout = false;

        if (!empty($_POST)) {

            $user = new User();
            $user->scenario = 'register';
            $user->bind($_POST);
            $user->validate();
            $errors = $user->errors;
        }

        header('Content-type: application/json');

        return json_encode($errors);
    }

    /**
     * Render user activation confirmation
     *
     * @throws HttpException
     * @return string
     */
    public function confirmationAction()
    {
        $token = isset($_GET['token']) ? $_GET['token'] : '';
        $user = User::findByAuthKey($token);

        if (!$user) {
            throw new HttpException('Not found.', 404);
        }

        $message = false;

        if (!$user->isActivated()) {

            if ($user->activate()) {
                $message['type'] = 'success';
                $message['content'] = Piko::t('user', 'Your account has been activated. You can now log in.');
            } else {
                $message['type'] = 'danger';
                $message['content'] = Piko::t(
                    'user',
                    'Unable to activate your account. Please contact the site manager.'
                );
            }
        } else {
            $message['type'] = 'warning';
            $message['content'] = Piko::t('user', 'Your account has already been activated.');
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
        $reminder = isset($_POST['reminder']) ? $_POST['reminder'] : '';

        if (!empty($reminder)) {

            $user = User::findByUsername($reminder);

            if (!$user) {
                $user = User::findByEmail($reminder);
            }

            if ($user) {
                $user->sendResetPassword();
                $message['type'] = 'success';
                $message['content'] = Piko::t(
                    'user',
                    'A link has been sent to you by email ({email}). It will allow you to recreate your password.',
                    ['email' => $user->email]
                );
            } else {
                $message['type'] = 'danger';
                $message['content'] = Piko::t('user', 'Account not found.');
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
     * @throws HttpException
     * @return string
     */
    public function resetPasswordAction()
    {

        $token = isset($_GET['token']) ? $_GET['token'] : '';
        $user = User::findByAuthKey($token);

        if (!$user) {
            throw new HttpException('Not found', 404);
        }

        $message = false;

        if (!empty($_POST)) {
            $user->scenario = 'reset';

            $user->bind($_POST);

            if ($user->validate() && $user->save()) {
                $message['type'] = 'success';
                $message['content'] = Piko::t('user', 'Your password has been successfully updated.');
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
        /* @var $user \piko\User */
        $user = Piko::get('user');

        if (!$user->getId()) {
            throw new HttpException(Piko::t('user', 'You must be logged to access this page.'), 401);
        }

        /* @var $identity User */
        $identity = $user->getIdentity();

        $message = false;

        if (!empty($_POST)) {
            $identity->bind($_POST);

            if ($identity->validate() && $identity->save()) {
                $message['type'] = 'success';
                $message['content'] = Piko::t('user', 'Changes saved!');
            } else {
                $message['type'] = 'danger';
                $message['content'] = implode(', ', $user->errors);
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

        if (!empty($_POST)) {
            $identity = User::findByUsername($_POST['username']);

            if ($identity instanceof User && $identity->validatePassword($_POST['password'])) {

                $user = Piko::get('user');
                $user->login($identity);
                $identity->last_login_at = time();
                $identity->save();

                return Piko::$app->redirect('/');

            } else {
                $message['type'] = 'danger';
                $message['content'] = Piko::get('i18n')->translate('user', 'Authentication failure');
            }
        }

        return $this->render('login', [
            'message' => $message,
            'canRegister' => $this->module->allowUserRegistration
        ]);
    }

    /**
     * User logout
     */
    public function logoutAction()
    {
        $user = Piko::get('user');
        $user->logout();
        Piko::$app->redirect('/');
    }
}
