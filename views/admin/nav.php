<?php
use piko\Piko;

/* @var $this \piko\View */
/* @var $page string  */
/* @var $router \piko\Router */

$router = Piko::get('router');
?>
<ul class="nav nav-tabs my-3">
  <li class="nav-item">
    <a class="nav-link<?= $page == 'users' ? ' active' : '' ?>" href="<?= $router->getUrl('user/admin/users') ?>"><?= Piko::t('user', 'Users') ?></a>
  </li>
  <li class="nav-item">
    <a class="nav-link<?= $page == 'roles' ? ' active' : '' ?>" href="<?= $router->getUrl('user/admin/roles') ?>"><?= Piko::t('user', 'Roles') ?></a>
  </li>
  <li class="nav-item">
    <a class="nav-link<?= $page == 'permissions' ? ' active' : '' ?>" href="<?= $router->getUrl('user/admin/permissions') ?>"><?= Piko::t('user', 'Permissions') ?></a>
  </li>
</ul>