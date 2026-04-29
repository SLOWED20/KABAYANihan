<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';
$result = $conn->query("SELECT * FROM faqs ORDER BY created_at DESC");
$faqs = $result->fetch_all(MYSQLI_ASSOC);
?>

<div style="background:linear-gradient(135deg,var(--terra-pale) 0%,var(--ivory-2) 100%);padding:140px 0 72px;text-align:center;">
  <div class="container">
    <span class="section-label">Help & Support</span>
    <h1 style="margin-bottom:14px;">Frequently Asked<br><em style="font-style:italic;color:var(--terra);">Questions</em></h1>
    <p class="lead" style="max-width:520px;margin:0 auto;">Everything you need to know about visiting Kabayan, its attractions, and municipal services.</p>
  </div>
</div>

<section style="background:var(--ivory);">
  <div class="container">
    <?php if (!empty($faqs)): ?>
    <div class="faq-list">
      <?php foreach ($faqs as $index => $f): ?>
      <div class="faq-item <?= $index === 0 ? 'open' : '' ?>" onclick="this.classList.toggle('open')">
        <div class="faq-question">
          <span class="faq-question-text"><?= htmlspecialchars($f['question']) ?></span>
          <span class="faq-icon">+</span>
        </div>
        <div class="faq-answer">
          <div class="faq-answer-inner">
            <?= nl2br(htmlspecialchars($f['answer'])) ?>
            <div class="faq-meta">Added <?= date("F j, Y", strtotime($f['created_at'])) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p class="notice">No FAQs available at the moment.</p>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
