<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['role'])) {
  header("Location: login.php");
  exit;
}
$role        = $_SESSION['role'];
$active_page = 'approved_logs.php';

// Only admin can delete log entries
if (isset($_GET['delete']) && $role === 'admin') {
  $id = intval($_GET['delete']);
  $s  = $conn->prepare("DELETE FROM approved_logs WHERE id=?");
  $s->bind_param("i", $id);
  $s->execute();
  $s->close();
  header("Location: approved_logs.php?msg=deleted");
  exit;
}

$result = $conn->query("SELECT * FROM approved_logs ORDER BY approved_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Approved Logs — Municipal CMS</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body {
      font-family: 'DM Sans', sans-serif !important;
      background: var(--surface) !important;
    }
  </style>
</head>

<body>
  <div class="admin-shell">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
      <div class="topbar">
        <div class="topbar-title">Approved Logs</div>
      </div>
      <div class="page-body">

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
          <div class="alert alert-warning">Log entry deleted.</div>
        <?php endif; ?>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Module</th>
                <th>Item ID</th>
                <th>Approved By</th>
                <th>Approved At</th>
                <?php if ($role === 'admin'): ?><th>Actions</th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php
              $count = 0;
              while ($row = $result->fetch_assoc()):
                $count++;
              ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><strong><?= htmlspecialchars($row['module_name']) ?></strong></td>
                  <td><span style="font-family:'DM Mono',monospace;font-size:12px;">#<?= $row['item_id'] ?></span></td>
                  <td style="font-size:13px;"><?= htmlspecialchars($row['approved_by']) ?></td>
                  <td style="font-size:12.5px;"><?= date('M d, Y H:i', strtotime($row['approved_at'])) ?></td>
                  <?php if ($role === 'admin'): ?>
                    <td>
                      <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Remove this log entry?')">Delete</a>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endwhile; ?>
              <?php if ($count === 0): ?>
                <tr>
                  <td colspan="6" style="text-align:center;padding:40px;color:var(--text-3);">
                    No approvals on record yet.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/admin.js"></script>
</body>

</html>