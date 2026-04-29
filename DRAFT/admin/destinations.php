<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}
$role = $_SESSION['role'];
$active_page = 'destinations.php';

// ── TOGGLE OPEN/CLOSED ──────────────────────────────────────────────────────
if (isset($_GET['toggle']) && ($role === 'admin' || $role === 'approver')) {
    $id = intval($_GET['toggle']);
    $s = $conn->prepare("UPDATE destinations SET is_open = NOT is_open WHERE id=?");
    $s->bind_param("i", $id);
    $s->execute();
    header("Location: destinations.php");
    exit;
}

// ── DELETE → LOG ─────────────────────────────────────────────────────────────
if (isset($_GET['delete']) && ($role === 'admin' || $role === 'approver')) {
    $id = intval($_GET['delete']);
    // Soft delete
    $s = $conn->prepare("UPDATE destinations SET status='deleted' WHERE id=?");
    $s->bind_param("i", $id);
    $s->execute();
    // Log it
    $log = $conn->prepare("INSERT INTO deleted_logs (module_name, item_id, deleted_by) VALUES ('destinations', ?, ?)");
    $log->bind_param("is", $id, $_SESSION['role']);
    $log->execute();
    header("Location: destinations.php");
    exit;
}

// ── APPROVE → LOG ─────────────────────────────────────────────────────────────
if (isset($_GET['approve']) && ($role === 'admin' || $role === 'approver')) {
    $id = intval($_GET['approve']);
    $s = $conn->prepare("UPDATE destinations SET status='active' WHERE id=?");
    $s->bind_param("i", $id);
    $s->execute();
    $log = $conn->prepare("INSERT INTO approved_logs (module_name, item_id, approved_by) VALUES ('destinations', ?, ?)");
    $log->bind_param("is", $id, $_SESSION['role']);
    $log->execute();
    header("Location: destinations.php");
    exit;
}

// ── CREATE ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $name        = trim($_POST['name']);
    $desc        = trim($_POST['description']);
    $coord       = trim($_POST['coordinators']);
    $coord_links = trim($_POST['coordinator_links']);
    $homestay    = trim($_POST['homestay_links']);
    $visitors    = intval($_POST['analytics_visitors']);
    $forecast    = intval($visitors * 1.15);
    $is_open     = isset($_POST['is_open']) ? 1 : 0;

    // Trails JSON
    $trails = [];
    if (!empty($_POST['trail_name'])) {
        foreach ($_POST['trail_name'] as $i => $tname) {
            if (trim($tname) !== '') {
                $trails[] = [
                    'name'      => trim($tname),
                    'jumpoff'   => trim($_POST['trail_jumpoff'][$i] ?? ''),
                    'difficulty' => trim($_POST['trail_difficulty'][$i] ?? ''),
                    'duration'  => trim($_POST['trail_duration'][$i] ?? ''),
                ];
            }
        }
    }

    // Camping sites JSON
    $camping = [];
    if (!empty($_POST['camp_name'])) {
        foreach ($_POST['camp_name'] as $i => $cname) {
            if (trim($cname) !== '') {
                $camp_img = '';
                if (!empty($_FILES['camp_image']['name'][$i])) {
                    $camp_img = basename($_FILES['camp_image']['name'][$i]);
                    move_uploaded_file($_FILES['camp_image']['tmp_name'][$i], "../uploads/" . $camp_img);
                }
                $camping[] = [
                    'name'     => trim($cname),
                    'location' => trim($_POST['camp_location'][$i] ?? ''),
                    'capacity' => trim($_POST['camp_capacity'][$i] ?? ''),
                    'image'    => $camp_img,
                ];
            }
        }
    }

    // Bullet descriptions
    $bullets = array_filter(array_map('trim', explode("\n", $_POST['bullet_descriptions'] ?? '')));

    // Preview image
    $preview = '';
    if (!empty($_FILES['preview_image']['name'])) {
        $preview = basename($_FILES['preview_image']['name']);
        move_uploaded_file($_FILES['preview_image']['tmp_name'], "../uploads/" . $preview);
    }

    // Multiple media (images/videos)
    $media_files = [];
    if (!empty($_FILES['media_links']['name'][0])) {
        foreach ($_FILES['media_links']['name'] as $k => $fname) {
            if ($fname) {
                $safe = basename($fname);
                move_uploaded_file($_FILES['media_links']['tmp_name'][$k], "../uploads/" . $safe);
                $media_files[] = $safe;
            }
        }
    }

    $s = $conn->prepare(
        "INSERT INTO destinations
         (name, description, bullet_descriptions, preview_image, media_links,
          coordinators, coordinator_links, homestay_links,
          analytics_visitors, forecast_traffic,
          trails, camping_sites, is_open, status)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')"
    );
    $desc_json    = json_encode($bullets);
    $media_json   = json_encode($media_files);
    $trails_json  = json_encode($trails);
    $camping_json = json_encode($camping);
    $s->bind_param(
        "ssssssssiissi",
        $name,
        $desc,
        $desc_json,
        $preview,
        $media_json,
        $coord,
        $coord_links,
        $homestay,
        $visitors,
        $forecast,
        $trails_json,
        $camping_json,
        $is_open
    );
    $s->execute();
    $new_id = $conn->insert_id;

    // Log as pending
    $p = $conn->prepare("INSERT INTO pending_approval (module_name, item_id, submitted_by, status) VALUES ('destinations', ?, ?, 'pending')");
    $p->bind_param("is", $new_id, $_SESSION['role']);
    $p->execute();

    header("Location: destinations.php");
    exit;
}

// ── EDIT ───────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id      = intval($_POST['edit_id']);
    $name    = trim($_POST['name']);
    $desc    = trim($_POST['description']);
    $coord   = trim($_POST['coordinators']);
    $coord_links = trim($_POST['coordinator_links']);
    $homestay = trim($_POST['homestay_links']);
    $visitors = intval($_POST['analytics_visitors']);
    $forecast = intval($visitors * 1.15);
    $is_open = isset($_POST['is_open']) ? 1 : 0;

    $trails = [];
    if (!empty($_POST['trail_name'])) {
        foreach ($_POST['trail_name'] as $i => $tname) {
            if (trim($tname) !== '') {
                $trails[] = [
                    'name'      => trim($tname),
                    'jumpoff'   => trim($_POST['trail_jumpoff'][$i] ?? ''),
                    'difficulty' => trim($_POST['trail_difficulty'][$i] ?? ''),
                    'duration'  => trim($_POST['trail_duration'][$i] ?? ''),
                ];
            }
        }
    }

    $camping = [];
    if (!empty($_POST['camp_name'])) {
        foreach ($_POST['camp_name'] as $i => $cname) {
            if (trim($cname) !== '') {
                $camp_img = $_POST['camp_existing_image'][$i] ?? '';
                if (!empty($_FILES['camp_image']['name'][$i])) {
                    $camp_img = basename($_FILES['camp_image']['name'][$i]);
                    move_uploaded_file($_FILES['camp_image']['tmp_name'][$i], "../uploads/" . $camp_img);
                }
                $camping[] = [
                    'name'     => trim($cname),
                    'location' => trim($_POST['camp_location'][$i] ?? ''),
                    'capacity' => trim($_POST['camp_capacity'][$i] ?? ''),
                    'image'    => $camp_img,
                ];
            }
        }
    }

    $bullets = array_filter(array_map('trim', explode("\n", $_POST['bullet_descriptions'] ?? '')));

    // Get existing data for images
    $ex_stmt = $conn->prepare("SELECT preview_image, media_links FROM destinations WHERE id=?");
    $ex_stmt->bind_param("i", $id);
    $ex_stmt->execute();
    $ex = $ex_stmt->get_result()->fetch_assoc();

    $preview = $ex['preview_image'];
    if (!empty($_FILES['preview_image']['name'])) {
        $preview = basename($_FILES['preview_image']['name']);
        move_uploaded_file($_FILES['preview_image']['tmp_name'], "../uploads/" . $preview);
    }

    $media_files = json_decode($ex['media_links'] ?? '[]', true) ?: [];
    if (!empty($_FILES['media_links']['name'][0])) {
        $media_files = []; // replace all
        foreach ($_FILES['media_links']['name'] as $k => $fname) {
            if ($fname) {
                $safe = basename($fname);
                move_uploaded_file($_FILES['media_links']['tmp_name'][$k], "../uploads/" . $safe);
                $media_files[] = $safe;
            }
        }
    }

    $s = $conn->prepare(
        "UPDATE destinations SET
         name=?, description=?, bullet_descriptions=?, preview_image=?, media_links=?,
         coordinators=?, coordinator_links=?, homestay_links=?,
         analytics_visitors=?, forecast_traffic=?,
         trails=?, camping_sites=?, is_open=?
         WHERE id=?"
    );
    $desc_json    = json_encode(array_values($bullets));
    $media_json   = json_encode($media_files);
    $trails_json  = json_encode($trails);
    $camping_json = json_encode($camping);
    $s->bind_param(
        "ssssssssiissii",
        $name,
        $desc,
        $desc_json,
        $preview,
        $media_json,
        $coord,
        $coord_links,
        $homestay,
        $visitors,
        $forecast,
        $trails_json,
        $camping_json,
        $is_open,
        $id
    );
    $s->execute();
    header("Location: destinations.php");
    exit;
}

// ── FETCH ──────────────────────────────────────────────────────────────────────
$result = $conn->query("SELECT * FROM destinations WHERE status != 'deleted' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Destinations — Municipal CMS</title>
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

        /* Status badge */
        .badge-open {
            background: #f0fdf4;
            color: #166534;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .badge-closed {
            background: #fff1f2;
            color: #be123c;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .badge-pending {
            background: #fef9c3;
            color: #854d0e;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
        }

        /* Analytics mini bar */
        .analytics-bar {
            background: #f1f5f9;
            border-radius: 4px;
            height: 6px;
            margin-top: 4px;
            overflow: hidden;
        }

        .analytics-fill {
            background: var(--teal, #0d9488);
            height: 6px;
            border-radius: 4px;
            transition: width .4s;
        }

        /* Media grid in modal */
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .media-grid img,
        .media-grid video {
            width: 100%;
            border-radius: 8px;
            object-fit: cover;
            max-height: 150px;
        }

        /* Trail & camp cards */
        .trail-card,
        .camp-card {
            background: var(--surface, #f8fafc);
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 8px;
        }

        .trail-card h6,
        .camp-card h6 {
            margin: 0 0 4px;
            font-size: 13.5px;
            font-weight: 700;
        }

        .trail-card p,
        .camp-card p {
            margin: 0;
            font-size: 12px;
            color: #64748b;
        }

        /* Repeater rows */
        .repeater-row {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            position: relative;
        }

        .repeater-row .remove-row {
            position: absolute;
            top: 8px;
            right: 8px;
            background: none;
            border: none;
            color: #ef4444;
            font-size: 18px;
            cursor: pointer;
            line-height: 1;
        }

        /* Toggle switch */
        .toggle-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .toggle-label {
            font-size: 13px;
            font-weight: 600;
        }

        /* Stat pills */
        .stat-pill {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 16px;
            border-radius: 12px;
            min-width: 80px;
        }

        /* View modal media */
        .view-media-wrap {
            position: relative;
        }

        .view-media-wrap video,
        .view-media-wrap img {
            width: 100%;
            max-height: 300px;
            object-fit: contain;
            border-radius: 10px;
            background: #000;
        }
    </style>
</head>

<body>
    <div class="admin-shell">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="topbar">
                <div class="topbar-title">Destinations</div>
                <div class="topbar-actions">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#createForm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Destination
                    </button>
                </div>
            </div>

            <div class="page-body">

                <!-- ═══════════════ CREATE FORM ═══════════════ -->
                <div class="collapse mb-4" id="createForm">
                    <div class="card">
                        <div class="card-header fw-bold">Add New Destination</div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">

                                <!-- Basic Info -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Name</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Visitor Count</label>
                                        <input type="number" name="analytics_visitors" class="form-control" value="0">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Overview Description</label>
                                        <textarea name="description" class="form-control" rows="2" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Bullet Highlights <small class="text-muted">(one per line)</small></label>
                                        <textarea name="bullet_descriptions" class="form-control" rows="3" placeholder="Breathtaking sunrise views&#10;Rich biodiversity&#10;Traditional Igorot culture"></textarea>
                                    </div>
                                </div>

                                <!-- Open/Closed toggle -->
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_open" id="createIsOpen" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="createIsOpen">Open to Public</label>
                                </div>

                                <!-- Media -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Preview Image</label>
                                        <input type="file" name="preview_image" class="form-control" accept="image/*">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Gallery Media <small class="text-muted">(images/videos, multiple)</small></label>
                                        <input type="file" name="media_links[]" class="form-control" multiple accept="image/*,video/*">
                                    </div>
                                </div>

                                <!-- Coordinators & Homestay -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Coordinators</label>
                                        <input type="text" name="coordinators" class="form-control" placeholder="Names separated by comma">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Coordinator Contact Links</label>
                                        <input type="text" name="coordinator_links" class="form-control" placeholder="https://fb.com/... or phone">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Homestay Links</label>
                                        <input type="text" name="homestay_links" class="form-control" placeholder="Booking link or address">
                                    </div>
                                </div>

                                <!-- Trails -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">🥾 Trails</label>
                                    <div id="trail-repeater-create">
                                        <div class="repeater-row">
                                            <button type="button" class="remove-row" onclick="this.closest('.repeater-row').remove()">×</button>
                                            <div class="row g-2">
                                                <div class="col-md-3"><input type="text" name="trail_name[]" class="form-control form-control-sm" placeholder="Trail name"></div>
                                                <div class="col-md-3"><input type="text" name="trail_jumpoff[]" class="form-control form-control-sm" placeholder="Jump-off point"></div>
                                                <div class="col-md-3">
                                                    <select name="trail_difficulty[]" class="form-control form-control-sm">
                                                        <option value="">Difficulty</option>
                                                        <option>Easy</option>
                                                        <option>Moderate</option>
                                                        <option>Difficult</option>
                                                        <option>Expert</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><input type="text" name="trail_duration[]" class="form-control form-control-sm" placeholder="Duration (e.g. 3-4 hrs)"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addTrailRow('trail-repeater-create')">+ Add Trail</button>
                                </div>

                                <!-- Camping Sites -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">⛺ Camping Sites</label>
                                    <div id="camp-repeater-create">
                                        <div class="repeater-row">
                                            <button type="button" class="remove-row" onclick="this.closest('.repeater-row').remove()">×</button>
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-3"><input type="text" name="camp_name[]" class="form-control form-control-sm" placeholder="Site name"></div>
                                                <div class="col-md-3"><input type="text" name="camp_location[]" class="form-control form-control-sm" placeholder="Location/coordinates"></div>
                                                <div class="col-md-3"><input type="text" name="camp_capacity[]" class="form-control form-control-sm" placeholder="Capacity (e.g. 20 tents)"></div>
                                                <div class="col-md-3"><input type="file" name="camp_image[]" class="form-control form-control-sm" accept="image/*"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addCampRow('camp-repeater-create')">+ Add Camping Site</button>
                                </div>

                                <div style="display:flex;gap:8px;">
                                    <button type="submit" name="create" class="btn btn-primary">Save & Submit</button>
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#createForm">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════ TABLE ═══════════════ -->
                <div class="table-wrap">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Preview</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Visitors</th>
                                <th>Forecast</th>
                                <th>Open?</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()):
                                $bullets  = json_decode($row['bullet_descriptions'] ?? '[]', true) ?: [];
                                $trails   = json_decode($row['trails'] ?? '[]', true) ?: [];
                                $camping  = json_decode($row['camping_sites'] ?? '[]', true) ?: [];
                                $media    = json_decode($row['media_links'] ?? '[]', true) ?: [];
                                $visitors = intval($row['analytics_visitors']);
                                $forecast = intval($row['forecast_traffic']);
                                $maxBar   = max($forecast, 1);
                            ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td>
                                        <?php if ($row['preview_image']): ?>
                                            <img src="../uploads/<?= htmlspecialchars($row['preview_image']) ?>"
                                                width="72" height="52" style="object-fit:cover;border-radius:8px;">
                                        <?php else: ?>
                                            <div style="width:72px;height:52px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:20px;">🏔️</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['name']) ?></strong>
                                        <div style="font-size:11.5px;color:#64748b;margin-top:1px;">
                                            <?= count($trails) ?> trail<?= count($trails) != 1 ? 's' : '' ?> &bull;
                                            <?= count($camping) ?> camp site<?= count($camping) != 1 ? 's' : '' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $st = $row['status'] ?? 'active';
                                        $stColors = ['active' => 'background:#f0fdf4;color:#166534', 'pending' => 'background:#fef9c3;color:#854d0e', 'deleted' => 'background:#fee2e2;color:#991b1b'];
                                        ?>
                                        <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;<?= $stColors[$st] ?? '' ?>"><?= ucfirst($st) ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight:700;color:#0d9488;"><?= number_format($visitors) ?></div>
                                        <div class="analytics-bar" title="Visitors vs forecast">
                                            <div class="analytics-fill" style="width:<?= min(100, round($visitors / $maxBar * 100)) ?>%"></div>
                                        </div>
                                    </td>
                                    <td style="font-size:13px;color:#64748b;"><?= number_format($forecast) ?></td>
                                    <td>
                                        <?php if ($role === 'admin' || $role === 'approver'): ?>
                                            <a href="?toggle=<?= $row['id'] ?>" title="Click to toggle"
                                                style="text-decoration:none;">
                                                <span class="<?= $row['is_open'] ? 'badge-open' : 'badge-closed' ?>">
                                                    <?= $row['is_open'] ? '✓ Open' : '✕ Closed' ?>
                                                </span>
                                            </a>
                                        <?php else: ?>
                                            <span class="<?= $row['is_open'] ? 'badge-open' : 'badge-closed' ?>">
                                                <?= $row['is_open'] ? 'Open' : 'Closed' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <!-- View -->
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['id'] ?>">View</button>
                                        <!-- Edit -->
                                        <?php if ($role === 'admin' || $role === 'approver'): ?>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                                        <?php endif; ?>
                                        <!-- Approve (if pending) -->
                                        <?php if (($role === 'admin' || $role === 'approver') && $row['status'] === 'pending'): ?>
                                            <a href="?approve=<?= $row['id'] ?>" class="btn btn-sm btn-success">Approve</a>
                                        <?php endif; ?>
                                        <!-- Delete -->
                                        <?php if ($role === 'admin' || $role === 'approver'): ?>
                                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this destination? It will be logged.')">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- ═══ VIEW MODAL ═══ -->
                                <div class="modal fade" id="viewModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <div>
                                                    <h5 class="modal-title mb-0"><?= htmlspecialchars($row['name']) ?></h5>
                                                    <small class="text-muted">
                                                        <?= $row['is_open'] ? '✓ Open to public' : '✕ Closed to public' ?>
                                                        &bull; <?= ucfirst($row['status'] ?? 'active') ?>
                                                    </small>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-4">

                                                    <!-- Media Carousel -->
                                                    <?php if (!empty($media)): ?>
                                                        <div class="col-12">
                                                            <div id="carousel<?= $row['id'] ?>" class="carousel slide" data-bs-ride="carousel">
                                                                <div class="carousel-indicators">
                                                                    <?php foreach ($media as $mi => $mf): ?>
                                                                        <button type="button" data-bs-target="#carousel<?= $row['id'] ?>"
                                                                            data-bs-slide-to="<?= $mi ?>"
                                                                            <?= $mi === 0 ? 'class="active"' : '' ?>></button>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                <div class="carousel-inner" style="border-radius:12px;background:#000;max-height:320px;overflow:hidden;">
                                                                    <?php foreach ($media as $mi => $mfile): ?>
                                                                        <div class="carousel-item <?= $mi === 0 ? 'active' : '' ?>">
                                                                            <?php if (preg_match('/\.(mp4|webm|mov)$/i', $mfile)): ?>
                                                                                <video class="d-block mx-auto" controls
                                                                                    style="max-height:310px;max-width:100%;object-fit:contain;">
                                                                                    <source src="../uploads/<?= htmlspecialchars($mfile) ?>" type="video/mp4">
                                                                                </video>
                                                                            <?php else: ?>
                                                                                <img src="../uploads/<?= htmlspecialchars($mfile) ?>"
                                                                                    class="d-block mx-auto"
                                                                                    style="max-height:310px;max-width:100%;object-fit:contain;">
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                <?php if (count($media) > 1): ?>
                                                                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $row['id'] ?>" data-bs-slide="prev">
                                                                        <span class="carousel-control-prev-icon"></span>
                                                                    </button>
                                                                    <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $row['id'] ?>" data-bs-slide="next">
                                                                        <span class="carousel-control-next-icon"></span>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div style="font-size:11.5px;color:#94a3b8;text-align:center;margin-top:4px;">
                                                                <?= count($media) ?> media file<?= count($media) != 1 ? 's' : '' ?>
                                                            </div>
                                                        </div>
                                                    <?php elseif ($row['preview_image']): ?>
                                                        <div class="col-12 text-center">
                                                            <img src="../uploads/<?= htmlspecialchars($row['preview_image']) ?>"
                                                                style="max-height:280px;max-width:100%;border-radius:10px;object-fit:contain;">
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Description & Bullets -->
                                                    <div class="col-md-7">
                                                        <h6 class="fw-bold mb-2">About</h6>
                                                        <p style="color:#475569;font-size:14px;"><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                                                        <?php if (!empty($bullets)): ?>
                                                            <ul style="padding-left:18px;margin-top:8px;">
                                                                <?php foreach ($bullets as $b): ?>
                                                                    <li style="font-size:13.5px;color:#334155;margin-bottom:4px;"><?= htmlspecialchars($b) ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Analytics -->
                                                    <div class="col-md-5">
                                                        <h6 class="fw-bold mb-2">📊 Analytics</h6>
                                                        <div style="display:flex;gap:12px;flex-wrap:wrap;">
                                                            <div class="stat-pill" style="background:#f0fdf4;">
                                                                <span style="font-size:22px;font-weight:800;color:#0d9488;"><?= number_format($visitors) ?></span>
                                                                <span style="font-size:10px;color:#64748b;">VISITORS</span>
                                                            </div>
                                                            <div class="stat-pill" style="background:#f8fafc;">
                                                                <span style="font-size:22px;font-weight:800;color:#475569;"><?= number_format($forecast) ?></span>
                                                                <span style="font-size:10px;color:#64748b;">FORECAST</span>
                                                            </div>
                                                            <div class="stat-pill" style="background:#eff6ff;">
                                                                <span style="font-size:22px;font-weight:800;color:#3b82f6;"><?= $forecast > 0 ? round($visitors / $forecast * 100) : 0 ?>%</span>
                                                                <span style="font-size:10px;color:#64748b;">CAPACITY</span>
                                                            </div>
                                                        </div>
                                                        <div class="analytics-bar mt-3" style="height:8px;">
                                                            <div class="analytics-fill" style="width:<?= $forecast > 0 ? min(100, round($visitors / $forecast * 100)) : 0 ?>%;height:8px;"></div>
                                                        </div>
                                                        <div style="font-size:11px;color:#94a3b8;margin-top:3px;">
                                                            <?= number_format($visitors) ?> of <?= number_format($forecast) ?> forecasted visitors
                                                        </div>

                                                        <!-- Coordinators -->
                                                        <?php if ($row['coordinators']): ?>
                                                            <div style="margin-top:16px;">
                                                                <h6 class="fw-bold mb-1">👤 Coordinators</h6>
                                                                <p style="font-size:13px;margin:0;"><?= htmlspecialchars($row['coordinators']) ?></p>
                                                                <?php if ($row['coordinator_links']): ?>
                                                                    <a href="<?= htmlspecialchars($row['coordinator_links']) ?>" target="_blank"
                                                                        style="font-size:12px;color:#0d9488;">Contact/Link →</a>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if ($row['homestay_links']): ?>
                                                            <div style="margin-top:10px;">
                                                                <h6 class="fw-bold mb-1">🏠 Homestay</h6>
                                                                <a href="<?= htmlspecialchars($row['homestay_links']) ?>" target="_blank"
                                                                    style="font-size:13px;color:#0d9488;"><?= htmlspecialchars($row['homestay_links']) ?></a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Trails -->
                                                    <?php if (!empty($trails)): ?>
                                                        <div class="col-md-6">
                                                            <h6 class="fw-bold mb-2">🥾 Trails</h6>
                                                            <?php foreach ($trails as $t): ?>
                                                                <div class="trail-card">
                                                                    <h6><?= htmlspecialchars($t['name']) ?>
                                                                        <?php if ($t['difficulty']): ?>
                                                                            <span style="font-size:10px;font-weight:600;padding:2px 7px;border-radius:99px;margin-left:4px;<?=
                                                                                                                                                                            $t['difficulty'] === 'Easy' ? 'background:#f0fdf4;color:#166534' : ($t['difficulty'] === 'Moderate' ? 'background:#fef9c3;color:#854d0e' : ($t['difficulty'] === 'Difficult' ? 'background:#fff7ed;color:#c2410c' : 'background:#fee2e2;color:#991b1b'))
                                                                                                                                                                            ?>"><?= $t['difficulty'] ?></span>
                                                                        <?php endif; ?>
                                                                    </h6>
                                                                    <?php if ($t['jumpoff']): ?>
                                                                        <p>⛳ Jump-off: <?= htmlspecialchars($t['jumpoff']) ?></p>
                                                                    <?php endif; ?>
                                                                    <?php if ($t['duration']): ?>
                                                                        <p>⏱ Duration: <?= htmlspecialchars($t['duration']) ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Camping Sites -->
                                                    <?php if (!empty($camping)): ?>
                                                        <div class="col-md-6">
                                                            <h6 class="fw-bold mb-2">⛺ Camping Sites</h6>
                                                            <?php foreach ($camping as $c): ?>
                                                                <div class="camp-card">
                                                                    <?php if (!empty($c['image'])): ?>
                                                                        <img src="../uploads/<?= htmlspecialchars($c['image']) ?>"
                                                                            style="width:100%;max-height:100px;object-fit:cover;border-radius:6px;margin-bottom:6px;">
                                                                    <?php endif; ?>
                                                                    <h6><?= htmlspecialchars($c['name']) ?></h6>
                                                                    <?php if ($c['location']): ?>
                                                                        <p>📍 <?= htmlspecialchars($c['location']) ?></p>
                                                                    <?php endif; ?>
                                                                    <?php if ($c['capacity']): ?>
                                                                        <p>👥 Capacity: <?= htmlspecialchars($c['capacity']) ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                </div><!-- /row -->
                                            </div><!-- /modal-body -->
                                        </div>
                                    </div>
                                </div>

                                <!-- ═══ EDIT MODAL ═══ -->
                                <?php if ($role === 'admin' || $role === 'approver'): ?>
                                    <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Destination</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">

                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Name</label>
                                                                <input type="text" name="name" class="form-control"
                                                                    value="<?= htmlspecialchars($row['name']) ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Visitor Count</label>
                                                                <input type="number" name="analytics_visitors" class="form-control"
                                                                    value="<?= $visitors ?>">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label fw-semibold">Overview</label>
                                                                <textarea name="description" class="form-control" rows="2" required><?= htmlspecialchars($row['description']) ?></textarea>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label fw-semibold">Bullet Highlights <small class="text-muted">(one per line)</small></label>
                                                                <textarea name="bullet_descriptions" class="form-control" rows="3"><?= htmlspecialchars(implode("\n", $bullets)) ?></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="form-check form-switch mb-3">
                                                            <input class="form-check-input" type="checkbox" name="is_open"
                                                                id="editIsOpen<?= $row['id'] ?>" value="1"
                                                                <?= $row['is_open'] ? 'checked' : '' ?>>
                                                            <label class="form-check-label fw-semibold" for="editIsOpen<?= $row['id'] ?>">Open to Public</label>
                                                        </div>

                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Preview Image
                                                                    <?php if ($row['preview_image']): ?>
                                                                        <small class="text-muted">(current: <?= htmlspecialchars($row['preview_image']) ?>)</small>
                                                                    <?php endif; ?>
                                                                </label>
                                                                <input type="file" name="preview_image" class="form-control" accept="image/*">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Gallery Media
                                                                    <?php if (!empty($media)): ?>
                                                                        <small class="text-muted">(<?= count($media) ?> current — upload to replace all)</small>
                                                                    <?php endif; ?>
                                                                </label>
                                                                <input type="file" name="media_links[]" class="form-control" multiple accept="image/*,video/*">
                                                            </div>
                                                        </div>

                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-semibold">Coordinators</label>
                                                                <input type="text" name="coordinators" class="form-control"
                                                                    value="<?= htmlspecialchars($row['coordinators']) ?>">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-semibold">Coordinator Links</label>
                                                                <input type="text" name="coordinator_links" class="form-control"
                                                                    value="<?= htmlspecialchars($row['coordinator_links'] ?? '') ?>">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-semibold">Homestay Links</label>
                                                                <input type="text" name="homestay_links" class="form-control"
                                                                    value="<?= htmlspecialchars($row['homestay_links']) ?>">
                                                            </div>
                                                        </div>

                                                        <!-- Trails repeater -->
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">🥾 Trails</label>
                                                            <div id="trail-repeater-<?= $row['id'] ?>">
                                                                <?php foreach ($trails as $ti => $t): ?>
                                                                    <div class="repeater-row">
                                                                        <button type="button" class="remove-row" onclick="this.closest('.repeater-row').remove()">×</button>
                                                                        <div class="row g-2">
                                                                            <div class="col-md-3"><input type="text" name="trail_name[]" class="form-control form-control-sm" value="<?= htmlspecialchars($t['name']) ?>" placeholder="Trail name"></div>
                                                                            <div class="col-md-3"><input type="text" name="trail_jumpoff[]" class="form-control form-control-sm" value="<?= htmlspecialchars($t['jumpoff']) ?>" placeholder="Jump-off point"></div>
                                                                            <div class="col-md-3">
                                                                                <select name="trail_difficulty[]" class="form-control form-control-sm">
                                                                                    <option value="">Difficulty</option>
                                                                                    <?php foreach (['Easy', 'Moderate', 'Difficult', 'Expert'] as $d): ?>
                                                                                        <option <?= $t['difficulty'] === $d ? 'selected' : '' ?>><?= $d ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-3"><input type="text" name="trail_duration[]" class="form-control form-control-sm" value="<?= htmlspecialchars($t['duration']) ?>" placeholder="Duration"></div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                                                onclick="addTrailRow('trail-repeater-<?= $row['id'] ?>')">+ Add Trail</button>
                                                        </div>

                                                        <!-- Camping repeater -->
                                                        <div class="mb-2">
                                                            <label class="form-label fw-semibold">⛺ Camping Sites</label>
                                                            <div id="camp-repeater-<?= $row['id'] ?>">
                                                                <?php foreach ($camping as $ci => $c): ?>
                                                                    <div class="repeater-row">
                                                                        <button type="button" class="remove-row" onclick="this.closest('.repeater-row').remove()">×</button>
                                                                        <input type="hidden" name="camp_existing_image[]" value="<?= htmlspecialchars($c['image'] ?? '') ?>">
                                                                        <div class="row g-2 align-items-end">
                                                                            <div class="col-md-3"><input type="text" name="camp_name[]" class="form-control form-control-sm" value="<?= htmlspecialchars($c['name']) ?>" placeholder="Site name"></div>
                                                                            <div class="col-md-3"><input type="text" name="camp_location[]" class="form-control form-control-sm" value="<?= htmlspecialchars($c['location']) ?>" placeholder="Location"></div>
                                                                            <div class="col-md-3"><input type="text" name="camp_capacity[]" class="form-control form-control-sm" value="<?= htmlspecialchars($c['capacity']) ?>" placeholder="Capacity"></div>
                                                                            <div class="col-md-3">
                                                                                <?php if (!empty($c['image'])): ?>
                                                                                    <div style="font-size:10px;color:#64748b;margin-bottom:2px;">Current: <?= htmlspecialchars($c['image']) ?></div>
                                                                                <?php endif; ?>
                                                                                <input type="file" name="camp_image[]" class="form-control form-control-sm" accept="image/*">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                                                onclick="addCampRow('camp-repeater-<?= $row['id'] ?>')">+ Add Camping Site</button>
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
                                <?php endif; ?>

                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div><!-- /table-wrap -->
            </div><!-- /page-body -->
        </div><!-- /main-content -->
    </div><!-- /admin-shell -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── Repeater helpers ──────────────────────────────────────────────────────────
        function addTrailRow(containerId) {
            const c = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = 'repeater-row';
            div.innerHTML = `
        <button type="button" class="remove-row" onclick="this.closest('.repeater-row').remove()">×</button>
        <div class="row g-2">
            <div class="col-md-3"><input type="text" name="trail_name[]" class="form-control form-control-sm" placeholder="Trail name"></div>
            <div class="col-md-3"><input type="text" name="trail_jumpoff[]" class="form-control form-control-sm" placeholder="Jump-off point"></div>
            <div class="col-md-3">
                <select name="trail_difficulty[]" class="form-control form-control-sm">
                    <option value="">Difficulty</option>
                    <option>Easy</option><option>Moderate</option><option>Difficult</option><option>Expert</option>
                </select>
            </div>
            <div class="col-md-3"><input type="text" name="trail_duration[]" class="form-control form-control-sm" placeholder="Duration (e.g. 3-4 hrs)"></div>
        </div>`;
            c.appendChild(div);
        }

        function addCampRow(containerId) {
            const c = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = 'repeater-row';
            div.innerHTML = `
        <button type="button" class="remove-row" onclick="this.closest('.repeater-row').remove()">×</button>
        <div class="row g-2 align-items-end">
            <div class="col-md-3"><input type="text" name="camp_name[]" class="form-control form-control-sm" placeholder="Site name"></div>
            <div class="col-md-3"><input type="text" name="camp_location[]" class="form-control form-control-sm" placeholder="Location/coordinates"></div>
            <div class="col-md-3"><input type="text" name="camp_capacity[]" class="form-control form-control-sm" placeholder="Capacity (e.g. 20 tents)"></div>
            <div class="col-md-3"><input type="file" name="camp_image[]" class="form-control form-control-sm" accept="image/*"></div>
        </div>`;
            c.appendChild(div);
        }
    </script>
</body>

</html>