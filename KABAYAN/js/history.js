// Parallax effect for hero background
window.addEventListener("scroll", () => {
    const hero = document.querySelector(".history-hero");
    let scrollPosition = window.scrollY;
    hero.style.backgroundPositionY = `${scrollPosition * 0.5}px`;
});

// Assign alternating classes (left/right) to cards
document.querySelectorAll(".card").forEach((card, index) => {
    if (index % 2 === 0) {
        card.classList.add("left");
    } else {
        card.classList.add("right");
    }
});

// Timeline reveal animation with staggered delay
const revealCards = () => {
    const cards = document.querySelectorAll(".card");
    const triggerBottom = window.innerHeight * 0.85;

    cards.forEach((card, index) => {
        const cardTop = card.getBoundingClientRect().top;
        if (cardTop < triggerBottom) {
            setTimeout(() => {
                card.classList.add("visible");
            }, index * 150);
        }
    });
};
window.addEventListener("scroll", revealCards);
window.addEventListener("load", revealCards);

// Generate stars dynamically
const starContainer = document.createElement("div");
starContainer.classList.add("stars");
document.querySelector(".history-hero").appendChild(starContainer);

const numStars = 50;
for (let i = 0; i < numStars; i++) {
    const star = document.createElement("div");
    star.classList.add("star");
    star.style.top = `${Math.random() * 100}%`;
    star.style.left = `${Math.random() * 100}%`;
    star.style.animationDelay = `${Math.random() * 5}s`;
    starContainer.appendChild(star);
}

// Control star visibility based on scroll (nightfall phase)
window.addEventListener("scroll", () => {
    const scrollPosition = window.scrollY;
    const docHeight = document.body.scrollHeight - window.innerHeight;
    const scrollPercent = scrollPosition / docHeight;

    if (scrollPercent > 0.7) {
        starContainer.style.opacity = (scrollPercent - 0.7) * 3;
    } else {
        starContainer.style.opacity = 0;
    }
});

// Shooting star generator
const hero = document.querySelector(".history-hero");
function createShootingStar() {
    const star = document.createElement("div");
    star.classList.add("shooting-star");
    star.style.top = `${Math.random() * 50}%`;
    star.style.left = `${Math.random() * 100}%`;
    hero.appendChild(star);
    setTimeout(() => star.remove(), 2000);
}
setInterval(() => {
    const scrollPosition = window.scrollY;
    const docHeight = document.body.scrollHeight - window.innerHeight;
    const scrollPercent = scrollPosition / docHeight;

    if (scrollPercent > 0.7 && Math.random() < 0.3) {
        createShootingStar();
    }
}, 4000);