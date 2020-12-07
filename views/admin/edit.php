<?php
use piko\Piko;

/* @var $this \piko\View */
/* @var $user \piko\user\models\User */
/* @var $message array */
/* @var $roles array */

$router = Piko::get('router');
$this->title = empty($user->id) ? Piko::t('user', 'Create user') : Piko::t('user', 'Edit user');
$roleIds = $user->getRoleIds();
?>
<div class="container">

<?php if (is_array($message)): ?>
<div class="alert alert-<?= $message['type'] ?> alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <?= $message['content'] ?>
</div>
<?php endif ?>

<form method="post">
  <div class="form-group">
    <label for="name"><?= Piko::t('user', 'Name') ?></label>
    <input type="text" class="form-control" id="name" name="name" value="<?= $user->name ?>">
  </div>

  <div class="form-group">
    <label for="email"><?= Piko::t('user', 'Email') ?></label>
    <input type="text" class="form-control" id="email" name="email" value="<?= $user->email ?>">
  </div>

  <div class="form-group">
    <label for="username"><?= Piko::t('user', 'Username') ?></label>
    <input type="text" class="form-control" id="username" name="username" value="<?= $user->username ?>">
  </div>

  <div class="form-group">
    <label for="password"><?= Piko::t('user', 'Password') ?></label>
    <input type="text" class="form-control" id="password" name="password" value="">
  </div>

  <div class="form-group">
    <label for="roles"><?= Piko::t('user', 'Roles') ?></label>
    <select class="custom-select" id="roles" name="roles[]" multiple>
    <?php foreach ($roles as $role): ?>
      <option value="<?= $role['id']?>"<?= in_array($role['id'], $roleIds)? ' selected' : '' ?>><?= $role['name']?></option>
    <?php endforeach ?>
    </select>
  </div>

  <button type="submit" class="btn btn-primary"><?= Piko::t('user', 'Save') ?></button>
  <a href="<?= $router->getUrl('user/admin/users')?>" class="btn btn-default"><?= Piko::t('user', 'Close') ?></a>
</form>
</div>