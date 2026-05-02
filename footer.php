<?php
// Check if we are on the landing page (Home with no filters)
$is_home = (basename($_SERVER['PHP_SELF']) == 'index.php' && empty($_GET['type']) && empty($_GET['search']) && !isset($_GET['view']));
?>

</main>
<footer class="site-footer-modern <?= !$is_home ? 'footer-mini' : '' ?>">
    
    <?php if ($is_home): ?>
        <div class="footer-cta-section">
            <div class="cta-content">
                <h2>Ready to join the conversation?</h2>
                <p>Don't just lurk. Share your code, find your gear, and connect with the campus.</p>
            </div>
            <div class="cta-actions">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="signup.php" class="btn-footer-primary">Get Started Now</a>
                <?php else: ?>
                    <a href="upload.php" class="btn-footer-primary">Create a Post</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="footer-divider"></div>
        
        <div class="footer-grid-container">
            <div class="footer-col brand-col">
                <div class="footer-logo">
                    <span class="material-icons">school</span> Campus<span>Grid</span>
                </div>
                <p class="footer-bio">The central nervous system of student life. Built by students, for students.</p>
                <div class="footer-socials">
                    <a href="https://x.com/salgare_varun" target="_blank" class="social-link">Twitter/X</a>
                    <a href="https://github.com/salgarevarun" target="_blank" class="social-link"">GitHub</a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Discover</h4>
                <ul class="footer-nav">
                    <li><a href="index.php?type=notice">Notices</a></li>
                    <li><a href="index.php?type=market">Market</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <ul class="footer-nav">
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col newsletter-col">
                <h4>Stay in loop</h4>
                <form class="footer-form"><input type="email" placeholder="Email"><button type="submit">➔</button></form>
            </div>
        </div>
    <?php endif; ?>

    <div class="footer-copyright">
        <?php if (!$is_home): ?>
            <div class="footer-logo mini-logo" style="margin-bottom: 0;">
                <span class="material-icons" style="font-size: 1.2rem;">school</span> Campus<span>Grid</span>
            </div>
        <?php endif; ?>
        
        <p>&copy; 2026 Campus Grid. All rights reserved.</p>
        <p class="made-with">Made with <span class="material-icons" style="font-size:14px; color:#ef4444;">favorite</span></p>
    </div>

</footer>

<script src="https://unpkg.com/lenis@1.1.13/dist/lenis.min.js"></script>
<script>
    const lenis = new Lenis({ duration: 1.2, smoothWheel: true });
    function raf(time) { lenis.raf(time); requestAnimationFrame(raf); }
    requestAnimationFrame(raf);

    const observerOptions = {
    threshold: 0.1 // Triggers when 10% of the element is visible
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('active');
        }
    });
}, observerOptions);

// Target all elements you want to animate
document.querySelectorAll('.reveal-on-scroll').forEach((el) => {
    observer.observe(el);
});

</script>
<script>
    window.addEventListener("load", function() {
        const loader = document.querySelector(".loader-wrapper");
        // Add a slight delay (500ms) for a smoother transition
        setTimeout(() => {
            loader.classList.add("loader-hidden");
        }, 500);
    });
</script>
