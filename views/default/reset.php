<?php
use piko\Piko;

/* @var $this \piko\View */
/* @var $user piko\user\models\User */
/* @var $message array */
/* @var $router \piko\Router */

$router = Piko::get('router');

$this->title = Piko::t('user', 'Change your account ({account}) password',['account' => $user->username]);

if (is_array($message)) {
    $this->params['message'] = $message;

    echo '<div class="container text-center"><a class="btn btn-primary" href="'. $router->getUrl('user/default/login').'">'
         . Piko::t('user', 'Login') . '</a></div>';

    return;
}

$js = <<<SCRIPT
jQuery(document).ready(function($) {

    function validateField(e) {
        var that = this;

        $.post('{$router->getUrl('user/default/check-registration')}', $('#register-form').serialize(), function(errors) {
            if (errors[that.name]) {
                $(that).addClass('is-invalid')
                 $(that).removeClass('is-valid')
                $(that).next('.invalid-feedback').text(errors[that.name])
            } else {
                $(that).removeClass('is-invalid')
                $(that).addClass('is-valid')
            }
        });
    }

    $('#password').focusout(validateField);
    $('#password2').focusout(validateField);

});
SCRIPT;

$this->registerJs($js);


?>

<div class="container" style="margin-top: 100px">

<h1 class="h4"><?= $this->title ?></h1>

<form method="post" id="register-form" novalidate>

  <div class="form-group">
    <label for="password"><?= Piko::t('user', 'Password') ?></label>
    <input type="password" class="form-control" id="password" name="password" value="" autocomplete="off">
    <div class="invalid-feedback"></div>
  </div>

  <div class="form-group">
    <label for="password2"><?= Piko::t('user', 'Confirm your password') ?></label>
    <input type="password" class="form-control" id="password2" name="password2" value="" autocomplete="off">
    <div class="invalid-feedback"></div>
  </div>

  <button type="submit" class="btn btn-primary"><?= Piko::t('user', 'Send') ?></button>
</form>

</div>



