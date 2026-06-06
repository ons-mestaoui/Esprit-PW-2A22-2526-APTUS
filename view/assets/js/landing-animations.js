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
                this.radius = Math.random() * 3 + 2;
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
                    if (dist < 120) {
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.strokeStyle = `rgba(79, 70, 229, ${1.2 * (1 - dist/120)})`;
                        ctx.lineWidth = 1.8;
                        ctx.stroke();
                    }
                }

                // Draw lines from particle to center
                let cdx = particles[i].x - centerX;
                let cdy = particles[i].y - centerY;
                let cDist = Math.sqrt(cdx*cdx + cdy*cdy);
                if (cDist < 170) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(centerX, centerY);
                    ctx.strokeStyle = `rgba(124, 58, 237, ${1.2 * (1 - cDist/170)})`;
                    ctx.lineWidth = 2.2;
                    ctx.stroke();
                }
            }
            requestAnimationFrame(animateCanvas);
        }

        initParticles();
        animateCanvas();
    }

    /* ========================================================
       7. GLOBAL BACKGROUND 3D CAREER DATA HIGHWAY (PURE GEOMETRY)
       ======================================================== */
    const globalCanvas = document.getElementById('global-plexus-canvas');
    if (globalCanvas) {
        const ctx = globalCanvas.getContext('2d');
        let globalWidth = globalCanvas.width = window.innerWidth;
        let globalHeight = globalCanvas.height = window.innerHeight;
        
        let mouse = { x: null, y: null, radius: 240 };
        let vanishingPoint = { x: globalWidth * 0.5, y: globalHeight * 0.5 };
        let targetVanishingPoint = { x: globalWidth * 0.5, y: globalHeight * 0.5 };
        
        const fov = 350;
        const maxZ = 1200;
        const minZ = 10;
        let speed = 3.5;
        let zOffset = 0;
        
        // Expanded Grid longitudinal rails (3D layout: x, y relative to center)
        const rails = [
            { x: -750, y: -420 }, // Top Left
            { x: 0, y: -420 },    // Top Center
            { x: 750, y: -420 },  // Top Right
            { x: 800, y: 0 },     // Mid Right
            { x: 750, y: 420 },   // Bottom Right
            { x: 0, y: 420 },     // Bottom Center
            { x: -750, y: 420 },  // Bottom Left
            { x: -800, y: 0 }     // Mid Left
        ];
        
        let comets = [];
        let panels = [];
        let clickParticles = [];
        
        // Define theme colors
        const getThemeColors = () => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            return {
                gridColor: isDark ? 'rgba(20, 184, 166, 0.12)' : 'rgba(79, 70, 229, 0.08)',
                railColor: isDark ? 'rgba(99, 102, 241, 0.08)' : 'rgba(79, 70, 229, 0.04)',
                hudBorder: isDark ? 'rgba(20, 184, 166, 0.25)' : 'rgba(79, 70, 229, 0.16)',
                hudBg: isDark ? 'rgba(15, 23, 42, 0.45)' : 'rgba(255, 255, 255, 0.45)',
                hudText: isDark ? 'rgba(20, 184, 166, 0.85)' : 'rgba(79, 70, 229, 0.8)',
                cyanComet: isDark ? 'rgba(34, 211, 238, 0.85)' : 'rgba(6, 182, 212, 0.75)',
                orangeComet: isDark ? 'rgba(249, 115, 22, 0.85)' : 'rgba(234, 88, 12, 0.8)',
                radarColor: isDark ? 'rgba(20, 184, 166, 0.2)' : 'rgba(79, 70, 229, 0.15)',
                glyphColor: isDark ? 'rgba(20, 184, 166, 0.5)' : 'rgba(79, 70, 229, 0.4)'
            };
        };
        
        // Project 3D point (x, y, z) into 2D canvas screen coordinates
        function project(x, y, z) {
            if (z <= 0) return { x: 0, y: 0, scale: 0, visible: false };
            const scale = fov / (z + fov);
            const screenX = vanishingPoint.x + x * scale;
            const screenY = vanishingPoint.y + y * scale;
            return { x: screenX, y: screenY, scale: scale, visible: true };
        }
        
        // Initialize objects
        function initScene() {
            comets = [];
            panels = [];
            clickParticles = [];
            
            // Create comets
            const count = 14;
            const glyphs = ['diamond', 'square', 'cross'];
            for (let i = 0; i < count; i++) {
                comets.push({
                    railIndex: Math.floor(Math.random() * rails.length),
                    z: Math.random() * maxZ,
                    speed: 6 + Math.random() * 8,
                    colorKey: Math.random() > 0.5 ? 'cyan' : 'orange',
                    glyph: glyphs[Math.floor(Math.random() * glyphs.length)]
                });
            }
            
            // Create Floating HUD Panels (Scale increased to w:240, h:100)
            panels.push({
                x: -450, y: -250, z: 900,
                w: 240, h: 100,
                type: 'wave',
                phase: 0
            });
            panels.push({
                x: 500, y: -220, z: 600,
                w: 240, h: 100,
                type: 'dial',
                phase: Math.PI / 3
            });
            panels.push({
                x: -550, y: 250, z: 400,
                w: 240, h: 100,
                type: 'matrix',
                phase: (Math.PI * 2) / 3
            });
            panels.push({
                x: 500, y: 280, z: 800,
                w: 240, h: 100,
                type: 'network',
                phase: Math.PI
            });
        }
        
        // Handle Mouse Events
        window.addEventListener('mousemove', (e) => {
            mouse.x = e.clientX;
            mouse.y = e.clientY;
        });
        
        window.addEventListener('mouseleave', () => {
            mouse.x = null;
            mouse.y = null;
        });
        
        window.addEventListener('click', (e) => {
            if (mouse.x === null || mouse.y === null) return;
            const colors = getThemeColors();
            
            // Spawn click burst in 3D
            const targetX = (e.clientX - vanishingPoint.x) / (fov / (500 + fov));
            const targetY = (e.clientY - vanishingPoint.y) / (fov / (500 + fov));
            
            for (let i = 0; i < 25; i++) {
                clickParticles.push({
                    x: targetX + (Math.random() - 0.5) * 100,
                    y: targetY + (Math.random() - 0.5) * 100,
                    z: 500 + (Math.random() - 0.5) * 150,
                    vx: (Math.random() - 0.5) * 12,
                    vy: (Math.random() - 0.5) * 12,
                    vz: -8 - Math.random() * 12,
                    life: 1.0,
                    decay: 0.015 + Math.random() * 0.015,
                    color: Math.random() > 0.5 ? colors.cyanComet : colors.orangeComet
                });
            }
        });
        
        window.addEventListener('resize', () => {
            globalWidth = globalCanvas.width = window.innerWidth;
            globalHeight = globalCanvas.height = window.innerHeight;
            targetVanishingPoint = { x: globalWidth * 0.5, y: globalHeight * 0.5 };
        });
        
        // Main Loop
        function animate() {
            ctx.clearRect(0, 0, globalWidth, globalHeight);
            const colors = getThemeColors();
            
            // Interpolate Vanishing Point towards mouse to create 3D Parallax warp lens
            if (mouse.x !== null && mouse.y !== null) {
                targetVanishingPoint.x = globalWidth * 0.5 + (mouse.x - globalWidth * 0.5) * 0.18;
                targetVanishingPoint.y = globalHeight * 0.5 + (mouse.y - globalHeight * 0.5) * 0.18;
            } else {
                targetVanishingPoint.x = globalWidth * 0.5;
                targetVanishingPoint.y = globalHeight * 0.5;
            }
            vanishingPoint.x += (targetVanishingPoint.x - vanishingPoint.x) * 0.07;
            vanishingPoint.y += (targetVanishingPoint.y - vanishingPoint.y) * 0.07;
            
            // Advance zOffset (perspective zoom)
            zOffset -= speed;
            if (zOffset < 0) {
                zOffset += maxZ / 6;
            }
            
            // 1. Draw Longitudinal Rails (lineWidth increased from 0.8 to 1.2)
            rails.forEach(rail => {
                const nearPt = project(rail.x, rail.y, minZ);
                const farPt = project(rail.x, rail.y, maxZ);
                
                if (nearPt.visible && farPt.visible) {
                    ctx.beginPath();
                    ctx.moveTo(nearPt.x, nearPt.y);
                    ctx.lineTo(farPt.x, farPt.y);
                    ctx.strokeStyle = colors.railColor;
                    ctx.lineWidth = 1.2;
                    ctx.stroke();
                }
            });
            
            // 2. Draw Transverse Grid Rings (lineWidth increased from 0.6 to 1.0)
            const ringCount = 6;
            for (let i = 0; i < ringCount; i++) {
                const z = ((i * (maxZ / ringCount)) + zOffset) % maxZ;
                
                ctx.beginPath();
                rails.forEach((rail, index) => {
                    const pt = project(rail.x, rail.y, z);
                    if (pt.visible) {
                        if (index === 0) ctx.moveTo(pt.x, pt.y);
                        else ctx.lineTo(pt.x, pt.y);
                    }
                });
                ctx.closePath();
                ctx.strokeStyle = colors.gridColor;
                ctx.lineWidth = 1.0;
                ctx.stroke();
            }
            
            // 3. Update & Draw Comets (lineWidth increased to 3.5, head radius to 6.0)
            comets.forEach(comet => {
                comet.z -= comet.speed;
                if (comet.z < minZ) {
                    comet.z = maxZ;
                    comet.railIndex = Math.floor(Math.random() * rails.length);
                    comet.speed = 6 + Math.random() * 8;
                    comet.colorKey = Math.random() > 0.5 ? 'cyan' : 'orange';
                }
                
                const rail = rails[comet.railIndex];
                const head = project(rail.x, rail.y, comet.z);
                const tail = project(rail.x, rail.y, comet.z + 120);
                
                if (head.visible && tail.visible) {
                    const cometColor = comet.colorKey === 'cyan' ? colors.cyanComet : colors.orangeComet;
                    
                    // Gradient Trail
                    const grad = ctx.createLinearGradient(tail.x, tail.y, head.x, head.y);
                    grad.addColorStop(0, 'rgba(0,0,0,0)');
                    grad.addColorStop(1, cometColor);
                    
                    ctx.beginPath();
                    ctx.moveTo(tail.x, tail.y);
                    ctx.lineTo(head.x, head.y);
                    ctx.strokeStyle = grad;
                    ctx.lineWidth = 3.5 * head.scale;
                    ctx.stroke();
                    
                    // Glow Head
                    ctx.beginPath();
                    ctx.arc(head.x, head.y, 6.0 * head.scale, 0, Math.PI * 2);
                    ctx.fillStyle = cometColor;
                    ctx.fill();
                    
                    // Display geometric abstract glyph (Scale increased from 6 to 9)
                    if (comet.z < 800 && comet.z > 150) {
                        let textAlpha = 1;
                        if (comet.z > 600) textAlpha = (800 - comet.z) / 200;
                        else if (comet.z < 300) textAlpha = (comet.z - 150) / 150;
                        
                        ctx.save();
                        ctx.globalAlpha = textAlpha;
                        ctx.strokeStyle = colors.glyphColor;
                        ctx.lineWidth = 1.2 * head.scale;
                        
                        const size = 9 * head.scale;
                        const gx = head.x + 14 * head.scale;
                        const gy = head.y;
                        
                        if (comet.glyph === 'diamond') {
                            ctx.beginPath();
                            ctx.moveTo(gx, gy - size);
                            ctx.lineTo(gx + size, gy);
                            ctx.lineTo(gx, gy + size);
                            ctx.lineTo(gx - size, gy);
                            ctx.closePath();
                            ctx.stroke();
                        } else if (comet.glyph === 'square') {
                            ctx.strokeRect(gx - size/2, gy - size/2, size, size);
                        } else if (comet.glyph === 'cross') {
                            ctx.beginPath();
                            ctx.moveTo(gx - size/2, gy);
                            ctx.lineTo(gx + size/2, gy);
                            ctx.moveTo(gx, gy - size/2);
                            ctx.lineTo(gx, gy + size/2);
                            ctx.stroke();
                        }
                        ctx.restore();
                    }
                }
            });
            
            // 4. Update & Draw Floating HUD Panels (Scale increased inside panel visuals)
            panels.forEach(panel => {
                const driftY = Math.sin(Date.now() * 0.001 + panel.phase) * 15;
                const currentY = panel.y + driftY;
                
                // Get 4 projected corners
                const tl = project(panel.x - panel.w / 2, currentY - panel.h / 2, panel.z);
                const tr = project(panel.x + panel.w / 2, currentY - panel.h / 2, panel.z);
                const br = project(panel.x + panel.w / 2, currentY + panel.h / 2, panel.z);
                const bl = project(panel.x - panel.w / 2, currentY + panel.h / 2, panel.z);
                
                if (tl.visible && tr.visible && br.visible && bl.visible) {
                    // Fill Panel
                    ctx.beginPath();
                    ctx.moveTo(tl.x, tl.y);
                    ctx.lineTo(tr.x, tr.y);
                    ctx.lineTo(br.x, br.y);
                    ctx.lineTo(bl.x, bl.y);
                    ctx.closePath();
                    ctx.fillStyle = colors.hudBg;
                    ctx.fill();
                    
                    // Draw clean futuristic corner bracket lines
                    const bracketLen = 18 * tl.scale;
                    ctx.strokeStyle = colors.hudBorder;
                    ctx.lineWidth = 1.2;
                    
                    // Top Left Corner
                    ctx.beginPath();
                    ctx.moveTo(tl.x, tl.y + bracketLen);
                    ctx.lineTo(tl.x, tl.y);
                    ctx.lineTo(tl.x + bracketLen, tl.y);
                    ctx.stroke();
                    
                    // Top Right Corner
                    ctx.beginPath();
                    ctx.moveTo(tr.x - bracketLen, tr.y);
                    ctx.lineTo(tr.x, tr.y);
                    ctx.lineTo(tr.x, tr.y + bracketLen);
                    ctx.stroke();
                    
                    // Bottom Right Corner
                    ctx.beginPath();
                    ctx.moveTo(br.x, br.y - bracketLen);
                    ctx.lineTo(br.x, br.y);
                    ctx.lineTo(br.x - bracketLen, br.y);
                    ctx.stroke();
                    
                    // Bottom Left Corner
                    ctx.beginPath();
                    ctx.moveTo(bl.x + bracketLen, bl.y);
                    ctx.lineTo(bl.x, bl.y);
                    ctx.lineTo(bl.x, bl.y - bracketLen);
                    ctx.stroke();
                    
                    // Draw Panel Graphics instead of text writings
                    if (panel.type === 'wave') {
                        // Draw mini oscilloscope line inside the panel
                        ctx.beginPath();
                        ctx.strokeStyle = colors.hudBorder;
                        ctx.lineWidth = 1.2;
                        const startX = tl.x + 16 * tl.scale;
                        const endX = tr.x - 16 * tl.scale;
                        const centerY = tl.y + (bl.y - tl.y) * 0.45;
                        const amp = 18 * tl.scale;
                        
                        ctx.moveTo(startX, centerY);
                        for (let px = startX; px <= endX; px += 2) {
                            const norm = (px - startX) / (endX - startX);
                            const py = centerY + Math.sin(norm * Math.PI * 4.5 + Date.now() * 0.004) * amp;
                            ctx.lineTo(px, py);
                        }
                        ctx.stroke();
                        
                        // Progress bar status
                        const barX = tl.x + 16 * tl.scale;
                        const barY = bl.y - 20 * tl.scale;
                        const barW = (tr.x - tl.x) - 32 * tl.scale;
                        const barH = 4.5 * tl.scale;
                        
                        ctx.strokeStyle = colors.railColor;
                        ctx.strokeRect(barX, barY, barW, barH);
                        
                        const fillW = barW * (0.5 + Math.sin(Date.now() * 0.0012) * 0.35);
                        ctx.fillStyle = colors.hudText;
                        ctx.fillRect(barX, barY, fillW, barH);
                        
                    } else if (panel.type === 'dial') {
                        // Draw dial on the left
                        const dialX = tl.x + 40 * tl.scale;
                        const dialY = tl.y + (bl.y - tl.y) * 0.5;
                        const dialR = 24 * tl.scale;
                        
                        ctx.beginPath();
                        ctx.arc(dialX, dialY, dialR, 0, Math.PI * 2);
                        ctx.strokeStyle = colors.hudBorder;
                        ctx.stroke();
                        
                        const dialAngle = (Date.now() * 0.0025) % (Math.PI * 2);
                        ctx.beginPath();
                        ctx.moveTo(dialX, dialY);
                        ctx.lineTo(dialX + Math.cos(dialAngle) * dialR, dialY + Math.sin(dialAngle) * dialR);
                        ctx.stroke();
                        
                        // Vertical bar gauges on the right
                        const graphX = tl.x + 90 * tl.scale;
                        const graphW = 10 * tl.scale;
                        const maxH = 36 * tl.scale;
                        const graphY = tl.y + 24 * tl.scale;
                        
                        for (let bi = 0; bi < 5; bi++) {
                            const bx = graphX + bi * 16 * tl.scale;
                            const valH = maxH * (0.2 + 0.8 * Math.sin(Date.now() * 0.0016 + bi * 0.6) * Math.sin(Date.now() * 0.0016 + bi * 0.6));
                            ctx.fillStyle = colors.hudBorder;
                            ctx.fillRect(bx, graphY + maxH - valH, graphW, valH);
                        }
                        
                    } else if (panel.type === 'matrix') {
                        // Draw grid of status dots
                        const startDotX = tl.x + 30 * tl.scale;
                        const startDotY = tl.y + 24 * tl.scale;
                        const spacingX = 26 * tl.scale;
                        const spacingY = 16 * tl.scale;
                        
                        for (let r = 0; r < 4; r++) {
                            for (let c = 0; c < 7; c++) {
                                const dx = startDotX + c * spacingX;
                                const dy = startDotY + r * spacingY;
                                const active = Math.sin(Date.now() * 0.0022 + r * 0.5 + c * 0.3) > 0.15;
                                
                                ctx.beginPath();
                                ctx.arc(dx, dy, 3.5 * tl.scale, 0, Math.PI * 2);
                                ctx.fillStyle = active ? colors.hudText : colors.railColor;
                                ctx.fill();
                            }
                        }
                        
                    } else if (panel.type === 'network') {
                        // Connected nodes (Scaled layout coordinates)
                        const nodes = [
                            { x: tl.x + 40 * tl.scale, y: tl.y + 30 * tl.scale },
                            { x: tl.x + 180 * tl.scale, y: tl.y + 20 * tl.scale },
                            { x: tl.x + 110 * tl.scale, y: tl.y + 55 * tl.scale },
                            { x: tl.x + 60 * tl.scale, y: tl.y + 75 * tl.scale },
                            { x: tl.x + 200 * tl.scale, y: tl.y + 70 * tl.scale }
                        ];
                        
                        ctx.strokeStyle = colors.railColor;
                        ctx.lineWidth = 1.0;
                        
                        ctx.beginPath();
                        ctx.moveTo(nodes[0].x, nodes[0].y); ctx.lineTo(nodes[2].x, nodes[2].y);
                        ctx.moveTo(nodes[1].x, nodes[1].y); ctx.lineTo(nodes[2].x, nodes[2].y);
                        ctx.moveTo(nodes[2].x, nodes[2].y); ctx.lineTo(nodes[3].x, nodes[3].y);
                        ctx.moveTo(nodes[2].x, nodes[2].y); ctx.lineTo(nodes[4].x, nodes[4].y);
                        ctx.moveTo(nodes[3].x, nodes[3].y); ctx.lineTo(nodes[4].x, nodes[4].y);
                        ctx.stroke();
                        
                        nodes.forEach((n, nidx) => {
                            ctx.beginPath();
                            ctx.arc(n.x, n.y, (nidx === 2 ? 6.5 : 3.5) * tl.scale, 0, Math.PI * 2);
                            ctx.fillStyle = nidx === 2 ? colors.hudText : colors.hudBorder;
                            ctx.fill();
                        });
                    }
                }
            });
            
            // 5. Update & Draw Click Burst Particles
            for (let i = clickParticles.length - 1; i >= 0; i--) {
                const p = clickParticles[i];
                p.x += p.vx;
                p.y += p.vy;
                p.z += p.vz;
                p.life -= p.decay;
                
                if (p.life <= 0 || p.z < minZ) {
                    clickParticles.splice(i, 1);
                    continue;
                }
                
                const proj = project(p.x, p.y, p.z);
                if (proj.visible) {
                    ctx.save();
                    ctx.globalAlpha = p.life;
                    ctx.beginPath();
                    ctx.arc(proj.x, proj.y, 3.5 * proj.scale, 0, Math.PI * 2);
                    ctx.fillStyle = p.color;
                    ctx.fill();
                    ctx.restore();
                }
            }
            
            // 6. Sonar Radar Focus Ring (Cursor Analyzer) (Radius increased to 240)
            if (mouse.x !== null && mouse.y !== null) {
                // Outer scan circle
                ctx.beginPath();
                ctx.arc(mouse.x, mouse.y, mouse.radius, 0, Math.PI * 2);
                ctx.strokeStyle = colors.radarColor;
                ctx.lineWidth = 0.8;
                ctx.setLineDash([4, 4]);
                ctx.stroke();
                ctx.setLineDash([]);
                
                // Sweeper Sweep Line
                const angle = (Date.now() * 0.0016) % (Math.PI * 2);
                ctx.beginPath();
                ctx.moveTo(mouse.x, mouse.y);
                ctx.lineTo(
                    mouse.x + Math.cos(angle) * mouse.radius,
                    mouse.y + Math.sin(angle) * mouse.radius
                );
                ctx.strokeStyle = colors.radarColor.replace('0.2', '0.08');
                ctx.lineWidth = 1.2;
                ctx.stroke();
                
                // Crosshair HUD lines
                const tickLen = 12;
                ctx.strokeStyle = colors.radarColor;
                ctx.lineWidth = 1.2;
                
                // Horizontal crosshair ticks
                ctx.beginPath();
                ctx.moveTo(mouse.x - tickLen, mouse.y);
                ctx.lineTo(mouse.x + tickLen, mouse.y);
                ctx.moveTo(mouse.x, mouse.y - tickLen);
                ctx.lineTo(mouse.x, mouse.y + tickLen);
                
                // Radar bounds notches
                ctx.moveTo(mouse.x - mouse.radius, mouse.y - 6);
                ctx.lineTo(mouse.x - mouse.radius, mouse.y + 6);
                ctx.moveTo(mouse.x + mouse.radius, mouse.y - 6);
                ctx.lineTo(mouse.x + mouse.radius, mouse.y + 6);
                ctx.moveTo(mouse.x - 6, mouse.y - mouse.radius);
                ctx.lineTo(mouse.x + 6, mouse.y - mouse.radius);
                ctx.moveTo(mouse.x - 6, mouse.y + mouse.radius);
                ctx.lineTo(mouse.x + 6, mouse.y + mouse.radius);
                ctx.stroke();
                
                // Identify target comets inside the scanner range
                let targetedComet = null;
                let minDist = mouse.radius;
                
                comets.forEach(comet => {
                    const rail = rails[comet.railIndex];
                    const pos = project(rail.x, rail.y, comet.z);
                    if (pos.visible) {
                        const dx = pos.x - mouse.x;
                        const dy = pos.y - mouse.y;
                        const dist = Math.sqrt(dx*dx + dy*dy);
                        if (dist < minDist) {
                            minDist = dist;
                            targetedComet = { pos, comet };
                        }
                    }
                });
                
                if (targetedComet) {
                    const { pos, comet } = targetedComet;
                    // Draw locking bridge line
                    ctx.beginPath();
                    ctx.moveTo(mouse.x, mouse.y);
                    ctx.lineTo(pos.x, pos.y);
                    ctx.strokeStyle = comet.colorKey === 'cyan' ? colors.cyanComet.replace('0.85', '0.35') : colors.orangeComet.replace('0.85', '0.35');
                    ctx.lineWidth = 1.2;
                    ctx.stroke();
                    
                    // Locking box around target (Size increased to 16)
                    const boxSize = 16;
                    ctx.strokeStyle = comet.colorKey === 'cyan' ? colors.cyanComet : colors.orangeComet;
                    ctx.strokeRect(pos.x - boxSize/2, pos.y - boxSize/2, boxSize, boxSize);
                }
            }
            
            requestAnimationFrame(animate);
        }
        
        initScene();
        animate();
    }

});
