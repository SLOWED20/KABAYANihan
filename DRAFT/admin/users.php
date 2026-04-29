<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}
$role        = $_SESSION['role'];
$active_page = 'users.php';
$success = $error = '';

// ── CREATE USER ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $newrole  = $_POST['role'] ?? 'editor';
  if (!in_array($newrole, ['admin', 'approver', 'editor'])) $newrole = 'editor';

  if (strlen($username) < 3) {
    $error = "Username must be at least 3 characters.";
  } elseif (strlen($password) < 6) {
    $error = "Password must be at least 6 characters.";
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hash, $newrole);
    if ($stmt->execute()) {
      $success = "User '{$username}' created successfully.";
    } else {
      $error = "Error: " . $stmt->error;
    }
    $stmt->close();
  }
}

// ── EDIT USER ─────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
  $id       = intval($_POST['edit_id']);
  $username = trim($_POST['username'] ?? '');
  $newrole  = $_POST['role'] ?? 'editor';
  if (!in_array($newrole, ['admin', 'approver', 'editor'])) $newrole = 'editor';
  $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
  $stmt->bind_param("ssi", $username, $newrole, $id);
  $stmt->execute();
  $stmt->close();
  $success = "User updated.";
}

// ── RESET PASSWORD ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_id'])) {
  $id   = intval($_POST['reset_id']);
  $hash = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);
  $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
  $stmt->bind_param("si", $hash, $id);
  $stmt->execute();
  $stmt->close();
  $success = "Password reset successfully.";
}

// ── DELETE USER ───────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  // Prevent self-deletion
  if ($id !== intval($_SESSION['user_id'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
  }
  header("Location: users.php?msg=deleted");
  exit;
}

$result = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>User Management — Municipal CMS</title>
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
        <div class="topbar-title">User Management</div>
        <div class="topbar-actions">
          <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#createForm">
            + Create User
          </button>
        </div>
      </div>

      <div class="page-body">
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
          <div class="alert alert-warning">User deleted.</div>
        <?php endif; ?>

        <!-- CREATE FORM -->
        <div class="collapse mb-4" id="createForm">
          <div class="card">
            <div class="card-header">➕ Create New User Account</div>
            <div class="card-body">
              <form method="POST">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:14px;align-items:end;">
                  <div class="form-group" style="margin:0;">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="e.g. juan_dela_cruz" required>
                  </div>
                  <div class="form-group" style="margin:0;">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="min. 6 characters" required>
                  </div>
                  <div class="form-group" style="margin:0;">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control form-select" required>
                      <option value="editor">Editor</option>
                      <option value="approver">Approver</option>
                      <option value="admin">Admin</option>
                    </select>
                  </div>
                  <div style="display:flex;gap:8px;">
                    <button type="submit" name="create" class="btn btn-primary">Create</button>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#createForm">Cancel</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- ROLE KEY -->
        <div style="display:flex;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
          <div style="background:white;border:1px solid var(--border);border-radius:var(--radius);padding:10px 16px;display:flex;gap:10px;align-items:flex-start;">
            <span class="pill pill-admin">Admin</span>
            <span style="font-size:12px;color:var(--text-2);">Full access: create users, manage all content, approve/reject, restore deleted items.</span>
          </div>
          <div style="background:white;border:1px solid var(--border);border-radius:var(--radius);padding:10px 16px;display:flex;gap:10px;align-items:flex-start;">
            <span class="pill pill-approver">Approver</span>
            <span style="font-size:12px;color:var(--text-2);">Can approve, reject, edit, delete, and publish content to the public site.</span>
          </div>
          <div style="background:white;border:1px solid var(--border);border-radius:var(--radius);padding:10px 16px;display:flex;gap:10px;align-items:flex-start;">
            <span class="pill pill-editor">Editor</span>
            <span style="font-size:12px;color:var(--text-2);">Can submit/edit content — goes to Pending for approver review before publishing.</span>
          </div>
        </div>

        <!-- TABLE -->
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Username</th>
                <th>Role</th>
                <th>Permissions</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()):
                $isSelf = ($row['id'] == $_SESSION['user_id']);
                $pillClass = 'pill-' . $row['role'];
                $permissions = [
                  'admin'    => 'Full access · Create users · Approve · Delete',
                  'approver' => 'Approve · Reject · Edit · Delete · Publish',
                  'editor'   => 'Submit &amp; Edit → Pending for approval',
                ];
              ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td>
                    <strong><?= htmlspecialchars($row['username']) ?></strong>
                    <?php if ($isSelf): ?>
                      <span style="font-size:10px;background:#f0fdf4;color:#16a34a;padding:2px 7px;border-radius:99px;margin-left:6px;">You</span>
                    <?php endif; ?>
                  </td>
                  <td><span class="pill <?= $pillClass ?>"><?= ucfirst($row['role']) ?></span></td>
                  <td style="font-size:12px;color:var(--text-3);"><?= $permissions[$row['role']] ?? '—' ?></td>
                  <td style="font-size:12.5px;"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                  <td>
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                    <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#resetModal<?= $row['id'] ?>">Reset PW</button>
                    <?php if (!$isSelf): ?>
                      <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Delete user <?= htmlspecialchars($row['username']) ?>?')">Delete</a>
                    <?php endif; ?>
                  </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title">Edit User: <?= htmlspecialchars($row['username']) ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                          <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($row['username']) ?>" required>
                          </div>
                          <div class="form-group">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control form-select">
                              <option value="editor" <?= $row['role'] === 'editor'   ? 'selected' : '' ?>>Editor</option>
                              <option value="approver" <?= $row['role'] === 'approver' ? 'selected' : '' ?>>Approver</option>
                              <option value="admin" <?= $row['role'] === 'admin'    ? 'selected' : '' ?>>Admin</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Reset Password Modal -->
                <div class="modal fade" id="resetModal<?= $row['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title">Reset Password — <?= htmlspecialchars($row['username']) ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="reset_id" value="<?= $row['id'] ?>">
                          <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="min. 6 characters" required minlength="6">
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-warning">Reset Password</button>
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