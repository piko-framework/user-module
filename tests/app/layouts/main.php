<?php
/**
 * @var Piko\View $this
 * @var string $content
 * @var Piko\User $user
 */

$user = $this->params['user'];
?>
<!DOCTYPE html>
<html data-bs-theme="dark">
  <head>
    <meta charset="<?= $this->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->escape($this->title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <?= $this->head() ?>
  </head>
  <body>
    <nav id="mainnav" class="navbar navbar-expand-lg navbar-dark bg-primary py-0">
      <div class="container">
        <a class="navbar-brand" href="<?= Piko::getAlias('@web/') ?>">Piko user module</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainmenu" aria-controls="mainmenu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <?php if (!$user->isGuest()): ?>
        <div id="mainmenu" class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="<?= $this->getUrl('user/default/edit') ?>">Edit profile</a></li>
            <?php if ($user->can('admin')): ?>
            <li class="nav-item"><a class="nav-link" href="<?= $this->getUrl('user/admin/users') ?>">Manage users</a></li>
            <?php endif ?>
            <li class="nav-item"><a class="nav-link" href="<?= $this->getUrl('user/default/logout') ?>">Logout (<?= $user->getIdentity()->username ?>)</a></li>
          </ul>
        </div>
        <?php endif ?>

      </div>
    </nav>

    <div role="main" class="wrap">
      <div class="container">
      <?= $content ?>
      </div>
    </div>

    <?= $this->endBody() ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
  </body>
</html>
