<?php
include 'db.php';
session_start();

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Require the files we just downloaded
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$error = "";
$success = "";
date_default_timezone_set('Asia/Kolkata'); // Set to your local timezone

// --- STEP 1: HANDLE EMAIL SUBMISSION ---
if (isset($_POST['send_otp'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");

    if (mysqli_num_rows($check) > 0) {
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        
        // Save OTP to database with 10-minute expiry
        mysqli_query($conn, "UPDATE users SET reset_otp = '$otp', otp_expiry = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE email = '$email'");

        // Send Email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // YOUR GMAIL CREDENTIALS HERE
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'varunsalgare09@gmail.com'; 
            $mail->Password   = 'muon avjr ntnu jcny'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('varunsalgare09@gmail.com', 'Campus Grid Security'); // <--- Change This
            $mail->addAddress($email);
            
            $mail->isHTML(true);
            $mail->Subject = 'Your Campus Grid Password Reset OTP';
            $mail->Body    = "<div style='font-family: Arial, sans-serif; padding: 20px; text-align: center; background: #0f172a; color: white;'>
                                <h2>Campus Grid Password Reset</h2>
                                <p>Your One-Time Password (OTP) is:</p>
                                <h1 style='color: #818cf8; letter-spacing: 5px; font-size: 36px;'>$otp</h1>
                                <p>This code will expire in 10 minutes.</p>
                              </div>";

            $mail->send();
            
            $_SESSION['reset_email'] = $email;
            $success = "OTP sent! Check your email inbox (and spam folder).";
        } catch (Exception $e) {
            $error = "Email failed to send. Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "No account found with that email.";
    }
}

// --- STEP 2: HANDLE OTP VERIFICATION ---
if (isset($_POST['verify_otp'])) {
    $entered_otp = mysqli_real_escape_string($conn, $_POST['otp']);
    $email = $_SESSION['reset_email'];

    // Check if OTP matches and is not expired
    $query = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND reset_otp = '$entered_otp' AND otp_expiry >= NOW()");

    if (mysqli_num_rows($query) > 0) {
        $_SESSION['otp_verified'] = true;
        $success = "OTP Verified! Please enter your new password.";
    } else {
        $error = "Invalid or expired OTP. Please try again.";
    }
}

// --- STEP 3: HANDLE NEW PASSWORD ---
if (isset($_POST['reset_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];

    if ($new_pass === $confirm_pass) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        
        // Update password and clear OTP data so it can't be reused
        mysqli_query($conn, "UPDATE users SET password = '$hashed', reset_otp = NULL, otp_expiry = NULL WHERE email = '$email'");
        
        // Clear the session variables
        unset($_SESSION['reset_email']);
        unset($_SESSION['otp_verified']);
        
        echo "<script>alert('Password reset successful! Please login.'); window.location='auth.php';</script>";
        exit();
    } else {
        $error = "Passwords do not match!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | Campus Grid</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

    <!-- Include header so it matches the rest of the site -->
    <?php include 'header.php'; ?>

    <div class="auth-container" style="position: relative; overflow: hidden; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding-top: 60px;">
        <canvas id="network-canvas" style="position: absolute; top:0; left:0; width:100%; height:100%; z-index:0;"></canvas>

        <div class="glass-form-container" style="max-width: 400px; width: 100%; position: relative; z-index: 1;">
            <h2 style="margin-bottom: 10px; color: var(--text-main, white); text-align: center; font-size: 2rem;">Account Recovery</h2>
            
            <?php if ($error): ?>
                <div class="error-msg" style="color: #ef4444; margin-bottom: 20px; text-align: center; background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 8px;">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div style="color: #10b981; margin-bottom: 20px; text-align: center; background: rgba(16, 185, 129, 0.1); padding: 10px; border-radius: 8px; font-weight: bold;">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <!-- STEP 3 FORM: NEW PASSWORD -->
            <?php if (isset($_SESSION['otp_verified'])): ?>
                <p style="text-align: center; color: var(--text-muted); margin-bottom: 30px;">Create a new secure password.</p>
                <form action="" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="input-group">
                        <label class="form-label" style="color: var(--text-main);">New Password</label>
                        <input type="password" name="new_password" class="glass-input" placeholder="Enter new password" required style="width: 100%;">
                    </div>
                    <div class="input-group">
                        <label class="form-label" style="color: var(--text-main);">Confirm Password</label>
                        <input type="password" name="confirm_password" class="glass-input" placeholder="Confirm new password" required style="width: 100%;">
                    </div>
                    <button type="submit" name="reset_password" class="btn-login" style="margin-top: 10px;">Update Password</button>
                </form>

            <!-- STEP 2 FORM: VERIFY OTP -->
            <?php elseif (isset($_SESSION['reset_email'])): ?>
                <p style="text-align: center; color: var(--text-muted); margin-bottom: 30px;">We sent a 6-digit code to <br><b style="color: var(--primary);"><?= htmlspecialchars($_SESSION['reset_email']) ?></b></p>
                <form action="" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="input-group">
                        <label class="form-label" style="color: var(--text-main);" text-align: center;">Enter OTP</label>
                        <input type="text" name="otp" class="glass-input" placeholder="######" required maxlength="6" style="text-align: center; font-size: 1.5rem; letter-spacing: 10px; width: 100%;">
                    </div>
                    <button type="submit" name="verify_otp" class="btn-login" style="margin-top: 10px;">Verify Code</button>
                </form>

            <!-- STEP 1 FORM: ENTER EMAIL -->
            <?php else: ?>
                <p style="text-align: center; color: var(--text-muted); margin-bottom: 30px;">Enter your email to receive an OTP.</p>
                <form action="" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="input-group">
                        <label class="form-label" style="color: var(--text-main);">Registered Email</label>
                        <input type="email" name="email" class="glass-input" placeholder="example@college.edu" required style="width: 100%;">
                    </div>
                    <button type="submit" name="send_otp" class="btn-login" style="margin-top: 10px;">Send OTP</button>
                </form>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="auth.php" style="color: var(--primary); font-size: 0.9rem; text-decoration: none; font-weight: bold;">&larr; Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Background Animation matching upload.php
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
                particles.forEach(p => {
                    p.update();
                    p.draw();
                });
                requestAnimationFrame(animate);
            }
            animate();
        });
    </script>
</body>
</html>