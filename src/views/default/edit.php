<?php
use function Piko\I18n\__;

assert($this instanceof Piko\View);

/**
 * @var array $message
 * @var \Piko\UserModule\Models\User $user
 */

$this->title = __('user', 'Edit your account');

if (is_array($message)) {
    $this->params['message'] = $message;
}

if (!empty($user->profil)) {
    $user->profil = json_decode($user->profil);
}

?>

<h1><?= $this->title ?></h1>

<form method="post" novalidate>

  <div class="mb-3">
    <label for="username" class="form-label"><?= __('user', 'Username') ?> : <strong><?= $user->username ?></strong></label>
  </div>

  <div class="mb-3">
    <label for="email" class="form-label"><?= __('user', 'Email') ?></label>
    <input type="text" class="form-control" id="email" name="email" value="<?= $user->email ?>">
    <?php if (!empty($user->errors['email'])): ?>
    <div class="alert alert-danger" role="alert"><?= $user->errors['email'] ?></div>
    <?php endif ?>
  </div>

  <div class="mb-3">
    <label for="password" class="form-label"><?= __('user', 'Password (leave blank to keep the same)') ?></label>
    <input type="password" class="form-control" id="password" name="password" value="" autocomplete="off">
  </div>

  <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="lastname" class="form-label"><?= __('user', 'Last name') ?></label>
        <input type="text" class="form-control" id="lastname" name="profil[lastname]" value="<?= isset($user->profil->lastname) ? $user->profil->lastname : '' ?>">
      </div>

      <div class="col-md-6">
        <label for="firstname" class="form-label"><?= __('user', 'First name') ?></label>
        <input type="text" class="form-control" id="firstname" name="profil[firstname]" value="<?= isset($user->profil->firstname) ? $user->profil->firstname : '' ?>">
      </div>
  </div>

  <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="company" class="form-label"><?= __('user', 'Company') ?></label>
        <input type="text" class="form-control" id="company" name="profil[company]" value="<?= isset($user->profil->company) ? $user->profil->company : ''?>">
      </div>

      <div class="col-md-6">
        <label for="telephone" class="form-label"><?= __('user', 'Phone number') ?></label>
        <input type="text" class="form-control" id="telephone" name="profil[telephone]" value="<?= isset($user->profil->telephone) ? $user->profil->telephone : ''?>">
      </div>
  </div>

  <div class="mb-3">
    <label for="address" class="form-label"><?= __('user', 'Address') ?></label>
    <input type="text" class="form-control" id="address" name="profil[address]" value="<?= isset($user->profil->address) ? $user->profil->address : ''?>">
  </div>

  <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label for="zipcode" class="form-label"><?= __('user', 'Zip code') ?></label>
        <input type="text" class="form-control" id="zipcode" name="profil[zipcode]" value="<?= isset($user->profil->zipcode) ? $user->profil->zipcode : ''?>">
      </div>

      <div class="col-md-4">
        <label for="city" class="form-label"><?= __('user', 'City') ?></label>
        <input type="text" class="form-control" id="city" name="profil[city]" value="<?= isset($user->profil->city) ? $user->profil->city : ''?>">
      </div>

      <div class="col-md-4">
        <label for="country" class="form-label"><?= __('user', 'Country') ?></label>
        <input type="text" class="form-control" id="country" name="profil[country]" value="<?= isset($user->profil->country) ? $user->profil->country : ''?>">
      </div>
  </div>

  <button type="submit" class="btn btn-primary"><?= __('user', 'Save') ?></button>
  <a href="<?= Piko::getAlias('@web/')?>" class="btn btn-default"><?= __('user', 'Cancel') ?></a>
</form>

