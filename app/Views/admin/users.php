<section class="page-head"><span class="eyebrow">OPERATIONS</span><h1>Users</h1><p>Customer accounts (latest 100).</p><a class="btn secondary" href="/admin">Back to dashboard</a></section>
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Mobile</th><th>Role</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead><tbody>
<?php foreach ($users as $u): ?>
<tr>
  <td><?= Fitmeal\Core\Security::e($u['name']) ?></td>
  <td><?= Fitmeal\Core\Security::e($u['email']) ?></td>
  <td><?= Fitmeal\Core\Security::e($u['mobile'] ?? '') ?></td>
  <td><?= Fitmeal\Core\Security::e($u['role']) ?></td>
  <td><?= Fitmeal\Core\Security::e($u['status']) ?></td>
  <td><?= Fitmeal\Core\Security::e($u['created_at']) ?></td>
  <td>
    <?php if ($u['role'] === 'customer'): ?>
    <form method="post" action="/admin/users/<?= (int)$u['id'] ?>/status" style="display:inline">
      <input type="hidden" name="_csrf" value="<?= Fitmeal\Core\Security::csrfToken() ?>">
      <input type="hidden" name="status" value="<?= $u['status'] === 'active' ? 'blocked' : 'active' ?>">
      <button class="btn secondary" type="submit"><?= $u['status'] === 'active' ? 'Block' : 'Activate' ?></button>
    </form>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
