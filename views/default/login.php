<?php
use piko\Piko;

/**
 * @var $this piko\View
 * @var $router \piko\Router
 * @var $message boolean | array
 * @var $canRegister boolean
 */

$this->title = Piko::t('user', 'Login');
$this->params['breadcrumbs'][] = $this->title;

if (is_array($message)) {
    $this->params['message'] = $message;
}

$router = Piko::get('router');
?>

<div class="container site-login">
  <h1><?= $this->title ?></h1>

  <div class="row">

  <div class="col-md-6">
    <form action="<?= $router->getUrl('user/default/login') ?>" id="login-form" method="post">
        <div class="form-group row">
          <label class="col-sm-3 col-form-label" for="loginform-username"><?= Piko::t('user', 'Username') ?></label>
          <div class="col-sm-9">
            <input type="text" id="username" class="form-control" name="username" autofocus="autofocus" aria-required="true"
              aria-invalid="true">
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-3 col-form-label" for="loginform-password"><?= Piko::t('user', 'Password') ?></label>
          <div class="col-sm-9">
            <input type="password" id="loginform-password" class="form-control" name="password" value="" aria-required="true">
          </div>
        </div>

        <div class="form-group">

          <div class="offset-sm-3 col-sm-9">
            <button type="submit" class="btn btn-primary" name="login-button"><?= Piko::t('user', 'Login') ?></button>
          </div>
        </div>
    </form>
  </div>
  <?php if ($canRegister): ?>
  <div class="col-md-6">
    <div class="p-3 border bg-light text-dark">
      <p><?= Piko::t('user', 'No account yet?') ?></p>
      <p><a href="<?= $router->getUrl('user/default/register')?>" class="btn btn-primary"><?= Piko::t('user', 'Register') ?></a></p>
      <hr>
      <p><a href="<?= $router->getUrl('user/default/reminder')?>"><?= Piko::t('user', 'Forget password?') ?></a></p>
    </div>
  </div>
  <?php endif ?>

  </div>
</div>
