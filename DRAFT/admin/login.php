<?php
session_start();
include '../includes/db.php';

// Already logged in → redirect
if (isset($_SESSION['role'])) {
  header("Location: dashboard.php");
  exit;
}

$error   = '';
$success = '';

// ── LOGIN ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $stmt = $conn->prepare("SELECT id, password_hash, role FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->bind_result($id, $hash, $role);
  if ($stmt->fetch() && password_verify($password, $hash)) {
    $_SESSION['user_id']  = $id;
    $_SESSION['role']     = $role;
    $_SESSION['username'] = $username;
    header("Location: dashboard.php");
    exit;
  } else {
    $error = "Invalid username or password.";
  }
  $stmt->close();
}

// ── REGISTER (admin-only, via POST from within session) ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
  if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $error = "Only Admins can register new users.";
  } else {
    $username = trim($_POST['new_username'] ?? '');
    $password = trim($_POST['new_password'] ?? '');
    $newrole  = $_POST['role'] ?? 'editor';
    if (!in_array($newrole, ['admin', 'approver', 'editor'])) $newrole = 'editor';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hash, $newrole);
    if ($stmt->execute()) {
      $success = "User '{$username}' registered successfully!";
    } else {
      $error = "Error: " . $stmt->error;
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Municipal CMS</title>
  <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
  <div class="login-wrap">
    <div class="login-brand">
      <div class="brand-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
      </div>
      <h1>Municipal CMS</h1>
      <p>Kabayan, Benguet · Admin Panel</p>
    </div>

    <div class="login-card">
      <ul class="nav-tabs">
        <li><a class="nav-link active" data-tab="login" href="#">Sign In</a></li>
        <li><a class="nav-link" data-tab="register" href="#">Register</a></li>
      </ul>

      <div class="tab-content">
        <?php if ($error)   echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

        <!-- LOGIN TAB -->
        <div class="tab-pane show active" id="login">
          <form method="POST" autocomplete="on">
            <input type="hidden" name="login" value="1">
            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" placeholder="your_username" required autofocus>
            </div>
            <div class="form-group">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="remember" name="remember">
              <label class="form-check-label" for="remember">Keep me signed in</label>
            </div>
            <button type="submit" class="btn btn-primary">Sign In →</button>
            <a href="#" class="link-subtle">Forgot your password? Contact Admin</a>
          </form>
        </div>

        <!-- REGISTER TAB (admin-only UI note) -->
        <div class="tab-pane" id="register">
          <div class="alert alert-warning" style="margin-bottom:16px;">
            ⚠️ Only Admins can create new accounts. You must be logged in as Admin.
          </div>
          <form method="POST">
            <input type="hidden" name="register" value="1">
            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" name="new_username" class="form-control" placeholder="new_user" required>
            </div>
            <div class="form-group">
              <label class="form-label">Password</label>
              <input type="password" name="new_password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="form-group">
              <label class="form-label">Role</label>
              <select name="role" class="form-control form-select" required>
                <option value="editor">Editor — can edit posts for approval</option>
                <option value="approver">Approver — can approve &amp; publish</option>
                <option value="admin">Admin — full access</option>
              </select>
            </div>
            <button type="submit" class="btn btn-success">Create Account</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.querySelectorAll('[data-tab]').forEach(tab => {
      tab.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('.nav-link').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show', 'active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('show', 'active');
      });
    });
  </script>
</body>

</html>