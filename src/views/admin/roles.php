<?php
use Piko;
use function Piko\I18n\__;
assert($this instanceof Piko\View);

/* @var $roles array */
/* @var $permissions array */

$this->title = __('user', 'Roles');
$confirmDeleteMsg = __('user', 'Are you sure you want to perform this action?');

$script = <<<JS
document.addEventListener('DOMContentLoaded', function() {

  const modal = new bootstrap.Modal(document.getElementById('editRoleModal'));

  var data = {
    id: '',
    name: '',
    description: ''
  };

  var action = '';

  document.getElementById('delete').addEventListener('click', function(e) {
    if (confirm('{$confirmDeleteMsg}')) {
        document.getElementById('admin-form').setAttribute('action', '/user/admin/delete-roles');
        document.getElementById('admin-form').submit();
    }
  });

  document.querySelectorAll('#btn-new, .edit-role').forEach(function(element) {
    element.addEventListener('click', function(e) {
      e.preventDefault();
      data.id = this.getAttribute('data-id');
      data.description = this.getAttribute('data-description');
      action = this.getAttribute('href');

      if (this.classList.contains('edit-role')) {
        data.name = this.textContent;
      }

      document.getElementById('role-name').value = data.name;
      document.getElementById('role-description').value = data.description;

      fetch(action)
        .then(response => response.json())
        .then(data => {
        if (Array.isArray(data.role.permissions)) {
            const permissionsSelect = document.getElementById('permissions');
            Array.from(permissionsSelect.options).forEach(option => {
              option.selected = data.role.permissions.includes(parseInt(option.value));
            });
        }
      });

      modal.show();
    });
  });

  document.getElementById('btn-save').addEventListener('click', function() {
    if (document.getElementById('role-name').value) {
      data.name = document.getElementById('role-name').value;
      data.description = document.getElementById('role-description').value;
      data.permissions = Array.from(document.getElementById('permissions').selectedOptions).map(option => option.value);
      fetch(action, {
        method: 'post',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
      })
      .then(response => response.json())
      .then(data => {
        if (data.status == 'success') {
          location.reload();
        } else if (data.status === 'error') {
          alert(data.error);
        }
      });
    }
  },
  { once: true });

  document.getElementById('btn-cancel').addEventListener('click', function() {
    modal.hide();
  })
});
JS;
$this->registerJs($script);
?>

<?= $this->render('nav', ['page' => 'roles']) ?>

<form action="" method="post" id="admin-form">

  <div class="btn-group mb-4" role="group">
    <a href="<?= $this->getUrl('user/admin/edit-role') ?>" class="btn btn-primary btn-sm" id="btn-new"><?= __('user', 'New role') ?></a>
    <button type="button" class="btn btn-danger btn-sm" id="delete"><?= __('user', 'Delete') ?></button>
  </div>

  <table class="table table-striped" id="roles-table">
    <thead>
      <tr>
        <th><?= __('user', 'Name') ?></th>
        <th><?= __('user', 'Description') ?></th>
        <th><?= __('user', 'Id') ?></th>
      </tr>
    </thead>
    <tbody>
<?php foreach($roles as $role): ?>
      <tr>
        <td>
          <input type="checkbox" name="items[]" value="<?= $role['id'] ?>">&nbsp;
          <a href="<?= $this->getUrl('user/admin/edit-role', ['id' => $role['id']])?>"
             class="edit-role"
             data-description="<?= $role['description'] ?>"
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
          <label for="role-name"><?= __('user', 'Role name') ?></label>
          <input type="text" id="role-name" class="form-control">
        </div>
        <div class="form-group">
          <label for="role-description"><?= __('user', 'Description') ?></label>
          <textarea rows="3" class="form-control" id="role-description"></textarea>
        </div>
        <div class="form-group">
          <label for="permissions"><?= __('user', 'Role permissions') ?></label>
          <select class="form-select" id="permissions" multiple>
            <?php foreach ($permissions as $perm): ?>
            <option value="<?= $perm['id']?>"><?= $perm['name']?></option>
            <?php endforeach ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btn-cancel"><?= __('user', 'Cancel') ?></button>
        <button type="button" class="btn btn-primary" id="btn-save"><?= __('user', 'Save') ?></button>
      </div>
    </div>
  </div>
</div>

