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
use piko\user\models\Permission;
use piko\user\models\Role;

/**
 * User admin controller
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class AdminController extends \piko\Controller
{
    /**
     * {@inheritDoc}
     * @see \piko\Controller::runAction()
     */
    public function runAction($id)
    {
        if (!Piko::get('user')->can($this->module->adminRole)) {
            throw new HttpException('Not authorized.', 403);
        }

        return parent::runAction($id);
    }

    /**
     * Render users view
     *
     * @return string
     */
    public function usersAction()
    {
        return $this->render('users', [
            'users' => User::find()
        ]);
    }

    /**
     * Render User form and create or update user
     *
     * @return string
     */
    public function editAction()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $user = new User($id);
        $user->scenario = User::SCENARIO_ADMIN;
        $message = false;

        if (!empty($_POST)) {

            $user->bind($_POST);

            if ($user->validate() && $user->save()) {
                $message['type'] = 'success';
                $message['content'] = Piko::t('user', 'User successfully saved');
            } else {
                $message['type'] = 'danger';
                $message['content'] = Piko::t('user', 'Save error!') . implode(' ', $user->errors);
            }
        }

        return $this->render('edit', [
            'user' => $user,
            'message' => $message,
            'roles' => Role::find('`name` ASC'),
        ]);
    }

    /**
     * Delete users
     */
    public function deleteAction()
    {
        $ids = isset($_POST['items'])? $_POST['items'] : [];

        foreach ($ids as $id) {
            $user = new User($id);
            $user->delete();
        }

        $router = Piko::get('router');
        Piko::$app->redirect($router->getUrl('user/admin/users'));
    }

    /**
     * Render roles view
     *
     * @return string
     */
    public function rolesAction()
    {
        return $this->render('roles', [
            'roles' => Role::find(),
            'permissions' => Permission::find('`name` ASC'),
        ]);
    }

    /**
     * Create/update role  (AJAX)
     *
     * @return string
     */
    public function editRoleAction()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $role = new Role($id);
        $role->scenario = Role::SCENARIO_ADMIN;

        $this->layout = false;

        $response = [
            'role' => $role
        ];

        if (!empty($_POST)) {

            $role->bind($_POST);

            if ($role->validate() && $role->save()) {
                $response['status'] = 'success';
            } else {
                $response['status'] = 'error';
            }
        }

        header('content-type:application/json');

        return json_encode($response);
    }


    /**
     * Delete roles
     */
    public function deleteRolesAction()
    {
        $ids = isset($_POST['items'])? $_POST['items'] : [];

        foreach ($ids as $id) {
            $item = new Role($id);
            $item->delete();
        }

        $router = Piko::get('router');
        Piko::$app->redirect($router->getUrl('user/admin/roles'));
    }

    /**
     * Render permissions view
     *
     * @return string
     */
    public function permissionsAction()
    {
        return $this->render('permissions', [
            'permissions' => Permission::find()
        ]);
    }

    /**
     * Create/update permission (AJAX)
     *
     * @return string
     */
    public function editPermissionAction()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $permission = new Permission($id);

        $this->layout = false;
        $response = [
            'permission' => $permission
        ];

        if (!empty($_POST)) {

            $permission->bind($_POST);

            if ($permission->validate() && $permission->save()) {
                $response['status'] = 'success';
            } else {
                $response['status'] = 'error';
                $response['error'] = array_pop($permission->errors);
            }
        }

        header('content-type: application/json');

        return json_encode($response);
    }

    /**
     * Delete permissions
     */
    public function deletePermissionsAction()
    {
        $ids = isset($_POST['items'])? $_POST['items'] : [];

        foreach ($ids as $id) {
            $item = new Permission($id);
            $item->delete();
        }

        $router = Piko::get('router');

        Piko::$app->redirect($router->getUrl('user/admin/permissions'));
    }
}
