<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

// Fetching data categorized by role/type
$result = $conn->query("SELECT * FROM profiles ORDER BY name ASC");
$all_data = $result->fetch_all(MYSQLI_ASSOC);

// Grouping data for the chart
$mayor = array_filter($all_data, fn($p) => strtolower($p['position'] ?? '') == 'mayor');
$vice_mayor = array_filter($all_data, fn($p) => strtolower($p['position'] ?? '') == 'vice mayor');
$councilors = array_filter($all_data, fn($p) => strtolower($p['category'] ?? '') == 'councilor');
$barangays = array_filter($all_data, fn($p) => strtolower($p['category'] ?? '') == 'barangay');
$offices = array_filter($all_data, fn($p) => strtolower($p['category'] ?? '') == 'office');
?>

<style>
    :root {
        --line-color: #ccc;
    }

    .org-chart-wrapper {
        padding: 60px 0;
        background: #fdfdfd;
        overflow-x: auto;
    }

    .org-tree {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 40px;
        min-width: 1000px;
    }

    /* Hierarchy Labels */
    .level-label {
        font-weight: 800;
        text-transform: uppercase;
        color: var(--umber);
        letter-spacing: 2px;
        margin-bottom: 20px;
        position: relative;
        width: 100%;
        text-align: center;
    }

    .level-label::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background: var(--terra-light);
    }

    /* Cards */
    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        border: 1px solid #eee;
        transition: 0.3s ease;
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .chart-card:hover {
        transform: translateY(-5px);
        border-color: var(--terra-light);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .chart-card img {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        outline: 2px solid var(--terra-light);
        margin-bottom: 12px;
    }

    /* Layout Sections */
    .executive-row {
        display: flex;
        gap: 50px;
        justify-content: center;
    }

    .grid-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        width: 100%;
        max-width: 1100px;
    }

    .barangay-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        width: 100%;
        max-width: 1200px;
    }

    .office-badge {
        background: var(--umber);
        color: white;
        padding: 12px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: 0.2s;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .office-badge:hover {
        background: var(--terra-light);
        color: white;
    }

    /* Modal Styling */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(5px);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        padding: 40px;
        border-radius: 20px;
        max-width: 500px;
        width: 90%;
        text-align: center;
    }
</style>

<div style="background:var(--umber);padding:80px 0;text-align:center;">
    <div class="container">
        <h1 style="color:white; font-size: 3rem;">Organizational Chart</h1>
        <p style="color:rgba(255,255,255,.8); font-size: 1.2rem;">Municipality of Kabayan</p>
    </div>
</div>

<section class="org-chart-wrapper">
    <div class="container org-tree">

        <div class="level-label">Executive Leadership</div>
        <div class="executive-row">
            <?php foreach ($mayor as $p): ?>
                <div class="chart-card" style="width: 250px;" onclick="openModal('<?= addslashes($p['name']) ?>', 'As your Mayor, I am dedicated to serving the people of Kabayan with integrity and transparency. Our office is always open to your concerns.')">
                    <img src="uploads/<?= $p['image'] ?>" alt="Mayor">
                    <h5 class="mb-0"><?= htmlspecialchars($p['name']) ?></h5>
                    <span class="badge bg-primary">MAYOR</span>
                </div>
            <?php endforeach; ?>

            <?php foreach ($vice_mayor as $p): ?>
                <div class="chart-card" style="width: 250px;" onclick="openModal('<?= addslashes($p['name']) ?>', 'Greetings! In the Vice Mayor\'s office, we focus on legislative excellence and community-driven policies for a better Kabayan.')">
                    <img src="uploads/<?= $p['image'] ?>" alt="Vice Mayor">
                    <h5 class="mb-0"><?= htmlspecialchars($p['name']) ?></h5>
                    <span class="badge bg-info text-dark">VICE MAYOR</span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="level-label">Sangguniang Bayan (Councilors)</div>
        <div class="grid-row">
            <?php foreach ($councilors as $p): ?>
                <div class="chart-card" onclick="openModal('Councilor <?= addslashes($p['name']) ?>', 'A member of the Sangguniang Bayan, working on ordinances and resolutions that benefit our local community.')">
                    <h6 class="mb-1"><?= htmlspecialchars($p['name']) ?></h6>
                    <small class="text-muted">Councilor</small>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="level-label">Municipal Offices & Departments</div>
        <div class="grid-row">
            <?php foreach ($offices as $p): ?>
                <a href="services.php?office=<?= urlencode($p['name']) ?>" class="office-badge">
                    <?= htmlspecialchars($p['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="level-label">The 13 Barangays of Kabayan</div>
        <div class="barangay-row">
            <?php foreach ($barangays as $p): ?>
                <div class="chart-card" onclick="openModal('Barangay <?= addslashes($p['name']) ?>', 'Welcome to Barangay <?= addslashes($p['name']) ?>. Location: <?= addslashes($p['description']) ?>. We are here to provide local governance and primary services.')">
                    <img src="uploads/<?= $p['image'] ?>" style="width:60px; height:60px; outline:none; border: 1px solid #ddd;" alt="Logo">
                    <h6 class="mb-1" style="font-size: 0.9rem;"><?= htmlspecialchars($p['name']) ?></h6>
                    <p class="text-muted mb-0" style="font-size: 0.75rem;"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<div id="infoModal" class="modal-overlay" onclick="closeModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <h2 id="modalTitle" style="color:var(--umber);"></h2>
        <hr>
        <p id="modalBody" style="font-size: 1.1rem; color: #444; margin-bottom: 30px;"></p>
        <button onclick="closeModal()" class="btn btn-lg" style="background:var(--terra-light); color:white; width: 100%;">Close</button>
    </div>
</div>

<script>
    function openModal(title, message) {
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalBody').innerText = message;
        document.getElementById('infoModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('infoModal').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('infoModal')) closeModal();
    }
</script>

<?php include 'includes/footer.php'; ?>