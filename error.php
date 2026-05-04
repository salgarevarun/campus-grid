<?php
session_start();

// Get the error code from the URL, default to 404 if missing
$error_code = isset($_GET['code']) ? $_GET['code'] : '404';

// Define the custom messages for different errors
$errors = [
    '400' => ['title' => 'Bad Request', 'icon' => 'warning', 'desc' => 'The server could not understand your request.'],
    '401' => ['title' => 'Unauthorized', 'icon' => 'lock', 'desc' => 'You must be logged in to access this sector of the grid.'],
    '403' => ['title' => 'Forbidden Access', 'icon' => 'gavel', 'desc' => 'You do not have the required clearance to view this directory.'],
    '404' => ['title' => 'Node Not Found', 'icon' => 'explore_off', 'desc' => 'The page you are looking for has been moved, deleted, or never existed.'],
    '500' => ['title' => 'System Glitch', 'icon' => 'dns', 'desc' => 'The central server encountered an unexpected error. Try again later.']
];

// Fallback to 404 if somehow an unknown code is passed
if (!array_key_exists($error_code, $errors)) {
    $error_code = '404';
}

$error_data = $errors[$error_code];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error <?= $error_code ?> | Campus Grid</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="auth-container" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding-top: 60px;">
        <!-- Reusing your background animation -->
        <canvas id="network-canvas" style="position: absolute; top:0; left:0; width:100%; height:100%; z-index:0;"></canvas>

        <div class="glass-form-container" style="max-width: 500px; text-align: center; z-index: 1; padding: 50px 30px;">
            
            <span class="material-icons" style="font-size: 4rem; color: var(--primary); margin-bottom: 10px; opacity: 0.8;">
                <?= $error_data['icon'] ?>
            </span>

            <h1 style="font-size: 6rem; font-weight: 900; margin: 0; line-height: 1; 
                       background: linear-gradient(135deg, var(--text-main) 0%, var(--primary) 100%);
                       -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                <?= $error_code ?>
            </h1>

            <h2 style="color: var(--text-main); font-size: 1.8rem; margin: 15px 0 10px;">
                <?= $error_data['title'] ?>
            </h2>

            <p style="color: var(--text-muted); font-size: 1.1rem; line-height: 1.6; margin-bottom: 35px;">
                <?= $error_data['desc'] ?>
            </p>

            <a href="index.php" class="btn-login" style="text-decoration: none; display: inline-flex; width: auto; padding: 12px 30px; border-radius: 50px;">
                <span class="material-icons" style="font-size: 20px;">home</span> 
                Return to Base
            </a>

        </div>
    </div>

    <script>
        // Background Animation (Same as upload.php)
        document.addEventListener("DOMContentLoaded", function() {
            const canvas = document.getElementById("network-canvas");
            if(!canvas) return;
            const ctx = canvas.getContext("2d");
            let particles = [];
            const particleCount = 40;

            function resize() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            }
            window.addEventListener("resize", resize);
            resize();

            class Particle {
                constructor() {
                    this.x = Math.random() * canvas.width;
                    this.y = Math.random() * canvas.height;
                    this.vx = (Math.random() - 0.5) * 0.2;
                    this.vy = (Math.random() - 0.5) * 0.2;
                    this.size = Math.random() * 2 + 1;
                    this.color = "rgba(129, 140, 248, 0.2)";
                }
                update() {
                    this.x += this.vx;
                    this.y += this.vy;
                    if (this.x < 0 || this.x > canvas.width) this.vx *= -1;
                    if (this.y < 0 || this.y > canvas.height) this.vy *= -1;
                }
                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                    ctx.fillStyle = this.color;
                    ctx.fill();
                }
            }
            for (let i = 0; i < particleCount; i++) particles.push(new Particle());
            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                particles.forEach(p => { p.update(); p.draw(); });
                requestAnimationFrame(animate);
            }
            animate();
        });
    </script>
</body>
</html>