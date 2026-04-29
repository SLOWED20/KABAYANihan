/* ============================================================
   Municipal CMS — Global Admin JavaScript
   Location: assets/js/admin.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ──────────────────────────────────────────────────────────
     1. LOGIN PAGE: Tab switcher (Sign In / Register)
     ────────────────────────────────────────────────────────── */
  document.querySelectorAll('[data-tab]').forEach(function (tab) {
    tab.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelectorAll('.nav-link').forEach(function (t) {
        t.classList.remove('active');
      });
      document.querySelectorAll('.tab-pane').forEach(function (p) {
        p.classList.remove('show', 'active');
      });
      tab.classList.add('active');
      var target = document.getElementById(tab.dataset.tab);
      if (target) target.classList.add('show', 'active');
    });
  });

  /* ──────────────────────────────────────────────────────────
     2. DELETED LOGS: Module filter tabs
     ────────────────────────────────────────────────────────── */
  var tabBtns = document.querySelectorAll('.module-tab-btn');
  var sections = document.querySelectorAll('.module-section');

  if (tabBtns.length > 0) {
    tabBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = btn.dataset.module;

        // Update active button
        tabBtns.forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');

        // Show/hide sections
        sections.forEach(function (sec) {
          if (target === 'all' || sec.dataset.module === target) {
            sec.classList.add('visible');
          } else {
            sec.classList.remove('visible');
          }
        });
      });
    });
  }

  /* ──────────────────────────────────────────────────────────
     3. AUTO-DISMISS alerts after 4 seconds
     ────────────────────────────────────────────────────────── */
  document.querySelectorAll('.alert').forEach(function (alert) {
    setTimeout(function () {
      alert.style.transition = 'opacity .4s';
      alert.style.opacity = '0';
      setTimeout(function () {
        if (alert.parentNode) alert.parentNode.removeChild(alert);
      }, 400);
    }, 4000);
  });

  /* ──────────────────────────────────────────────────────────
     4. DESTINATIONS: Repeater rows — Add Trail
     ────────────────────────────────────────────────────────── */
  window.addTrailRow = function (containerId) {
    var c = document.getElementById(containerId);
    if (!c) return;
    var div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML =
      '<button type="button" class="remove-row" onclick="this.closest(\'.repeater-row\').remove()">×</button>' +
      '<div class="row g-2">' +
        '<div class="col-md-3"><input type="text" name="trail_name[]" class="form-control form-control-sm" placeholder="Trail name"></div>' +
        '<div class="col-md-3"><input type="text" name="trail_jumpoff[]" class="form-control form-control-sm" placeholder="Jump-off point"></div>' +
        '<div class="col-md-3">' +
          '<select name="trail_difficulty[]" class="form-control form-control-sm">' +
            '<option value="">Difficulty</option>' +
            '<option>Easy</option><option>Moderate</option><option>Difficult</option><option>Expert</option>' +
          '</select>' +
        '</div>' +
        '<div class="col-md-3"><input type="text" name="trail_duration[]" class="form-control form-control-sm" placeholder="Duration (e.g. 3-4 hrs)"></div>' +
      '</div>';
    c.appendChild(div);
  };

  /* ──────────────────────────────────────────────────────────
     5. DESTINATIONS: Repeater rows — Add Camping Site
     ────────────────────────────────────────────────────────── */
  window.addCampRow = function (containerId) {
    var c = document.getElementById(containerId);
    if (!c) return;
    var div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML =
      '<button type="button" class="remove-row" onclick="this.closest(\'.repeater-row\').remove()">×</button>' +
      '<div class="row g-2 align-items-end">' +
        '<div class="col-md-3"><input type="text" name="camp_name[]" class="form-control form-control-sm" placeholder="Site name"></div>' +
        '<div class="col-md-3"><input type="text" name="camp_location[]" class="form-control form-control-sm" placeholder="Location/coordinates"></div>' +
        '<div class="col-md-3"><input type="text" name="camp_capacity[]" class="form-control form-control-sm" placeholder="Capacity (e.g. 20 tents)"></div>' +
        '<div class="col-md-3"><input type="file" name="camp_image[]" class="form-control form-control-sm" accept="image/*"></div>' +
      '</div>';
    c.appendChild(div);
  };

  /* ──────────────────────────────────────────────────────────
     6. MOBILE: Sidebar toggle (hamburger)
     ────────────────────────────────────────────────────────── */
  var hamburger = document.getElementById('sidebarToggle');
  var sidebar   = document.getElementById('sidebar');
  if (hamburger && sidebar) {
    hamburger.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (e) {
      if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && e.target !== hamburger) {
          sidebar.classList.remove('open');
        }
      }
    });
  }

  /* ──────────────────────────────────────────────────────────
     7. TABLE ROWS: Confirm before navigation on delete links
        (already handled inline with onclick="return confirm(...)"
         but this catches any that don't have it)
     ────────────────────────────────────────────────────────── */
  // No additional handling needed; inline confirms are sufficient.

});
