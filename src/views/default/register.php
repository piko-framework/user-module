<?php
use function Piko\I18n\__;

/**
 * @var \Piko\View $this
 * @var array $message
 */

$this->title = __('user', 'Register');

if (is_array($message)) {
    $this->params['message'] = $message;
    return;
}

$js = <<<SCRIPT
jQuery(document).ready(function($) {

    function validateField(e) {
        var that = this;

        $.post('{$this->getUrl('user/default/check-registration')}', $('#register-form').serialize(), function(errors) {
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

    $('#username').focusout(validateField);
    $('#email').focusout(validateField);
    $('#password').focusout(validateField);
    $('#password2').focusout(validateField);

});
SCRIPT;

$this->registerJs($js);


?>

<div class="container mt-5">

<h1><?= $this->title ?></h1>

<form method="post" id="register-form" novalidate>
  <div class="form-group">
    <label for="username"><?= __('user', 'Username') ?></label>
    <input type="text" class="form-control" id="username" name="username" value="">
    <div class="invalid-feedback"></div>
  </div>

  <div class="form-group">
    <label for="email"><?= __('user', 'Email') ?></label>
    <input type="text" class="form-control" id="email" name="email" value="">
    <div class="invalid-feedback"></div>
  </div>

  <div class="form-group">
    <label for="password"><?= __('user', 'Password') ?></label>
    <input type="password" class="form-control" id="password" name="password" value="" autocomplete="off">
    <div class="invalid-feedback"></div>
  </div>

  <div class="form-group">
    <label for="password2"><?= __('user', 'Confirm your password') ?></label>
    <input type="password" class="form-control" id="password2" name="password2" value="" autocomplete="off">
    <div class="invalid-feedback"></div>
  </div>

  <button type="submit" class="btn btn-primary"><?= __('user', 'Register') ?></button>
</form>

</div>
