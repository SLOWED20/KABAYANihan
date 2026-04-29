<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['role'])) {
  header("Location: login.php");
  exit;
}
$role        = $_SESSION['role'];
$active_page = 'pending.php';

// ── APPROVE (Approver/Admin) ───────────────────────────────────────────────────
if (isset($_GET['approve']) && ($role === 'approver' || $role === 'admin')) {
  $id = intval($_GET['approve']);

  // Fetch module/item from pending table
  $s = $conn->prepare("SELECT module_name, item_id, submitted_by FROM pending_approval WHERE id=?");
  $s->bind_param("i", $id);
  $s->execute();
  $s->bind_result($module_name, $item_id, $submitted_by);

  if ($s->fetch()) {
    $s->close();

    // Activate the item in its source table
    $valid_modules = ['announcements', 'destinations', 'services', 'faqs', 'profiles'];
    if (in_array($module_name, $valid_modules)) {
      $upd = $conn->prepare("UPDATE `$module_name` SET status='active' WHERE id=?");
      $upd->bind_param("i", $item_id);
      $upd->execute();
      $upd->close();
    }

    // Log to approved_logs
    $log = $conn->prepare("INSERT INTO approved_logs (module_name, item_id, approved_by) VALUES (?, ?, ?)");
    $log->bind_param("sis", $module_name, $item_id, $_SESSION['username']);
    $log->execute();
    $log->close();

    // Remove from pending
    $d = $conn->prepare("DELETE FROM pending_approval WHERE id=?");
    $d->bind_param("i", $id);
    $d->execute();
    $d->close();
  } else {
    $s->close();
  }

  header("Location: pending.php?msg=approved");
  exit;
}

// ── REJECT (Approver/Admin) ────────────────────────────────────────────────────
if (isset($_GET['reject']) && ($role === 'approver' || $role === 'admin')) {
  $id = intval($_GET['reject']);
  $s  = $conn->prepare("UPDATE pending_approval SET status='rejected' WHERE id=?");
  $s->bind_param("i", $id);
  $s->execute();
  $s->close();
  header("Location: pending.php?msg=rejected");
  exit;
}

// ── DELETE pending entry (Editor/Admin can withdraw) ──────────────────────────
if (isset($_GET['delete']) && ($role === 'editor' || $role === 'admin')) {
  $id = intval($_GET['delete']);
  $s  = $conn->prepare("DELETE FROM pending_approval WHERE id=?");
  $s->bind_param("i", $id);
  $s->execute();
  $s->close();
  header("Location: pending.php?msg=withdrawn");
  exit;
}

$result = $conn->query("SELECT pa.*, pa.submitted_by FROM pending_approval pa ORDER BY pa.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pending Approval — Municipal CMS</title>
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
        <div class="topbar-title">Pending Approval</div>
      </div>
      <div class="page-body">

        <?php if (isset($_GET['msg'])): ?>
          <?php if ($_GET['msg'] === 'approved'):  ?><div class="alert alert-success">Item approved and published.</div><?php endif; ?>
          <?php if ($_GET['msg'] === 'rejected'):  ?><div class="alert alert-warning">Item rejected.</div><?php endif; ?>
          <?php if ($_GET['msg'] === 'withdrawn'): ?><div class="alert alert-info">Submission withdrawn.</div><?php endif; ?>
        <?php endif; ?>

        <div class="alert alert-info" style="margin-bottom:20px;">
          ℹ️ <strong>Workflow:</strong> Editors submit content → appears here as Pending → Approver/Admin approves or rejects → approved content goes live on the public site.
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Module</th>
                <th>Item ID</th>
                <th>Submitted By</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $count = 0;
              while ($row = $result->fetch_assoc()):
                $count++;
                $st = $row['status'];
                $stClass = ['pending' => 'pill-pending', 'rejected' => 'pill-rejected', 'approved' => 'pill-active'];
              ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><strong><?= htmlspecialchars($row['module_name']) ?></strong></td>
                  <td><span style="font-family:'DM Mono',monospace;font-size:12px;">#<?= $row['item_id'] ?></span></td>
                  <td style="font-size:13px;"><?= htmlspecialchars($row['submitted_by']) ?></td>
                  <td><span class="pill <?= $stClass[$st] ?? '' ?>"><?= ucfirst($st) ?></span></td>
                  <td style="font-size:12.5px;"><?= date('M d, Y H:i', strtotime($row['created_at'])) ?></td>
                  <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    <?php if ($st === 'pending' && ($role === 'approver' || $role === 'admin')): ?>
                      <a href="?approve=<?= $row['id'] ?>" class="btn btn-sm btn-success"
                        onclick="return confirm('Approve and publish this item?')">✓ Approve</a>
                      <a href="?reject=<?= $row['id'] ?>" class="btn btn-sm btn-warning"
                        onclick="return confirm('Reject this submission?')">✗ Reject</a>
                    <?php endif; ?>
                    <?php if ($role === 'editor' || $role === 'admin'): ?>
                      <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Withdraw this submission?')">Withdraw</a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
              <?php if ($count === 0): ?>
                <tr>
                  <td colspan="7" style="text-align:center;padding:40px;color:var(--text-3);">
                    ✅ No pending submissions at this time.
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