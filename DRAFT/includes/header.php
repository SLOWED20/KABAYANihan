<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabayan Tourism</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600&family=Lora:ital,wght@0,400;0,500;0,600;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/public.css">
</head>

<body>

    <nav class="site-nav" id="siteNav">
        <div class="nav-inner">
            <a href="index.php" class="nav-logo">
                <div class="nav-logo-mark">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <span class="nav-logo-text">KABAYANihan</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="destinations.php">Destinations</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="profiles.php">Officials</a></li>
                <li><a href="history.php">History</a></li>
                <li><a href="faqs.php">FAQs</a></li>
            </ul>
        </div>
    </nav>

    <script>
        const nav = document.getElementById('siteNav');
        const updateNav = () => {
            if (window.scrollY > 30) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        };
        window.addEventListener('scroll', updateNav, {
            passive: true
        });
        updateNav();

        // Highlight active link
        const links = document.querySelectorAll('.nav-links a');
        const curr = window.location.pathname.split('/').pop() || 'index.php';
        links.forEach(l => {
            if (l.getAttribute('href') === curr) l.classList.add('active');
        });
    </script>