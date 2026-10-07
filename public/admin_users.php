<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$users = db()->query("SELECT id, username, role, is_active, created_at FROM users ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h1 class="h">User Management (Admin)</h1>
  <p class="muted">This is a basic view. You can extend it to add/create users.</p>

  <table>
    <thead>
      <tr>
        <th>ID</th><th>Username</th><th>Role</th><th>Active</th><th>Created</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
          <td><?= (int)$u['is_active'] === 1 ? 'Yes' : 'No' ?></td>
          <td><?= htmlspecialchars($u['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
