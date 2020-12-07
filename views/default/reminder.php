<?php
use piko\Piko;
/* @var $this \piko\View */
/* @var $message array */
/* @var $reminder string */

$this->title = Piko::t('user', 'Forget password');

if (is_array($message)) {
    $this->params['message'] = $message;
}

?>

<div class="container" style="margin-top: 100px">

<h1><?= $this->title ?></h1>

<form method="post" id="reminder-form" novalidate>
  <div class="form-group">
    <label for="reminder"><?= Piko::t('user', 'Your email or your username') ?></label>
    <input type="text" class="form-control" id="reminder" name="reminder" value="<?= $reminder ?>" autocomplete="off">
  </div>
  <button type="submit" class="btn btn-primary"><?= Piko::t('user', 'Send') ?></button>
</form>

</div>




