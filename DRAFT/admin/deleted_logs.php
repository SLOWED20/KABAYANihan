<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['role'])) {
  header("Location: login.php");
  exit;
}
$role        = $_SESSION['role'];
$active_page = 'deleted_logs.php';

// ── RESTORE (Approver/Admin) ───────────────────────────────────────────────────
if (isset($_GET['restore']) && ($role === 'approver' || $role === 'admin')) {
  $log_id = intval($_GET['restore']);

  $s = $conn->prepare("SELECT module_name, item_id FROM deleted_logs WHERE id=?");
  $s->bind_param("i", $log_id);
  $s->execute();
  $s->bind_result($module_name, $item_id);

  if ($s->fetch()) {
    $s->close();
    $valid = ['announcements', 'destinations', 'services', 'faqs', 'profiles', 'galleries'];
    if (in_array($module_name, $valid)) {
      $r = $conn->prepare("UPDATE `$module_name` SET status='active' WHERE id=?");
      $r->bind_param("i", $item_id);
      $r->execute();
      $r->close();
    }
    $d = $conn->prepare("DELETE FROM deleted_logs WHERE id=?");
    $d->bind_param("i", $log_id);
    $d->execute();
    $d->close();
  } else {
    $s->close();
  }
  header("Location: deleted_logs.php?msg=restored");
  exit;
}

// ── PERMANENT DELETE from log (Admin only) ─────────────────────────────────────
if (isset($_GET['purge']) && $role === 'admin') {
  $log_id = intval($_GET['purge']);

  $s = $conn->prepare("SELECT module_name, item_id FROM deleted_logs WHERE id=?");
  $s->bind_param("i", $log_id);
  $s->execute();
  $s->bind_result($module_name, $item_id);
  if ($s->fetch()) {
    $s->close();
    $valid = ['announcements', 'destinations', 'services', 'faqs', 'profiles', 'galleries'];
    if (in_array($module_name, $valid)) {
      $del = $conn->prepare("DELETE FROM `$module_name` WHERE id=?");
      $del->bind_param("i", $item_id);
      $del->execute();
      $del->close();
    }
  } else {
    $s->close();
  }

  $d = $conn->prepare("DELETE FROM deleted_logs WHERE id=?");
  $d->bind_param("i", $log_id);
  $d->execute();
  $d->close();
  header("Location: deleted_logs.php?msg=purged");
  exit;
}

// ── FETCH grouped by module ────────────────────────────────────────────────────
$all_modules = ['announcements', 'destinations', 'services', 'faqs', 'profiles', 'galleries'];

// Module display config: icon + label
$module_meta = [
  'announcements' => ['icon' => '📢', 'label' => 'Announcements'],
  'destinations'  => ['icon' => '📍', 'label' => 'Destinations'],
  'services'      => ['icon' => '🗂️', 'label' => 'Services'],
  'faqs'          => ['icon' => '❓', 'label' => 'FAQs'],
  'profiles'      => ['icon' => '👤', 'label' => 'Profiles'],
  'galleries'     => ['icon' => '🖼️', 'label' => 'Galleries'],
];

// Fetch all logs, group into PHP arrays by module
$grouped = [];
foreach ($all_modules as $m) {
  $grouped[$m] = [];
}

$result = $conn->query("SELECT * FROM deleted_logs ORDER BY deleted_at DESC");
while ($row = $result->fetch_assoc()) {
  $mod = $row['module_name'];
  if (!isset($grouped[$mod])) {
    $grouped[$mod] = [];
  }
  $grouped[$mod][] = $row;
}

// Count totals per module and overall
$counts = [];
$total  = 0;
foreach ($all_modules as $m) {
  $counts[$m] = count($grouped[$m]);
  $total     += $counts[$m];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Deleted Logs — Municipal CMS</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
  <div class="admin-shell">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">

      <div class="topbar">
        <div class="topbar-title">Deleted Logs</div>
        <div class="topbar-actions">
          <span style="font-size:12px;color:var(--text-3);">
            <?= $total ?> total deleted item<?= $total !== 1 ? 's' : '' ?>
          </span>
        </div>
      </div>

      <div class="page-body">

        <?php if (isset($_GET['msg'])): ?>
          <?php if ($_GET['msg'] === 'restored'): ?>
            <div class="alert alert-success">✅ Item restored and set to active.</div>
          <?php endif; ?>
          <?php if ($_GET['msg'] === 'purged'): ?>
            <div class="alert alert-warning">🗑 Item permanently deleted.</div>
          <?php endif; ?>
        <?php endif; ?>

        <?php if ($total === 0): ?>
          <div class="table-wrap">
            <div style="text-align:center;padding:60px;color:var(--text-3);">
              <div style="font-size:40px;margin-bottom:12px;">🧹</div>
              <div style="font-weight:600;font-size:15px;margin-bottom:4px;">No deleted items on record</div>
              <div style="font-size:13px;">Items soft-deleted from any module will appear here.</div>
            </div>
          </div>
        <?php else: ?>

          <!-- Module Filter Tabs -->
          <div class="module-tabs">
            <button class="module-tab-btn active" data-module="all">
              All
              <span style="background:rgba(0,0,0,.08);border-radius:99px;padding:1px 7px;font-size:11px;"><?= $total ?></span>
            </button>
            <?php foreach ($all_modules as $m):
              if ($counts[$m] === 0) continue;
              $meta = $module_meta[$m];
            ?>
              <button class="module-tab-btn" data-module="<?= $m ?>">
                <?= $meta['icon'] ?> <?= $meta['label'] ?>
                <span style="background:rgba(0,0,0,.08);border-radius:99px;padding:1px 7px;font-size:11px;"><?= $counts[$m] ?></span>
              </button>
            <?php endforeach; ?>
          </div>

          <!-- Module Sections -->
          <?php foreach ($all_modules as $m):
            if (empty($grouped[$m])) continue;
            $meta = $module_meta[$m];
          ?>
            <div class="module-section visible" data-module="<?= $m ?>" style="margin-bottom:28px;">

              <!-- Section Header -->
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <span style="font-size:20px;"><?= $meta['icon'] ?></span>
                <div>
                  <div style="font-size:15px;font-weight:700;color:var(--text-1);"><?= $meta['label'] ?></div>
                  <div style="font-size:12px;color:var(--text-3);">
                    <?= $counts[$m] ?> deleted item<?= $counts[$m] !== 1 ? 's' : '' ?>
                  </div>
                </div>
              </div>

              <div class="table-wrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Item ID</th>
                      <th>Deleted By</th>
                      <th>Deleted At</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($grouped[$m] as $row): ?>
                      <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                          <span style="font-family:'DM Mono',monospace;font-size:12px;background:var(--surface);padding:2px 8px;border-radius:5px;border:1px solid var(--border);">
                            #<?= $row['item_id'] ?>
                          </span>
                        </td>
                        <td style="font-size:13px;">
                          <div style="display:flex;align-items:center;gap:7px;">
                            <div style="width:26px;height:26px;border-radius:50%;background:var(--rose-light);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:var(--rose);">
                              <?= strtoupper(substr(htmlspecialchars($row['deleted_by']), 0, 2)) ?>
                            </div>
                            <?= htmlspecialchars($row['deleted_by']) ?>
                          </div>
                        </td>
                        <td style="font-size:12.5px;color:var(--text-3);">
                          <?= date('M d, Y', strtotime($row['deleted_at'])) ?>
                          <div style="font-size:11px;color:var(--text-4);"><?= date('H:i', strtotime($row['deleted_at'])) ?></div>
                        </td>
                        <td style="display:flex;gap:6px;">
                          <?php if ($role === 'approver' || $role === 'admin'): ?>
                            <a href="?restore=<?= $row['id'] ?>" class="btn btn-sm btn-success"
                              onclick="return confirm('Restore this item? It will be set back to Active.')">↩ Restore</a>
                          <?php endif; ?>
                          <?php if ($role === 'admin'): ?>
                            <a href="?purge=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                              onclick="return confirm('PERMANENTLY delete this item? This cannot be undone.')">🗑 Purge</a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            </div><!-- /module-section -->
          <?php endforeach; ?>

        <?php endif; ?>

      </div><!-- /page-body -->
    </div><!-- /main-content -->
  </div><!-- /admin-shell -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/admin.js"></script>
</body>

</html>
