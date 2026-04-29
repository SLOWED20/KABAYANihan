<?php
session_start();
include 'includes/header.php';
?>

<div style="background:linear-gradient(160deg,var(--umber) 0%,var(--forest) 100%);padding:140px 0 80px;text-align:center;">
  <div class="container">
    <span class="section-label" style="color:var(--terra-light);">Heritage & Culture</span>
    <h1 style="color:white;margin-bottom:16px;">The History of<br><em style="font-style:italic;color:var(--terra-light);">Kabayan</em></h1>
    <p class="lead" style="color:rgba(255,255,255,.6);max-width:560px;margin:0 auto;">From sacred mummy caves to a thriving eco-tourism destination — a story thousands of years in the making.</p>
  </div>
</div>

<!-- INTRO -->
<section style="background:var(--ivory);">
  <div class="container">
    <div style="max-width:760px;margin:0 auto;text-align:center;">
      <span class="section-label">Brief Background</span>
      <h2 style="margin-bottom:20px;">Home of the<br><em style="font-style:italic;color:var(--terra);">Fire Mummies</em></h2>
      <p style="color:var(--text-2);font-size:15.5px;line-height:1.85;">
        Kabayan, located in Benguet, is renowned as the <strong>"Home of the Fire Mummies."</strong>
        Its caves preserve centuries-old cultural practices, making it one of the most
        important heritage towns in the Cordillera. The community has balanced tradition
        with modern development, keeping its identity intact through generations.
      </p>
    </div>
  </div>
</section>

<!-- TIMELINE -->
<section style="background:var(--ivory-2);">
  <div class="container">
    <div class="section-heading">
      <span class="section-label">Through the Ages</span>
      <h2>Historical Timeline</h2>
      <div class="section-divider"></div>
    </div>
    <div class="history-layout">
      <?php
      $eras = [
        ['era'=>'Pre-Colonial', 'content'=>'Long before foreign colonizers arrived, the Ibaloi people of Kabayan developed a sophisticated mummification practice. The fire mummies — preserved through a careful process of smoking and salt application — are testament to an advanced understanding of preservation. These mummies rest in limestone caves throughout the mountains, their secrets still studied by historians and archaeologists today.'],
        ['era'=>'Spanish Era', 'content'=>'During the Spanish period, explorers attempted to pacify the Igorots and establish administrative centers in Benguet. Trails, churches, and schools were introduced, laying foundations for governance and trade. Coffee was also brought into the region, which became part of Kabayan\'s agricultural identity that endures to this day.'],
        ['era'=>'American Era', 'content'=>'The arrival of the Americans brought new infrastructure, education systems, and governance models. Kabayan\'s cultural sites gained recognition, and preservation efforts began to protect its unique mummy caves and indigenous traditions. Roads connecting Kabayan to La Trinidad were established, opening the town to broader commerce.'],
        ['era'=>'Modern Era', 'content'=>'Today, Kabayan is a center for eco-tourism and cultural heritage. With sustainable tourism initiatives, homestays, and guided tours, the town continues to share its rich history while empowering local communities. UNESCO recognition efforts for the mummy caves continue, with the goal of sharing this irreplaceable heritage with the world.'],
      ];
      foreach ($eras as $i => $e): ?>
      <div class="history-block">
        <div class="history-block-label"><h3><?= $e['era'] ?></h3></div>
        <div class="history-block-dot"></div>
        <div class="history-block-content"><p><?= $e['content'] ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- MISSION & VISION -->
<section style="background:var(--ivory);">
  <div class="container">
    <div class="section-heading">
      <span class="section-label">Our Direction</span>
      <h2>Mission & Vision</h2>
      <div class="section-divider"></div>
    </div>
    <div class="mission-vision-grid">
      <div class="mv-card">
        <h3>Our Mission</h3>
        <p>To preserve Kabayan's cultural heritage, promote sustainable tourism, and empower local communities through inclusive development and education — ensuring that the traditions of our ancestors continue to inspire future generations.</p>
      </div>
      <div class="mv-card" style="border-top-color:var(--forest);">
        <h3>Our Vision</h3>
        <p>A globally recognized heritage town where culture, nature, and community thrive together, ensuring prosperity and pride for future generations — a Kabayan that is known the world over as a jewel of the Cordillera.</p>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
