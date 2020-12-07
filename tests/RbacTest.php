<?php
use PHPUnit\Framework\TestCase;
use piko\Db;
use piko\Piko;

use piko\user\Module;
use piko\user\models\User;
use piko\user\Rbac;

class RbacTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $db = new Db(['dsn' => 'sqlite::memory:']);
        Piko::set('db', $db);

        Module::install();

        $user = new User();
        $user->bind([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'password' => 'test'
        ]);

        $user->save();
    }

    public function testCreateRole()
    {
        $this->assertFalse(Rbac::roleExists('admin'));

        $roleId = Rbac::createRole('admin', 'Admin role');
        $this->assertEquals($roleId, Rbac::getRoleId('admin'));

        $this->assertTrue(Rbac::roleExists('admin'));
    }

    public function testCreatePermission()
    {
        $this->assertFalse(Rbac::permissionExists('can.edit'));

        $roleId = Rbac::createPermission('can.edit');
        $this->assertEquals($roleId, Rbac::getPermissionId('can.edit'));

        $this->assertTrue(Rbac::permissionExists('can.edit'));
    }

    public function testAssignPermission()
    {
        Rbac::createRole('author', 'Author role');
        Rbac::createPermission('can.delete');

        Rbac::assignPermission('author', 'can.edit');
        Rbac::assignPermission('author', 'can.delete');

        $perms = Rbac::getRolePermissions('author');

        $this->assertArraySubset(['can.edit', 'can.delete'], $perms);
    }

    public function testAssignRole()
    {
        Rbac::assignRole(1, 'admin');
        Rbac::assignRole(1, 'author');

        $roles = Rbac::getUserRoles(1);
        $this->assertArraySubset(['admin', 'author'], $roles);
    }

    public function testGetUserPermissions()
    {
        Rbac::createRole('director', 'Director role');
        Rbac::assignRole(1, 'director');
        Rbac::createPermission('document.can.delete');
        Rbac::assignPermission('director', 'document.can.delete');
        Rbac::assignRole(1, 'director');

        $perms = Rbac::getUserPermissions(1);
        $this->assertArraySubset(['can.edit', 'can.delete', 'document.can.delete'], $perms);
    }

}
