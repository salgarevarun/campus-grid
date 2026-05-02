<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = false;

if ($is_logged_in) {
    $uid = $_SESSION['user_id'];
    $role_query = mysqli_query($conn, "SELECT role FROM users WHERE id = '$uid'");
    $user_data = mysqli_fetch_assoc($role_query);
    if ($user_data && $user_data['role'] === 'admin') {
        $is_admin = true;
    }
}

$current_type = $_GET['type'] ?? '';
$current_search = $_GET['search'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Grid</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
</head>

<body class="<?= (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark-mode' : '' ?>">

    <div id="loader-wrapper" class="loader-wrapper">
        <div id="moving-logo" class="loader-logo">
            <span class="material-icons" style="font-size: 4rem; color: var(--primary);">school</span>
            Campus<span>Grid</span>
        </div>
        <div class="loader-line"></div>
    </div>

    <nav class="main-nav">
        <div class="nav-container">
            <a href="index.php" class="brand">
                <span class="material-icons" style="vertical-align: middle;">school</span> Campus<span>Grid</span>
            </a>

            <div class="menu">
                <a href="index.php" class="link <?= ($current_page === 'index.php' && !$current_type) ? 'active' : '' ?>">
                    <span class="material-icons">home</span><span class="link-title">Home</span>
                </a>
                <a href="index.php?type=notice" class="link <?= ($current_type === 'notice') ? 'active' : '' ?>">
                    <span class="material-icons">campaign</span><span class="link-title">Notices</span>
                </a>
                <a href="index.php?type=status" class="link <?= ($current_type === 'status') ? 'active' : '' ?>">
                    <span class="material-icons">timer</span><span class="link-title">Status</span>
                </a>
                <a href="index.php?type=code" class="link <?= ($current_type === 'code') ? 'active' : '' ?>">
                    <span class="material-icons">code</span><span class="link-title">Code</span>
                </a>
                <a href="index.php?type=market" class="link <?= ($current_type === 'market') ? 'active' : '' ?>">
                    <span class="material-icons">storefront</span><span class="link-title">Market</span>
                </a>
                <a href="index.php?type=lostfound" class="link <?= ($current_type === 'lostfound') ? 'active' : '' ?>">
                    <span class="material-icons">location_searching</span><span class="link-title">Lost</span>
                </a>
            </div>

            <div class="nav-right" style="display:flex; align-items:center; gap:15px;">
                <div class="search-wrapper">
                    <form action="index.php" method="GET" class="search-box-unified <?= !empty($current_search) ? 'search-active' : '' ?>">
                        <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($current_search) ?>" autocomplete="off">
                        <button type="submit"><span class="material-icons">search</span></button>
                    </form>
                </div>

                <?php if ($is_logged_in): ?>
                    <a href="upload.php" class="btn-posh">
                        <span class="material-icons">add_circle</span> <span>Create Post</span>
                    </a>

                    <div class="user-dropdown">
                        <div class="profile-trigger" style="cursor: pointer;">
                            <div style="width:38px; height:38px; border-radius:50%; background:var(--primary); display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; border: 2px solid var(--bg-body); box-shadow: 0 0 0 2px var(--border-color); font-size: 1rem;">
                                <?= substr(strtoupper($_SESSION['username']), 0, 1) ?>
                            </div>
                        </div>

                        <div class="dropdown-content" style="min-width: 220px;">
                            <a href="profile.php" style="font-weight: 700; color: var(--primary);">
                                <span class="material-icons" style="color: var(--primary);">badge</span> My Profile
                            </a>
                            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 5px 0;">

                            <?php if ($is_admin): ?>
                                <a href="admin_dashboard.php" style="color: var(--primary); font-weight: bold;">
                                    <span class="material-icons">dashboard_customize</span> Admin Panel
                                </a>
                                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 5px 0;">
                            <?php endif; ?>

                            <a href="user_settings.php"><span class="material-icons">settings</span> Settings</a>
                            <a href="logout.php" style="color: #ef4444;"><span class="material-icons" style="color: #ef4444;">logout</span> Logout</a>
                        </div>
                    </div>

                <?php else: ?>
                    <a href="auth.php" class="btn-signup-nav" style="display: flex; align-items: center; gap: 8px; padding: 10px 24px;">
                        <span class="material-icons" style="font-size: 20px;">login</span>
                        <span>Access Grid</span>
                    </a>
                <?php endif; ?>

                <label class="hamburger">
                    <input type="checkbox" id="menu-checkbox">
                    <svg viewBox="0 0 32 32">
                        <path class="line line-top-bottom" d="M27 10 13 10C10.8 10 9 8.2 9 6 9 3.5 10.8 2 13 2 15.2 2 17 3.8 17 6L17 26C17 28.2 18.8 30 21 30 23.2 30 25 28.2 25 26 25 23.8 23.2 22 21 22L7 22"></path>
                        <path class="line" d="M7 16 27 16"></path>
                    </svg>
                </label>
            </div>
        </div>
    </nav>

    <div id="curtain-menu" class="curtain-menu">
        <div class="curtain-content">
            <div class="curtain-links">
                <a href="index.php" class="curtain-link">Home <span class="material-icons">home</span></a>
                <a href="about.php" class="curtain-link">About Us <span class="material-icons">north_east</span></a>
                <a href="contact.php" class="curtain-link">Contact <span class="material-icons">north_east</span></a>
                <?php if (!$is_logged_in): ?>
                    <a href="auth.php" class="curtain-link highlight">Login / Join <span class="material-icons">login</span></a>
                <?php endif; ?>
            </div>
            <a href="#" class="curtain-link" id="dark-mode-toggle">Theme <span class="material-icons">dark_mode</span></a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="spa.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // --- UI LOGIC (Theme & Menu) ---
            const checkbox = document.getElementById('menu-checkbox');
            const curtain = document.getElementById('curtain-menu');
            const themeBtn = document.getElementById('dark-mode-toggle');
            const body = document.body;

            if (localStorage.getItem('theme') === 'dark') {
                body.classList.add('dark-mode');
                if (themeBtn) themeBtn.querySelector('.material-icons').innerText = 'light_mode';
            }
            if (themeBtn) {
                themeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    body.classList.toggle('dark-mode');
                    const isDark = body.classList.contains('dark-mode');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    this.querySelector('.material-icons').innerText = isDark ? 'light_mode' : 'dark_mode';
                });
            }
            if (checkbox) {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        curtain.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    } else {
                        curtain.classList.remove('active');
                        document.body.style.overflow = 'auto';
                    }
                });
            }

            // --- SMART LOADER LOGIC ---
            const loaderLogo = document.getElementById('moving-logo');
            const navBrand = document.querySelector('.brand');
            const loaderWrapper = document.getElementById('loader-wrapper');
            const loaderLine = document.querySelector('.loader-line');

            if (loaderLogo && navBrand && loaderWrapper) {

                // 1. Check if user just Logged In (Look for ?welcome=1 in URL)
                const urlParams = new URLSearchParams(window.location.search);
                const isFreshLogin = urlParams.has('welcome');

                // 2. Check if it's the very first visit
                const hasSeenIntro = sessionStorage.getItem('campusGrid_intro_done');

                // PLAY ANIMATION IF: (First Visit) OR (Just Logged In)
                if (!hasSeenIntro || isFreshLogin) {

                    document.body.classList.add('loading');

                    // Mark as seen
                    sessionStorage.setItem('campusGrid_intro_done', 'true');

                    // Clean URL
                    if (isFreshLogin) {
                        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        window.history.replaceState({
                            path: newUrl
                        }, '', newUrl);
                    }

                    // Animation Timeline
                    const finalPos = navBrand.getBoundingClientRect();
                    const currentPos = loaderLogo.getBoundingClientRect();
                    const scaleFactor = (finalPos.height && currentPos.height) ? finalPos.height / currentPos.height : 0.4;
                    const deltaX = finalPos.left - currentPos.left;
                    const deltaY = finalPos.top - currentPos.top;

                    gsap.to(loaderLine, {
                        width: window.innerWidth < 768 ? "100px" : "150px",
                        duration: 0.8,
                        ease: "power2.out"
                    });

                    const tl = gsap.timeline({
                        delay: 0.8
                    });

                    tl.to(loaderLine, {
                            opacity: 0,
                            y: 10,
                            duration: 0.3
                        })
                        .to(loaderLogo, {
                            x: deltaX + (finalPos.width / 2) - (currentPos.width / 2),
                            y: deltaY + (finalPos.height / 2) - (currentPos.height / 2),
                            scale: scaleFactor,
                            duration: 1.2,
                            ease: "expo.inOut"
                        }, "-=0.1")
                        .to(loaderWrapper, {
                            opacity: 0,
                            pointerEvents: "none",
                            duration: 0.5
                        }, "-=0.4")
                        .set(navBrand, {
                            visibility: "visible"
                        })
                        .set(loaderWrapper, {
                            display: "none"
                        })
                        .call(() => {
                            document.body.classList.remove('loading');
                        })
                        .to(".hero-content", {
                            opacity: 1,
                            y: 0,
                            duration: 1,
                            stagger: 0.2,
                            ease: "power4.out"
                        }, "-=0.2");

                } else {
                    // SKIP ANIMATION
                    document.body.classList.remove('loading');
                    if (navBrand) navBrand.style.visibility = 'visible';
                    if (loaderWrapper) loaderWrapper.style.display = 'none';
                    gsap.to(".hero-content", {
                        opacity: 1,
                        y: 0,
                        duration: 0.5
                    });
                }
            } else {
                if (navBrand) navBrand.style.visibility = 'visible';
            }
        });
    </script>
    <main id="main-content">