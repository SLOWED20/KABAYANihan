<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['role'])) {
  header("Location: login.php");
  exit;
}
$role        = $_SESSION['role'];
$active_page = 'profiles.php';

// ── CREATE ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
  $name     = trim($_POST['name']);
  $position = trim($_POST['position']);
  $category = trim($_POST['category']);
  $desc     = trim($_POST['description']);
  $image    = $_FILES['image']['name'];
  if ($image) {
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . basename($image));
  }
  $stmt = $conn->prepare("INSERT INTO profiles (name, position, category, description, image) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $name, $position, $category, $desc, $image);
  $stmt->execute();
  header("Location: profiles.php?msg=created");
  exit;
}

// ── EDIT ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
  $id       = intval($_POST['edit_id']);
  $name     = trim($_POST['name']);
  $position = trim($_POST['position']);
  $category = trim($_POST['category']);
  $desc     = trim($_POST['description']);
  $image    = $_FILES['image']['name'];
  if ($image) {
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . basename($image));
    $stmt = $conn->prepare("UPDATE profiles SET name=?,position=?,category=?,description=?,image=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $position, $category, $desc, $image, $id);
  } else {
    $stmt = $conn->prepare("UPDATE profiles SET name=?,position=?,category=?,description=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $position, $category, $desc, $id);
  }
  $stmt->execute();
  header("Location: profiles.php?msg=updated");
  exit;
}

// ── DELETE ─────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $stmt = $conn->prepare("DELETE FROM profiles WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  header("Location: profiles.php?msg=deleted");
  exit;
}

// ── FETCH & GROUP ──────────────────────────────────────────────────────
$result   = $conn->query("SELECT * FROM profiles ORDER BY name ASC");
$all_data = $result->fetch_all(MYSQLI_ASSOC);

$mayor      = array_values(array_filter($all_data, fn($p) => strtolower($p['position'] ?? '') === 'mayor'));
$vice_mayor = array_values(array_filter($all_data, fn($p) => strtolower($p['position'] ?? '') === 'vice mayor'));
$councilors = array_values(array_filter($all_data, fn($p) => strtolower($p['category'] ?? '') === 'councilor'));
$offices    = array_values(array_filter($all_data, fn($p) => strtolower($p['category'] ?? '') === 'office'));
$barangays  = array_values(array_filter($all_data, fn($p) => strtolower($p['category'] ?? '') === 'barangay'));

$categories = ['mayor', 'vice mayor', 'councilor', 'office', 'barangay'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Profiles — Municipal CMS</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    /* ── Org Chart Shell ── */
    .org-preview-banner {
      background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
      padding: 22px 28px;
      border-radius: var(--radius-lg);
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 28px;
      gap: 16px;
      flex-wrap: wrap;
    }

    .org-preview-banner h2 {
      margin: 0;
      color: #fff;
      font-size: 17px;
      font-weight: 700;
    }

    .org-preview-banner p {
      margin: 4px 0 0;
      color: rgba(255,255,255,.55);
      font-size: 12.5px;
    }

    /* ── Level labels (matching public side) ── */
    .level-label {
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 2px;
      font-size: 11px;
      color: var(--text-3);
      text-align: center;
      position: relative;
      margin-bottom: 20px;
      padding-bottom: 12px;
    }

    .level-label::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 40px;
      height: 2.5px;
      background: var(--teal);
      border-radius: 2px;
    }

    /* ── Org section wrappers ── */
    .org-section {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 28px 24px 20px;
      margin-bottom: 20px;
      box-shadow: var(--shadow-sm);
      overflow-x: auto;
    }

    /* ── Chart Cards (mirrors public) ── */
    .chart-card {
      background: var(--white);
      border-radius: 12px;
      padding: 20px 16px;
      border: 1px solid var(--border);
      text-align: center;
      position: relative;
      transition: box-shadow .2s, transform .2s, border-color .2s;
      box-shadow: 0 2px 10px rgba(0,0,0,.05);
    }

    .chart-card:hover {
      transform: translateY(-3px);
      border-color: var(--teal);
      box-shadow: 0 8px 24px rgba(0,0,0,.10);
    }

    .chart-card img.avatar-lg {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #fff;
      outline: 2px solid var(--teal);
      margin-bottom: 12px;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }

    .chart-card img.avatar-sm {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      border: 1px solid var(--border);
      margin-bottom: 8px;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }

    .avatar-placeholder-lg {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      background: var(--surface-2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      margin: 0 auto 12px;
      outline: 2px solid var(--border);
    }

    .avatar-placeholder-sm {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: var(--surface-2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin: 0 auto 8px;
    }

    /* ── Layout rows (mirrors public) ── */
    .executive-row {
      display: flex;
      gap: 30px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .executive-row .chart-card {
      width: 220px;
      flex-shrink: 0;
    }

    .councilor-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      max-width: 900px;
      margin: 0 auto;
    }

    .office-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      max-width: 900px;
      margin: 0 auto;
    }

    .office-badge-admin {
      background: #0f172a;
      color: #fff;
      padding: 12px 14px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 13px;
      text-align: center;
      position: relative;
      transition: background .2s;
    }

    .office-badge-admin:hover {
      background: var(--teal);
    }

    .barangay-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 12px;
      max-width: 1100px;
      margin: 0 auto;
    }

    /* ── Admin Action Overlay ── */
    .card-actions {
      position: absolute;
      top: 6px;
      right: 6px;
      display: flex;
      gap: 4px;
      opacity: 0;
      transition: opacity .18s;
      z-index: 10;
    }

    .chart-card:hover .card-actions,
    .office-badge-admin:hover .office-actions {
      opacity: 1;
    }

    .office-actions {
      position: absolute;
      top: 4px;
      right: 4px;
      display: flex;
      gap: 3px;
      opacity: 0;
      transition: opacity .18s;
      z-index: 10;
    }

    .office-badge-admin {
      position: relative;
    }

    .action-btn {
      width: 24px;
      height: 24px;
      border-radius: 5px;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      cursor: pointer;
      text-decoration: none;
      line-height: 1;
      font-weight: 700;
    }

    .action-edit   { background: #fef3c7; color: #92400e; }
    .action-delete { background: #fee2e2; color: #991b1b; }

    .action-edit:hover   { background: #d97706; color: #fff; }
    .action-delete:hover { background: #dc2626; color: #fff; }

    /* ── Role Pill (inside card) ── */
    .role-pill {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 99px;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .6px;
      text-transform: uppercase;
      margin-top: 6px;
    }

    .role-pill.mayor     { background: #dbeafe; color: #1e40af; }
    .role-pill.vice      { background: #e0f2fe; color: #0369a1; }
    .role-pill.councilor { background: #dcfce7; color: #166534; }
    .role-pill.barangay  { background: #fdf4ff; color: #7e22ce; }

    /* ── Empty state ── */
    .empty-slot {
      border: 2px dashed var(--border);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      color: var(--text-4);
      font-size: 12px;
    }

    /* ── Section connector line (decorative) ── */
    .connector {
      width: 2px;
      height: 24px;
      background: linear-gradient(to bottom, var(--border), transparent);
      margin: 0 auto;
    }
  </style>
</head>

<body>
  <div class="admin-shell">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">

      <div class="topbar">
        <div class="topbar-title">Profiles</div>
        <div class="topbar-actions">
          <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#createForm">
            + Add Profile
          </button>
        </div>
      </div>

      <div class="page-body">

        <?php if (isset($_GET['msg'])): ?>
          <?php if ($_GET['msg'] === 'created'): ?><div class="alert alert-success">Profile created successfully.</div><?php endif; ?>
          <?php if ($_GET['msg'] === 'updated'): ?><div class="alert alert-success">Profile updated successfully.</div><?php endif; ?>
          <?php if ($_GET['msg'] === 'deleted'): ?><div class="alert alert-warning">Profile deleted.</div><?php endif; ?>
        <?php endif; ?>

        <!-- ── CREATE FORM ─────────────────────────────────────────── -->
        <div class="collapse mb-4" id="createForm">
          <div class="card">
            <div class="card-header">Add New Profile</div>
            <div class="card-body">
              <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Juan dela Cruz" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Position <small class="text-muted">(e.g. Mayor, Vice Mayor)</small></label>
                    <input type="text" name="position" class="form-control">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control" required>
                      <option value="" disabled selected>Select category…</option>
                      <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>"><?= ucwords($cat) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label">Description <small class="text-muted">(used as location label for Barangays)</small></label>
                    <textarea name="description" class="form-control" rows="2" required></textarea>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Photo</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                  </div>
                </div>
                <div style="display:flex;gap:8px;margin-top:16px;">
                  <button type="submit" name="create" class="btn btn-primary">Save</button>
                  <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#createForm">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- ── ORG CHART PREVIEW BANNER ───────────────────────────── -->
        <div class="org-preview-banner">
          <div>
            <h2>📊 Organizational Chart Preview</h2>
            <p>This mirrors the public-facing org chart. Hover any card to edit or delete.</p>
          </div>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <span style="font-size:12px;color:rgba(255,255,255,.5);">
              <?= count($all_data) ?> total profile<?= count($all_data) !== 1 ? 's' : '' ?>
            </span>
          </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             LEVEL 1: Executive Leadership
             ══════════════════════════════════════════════════════════ -->
        <div class="org-section">
          <div class="level-label">Executive Leadership</div>
          <div class="executive-row">

            <?php if (!empty($mayor)): ?>
              <?php foreach ($mayor as $p): ?>
                <div class="chart-card">
                  <div class="card-actions">
                    <button class="action-btn action-edit" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>" title="Edit">✏</button>
                    <a class="action-btn action-delete" href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete <?= htmlspecialchars($p['name']) ?>?')" title="Delete">✕</a>
                  </div>
                  <?php if ($p['image']): ?>
                    <img class="avatar-lg" src="../uploads/<?= htmlspecialchars($p['image']) ?>" alt="Mayor">
                  <?php else: ?>
                    <div class="avatar-placeholder-lg">👤</div>
                  <?php endif; ?>
                  <h5 style="font-size:14px;font-weight:700;margin:0 0 4px;"><?= htmlspecialchars($p['name']) ?></h5>
                  <span class="role-pill mayor">Mayor</span>
                  <?php if ($p['description']): ?>
                    <p style="font-size:11.5px;color:var(--text-3);margin:6px 0 0;"><?= htmlspecialchars($p['description']) ?></p>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-slot" style="width:220px;">
                <div style="font-size:28px;margin-bottom:6px;">👤</div>
                No Mayor set
                <div style="margin-top:8px;">
                  <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#createForm">+ Add</button>
                </div>
              </div>
            <?php endif; ?>

            <?php if (!empty($vice_mayor)): ?>
              <?php foreach ($vice_mayor as $p): ?>
                <div class="chart-card">
                  <div class="card-actions">
                    <button class="action-btn action-edit" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>" title="Edit">✏</button>
                    <a class="action-btn action-delete" href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete <?= htmlspecialchars($p['name']) ?>?')" title="Delete">✕</a>
                  </div>
                  <?php if ($p['image']): ?>
                    <img class="avatar-lg" src="../uploads/<?= htmlspecialchars($p['image']) ?>" alt="Vice Mayor">
                  <?php else: ?>
                    <div class="avatar-placeholder-lg">👤</div>
                  <?php endif; ?>
                  <h5 style="font-size:14px;font-weight:700;margin:0 0 4px;"><?= htmlspecialchars($p['name']) ?></h5>
                  <span class="role-pill vice">Vice Mayor</span>
                  <?php if ($p['description']): ?>
                    <p style="font-size:11.5px;color:var(--text-3);margin:6px 0 0;"><?= htmlspecialchars($p['description']) ?></p>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-slot" style="width:220px;">
                <div style="font-size:28px;margin-bottom:6px;">👤</div>
                No Vice Mayor set
                <div style="margin-top:8px;">
                  <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#createForm">+ Add</button>
                </div>
              </div>
            <?php endif; ?>

          </div>
        </div>

        <div class="connector"></div>

        <!-- ══════════════════════════════════════════════════════════
             LEVEL 2: Councilors
             ══════════════════════════════════════════════════════════ -->
        <div class="org-section">
          <div class="level-label">Sangguniang Bayan (Councilors)</div>
          <?php if (!empty($councilors)): ?>
            <div class="councilor-grid">
              <?php foreach ($councilors as $p): ?>
                <div class="chart-card">
                  <div class="card-actions">
                    <button class="action-btn action-edit" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>" title="Edit">✏</button>
                    <a class="action-btn action-delete" href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete <?= htmlspecialchars($p['name']) ?>?')" title="Delete">✕</a>
                  </div>
                  <?php if ($p['image']): ?>
                    <img class="avatar-sm" src="../uploads/<?= htmlspecialchars($p['image']) ?>" alt="">
                  <?php else: ?>
                    <div class="avatar-placeholder-sm">👤</div>
                  <?php endif; ?>
                  <h6 style="font-size:12.5px;font-weight:700;margin:0 0 2px;"><?= htmlspecialchars($p['name']) ?></h6>
                  <span class="role-pill councilor">Councilor</span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="empty-slot">
              No councilors added yet.
              <button class="btn btn-sm btn-primary" style="margin-left:8px;" data-bs-toggle="collapse" data-bs-target="#createForm">+ Add Councilor</button>
            </div>
          <?php endif; ?>
        </div>

        <div class="connector"></div>

        <!-- ══════════════════════════════════════════════════════════
             LEVEL 3: Municipal Offices
             ══════════════════════════════════════════════════════════ -->
        <div class="org-section">
          <div class="level-label">Municipal Offices &amp; Departments</div>
          <?php if (!empty($offices)): ?>
            <div class="office-grid">
              <?php foreach ($offices as $p): ?>
                <div class="office-badge-admin">
                  <div class="office-actions">
                    <button class="action-btn action-edit" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>" title="Edit" style="background:rgba(255,255,255,.15);color:#fff;">✏</button>
                    <a class="action-btn action-delete" href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete <?= htmlspecialchars($p['name']) ?>?')" title="Delete" style="background:rgba(239,68,68,.3);color:#fff;">✕</a>
                  </div>
                  <div style="padding-right:36px;">
                    <?= htmlspecialchars($p['name']) ?>
                    <?php if ($p['description']): ?>
                      <div style="font-size:10.5px;font-weight:400;opacity:.65;margin-top:2px;"><?= htmlspecialchars($p['description']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="empty-slot">
              No offices added yet.
              <button class="btn btn-sm btn-primary" style="margin-left:8px;" data-bs-toggle="collapse" data-bs-target="#createForm">+ Add Office</button>
            </div>
          <?php endif; ?>
        </div>

        <div class="connector"></div>

        <!-- ══════════════════════════════════════════════════════════
             LEVEL 4: Barangays
             ══════════════════════════════════════════════════════════ -->
        <div class="org-section">
          <div class="level-label">The 13 Barangays of Kabayan</div>
          <?php if (!empty($barangays)): ?>
            <div class="barangay-grid">
              <?php foreach ($barangays as $p): ?>
                <div class="chart-card">
                  <div class="card-actions">
                    <button class="action-btn action-edit" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>" title="Edit">✏</button>
                    <a class="action-btn action-delete" href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete <?= htmlspecialchars($p['name']) ?>?')" title="Delete">✕</a>
                  </div>
                  <?php if ($p['image']): ?>
                    <img class="avatar-sm" src="../uploads/<?= htmlspecialchars($p['image']) ?>" alt="">
                  <?php else: ?>
                    <div class="avatar-placeholder-sm">🏘️</div>
                  <?php endif; ?>
                  <h6 style="font-size:12px;font-weight:700;margin:0 0 2px;"><?= htmlspecialchars($p['name']) ?></h6>
                  <span class="role-pill barangay">Barangay</span>
                  <?php if ($p['description']): ?>
                    <p style="font-size:11px;color:var(--text-3);margin:5px 0 0;">
                      📍 <?= htmlspecialchars($p['description']) ?>
                    </p>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="empty-slot">
              No barangays added yet.
              <button class="btn btn-sm btn-primary" style="margin-left:8px;" data-bs-toggle="collapse" data-bs-target="#createForm">+ Add Barangay</button>
            </div>
          <?php endif; ?>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             EDIT MODALS (one per profile)
             ══════════════════════════════════════════════════════════ -->
        <?php foreach ($all_data as $row):
          $cat     = strtolower($row['category'] ?? '');
          $cat_css = 'cat-' . str_replace(' ', '-', $cat);
        ?>
          <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                    <div class="row g-3">
                      <div class="col-md-4">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Position</label>
                        <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($row['position']) ?>">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-control" required>
                          <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= strtolower($row['category'] ?? '') === $cat ? 'selected' : '' ?>>
                              <?= ucwords($cat) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-8">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" required><?= htmlspecialchars($row['description']) ?></textarea>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Photo <small class="text-muted">(leave blank to keep current)</small></label>
                        <?php if ($row['image']): ?>
                          <div style="margin-bottom:8px;">
                            <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" width="54" height="54"
                              style="object-fit:cover;border-radius:50%;border:2px solid var(--border);">
                          </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*">
                      </div>
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
        <?php endforeach; ?>

      </div><!-- /page-body -->
    </div><!-- /main-content -->
  </div><!-- /admin-shell -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/admin.js"></script>
</body>

</html>
