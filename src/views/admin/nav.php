<?php
use function Piko\I18n\__;
/**
 * @var \Piko\View $this
 * @var string $page
 */

?>
<ul class="nav nav-tabs my-3">
  <li class="nav-item">
    <a class="nav-link<?= $page == 'users' ? ' active' : '' ?>" href="<?= $this->getUrl('user/admin/users') ?>"><?= __('user', 'Users') ?></a>
  </li>
  <li class="nav-item">
    <a class="nav-link<?= $page == 'roles' ? ' active' : '' ?>" href="<?= $this->getUrl('user/admin/roles') ?>"><?= __('user', 'Roles') ?></a>
  </li>
  <li class="nav-item">
    <a class="nav-link<?= $page == 'permissions' ? ' active' : '' ?>" href="<?= $this->getUrl('user/admin/permissions') ?>"><?= __('user', 'Permissions') ?></a>
  </li>
</ul>
