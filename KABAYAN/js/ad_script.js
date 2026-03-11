// Load existing data
let faqs = JSON.parse(localStorage.getItem("faqs")) || [];
let announcements = JSON.parse(localStorage.getItem("announcements")) || [];

function renderFAQs() {
  const faqList = document.getElementById("faqList");
  faqList.innerHTML = "";
  faqs.forEach((faq, index) => {
    faqList.innerHTML += `
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <span><strong>${faq.question}</strong>: ${faq.answer}</span>
        <button class="btn btn-sm btn-danger" onclick="deleteFAQ(${index})">Delete</button>
      </li>
    `;
  });
}

function renderAnnouncements() {
  const announcementList = document.getElementById("announcementList");
  announcementList.innerHTML = "";
  announcements.forEach((a, index) => {
    announcementList.innerHTML += `
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <span>${a.date}: ${a.text}</span>
        <button class="btn btn-sm btn-danger" onclick="deleteAnnouncement(${index})">Delete</button>
      </li>
    `;
  });
}

// Add FAQ
document.getElementById("faqForm").addEventListener("submit", e => {
  e.preventDefault();
  const question = document.getElementById("faqQuestion").value.trim();
  const answer = document.getElementById("faqAnswer").value.trim();
  if (question && answer) {
    faqs.push({ question, answer });
    localStorage.setItem("faqs", JSON.stringify(faqs));
    renderFAQs();
  }
});

// Add Announcement
document.getElementById("announcementForm").addEventListener("submit", e => {
  e.preventDefault();
  const text = document.getElementById("announcementText").value.trim();
  if (text) {
    announcements.push({ text, date: new Date().toLocaleString() });
    localStorage.setItem("announcements", JSON.stringify(announcements));
    renderAnnouncements();
  }
});

// Delete functions
function deleteFAQ(index) {
  faqs.splice(index, 1);
  localStorage.setItem("faqs", JSON.stringify(faqs));
  renderFAQs();
}

function deleteAnnouncement(index) {
  announcements.splice(index, 1);
  localStorage.setItem("announcements", JSON.stringify(announcements));
  renderAnnouncements();
}

// Initial render
renderFAQs();
renderAnnouncements();
