<!DOCTYPE html>
<html>
<head>
    <script src="https://unpkg.com/@studio-freight/lenis@1.0.34/dist/lenis.min.js"></script>
    
    <style>
        html, body { margin: 0; padding: 0; background: #0b0b0b; font-family: 'Helvetica', sans-serif; color: white; }

        /* THE HERO SECTION */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .hero-img-container {
            position: absolute;
            width: 100%;
            height: 120%; /* Taller for parallax */
            top: -10%;
            z-index: -1;
        }

        #heroImage {
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1523050335102-c89b182ae4f4?q=80&w=2000');
            background-size: cover;
            background-position: center;
            transform: scale(1.1); /* Start slightly zoomed */
            filter: brightness(0.6);
        }

        .hero h1 { font-size: 8vw; letter-spacing: -2px; margin: 0; }

        /* THE SEAMLESS TRANSITION SECTION */
        .gallery-wrap {
            position: relative;
            background: #fff; /* White background like Snellenberg */
            color: #000;
            border-radius: 40px 40px 0 0; /* Agency-style rounded top */
            padding: 100px 5%;
            z-index: 10;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .card {
            background: #f4f4f4;
            height: 500px;
            border-radius: 20px;
            overflow: hidden;
            transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .card:hover { transform: scale(0.98); }

    </style>
</head>
<body>

    <section class="hero">
        <div class="hero-img-container">
            <div id="heroImage"></div>
        </div>
        <h1>CAMPUS GRID</h1>
    </section>

    <section class="gallery-wrap">
        <h2>Latest from Campus</h2>
        <div class="gallery-grid">
            <div class="card"></div>
            <div class="card" style="margin-top: 100px;"></div> <div class="card"></div>
            <div class="card" style="margin-top: 100px;"></div>
        </div>
    </section>

    <script>
        // Initialize Smooth Scroll
        const lenis = new Lenis();
        function raf(time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        }
        requestAnimationFrame(raf);

        // Transition Animation
        window.addEventListener('scroll', () => {
            const scroll = window.scrollY;
            
            // 1. Zoom the Hero Image OUT (towards 1.0) while scrolling down
            const heroImage = document.getElementById('heroImage');
            const scaleValue = 1.1 - (scroll / 5000);
            const translateY = scroll * 0.2; // Parallax effect
            
            heroImage.style.transform = `scale(${Math.max(scaleValue, 1)}) translateY(${translateY}px)`;
        });
    </script>

</body>
</html>