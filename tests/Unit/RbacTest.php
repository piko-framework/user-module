<?php
namespace Tests\Unit;

use Tests\Support\UnitTester;
use Codeception\Attribute\Env;

use PDO;
use Piko\UserModule\Rbac;

class RbacTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    protected PDO $db;

    protected function _before(): void
    {
        /** @var \Codeception\Module\Db $dbModule */
        $dbModule = $this->getModule('Db');
        $this->db = $dbModule->_getDbh();

        Rbac::setPDO($this->db);
    }

    #[Env('sqlite', 'mysql')]
    public function testCreateRole(): void
    {
        $this->assertFalse(Rbac::roleExists('guest'));

        $roleId = Rbac::createRole('guest', 'Guest role');
        $this->assertEquals($roleId, Rbac::getRoleId('guest'));

        $this->assertTrue(Rbac::roleExists('guest'));
    }

    #[Env('sqlite', 'mysql')]
    public function testCreatePermission(): void
    {
        $this->assertFalse(Rbac::permissionExists('can.edit'));

        $id = Rbac::createPermission('can.edit');
        $this->assertEquals($id, Rbac::getPermissionId('can.edit'));

        $this->assertTrue(Rbac::permissionExists('can.edit'));
    }

    #[Env('sqlite', 'mysql')]
    public function testAssignPermission(): void
    {
        Rbac::createRole('author', 'Author role');
        Rbac::createPermission('can.edit');
        Rbac::createPermission('can.delete');

        Rbac::assignPermission('author', 'can.edit');
        Rbac::assignPermission('author', 'can.delete');

        $perms = Rbac::getRolePermissions('author');
        $this->assertContains('can.edit', $perms);
        $this->assertContains('can.delete', $perms);
    }

    #[Env('sqlite', 'mysql')]
    public function testAssignRole(): void
    {
        $userAdminId = 1;
        Rbac::createRole('author', 'Author role');
        Rbac::assignRole($userAdminId, 'author');
        $roles = Rbac::getUserRoles($userAdminId);
        $this->assertContains('admin', $roles);
        $this->assertContains('author', $roles);
    }

    #[Env('sqlite', 'mysql')]
    public function testGetUserPermissions(): void
    {
        $userAdminId = 1;
        Rbac::createRole('director', 'Director role');
        Rbac::assignRole($userAdminId, 'director');
        Rbac::createPermission('can.edit');
        Rbac::createPermission('document.can.delete');
        Rbac::assignPermission('director', 'can.edit');
        Rbac::assignPermission('director', 'document.can.delete');

        $perms = Rbac::getUserPermissions($userAdminId);
        $this->assertContains('can.edit', $perms);
        $this->assertContains('document.can.delete', $perms);
    }
}
