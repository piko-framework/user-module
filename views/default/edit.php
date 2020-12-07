<?php
use piko\Piko;

/* @var $this \piko\View */
/* @var $message array */
/* @var $user piko\user\models\User */

$this->title = Piko::t('user', 'Edit your account');

if (is_array($message)) {
    $this->params['message'] = $message;
}

if (!empty($user->profil)) {
    $user->profil = json_decode($user->profil);
}

?>

<div class="container mt-5">
<h1><?= $this->title ?></h1>

<form method="post" novalidate>

  <div class="form-group">
    <label for="username"><?= Piko::t('user', 'Username') ?> : <strong><?= $user->username ?></strong></label>
  </div>

  <div class="form-group">
    <label for="email"><?= Piko::t('user', 'Email') ?></label>
    <input type="text" class="form-control" id="email" name="email" value="<?= $user->email ?>">
    <?php if (!empty($user->errors['email'])): ?>
    <div class="alert alert-danger" role="alert"><?= $user->errors['email'] ?></div>
    <?php endif ?>
  </div>

  <div class="form-group">
    <label for="password"><?= Piko::t('user', 'Password (leave blank to keep the same)') ?></label>
    <input type="password" class="form-control" id="password" name="password" value="" autocomplete="off">
  </div>

  <div class="form-row">
      <div class="col-md-6 mb-3">
        <label for="lastname"><?= Piko::t('user', 'Last name') ?></label>
        <input type="text" class="form-control" id="lastname" name="profil[lastname]" value="<?= isset($user->profil->lastname) ? $user->profil->lastname : '' ?>">
      </div>

      <div class="col-md-6 mb-3">
        <label for="firstname"><?= Piko::t('user', 'First name') ?></label>
        <input type="text" class="form-control" id="firstname" name="profil[firstname]" value="<?= isset($user->profil->firstname) ? $user->profil->firstname : '' ?>">
      </div>
  </div>
  <div class="form-row">
      <div class="col-md-6 mb-3">
        <label for="company"><?= Piko::t('user', 'Company') ?></label>
        <input type="text" class="form-control" id="company" name="profil[company]" value="<?= isset($user->profil->company) ? $user->profil->company : ''?>">
      </div>

      <div class="col-md-6 mb-3">
        <label for="telephone"><?= Piko::t('user', 'Phone number') ?></label>
        <input type="text" class="form-control" id="telephone" name="profil[telephone]" value="<?= isset($user->profil->telephone) ? $user->profil->telephone : ''?>">
      </div>
  </div>

  <div class="form-group mb-3">
    <label for="address"><?= Piko::t('user', 'Address') ?></label>
    <input type="text" class="form-control" id="address" name="profil[address]" value="<?= isset($user->profil->address) ? $user->profil->address : ''?>">
  </div>

  <div class="form-row">
      <div class="col-md-4 mb-3">
        <label for="zipcode"><?= Piko::t('user', 'Zip code') ?></label>
        <input type="text" class="form-control" id="zipcode" name="profil[zipcode]" value="<?= isset($user->profil->zipcode) ? $user->profil->zipcode : ''?>">
      </div>

      <div class="col-md-4 mb-3">
        <label for="city"><?= Piko::t('user', 'City') ?></label>
        <input type="text" class="form-control" id="city" name="profil[city]" value="<?= isset($user->profil->city) ? $user->profil->city : ''?>">
      </div>

      <div class="col-md-4 mb-3">
        <label for="country"><?= Piko::t('user', 'Country') ?></label>
        <input type="text" class="form-control" id="country" name="profil[country]" value="<?= isset($user->profil->country) ? $user->profil->country : ''?>">
      </div>
  </div>

  <button type="submit" class="btn btn-primary"><?= Piko::t('user', 'Save') ?></button>
  <a href="<?= Piko::getAlias('@web/')?>" class="btn btn-default"><?= Piko::t('user', 'Cancel') ?></a>
</form>
</div>


