<?php
include 'db.php';
session_start();

// Check if user already has a saved phone number
$u_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT phone_number FROM users WHERE id = '$u_id'");
$user_data = mysqli_fetch_assoc($user_query);
$needs_phone = empty($user_data['phone_number']);

include 'auth_check.php';

$error = "";

if (isset($_POST['submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    // Sub-Option Logic
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $final_type = $category;

    if ($category == 'lostfound' && isset($_POST['status'])) {
        // This captures 'lost' or 'found' from the radio buttons
        $final_type = mysqli_real_escape_string($conn, $_POST['status']);
    }

    if ($category == 'market' && isset($_POST['intent'])) {
        $final_type = mysqli_real_escape_string($conn, $_POST['intent']);
    }

    $uid = $_SESSION['user_id'];
    $code_snippet = "";
    $image = "";

    // Handle Code vs Image
    if ($category == 'code') {
        $code_snippet = mysqli_real_escape_string($conn, $_POST['code_snippet']);
    } else {
        // Inside upload.php, replace your file upload logic with this:
        if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // SECURITY FIX: Get file extension and validate
            $allowed_extensions = array("jpg", "jpeg", "png", "gif", "webp");
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

            // SECURITY FIX: Verify MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES["image"]["tmp_name"]);
            $allowed_mimes = array("image/jpeg", "image/png", "image/gif", "image/webp");
            finfo_close($finfo);

            if (in_array($file_extension, $allowed_extensions) && in_array($mime_type, $allowed_mimes)) {
                $image = time() . "_" . basename($_FILES["image"]["name"]);
                move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);
            } else {
                die("Security Error: Only image files are allowed.");
            }
        }
    }

    // If they provided a phone number, save it to their profile permanently
    if (isset($_POST['phone_number']) && !empty($_POST['phone_number'])) {
        $new_phone = mysqli_real_escape_string($conn, $_POST['phone_number']);
        mysqli_query($conn, "UPDATE users SET phone_number = '$new_phone' WHERE id = '$u_id'");
    }

    $sql = "INSERT INTO posts (user_id, type, title, description, image, code_snippet) 
            VALUES ('$uid', '$final_type', '$title', '$desc', '$image', '$code_snippet')";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?msg=success");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Upload | College Grid</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>

<body>

    <?php include 'header.php'; ?>

    <div class="auth-container" style="position: relative; overflow: hidden;">
        <canvas id="network-canvas" style="position: absolute; top:0; left:0; width:100%; height:100%; z-index:0;"></canvas>

        <div class="glass-form-container">
            <h2 style="margin-bottom: 10px; color: white; text-align: center; font-size: 2rem;">Create Post</h2>
            <p style="text-align: center; color: #94a3b8; margin-bottom: 30px;">Share something with the grid</p>

            <?php if ($error): ?>
                <div class="error-msg" style="color: #ef4444; margin-bottom: 20px; text-align: center; background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 8px;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="upload.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">

                <div class="input-group">
                    <label class="form-label" style="color: #cbd5e1;">Category</label>
                    <select name="category" class="glass-input" id="categorySelect" style="width: 100%; padding: 12px; border-radius: 12px;">
                        <option value="notice">📢 General Notice</option>
                        <option value="status">🕒 Status Update</option>
                        <option value="code">💻 Code Snippet</option>
                        <option value="market">🛒 Market</option>
                        <option value="lostfound">🔍 Lost & Found</option>
                    </select>

                    <div id="status-wrap" style="display: none; margin-top: 15px;">
                        <p class="form-label" style="color: #cbd5e1;">Status:</p>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="status" value="lost" checked>
                                <div class="radio-card"><span class="material-icons">search_off</span> I Lost It</div>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="status" value="found">
                                <div class="radio-card"><span class="material-icons">check_circle</span> I Found It</div>
                            </label>
                        </div>
                    </div>

                    <div id="intent-wrap" style="display: none; margin-top: 15px;">
                        <p class="form-label" style="color: #cbd5e1;">Intent:</p>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="intent" value="selling" checked>
                                <div class="radio-card"><span class="material-icons">storefront</span> Selling</div>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="intent" value="buying">
                                <div class="radio-card"><span class="material-icons">shopping_cart</span> Buying</div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <label class="form-label" style="color: #cbd5e1;">Title</label>
                    <input type="text" name="title" class="glass-input" placeholder="What's on your mind?" required style="width: 100%;">
                </div>

                <div class="input-group">
                    <label class="form-label" style="color: #cbd5e1;">Description</label>
                    <textarea name="description" class="glass-input" placeholder="Provide more details..." rows="4" required style="width: 100%;"></textarea>
                </div>

                <div id="code-input-section" class="input-group" style="display:none;">
                    <label class="form-label" style="color: #cbd5e1;">Paste Your Code:</label>
                    <textarea name="code_snippet" class="glass-input" placeholder="// paste code here..." rows="10" style="font-family: monospace; color: #a5b4fc;"></textarea>
                </div>

                <div id="image-input-section" class="input-group">
                    <label class="form-label" style="color: #cbd5e1;">Attach Image (Optional)</label>
                    <input type="file" name="image" accept="image/*" class="glass-input file-input">
                </div>

                <?php if ($needs_phone): ?>
                    <div class="input-group" style="background: rgba(129, 140, 248, 0.05); border: 1px dashed #818cf8; padding: 20px; border-radius: 12px; margin-top: 10px;">
                        <label class="form-label" style="color: #818cf8; display: flex; align-items: center; gap: 8px; margin-bottom: 12px; font-weight: bold;">
                            <span class="material-icons" style="font-size: 1.2rem;">whatsapp</span> Contact Info Needed
                        </label>

                        <input type="text" name="phone_number" class="glass-input" placeholder="WhatsApp No. (e.g., 9876543210)" required style="width: 100%;">

                        <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 10px; margin-bottom: 0;">
                            We'll save this securely so buyers can contact you on future posts.
                        </p>
                    </div>
                <?php endif; ?>

                <button type="submit" name="submit" class="btn-login" style="margin-top: 10px;">Post to Grid</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Form Logic
            const catSelect = document.getElementById('categorySelect');
            const statusWrap = document.getElementById('status-wrap');
            const intentWrap = document.getElementById('intent-wrap');
            const codeSection = document.getElementById('code-input-section');
            const imageSection = document.getElementById('image-input-section');

            function updateForm() {
                const val = catSelect.value;
                statusWrap.style.display = 'none';
                intentWrap.style.display = 'none';
                codeSection.style.display = 'none';
                imageSection.style.display = 'block';

                if (val === 'lostfound') statusWrap.style.display = 'block';
                else if (val === 'market') intentWrap.style.display = 'block';
                else if (val === 'code') {
                    codeSection.style.display = 'block';
                    imageSection.style.display = 'none';
                }
            }
            catSelect.addEventListener('change', updateForm);
            updateForm();

            // 2. Background Animation (Slow & Calm)
            const canvas = document.getElementById("network-canvas");
            const ctx = canvas.getContext("2d");
            let particles = [];
            const particleCount = 50;

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
                    this.vx = (Math.random() - 0.5) * 0.2; // Very slow speed
                    this.vy = (Math.random() - 0.5) * 0.2;
                    this.size = Math.random() * 2 + 1;
                    this.color = "rgba(129, 140, 248, 0.4)";
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
                particles.forEach((p, index) => {
                    p.update();
                    p.draw();
                    for (let j = index + 1; j < particles.length; j++) {
                        const p2 = particles[j];
                        const dx = p.x - p2.x;
                        const dy = p.y - p2.y;
                        const distance = Math.sqrt(dx * dx + dy * dy);
                        if (distance < 150) {
                            ctx.beginPath();
                            ctx.strokeStyle = `rgba(129, 140, 248, ${0.15 * (1 - distance/150)})`;
                            ctx.lineWidth = 0.5;
                            ctx.moveTo(p.x, p.y);
                            ctx.lineTo(p2.x, p2.y);
                            ctx.stroke();
                        }
                    }
                });
                requestAnimationFrame(animate);
            }
            animate();
        });
    </script>
</body>

</html>