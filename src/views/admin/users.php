<?php
use function Piko\I18n\__;

assert($this instanceof Piko\View);

/* @var $users array */

$this->title = __('user', 'Users management');

$this->registerCSSFile(Piko::getAlias('@web/js/DataTables/datatables.min.css'));
$this->registerJsFile(Piko::getAlias('@web/js/jquery-3.7.1.min.js'));
$this->registerJsFile(Piko::getAlias('@web/js/DataTables/datatables.min.js'));

$confirmDeleteMsg = __('user', 'Are you sure you want to perform this action?');

$formatDate = function($date) {

  if (empty($date)) return '';

  if (is_numeric($date)) {
    return date('Y-m-d H:i', $date);
  }

  $date = new \DateTime($date);

  return $date->format('Y-m-d H:i');
};

$script = <<<JS
$(document).ready(function() {

  $('#users-table').DataTable({
    'order': [[3, 'desc']]
  });

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
    <a href="<?= $this->getUrl('user/admin/edit') ?>" class="btn btn-primary btn-sm"><?= __('user', 'Create user') ?></a>
    <button type="button" class="btn btn-danger btn-sm" id="delete"><?= __('user', 'Delete') ?></button>
  </div>

  <table id="users-table" class="table table-striped">
    <thead>
      <tr>
        <th><?= __('user', 'Name') ?></th>
        <th><?= __('user', 'Username') ?></th>
        <th><?= __('user', 'Email') ?></th>
        <th><?= __('user', 'Last login at') ?></th>
        <th><?= __('user', 'Created at') ?></th>
        <th><?= __('user', 'Id') ?></th>
      </tr>
    </thead>
    <tbody>
<?php foreach($users as $user): ?>
      <tr>
        <td>
          <input type="checkbox" name="items[]" value="<?= $user['id'] ?>">&nbsp;
          <a href="<?= $this->getUrl('user/admin/edit', ['id' => $user['id']])?>"><?= $user['name'] ?></a>
        </td>
        <td><?= $user['username']?></td>
        <td><?= $user['email']?></td>
        <td><?= $formatDate($user['last_login_at']) ?></td>
        <td><?= $formatDate($user['created_at']) ?></td>
        <td><?= $user['id'] ?></td>
    </tr>
<?php endforeach ?>
	</tbody>
  </table>
</form>
