<?php
/**
 * @var Piko\View $this
 * @var string $content
 */
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="<?= $this->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->escape($this->title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <?= $this->head() ?>
  </head>
  <body>
    <?= $content ?>
  </body>
</html>
