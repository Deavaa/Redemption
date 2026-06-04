// ========== Navbar Scroll Shrink ==========
        (function() {
            var navbar = document.getElementById('navbar');
            if (!navbar) return;
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        })();

        // ========== Scroll Reveal ==========
        (function() {
            var revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
            if (!revealElements.length) return;
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
            revealElements.forEach(function(el) { observer.observe(el); });
        })();

        // ========== Mobile Drawer ==========
        (function() {
            var hamburgerBtn = document.getElementById('hamburgerBtn');
            var mobileDrawer = document.getElementById('mobileDrawer');
            var mobileDrawerOverlay = document.getElementById('mobileDrawerOverlay');
            var mobileDrawerClose = document.getElementById('mobileDrawerClose');

            function closeMobileDrawer() {
                var mobileDrawer = document.getElementById('mobileDrawer');
                var mobileDrawerOverlay = document.getElementById('mobileDrawerOverlay');
                if (mobileDrawer) {
                    mobileDrawer.classList.remove('active');
                    mobileDrawerOverlay.classList.remove('active');
                }
            }

            if (hamburgerBtn && mobileDrawer) {
                hamburgerBtn.addEventListener('click', function() {
                    mobileDrawer.classList.add('active');
                    mobileDrawerOverlay.classList.add('active');
                });
                mobileDrawerClose.addEventListener('click', closeMobileDrawer);
                mobileDrawerOverlay.addEventListener('click', closeMobileDrawer);

                mobileDrawer.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', closeMobileDrawer);
                });
            }
        })();

        // ========== Back to Top ==========
        (function() {
            var backToTop = document.getElementById('backToTop');
            if (!backToTop) return;
            window.addEventListener('scroll', function() {
                if (window.scrollY > 500) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
            });
            backToTop.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        })();

        // ========== Custom Cursor (Desktop only) ==========
        (function() {
            if (window.innerWidth <= 991) return;
            var dot = document.getElementById('cursorDot');
            var ring = document.getElementById('cursorRing');
            if (!dot || !ring) return;

            var mouseX = 0, mouseY = 0;
            var ringX = 0, ringY = 0;

            document.addEventListener('mousemove', function(e) {
                mouseX = e.clientX;
                mouseY = e.clientY;
                dot.style.left = mouseX + 'px';
                dot.style.top = mouseY + 'px';
            });

            function animateRing() {
                ringX += (mouseX - ringX) * 0.15;
                ringY += (mouseY - ringY) * 0.15;
                ring.style.left = ringX + 'px';
                ring.style.top = ringY + 'px';
                requestAnimationFrame(animateRing);
            }
            animateRing();

            // Hover effect on interactive elements
            var interactiveElements = document.querySelectorAll('a, button, .btn, input, textarea, select');
            interactiveElements.forEach(function(el) {
                el.addEventListener('mouseenter', function() {
                    ring.style.width = '50px';
                    ring.style.height = '50px';
                    ring.style.borderColor = 'rgba(212, 160, 23, 0.6)';
                    dot.style.transform = 'scale(1.5)';
                });
                el.addEventListener('mouseleave', function() {
                    ring.style.width = '36px';
                    ring.style.height = '36px';
                    ring.style.borderColor = 'rgba(212, 160, 23, 0.5)';
                    dot.style.transform = 'scale(1)';
                });
            });
        })();