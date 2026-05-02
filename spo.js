document.addEventListener("DOMContentLoaded", () => {
    
    // 1. Listen for clicks on internal links
    document.body.addEventListener("click", e => {
        const link = e.target.closest("a");
        
        // Ignore if: no link, external link, or special links (download/target=_blank)
        if (!link || link.getAttribute("href").startsWith("#") || link.target === "_blank" || link.hostname !== window.location.hostname) {
            return;
        }

        // Prevent default browser reload
        e.preventDefault();
        const url = link.getAttribute("href");

        // Navigate
        loadPage(url);
    });

    // 2. Handle Browser "Back" Button
    window.addEventListener("popstate", () => {
        loadPage(window.location.href, false);
    });
});

async function loadPage(url, push = true) {
    const mainContent = document.getElementById("main-content");
    
    // A. Add a subtle fade-out effect
    mainContent.style.opacity = "0.5";
    mainContent.style.transition = "opacity 0.2s ease";

    try {
        // B. Fetch the new page HTML
        const response = await fetch(url);
        const text = await response.text();

        // C. Parse the HTML to find the #main-content div
        const parser = new DOMParser();
        const doc = parser.parseFromString(text, "text/html");
        const newContent = doc.getElementById("main-content").innerHTML;
        const newTitle = doc.title;

        // D. Swap the Content
        mainContent.innerHTML = newContent;
        document.title = newTitle;

        // E. Update URL History (so back button works)
        if (push) {
            history.pushState({}, "", url);
        }

        // F. Update Navbar "Active" Class
        updateActiveLink(url);

        // G. Re-Initialize Scripts (Scroll Reveal, GSAP, etc.)
        reInitScripts();

    } catch (error) {
        console.error("Error loading page:", error);
        window.location.href = url; // Fallback to normal reload if error
    } finally {
        // H. Fade back in
        mainContent.style.opacity = "1";
        // Scroll to top
        window.scrollTo(0, 0);
    }
}

function updateActiveLink(url) {
    const currentPath = new URL(url, window.location.origin).href;
    
    document.querySelectorAll(".main-nav .link").forEach(link => {
        // Check if this link matches the current URL
        if (link.href === currentPath) {
            link.classList.add("active");
        } else {
            link.classList.remove("active");
        }
    });
}

// THIS FUNCTION RE-RUNS YOUR ANIMATIONS AFTER PAGE SWAP
function reInitScripts() {
    // 1. Re-attach Scroll Observer
    const observerOptions = { root: null, rootMargin: '0px', threshold: 0.1 };
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal-on-scroll').forEach((element) => {
        observer.observe(element);
    });

    // 2. Re-attach Hero Mouse Effect (if on home)
    const hero = document.querySelector('.hero-section');
    if (hero) {
        // Remove old listener to prevent duplicates (optional, but good practice)
        // Add new listener
        document.addEventListener('mousemove', (e) => {
            const x = e.clientX;
            const y = e.clientY;
            hero.style.background = `radial-gradient(circle at ${x}px ${y}px, var(--primary-glow) 0%, transparent 40%), var(--bg-body)`;
        });
        
        // Re-run number counters if they exist
        if(document.getElementById("count-users")) {
           // You'd need to expose your animateValue function globally to call it here
           // Or re-define it here.
        }
    }
}