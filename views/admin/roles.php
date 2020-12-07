<?php
use piko\Piko;

use piko\datatable\Datatable;

/* @var $this \piko\View */
/* @var $router \piko\Router */
/* @var $roles array */
/* @var $permissions array */

$router = Piko::get('router');

$this->title = Piko::t('user', 'Roles');

Datatable::init($this, 'roles-table', [
    'order' => [[1, 'desc']],
    'language' => Piko::$app->language
]);

$confirmDeleteMsg = Piko::t('user', 'Are you sure you want to perform this action?');

$script = <<<JS
$(function() {

  $('#delete').click(function(e) {
    if (confirm('{$confirmDeleteMsg}')) {
        $('#admin-form').attr('action', '/user/admin/delete-roles')
        $('#admin-form').submit()
    }
  });

  $('#btn-new, .edit-role').on('click', function(e) {
    e.preventDefault();

    var data = {
        id: $(this).data('id'),
        parent_id: $(this).data('parent_id'),
        name: '',
        description: $(this).data('description')
    };

    var action = $(this).attr('href');
    
    if ($(this).hasClass('edit-role')) {
        data.name = $(this).text();
    }
    
    $('#role-name').val(data.name);
    $('#role-description').val(data.description);

    $.ajax({
        method: 'get',
        url: action,
    })
    .done(function(data) {
       if (data.role.permissions) {
        $('#permissions').val(data.role.permissions)
       }
    });

    $('#editRoleModal').modal('show');

    $('#btn-save').on('click', function() {
        if ($('#role-name').val()) {
            data.name = $('#role-name').val();
            data.description = $('#role-description').val();
            data.parent_id = $('#role-parent-id').val();
            data.permissions = $('#permissions').val();
            $.ajax({
                method: 'post',
                url: action,
                data: data
            })
            .done(function(data) {
               if (data.status == 'success') {
                    location.reload();
               }
            });
        }
    });
  });
});
JS;
$this->registerJs($script);

?>

<?= $this->render('nav', ['page' => 'roles']) ?>

<form action="" method="post" id="admin-form">

  <div class="btn-group mb-4" role="group">
    <a href="<?= $router->getUrl('user/admin/edit-role') ?>" class="btn btn-primary btn-sm" id="btn-new"><?= Piko::t('user', 'New role') ?></a>
    <button type="button" class="btn btn-danger btn-sm" id="delete"><?= Piko::t('user', 'Delete') ?></button>
  </div>

  <table class="table table-striped" id="roles-table">
    <thead>
      <tr>
        <th><?= Piko::t('user', 'Name') ?></th>
        <th><?= Piko::t('user', 'Description') ?></th>
        <th><?= Piko::t('user', 'Id') ?></th>
      </tr>
    </thead>
    <tbody>
<?php foreach($roles as $role): ?>
      <tr>
        <td>
          <input type="checkbox" name="items[]" value="<?= $role['id'] ?>">&nbsp;
          <a href="<?= $router->getUrl('user/admin/edit-role', ['id' => $role['id']])?>"
             class="edit-role"
             data-description="<?= $role['description'] ?>"
             data-parent_id="<?= $role['parent_id'] ?>"
             data-id="<?= $role['id'] ?>"><?= $role['name'] ?></a>
        </td>
        <td><?= $role['description'] ?></td>
        <td><?= $role['id'] ?></td>
    </tr>
<?php endforeach ?>
	</tbody>
  </table>
</form>


<div class="modal fade" id="editRoleModal" tabindex="-1" role="dialog" aria-labelledby="editRoleModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <div class="form-group">
          <label for="role-name"><?= Piko::t('user', 'Role name') ?></label>
          <input type="text" id="role-name" class="form-control">
        </div>
        <div class="form-group">
          <label for="role-description"><?= Piko::t('user', 'Description') ?></label>
          <textarea rows="3" class="form-control" id="role-description"></textarea>
        </div>
        <div class="form-group">
          <label for="permissions"><?= Piko::t('user', 'Role permissions') ?></label>
          <select class="custom-select" id="permissions" multiple>
            <?php foreach ($permissions as $perm): ?>
            <option value="<?= $perm['id']?>"><?= $perm['name']?></option>
            <?php endforeach ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= Piko::t('user', 'Cancel') ?></button>
        <button type="button" class="btn btn-primary" id="btn-save"><?= Piko::t('user', 'Save') ?></button>
      </div>
    </div>
  </div>
</div>

