<?php
include 'db.php';
session_start();

$error = "";
$success = "";
$active_panel = ""; // Default is Login side

// 1. CHECK URL: Did they click "Sign Up" in the header?
if (isset($_GET['mode']) && $_GET['mode'] === 'signup') {
    $active_panel = "right-panel-active";
}

// 2. HANDLE REGISTER POST
if (isset($_POST['register'])) {
    $active_panel = "right-panel-active"; // Stay on signup side if error

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // 1. Check for BOTH email and username
        $check = $conn->prepare("SELECT email, username FROM users WHERE email = ? OR username = ?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        $result = $check->get_result();

        // 2. Identify exactly which one is a duplicate
        if ($result->num_rows > 0) {
            $existing_user = $result->fetch_assoc();
            if ($existing_user['email'] === $email) {
                $error = "An account with this email already exists!";
            } else if ($existing_user['username'] === $username) {
                $error = "That username is already taken! Please choose another.";
            }
        } else {
            // 3. No duplicates found! Safe to create the account.
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $email, $hashed);
            if ($stmt->execute()) {
                $success = "Account created! Please Sign In.";
                $active_panel = ""; // Switch back to Login side
            } else {
                $error = "Database error: Could not create account.";
            }
        }
    }
}

// 3. HANDLE LOGIN POST
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php?welcome=1");
            exit();
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Access The Grid | Campus Grid</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="auth-container">
        <canvas id="network-canvas"></canvas>

        <div class="glass-container <?php echo $active_panel; ?>" id="container">

            <div class="form-container sign-up-container">
                <form action="auth.php" method="POST" class="auth-form">
                    <h2 style="color:var(--text-main); margin-bottom:5px;">Create Account</h2>
                    <p style="color:var(--text-muted); font-size:0.9rem;">Join the community</p>

                    <?php if ($error && $active_panel): ?>
                        <div class="error-msg" style="width:100%; margin: 5px 0;"><?= $error ?></div>
                    <?php endif; ?>

                    <div class="inputForm">
                        <span class="material-icons">person</span>
                        <input type="text" name="username" class="input" placeholder="Username" required>
                    </div>

                    <div class="inputForm">
                        <span class="material-icons">email</span>
                        <input type="email" name="email" class="input" placeholder="Email" required>
                    </div>

                    <div class="inputForm">
                        <span class="material-icons">lock</span>
                        <input type="password" name="password" id="regPass" class="input" placeholder="Password" required>
                        <span class="material-icons toggle-icon" onclick="togglePass('regPass', this)">visibility_off</span>
                    </div>

                    <div class="inputForm">
                        <span class="material-icons">verified_user</span>
                        <input type="password" name="confirm_password" id="regPassConfirm" class="input" placeholder="Confirm Password" required>
                        <span class="material-icons toggle-icon" onclick="togglePass('regPassConfirm', this)">visibility_off</span>
                    </div>

                    <button type="submit" name="register" class="button-submit">Sign Up</button>

                    <p class="mobile-toggle" onclick="container.classList.remove('right-panel-active')">
                        Already have an account? Sign In
                    </p>
                </form>
            </div>

            <div class="form-container sign-in-container">
                <form action="auth.php" method="POST" class="auth-form">
                    <h2 style="color:var(--text-main); margin-bottom:5px;">Welcome Back</h2>
                    <p style="color:var(--text-muted); font-size:0.9rem;">Sign in to access the grid</p>

                    <?php if ($success): ?>
                        <div style="color:#4ade80; margin: 10px 0; font-weight:bold;"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if ($error && !$active_panel): ?>
                        <div class="error-msg" style="width:100%; margin: 5px 0;"><?= $error ?></div>
                    <?php endif; ?>

                    <div class="inputForm">
                        <span class="material-icons">email</span>
                        <input type="email" name="email" class="input" placeholder="Email" required>
                    </div>

                    <div class="inputForm">
                        <span class="material-icons">lock</span>
                        <input type="password" name="password" id="loginPass" class="input" placeholder="Password" required>
                        <span class="material-icons toggle-icon" onclick="togglePass('loginPass', this)">visibility_off</span>
                    </div>

                    <a href="forgot_password.php" style="color:var(--primary); font-size:0.9rem; font-weight:600;">Forgot password?</a>

                    <button type="submit" name="login" class="button-submit">Sign In</button>

                    <p class="mobile-toggle" onclick="container.classList.add('right-panel-active')">
                        Don't have an account? Sign Up
                    </p>
                </form>
            </div>

            <div class="overlay-container">
                <div class="overlay">
                    <div class="overlay-panel overlay-left">
                        <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 10px;">Welcome Back!</h2>
                        <p style="margin-bottom: 30px; font-size: 1.1rem; opacity: 0.9;">
                            To keep connected with us please login with your personal info
                        </p>
                        <button class="btn-ghost" id="signIn">Sign In</button>
                    </div>

                    <div class="overlay-panel overlay-right">
                        <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 10px;">Hello, Friend!</h2>
                        <p style="margin-bottom: 30px; font-size: 1.1rem; opacity: 0.9;">
                            Enter your personal details and start your journey with Campus Grid
                        </p>
                        <button class="btn-ghost" id="signUp">Sign Up</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });

        function togglePass(id, icon) {
            const field = document.getElementById(id);
            if (field.type === "password") {
                field.type = "text";
                icon.innerText = "visibility";
            } else {
                field.type = "password";
                icon.innerText = "visibility_off";
            }
        }
    </script>
    <script>
        const canvas = document.getElementById("network-canvas");
        const ctx = canvas.getContext("2d");

        let particles = [];
        const particleCount = 70; // Change this to add more/less dots
        const connectionDistance = 150; // How close dots must be to connect

        // Resize canvas to fill screen
        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener("resize", resize);
        resize();

        // Particle Class
        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.vx = (Math.random() - 0.5) * 1.0; // Speed X
                this.vy = (Math.random() - 0.5) * 1.0; // Speed Y
                this.size = Math.random() * 2 + 1;
                // The "Campus Grid" Blue Color
                this.color = "rgba(129, 140, 248, 0.8)";
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;

                // Bounce off edges
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

        // Initialize Particles
        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }

        // Animation Loop
        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // 1. Update and Draw Particles
            particles.forEach((p, index) => {
                p.update();
                p.draw();

                // 2. Draw Lines to Neighbors
                for (let j = index + 1; j < particles.length; j++) {
                    const p2 = particles[j];
                    const dx = p.x - p2.x;
                    const dy = p.y - p2.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < connectionDistance) {
                        ctx.beginPath();
                        // Fade line based on distance
                        const opacity = 1 - (distance / connectionDistance);
                        ctx.strokeStyle = `rgba(129, 140, 248, ${opacity * 0.5})`; // Blue lines
                        ctx.lineWidth = 1;
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.stroke();
                    }
                }
            });

            requestAnimationFrame(animate);
        }

        animate();
    </script>
</body>

</html>