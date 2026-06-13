<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Reading: {{ $library->title }} - Redemption School</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    /* ===== RESET ===== */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
        font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        height: 100%;
        overflow: hidden;
        background: #1a1a2e;
    }

    /* ===== READER PAGE ===== */
    .reader-page {
        display: flex;
        flex-direction: column;
        height: 100vh;
        background: #1a1a2e;
        overflow: hidden;
    }

    /* ===== TOOLBAR ===== */
    .reader-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.65rem 1.25rem;
        background: #2d2d3a;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
    }

    .reader-toolbar-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
        min-width: 0;
    }

    .reader-back-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(255,255,255,0.1);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background 0.2s;
        flex-shrink: 0;
    }
    .reader-back-btn:hover { background: rgba(255,255,255,0.2); color: #fff; }

    .reader-book-info { min-width: 0; }

    .reader-book-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #fff;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .reader-book-author {
        font-size: 0.75rem;
        color: rgba(255,255,255,0.6);
    }

    .reader-toolbar-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .reader-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.7rem;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 600;
        background: rgba(255,255,255,0.1);
        color: rgba(255,255,255,0.7);
    }

    .reader-badge-info {
        background: rgba(16,185,129,0.2);
        color: #6ee7b7;
    }

    /* ===== COPYRIGHT BANNER ===== */
    .copyright-banner {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 1.25rem;
        background: rgba(217,119,6,0.15);
        border-bottom: 1px solid rgba(217,119,6,0.3);
        font-size: 0.78rem;
        color: #fcd34d;
        flex-shrink: 0;
    }
    .copyright-banner i { color: #fbbf24; }

    /* ===== BACK TO LIBRARY BUTTON (visible, not at very bottom) ===== */
    .reader-back-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1.25rem;
        background: #2d2d3a;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
    }
    .reader-back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 1rem;
        border-radius: 8px;
        background: rgba(255,255,255,0.08);
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 500;
        transition: background 0.2s, color 0.2s;
    }
    .reader-back-link:hover {
        background: rgba(255,255,255,0.15);
        color: #fff;
    }

    /* ===== PDF VIEWER ===== */
    .reader-container {
        flex: 1;
        overflow: auto;
        display: flex;
        justify-content: center;
        padding: 1rem;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .pdf-viewer-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    #pdfCanvas {
        max-width: 100%;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }

    .pdf-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1.25rem;
        background: #2d2d3a;
        border-radius: 12px;
        margin-top: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        flex-wrap: wrap;
        justify-content: center;
    }

    .pdf-control-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        background: rgba(255,255,255,0.1);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
        font-size: 0.82rem;
    }
    .pdf-control-btn:hover { background: rgba(255,255,255,0.2); }

    .pdf-page-info {
        font-size: 0.78rem;
        color: rgba(255,255,255,0.8);
        font-weight: 600;
        min-width: 60px;
        text-align: center;
    }

    .pdf-separator {
        color: rgba(255,255,255,0.2);
        font-size: 0.9rem;
    }

    /* ===== GENERIC VIEWER ===== */
    .generic-viewer {
        width: 100%;
        max-width: 900px;
    }

    .generic-viewer-content {
        background: #fff;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
    }

    .generic-viewer-iframe-wrapper {
        margin-top: 1rem;
    }

    /* ===== MOBILE RESPONSIVE ===== */
    @media (max-width: 768px) {
        .reader-toolbar {
            padding: 0.5rem 0.75rem;
        }
        .reader-toolbar-right {
            display: none;
        }
        .reader-book-title {
            font-size: 0.85rem;
        }
        .copyright-banner {
            font-size: 0.7rem;
            padding: 0.4rem 0.75rem;
            gap: 0.5rem;
        }
        .reader-container {
            padding: 0.5rem;
        }
        .pdf-controls {
            gap: 0.35rem;
            padding: 0.5rem 0.75rem;
            margin-top: 0.75rem;
        }
        .pdf-control-btn {
            width: 30px;
            height: 30px;
            font-size: 0.75rem;
        }
        .pdf-page-info {
            font-size: 0.7rem;
            min-width: 45px;
        }
        .reader-back-bar {
            padding: 0.4rem 0.75rem;
        }
        .reader-back-link {
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
        }
    }
    </style>
</head>
<body>
<div class="reader-page">
    {{-- Reader Toolbar --}}
    <div class="reader-toolbar">
        <div class="reader-toolbar-left">
            <a href="{{ route('admin.library.index') }}" class="reader-back-btn" title="Back to Library">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="reader-book-info">
                <h1 class="reader-book-title">{{ $library->title }}</h1>
                @if($library->author)
                <span class="reader-book-author">by {{ $library->author }}</span>
                @endif
            </div>
        </div>
        <div class="reader-toolbar-right">
            <span class="reader-badge">
                <i class="fas fa-shield-alt"></i> Read-Only
            </span>
            <span class="reader-badge reader-badge-info">
                <i class="fas fa-eye"></i> {{ $library->read_count }} reads
            </span>
        </div>
    </div>

    {{-- Copyright Protection Banner --}}
    <div class="copyright-banner">
        <i class="fas fa-lock"></i>
        <span>This book is protected by copyright. Download, copy, print, and screenshot are disabled. For authorized reading only.</span>
    </div>

    {{-- Back to Library bar (visible but NOT at very bottom — avoids mobile bottom nav conflict) --}}
    <div class="reader-back-bar">
        <a href="{{ route('admin.library.index') }}" class="reader-back-link">
            <i class="fas fa-arrow-left"></i> Back to Library
        </a>
    </div>

    {{-- PDF Reader Container --}}
    <div class="reader-container" id="readerContainer">
        @if($isPdf)
        {{-- PDF.js based reader with download prevention --}}
        <div class="pdf-viewer-wrapper" id="pdfViewerWrapper">
            <canvas id="pdfCanvas"></canvas>
            <div class="pdf-controls">
                <button onclick="prevPage()" id="prevBtn" class="pdf-control-btn" title="Previous Page">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="pdf-page-info">
                    Page <span id="pageNum">1</span> of <span id="pageCount">0</span>
                </span>
                <button onclick="nextPage()" id="nextBtn" class="pdf-control-btn" title="Next Page">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <span class="pdf-separator">|</span>
                <button onclick="zoomOut()" class="pdf-control-btn" title="Zoom Out">
                    <i class="fas fa-search-minus"></i>
                </button>
                <span class="pdf-page-info" id="zoomLevel">100%</span>
                <button onclick="zoomIn()" class="pdf-control-btn" title="Zoom In">
                    <i class="fas fa-search-plus"></i>
                </button>
                <button onclick="fitWidth()" class="pdf-control-btn" title="Fit Width">
                    <i class="fas fa-arrows-alt-h"></i>
                </button>
            </div>
        </div>
        @else
        {{-- Generic viewer for non-PDF files --}}
        <div class="generic-viewer">
            <div class="generic-viewer-content">
                <i class="fas fa-book-open" style="font-size:3rem;color:#d1d5db;margin-bottom:1rem;display:block;"></i>
                <h3>{{ $library->title }}</h3>
                <p>This file format is best viewed in the browser's built-in viewer.</p>
                <div class="generic-viewer-iframe-wrapper">
                    <iframe src="{{ route('admin.library.serve', $library->id) }}?token={{ $readToken }}#toolbar=0&navpanes=0&scrollbar=1"
                            style="width:100%;height:75vh;border:none;border-radius:12px;"
                            sandbox="allow-same-origin"
                            id="genericIframe">
                    </iframe>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@if($isPdf)
{{-- PDF.js library --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
// Configure PDF.js worker
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

let pdfDoc = null;
let currentPage = 1;
let currentScale = 1.5;
const canvas = document.getElementById('pdfCanvas');
const ctx = canvas.getContext('2d');

// Load PDF using the serve route with reader flag (prevents direct file access/download)
const pdfUrl = '{{ route('admin.library.serve', $library->id) }}?token={{ $readToken }}';

// Use fetch to load PDF as ArrayBuffer (prevents browser from intercepting as download)
fetch(pdfUrl, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
})
.then(function(response) {
    if (!response.ok) throw new Error('Failed to load PDF');
    return response.arrayBuffer();
})
.then(function(data) {
    return pdfjsLib.getDocument({ data: data }).promise;
})
.then(function(pdf) {
    pdfDoc = pdf;
    document.getElementById('pageCount').textContent = pdf.numPages;
    renderPage(currentPage);
}).catch(function(error) {
    console.error('Error loading PDF:', error);
    document.getElementById('pdfViewerWrapper').innerHTML =
        '<div style="text-align:center;padding:3rem;color:#fca5a5;">' +
        '<i class="fas fa-exclamation-triangle" style="font-size:2rem;display:block;margin-bottom:1rem;"></i>' +
        '<h3>Failed to load PDF</h3>' +
        '<p style="color:rgba(255,255,255,0.6);font-size:0.85rem;">The file could not be loaded. Please try again later.</p>' +
        '</div>';
});

function renderPage(num) {
    pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({ scale: currentScale });
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };

        page.render(renderContext).promise.then(function() {
            document.getElementById('pageNum').textContent = num;
            document.getElementById('zoomLevel').textContent = Math.round(currentScale / 1.5 * 100) + '%';
        });
    });
}

function prevPage() {
    if (currentPage <= 1) return;
    currentPage--;
    renderPage(currentPage);
}

function nextPage() {
    if (currentPage >= pdfDoc.numPages) return;
    currentPage++;
    renderPage(currentPage);
}

function zoomIn() {
    currentScale = Math.min(currentScale + 0.3, 4);
    renderPage(currentPage);
}

function zoomOut() {
    currentScale = Math.max(currentScale - 0.3, 0.5);
    renderPage(currentPage);
}

function fitWidth() {
    const containerWidth = document.getElementById('readerContainer').clientWidth - 60;
    pdfDoc.getPage(currentPage).then(function(page) {
        const viewport = page.getViewport({ scale: 1 });
        currentScale = containerWidth / viewport.width;
        renderPage(currentPage);
    });
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { e.preventDefault(); prevPage(); }
    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); nextPage(); }
});
</script>
@endif

<script>
// ===== COPYRIGHT PROTECTION =====
// Disable right-click
document.addEventListener('contextmenu', function(e) { e.preventDefault(); });

// Disable keyboard shortcuts for save, copy, print
document.addEventListener('keydown', function(e) {
    // Ctrl+S, Ctrl+P, Ctrl+C, Ctrl+Shift+S, Ctrl+Shift+I, F12
    if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'p' || e.key === 'P' || e.key === 'c' || e.key === 'C')) {
        e.preventDefault();
        return false;
    }
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 's' || e.key === 'S' || e.key === 'I' || e.key === 'i')) {
        e.preventDefault();
        return false;
    }
    if (e.key === 'F12') {
        e.preventDefault();
        return false;
    }
    if (e.key === 'PrintScreen') {
        e.preventDefault();
        // Clear clipboard
        navigator.clipboard.writeText('').catch(function(){});
        return false;
    }
});

// Disable drag
document.addEventListener('dragstart', function(e) { e.preventDefault(); });

// Block Ctrl+U (View Source)
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && (e.key === 'u' || e.key === 'U')) {
        e.preventDefault();
        return false;
    }
});

// Detect print attempt
window.addEventListener('beforeprint', function(e) {
    document.body.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100vh;background:#1a1a2e;color:#fca5a5;font-family:sans-serif;text-align:center;"><div><h1>Printing is Disabled</h1><p>This content is protected by copyright.</p></div></div>';
});

// Detect developer tools (basic detection) — reduced interval to save CPU
(function() {
    var threshold = 160;
    var check = function() {
        var widthThreshold = window.outerWidth - window.innerWidth > threshold;
        var heightThreshold = window.outerHeight - window.innerHeight > threshold;
        if (widthThreshold || heightThreshold) {
            document.body.style.opacity = '0.1';
            setTimeout(function() { document.body.style.opacity = '1'; }, 1000);
        }
    };
    // Check every 5 seconds instead of every 1 second to reduce CPU usage
    setInterval(check, 5000);
    // Also check on window resize (more efficient than constant polling)
    window.addEventListener('resize', check, { passive: true });
})();

// Prevent browser download bar / save dialog for PDF content
window.addEventListener('beforeunload', function(e) {
    if (e.target && e.target.location && String(e.target.location).includes('/serve')) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Add invisible watermark overlay to prevent screen capture abuse
(function() {
    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9999;opacity:0.01;background:transparent;';
    overlay.setAttribute('data-protected', 'true');
    document.body.appendChild(overlay);
})();

@if(!$isPdf)
// Copyright protection for non-PDF viewer (iframe)
var iframe = document.getElementById('genericIframe');
if (iframe) {
    iframe.onload = function() {
        try {
            iframe.contentDocument.addEventListener('contextmenu', function(e) { e.preventDefault(); });
            iframe.contentDocument.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'p' || e.key === 'c')) { e.preventDefault(); }
            });
        } catch(e) {
            // Cross-origin restriction - that's actually good for protection
        }
    };
}
@endif
</script>
</body>
</html>
