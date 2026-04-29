<?php session_start();
include '../includes/db.php';
if (!isset($_SESSION['role'])) {
  header("Location: login.php");
  exit;
}
$role = $_SESSION['role'];
$active_page = 'services.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
  $n = trim($_POST['service_name']);
  $o = trim($_POST['office']);
  $l = trim($_POST['form_link']);
  $s = $conn->prepare("INSERT INTO services (service_name, office, form_link) VALUES (?, ?, ?)");
  $s->bind_param("sss", $n, $o, $l);
  $s->execute();
  header("Location: services.php");
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
  $id = intval($_POST['edit_id']);
  $n = trim($_POST['service_name']);
  $o = trim($_POST['office']);
  $l = trim($_POST['form_link']);
  $s = $conn->prepare("UPDATE services SET service_name=?,office=?,form_link=? WHERE id=?");
  $s->bind_param("sssi", $n, $o, $l, $id);
  $s->execute();
  header("Location: services.php");
  exit;
}
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $s = $conn->prepare("DELETE FROM services WHERE id=?");
  $s->bind_param("i", $id);
  $s->execute();
  header("Location: services.php");
  exit;
}
$search = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_search'])) {
  $search = trim($_POST['search']);
  $stmt = $conn->prepare("SELECT * FROM services WHERE service_name LIKE CONCAT('%',?,'%') OR office LIKE CONCAT('%',?,'%')");
  $stmt->bind_param("ss", $search, $search);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query("SELECT * FROM services ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Services — Municipal CMS</title>
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
  <div class="admin-shell"><?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
      <div class="topbar">
        <div class="topbar-title">Services</div>
        <div class="topbar-actions"><button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#createForm">+ Add Service</button></div>
      </div>
      <div class="page-body">
        <div class="collapse mb-4" id="createForm">
          <div class="card">
            <div class="card-header">Add New Service</div>
            <div class="card-body">
              <form method="POST">
                <div class="form-group"><label class="form-label">Service Name</label><input type="text" name="service_name" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Office</label><input type="text" name="office" class="form-control"></div>
                <div class="form-group"><label class="form-label">Form Link</label><input type="text" name="form_link" class="form-control" placeholder="https://..."></div>
                <div style="display:flex;gap:8px;"><button type="submit" name="create" class="btn btn-primary">Save</button><button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#createForm">Cancel</button></div>
              </form>
            </div>
          </div>
        </div>
        <div class="search-bar">
          <form method="POST" style="display:flex;gap:8px;">
            <input type="text" name="search" class="form-control" placeholder="Search services or offices..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" name="do_search" class="btn btn-secondary">Search</button>
          </form>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Service Name</th>
                <th>Office</th>
                <th>Form Link</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody><?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><strong><?= htmlspecialchars($row['service_name']) ?></strong></td>
                  <td style="font-size:13px;"><?= htmlspecialchars($row['office']) ?></td>
                  <td><?php if ($row['form_link']): ?><a href="<?= htmlspecialchars($row['form_link']) ?>" target="_blank" style="font-size:13px;color:var(--teal);">Open →</a><?php endif; ?></td>
                  <td style="font-size:12.5px;"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                  <td><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
                  </td>
                </tr>
                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title">Edit Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body"><input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                          <div class="form-group"><label class="form-label">Service Name</label><input type="text" name="service_name" class="form-control" value="<?= htmlspecialchars($row['service_name']) ?>" required></div>
                          <div class="form-group"><label class="form-label">Office</label><input type="text" name="office" class="form-control" value="<?= htmlspecialchars($row['office']) ?>"></div>
                          <div class="form-group"><label class="form-label">Form Link</label><input type="text" name="form_link" class="form-control" value="<?= htmlspecialchars($row['form_link']) ?>"></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
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
</body>

</html>