/**
 * Redemption School - PWA Install Prompt
 * Shows a one-time install suggestion to users who haven't installed the app yet
 */
(function() {
    'use strict';

    let deferredPrompt = null;
    const STORAGE_KEY = 'redemption_pwa_dismissed';
    const INSTALL_SHOWN_KEY = 'redemption_pwa_install_shown';

    // Check if already dismissed recently (within 7 days)
    function wasRecentlyDismissed() {
        const dismissed = localStorage.getItem(STORAGE_KEY);
        if (!dismissed) return false;
        const dismissedTime = parseInt(dismissed, 10);
        const daysSinceDismissed = (Date.now() - dismissedTime) / (1000 * 60 * 60 * 24);
        return daysSinceDismissed < 7;
    }

    // Check if already in standalone mode (app installed)
    function isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches || 
               window.navigator.standalone === true;
    }

    // Catch the beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        // Show install suggestion after a delay if not recently dismissed
        if (!isStandalone() && !wasRecentlyDismissed()) {
            setTimeout(showInstallSuggestion, 5000);
        }
    });

    function showInstallSuggestion() {
        // Don't show if already shown this session
        if (sessionStorage.getItem(INSTALL_SHOWN_KEY)) return;
        // Don't show if in standalone mode
        if (isStandalone()) return;
        // Don't show if no deferred prompt (can't install)
        if (!deferredPrompt) return;

        sessionStorage.setItem(INSTALL_SHOWN_KEY, '1');

        // Create the toast notification
        const toast = document.createElement('div');
        toast.id = 'pwa-install-toast';
        toast.innerHTML = `
            <div style="position:fixed;bottom:20px;right:20px;z-index:10000;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:14px;padding:16px 20px;box-shadow:0 8px 30px rgba(99,102,241,0.35);max-width:320px;font-family:'Inter',-apple-system,sans-serif;animation:slideInUp 0.3s ease;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:14px;">Install Redemption App</div>
                        <div style="font-size:11px;opacity:0.8;">Quick access from your home screen</div>
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button id="pwaInstallBtn" style="flex:1;padding:8px;border-radius:8px;background:#fff;color:#6366f1;font-weight:700;font-size:12px;border:none;cursor:pointer;">Install</button>
                    <button id="pwaDismissBtn" style="padding:8px 12px;border-radius:8px;background:rgba(255,255,255,0.15);color:#fff;font-size:12px;border:none;cursor:pointer;">Not now</button>
                </div>
            </div>
        `;

        // Add animation
        const style = document.createElement('style');
        style.textContent = '@keyframes slideInUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}';
        document.head.appendChild(style);
        document.body.appendChild(toast);

        // Button handlers
        document.getElementById('pwaInstallBtn').addEventListener('click', function() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('[PWA] User accepted install');
                    }
                    deferredPrompt = null;
                });
            }
            toast.remove();
        });

        document.getElementById('pwaDismissBtn').addEventListener('click', function() {
            localStorage.setItem(STORAGE_KEY, Date.now().toString());
            toast.remove();
        });
    }

    // Track app installation
    window.addEventListener('appinstalled', (evt) => {
        console.log('[PWA] App installed successfully');
        deferredPrompt = null;
        // Show success message
        const toast = document.createElement('div');
        toast.innerHTML = `
            <div style="position:fixed;top:20px;right:20px;z-index:10000;background:#10b981;color:#fff;border-radius:12px;padding:14px 20px;box-shadow:0 4px 20px rgba(16,185,129,0.3);font-family:'Inter',sans-serif;font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px;animation:slideInUp 0.3s ease;">
                <i class="fas fa-check-circle"></i> App installed successfully!
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    });
})();
