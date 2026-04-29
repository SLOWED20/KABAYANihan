<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

// ── DETAIL VIEW ───────────────────────────────────────────────────────────────
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE id=? AND status='active'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $destination = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($destination):
        $media   = json_decode($destination['media_links']        ?? '[]', true) ?: [];
        $bullets = json_decode($destination['bullet_descriptions'] ?? '[]', true) ?: [];
        $trails  = json_decode($destination['trails']              ?? '[]', true) ?: [];
        $camping = json_decode($destination['camping_sites']       ?? '[]', true) ?: [];
        $isOpen  = (bool)($destination['is_open'] ?? 1);
        $cap     = $destination['forecast_traffic'] > 0
                   ? min(100, round($destination['analytics_visitors'] / $destination['forecast_traffic'] * 100))
                   : 0;
?>

<!-- ── HERO ──────────────────────────────────────────────────────────────── -->
<div style="padding-top:var(--nav-h);position:relative;background:var(--umber);overflow:hidden;">
    <img src="uploads/<?= htmlspecialchars($destination['preview_image']) ?>"
         alt="<?= htmlspecialchars($destination['name']) ?>"
         style="width:100%;height:58vh;min-height:340px;object-fit:cover;opacity:.55;display:block;">
    <div style="position:absolute;inset:0;background:linear-gradient(to top,var(--umber) 0%,transparent 60%);"></div>
    <div style="position:absolute;bottom:0;left:0;right:0;padding:36px 0 40px;">
        <div class="container">
            <div style="margin-bottom:12px;">
                <?php if ($isOpen): ?>
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(22,101,52,.85);color:#bbf7d0;font-family:'DM Mono',monospace;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:5px 14px;border-radius:99px;">
                    <span style="width:7px;height:7px;background:#4ade80;border-radius:50%;display:inline-block;"></span> Open to Visitors
                </span>
                <?php else: ?>
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(153,27,27,.85);color:#fecaca;font-family:'DM Mono',monospace;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:5px 14px;border-radius:99px;">
                    <span style="width:7px;height:7px;background:#f87171;border-radius:50%;display:inline-block;"></span> Currently Closed
                </span>
                <?php endif; ?>
            </div>
            <span class="section-label" style="color:var(--terra-light);">Kabayan Tourism</span>
            <h1 style="color:#fff;margin:6px 0 0;text-shadow:0 2px 12px rgba(0,0,0,.4);"><?= htmlspecialchars($destination['name']) ?></h1>
        </div>
    </div>
</div>

<!-- ── BODY ──────────────────────────────────────────────────────────────── -->
<div class="container" style="padding-top:52px;padding-bottom:96px;">

    <a href="destinations.php" style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:var(--terra);display:inline-flex;align-items:center;gap:6px;margin-bottom:36px;text-decoration:none;">
        ← Back to Destinations
    </a>

    <?php if (!$isOpen): ?>
    <div style="background:#fff1f2;border:1.5px solid #fecaca;border-radius:12px;padding:14px 20px;margin-bottom:32px;display:flex;align-items:center;gap:12px;">
        <span style="font-size:20px;">⚠️</span>
        <span style="font-size:14px;color:#be123c;font-weight:600;">This destination is currently closed to the public. Please check back for updates.</span>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:52px;align-items:start;">

        <!-- ── LEFT ── -->
        <div>

            <!-- Description -->
            <p class="lead" style="margin-bottom:20px;line-height:1.75;"><?= nl2br(htmlspecialchars($destination['description'])) ?></p>

            <!-- Bullet highlights -->
            <?php if (!empty($bullets)): ?>
            <ul style="list-style:none;padding:0;margin:0 0 36px;display:flex;flex-direction:column;gap:9px;">
                <?php foreach ($bullets as $b): ?>
                <li style="display:flex;align-items:flex-start;gap:10px;font-size:15px;color:var(--text-2);">
                    <span style="color:var(--terra);font-size:16px;margin-top:1px;">✦</span>
                    <?= htmlspecialchars($b) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <small style="font-family:'DM Mono',monospace;font-size:11px;color:var(--text-3);letter-spacing:.05em;display:block;margin-bottom:44px;">
                Added <?= date("F j, Y", strtotime($destination['created_at'])) ?>
            </small>

            <!-- ── MEDIA GALLERY ── -->
            <?php if (!empty($media)): ?>
            <div style="margin-bottom:56px;">
                <span class="section-label">Gallery</span>
                <h3 style="margin:6px 0 16px;">Photos &amp; Videos</h3>

                <?php $hasMultiple = count($media) > 1; ?>

                <!-- Wrapper: main viewer + vertical thumb strip -->
                <div style="display:grid;grid-template-columns:<?= $hasMultiple ? '1fr 72px' : '1fr' ?>;gap:8px;height:260px;">

                    <!-- Main viewer (carousel) -->
                    <div id="detailCarousel" class="carousel slide" data-bs-ride="false"
                         style="border-radius:12px;overflow:hidden;background:#111;height:260px;">
                        <div class="carousel-inner" style="height:100%;">
                            <?php foreach ($media as $mi => $mfile): ?>
                            <div class="carousel-item <?= $mi === 0 ? 'active' : '' ?>" style="height:260px;">
                                <?php if (preg_match('/\.(mp4|webm|mov)$/i', $mfile)): ?>
                                <video style="width:100%;height:260px;object-fit:contain;background:#000;" controls>
                                    <source src="uploads/<?= htmlspecialchars($mfile) ?>" type="video/mp4">
                                </video>
                                <?php else: ?>
                                <img src="uploads/<?= htmlspecialchars($mfile) ?>"
                                     style="width:100%;height:260px;object-fit:cover;display:block;">
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($hasMultiple): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#detailCarousel" data-bs-slide="prev" style="width:36px;">
                            <span class="carousel-control-prev-icon" style="width:20px;height:20px;"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#detailCarousel" data-bs-slide="next" style="width:36px;">
                            <span class="carousel-control-next-icon" style="width:20px;height:20px;"></span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Vertical thumbnail strip -->
                    <?php if ($hasMultiple): ?>
                    <div style="display:flex;flex-direction:column;gap:6px;height:260px;overflow-y:auto;scrollbar-width:thin;" id="thumbStrip">
                        <?php foreach ($media as $ti => $tf): ?>
                        <div onclick="gotoSlide(<?= $ti ?>)" id="thumb-<?= $ti ?>"
                             style="flex-shrink:0;width:66px;height:50px;border-radius:7px;overflow:hidden;cursor:pointer;border:2px solid <?= $ti === 0 ? 'var(--terra,#c2673a)' : 'transparent' ?>;opacity:<?= $ti === 0 ? '1' : '.55' ?>;transition:all .18s;">
                            <?php if (preg_match('/\.(mp4|webm|mov)$/i', $tf)): ?>
                            <div style="width:100%;height:100%;background:#1a1a1a;display:flex;align-items:center;justify-content:center;font-size:18px;">▶</div>
                            <?php else: ?>
                            <img src="uploads/<?= htmlspecialchars($tf) ?>"
                                 style="width:100%;height:100%;object-fit:cover;display:block;">
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                </div><!-- /grid wrapper -->

                <!-- Count label -->
                <div style="font-size:11.5px;color:#94a3b8;margin-top:6px;font-family:'DM Mono',monospace;">
                    <?= count($media) ?> item<?= count($media) !== 1 ? 's' : '' ?> in gallery
                </div>
            </div>

            <script>
            function gotoSlide(idx) {
                // Update carousel
                var items = document.querySelectorAll('#detailCarousel .carousel-item');
                items.forEach(function(el){ el.classList.remove('active'); });
                items[idx].classList.add('active');
                // Update thumbs
                var thumbs = document.querySelectorAll('#thumbStrip > div');
                thumbs.forEach(function(th, i){
                    th.style.borderColor = i === idx ? 'var(--terra,#c2673a)' : 'transparent';
                    th.style.opacity     = i === idx ? '1' : '.55';
                });
            }
            // Keep thumbs in sync when carousel arrows are used
            document.getElementById('detailCarousel').addEventListener('slid.bs.carousel', function(e){
                gotoSlide(e.to);
            });
            </script>
            <?php endif; ?>

            <!-- ── TRAILS ── -->
            <?php if (!empty($trails)): ?>
            <div style="margin-bottom:56px;">
                <span class="section-label">Trekking</span>
                <h3 style="margin:6px 0 24px;">🥾 Trails</h3>
                <div style="display:grid;gap:14px;">
                    <?php foreach ($trails as $t):
                        $diffStyle = match($t['difficulty'] ?? '') {
                            'Easy'     => 'background:#f0fdf4;color:#166534',
                            'Moderate' => 'background:#fef9c3;color:#854d0e',
                            'Difficult'=> 'background:#fff7ed;color:#c2410c',
                            'Expert'   => 'background:#fee2e2;color:#991b1b',
                            default    => 'background:#f1f5f9;color:#475569',
                        };
                    ?>
                    <div style="border:1.5px solid var(--border,#e2e8f0);border-radius:14px;padding:20px 22px;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                            <div style="font-size:17px;font-weight:700;color:var(--umber);"><?= htmlspecialchars($t['name']) ?></div>
                            <?php if (!empty($t['difficulty'])): ?>
                            <span style="font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;white-space:nowrap;<?= $diffStyle ?>">
                                <?= htmlspecialchars($t['difficulty']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:20px;">
                            <?php if (!empty($t['jumpoff'])): ?>
                            <div style="font-size:13.5px;color:var(--text-2);">
                                <span style="font-weight:700;color:var(--terra);">⛳ Jump-off Point</span><br>
                                <?= htmlspecialchars($t['jumpoff']) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($t['duration'])): ?>
                            <div style="font-size:13.5px;color:var(--text-2);">
                                <span style="font-weight:700;color:var(--terra);">⏱ Duration</span><br>
                                <?= htmlspecialchars($t['duration']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ── CAMPING SITES ── -->
            <?php if (!empty($camping)): ?>
            <div style="margin-bottom:56px;">
                <span class="section-label">Overnight</span>
                <h3 style="margin:6px 0 24px;">⛺ Camping Sites</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px;">
                    <?php foreach ($camping as $c): ?>
                    <div style="border:1.5px solid var(--border,#e2e8f0);border-radius:14px;overflow:hidden;">
                        <?php if (!empty($c['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($c['image']) ?>"
                             style="width:100%;height:130px;object-fit:cover;">
                        <?php else: ?>
                        <div style="width:100%;height:130px;background:linear-gradient(135deg,#1e3a2f,#2d5a3d);display:flex;align-items:center;justify-content:center;font-size:38px;">⛺</div>
                        <?php endif; ?>
                        <div style="padding:14px 16px;">
                            <div style="font-size:15px;font-weight:700;margin-bottom:7px;color:var(--umber);"><?= htmlspecialchars($c['name']) ?></div>
                            <?php if (!empty($c['location'])): ?>
                            <div style="font-size:12.5px;color:var(--text-2);display:flex;align-items:flex-start;gap:5px;margin-bottom:4px;">
                                <span>📍</span><?= htmlspecialchars($c['location']) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($c['capacity'])): ?>
                            <div style="font-size:12.5px;color:var(--text-2);display:flex;align-items:center;gap:5px;">
                                <span>👥</span><?= htmlspecialchars($c['capacity']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ── ANALYTICS ── -->
            <div>
                <span class="section-label">Data</span>
                <h3 style="margin:6px 0 22px;">📊 Visitor Analytics</h3>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;">
                    <div style="background:var(--terra-pale,#fdf4ef);border-radius:14px;padding:18px 20px;border-left:4px solid var(--terra,#c2673a);">
                        <div style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--terra);margin-bottom:5px;">Visitors</div>
                        <div style="font-size:26px;font-weight:800;color:var(--terra);font-family:'Playfair Display',serif;"><?= number_format((int)$destination['analytics_visitors']) ?></div>
                    </div>
                    <div style="background:#f0f9ff;border-radius:14px;padding:18px 20px;border-left:4px solid #0ea5e9;">
                        <div style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#0ea5e9;margin-bottom:5px;">Forecast</div>
                        <div style="font-size:26px;font-weight:800;color:#0ea5e9;font-family:'Playfair Display',serif;"><?= number_format((int)$destination['forecast_traffic']) ?></div>
                    </div>
                    <div style="background:#f0fdf4;border-radius:14px;padding:18px 20px;border-left:4px solid #22c55e;">
                        <div style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#22c55e;margin-bottom:5px;">Capacity</div>
                        <div style="font-size:26px;font-weight:800;color:#22c55e;font-family:'Playfair Display',serif;"><?= $cap ?>%</div>
                    </div>
                </div>

                <div style="background:#f1f5f9;border-radius:6px;height:7px;overflow:hidden;margin-bottom:5px;">
                    <div style="background:linear-gradient(90deg,var(--terra,#c2673a),#e8884a);height:7px;border-radius:6px;width:<?= $cap ?>%;"></div>
                </div>
                <div style="font-size:12px;color:var(--text-3,#94a3b8);margin-bottom:24px;">
                    <?= number_format((int)$destination['analytics_visitors']) ?> of <?= number_format((int)$destination['forecast_traffic']) ?> forecasted visitors
                </div>

                <div style="background:#fff;border:1.5px solid var(--border,#e2e8f0);border-radius:14px;padding:20px 24px;">
                    <canvas id="visitorChart" height="90"></canvas>
                </div>
            </div>

        </div><!-- /left -->

        <!-- ── SIDEBAR ── -->
        <div>
            <div style="background:var(--terra-pale,#fdf4ef);border-radius:16px;padding:28px;border-left:4px solid var(--terra,#c2673a);position:sticky;top:calc(var(--nav-h,72px) + 20px);">
                <h4 style="font-family:'Playfair Display',serif;color:var(--umber);margin-bottom:22px;font-size:19px;">Destination Info</h4>

                <div style="margin-bottom:18px;">
                    <div style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--terra);margin-bottom:6px;">Status</div>
                    <?php if ($isOpen): ?>
                    <span style="background:#f0fdf4;color:#166534;font-size:12px;font-weight:700;padding:4px 12px;border-radius:99px;">✓ Open to Public</span>
                    <?php else: ?>
                    <span style="background:#fee2e2;color:#991b1b;font-size:12px;font-weight:700;padding:4px 12px;border-radius:99px;">✕ Temporarily Closed</span>
                    <?php endif; ?>
                </div>

                <dl style="margin:0;">
                    <dt style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--terra);margin-top:16px;">Visitor Count</dt>
                    <dd style="font-size:1.5rem;font-weight:700;color:var(--terra);font-family:'Playfair Display',serif;margin:4px 0 0;"><?= number_format((int)$destination['analytics_visitors']) ?></dd>

                    <dt style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--terra);margin-top:16px;">Forecast Traffic</dt>
                    <dd style="font-size:15px;color:var(--text-2);margin:4px 0 0;"><?= number_format((int)$destination['forecast_traffic']) ?></dd>

                    <?php if (!empty($trails)): ?>
                    <dt style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--terra);margin-top:16px;">Trails</dt>
                    <dd style="font-size:14px;color:var(--text-2);margin:4px 0 0;"><?= count($trails) ?> trail<?= count($trails) != 1 ? 's' : '' ?> available</dd>
                    <?php endif; ?>

                    <?php if (!empty($camping)): ?>
                    <dt style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--terra);margin-top:16px;">Camping Sites</dt>
                    <dd style="font-size:14px;color:var(--text-2);margin:4px 0 0;"><?= count($camping) ?> site<?= count($camping) != 1 ? 's' : '' ?></dd>
                    <?php endif; ?>

                    <?php if (!empty($destination['coordinators'])): ?>
                    <dt style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--terra);margin-top:16px;">Coordinator</dt>
                    <dd style="font-size:14px;color:var(--text-2);margin:4px 0 0;">
                        <?= htmlspecialchars($destination['coordinators']) ?>
                        <?php if (!empty($destination['coordinator_links'])): ?>
                        <br><a href="<?= htmlspecialchars($destination['coordinator_links']) ?>" target="_blank"
                               style="font-size:12.5px;color:var(--terra);text-decoration:none;font-weight:600;">Contact / Info →</a>
                        <?php endif; ?>
                    </dd>
                    <?php endif; ?>

                    <?php if (!empty($destination['homestay_links'])): ?>
                    <dt style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--terra);margin-top:16px;">Homestay Options</dt>
                    <dd style="margin:8px 0 0;">
                        <a href="<?= htmlspecialchars($destination['homestay_links']) ?>" target="_blank"
                           class="btn btn-terra btn-sm" style="display:inline-block;">View Homestays →</a>
                    </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

    </div><!-- /grid -->
</div><!-- /container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const visitors = <?= (int)$destination['analytics_visitors'] ?>;
    const forecast = <?= (int)$destination['forecast_traffic'] ?>;
    function buildTrend(end) {
        const base = Math.max(0, Math.round(end * 0.45));
        const step = (end - base) / 5;
        return Array.from({ length: 6 }, (_, i) => Math.round(base + step * i));
    }
    new Chart(document.getElementById('visitorChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [
                {
                    label: 'Visitors',
                    data: buildTrend(visitors),
                    borderColor: '#c2673a',
                    backgroundColor: 'rgba(194,103,58,0.08)',
                    fill: true, tension: 0.45,
                    pointBackgroundColor: '#c2673a', pointRadius: 5, pointHoverRadius: 7,
                },
                {
                    label: 'Forecast',
                    data: buildTrend(forecast),
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14,165,233,0.04)',
                    fill: false, tension: 0.45, borderDash: [5,5],
                    pointBackgroundColor: '#0ea5e9', pointRadius: 4, pointHoverRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true, position: 'top', labels: { font: { family: "'DM Mono',monospace", size: 11 }, usePointStyle: true } },
                tooltip: { callbacks: { label: c => ' ' + c.dataset.label + ': ' + c.parsed.y.toLocaleString() } }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } },
                x: { grid: { display: false } }
            }
        }
    });
})();
</script>

<?php
    else:
        echo "<div class='container' style='padding:80px 24px;'><p class='notice'>Destination not found or not available.</p></div>";
    endif;

// ── LIST VIEW ─────────────────────────────────────────────────────────────────
} else {
    $result = $conn->query(
        "SELECT id, name, description, preview_image,
                analytics_visitors, forecast_traffic,
                is_open, trails, camping_sites, bullet_descriptions
         FROM destinations
         WHERE status='active'
         ORDER BY analytics_visitors DESC"
    );
    $destinations = $result->fetch_all(MYSQLI_ASSOC);
?>

<!-- LIST HERO -->
<div style="background:linear-gradient(160deg,var(--umber) 0%,var(--forest) 100%);padding:140px 0 80px;text-align:center;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background-image:radial-gradient(circle at 20% 50%,rgba(194,103,58,.15) 0%,transparent 60%),radial-gradient(circle at 80% 20%,rgba(255,255,255,.04) 0%,transparent 50%);"></div>
    <div class="container" style="position:relative;">
        <span class="section-label" style="color:var(--terra-light);">Explore</span>
        <h1 style="color:white;margin-bottom:16px;">
            Kabayan <em style="font-style:italic;color:var(--terra-light);">Destinations</em>
        </h1>
        <p class="lead" style="color:rgba(255,255,255,.6);max-width:560px;margin:0 auto;">
            Discover the natural wonders and cultural heritage of Kabayan — one of Benguet's most treasured destinations.
        </p>
    </div>
</div>

<section>
    <div class="container">
        <?php if (!empty($destinations)): ?>
        <div class="card-grid card-grid-3">
            <?php foreach ($destinations as $d):
                $dTrails  = json_decode($d['trails']              ?? '[]', true) ?: [];
                $dCamping = json_decode($d['camping_sites']       ?? '[]', true) ?: [];
                $dBullets = json_decode($d['bullet_descriptions'] ?? '[]', true) ?: [];
                $isOpen   = (bool)($d['is_open'] ?? 1);
                $cap      = $d['forecast_traffic'] > 0
                            ? min(100, round($d['analytics_visitors'] / $d['forecast_traffic'] * 100))
                            : 0;
            ?>
            <div class="destination-card" style="position:relative;">

                <div class="destination-card-img" style="position:relative;">
                    <img src="uploads/<?= htmlspecialchars($d['preview_image']) ?>"
                         alt="<?= htmlspecialchars($d['name']) ?>"
                         style="<?= !$isOpen ? 'filter:grayscale(.45);' : '' ?>">

                    <!-- Visitor badge -->
                    <span class="destination-card-badge">🏔 <?= number_format($d['analytics_visitors']) ?> visitors</span>

                    <!-- Open/Closed pill -->
                    <span style="position:absolute;top:10px;left:10px;font-size:10.5px;font-weight:700;letter-spacing:.06em;padding:4px 11px;border-radius:99px;
                        <?= $isOpen
                            ? 'background:rgba(22,101,52,.82);color:#bbf7d0;'
                            : 'background:rgba(153,27,27,.82);color:#fecaca;' ?>">
                        <?= $isOpen ? '✓ Open' : '✕ Closed' ?>
                    </span>
                </div>

                <div class="destination-card-body">
                    <h5><?= htmlspecialchars($d['name']) ?></h5>
                    <p><?= htmlspecialchars(substr($d['description'], 0, 110)) ?>…</p>

                    <!-- Bullet highlights (top 2) -->
                    <?php if (!empty($dBullets)): ?>
                    <ul style="list-style:none;padding:0;margin:0 0 12px;display:flex;flex-direction:column;gap:5px;">
                        <?php foreach (array_slice($dBullets, 0, 2) as $b): ?>
                        <li style="font-size:12.5px;color:var(--text-2);display:flex;align-items:center;gap:7px;">
                            <span style="color:var(--terra);font-size:12px;">✦</span>
                            <?= htmlspecialchars($b) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <!-- Trail / camp pills -->
                    <?php if (!empty($dTrails) || !empty($dCamping)): ?>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;">
                        <?php if (!empty($dTrails)): ?>
                        <span style="font-size:11px;padding:3px 9px;border-radius:99px;background:#fdf4ef;color:#c2673a;font-weight:600;">
                            🥾 <?= count($dTrails) ?> Trail<?= count($dTrails) != 1 ? 's' : '' ?>
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($dCamping)): ?>
                        <span style="font-size:11px;padding:3px 9px;border-radius:99px;background:#f0fdf4;color:#166534;font-weight:600;">
                            ⛺ <?= count($dCamping) ?> Camp<?= count($dCamping) != 1 ? 's' : '' ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Stat row -->
                    <div class="destination-card-stats">
                        <div class="stat-item">
                            <strong><?= number_format($d['analytics_visitors']) ?></strong>Visitors
                        </div>
                        <div class="stat-item">
                            <strong><?= number_format($d['forecast_traffic']) ?></strong>Forecast
                        </div>
                    </div>

                    <!-- Capacity bar -->
                    <div style="background:#f1f5f9;border-radius:4px;height:4px;margin:10px 0 14px;overflow:hidden;">
                        <div style="background:var(--terra,#c2673a);height:4px;border-radius:4px;width:<?= $cap ?>%;"></div>
                    </div>

                    <a href="destinations.php?id=<?= $d['id'] ?>" class="btn btn-terra"
                       style="<?= !$isOpen ? 'opacity:.65;' : '' ?>">
                        View Details →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="notice">No destinations available yet. Check back soon!</p>
        <?php endif; ?>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php }
include 'includes/footer.php'; ?>
