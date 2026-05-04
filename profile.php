<?php
// Force Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
session_start();

// Kick out unauthenticated users
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// ==========================================
// 1. HANDLE IDENTITY & SECURITY UPDATES
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // A. Update General Profile (Bio, Username, Avatar)
    if (isset($_POST['update_profile'])) {
        $new_username = mysqli_real_escape_string($conn, $_POST['username']);
        $new_bio = mysqli_real_escape_string($conn, $_POST['bio']);
        
        // Avatar Upload Logic
        $avatar_sql_part = "";
        if (isset($_FILES['avatar']['name']) && $_FILES['avatar']['name'] != "") {
            $target_dir = "uploads/avatars/";
            if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
            
            $file_ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
            $new_filename = "avatar_" . $user_id . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                $avatar_sql_part = ", avatar = '$new_filename'";
            } else {
                $error = "Failed to upload image.";
            }
        }

        if (!$error) {
            $sql = "UPDATE users SET username = '$new_username', bio = '$new_bio' $avatar_sql_part WHERE id = '$user_id'";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['username'] = $new_username;
                $msg = "Identity Protocol Updated.";
            } else {
                $error = "Database Error: " . mysqli_error($conn);
            }
        }
    }

    // B. Update Security (Email & Password)
    if (isset($_POST['update_security'])) {
        $new_email = mysqli_real_escape_string($conn, $_POST['email']);
        $new_pass = $_POST['new_pass'];
        $confirm_pass = $_POST['confirm_pass'];

        // Update Email
        if (!empty($new_email)) {
            $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$new_email' AND id != '$user_id'");
            if (mysqli_num_rows($check) > 0) {
                $error = "That email is already registered to another node!";
            } else {
                mysqli_query($conn, "UPDATE users SET email='$new_email' WHERE id='$user_id'");
                $msg = "Security Settings Saved!";
            }
        }

        // Update Password
        if (!empty($new_pass) && !$error) {
            if ($new_pass === $confirm_pass) {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id='$user_id'");
                $msg = "Encryption Key (Password) Updated!";
            } else {
                $error = "Passwords do not match!";
            }
        }
    }
}               

// ==========================================
// 2. FETCH CURRENT USER DATA
// ==========================================
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$profile_data = mysqli_fetch_assoc(mysqli_query($conn, $sql));
$post_count_sql = "SELECT COUNT(*) as total FROM posts WHERE user_id = '$user_id'";
$post_count = mysqli_fetch_assoc(mysqli_query($conn, $post_count_sql))['total'];
$avatar_url = $profile_data['avatar'] ? "uploads/avatars/" . $profile_data['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($profile_data['username']) . "&background=random&color=fff";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Node | Campus Grid</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

    <?php include 'header.php'; ?>

    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; background: var(--bg-body);">
        <canvas id="network-canvas"></canvas>
    </div>

    <div class="profile-container">
        
        <!-- LEFT COLUMN: Settings -->
        <div class="profile-sidebar">
            
            <!-- Global Messages -->
            <?php if($msg): ?>
                <div class="success-badge" style="margin-bottom: 15px;">✨ <?= $msg ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="error-msg" style="color: #ef4444; background: rgba(239,68,68,0.1); padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center;">
                    ⚠️ <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- CARD 1: Public Identity -->
            <div class="glass-card identity-card" style="margin-bottom: 25px;">
                <div class="avatar-wrapper">
                    <img src="<?= $avatar_url ?>" alt="Profile" class="profile-avatar">
                    <div class="online-indicator"></div>
                </div>
                
                <h2 class="profile-name">@<?= htmlspecialchars($profile_data['username']) ?></h2>
                <p class="profile-role">Student Node</p>
                
                <div class="profile-stats">
                    <div class="stat-box">
                        <span class="stat-num"><?= $post_count ?></span>
                        <span class="stat-label">Posts</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-num"><?= date("M 'y", strtotime($profile_data['created_at'] ?? 'now')) ?></span>
                        <span class="stat-label">Joined</span>
                    </div>
                </div>

                <hr class="glass-divider">

                <form action="profile.php" method="POST" enctype="multipart/form-data" class="profile-form">
                    <label class="form-label">Avatar</label>
                    <label for="avatar-upload" class="custom-file-upload">
                        <span class="material-icons">cloud_upload</span>
                        <span id="file-name">Change Profile Photo</span>
                    </label>
                    <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display:none;" onchange="document.getElementById('file-name').innerText = this.files[0].name">
                    
                    <label class="form-label">Nickname</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($profile_data['username']) ?>" class="glass-input" required style="margin-bottom: 15px; width: 100%;">
                    
                    <label class="form-label">Bio / Status</label>
                    <textarea name="bio" rows="2" class="glass-input" placeholder="System status..." style="margin-bottom: 15px; width: 100%;"><?= htmlspecialchars($profile_data['bio'] ?? '') ?></textarea>
                    
                    <button type="submit" name="update_profile" class="btn-posh" style="width:100%; justify-content:center;">
                        Update Identity
                    </button>
                </form>
            </div>

            <!-- CARD 2: Private Security (Merged from user_settings) -->
            <div class="glass-card" style="padding: 25px;">
                <h3 style="color: var(--text-main); margin-bottom: 20px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons" style="color: var(--primary);">lock</span> Security
                </h3>
                
                <form action="profile.php" method="POST" class="profile-form">
                    <label class="form-label">Registered Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($profile_data['email']) ?>" class="glass-input" required style="margin-bottom: 20px; width: 100%;">
                    
                    <label class="form-label">Change Password <span style="font-size: 0.75rem; color: var(--text-muted);">(Optional)</span></label>
                    <input type="password" name="new_pass" class="glass-input" placeholder="New Password" style="margin-bottom: 10px; width: 100%;">
                    <input type="password" name="confirm_pass" class="glass-input" placeholder="Confirm Password" style="margin-bottom: 20px; width: 100%;">
                    
                    <button type="submit" name="update_security" class="btn-login-ghost" style="width:100%; justify-content:center; padding: 12px;">
                        Save Security Settings
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT COLUMN: The Feed -->
        <div class="profile-content">
            <h3 class="section-title">Transmission Log</h3>
            
            <div style="width: 100%;">
                <?php
                $my_posts = mysqli_query($conn, "SELECT * FROM posts WHERE user_id = '$user_id' ORDER BY created_at DESC");
                
                if (mysqli_num_rows($my_posts) > 0) {
                    while ($row = mysqli_fetch_assoc($my_posts)) {
                        echo '
                        <div class="profile-feed-card">
                            <span class="feed-date">' . date("M d", strtotime($row['created_at'])) . '</span>
                            
                            <div style="margin-bottom:10px;">
                                <span class="badge">' . strtoupper($row['type']) . '</span>
                            </div>
                            
                            <h3 style="color: var(--text-main); margin-bottom: 8px; font-size: 1.1rem;">' . htmlspecialchars($row['title']) . '</h3>
                            <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5;">' . htmlspecialchars($row['description']) . '</p>
                            
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color); display:flex; gap:10px;">
                                <a href="delete.php?id=' . $row['id'] . '" onclick="return confirm(\'Delete transmission?\')" 
                                   style="font-size: 0.8rem; color: #ef4444; text-decoration: none; display:flex; align-items:center; gap:5px; transition:opacity 0.2s;">
                                   <span class="material-icons" style="font-size:16px;">delete</span> Erase Data
                                </a>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<div class="glass-card" style="text-align:center; padding:50px; color:var(--text-muted);">
                            <span class="material-icons" style="font-size:40px; margin-bottom:10px; opacity:0.5;">signal_wifi_off</span><br>
                            No transmissions detected.
                          </div>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Background Animation Script -->
    <script>
        const canvas = document.getElementById("network-canvas");
        const ctx = canvas.getContext("2d");
        let particles = [];
        const particleCount = 40; 
        function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
        window.addEventListener("resize", resize); resize();
        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width; this.y = Math.random() * canvas.height;
                this.vx = (Math.random() - 0.5) * 0.2; this.vy = (Math.random() - 0.5) * 0.2; 
                this.size = Math.random() * 2 + 1; this.color = "rgba(129, 140, 248, 0.4)"; 
            }
            update() { this.x += this.vx; this.y += this.vy; if(this.x<0||this.x>canvas.width)this.vx*=-1; if(this.y<0||this.y>canvas.height)this.vy*=-1; }
            draw() { ctx.beginPath(); ctx.arc(this.x, this.y, this.size, 0, Math.PI*2); ctx.fillStyle=this.color; ctx.fill(); }
        }
        for(let i=0; i<particleCount; i++) particles.push(new Particle());
        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach((p, i) => { p.update(); p.draw();
                for(let j=i+1; j<particles.length; j++) {
                    const p2 = particles[j]; const d = Math.sqrt((p.x-p2.x)**2 + (p.y-p2.y)**2);
                    if(d<150) { ctx.beginPath(); ctx.strokeStyle=`rgba(129,140,248,${0.15*(1-d/150)})`; ctx.lineWidth=0.5; ctx.moveTo(p.x,p.y); ctx.lineTo(p2.x,p2.y); ctx.stroke(); }
                }
            }); requestAnimationFrame(animate);
        }
        animate();
    </script>
</body>
</html>