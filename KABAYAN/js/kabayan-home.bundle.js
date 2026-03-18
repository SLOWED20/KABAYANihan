/* ===========================
   Theme Toggle
   =========================== */
const toggleBtn = document.getElementById('themeToggle');
if (toggleBtn) {
  toggleBtn.addEventListener('click', () => {
    document.body.classList.toggle('dark-theme');
    document.body.classList.toggle('light-theme');

    // Staggered section transitions
    const sections = document.querySelectorAll('section');
    sections.forEach((sec, i) => {
      sec.style.transitionDelay = `${i * 0.1}s`; // 100ms stagger
    });
  });
}

/* ===========================
   FAQ Accordion
   =========================== */
const faqItems = document.querySelectorAll('.faq-item');
faqItems.forEach(item => {
  const question = item.querySelector('.faq-question');
  const answer = item.querySelector('.faq-answer');
  if (question) {
    question.addEventListener('click', () => {
      const isOpen = answer.style.display === 'block';
      document.querySelectorAll('.faq-answer').forEach(ans => ans.style.display = 'none');
      answer.style.display = isOpen ? 'none' : 'block';
    });
  }
});

/* ===========================
   Copilot Widget Toggle
   =========================== */
const copilotWidget = document.querySelector('.copilot-widget');
const copilotHeader = document.querySelector('.copilot-header');
if (copilotHeader && copilotWidget) {
  copilotHeader.addEventListener('click', () => {
    copilotWidget.classList.toggle('collapsed');
    if (copilotWidget.classList.contains('collapsed')) {
      copilotWidget.style.height = '40px';
    } else {
      copilotWidget.style.height = '300px';
    }
  });
}

/* ===========================
   Smooth Scroll for Nav Links
   =========================== */
document.querySelectorAll('.navbar .nav-link').forEach(link => {
  link.addEventListener('click', e => {
    const targetId = link.getAttribute('href');
    if (targetId.startsWith('#')) {
      e.preventDefault();
      document.querySelector(targetId).scrollIntoView({
        behavior: 'smooth'
      });
    }
  });
});
/* ===========================
   Copilot Chatbot Logic
   =========================== */
const minimizeBtn = document.getElementById('minimizeBtn');
const copilotChat = document.getElementById('copilotChat');
const copilotTrigger = document.getElementById('copilotTrigger');
const backBtn = document.getElementById('backBtn');
const faqMenu = document.getElementById('faqMenu');
const expandSound = document.getElementById('expandSound');
const collapseSound = document.getElementById('collapseSound');
const soundToggle = document.getElementById('soundToggle');
let soundEnabled = true;

// Sound toggle
if (soundToggle) {
  soundToggle.addEventListener('click', () => {
    soundEnabled = !soundEnabled;
    soundToggle.textContent = soundEnabled ? '🔊' : '🔇';
  });
}

// Minimize/Expand toggle
if (minimizeBtn) {
  minimizeBtn.addEventListener('click', () => {
    if (copilotChat.classList.contains('collapsed')) {
      copilotChat.classList.remove('collapsed');
      minimizeBtn.textContent = '−';
      copilotChat.style.height = '400px';
      copilotChat.style.opacity = '1';
      copilotChat.style.animation = 'bounceExpand 0.5s ease';
      if (soundEnabled) expandSound.play();
    } else {
      copilotChat.classList.add('collapsed');
      minimizeBtn.textContent = '+';
      copilotChat.style.height = '40px';
      copilotChat.style.opacity = '0.7';
      copilotChat.style.animation = 'bounceCollapse 0.5s ease';
      if (soundEnabled) collapseSound.play();
    }
  });
}

// Trigger icon opens chat
if (copilotTrigger) {
  copilotTrigger.addEventListener('click', () => {
    copilotChat.classList.remove('collapsed');
    minimizeBtn.textContent = '−';
    copilotChat.style.height = '400px';
    copilotChat.style.opacity = '1';
    copilotChat.style.animation = 'bounceExpand 0.5s ease';
    if (soundEnabled) expandSound.play();
    copilotChat.style.display = 'block';
  });
}

// FAQ buttons
document.querySelectorAll('.faq-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const topic = btn.dataset.topic;
    let reply = "";
    switch (topic) {
      case "tourism": reply = "Kabayan is home to Mount Pulag, Luzon’s highest peak, offering the famous 'sea of clouds' sunrise hike."; break;
      case "culture": reply = "Kabayan is renowned for its ancient fire mummies, preserved in caves, reflecting deep Cordilleran traditions."; break;
      case "food": reply = "Local delicacies include pinikpikan, etag (smoked meat), and fresh vegetables from Kabayan’s highland farms."; break;
      case "festival": reply = "Kabayan celebrates harvest rituals and Cordilleran festivals with indigenous dances, chants, and community feasts."; break;
      case "lodging": reply = "Visitors can stay in homestays and small inns near Mount Pulag jump-off points, offering warm hospitality."; break;
      case "travel": reply = "Kabayan is accessible via Baguio City, with jeepneys and buses heading toward Benguet towns. Travel time is 4–5 hours."; break;
      case "season": reply = "The best time to visit Kabayan is November to April, during the dry season, for clear skies and safe hikes."; break;
    }
    const replyBubble = document.createElement('p');
    replyBubble.innerHTML = `<strong>Copilot:</strong> ${reply}`;
    document.getElementById('chatBody').appendChild(replyBubble);
    document.getElementById('chatBody').scrollTop = document.getElementById('chatBody').scrollHeight;

    faqMenu.style.display = 'none';
    backBtn.style.display = 'block';
  });
});

// Back to Menu
if (backBtn) {
  backBtn.addEventListener('click', () => {
    faqMenu.style.display = 'flex';
    backBtn.style.display = 'none';
  });
}

// Quick Links
document.querySelectorAll('.quick-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    let reply = "";
    switch (btn.dataset.topic) {
      case "pulag": reply = "Mount Pulag is Luzon’s highest peak, famous for its 'sea of clouds' and breathtaking sunrise hikes."; break;
      case "mummies": reply = "Kabayan’s fire mummies are centuries-old preserved bodies found in caves, a unique cultural heritage site."; break;
      case "foodspots": reply = "Kabayan offers homestyle eateries serving pinikpikan, etag, and fresh vegetables — perfect after a mountain trek."; break;
    }
    const replyBubble = document.createElement('p');
    replyBubble.innerHTML = `<strong>Copilot:</strong> ${reply}`;
    document.getElementById('chatBody').appendChild(replyBubble);
    document.getElementById('chatBody').scrollTop = document.getElementById('chatBody').scrollHeight;
  });
});