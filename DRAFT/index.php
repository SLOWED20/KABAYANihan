<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

$latestAnnouncement = null;
$stmt = $conn->prepare("SELECT id, title, description, created_at FROM announcements ORDER BY created_at DESC LIMIT 1");
if ($stmt && $stmt->execute()) {
    $r = $stmt->get_result();
    if ($r) $latestAnnouncement = $r->fetch_assoc();
}
$stmt->close();

$searchResults = [];
if (!empty($_GET['query'])) {
    $q = "%" . $_GET['query'] . "%";
    $sql = "(SELECT 'destination' AS type, id, name AS title, description, preview_image, analytics_visitors, forecast_traffic FROM destinations WHERE name LIKE ? OR description LIKE ?)
            UNION
            (SELECT 'service' AS type, id, service_name AS title, office AS description, NULL, NULL, NULL FROM services WHERE service_name LIKE ? OR office LIKE ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $q, $q, $q, $q);
    if ($stmt->execute()) {
        $r = $stmt->get_result();
        if ($r) $searchResults = $r->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}

$topDestinations = [];
$stmt = $conn->prepare("SELECT id, name, description, preview_image, analytics_visitors, forecast_traffic FROM destinations ORDER BY analytics_visitors DESC LIMIT 3");
if ($stmt && $stmt->execute()) {
    $r = $stmt->get_result();
    if ($r) $topDestinations = $r->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

$galleryItems = [];
$stmt = $conn->prepare("SELECT id, title, image FROM galleries WHERE status = 'active' ORDER BY display_order ASC, created_at DESC");
if ($stmt && $stmt->execute()) {
    $r = $stmt->get_result();
    if ($r) $galleryItems = $r->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
?>

<!-- HERO -->
<section class="video-hero">
    <video autoplay muted loop playsinline>
        <source src="vd.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-eyebrow">Benguet · Cordillera · Philippines</span>
        <h1 class="hero-title">Welcome to<br><em>Kabayan</em></h1>
        <p class="hero-sub">"Home of the Century Old Mummies"</p>
        <form method="GET" action="index.php" class="hero-search">
            <input type="text" name="query" placeholder="Search destinations, services…" value="<?= htmlspecialchars($_GET['query'] ?? '') ?>">
            <button type="submit" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z" />
                </svg>
            </button>
        </form>
    </div>
    <div class="hero-scroll">
        <div class="hero-scroll-line"></div>
    </div>
</section>

<!-- SEARCH RESULTS -->
<?php if (!empty($searchResults)): ?>
    <div class="container" style="padding-top:48px;">
        <div class="search-results">
            <h3><?= count($searchResults) ?> result<?= count($searchResults) !== 1 ? 's' : '' ?> for "<?= htmlspecialchars($_GET['query']) ?>"</h3>
            <div class="card-grid card-grid-3">
                <?php foreach ($searchResults as $r): ?>
                    <div class="destination-card">
                        <?php if (!empty($r['preview_image'])): ?>
                            <div class="destination-card-img" style="height:160px;">
                                <img src="uploads/<?= htmlspecialchars($r['preview_image']) ?>" alt="<?= htmlspecialchars($r['title']) ?>">
                                <span class="destination-card-badge"><?= ucfirst($r['type']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="destination-card-body">
                            <h5><?= htmlspecialchars($r['title']) ?></h5>
                            <p><?= htmlspecialchars(substr($r['description'], 0, 90)) ?>…</p>
                            <?php if ($r['type'] === 'destination'): ?>
                                <a href="destinations.php?id=<?= $r['id'] ?>" class="btn btn-terra btn-sm">View →</a>
                            <?php else: ?>
                                <a href="services.php" class="btn btn-outline btn-sm">See Services →</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ANNOUNCEMENT BAND -->
<?php if ($latestAnnouncement): ?>
    <div class="announcement-band">
        <div class="container">
            <div class="announcement-inner">
                <span class="announcement-tag">📢 Latest</span>
                <div class="announcement-text">
                    <h4><?= htmlspecialchars($latestAnnouncement['title']) ?></h4>
                    <p><?= htmlspecialchars(substr($latestAnnouncement['description'], 0, 130)) ?>…</p>
                </div>
                <a href="announcements.php?id=<?= $latestAnnouncement['id'] ?>" class="btn btn-outline-white btn-sm">Read More</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ABOUT / HISTORY TEASER -->
<section style="background: var(--ivory);">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:center;">
            <div>
                <span class="section-label">Our Heritage</span>
                <h2 style="margin-bottom:20px;">A Town Woven<br><em style="font-style:italic;color:var(--terra);">Through Time</em></h2>
                <p class="lead" style="margin-bottom:24px;">Nestled in the Cordillera mountains of Benguet, Kabayan is renowned for its sacred burial caves, ancient Fire Mummies, and breathtaking natural landscapes that have endured for centuries.</p>
                <a href="history.php" class="btn btn-terra">Explore History →</a>
            </div>
            <div style="position:relative;">
                <div style="background:var(--terra-pale);border-radius:var(--radius-xl);padding:36px;border-left:4px solid var(--terra);">
                    <blockquote style="font-family:'Playfair Display',serif;font-size:1.2rem;font-style:italic;color:var(--umber);line-height:1.6;margin-bottom:16px;">
                        "Kabayan holds a unique place in Philippine heritage — its ancient mummies standing as testament to a civilization that mastered the art of preservation long before the modern world."
                    </blockquote>
                    <cite style="font-family:'DM Mono',monospace;font-size:11px;color:var(--terra);letter-spacing:.08em;text-transform:uppercase;">— Cultural Heritage Commission</cite>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TOP DESTINATIONS -->
<section style="background: var(--ivory-2);">
    <div class="container">
        <div class="section-heading">
            <span class="section-label">Tourism</span>
            <h2>Top Destinations</h2>
            <div class="section-divider"></div>
        </div>
        <?php if (!empty($topDestinations)): ?>
            <div class="card-grid card-grid-3">
                <?php foreach ($topDestinations as $d): ?>
                    <div class="destination-card">
                        <div class="destination-card-img">
                            <img src="uploads/<?= htmlspecialchars($d['preview_image']) ?>" alt="<?= htmlspecialchars($d['name']) ?>">
                            <span class="destination-card-badge">🏔 Destination</span>
                        </div>
                        <div class="destination-card-body">
                            <h5><?= htmlspecialchars($d['name']) ?></h5>
                            <p><?= htmlspecialchars(substr($d['description'], 0, 100)) ?>…</p>
                            <div class="destination-card-stats">
                                <div class="stat-item"><strong><?= number_format($d['analytics_visitors']) ?></strong>Visitors</div>
                                <div class="stat-item"><strong><?= number_format($d['forecast_traffic']) ?></strong>Forecast</div>
                            </div>
                            <a href="destinations.php?id=<?= $d['id'] ?>" class="btn btn-terra">View Details →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:36px;">
                <a href="destinations.php" class="btn btn-outline">All Destinations →</a>
            </div>
        <?php else: ?>
            <p class="notice">No destinations available yet.</p>
        <?php endif; ?>
    </div>
</section>

<!-- GALLERY -->
<?php if (!empty($galleryItems)): ?>
    <section id="gallery" style="background:var(--umber);">
        <div class="container">
            <div class="section-heading">
                <span class="section-label" style="color:var(--terra-light);">Photo Gallery</span>
                <h2 style="color:white;">Scenes of Kabayan</h2>
                <div class="section-divider"></div>
            </div>
            <div style="overflow:hidden;border-radius:var(--radius-xl);" id="galleryWrap">
                <div style="display:flex;gap:18px;transition:transform .5s cubic-bezier(.25,.1,.25,1);" id="galleryTrack">
                    <?php foreach ($galleryItems as $g): ?>
                        <div style="flex:0 0 calc(33.333% - 12px);min-width:calc(33.333% - 12px);">
                            <div class="gallery-item">
                                <img src="uploads/<?= htmlspecialchars($g['image']) ?>" alt="<?= htmlspecialchars($g['title']) ?>">
                                <div class="gallery-item-title"><?= htmlspecialchars($g['title']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="carousel-controls" style="margin-top:28px;">
                <button class="carousel-btn" id="galPrev" aria-label="Previous">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button class="carousel-btn" id="galNext" aria-label="Next">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </section>
    <script>
        const track = document.getElementById('galleryTrack');
        const slides = track.children.length;
        let idx = 0;
        const move = () => {
            track.style.transform = `translateX(calc(-${idx} * (33.333% + 18px)))`;
        };
        document.getElementById('galNext').onclick = () => {
            idx = Math.min(idx + 1, slides - 3);
            move();
        };
        document.getElementById('galPrev').onclick = () => {
            idx = Math.max(idx - 1, 0);
            move();
        };
    </script>
<?php endif; ?>

<!-- QUICK LINKS -->
<section style="background: var(--terra-pale);">
    <div class="container">
        <div class="section-heading">
            <span class="section-label">Explore More</span>
            <h2>What We Offer</h2>
            <div class="section-divider"></div>
        </div>
        <div class="card-grid card-grid-4">
            <?php
            $links = [
                ['href' => 'services.php',  'icon' => '🗂️', 'title' => 'Municipal Services', 'desc' => 'Access government forms and office services online.'],
                ['href' => 'profiles.php',  'icon' => '👤', 'title' => 'Local Officials',     'desc' => 'Meet the leaders serving the community of Kabayan.'],
                ['href' => 'faqs.php',      'icon' => '❓', 'title' => 'FAQs',               'desc' => 'Find answers to the most common tourism questions.'],
                ['href' => 'history.php',   'icon' => '📜', 'title' => 'Our History',         'desc' => 'Discover the rich cultural heritage of Kabayan.'],
            ];
            foreach ($links as $l): ?>
                <a href="<?= $l['href'] ?>" class="destination-card" style="text-decoration:none;">
                    <div class="destination-card-body" style="text-align:center;align-items:center;">
                        <div style="font-size:36px;margin-bottom:12px;"><?= $l['icon'] ?></div>
                        <h5><?= $l['title'] ?></h5>
                        <p><?= $l['desc'] ?></p>
                        <span class="btn btn-outline btn-sm" style="margin-top:auto;">Learn More →</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>