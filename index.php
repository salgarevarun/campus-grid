<?php
include 'db.php';
session_start();

// 🛠️ TOOL: CARD RENDERER (Universal)

function renderCard($row, $logged_in_user)
{
    if (!$row) return;

    // 1. Color Logic
    $badgeColor = "var(--primary-glow)";
    $textColor = "var(--primary)";

    // Lost & Found (Orange)
    if (in_array($row['type'], ['lost', 'found'])) {
        $badgeColor = "#fff7ed";
        $textColor = "#c2410c";
    }
    // 🟢 Status Update (Green)
    elseif ($row['type'] == 'status') {
        $badgeColor = "#dcfce7";
        $textColor = "#166534";
    }
    // 🛒 Market (Emerald)
    elseif (in_array($row['type'], ['market', 'buy', 'sell', 'buying', 'selling'])) {
        $badgeColor = "#ecfdf5";
        $textColor = "#047857";
    }
    // Code (Gray)
    elseif ($row['type'] == 'code') {
        $badgeColor = "#f3f4f6";
        $textColor = "#1f2937";
    }

    $isCode = ($row['type'] == 'code');
    $isOwner = ($row['user_id'] == $logged_in_user);

    // Content Prep
    $imgUrl = !empty($row['image']) ? "uploads/" . htmlspecialchars($row['image']) : "";
    $rawCode = !empty($row['code_snippet']) ? htmlspecialchars($row['code_snippet']) : "";
    $hasMedia = ($isCode || !empty($row['image']));

    // HTML Output
    echo '
    <div class="card reveal-on-scroll"
        onclick="openModal(this)"
        data-title="' . htmlspecialchars($row['title']) . '"
        data-desc="' . htmlspecialchars($row['description']) . '"
        data-user="' . htmlspecialchars($row['username']) . '"
        data-email="' . htmlspecialchars($row['email']) . '"
        data-type="' . strtoupper($row['type']) . '"
        data-img="' . $imgUrl . '"
        data-is-code="' . ($isCode ? 'yes' : 'no') . '"
        data-badge-color="' . $badgeColor . '"
        data-text-color="' . $textColor . '">

        <div class="hidden-source" style="display:none;">' . $rawCode . '</div>';

    // Media Section
    if ($hasMedia) {
        echo '<div class="card-image" style="width: 100%; overflow: hidden; background: var(--bg-body); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; padding: 0;">';
        if ($isCode) {
            echo '
                <div class="code-window" style="width:100%; border:none;">
                    <div class="code-header"><span class="code-lang">SNIPPET</span></div>
                    <div class="code-body" style="font-size: 0.7rem; max-height: 250px; overflow:hidden; position:relative;">
                        ' . (!empty($rawCode) ? $rawCode : "// No code") . '
                        <div style="position:absolute; bottom:0; left:0; width:100%; height:40px; background:linear-gradient(to top, #1e1e1e, transparent);"></div>
                    </div>
                </div>';
        } elseif (!empty($row['image'])) {
            echo '<img src="uploads/' . htmlspecialchars($row['image']) . '" style="width: 100%; height: auto; display:block;">';
        }
        echo '</div>';
    }

    // Text Section
    echo '
        <div style="padding: 15px; display: flex; flex-direction: column;">
            <div style="margin-bottom: 8px; display:flex; justify-content:space-between;">
                <span class="badge" style="background: ' . $badgeColor . '; color: ' . $textColor . '; font-size:0.65rem;">
                    ' . strtoupper($row['type']) . '
                </span>
                ' . ($isOwner ? '<span style="font-size:0.65rem; color:var(--text-muted); font-weight:bold;">YOU</span>' : '') . '
            </div>
            
            <h3 style="margin: 0 0 6px 0; font-size: 1rem; color: var(--text-main);">' . htmlspecialchars($row['title']) . '</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin: 0;">
                ' . htmlspecialchars($row['description']) . '
            </p>

            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width:24px; height:24px; background: var(--bg-body); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:bold; color:var(--text-muted); border:1px solid var(--border-color);">
                        ' . substr(strtoupper($row['username']), 0, 1) . '
                    </div>
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">' . htmlspecialchars($row['username']) . '</span>
                </div>
            </div>';

    // Owner Buttons
    if ($isOwner) {
        echo '<div class="owner-controls" onclick="event.stopPropagation();" style="margin-top:10px; display:flex; gap:10px;">';
        echo '<a href="delete.php?id=' . $row['id'] . '" onclick="return confirm(\'Delete this post?\')" 
      style="flex:1; text-align:center; border: 1px solid var(--border-color); color:var(--text-muted); padding:6px; border-radius:6px; font-size:0.75rem; font-weight:600; transition:all 0.2s;"
      onmouseover="this.style.background=\'#fee2e2\'; this.style.color=\'#ef4444\'; this.style.borderColor=\'#ef4444\';"
      onmouseout="this.style.background=\'transparent\'; this.style.color=\'var(--text-muted)\'; this.style.borderColor=\'var(--border-color)\';">Delete</a>';

        if ($row['type'] == 'lost') {
            echo '<a href="update_status.php?id=' . $row['id'] . '&action=mark_found" 
                            style="flex:1; text-align:center; background:#dcfce7; color:#166534; padding:6px; border-radius:6px; font-size:0.75rem; font-weight:bold;">Mark Found</a>';
        }
        if ($row['type'] == 'selling') {
            echo '<a href="update_status.php?id=' . $row['id'] . '&action=mark_sold" onclick="return confirm(\'Mark as Sold?\')"
                            style="flex:1; text-align:center; background:#dcfce7; color:#166534; padding:6px; border-radius:6px; font-size:0.75rem; font-weight:bold;">Mark Sold</a>';
        }
        echo '</div>';
    }

    echo '</div></div>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Campus Grid | Home</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <?php include 'header.php'; ?>

    <?php
    $view_all = isset($_GET['view']) && $_GET['view'] === 'all';
    $is_landing = empty($_GET['type']) && empty($_GET['search']) && !$view_all;
    ?>

    <?php if ($is_landing): ?>

        <header class="hero-section">
            <canvas id="hero-grid-canvas"></canvas>

            <div class="hero-content">
                <span class="hero-badge">🚀 v2.0 Is Live</span>
                <h1 class="hero-title">The Central Nervous System<br>of Student Life.</h1>
                <p class="hero-subtitle">Connect, share, and solve problems together. Campus Grid is more than a platform—it’s the ecosystem where students help students thrive.</p>
                <div class="hero-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="upload.php" class="btn-hero-primary">Create a Post</a>
                    <?php else: ?>
                        <a href="auth.php" class="btn-hero-primary">Join the Grid</a>
                    <?php endif; ?>
                    <a href="index.php?view=all" class="btn-hero-secondary">Explore Feed</a>
                </div>
            </div>
            <div class="scroll-indicator">
                <span class="material-icons">keyboard_arrow_down</span>
            </div>
        </header>

        <div class="features-grid">
            <?php
            // 1. Define ALL your available features here
            $all_features = [
                ['icon' => 'campaign', 'title' => 'Campus News', 'desc' => 'Stay updated with the latest events, notices, and club announcements.', 'link' => 'index.php?type=notice'],
                ['icon' => 'code', 'title' => 'Code Snippets', 'desc' => 'Stuck on a bug? Share and discover code solutions with your peers.', 'link' => 'index.php?type=code'],
                ['icon' => 'storefront', 'title' => 'Marketplace', 'desc' => 'Buy textbooks, sell old gear, or find that hoodie you lost.', 'link' => 'index.php?type=market'],
                ['icon' => 'timer', 'title' => 'Status Updates', 'desc' => 'Share what you are doing right now. Studying? Gaming? Chilling?', 'link' => 'index.php?type=status'],
                ['icon' => 'location_searching', 'title' => 'Lost & Found', 'desc' => 'Did you lose your ID card? or found one? Post it here to help others.', 'link' => 'index.php?type=lostfound']
            ];
            shuffle($all_features);
            $random_features = array_slice($all_features, 0, 3);
            foreach ($random_features as $index => $feature):
                $stagger_num = $index + 1;
            ?>
                <a href="<?= $feature['link'] ?>" class="feature-card reveal-on-scroll delay-<?= $stagger_num ?>" style="text-decoration: none;">
                    <div class="feature-icon"><span class="material-icons"><?= $feature['icon'] ?></span></div>
                    <div class="feature-title"><?= $feature['title'] ?></div>
                    <p class="feature-desc"><?= $feature['desc'] ?></p>
                </a>
            <?php endforeach; ?>
        </div>

        <section style="padding: 80px 20px; background: var(--bg-body); border-bottom: 1px solid var(--border-color);">
            <div style="max-width: 1200px; margin: 0 auto;">
                <div style="text-align:center; margin-bottom:40px;">
                    <h2 style="font-size: 2rem; font-weight: 800; color: var(--text-main);">Fresh on the Grid ⚡</h2>
                    <p style="color:var(--text-muted);">The very latest updates from around campus.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <?php
                    $showcase_sql = "SELECT posts.*, users.username, users.email FROM posts JOIN users ON posts.user_id = users.id ORDER BY created_at DESC LIMIT 3";
                    $showcase_res = mysqli_query($conn, $showcase_sql);
                    $my_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

                    if (mysqli_num_rows($showcase_res) > 0) {
                        while ($row = mysqli_fetch_assoc($showcase_res)) {
                            renderCard($row, $my_id);
                        }
                    } else {
                        echo '<p style="text-align:center; color:var(--text-muted); width:100%;">No posts yet. Be the first!</p>';
                    }
                    ?>
                </div>

                <div style="text-align: center; margin-top: 40px;">
                    <a href="index.php?view=all" class="btn-hero-secondary" style="padding: 12px 40px;">
                        View All Posts <span class="material-icons" style="vertical-align: middle; font-size: 18px; margin-left: 5px;">arrow_forward</span>
                    </a>
                </div>
            </div>
        </section>

    <?php else: ?>

        <?php
        $filter = isset($_GET['type']) ? $_GET['type'] : '';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $logged_in_user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

        $pageTitle = "All Posts";
        if ($filter == 'notice') $pageTitle = "📢 Notices";
        elseif ($filter == 'status') $pageTitle = "⏱️ Status Updates";
        elseif ($filter == 'code') $pageTitle = "💻 Code Snippets";
        elseif ($filter == 'market') $pageTitle = "🛒 Marketplace";
        elseif ($filter == 'lostfound') $pageTitle = "🔍 Lost & Found";
        elseif ($search) $pageTitle = "🔍 Search Results: " . htmlspecialchars($search);

        echo '<div style="max-width:1200px; margin: 120px auto 20px; padding:0 20px;">
                <h2 style="font-size:1.8rem; color:var(--text-main);">' . $pageTitle . '</h2>
                <a href="index.php" style="color:var(--primary); font-size:0.9rem;">&larr; Back to Home</a>
              </div>';

        // 1. Start the base query and use WHERE 1=1 so we can easily append AND clauses
        $sql = "SELECT posts.*, users.username, users.email 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE 1=1";

        // 2. Append the filters
        if ($filter) {
            if ($filter == 'lostfound') {
                $sql .= " AND type IN ('lost', 'found')";
            } elseif ($filter == 'market') {
                $sql .= " AND type IN ('market', 'buy', 'sell', 'buying', 'selling')";
            } else {
                $sql .= " AND type = '" . mysqli_real_escape_string($conn, $filter) . "'";
            }
        } else {
            $sql .= " AND type != 'sold'";
        }

        // 3. Append the search
        if ($search) {
            $sql .= " AND title LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
        }

        // 4. Finally, append the ORDER BY and LIMIT at the very end using .=
        $sql .= " ORDER BY posts.created_at DESC LIMIT 24";
        
        $res = mysqli_query($conn, $sql);

        ?>

        <div class="masonry-wrapper" style="margin-top: 20px;">
            <?php
            while ($row = mysqli_fetch_assoc($res)) {
                renderCard($row, $logged_in_user);
            }
            ?>
        </div>

    <?php endif; ?>

    <?php include 'footer.php'; ?>

    <div id="postModal" class="modal-overlay" onclick="closeModal(event)">
        <div class="modal-content">
            <button class="modal-close" onclick="forceClose()" ontouchstart="forceClose()">✕</button>
            <div class="modal-left" id="modalLeftContainer"></div>
            <div class="modal-right">
                <span id="modalBadge" class="badge">TYPE</span>
                <h2 id="modalTitle" style="color: var(--text-main); margin-bottom: 15px;">Title</h2>
                <div style="display: flex; align-items: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                    <strong id="modalUser" style="color: var(--text-main);">@username</strong>
                </div>
                <p id="modalDesc" style="color: var(--text-muted); line-height: 1.6;"></p>
                <a id="modalContactBtn" href="#" class="btn-contact">Contact Author</a>
            </div>
        </div>
    </div>

    <script>
        /* --- ANIMATED MODAL LOGIC --- */
        function openModal(card) {
            // 1. Populate Data (Keep your existing logic here)
            document.getElementById('modalTitle').innerText = card.getAttribute('data-title');
            document.getElementById('modalDesc').innerText = card.getAttribute('data-desc');
            document.getElementById('modalUser').innerText = "@" + card.getAttribute('data-user');

            const container = document.getElementById('modalLeftContainer');
            const img = card.getAttribute('data-img');
            const isCode = card.getAttribute('data-is-code') === 'yes';

            if (isCode) {
                const code = card.querySelector('.hidden-source').innerHTML;
                container.innerHTML = `<div class="code-window"><div class="code-body" style="height:100%;">${code}</div></div>`;
            } else if (img) {
                container.innerHTML = `<img src="${img}" style="width: 100%; height: 100%; object-fit: contain;">`;
            } else {
                container.innerHTML = `<div style="color:white; opacity:0.5; text-align:center;"><p>Text Only</p></div>`;
            }

            // 2. Show & Animate
            const modal = document.getElementById('postModal');
            modal.style.display = 'flex';

            // Small delay to allow 'display: flex' to apply before adding class (triggers transition)
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }

        function forceClose() {
            const modal = document.getElementById('postModal');
            modal.classList.remove('active');

            // Wait for animation to finish before hiding
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300); // Matches the CSS transition time (0.3s)
        }

        /* --- MAGNETIC NEURAL GRID (Chaotic Version) --- */
        const canvas = document.getElementById('hero-grid-canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');

            // CONFIGURATION (Chaos Mode)
            const spacing = 45;
            const mouseRadius = 100; // ⚡ SMALLER (Was 200)
            const pushForce = 30; // ⚡ STRONGER (Was 5) - Explosive repulsion!
            const returnSpeed = 0.08; // Faster snap-back for high energy

            // Colors
            const particleColor = "rgba(129, 140, 248, 0.8)";
            const lineColor = "rgba(14, 165, 164, 0.15)";

            let particles = [];
            let width, height;
            let mouse = {
                x: -1000,
                y: -1000
            };

            function resize() {
                const parent = canvas.parentElement;
                width = parent.offsetWidth;
                height = parent.offsetHeight;
                canvas.width = width;
                canvas.height = height;
                initGrid();
            }
            window.addEventListener('resize', resize);

            window.addEventListener('mousemove', (e) => {
                const rect = canvas.getBoundingClientRect();
                mouse.x = e.clientX - rect.left;
                mouse.y = e.clientY - rect.top;
            });

            window.addEventListener('mouseout', () => {
                mouse.x = -1000;
                mouse.y = -1000;
            });

            class Particle {
                constructor(x, y) {
                    this.x = x;
                    this.y = y;
                    this.baseX = x;
                    this.baseY = y;
                    // Increased density range for more random/chaotic movement
                    this.density = (Math.random() * 20) + 1;
                }

                update() {
                    let dx = mouse.x - this.x;
                    let dy = mouse.y - this.y;
                    let distance = Math.sqrt(dx * dx + dy * dy);
                    let forceDirectionX = dx / distance;
                    let forceDirectionY = dy / distance;

                    if (distance < mouseRadius) {
                        // CHAOS LOGIC: Explosive push
                        const force = (mouseRadius - distance) / mouseRadius;
                        // We use Math.sign to ensure they blast away from center
                        const directionX = forceDirectionX * force * this.density * pushForce;
                        const directionY = forceDirectionY * force * this.density * pushForce;

                        this.x -= directionX;
                        this.y -= directionY;
                    } else {
                        if (this.x !== this.baseX) {
                            let dx = this.x - this.baseX;
                            this.x -= dx * returnSpeed;
                        }
                        if (this.y !== this.baseY) {
                            let dy = this.y - this.baseY;
                            this.y -= dy * returnSpeed;
                        }
                    }
                }

                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, 1.5, 0, Math.PI * 2);
                    ctx.fillStyle = particleColor;
                    ctx.fill();
                }
            }

            function initGrid() {
                particles = [];
                for (let y = 0; y < height; y += spacing) {
                    for (let x = 0; x < width; x += spacing) {
                        particles.push(new Particle(x, y));
                    }
                }
            }

            function connect() {
                for (let a = 0; a < particles.length; a++) {
                    for (let b = a; b < particles.length; b++) {
                        // Optimization: Only check X distance first to save CPU
                        if (Math.abs(particles[a].x - particles[b].x) > spacing * 1.5) continue;

                        let dx = particles[a].x - particles[b].x;
                        let dy = particles[a].y - particles[b].y;
                        let distance = dx * dx + dy * dy;

                        if (distance < (spacing * spacing * 1.8)) {
                            ctx.strokeStyle = lineColor;
                            ctx.lineWidth = 0.5;
                            ctx.beginPath();
                            ctx.moveTo(particles[a].x, particles[a].y);
                            ctx.lineTo(particles[b].x, particles[b].y);
                            ctx.stroke();
                        }
                    }
                }
            }

            function animate() {
                ctx.clearRect(0, 0, width, height);

                for (let i = 0; i < particles.length; i++) {
                    particles[i].update();
                    particles[i].draw();
                }
                connect();
                requestAnimationFrame(animate);
            }

            resize();
            animate();
        }

        // --- SCROLL REVEAL LOGIC ---
        document.addEventListener("DOMContentLoaded", function() {
            const observerOptions = {
                root: null,
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            setTimeout(() => {
                document.querySelectorAll('.reveal-on-scroll').forEach((element) => {
                    observer.observe(element);
                });
            }, 500);
        });
    </script>

    <script>
        /* --- 3D TILT EFFECT FOR CARDS --- */
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                // Calculate center
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                // Calculate tilt (Max 15 degrees)
                const rotateX = ((y - centerY) / centerY) * -10; // Negative to tilt away
                const rotateY = ((x - centerX) / centerX) * 10;

                // Apply Transform
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.05, 1.05, 1.05)`;
            });

            // Reset when mouse leaves
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
                card.style.transition = 'transform 0.5s ease'; // Smooth return
            });

            // Remove transition on enter so it follows mouse instantly
            card.addEventListener('mouseenter', () => {
                card.style.transition = 'none';
            });
        });
    </script>
</body>

</html>