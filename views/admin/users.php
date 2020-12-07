<?php
use piko\Piko;
use piko\datatable\Datatable;

/* @var $this \piko\View */
/* @var $router \piko\Router */
/* @var $users array */

$router = Piko::get('router');

$this->title = Piko::t('user', 'Users management');

Datatable::init($this, 'users-table', [
    'order' => [[3, 'desc']],
    'language' => Piko::$app->language
]);

$confirmDeleteMsg = Piko::t('user', 'Are you sure you want to perform this action?');
$script = <<<JS
$(document).ready(function() {
    $('#delete').click(function(e) {
        if (confirm('{$confirmDeleteMsg}')) {
            $('#admin-form').attr('action', '/user/admin/delete')
            $('#admin-form').submit()
        }
    });
});
JS;
$this->registerJs($script);

?>

<?= $this->render('nav', ['page' => 'users']) ?>

<form action="" method="post" id="admin-form">

  <div class="btn-group mb-4" role="group">
    <a href="<?= $router->getUrl('user/admin/edit') ?>" class="btn btn-primary btn-sm"><?= Piko::t('user', 'Create user') ?></a>
    <button type="button" class="btn btn-danger btn-sm" id="delete"><?= Piko::t('user', 'Delete') ?></button>
  </div>

  <table id="users-table" class="table table-striped">
    <thead>
      <tr>
        <th><?= Piko::t('user', 'Name') ?></th>
        <th><?= Piko::t('user', 'Username') ?></th>
        <th><?= Piko::t('user', 'Email') ?></th>
        <th><?= Piko::t('user', 'Last login at') ?></th>
        <th><?= Piko::t('user', 'Created at') ?></th>
        <th><?= Piko::t('user', 'Id') ?></th>
      </tr>
    </thead>
    <tbody>
<?php foreach($users as $user): ?>
      <tr>
        <td>
          <input type="checkbox" name="items[]" value="<?= $user['id'] ?>">&nbsp;
          <a href="<?= $router->getUrl('user/admin/edit', ['id' => $user['id']])?>"><?= $user['name'] ?></a>
        </td>
        <td><?= $user['username']?></td>
        <td><?= $user['email']?></td>
        <td><?= empty($user['last_login_at']) ? '' : date('Y-m-d H:i', $user['last_login_at']) ?></td>
        <td><?= date('Y-m-d H:i', $user['created_at']) ?></td>
        <td><?= $user['id'] ?></td>
    </tr>
<?php endforeach ?>
	</tbody>
  </table>
</form>
