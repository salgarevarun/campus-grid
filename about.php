<?php
include 'db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>About Us | Campus Grid</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="display: flex; flex-direction: column; min-height: 100vh;">

    <?php include 'header.php'; ?>

    <div class="reveal-on-scroll active" style="padding: 120px 20px 60px; text-align: center; background: var(--bg-body);">
        <span class="badge" style="background: var(--primary-glow); color: var(--primary); font-size: 0.8rem; font-weight: 800; letter-spacing: 1px; padding: 6px 15px; border-radius: 20px; margin-bottom: 20px; display: inline-block; text-transform: uppercase;">
            Our Mission
        </span>
        <h1 style="font-size: clamp(2.5rem, 5vw, 3.5rem); font-weight: 800; color: var(--text-main); margin-bottom: 20px; line-height: 1.1; letter-spacing: -1px;">
            Connecting the Campus,<br><span style="color: var(--primary);">One Pixel at a Time.</span>
        </h1>
        <p style="color: var(--text-muted); max-width: 700px; margin: 0 auto; font-size: 1.15rem; line-height: 1.6; font-weight: 500;">
            Campus Grid was built to solve the fragmentation of student life. No more checking five different apps to find your lost keys or sell your old textbooks.
        </p>
    </div>

    <div class="reveal-on-scroll" style="max-width: 1000px; margin: 0 auto; padding: 40px 20px;">
        <div style="background: var(--card-bg); padding: 50px; border-radius: 24px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.05); display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">

            <div>
                <h2 style="color: var(--text-main); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <span class="material-icons" style="color: #ef4444;">error_outline</span> The Problem
                </h2>
                <p style="color: var(--text-muted); line-height: 1.7; font-size: 1rem;">
                    Critical updates are stuck on physical boards, while resources like lost items are siloed in different departments. It is fragmented chaos—a disconnected experience that slows everyone down.
                </p>
            </div>

            <div>
                <h2 style="color: var(--text-main); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <span class="material-icons" style="color: var(--primary);">check_circle_outline</span> The Solution
                </h2>
                <p style="color: var(--text-muted); line-height: 1.7; font-size: 1rem;">
                    <strong>Campus Grid</strong> is the Central Nervous System. We unify all information into one clean, searchable feed. Beyond just a site, we bridge the gap between different student platforms to create a better, more connected campus experience
                </p>
            </div>

        </div>
    </div>

    <div style="padding: 80px 20px; text-align: center; flex: 1;">
        <h2 class="reveal-on-scroll" style="color: var(--text-main); margin-bottom: 10px; font-size: 2.2rem; font-weight: 800;">Built By Students, For Students</h2>
        <p class="reveal-on-scroll" style="color: var(--text-muted); margin-bottom: 50px; font-weight: 500;">The engineering team behind the grid.</p>

        <div style="display: flex; justify-content: center; gap: 40px; flex-wrap: wrap;">

            <div class="dev-card reveal-on-scroll delay-1">
                <div class="dev-card-inner">
                    <div class="dev-front">
                        <div class="dev-img-container">
                            <img src="Avtar/vs.png" alt="Lead Developer">
                        </div>
                        <div class="dev-info-basic">
                            <h3 style="margin: 0; font-size: 1.4rem;">Varun Salgare</h3>
                            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">Namespace</p>
                        </div>
                    </div>
                    <div class="dev-back">
                        <p style="font-size: 0.9rem; margin-bottom: 15px;">Full Stack Engineering & System Architecture</p>
                        <div class="dev-socials">
                            <a href="https://github.com/yourusername" target="_blank">
                                <i class="fa-brands fa-github"></i> GitHub</a>
                            <a href="https://linkedin.com/in/yourusername" target="_blank">
                                <i class="fa-brands fa-linkedin"></i> LinkedIn</a>
                        </div>
                    </div>
                </div>
            </div>

            

        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // B. SCROLL REVEAL OBSERVER
        const observerOptions = {
            threshold: 0.15
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal-on-scroll').forEach((el) => {
            observer.observe(el);
        });
    </script>

</body>

</html>