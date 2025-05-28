<?php
use Piko;
use function Piko\I18n\__;
assert($this instanceof Piko\View);

/* @var $permissions array */

$this->title = __('user', 'Permissions');

$script = <<<JS
document.addEventListener('DOMContentLoaded', function() {

  const modal = new bootstrap.Modal(document.getElementById('editPermissionModal'));

  var action = '';
  var permissionName = '';
  var permissionId = '';

  // Delete button event listener
  document.querySelector('#delete').addEventListener('click', function(e) {
    if (confirm('Êtes-vous sûr de vouloir effectuer cette action ?')) {
        document.querySelector('#admin-form').setAttribute('action', '/user/admin/delete-permissions');
        document.querySelector('#admin-form').submit();
    }
  });

  // New/Edit permission button event listener
  document.querySelectorAll('#btn-new-permission, .edit-permission').forEach(function(element) {
    element.addEventListener('click', function(e) {
      e.preventDefault();

      permissionId = this.getAttribute('data-id');
      action = this.getAttribute('href');

      if (this.classList.contains('edit-permission')) {
        permissionName = this.textContent;
      }

      document.querySelector('#permission-name').value = permissionName;
      modal.show();
    });
  });

  document.querySelector('#btn-save').addEventListener('click', function() {
    if (document.querySelector('#permission-name').value) {
      fetch(action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          name: document.querySelector('#permission-name').value,
          id: permissionId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          location.reload();
        } else if (data.status === 'error') {
          alert(data.error);
        }
      });
    }
  });

  document.getElementById('btn-cancel').addEventListener('click', function() {
    modal.hide();
  })
});
JS;
$this->registerJs($script);

?>

<?= $this->render('nav', ['page' => 'permissions']) ?>

<form action="" method="post" id="admin-form">

  <div class="btn-group mb-4" role="group">
    <a href="<?= $this->getUrl('user/admin/edit-permission') ?>" class="btn btn-primary btn-sm" id="btn-new-permission"><?= __('user', 'New permission') ?></a>
    <button type="button" class="btn btn-danger btn-sm" id="delete"><?= __('user', 'Delete') ?></button>
  </div>

  <table class="table table-striped" id="permissions-table">
    <thead>
      <tr>
        <th><?= __('user', 'Name') ?></th>
        <th><?= __('user', 'Id') ?></th>
      </tr>
    </thead>
    <tbody>
<?php foreach($permissions as $permission): ?>
      <tr>
        <td>
          <input type="checkbox" name="items[]" value="<?= $permission['id'] ?>">&nbsp;
          <a href="<?= $this->getUrl('user/admin/edit-permission', ['id' => $permission['id']])?>"
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
        <input type="text" id="permission-name" class="form-control" placeholder="<?= __('user', 'Permission name') ?>">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btn-cancel"><?= __('user', 'Cancel') ?></button>
        <button type="button" class="btn btn-primary" id="btn-save"><?= __('user', 'Save') ?></button>
      </div>
    </div>
  </div>
</div>
