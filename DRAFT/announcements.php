<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

// SINGLE ANNOUNCEMENT DETAIL VIEW
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id=? AND status='active'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ann = $result->fetch_assoc();
    $stmt->close();

    if ($ann):
?>

<!-- DETAIL HERO -->
<div style="padding-top:var(--nav-h);background:var(--umber);min-height:38vh;display:flex;flex-direction:column;justify-content:flex-end;">
  <?php if (!empty($ann['image'])): ?>
    <div style="position:absolute;inset:0;z-index:0;">
      <img src="uploads/<?= htmlspecialchars($ann['image']) ?>"
           alt="<?= htmlspecialchars($ann['title']) ?>"
           style="width:100%;height:100%;object-fit:cover;opacity:.3;">
    </div>
  <?php endif; ?>
  <div class="container" style="position:relative;z-index:1;padding-bottom:52px;">
    <a href="announcements.php" style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.5);display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;transition:color .2s;" onmouseover="this.style.color='#d98a67'" onmouseout="this.style.color='rgba(255,255,255,.5)'">
      ← All Announcements
    </a>
    <span style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--terra-light);display:block;margin-bottom:12px;">📢 Announcement</span>
    <h1 style="color:white;max-width:720px;margin-bottom:16px;"><?= htmlspecialchars($ann['title']) ?></h1>
    <span style="font-family:'DM Mono',monospace;font-size:11.5px;color:rgba(255,255,255,.4);letter-spacing:.05em;">
      Posted <?= date("F j, Y", strtotime($ann['created_at'])) ?>
    </span>
  </div>
</div>

<!-- DETAIL BODY -->
<section style="background:var(--ivory);">
  <div class="container">
    <div style="max-width:780px;margin:0 auto;">

      <?php if (!empty($ann['image'])): ?>
      <div style="margin-bottom:40px;border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow-lg);">
        <img src="uploads/<?= htmlspecialchars($ann['image']) ?>"
             alt="<?= htmlspecialchars($ann['title']) ?>"
             style="width:100%;max-height:480px;object-fit:cover;">
      </div>
      <?php endif; ?>

      <div style="background:white;border-radius:var(--radius-xl);padding:44px 48px;box-shadow:var(--shadow);border-top:4px solid var(--terra);">
        <p style="font-size:16.5px;line-height:1.9;color:var(--text-2);">
          <?= nl2br(htmlspecialchars($ann['description'])) ?>
        </p>
        <div style="margin-top:36px;padding-top:24px;border-top:1px solid var(--ivory-3);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
          <span style="font-family:'DM Mono',monospace;font-size:11px;color:var(--text-3);letter-spacing:.06em;">
            Published on <?= date("F j, Y · g:i A", strtotime($ann['created_at'])) ?>
          </span>
          <a href="announcements.php" class="btn btn-outline btn-sm">← Back to All Announcements</a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- MORE ANNOUNCEMENTS -->
<?php
    // Fetch 3 other recent announcements (not this one)
    $others_stmt = $conn->prepare("SELECT id, title, description, image, created_at FROM announcements WHERE status='active' AND id != ? ORDER BY created_at DESC LIMIT 3");
    $others_stmt->bind_param("i", $id);
    $others_stmt->execute();
    $others = $others_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $others_stmt->close();

    if (!empty($others)):
?>
<section style="background:var(--ivory-2);">
  <div class="container">
    <div class="section-heading">
      <span class="section-label">Stay Informed</span>
      <h2>More Announcements</h2>
      <div class="section-divider"></div>
    </div>
    <div class="card-grid card-grid-3">
      <?php foreach ($others as $o): ?>
      <a href="announcements.php?id=<?= $o['id'] ?>" style="text-decoration:none;">
        <div class="destination-card" style="cursor:pointer;">
          <?php if (!empty($o['image'])): ?>
          <div class="destination-card-img" style="height:180px;">
            <img src="uploads/<?= htmlspecialchars($o['image']) ?>" alt="<?= htmlspecialchars($o['title']) ?>">
            <span class="destination-card-badge">📢 Notice</span>
          </div>
          <?php else: ?>
          <div style="height:100px;background:var(--terra-pale);display:flex;align-items:center;justify-content:center;font-size:32px;">📢</div>
          <?php endif; ?>
          <div class="destination-card-body">
            <span style="font-family:'DM Mono',monospace;font-size:10.5px;color:var(--text-3);letter-spacing:.05em;"><?= date("M j, Y", strtotime($o['created_at'])) ?></span>
            <h5 style="margin-top:6px;"><?= htmlspecialchars($o['title']) ?></h5>
            <p><?= htmlspecialchars(substr($o['description'], 0, 100)) ?>…</p>
            <span style="font-family:'DM Mono',monospace;font-size:11px;color:var(--terra);letter-spacing:.06em;text-transform:uppercase;">Read More →</span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
    else:
        // Announcement not found or deleted
?>
<div style="padding-top:var(--nav-h);">
  <section style="background:var(--ivory);text-align:center;">
    <div class="container">
      <div style="font-size:56px;margin-bottom:20px;">📭</div>
      <h2 style="margin-bottom:12px;">Announcement Not Found</h2>
      <p style="color:var(--text-2);margin-bottom:28px;">This announcement may have been removed or is no longer available.</p>
      <a href="announcements.php" class="btn btn-terra">View All Announcements →</a>
    </div>
  </section>
</div>
<?php
    endif;

// ALL ANNOUNCEMENTS LIST VIEW
} else {
    $result = $conn->query("SELECT * FROM announcements WHERE status='active' ORDER BY created_at DESC");
    $announcements = $result->fetch_all(MYSQLI_ASSOC);
    $latest = !empty($announcements) ? $announcements[0] : null;
    $rest   = array_slice($announcements, 1);
?>

<!-- HERO -->
<div style="background:linear-gradient(160deg,var(--umber) 0%,#3d2516 100%);padding:140px 0 80px;">
  <div class="container" style="text-align:center;">
    <span style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--terra-light);display:block;margin-bottom:14px;">Municipality of Kabayan</span>
    <h1 style="color:white;margin-bottom:16px;">Announcements &<br><em style="font-style:italic;color:var(--terra-light);">Public Notices</em></h1>
    <p class="lead" style="color:rgba(255,255,255,.55);max-width:500px;margin:0 auto;">Stay updated with the latest news, advisories, and official notices from the local government of Kabayan.</p>
  </div>
</div>

<section style="background:var(--ivory);">
  <div class="container">

    <?php if (empty($announcements)): ?>
      <div style="text-align:center;padding:60px 0;">
        <div style="font-size:52px;margin-bottom:16px;">📭</div>
        <h3 style="color:var(--text-2);margin-bottom:8px;">No announcements yet</h3>
        <p style="color:var(--text-3);">Check back soon for updates from the Municipality of Kabayan.</p>
      </div>
    <?php else: ?>

      <!-- FEATURED / LATEST -->
      <?php if ($latest): ?>
      <div style="margin-bottom:56px;">
        <span class="section-label">Latest Notice</span>
        <a href="announcements.php?id=<?= $latest['id'] ?>" style="text-decoration:none;display:block;">
          <div style="background:white;border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow-lg);display:grid;grid-template-columns:<?= !empty($latest['image']) ? '1fr 1fr' : '1fr' ?>;transition:all .22s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-lg)'">
            <?php if (!empty($latest['image'])): ?>
            <div style="overflow:hidden;max-height:360px;">
              <img src="uploads/<?= htmlspecialchars($latest['image']) ?>"
                   alt="<?= htmlspecialchars($latest['title']) ?>"
                   style="width:100%;height:100%;object-fit:cover;transition:transform .5s ease;"
                   onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform=''">
            </div>
            <?php endif; ?>
            <div style="padding:40px 44px;display:flex;flex-direction:column;justify-content:center;border-left:5px solid var(--terra);">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
                <span style="background:var(--terra);color:white;font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.14em;text-transform:uppercase;padding:5px 13px;border-radius:99px;">📢 Latest</span>
                <span style="font-family:'DM Mono',monospace;font-size:11px;color:var(--text-3);"><?= date("F j, Y", strtotime($latest['created_at'])) ?></span>
              </div>
              <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--umber);margin-bottom:16px;line-height:1.25;"><?= htmlspecialchars($latest['title']) ?></h2>
              <p style="color:var(--text-2);font-size:15px;line-height:1.75;margin-bottom:24px;"><?= htmlspecialchars(substr($latest['description'], 0, 220)) ?>…</p>
              <span class="btn btn-terra" style="align-self:flex-start;">Read Full Notice →</span>
            </div>
          </div>
        </a>
      </div>
      <?php endif; ?>

      <!-- REMAINING ANNOUNCEMENTS GRID -->
      <?php if (!empty($rest)): ?>
      <div style="margin-bottom:16px;">
        <span class="section-label">All Notices</span>
        <h2 style="margin-bottom:32px;">Previous Announcements</h2>
      </div>
      <div class="card-grid card-grid-3">
        <?php foreach ($rest as $ann): ?>
        <a href="announcements.php?id=<?= $ann['id'] ?>" style="text-decoration:none;">
          <div class="destination-card" style="cursor:pointer;">
            <?php if (!empty($ann['image'])): ?>
            <div class="destination-card-img" style="height:190px;">
              <img src="uploads/<?= htmlspecialchars($ann['image']) ?>" alt="<?= htmlspecialchars($ann['title']) ?>">
              <span class="destination-card-badge">📢 Notice</span>
            </div>
            <?php else: ?>
            <div style="height:90px;background:linear-gradient(135deg,var(--terra-pale),var(--ivory-2));display:flex;align-items:center;justify-content:center;font-size:28px;border-bottom:1px solid var(--ivory-3);">📢</div>
            <?php endif; ?>
            <div class="destination-card-body">
              <span style="font-family:'DM Mono',monospace;font-size:10.5px;color:var(--text-3);letter-spacing:.05em;"><?= date("F j, Y", strtotime($ann['created_at'])) ?></span>
              <h5 style="margin-top:6px;"><?= htmlspecialchars($ann['title']) ?></h5>
              <p><?= htmlspecialchars(substr($ann['description'], 0, 110)) ?>…</p>
              <span style="font-family:'DM Mono',monospace;font-size:11px;color:var(--terra);letter-spacing:.06em;text-transform:uppercase;margin-top:auto;">Read More →</span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</section>

<?php } include 'includes/footer.php'; ?>
