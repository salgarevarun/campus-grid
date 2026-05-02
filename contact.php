<?php
include 'db.php';
session_start();

$msg = "";
// Simulated Email Sending for Demo
if (isset($_POST['send_message'])) {
    $msg = "Thanks! We have received your message and will reply shortly.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Us | Campus Grid</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body>

    <?php include 'header.php'; ?>

    <div style="max-width: 1000px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; min-height: calc(100vh - 70px);">

        <div>
            <h1 style="font-size: 3rem; font-weight: 800; color: var(--text-main); margin-bottom: 20px;">
                Get in Touch.
            </h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; line-height: 1.6; margin-bottom: 40px;">
                Have a feature request? Found a bug? Or just want to say hi? We are always listening to the community.
            </p>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 40px; height: 40px; background: var(--bg-body); border-radius: 50%; display:flex; align-items:center; justify-content:center; border:1px solid var(--border-color);">
                        <span class="material-icons" style="color: var(--text-muted);">email</span>
                    </div>
                    <div>
                        <strong style="display:block; color:var(--text-main);">Email Us</strong>
                        <span style="color:var(--text-muted);">support@campusgrid.com</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 40px; height: 40px; background: var(--bg-body); border-radius: 50%; display:flex; align-items:center; justify-content:center; border:1px solid var(--border-color);">
                        <span class="material-icons" style="color: var(--text-muted);">location_on</span>
                    </div>
                    <div>
                        <strong style="display:block; color:var(--text-main);">Find Us</strong>
                        <span style="color:var(--text-muted);">Will Be available soon</span>
                    </div>
                </div>
            </div>
        </div>

        <div style="background: var(--card-bg); padding: 40px; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.05);">

            <?php if ($msg): ?>
                <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold;">
                    <?= $msg ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="input-group">
                    <label class="form-label">Your Name</label>
                    <input type="text" name="name" placeholder="Your Name" required>
                </div>

                <div class="input-group">
                    <label class="form-label">Your Email</label>
                    <input type="email" name="email" placeholder="email@domain.com" required>
                </div>

                <div class="input-group">
                    <label class="form-label">Message</label>
                    <textarea name="message" rows="5" placeholder="How can we help?" style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-main); outline: none;" required></textarea>
                </div>

                <button type="submit" name="send_message" class="btn-login">
                    Send Message
                </button>
            </form>
        </div>

    </div>

    <?php include 'footer.php'; ?>
</body>
</html>