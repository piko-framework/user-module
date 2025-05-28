<?php
use function Piko\I18n\__;

assert($this instanceof Piko\View);

/**
 * @var boolean|array $message
 * @var boolean $canRegister
 */

$this->title = __('user', 'Login');
$this->params['breadcrumbs'][] = $this->title;

$css = <<<CSS
.form-signin {
  max-width: 330px;
  padding: 1rem;
}

.form-signin .form-floating:focus-within {
  z-index: 2;
}

.form-signin input[type="text"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}

.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
CSS;

$this->registerCSS($css);
?>

<main class="form-signin w-100 m-auto">

  <?php if (!empty($message)): ?>
  <div class="container alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <?= $message['content'] ?>
  </div>
  <?php endif ?>

  <form action="<?= $this->getUrl('user/default/login') ?>" id="login-form" method="post">
    <h1 class="h3 mb-3 fw-normal"><?= $this->title ?></h1>

    <div class="form-floating">
      <input type="text"
             class="form-control"
             id="username"
             name="username"
             placeholder="<?= __('user', 'Username') ?>"
             autofocus
             aria-required="true">
      <label for="username"><?= __('user', 'Username') ?></label>
    </div>

    <div class="form-floating">
      <input type="password" class="form-control" id="loginform-password"  name="password" placeholder="<?= __('user', 'Password') ?>">
      <label for="loginform-password"><?= __('user', 'Password') ?></label>
    </div>

    <button class="btn btn-primary w-100 py-2" type="submit"><?= __('user', 'Login') ?></button>
    <p class="mt-5 mb-3 text-body-secondary">
      <a href="<?= $this->getUrl('user/default/reminder')?>"><?= __('user', 'Forget password?') ?></a>
    </p>
  </form>

  <?php if ($canRegister): ?>
    <div class="p-3 border bg-light text-dark">
      <p><?= __('user', 'No account yet?') ?></p>
      <p><a href="<?= $this->getUrl('user/default/register')?>" class="btn btn-primary"><?= __('user', 'Register') ?></a></p>
    </div>
  <?php endif ?>
  </main>



