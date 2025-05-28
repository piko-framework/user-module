<?php
use function Piko\I18n\__;

assert($this instanceof Piko\View);
assert($user instanceof Piko\UserModule\Models\User);

/* @var $message array */
/* @var $roles array */

$this->title = empty($user->id) ? __('user', 'Create user') : __('user', 'Edit user');
$roleIds = $user->getRoleIds();
?>
<div class="container">

<?php if (!empty($message)): ?>
<div class="alert alert-<?= $message['type'] ?> alert-dismissible" role="alert">
  <?= $message['content'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif ?>

<form method="post">
  <div class="mb-3">
    <label for="name"><?= __('user', 'Name') ?></label>
    <input type="text" class="form-control" id="name" name="name" value="<?= $user->name ?>">
  </div>

  <div class="mb-3">
    <label for="email"><?= __('user', 'Email') ?></label>
    <input type="text" class="form-control" id="email" name="email" value="<?= $user->email ?>">
  </div>

  <div class="mb-3">
    <label for="username"><?= __('user', 'Username') ?></label>
    <input type="text" class="form-control" id="username" name="username" value="<?= $user->username ?>">
  </div>

  <div class="mb-3">
    <label for="password"><?= __('user', 'Password') ?></label>
    <input type="password" class="form-control" id="password" name="password" value="">
  </div>

  <div class="mb-3">
    <label for="roles"><?= __('user', 'Roles') ?></label>
    <select class="form-select" id="roles" name="roles[]" multiple>
    <?php foreach ($roles as $role): ?>
      <option value="<?= $role['id']?>"<?= in_array($role['id'], $roleIds)? ' selected' : '' ?>><?= $role['name']?></option>
    <?php endforeach ?>
    </select>
  </div>

  <div class="mb-3">
    <button type="submit" class="btn btn-primary"><?= __('user', 'Save') ?></button>
    <a href="<?= $this->getUrl('user/admin/users')?>" class="btn btn-default"><?= __('user', 'Close') ?></a>
  </div>

</form>
</div>
