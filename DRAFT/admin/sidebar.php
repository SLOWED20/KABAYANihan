<?php
// admin/includes/sidebar.php
$active_page = $active_page ?? '';
$role        = $role ?? '';

$nav_items = [
  ['href' => 'dashboard.php',     'label' => 'Dashboard',     'icon' => '🏠'],
  ['href' => 'announcements.php', 'label' => 'Announcements', 'icon' => '📢'],
  ['href' => 'profiles.php',      'label' => 'Profiles',      'icon' => '👤'],
  ['href' => 'services.php',      'label' => 'Services',      'icon' => '🗂️'],
  ['href' => 'destinations.php',  'label' => 'Destinations',  'icon' => '📍'],
  ['href' => 'galleries.php',     'label' => 'Galleries',     'icon' => '🖼️'],
  ['href' => 'faqs.php',          'label' => 'FAQs',          'icon' => '❓'],
  ['href' => 'deleted_logs.php',  'label' => 'Deleted Logs',  'icon' => '🗑️'],
];

$workflow_items = [
  ['href' => 'pending.php',       'label' => 'Pending',       'icon' => '⏳'],
  ['href' => 'approved_logs.php', 'label' => 'Approved Logs', 'icon' => '✅'],
];
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
      </svg>
    </div>
    <div class="brand-name">Municipal CMS</div>
    <div class="brand-sub">Kabayan Admin</div>
  </div>

  <div class="sidebar-role">
    <div class="role-avatar"><?= strtoupper(substr($role, 0, 2)) ?></div>
    <div class="role-info">
      <div class="role-label">Logged in as</div>
      <div class="role-name"><?= ucfirst(htmlspecialchars($role)) ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Content</div>
    <?php foreach ($nav_items as $item): ?>
      <a href="<?= $item['href'] ?>" class="nav-link <?= ($active_page === $item['href']) ? 'active' : '' ?>">
        <span style="font-size:15px;line-height:1;"><?= $item['icon'] ?></span>
        <?= $item['label'] ?>
      </a>
    <?php endforeach; ?>

    <?php if ($role === 'admin' || $role === 'approver'): ?>
      <div class="nav-section-label" style="margin-top:8px;">Workflow</div>
      <?php foreach ($workflow_items as $item): ?>
        <a href="<?= $item['href'] ?>" class="nav-link <?= ($active_page === $item['href']) ? 'active' : '' ?>">
          <span style="font-size:15px;line-height:1;"><?= $item['icon'] ?></span>
          <?= $item['label'] ?>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
      <div class="nav-section-label" style="margin-top:8px;">Admin</div>
      <a href="users.php" class="nav-link <?= ($active_page === 'users.php') ? 'active' : '' ?>">
        <span style="font-size:15px;line-height:1;">👥</span>
        User Management
      </a>
    <?php endif; ?>

    <div style="margin-top:auto;padding-top:16px;border-top:1px solid rgba(255,255,255,.06);">
      <a href="logout.php" class="nav-link danger">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        Logout
      </a>
    </div>
  </nav>
</aside>