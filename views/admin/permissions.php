<?php
use piko\Piko;
use piko\datatable\Datatable;

/* @var $this \piko\View */
/* @var $router \piko\Router */
/* @var $permissions array */

$router = Piko::get('router');

$this->title = Piko::t('user', 'Permissions');

Datatable::init($this, 'permissions-table', [
    'order' => [[1, 'desc']],
    'language' => Piko::$app->language
]);

$script = <<<JS
$(function() {

  $('#delete').click(function(e) {
    if (confirm('Êtes-vous sûr de vouloir effectuer cette action ?')) {
        $('#admin-form').attr('action', '/user/admin/delete-permissions')
        $('#admin-form').submit()
    }
  });

  $('#btn-new-permission, .edit-permission').on('click', function(e) {
    e.preventDefault();

    var permissionName = '';
    var permissionId = $(this).data('id');
    var action = $(this).attr('href')

    if ($(this).hasClass('edit-permission')) {
        permissionName = $(this).text();
    }

    $('#permission-name').val(permissionName);
    $('#editPermissionModal').modal('show');


    $('#btn-save-permission').on('click', function() {
        if ($('#permission-name').val()) {
            $.ajax({
                method: 'post',
                url: action,
                data: {name: $('#permission-name').val(), id: permissionId}
            })
            .done(function(data) {
               if (data.status == 'success') {
                    location.reload();
               }
               if (data.status == 'error') {
                   alert(data.error)
               }
            });
        }
    });
  });
});
JS;
$this->registerJs($script);

?>

<?= $this->render('nav', ['page' => 'permissions']) ?>

<form action="" method="post" id="admin-form">

  <div class="btn-group mb-4" role="group">
    <a href="<?= $router->getUrl('user/admin/edit-permission') ?>" class="btn btn-primary btn-sm" id="btn-new-permission"><?= Piko::t('user', 'New permission') ?></a>
    <button type="button" class="btn btn-danger btn-sm" id="delete"><?= Piko::t('user', 'Delete') ?></button>
  </div>

  <table class="table table-striped" id="permissions-table">
    <thead>
      <tr>
        <th><?= Piko::t('user', 'Name') ?></th>
        <th><?= Piko::t('user', 'Id') ?></th>
      </tr>
    </thead>
    <tbody>
<?php foreach($permissions as $permission): ?>
      <tr>
        <td>
          <input type="checkbox" name="items[]" value="<?= $permission['id'] ?>">&nbsp;
          <a href="<?= $router->getUrl('user/admin/edit-permission', ['id' => $permission['id']])?>"
             class="edit-permission" data-id="<?= $permission['id'] ?>"><?= $permission['name'] ?></a>
        </td>
        <td><?= $permission['id'] ?></td>
    </tr>
<?php endforeach ?>
	</tbody>
  </table>
</form>


<div class="modal fade" id="editPermissionModal" tabindex="-1" role="dialog" aria-labelledby="editPermissionModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <input type="text" id="permission-name" class="form-control" placeholder="<?= Piko::t('user', 'Permission name') ?>">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= Piko::t('user', 'Cancel') ?></button>
        <button type="button" class="btn btn-primary" id="btn-save-permission"><?= Piko::t('user', 'Save') ?></button>
      </div>
    </div>
  </div>
</div>
