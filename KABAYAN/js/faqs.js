// Scroll to Top Button
const scrollTopBtn = document.createElement("div");
scrollTopBtn.className = "scroll-top";
scrollTopBtn.innerHTML = "⬆";
document.body.appendChild(scrollTopBtn);

window.addEventListener("scroll", () => {
    if (window.scrollY > window.innerHeight / 2) {
        scrollTopBtn.classList.add("show");
    } else {
        scrollTopBtn.classList.remove("show");
    }
});

scrollTopBtn.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
});