const navbar = document.querySelector(".navbar");
const progressBar = document.getElementById("scrollProgress");
const tooltip = document.getElementById("progressTooltip");
const sections = document.querySelectorAll("section");

// Sticky nav blur
window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }
});

// Progress bar fill
window.addEventListener("scroll", () => {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPercent = (scrollTop / docHeight) * 100;
    progressBar.style.width = scrollPercent + "%";
});

// Tooltip hover
progressBar.addEventListener("mousemove", (e) => {
    const barWidth = progressBar.offsetWidth;
    const hoverX = e.offsetX;
    const hoverPercent = (hoverX / barWidth) * 100;
    const sectionIndex = Math.floor((hoverPercent / 100) * sections.length);
    const targetSection = sections[sectionIndex];

    if (targetSection) {
        tooltip.textContent = targetSection.getAttribute("data-label") || `Section ${sectionIndex + 1}`;
        tooltip.style.left = e.pageX + "px";
        tooltip.classList.add("show");
    }
});
progressBar.addEventListener("mouseleave", () => {
    tooltip.classList.remove("show");
});

// Click to jump to section
progressBar.addEventListener("click", (e) => {
    const barWidth = progressBar.offsetWidth;
    const clickX = e.offsetX;
    const clickPercent = (clickX / barWidth) * 100;
    const sectionIndex = Math.floor((clickPercent / 100) * sections.length);
    const targetSection = sections[sectionIndex];
    if (targetSection) smoothScrollToSection(targetSection);
});

// Section markers
const docHeight = document.documentElement.scrollHeight - window.innerHeight;
sections.forEach((section, index) => {
    const marker = document.createElement("div");
    marker.classList.add("progress-marker");
    const sectionTop = section.offsetTop;
    const percent = (sectionTop / docHeight) * 100;
    marker.style.left = percent + "%";
    marker.title = section.getAttribute("data-label") || `Section ${index + 1}`;
    marker.addEventListener("click", () => {
        smoothScrollToSection(section);
    });
    document.body.appendChild(marker);
});

// Highlight active marker
window.addEventListener("scroll", () => {
    const scrollTop = window.scrollY;
    sections.forEach((section, index) => {
        const marker = document.querySelectorAll(".progress-marker")[index];
        if (scrollTop >= section.offsetTop - 50 && scrollTop < section.offsetTop + section.offsetHeight) {
            marker.classList.add("active");
        } else {
            marker.classList.remove("active");
        }
    });
});

// Cinematic overlay transition
const transitionOverlay = document.createElement("div");
transitionOverlay.classList.add("section-transition");
document.body.appendChild(transitionOverlay);

function smoothScrollToSection(section) {
    transitionOverlay.classList.add("active");
    setTimeout(() => {
        section.scrollIntoView({ behavior: "smooth" });
    }, 300);
    setTimeout(() => {
        transitionOverlay.classList.remove("active");
    }, 1000);
}