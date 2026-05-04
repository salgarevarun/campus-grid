<?php
include 'db.php';
session_start();

// 1. SECURITY: Admin Access Only
include 'auth_check.php';

$uid = $_SESSION['user_id'];
$check_admin = mysqli_query($conn, "SELECT role FROM users WHERE id='$uid'");
$user_role = mysqli_fetch_assoc($check_admin)['role'];

if ($user_role !== 'admin') {
    header("Location: index.php"); // Kick out non-admins
    exit();
}


// ⚙️ ACTION HANDLERS


// A. DELETE POST
if (isset($_GET['delete_post'])) {
    $pid = mysqli_real_escape_string($conn, $_GET['delete_post']);
    mysqli_query($conn, "DELETE FROM posts WHERE id='$pid'");
    header("Location: admin_dashboard.php");
    exit();
}

// B. DELETE USER (And their posts)
if (isset($_GET['delete_user'])) {
    $target_id = mysqli_real_escape_string($conn, $_GET['delete_user']);

    // Prevent suicide (Admin deleting themselves)
    if ($target_id != $uid) {
        mysqli_query($conn, "DELETE FROM users WHERE id='$target_id'");
        mysqli_query($conn, "DELETE FROM posts WHERE user_id='$target_id'");
    }
    header("Location: admin_dashboard.php?view=users");
    exit();
}

// C. TOGGLE ROLE (Promote/Demote)
if (isset($_GET['toggle_role'])) {
    $target_id = mysqli_real_escape_string($conn, $_GET['toggle_role']);

    // Prevent demoting yourself
    if ($target_id != $uid) {
        $current = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='$target_id'"));
        $new_role = ($current['role'] == 'admin') ? 'student' : 'admin';
        mysqli_query($conn, "UPDATE users SET role='$new_role' WHERE id='$target_id'");
    }
    header("Location: admin_dashboard.php?view=users");
    exit();
}

// D. BAN CONTROLS
if (isset($_GET['action']) && isset($_GET['uid'])) {
    $target_id = (int)mysqli_real_escape_string($conn, $_GET['uid']);
    $action = $_GET['action'];
    $msg = "";

    // 1. Fetch the username BEFORE we ban them
    $user_query = mysqli_query($conn, "SELECT username, role FROM users WHERE id='$target_id'");
    $target_user = mysqli_fetch_assoc($user_query);
    $target_username = $target_user['username'];
    $target_role = $target_user['role'];

    // 2. Security: Prevent banning yourself or other admins
    if ($target_id != $uid && $target_role != 'admin') {
        if ($action == 'ban_24h') {
            $ban_time = date('Y-m-d H:i:s', strtotime('+24 hours'));
            mysqli_query($conn, "UPDATE users SET banned_until = '$ban_time' WHERE id = '$target_id'");
            $msg = "@" . $target_username . " has been suspended for 24 hours.";
        } elseif ($action == 'ban_week') {
            $ban_time = date('Y-m-d H:i:s', strtotime('+7 days'));
            mysqli_query($conn, "UPDATE users SET banned_until = '$ban_time' WHERE id = '$target_id'");
            $msg = "@" . $target_username . " has been suspended for 7 days.";
        } elseif ($action == 'unban') {
            mysqli_query($conn, "UPDATE users SET banned_until = NULL WHERE id = '$target_id'");
            $msg = "Access restored for @" . $target_username . ".";
        }
    }

    // Redirect back to the users tab and attach the success message to the URL!
    header("Location: admin_dashboard.php?view=users&msg=" . urlencode($msg));
    exit();
}


// 📊 DATA FETCHING

$view = isset($_GET['view']) ? $_GET['view'] : 'overview';

$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$total_posts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM posts"))['c'];
$todays_posts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM posts WHERE DATE(created_at) = CURDATE()"))['c'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Command Node | Campus Grid</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Additional Admin Styles for User Table */
        .role-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .role-admin {
            background: rgba(129, 140, 248, 0.2);
            color: #818cf8;
            border: 1px solid rgba(129, 140, 248, 0.3);
        }

        .role-student {
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar-sm {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
            color: white;
            background: var(--primary);
        }
    </style>
</head>

<body style="background: #0f172a;">

    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;">
        <canvas id="network-canvas"></canvas>
    </div>

    <div class="admin-layout">

        <aside class="admin-sidebar glass-panel">
            <div class="sidebar-header">
                <span class="material-icons" style="color: var(--primary);">hub</span>
                <h3>CORE</h3>
            </div>

            <nav class="sidebar-menu">
                <a href="admin_dashboard.php?view=overview" class="menu-item <?= $view == 'overview' ? 'active' : '' ?>">
                    <span class="material-icons">dashboard</span> Overview
                </a>
                <a href="admin_dashboard.php?view=users" class="menu-item <?= $view == 'users' ? 'active' : '' ?>">
                    <span class="material-icons">people</span> User Nodes
                </a>
                <a href="index.php" class="menu-item">
                    <span class="material-icons">dns</span> Live Feed
                </a>
                <a href="logout.php" class="menu-item logout">
                    <span class="material-icons">power_settings_new</span> Disconnect
                </a>
            </nav>

            <div class="server-status">
                <div class="status-indicator"></div>
                <span>SYSTEM ONLINE</span>
                <small>Latency: 24ms</small>
            </div>
        </aside>

        <main class="admin-content">

            <header class="admin-header">
                <div>
                    <h1 style="font-size: 1.8rem; color: white;">
                        <?= $view == 'users' ? 'User Protocol' : 'Network Overview' ?>
                    </h1>
                    <p style="color: #94a3b8;">
                        <?= $view == 'users' ? 'Manage access levels and permissions' : 'Real-time data stream' ?>
                    </p>
                </div>
                <div class="admin-user">
                    <span style="color:white; font-weight:bold;">Admin Mode</span>
                    <div class="profile-trigger" style="background: var(--primary); color: white; width: 35px; height: 35px; border-radius: 50%; display:flex; align-items:center; justify-content:center;">A</div>
                </div>
            </header>

            <?php if ($view == 'overview'): ?>
                <div class="stats-grid">
                    <div class="stat-card glass-panel">
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.2); color: #34d399;">
                            <span class="material-icons">sensors</span>
                        </div>
                        <div>
                            <h3><?= $total_users ?></h3>
                            <p>Active Nodes</p>
                        </div>
                    </div>
                    <div class="stat-card glass-panel">
                        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.2); color: #818cf8;">
                            <span class="material-icons">article</span>
                        </div>
                        <div>
                            <h3><?= $total_posts ?></h3>
                            <p>Total Transmissions</p>
                        </div>
                    </div>
                    <div class="stat-card glass-panel">
                        <div class="stat-icon" style="background: rgba(244, 63, 94, 0.2); color: #fb7185;">
                            <span class="material-icons">schedule</span>
                        </div>
                        <div>
                            <h3><?= $todays_posts ?></h3>
                            <p>New Today</p>
                        </div>
                    </div>
                </div>

                <div class="data-section glass-panel">
                    <div class="section-header">
                        <h2>Recent Transmissions</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="glass-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $logs = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC LIMIT 8");
                                while ($row = mysqli_fetch_assoc($logs)):
                                    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE id=" . $row['user_id']));
                                ?>
                                    <tr>
                                        <td style="color: #64748b;">#<?= $row['id'] ?></td>
                                        <td style="font-weight: bold; color: white;">@<?= htmlspecialchars($u['username']) ?></td>
                                        <td><span class="badge"><?= strtoupper($row['type']) ?></span></td>
                                        <td style="color: #cbd5e1;"><?= substr(htmlspecialchars($row['title']), 0, 30) ?>...</td>
                                        <td style="color: #64748b; font-size: 0.8rem;"><?= date("M d, H:i", strtotime($row['created_at'])) ?></td>
                                        <td>
                                            <a href="admin_dashboard.php?delete_post=<?= $row['id'] ?>" class="action-btn delete" onclick="return confirm('Purge this data?')">
                                                <span class="material-icons" style="font-size:16px;">delete</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($view == 'users'): ?>

                <div class="section-header">
                    <h2>Registered Nodes</h2>
                </div>

                <!-- NEW: Dynamic Notification Banner -->
                <?php if (isset($_GET['msg']) && !empty($_GET['msg'])): ?>
                    <div style="background: rgba(16, 185, 129, 0.15); color: #34d399; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; border: 1px solid rgba(16, 185, 129, 0.3); display: flex; align-items: center; gap: 10px;">
                        <span class="material-icons" style="font-size: 20px;">check_circle</span>
                        <?= htmlspecialchars($_GET['msg']) ?>
                    </div>
                <?php endif; ?>

                <div class="data-section glass-panel">
                    <div class="section-header">
                        <h2>Registered Nodes</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="glass-table">
                            <thead>
                                <tr>
                                    <th>Identity</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
                                while ($row = mysqli_fetch_assoc($users)):
                                    $is_me = ($row['id'] == $uid);
                                    $is_admin_user = ($row['role'] == 'admin');
                                    $is_banned = ($row['banned_until'] && strtotime($row['banned_until']) > time());
                                ?>
                                    <tr>
                                        <td>
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                <div class="user-avatar-sm">
                                                    <?= substr(strtoupper($row['username']), 0, 1) ?>
                                                </div>
                                                <span style="color:white; font-weight:600;">@<?= htmlspecialchars($row['username']) ?></span>
                                            </div>
                                        </td>
                                        <td style="color: #cbd5e1;"><?= htmlspecialchars($row['email']) ?></td>
                                        <td>
                                            <span class="role-badge <?= $is_admin_user ? 'role-admin' : 'role-student' ?>">
                                                <?= strtoupper($row['role']) ?>
                                            </span>
                                        </td>

                                        <!-- NEW: IP ADDRESS -->
                                        <td style="font-family: monospace; color: #94a3b8; font-size: 0.9rem;">
                                            <?= $row['last_ip'] ? htmlspecialchars($row['last_ip']) : 'Unknown' ?>
                                        </td>

                                        <!-- NEW: STATUS -->
                                        <td>
                                            <?php if ($is_banned): ?>
                                                <span style="color: #ef4444; font-weight: bold; font-size: 0.75rem;">
                                                    BANNED UNTIL<br><?= date("M d, H:i", strtotime($row['banned_until'])) ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: #10b981; font-weight: bold; font-size: 0.8rem;">ACTIVE</span>
                                            <?php endif; ?>
                                        </td>

                                        <td style="color: #64748b; font-size: 0.8rem;"><?= date("M d, Y", strtotime($row['created_at'])) ?></td>

                                        <!-- UPDATED ACTIONS -->
                                        <td>
                                            <div style="display:flex; gap:8px;">
                                                <?php if (!$is_me): ?>

                                                    <!-- Demote/Promote -->
                                                    <a href="admin_dashboard.php?toggle_role=<?= $row['id'] ?>&view=users"
                                                        class="action-btn" title="<?= $is_admin_user ? 'Demote' : 'Promote' ?>"
                                                        style="background: rgba(99, 102, 241, 0.2); color: #818cf8;">
                                                        <span class="material-icons" style="font-size:16px;"><?= $is_admin_user ? 'remove_moderator' : 'add_moderator' ?></span>
                                                    </a>

                                                    <!-- Ban Controls -->
                                                    <?php if (!$is_admin_user): ?>
                                                        <?php if ($is_banned): ?>
                                                            <a href="admin_dashboard.php?action=unban&uid=<?= $row['id'] ?>" class="action-btn" title="Unban User" style="background: rgba(16, 185, 129, 0.2); color: #10b981;">
                                                                <span class="material-icons" style="font-size:16px;">lock_open</span>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="admin_dashboard.php?action=ban_24h&uid=<?= $row['id'] ?>" class="action-btn" title="Ban 24 Hours" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b;" onclick="return confirm('Suspend user for 24 hours?');">
                                                                <span class="material-icons" style="font-size:16px;">timer</span>
                                                            </a>
                                                            <a href="admin_dashboard.php?action=ban_week&uid=<?= $row['id'] ?>" class="action-btn" title="Ban 7 Days" style="background: rgba(239, 68, 68, 0.2); color: #ef4444;" onclick="return confirm('Suspend user for 7 days?');">
                                                                <span class="material-icons" style="font-size:16px;">gavel</span>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>

                                                    <!-- Delete User -->
                                                    <a href="admin_dashboard.php?delete_user=<?= $row['id'] ?>&view=users"
                                                        class="action-btn delete" title="Delete User"
                                                        onclick="return confirm('WARNING: Delete user AND all posts?')">
                                                        <span class="material-icons" style="font-size:16px;">delete</span>
                                                    </a>

                                                <?php else: ?>
                                                    <span style="font-size:0.7rem; color:#64748b;">(You)</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>

        </main>
    </div>

    <script>
        const canvas = document.getElementById("network-canvas");
        const ctx = canvas.getContext("2d");
        let particles = [];
        const particleCount = 60;

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
            particles.forEach((p, i) => {
                p.update();
                p.draw();
                for (let j = i + 1; j < particles.length; j++) {
                    const p2 = particles[j];
                    const d = Math.sqrt((p.x - p2.x) ** 2 + (p.y - p2.y) ** 2);
                    if (d < 150) {
                        ctx.beginPath();
                        ctx.strokeStyle = `rgba(129,140,248,${0.15*(1-d/150)})`;
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
    </script>
</body>

</html>