// Demo accounts
const accounts = [
  { username: "superadmin", password: "super123", role: "super", name: "Maria Santos" },
  { username: "senioradmin", password: "senior123", role: "senior", name: "Juan Dela Cruz" },
  { username: "junioradmin", password: "junior123", role: "junior", name: "Angela Reyes" }
];

let currentAdmin = null;

const rolesData = {
  super: { name: "Super Admin", badgeClass: "bg-warning text-dark" },
  senior: { name: "Senior Admin", badgeClass: "bg-primary" },
  junior: { name: "Junior Admin", badgeClass: "bg-success" }
};

// Login
function login(username, password) {
  const account = accounts.find(acc => acc.username === username && acc.password === password);
  if (account) {
    localStorage.setItem("isLoggedIn", "true");
    localStorage.setItem("currentAdmin", JSON.stringify(account));
    window.location.href = "admin.html"; // admin.html is in the root folder
  } else {
    alert("Invalid username or password");
  }
}
// Logout
function logout() {
  localStorage.removeItem("isLoggedIn");
  localStorage.removeItem("currentAdmin");
  document.getElementById("dashboardSection").classList.add("d-none");
  document.getElementById("loginSection").classList.remove("d-none");
}

// Update UI
function updateUI() {
  const roleBadge = document.getElementById("roleBadge");
  roleBadge.textContent = `Logged in as ${rolesData[currentAdmin.role].name}`;
  roleBadge.className = `badge ms-3 ${rolesData[currentAdmin.role].badgeClass}`;

  document.body.classList.remove("super-theme", "senior-theme", "junior-theme");
  document.body.classList.add(`${currentAdmin.role}-theme`);

  document.getElementById("adminName").textContent = currentAdmin.name;

  const announcementBtn = document.querySelector("#announcementForm button");
  const faqBtn = document.querySelector("#faqForm button");

  if (currentAdmin.role === "junior") {
    announcementBtn.disabled = true;
    announcementBtn.innerText = "No Permission";
    faqBtn.disabled = true;
    faqBtn.innerText = "No Permission";
  } else {
    announcementBtn.disabled = false;
    announcementBtn.innerText = "Add Announcement";
    faqBtn.disabled = false;
    faqBtn.innerText = "Add FAQ";
  }
}

// Show dashboard
function showDashboard(account) {
  currentAdmin = account;
  document.getElementById("loginSection").classList.add("d-none");
  document.getElementById("dashboardSection").classList.remove("d-none");
  updateUI();

  // Demo FAQs
  document.getElementById("faqList").innerHTML = `
    <li class="list-group-item">What is Kabayan known for? – Rice terraces & caves</li>
    <li class="list-group-item">How to reach Kabayan? – Via Baguio then bus/van</li>
  `;

  // Demo Announcements
  document.getElementById("announcementList").innerHTML = `
    <li class="list-group-item">Festival starts April 10</li>
    <li class="list-group-item">New homestay program launched</li>
  `;
}

// Initialize
document.addEventListener("DOMContentLoaded", () => {
  const storedAdmin = localStorage.getItem("currentAdmin");
  if (localStorage.getItem("isLoggedIn") === "true" && storedAdmin) {
    showDashboard(JSON.parse(storedAdmin));
  }

  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();
      login(username, password);
    });
  }
});
