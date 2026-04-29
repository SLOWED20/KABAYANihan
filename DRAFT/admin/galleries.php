<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['role'])) {
  header("Location: login.php");
  exit;
}

$role        = $_SESSION['role'];
$active_page = 'galleries.php';
$success = $error = '';

// ── CREATE (Editor/Admin/Approver) ────────────────────────────────────────────
// Editors → status=pending; Admins/Approvers → status=active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
  $title  = trim($_POST['title'] ?? '');
  $order  = intval($_POST['display_order'] ?? 0);
  $status = ($role === 'editor') ? 'pending' : 'active';
  $image  = '';

  if (!empty($_FILES['image']['name'])) {
    $image = time() . '_' . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $image);
  }

  $s = $conn->prepare("INSERT INTO galleries (title, display_order, image, status) VALUES (?, ?, ?, ?)");
  $s->bind_param("siss", $title, $order, $image, $status);
  $s->execute();
  $new_id = $conn->insert_id;
  $s->close();

  if ($role === 'editor') {
    // Push to pending_approval queue
    $p = $conn->prepare("INSERT INTO pending_approval (module_name, item_id, submitted_by, status) VALUES ('galleries', ?, ?, 'pending')");
    $p->bind_param("is", $new_id, $_SESSION['username']);
    $p->execute();
    $p->close();
    $success = "Gallery image submitted for approval.";
  } else {
    $success = "Gallery image published.";
  }
}

// ── EDIT (Editor → re-submit to pending; Approver/Admin → save directly) ──────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
  $id    = intval($_POST['edit_id']);
  $title = trim($_POST['title'] ?? '');
  $order = intval($_POST['display_order'] ?? 0);
  $image = '';

  if (!empty($_FILES['image']['name'])) {
    $image = time() . '_' . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $image);
  }

  if ($role === 'editor') {
    // Editor edits always go back to pending
    if ($image) {
      $s = $conn->prepare("UPDATE galleries SET title=?, display_order=?, image=?, status='pending' WHERE id=?");
      $s->bind_param("sisi", $title, $order, $image, $id);
    } else {
      $s = $conn->prepare("UPDATE galleries SET title=?, display_order=?, status='pending' WHERE id=?");
      $s->bind_param("sii", $title, $order, $id);
    }
    $s->execute();
    $s->close();

    // Add to pending queue (remove old entry first)
    $del = $conn->prepare("DELETE FROM pending_approval WHERE module_name='galleries' AND item_id=?");
    $del->bind_param("i", $id);
    $del->execute();
    $del->close();

    $p = $conn->prepare("INSERT INTO pending_approval (module_name, item_id, submitted_by, status) VALUES ('galleries', ?, ?, 'pending')");
    $p->bind_param("is", $id, $_SESSION['username']);
    $p->execute();
    $p->close();
    $success = "Edit submitted for approval.";
  } else {
    // Approver/Admin edits go live immediately
    if ($image) {
      $s = $conn->prepare("UPDATE galleries SET title=?, display_order=?, image=? WHERE id=?");
      $s->bind_param("sisi", $title, $order, $image, $id);
    } else {
      $s = $conn->prepare("UPDATE galleries SET title=?, display_order=? WHERE id=?");
      $s->bind_param("sii", $title, $order, $id);
    }
    $s->execute();
    $s->close();
    $success = "Gallery updated.";
  }
}

// ── APPROVE (Approver/Admin only) ──────────────────────────────────────────────
if (isset($_GET['approve']) && ($role === 'approver' || $role === 'admin')) {
  $id = intval($_GET['approve']);

  $s = $conn->prepare("UPDATE galleries SET status='active' WHERE id=?");
  $s->bind_param("i", $id);
  $s->execute();
  $s->close();

  // Log approval
  $log = $conn->prepare("INSERT INTO approved_logs (module_name, item_id, approved_by) VALUES ('galleries', ?, ?)");
  $log->bind_param("is", $id, $_SESSION['username']);
  $log->execute();
  $log->close();

  // Remove from pending
  $del = $conn->prepare("DELETE FROM pending_approval WHERE module_name='galleries' AND item_id=?");
  $del->bind_param("i", $id);
  $del->execute();
  $del->close();

  header("Location: galleries.php?msg=approved");
  exit;
}

// ── DELETE (Approver/Admin → soft-delete + log) ────────────────────────────────
if (isset($_GET['delete']) && ($role === 'approver' || $role === 'admin')) {
  $id = intval($_GET['delete']);

  $s = $conn->prepare("UPDATE galleries SET status='deleted' WHERE id=?");
  $s->bind_param("i", $id);
  $s->execute();
  $s->close();

  $log = $conn->prepare("INSERT INTO deleted_logs (module_name, item_id, deleted_by) VALUES ('galleries', ?, ?)");
  $log->bind_param("is", $id, $_SESSION['username']);
  $log->execute();
  $log->close();

  // Remove from pending if it was there
  $del = $conn->prepare("DELETE FROM pending_approval WHERE module_name='galleries' AND item_id=?");
  $del->bind_param("i", $id);
  $del->execute();
  $del->close();

  header("Location: galleries.php?msg=deleted");
  exit;
}

// ── FETCH (exclude deleted) ────────────────────────────────────────────────────
$result = $conn->query("SELECT * FROM galleries WHERE status != 'deleted' ORDER BY display_order ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Galleries — Municipal CMS</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body {
      font-family: 'DM Sans', sans-serif !important;
      background: var(--surface) !important;
    }

    .modal-content {
      border-radius: var(--radius-lg) !important;
    }
  </style>
</head>

<body>
  <div class="admin-shell">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
      <div class="topbar">
        <div class="topbar-title">Galleries</div>
        <div class="topbar-actions">
          <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#createForm">
            + <?= ($role === 'editor') ? 'Submit Image' : 'New Image' ?>
          </button>
        </div>
      </div>

      <div class="page-body">
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error):  ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if (isset($_GET['msg'])): ?>
          <?php if ($_GET['msg'] === 'approved'): ?><div class="alert alert-success">Gallery image approved and published.</div><?php endif; ?>
          <?php if ($_GET['msg'] === 'deleted'):  ?><div class="alert alert-warning">Gallery image deleted and logged.</div><?php endif; ?>
        <?php endif; ?>

        <div class="collapse mb-4" id="createForm">
          <div class="card">
            <div class="card-header">
              <?= $role === 'editor' ? '📝 Submit Gallery Image (will go to Pending)' : '🖼️ New Gallery Image' ?>
            </div>
            <div class="card-body">
              <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                  <label class="form-label">Title *</label>
                  <input type="text" name="title" class="form-control" placeholder="Image title" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Display Order</label>
                  <input type="number" name="display_order" class="form-control" value="0" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Image *</label>
                  <input type="file" name="image" class="form-control" accept="image/*" <?= $role === 'editor' ? 'required' : 'required' ?>>
                </div>
                <div style="display:flex;gap:8px;">
                  <button type="submit" name="create" class="btn btn-primary">
                    <?= $role === 'editor' ? 'Submit for Approval' : 'Publish Now' ?>
                  </button>
                  <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#createForm">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Image</th>
                <th>Title</th>
                <th>Order</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()):
                $st = $row['status'] ?? 'active'; // Fallback mapping if 'status' field missing initially
                $pillMap = ['active' => 'pill-active', 'pending' => 'pill-pending'];
                $pillCls = $pillMap[$st] ?? 'pill-deleted';
              ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td>
                    <?php if (!empty($row['image'])): ?>
                      <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" width="72" height="54"
                        style="object-fit:cover;border-radius:6px;border:1px solid var(--border);">
                    <?php else: ?>
                      <span style="font-size:20px;">📷</span>
                    <?php endif; ?>
                  </td>
                  <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                  <td><?= $row['display_order'] ?></td>
                  <td><span class="pill <?= $pillCls ?>"><?= ucfirst($st) ?></span></td>
                  <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    <?php if ($st === 'pending' && ($role === 'approver' || $role === 'admin')): ?>
                      <a href="?approve=<?= $row['id'] ?>" class="btn btn-sm btn-success"
                        onclick="return confirm('Approve and publish this image?')">✓ Approve</a>
                    <?php endif; ?>

                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>

                    <?php if ($role === 'approver' || $role === 'admin'): ?>
                      <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this image?')">Delete</a>
                    <?php endif; ?>
                  </td>
                </tr>

                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                          <h5 class="modal-title">Edit Gallery Image <?= $role === 'editor' ? '<small style="font-size:11px;color:#92400e;">(will re-submit to Pending)</small>' : '' ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                          <div class="form-group">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($row['title']) ?>" required>
                          </div>
                          <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="<?= $row['display_order'] ?>" required>
                          </div>
                          <div class="form-group">
                            <label class="form-label">Replace Image (optional)</label>
                            <?php if (!empty($row['image'])): ?>
                              <div style="font-size:11.5px;color:var(--text-3);margin-bottom:6px;">
                                Current: <code><?= htmlspecialchars($row['image']) ?></code>
                              </div>
                              <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" style="max-width:100%;max-height:100px;object-fit:cover;border-radius:6px;margin-bottom:8px;">
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control" accept="image/*">
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-primary">
                            <?= $role === 'editor' ? 'Re-submit for Approval' : 'Save Changes' ?>
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
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