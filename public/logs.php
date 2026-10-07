<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = db();
$stmt = $pdo->query("
  SELECT l.created_at, u.username, u.role, l.action, l.details, l.ip_address
  FROM verification_logs l
  LEFT JOIN users u ON u.id = l.actor_id
  ORDER BY l.id DESC
  LIMIT 200
");
$logs = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h1 class="h">Audit Logs</h1>
  <p class="muted">Tracks all verification checks and actions.</p>

  <table>
    <thead>
      <tr>
        <th>Time</th>
        <th>User</th>
        <th>Role</th>
        <th>Action</th>
        <th>Details</th>
        <th>IP</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td><?= htmlspecialchars($l['created_at']) ?></td>
          <td><?= htmlspecialchars($l['username'] ?? '—') ?></td>
          <td><?= htmlspecialchars($l['role'] ?? '—') ?></td>
          <td><span class="badge"><?= htmlspecialchars($l['action']) ?></span></td>
          <td><?= htmlspecialchars($l['details'] ?? '—') ?></td>
          <td><?= htmlspecialchars($l['ip_address'] ?? '—') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
