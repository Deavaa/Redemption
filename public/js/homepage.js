// ========== Smooth Scrolling for Anchor Links ==========
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    var offset = 80;
                    var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({ top: top, behavior: 'smooth' });
                }
                // Close mobile drawer if open
                var mobileDrawer = document.getElementById('mobileDrawer');
                var mobileDrawerOverlay = document.getElementById('mobileDrawerOverlay');
                if (mobileDrawer) {
                    mobileDrawer.classList.remove('active');
                    mobileDrawerOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        // ========== Counter Animation ==========
        (function() {
            var counters = document.querySelectorAll('.counter');
            var counterObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var el = entry.target;
                        var target = parseInt(el.getAttribute('data-target'));
                        var duration = 2000;
                        var start = 0;
                        var startTime = null;

                        function animate(currentTime) {
                            if (!startTime) startTime = currentTime;
                            var progress = Math.min((currentTime - startTime) / duration, 1);
                            // Ease out
                            var ease = 1 - Math.pow(1 - progress, 3);
                            var current = Math.floor(ease * target);
                            el.textContent = current;
                            if (progress < 1) {
                                requestAnimationFrame(animate);
                            } else {
                                el.textContent = target;
                            }
                        }

                        requestAnimationFrame(animate);
                        counterObserver.unobserve(el);
                    }
                });
            }, {
                threshold: 0.5
            });

            counters.forEach(function(counter) {
                counterObserver.observe(counter);
            });
        })();

        // ========== Video Modal ==========
        function openVideoModal(videoId) {
            var modal = document.getElementById('videoModal');
            var iframe = document.getElementById('videoModalIframe');
            iframe.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        (function() {
            var modal = document.getElementById('videoModal');
            if (!modal) return;
            var closeBtn = document.getElementById('videoModalClose');
            var iframe = document.getElementById('videoModalIframe');

            closeBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                iframe.src = '';
                document.body.style.overflow = '';
            });

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                    iframe.src = '';
                    document.body.style.overflow = '';
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    modal.classList.remove('active');
                    iframe.src = '';
                    document.body.style.overflow = '';
                }
            });
        })();

        // ========== Programs Horizontal Scroll Nav ==========
        (function() {
            var wrapper = document.getElementById('programsScrollWrapper');
            var leftBtn = document.getElementById('programsScrollLeft');
            var rightBtn = document.getElementById('programsScrollRight');

            if (wrapper && leftBtn && rightBtn) {
                leftBtn.addEventListener('click', function() {
                    wrapper.scrollBy({ left: -360, behavior: 'smooth' });
                });
                rightBtn.addEventListener('click', function() {
                    wrapper.scrollBy({ left: 360, behavior: 'smooth' });
                });
            }
        })();

        // ========== Hero Parallax Effect ==========
        (function() {
            var heroSlides = document.querySelectorAll('[data-parallax]');
            window.addEventListener('scroll', function() {
                var scrollY = window.pageYOffset;
                heroSlides.forEach(function(slide) {
                    var rect = slide.getBoundingClientRect();
                    if (rect.bottom > 0 && rect.top < window.innerHeight) {
                        slide.style.backgroundPositionY = (scrollY * 0.3) + 'px';
                    }
                });
            }, { passive: true });
        })();

        // ========== Announcement Ticker Script ==========
        (function() {
            var tickerEl = document.getElementById('announcementTicker');
            var trackEl = document.getElementById('tickerTrack');
            var closeBtn = document.getElementById('tickerClose');
            var navbar = document.getElementById('navbar');
            if (!tickerEl || !trackEl) return;

            if (sessionStorage.getItem('ticker_dismissed')) {
                tickerEl.style.display = 'none';
                if (navbar) navbar.style.top = '0';
                return;
            }

            // Defensive: closeBtn may be missing on some page variants.
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    tickerEl.style.display = 'none';
                    if (navbar) navbar.style.top = '0';
                    sessionStorage.setItem('ticker_dismissed', '1');
                });
            }

            var categoryLabels = {
                'holiday': 'Holiday', 'exam': 'Exam', 'event': 'Event',
                'meeting': 'Meeting', 'deadline': 'Deadline', 'other': 'Info'
            };

            fetch('/api/public/announcements', {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data || data.length === 0) {
                    tickerEl.style.display = 'none';
                    if (navbar) navbar.style.top = '0';
                    return;
                }

                var html = '';
                data.forEach(function(item) {
                    var dotColor = item.color || '#fff';
                    var cat = categoryLabels[item.category] || item.category || '';
                    html += '<span class="ticker-item">';
                    html += '<span class="ticker-dot" style="background:' + dotColor + '"></span>';
                    html += '<span class="ticker-cat">' + cat + '</span>';
                    html += ' ' + item.title;
                    if (item.start_date) html += ' <span class="ticker-date">(' + item.start_date + ')</span>';
                    html += '</span>';
                });

                var contentWidth = trackEl.scrollWidth;
                var containerWidth = trackEl.parentElement.offsetWidth;

                if (contentWidth > containerWidth) {
                    trackEl.innerHTML = html + html;
                    trackEl.classList.add('scrolling');
                    var totalWidth = trackEl.scrollWidth / 2;
                    var speed = Math.max(60, totalWidth / 5);
                    trackEl.style.animationDuration = speed + 's';
                } else {
                    trackEl.innerHTML = html;
                }

                tickerEl.style.display = 'block';
            })
            .catch(function() {
                tickerEl.style.display = 'none';
                if (navbar) navbar.style.top = '0';
            });
        })();