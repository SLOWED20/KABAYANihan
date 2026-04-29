<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';
$searchResults = [];
if (!empty($_GET['query'])) {
    $q = "%" . $_GET['query'] . "%";
    $stmt = $conn->prepare("SELECT * FROM services WHERE service_name LIKE ? OR office LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("ss", $q, $q);
    $stmt->execute();
    $result = $stmt->get_result();
    $searchResults = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM services ORDER BY created_at DESC");
    $searchResults = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<div style="background:var(--forest);padding:140px 0 72px;text-align:center;">
    <div class="container">
        <span class="section-label" style="color:rgba(255,255,255,.5);">Municipality of Kabayan</span>
        <h1 style="color:white;margin-bottom:16px;">Municipal <em style="font-style:italic;color:#7ecb8f;">Services</em></h1>
        <p class="lead" style="color:rgba(255,255,255,.6);max-width:500px;margin:0 auto 32px;">Access government services, forms, and information from the offices of Kabayan.</p>
        <form method="GET" action="services.php" class="search-form">
            <input type="text" name="query" placeholder="Search services or offices…" value="<?= htmlspecialchars($_GET['query'] ?? '') ?>">
            <button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z" />
                </svg>
            </button>
        </form>
    </div>
</div>

<section style="background:var(--ivory);">
    <div class="container">
        <?php if (!empty($_GET['query'])): ?>
            <p style="font-family:'DM Mono',monospace;font-size:12px;color:var(--text-3);margin-bottom:28px;letter-spacing:.05em;">
                <?= count($searchResults) ?> result<?= count($searchResults) !== 1 ? 's' : '' ?> for "<?= htmlspecialchars($_GET['query']) ?>"
            </p>
        <?php endif; ?>
        <?php if (!empty($searchResults)): ?>
            <div class="services-grid">
                <?php foreach ($searchResults as $s): ?>
                    <div class="service-card">
                        <div class="service-card-name"><?= htmlspecialchars($s['service_name']) ?></div>
                        <?php if (!empty($s['office'])): ?>
                            <div class="service-card-office"><?= htmlspecialchars($s['office']) ?></div>
                        <?php endif; ?>
                        <div class="service-card-footer">
                            <span style="font-family:'DM Mono',monospace;font-size:11px;color:var(--text-3);"><?= date("M j, Y", strtotime($s['created_at'])) ?></span>
                            <?php if (!empty($s['form_link'])): ?>
                                <a href="<?= htmlspecialchars($s['form_link']) ?>" target="_blank" class="btn btn-terra btn-sm">Open Form →</a>
                            <?php else: ?>
                                <span style="font-size:12px;color:var(--text-3);font-family:'DM Mono',monospace;">No form</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="notice">No services found<?= !empty($_GET['query']) ? ' for "' . htmlspecialchars($_GET['query']) . '"' : '' ?>.</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>