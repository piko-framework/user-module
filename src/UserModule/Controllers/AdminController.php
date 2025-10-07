<?php

/**
 * This file is part of the Piko user module
 *
 * @package Piko\UserModule
 * @copyright 2025 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/user-module
 */

namespace Piko\UserModule\Controllers;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Piko\UserModule;
use Piko\HttpException;
use Piko\User as PikoUser;
use Piko\UserModule\Models\Role;
use Piko\UserModule\Models\User;
use Piko\UserModule\Models\Permission;
use Piko\Controller\Event\BeforeActionEvent;

use function Piko\I18n\__;

/**
 * AdminController Class
 *
 * User administration controller
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class AdminController extends \Piko\Controller
{
    public function __construct(protected PikoUser $user, protected PDO $db)
    {
        $this->on(BeforeActionEvent::class, function () {
            assert($this->module instanceof UserModule);

            if (!$this->user->can($this->module->adminRole)) {
                throw new HttpException(403, 'Not authorized.');
            }
        });
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
    public function editAction(int $id = 0)
    {
        $user = new User($this->db);

        if ($id) {
            $user->load($id);
        }

        $user->scenario = User::SCENARIO_ADMIN;
        $message = [];

        $post = $this->request->getParsedBody();

        if (!empty($post)) {

            $user->bind($post);

            if ($user->isValid() && $user->save()) {
                $message['type'] = 'success';
                $message['content'] = __('user', 'User successfully saved');
            } else {
                $message['type'] = 'danger';
                $message['content'] = __('user', 'Save error!') . implode(' ', $user->errors);
            }
        }

        return $this->render('edit', [
            'user' => $user,
            'message' => $message,
            'roles' => Role::find($this->db, '`name` ASC'),
        ]);
    }

    /**
     * Delete users
     */
    public function deleteAction(): void
    {
        $post = $this->request->getParsedBody();
        $ids = isset($post['items']) ? $post['items'] : [];

        foreach ($ids as $id) {
            $user = new User($this->db);
            $user->load($id);
            $user->delete();
        }

        $this->redirect($this->getUrl('user/admin/users'));
    }

    /**
     * Render roles view
     *
     * @return string
     */
    public function rolesAction()
    {
        return $this->render('roles', [
            'roles' => Role::find($this->db),
            'permissions' => Permission::find($this->db, '`name` ASC'),
        ]);
    }

    /**
     * Create/update role  (AJAX)
     *
     * @return ResponseInterface
     */
    public function editRoleAction(int $id = 0): ResponseInterface
    {
        $role = new Role($this->db);

        if ($id) {
            $role->load($id);
        }

        $role->scenario = Role::SCENARIO_ADMIN;

        $post = json_decode((string) $this->request->getBody(), true);

        $response = [
            'role' => $role
        ];

        if (!empty($post)) {

            $role->bind($post);

            if ($role->isValid() && $role->save()) {
                $response['status'] = 'success';
            } else {
                $response['status'] = 'error';
                $response['error'] = array_pop($role->getErrors());
            }
        }

        return $this->jsonResponse($response);
    }


    /**
     * Delete roles
     */
    public function deleteRolesAction(): void
    {
        $post = $this->request->getParsedBody();
        $ids = isset($post['items']) ? $post['items'] : [];

        foreach ($ids as $id) {
            $item = new Role($this->db);
            $item->load($id);
            $item->delete();
        }

        $this->redirect($this->getUrl('user/admin/roles'));
    }

    /**
     * Render permissions view
     *
     * @return string
     */
    public function permissionsAction()
    {
        return $this->render('permissions', [
            'permissions' => Permission::find($this->db)
        ]);
    }

    /**
     * Create/update permission (AJAX)
     *
     * @return ResponseInterface
     */
    public function editPermissionAction(int $id = 0): ResponseInterface
    {
        $permission = new Permission($this->db);

        if ($id) {
            $permission->load($id);
        }

        $response = [
            'permission' => $permission
        ];

        $post = json_decode((string) $this->request->getBody(), true);

        if (!empty($post)) {

            $permission->bind($post);

            if ($permission->isValid() && $permission->save()) {
                $response['status'] = 'success';
            } else {
                $response['status'] = 'error';
                $response['error'] = array_pop($permission->getErrors());
            }
        }

        return $this->jsonResponse($response);
    }

    /**
     * Delete permissions
     */
    public function deletePermissionsAction(): void
    {
        $post = $this->request->getParsedBody();
        $ids = isset($post['items']) ? $post['items'] : [];

        foreach ($ids as $id) {
            $item = new Permission($this->db);
            $item->load($id);
            $item->delete();
        }

        $this->redirect($this->getUrl('user/admin/permissions'));
    }
}
