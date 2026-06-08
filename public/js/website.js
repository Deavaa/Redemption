// ========== Navbar Scroll Shrink & Mobile Fix ==========
(function() {
    var navbar = document.getElementById('navbar');
    if (!navbar) return;

    // Ensure navbar stays fixed at top on mobile at all times
    function fixNavbarPosition() {
        var ticker = document.getElementById('announcementTicker');
        if (ticker && ticker.style.display !== 'none' && ticker.offsetHeight > 0) {
            navbar.style.top = ticker.offsetHeight + 'px';
        } else {
            navbar.style.top = '0';
        }
    }

    // Fix on load, scroll, and resize
    fixNavbarPosition();
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        fixNavbarPosition();
    }, { passive: true });
    window.addEventListener('resize', fixNavbarPosition, { passive: true });

    // Ensure hamburger button is always visible on mobile
    var hamburgerBtn = document.getElementById('hamburgerBtn');
    if (hamburgerBtn && window.innerWidth <= 991) {
        hamburgerBtn.style.display = 'flex';
        hamburgerBtn.style.visibility = 'visible';
        hamburgerBtn.style.opacity = '1';
    }

    // Re-check on resize
    window.addEventListener('resize', function() {
        if (hamburgerBtn) {
            if (window.innerWidth <= 991) {
                hamburgerBtn.style.display = 'flex';
                hamburgerBtn.style.visibility = 'visible';
                hamburgerBtn.style.opacity = '1';
            } else {
                hamburgerBtn.style.display = '';
                hamburgerBtn.style.visibility = '';
                hamburgerBtn.style.opacity = '';
            }
        }
    }, { passive: true });
})();

// ========== Scroll Reveal (with performance optimization) ==========
(function() {
    var revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
    if (!revealElements.length) return;
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target); // Stop observing once revealed for performance
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
        var drawer = document.getElementById('mobileDrawer');
        var overlay = document.getElementById('mobileDrawerOverlay');
        if (drawer) {
            drawer.classList.remove('active');
        }
        if (overlay) {
            overlay.classList.remove('active');
        }
        document.body.style.overflow = '';
    }

    function openMobileDrawer() {
        if (mobileDrawer) {
            mobileDrawer.classList.add('active');
        }
        if (mobileDrawerOverlay) {
            mobileDrawerOverlay.classList.add('active');
        }
        document.body.style.overflow = 'hidden'; // Prevent background scroll
    }

    if (hamburgerBtn && mobileDrawer) {
        hamburgerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (mobileDrawer.classList.contains('active')) {
                closeMobileDrawer();
            } else {
                openMobileDrawer();
            }
        });
        if (mobileDrawerClose) {
            mobileDrawerClose.addEventListener('click', closeMobileDrawer);
        }
        if (mobileDrawerOverlay) {
            mobileDrawerOverlay.addEventListener('click', closeMobileDrawer);
        }

        mobileDrawer.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', closeMobileDrawer);
        });
    }

    // Close drawer on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMobileDrawer();
    });
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
    }, { passive: true });
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
    }, { passive: true });

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

// ========== Counter Animation (performance-optimized) ==========
(function() {
    var counters = document.querySelectorAll('.counter');
    if (!counters.length) return;

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var counter = entry.target;
                var target = parseInt(counter.getAttribute('data-target'));
                if (isNaN(target) || target === 0) {
                    counter.textContent = target;
                    observer.unobserve(counter);
                    return;
                }
                var duration = 2000;
                var startTime = null;

                function animate(currentTime) {
                    if (!startTime) startTime = currentTime;
                    var elapsed = currentTime - startTime;
                    var progress = Math.min(elapsed / duration, 1);
                    // Ease out cubic for smooth deceleration
                    var easedProgress = 1 - Math.pow(1 - progress, 3);
                    counter.textContent = Math.floor(easedProgress * target);
                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    } else {
                        counter.textContent = target;
                    }
                }
                requestAnimationFrame(animate);
                observer.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(function(counter) { observer.observe(counter); });
})();

// ========== Image Lazy Loading Fallback ==========
(function() {
    // For browsers that don't support native lazy loading
    if ('loading' in HTMLImageElement.prototype) return;

    var lazyImages = document.querySelectorAll('img[loading="lazy"]');
    if (!lazyImages.length) return;

    var imageObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
                imageObserver.unobserve(img);
            }
        });
    }, { rootMargin: '100px' });

    lazyImages.forEach(function(img) { imageObserver.observe(img); });
})();
