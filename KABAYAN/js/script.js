document.addEventListener("DOMContentLoaded", () => {
  const faqItems = document.querySelectorAll(".faq-item h3");

  faqItems.forEach(item => {
    item.addEventListener("click", () => {
      const answer = item.nextElementSibling;
      answer.classList.toggle("d-none");
    });
  });
});
// Optional: Auto-cycle carousel every 5 seconds
document.addEventListener("DOMContentLoaded", () => {
  const carousel = document.querySelector("#destinationCarousel");
  if (carousel) {
    new bootstrap.Carousel(carousel, {
      interval: 5000,
      ride: 'carousel'
    });
  }
});
// Simple site-wide search mockup
document.addEventListener("DOMContentLoaded", () => {
  const searchForm = document.getElementById("searchForm");
  const searchInput = document.getElementById("searchInput");
  const resultsContainer = document.getElementById("results");

  // Content index (mock data)
  const contentIndex = [
    { title: "Community Profiles", page: "profile.html", text: "Meet the people behind Kabayan’s tourism and heritage." },
    { title: "Rich History", page: "history.html", text: "Discover Kabayan’s heritage from ancient times to today." },
    { title: "Destinations", page: "destination.html", text: "Experience breathtaking landscapes and cultural sites." },
    { title: "FAQ", page: "faq.html", text: "Find quick answers to common visitor questions." },
    { title: "Admin Announcements", page: "admin.html", text: "Staff updates and community announcements." }
  ];

  // Handle search submission
  if (searchForm) {
    searchForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const query = searchInput.value.trim().toLowerCase();
      if (query) {
        window.location.href = `search.html?q=${encodeURIComponent(query)}`;
      }
    });
  }

  // Display results if on search.html
  if (resultsContainer) {
    const params = new URLSearchParams(window.location.search);
    const query = params.get("q");
    if (query) {
      const matches = contentIndex.filter(item =>
        item.title.toLowerCase().includes(query) || item.text.toLowerCase().includes(query)
      );

      if (matches.length > 0) {
        matches.forEach(match => {
          const card = document.createElement("div");
          card.className = "col-md-4";
          card.innerHTML = `
            <div class="card shadow h-100">
              <div class="card-body">
                <h5 class="card-title">${match.title}</h5>
                <p class="card-text">${match.text}</p>
                <a href="${match.page}" class="btn btn-primary">Go to Page</a>
              </div>
            </div>
          `;
          resultsContainer.appendChild(card);
        });
      } else {
        resultsContainer.innerHTML = `<p class="text-center text-muted">No results found for "${query}".</p>`;
      }
    }
  }
});
//FAQS
const faqs = JSON.parse(localStorage.getItem("faqs")) || [];
faqs.forEach(faq => {
  document.getElementById("faqList").innerHTML += `
    <div class="accordion-item">
      <h2 class="accordion-header">${faq.question}</h2>
      <div class="accordion-body">${faq.answer}</div>
    </div>
  `;
});

//index
const announcements = JSON.parse(localStorage.getItem("announcements")) || [];
announcements.forEach(a => {
  document.getElementById("announcementList").innerHTML += `
    <li>${a.date}: ${a.text}</li>
  `;
});