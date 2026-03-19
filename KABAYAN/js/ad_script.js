// Run guard only if we're on admin.html
if (window.location.pathname.includes("admin.html")) {
  if (localStorage.getItem("isLoggedIn") !== "true") {
    window.location.href = "login.html";
  }
}


// Session timeout (30 minutes)
const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes in ms
function resetSessionTimer() {
  clearTimeout(window.sessionTimer);
  window.sessionTimer = setTimeout(() => {
    alert("Session expired. Please log in again.");
    logout();
  }, SESSION_TIMEOUT);
}
["click", "keypress", "mousemove", "scroll"].forEach(evt => {
  document.addEventListener(evt, resetSessionTimer);
});
resetSessionTimer();

// Login handler
function login(username, password) {
  let role = null;
  if (username === "superadmin" && password === "super123") {
    role = "Super";
  } else if (username === "senioradmin" && password === "senior123") {
    role = "Senior";
  } else if (username === "junioradmin" && password === "junior123") {
    role = "Junior";
  }
  if (role) {
    localStorage.setItem("isLoggedIn", "true");
    localStorage.setItem("role", role);
    window.location.href = "admin.html";
  } else {
    alert("Invalid credentials. Please try again.");
  }
}

// Logout handler
function logout() {
  localStorage.removeItem("isLoggedIn");
  localStorage.removeItem("role");
  window.location.href = "login.html";
}

document.addEventListener("DOMContentLoaded", () => {
  const role = localStorage.getItem("role");
  const badge = document.getElementById("roleBadge");

  // Badge styling
  badge.textContent = role + " Admin";
  if (role === "Super") badge.classList.add("badge-super");
  if (role === "Senior") badge.classList.add("badge-senior");
  if (role === "Junior") badge.classList.add("badge-junior");

  // Role-based tab visibility
  if (role === "Senior") {
    document.querySelector('[data-bs-target="#faqs"]').style.display = "none";
    document.querySelector('[data-bs-target="#services"]').style.display = "none";
  } else if (role === "Junior") {
    document.querySelector('[data-bs-target="#announcements"]').style.display = "none";
    document.querySelector('[data-bs-target="#destinations"]').style.display = "none";
  }

  // Load stored data
  loadFAQs(); loadAnnouncements(); loadDestinations(); loadServices();

  // FAQ submission
  faqForm.addEventListener("submit", (e) => {
    e.preventDefault();
    if (role !== "Junior" && role !== "Super") return alert("No permission");
    const faqs = JSON.parse(localStorage.getItem("faqs") || "[]");
    faqs.push({ id: Date.now(), question: faqQuestion.value, answer: faqAnswer.value, addedBy: role });
    localStorage.setItem("faqs", JSON.stringify(faqs));
    loadFAQs(); e.target.reset();
  });

  // Announcement submission
  announcementForm.addEventListener("submit", (e) => {
    e.preventDefault();
    if (role !== "Senior" && role !== "Super") return alert("No permission");
    const announcements = JSON.parse(localStorage.getItem("announcements") || "[]");
    announcements.push({ id: Date.now(), text: announcementText.value, addedBy: role });
    localStorage.setItem("announcements", JSON.stringify(announcements));
    loadAnnouncements(); e.target.reset();
  });

  // Destination submission
  destinationForm.addEventListener("submit", (e) => {
    e.preventDefault();
    if (role !== "Senior" && role !== "Super") return alert("No permission");
    const destinations = JSON.parse(localStorage.getItem("destinations") || "[]");
    destinations.push({ id: Date.now(), name: destinationName.value, desc: destinationDesc.value, addedBy: role });
    localStorage.setItem("destinations", JSON.stringify(destinations));
    loadDestinations(); e.target.reset();
  });

  // Service submission
  serviceForm.addEventListener("submit", (e) => {
    e.preventDefault();
    if (role !== "Junior" && role !== "Super") return alert("No permission");
    const services = JSON.parse(localStorage.getItem("services") || "[]");
    services.push({ id: Date.now(), name: serviceName.value, desc: serviceDesc.value, addedBy: role });
    localStorage.setItem("services", JSON.stringify(services));
    loadServices(); e.target.reset();
  });

  // Loaders with delete buttons
  function loadFAQs() {
    const faqs = JSON.parse(localStorage.getItem("faqs") || "[]");
    faqList.innerHTML = "";
    faqs.forEach((f, i) => {
      const li = document.createElement("li");
      li.className = "list-group-item d-flex justify-content-between align-items-center";
      li.textContent = `${f.question} – ${f.answer} (by ${f.addedBy})`;
      const btn = document.createElement("button");
      btn.className = "btn btn-sm btn-danger ms-2"; btn.textContent = "Delete";
      btn.onclick = () => {
        if (role === "Super" || (role === "Junior" && f.addedBy === role)) {
          faqs.splice(i, 1); localStorage.setItem("faqs", JSON.stringify(faqs)); loadFAQs();
        } else alert("No permission to delete");
      };
      li.appendChild(btn); faqList.appendChild(li);
    });
  }

  function loadAnnouncements() {
    const announcements = JSON.parse(localStorage.getItem("announcements") || "[]");
    announcementList.innerHTML = "";
    announcements.forEach((a, i) => {
      const li = document.createElement("li");
      li.className = "list-group-item d-flex justify-content-between align-items-center";
      li.textContent = `${a.text} (by ${a.addedBy})`;
      const btn = document.createElement("button");
      btn.className = "btn btn-sm btn-danger ms-2"; btn.textContent = "Delete";
      btn.onclick = () => {
        if (role === "Super" || (role === "Senior" && a.addedBy === role)) {
          announcements.splice(i, 1); localStorage.setItem("announcements", JSON.stringify(announcements)); loadAnnouncements();
        } else alert("No permission to delete");
      };
      li.appendChild(btn); announcementList.appendChild(li);
    });
  }

  function loadDestinations() {
    const destinations = JSON.parse(localStorage.getItem("destinations") || "[]");
    destinationList.innerHTML = "";
    destinations.forEach((d, i) => {
      const li = document.createElement("li");
      li.className = "list-group-item d-flex justify-content-between align-items-center";
      li.textContent = `${d.name} – ${d.desc} (by ${d.addedBy})`;
      const btn = document.createElement("button");
      btn.className = "btn btn-sm btn-danger ms-2"; btn.textContent = "Delete";
      btn.onclick = () => {
        if (role === "Super" || (role === "Senior" && d.addedBy === role)) {
          destinations.splice(i, 1); localStorage.setItem("destinations", JSON.stringify(destinations)); loadDestinations();
        } else alert("No permission to delete");
      };
      li.appendChild(btn); destinationList.appendChild(li);
    });
  }

  function loadServices() {
    const services = JSON.parse(localStorage.getItem("services") || "[]");
    serviceList.innerHTML = "";
    services.forEach((s, i) => {
      const li = document.createElement("li");
      li.className = "list-group-item d-flex justify-content-between align-items-center";
      li.textContent = `${s.name} – ${s.desc} (by ${s.addedBy})`;
      const btn = document.createElement("button");
      btn.className = "btn btn-sm btn-danger ms-2"; btn.textContent = "Delete";
      btn.onclick = () => {
        if (role === "Super" || (role === "Junior" && s.addedBy === role)) {
          services.splice(i, 1); localStorage.setItem("services", JSON.stringify(services)); loadServices();
        } else alert("No permission to delete");
      };
      li.appendChild(btn); serviceList.appendChild(li);
    });
  }
});
