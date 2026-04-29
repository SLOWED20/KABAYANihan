<?php session_start();
include '../includes/db.php';
if (!isset($_SESSION['role'])) {
  header("Location: login.php");
  exit;
}
$role = $_SESSION['role'];
$active_page = 'faqs.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
  $q = trim($_POST['question']);
  $a = trim($_POST['answer']);
  $s = $conn->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)");
  $s->bind_param("ss", $q, $a);
  $s->execute();
  header("Location: faqs.php");
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
  $id = intval($_POST['edit_id']);
  $q = trim($_POST['question']);
  $a = trim($_POST['answer']);
  $s = $conn->prepare("UPDATE faqs SET question=?,answer=? WHERE id=?");
  $s->bind_param("ssi", $q, $a, $id);
  $s->execute();
  header("Location: faqs.php");
  exit;
}
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $s = $conn->prepare("DELETE FROM faqs WHERE id=?");
  $s->bind_param("i", $id);
  $s->execute();
  header("Location: faqs.php");
  exit;
}
$result = $conn->query("SELECT * FROM faqs ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>FAQs — Municipal CMS</title>
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
        <div class="topbar-title">FAQs</div>
        <div class="topbar-actions"><button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#createForm">+ Add FAQ</button></div>
      </div>
      <div class="page-body">
        <div class="collapse mb-4" id="createForm">
          <div class="card">
            <div class="card-header">Add FAQ</div>
            <div class="card-body">
              <form method="POST">
                <div class="form-group"><label class="form-label">Question</label><input type="text" name="question" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Answer</label><textarea name="answer" class="form-control" rows="3" required></textarea></div>
                <div style="display:flex;gap:8px;"><button type="submit" name="create" class="btn btn-primary">Save</button><button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#createForm">Cancel</button></div>
              </form>
            </div>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Question</th>
                <th>Answer</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody><?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><strong><?= htmlspecialchars($row['question']) ?></strong></td>
                  <td style="font-size:13px;color:var(--text-2);max-width:240px;"><?= htmlspecialchars(substr($row['answer'], 0, 100)) ?>...</td>
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
                          <h5 class="modal-title">Edit FAQ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body"><input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                          <div class="form-group"><label class="form-label">Question</label><input type="text" name="question" class="form-control" value="<?= htmlspecialchars($row['question']) ?>" required></div>
                          <div class="form-group"><label class="form-label">Answer</label><textarea name="answer" class="form-control" required><?= htmlspecialchars($row['answer']) ?></textarea></div>
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