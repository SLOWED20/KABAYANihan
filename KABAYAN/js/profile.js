// Page-load transition
window.addEventListener("load", () => {
    document.body.style.opacity = "1";

    const hero = document.querySelector(".profile-hero");
    setTimeout(() => {
        hero.classList.add("visible");

        // Trigger floating animation after hero is visible
        hero.querySelectorAll("h1, p").forEach(el => {
            el.style.animationPlayState = "running";
        });
    }, 300);
});

// Navbar scroll effect
window.addEventListener("scroll", () => {
    const navbar = document.querySelector(".navbar");
    if (window.scrollY > 50) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }
});

// Search box animation
const searchInput = document.getElementById("searchInput");
const noResults = document.getElementById("noResults");
const searchBtn = document.querySelector(".btn-primary");

searchBtn.addEventListener("click", () => {
    const query = searchInput.value.trim().toLowerCase();
    if (!query) {
        noResults.style.display = "block";
        noResults.textContent = "Please enter a search term.";
        return;
    }
    noResults.style.display = "none";
    searchInput.classList.add("highlight");
    setTimeout(() => searchInput.classList.remove("highlight"), 800);
});

// Staggered card reveal with scroll throttling
const cards = document.querySelectorAll(".card");
const revealCards = () => {
    cards.forEach((card, index) => {
        const rect = card.getBoundingClientRect();
        if (rect.top < window.innerHeight - 50) {
            setTimeout(() => card.classList.add("visible"), index * 150);
        }
    });
};

let ticking = false;
window.addEventListener("scroll", () => {
    if (!ticking) {
        window.requestAnimationFrame(() => {
            revealCards();
            ticking = false;
        });
        ticking = true;
    }
});
window.addEventListener("load", revealCards);

// Accessibility: disable JS-driven animations if reduced motion is preferred
const mediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
if (mediaQuery.matches) {
    window.removeEventListener("scroll", revealCards);
    window.removeEventListener("load", revealCards);
    cards.forEach(card => card.classList.add("visible"));
}