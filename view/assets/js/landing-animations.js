/**
 * Aptus Landing Page Animations & Interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    
    /* ========================================================
       1. INTERSECTION OBSERVERS (SCROLL REVEAL)
       ======================================================== */
    const revealElements = document.querySelectorAll(
        '.reveal-left, .reveal-right, .reveal-up, .reveal-on-scroll'
    );
    
    const revealOptions = {
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px"
    };
    
    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            } else {
                entry.target.classList.remove('active');
            }
        });
    }, revealOptions);
    
    revealElements.forEach(el => {
        revealObserver.observe(el);
    });

    /* ========================================================
       2. NAVBAR SCROLL EFFECT
       ======================================================== */
    const nav = document.getElementById('landing-nav');
    
    const handleScroll = () => {
        if (window.scrollY > 50) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    };
    
    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Trigger once on load

    /* ========================================================
       3. SMOOTH SCROLL FOR ANCHORS & ACTIVE STATE
       ======================================================== */
    const anchors = document.querySelectorAll('.nav-anchor[href^="#"]');
    
    anchors.forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                
                // Adjust for fixed sticky nav offset
                const navHeight = nav.offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.scrollY - navHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                if (mobileMenu && mobileMenu.classList.contains('active')) {
                    mobileMenu.classList.remove('active');
                }
            }
        });
    });

    // Update active class based on scroll position
    const sections = document.querySelectorAll('section[id]');
    
    window.addEventListener('scroll', () => {
        let current = '';
        const navHeight = nav.offsetHeight;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            // Check if user has scrolled past the section (with offset)
            if (window.scrollY >= (sectionTop - navHeight - 150)) {
                current = section.getAttribute('id');
            }
        });
        
        anchors.forEach(anchor => {
            anchor.classList.remove('active');
            if (anchor.getAttribute('href') === `#${current}`) {
                anchor.classList.add('active');
            }
        });
    });

    /* ========================================================
       4. MOBILE MENU TOGGLE
       ======================================================== */
    const hamburger = document.getElementById('hamburger-landing');
    const mobileMenu = document.getElementById('mobile-menu-landing');
    
    if (hamburger && mobileMenu) {
        hamburger.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });
    }

    /* ========================================================
       5. CURSOR AURA EFFECT
       ======================================================== */
    const cursorAura = document.getElementById('cursor-aura');
    if (cursorAura) {
        document.addEventListener('mousemove', (e) => {
            // Use requestAnimationFrame for smoother performance
            requestAnimationFrame(() => {
                cursorAura.style.left = e.clientX + 'px';
                cursorAura.style.top = e.clientY + 'px';
            });
        });
        
        // Optional: reduce opacity if mouse leaves window
        document.addEventListener('mouseleave', () => {
            cursorAura.style.opacity = '0';
        });
        document.addEventListener('mouseenter', () => {
            cursorAura.style.opacity = '1';
        });
    }

    /* ========================================================
       6. CANVAS NEURONS/NODES ANIMATION
       ======================================================== */
    const canvas = document.getElementById('neurons-canvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let width = canvas.width = canvas.parentElement.offsetWidth;
        let height = canvas.height = canvas.parentElement.offsetHeight;
        let particles = [];
        
        window.addEventListener('resize', () => {
            width = canvas.width = canvas.parentElement.offsetWidth;
            height = canvas.height = canvas.parentElement.offsetHeight;
            initParticles();
        });

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 1.5;
                this.vy = (Math.random() - 0.5) * 1.5;
                this.radius = Math.random() * 2 + 1;
            }
            update() {
                this.x += this.vx;
                this.y += this.vy;
                if (this.x < 0 || this.x > width) this.vx *= -1;
                if (this.y < 0 || this.y > height) this.vy *= -1;
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(79, 70, 229, 0.6)';
                ctx.fill();
            }
        }

        function initParticles() {
            particles = [];
            let count = window.innerWidth < 768 ? 30 : 60;
            for (let i = 0; i < count; i++) {
                particles.push(new Particle());
            }
        }

        function animateCanvas() {
            ctx.clearRect(0, 0, width, height);
            
            // Central point coordinates
            let centerX = width / 2;
            let centerY = height / 2;

            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();
                
                // Draw lines between particles
                for (let j = i + 1; j < particles.length; j++) {
                    let dx = particles[i].x - particles[j].x;
                    let dy = particles[i].y - particles[j].y;
                    let dist = Math.sqrt(dx*dx + dy*dy);
                    if (dist < 100) {
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.strokeStyle = `rgba(79, 70, 229, ${1 - dist/100})`;
                        ctx.lineWidth = 1;
                        ctx.stroke();
                    }
                }

                // Draw lines from particle to center
                let cdx = particles[i].x - centerX;
                let cdy = particles[i].y - centerY;
                let cDist = Math.sqrt(cdx*cdx + cdy*cdy);
                if (cDist < 150) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(centerX, centerY);
                    ctx.strokeStyle = `rgba(124, 58, 237, ${1 - cDist/150})`;
                    ctx.lineWidth = 1.5;
                    ctx.stroke();
                }
            }
            requestAnimationFrame(animateCanvas);
        }

        initParticles();
        animateCanvas();
    }

});
