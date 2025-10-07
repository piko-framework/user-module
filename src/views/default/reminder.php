<?php
use function Piko\I18n\__;

/**
 * @var \Piko\View $this
 * @var array $message
 * @var string $reminder
 */

$this->title = __('user', 'Forget password');

$css = <<<CSS
.form-reminder {
  max-width: 330px;
  padding: 1rem;
}

.form-reminder .form-floating:focus-within {
  z-index: 2;
}
CSS;

$this->registerCSS($css);
?>

<main class="form-reminder w-100 m-auto">

  <h1 class="h3"><?= $this->title ?></h1>

  <?php if (!empty($message)): ?>
  <div class="container alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <?= $message['content'] ?>
  </div>
  <?php endif ?>

  <form method="post" id="reminder-form" novalidate>
    <div class="form-floating">
      <input type="text"
            class="form-control"
            id="reminder"
            name="reminder"
            value="<?= $reminder ?>"
            autocomplete="off"
            placeholder="<?= __('user', 'Your email or your username') ?>">
      <label for="reminder"><?= __('user', 'Your email or your username') ?></label>
    </div>
  <p class="my-2">
      <button type="submit" class="btn btn-primary"><?= __('user', 'Send') ?></button>
  </p>
</form>

</main>
