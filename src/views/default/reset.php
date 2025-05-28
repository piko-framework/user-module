<?php
use function Piko\I18n\__;

assert($this instanceof Piko\View);

/* @var $user piko\user\models\User */
/* @var $message array */

$this->title = __('user', 'Change your account ({account}) password',['account' => $user->username]);

$js = <<<SCRIPT
document.addEventListener('DOMContentLoaded', function() {
    function validateField(event) {
        var field = event.target;

        fetch('{$this->getUrl('user/default/check-registration')}', {
            method: 'POST',
            body: new URLSearchParams(new FormData(document.getElementById('register-form')))
        })
        .then(response => response.json())
        .then(errors => {
            if (errors[field.name]) {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                field.nextElementSibling.textContent = errors[field.name];
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        });
    }

    document.getElementById('password').addEventListener('focusout', validateField);
    document.getElementById('password2').addEventListener('focusout', validateField);
});
SCRIPT;

$this->registerJs($js);
?>

<div class="container">

<h1 class="h4"><?= $this->title ?></h1>

<?php if (!empty($message) && $message['type'] === 'success'): ?>
<div class="alert alert-success" role="alert">
  <?= $message['content'] ?>
  <p class="text-center"><a class="btn btn-primary" href="<?= $this->getUrl('user/default/login') ?>">
    <?= __('user', 'Login') ?></a>
  </p>
</div>

<?php else: ?>

<form method="post" id="register-form" novalidate>

  <div class="mb-2">
    <label for="password"><?= __('user', 'Password') ?></label>
    <input type="password" class="form-control" id="password" name="password" value="" autocomplete="off">
    <div class="invalid-feedback"></div>
  </div>

  <div class="mb-2">
    <label for="password2"><?= __('user', 'Confirm your password') ?></label>
    <input type="password" class="form-control" id="password2" name="password2" value="" autocomplete="off">
    <div class="invalid-feedback"></div>
  </div>

  <button type="submit" class="btn btn-primary"><?= __('user', 'Send') ?></button>
</form>

<?php endif ?>

</div>



