<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['role'])) {
  header("Location: login.php");
  exit;
}
$role        = $_SESSION['role'];
$active_page = 'dashboard.php';

// Counts
$count_ann     = $conn->query("SELECT COUNT(*) FROM announcements WHERE status='active'")->fetch_row()[0] ?? 0;
$count_dest    = $conn->query("SELECT COUNT(*) FROM destinations")->fetch_row()[0] ?? 0;
$count_pending = $conn->query("SELECT COUNT(*) FROM pending_approval WHERE status='pending'")->fetch_row()[0] ?? 0;
$count_svc     = $conn->query("SELECT COUNT(*) FROM services")->fetch_row()[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Municipal CMS</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
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
        <div class="topbar-title">Dashboard</div>
        <div class="topbar-actions">
          <span style="font-size:13px;color:var(--text-3);">Welcome back,
            <strong style="color:var(--text-1);"><?= ucfirst(htmlspecialchars($role)) ?></strong>
            <?php if (isset($_SESSION['username'])): ?>
              <span style="color:var(--text-3);">(<?= htmlspecialchars($_SESSION['username']) ?>)</span>
            <?php endif; ?>
          </span>
        </div>
      </div>

      <div class="page-body">

        <!-- Role banner -->
        <?php
        $roleBanners = [
          'admin'    => ['color' => '#fee2e2', 'border' => '#fca5a5', 'text' => '#991b1b', 'msg' => '🔑 Admin — You have full access: manage users, approve content, delete and restore items.'],
          'approver' => ['color' => '#dbeafe', 'border' => '#93c5fd', 'text' => '#1e40af', 'msg' => '✅ Approver — You can approve/reject pending submissions, edit content, and publish to the public site.'],
          'editor'   => ['color' => '#dcfce7', 'border' => '#86efac', 'text' => '#166534', 'msg' => '✏️ Editor — You can submit and edit content. Your submissions go to Pending for Approver review.'],
        ];
        $rb = $roleBanners[$role] ?? null;
        if ($rb):
        ?>
          <div style="background:<?= $rb['color'] ?>;border:1px solid <?= $rb['border'] ?>;color:<?= $rb['text'] ?>;border-radius:var(--radius);padding:12px 16px;margin-bottom:24px;font-size:13px;">
            <?= $rb['msg'] ?>
          </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stat-grid">
          <div class="stat-card teal">
            <div class="stat-icon teal">📢</div>
            <div class="stat-label">Active Announcements</div>
            <div class="stat-value"><?= $count_ann ?></div>
          </div>
          <div class="stat-card amber">
            <div class="stat-icon amber">📍</div>
            <div class="stat-label">Destinations</div>
            <div class="stat-value"><?= $count_dest ?></div>
          </div>
          <div class="stat-card rose">
            <div class="stat-icon rose">⏳</div>
            <div class="stat-label">Pending Approvals</div>
            <div class="stat-value"><?= $count_pending ?></div>
          </div>
          <div class="stat-card green">
            <div class="stat-icon green">🗂️</div>
            <div class="stat-label">Services</div>
            <div class="stat-value"><?= $count_svc ?></div>
          </div>
        </div>

        <!-- Module Cards -->
        <div class="page-header">
          <div class="page-header-text">
            <h2>Content Modules</h2>
            <p>Manage all public-facing content from here</p>
          </div>
        </div>

        <div class="module-grid">
          <?php
          $modules = [
            ['href' => 'announcements.php', 'icon' => '📢', 'title' => 'Announcements', 'sub' => 'Public notices', 'desc' => 'Publish news, advisories, and updates for the community. Editors submit → Approver publishes.', 'meta' => 'Sorted by newest', 'badge' => 'active'],
            ['href' => 'profiles.php',    'icon' => '👤', 'title' => 'Profiles',      'sub' => 'Officials', 'desc' => 'Manage names, positions, photos, and descriptions for local government leaders.', 'meta' => 'Org chart source', 'badge' => 'complete'],
            ['href' => 'services.php',    'icon' => '🗂️', 'title' => 'Services',      'sub' => 'Municipal services', 'desc' => 'Add searchable services with office and form link for citizens.', 'meta' => 'Searchable by citizens', 'badge' => 'draft'],
            ['href' => 'destinations.php', 'icon' => '📍', 'title' => 'Destinations',  'sub' => 'Tourism spots', 'desc' => 'Upload media, manage visitor analytics, trails, and camping sites.', 'meta' => 'Top 3 shown on homepage', 'badge' => 'active'],
            ['href' => 'faqs.php',        'icon' => '❓', 'title' => 'FAQs',          'sub' => 'Help center', 'desc' => 'Maintain common questions and answers for visitors and residents.', 'meta' => 'Updated weekly', 'badge' => 'complete'],
            ['href' => 'galleries.php',   'icon' => '🖼️', 'title' => 'Galleries',    'sub' => 'Photo gallery', 'desc' => 'Upload and order images for the public-facing carousel and gallery.', 'meta' => 'Sorted by display order', 'badge' => 'complete'],
          ];
          foreach ($modules as $m):
          ?>
            <div class="module-card">
              <div class="module-card-icon"><?= $m['icon'] ?></div>
              <span class="status-badge <?= $m['badge'] ?>"><?= ucfirst($m['badge']) ?></span>
              <h5><?= $m['title'] ?></h5>
              <small><?= $m['sub'] ?></small>
              <p><?= $m['desc'] ?></p>
              <div class="meta"><?= $m['meta'] ?></div>
              <a href="<?= $m['href'] ?>" class="manage-link">Manage →</a>
            </div>
          <?php endforeach; ?>
        </div>

      </div>
    </div>
  </div>
  <script src="../assets/js/admin.js"></script>
</body>

</html>