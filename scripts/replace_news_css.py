#!/usr/bin/env python3
"""
Replace section 4 (NEWS SPLASH) in modern-glass.css with a clean rewrite.
The panel is now OUTSIDE .hero-slider, so no more !important wars.
"""

import re

css_path = '/home/z/my-project/Redemption/public/css/modern-glass.css'
with open(css_path, 'r') as f:
    content = f.read()

# Find the start of section 4
start_marker = "/* ============================================================================\n   4. NEWS SPLASH"
# Find the start of section 5
end_marker = "/* ============================================================================\n   5. ANNOUNCEMENT TICKER"

start_idx = content.find(start_marker)
end_idx = content.find(end_marker)

if start_idx == -1 or end_idx == -1:
    print(f"ERROR: Could not find markers. start={start_idx}, end={end_idx}")
    exit(1)

print(f"Replacing section 4: chars {start_idx}-{end_idx} ({end_idx - start_idx} chars)")

new_section = """/* ============================================================================
   4. NEWS SPLASH — Right-Side Panel (clean, harmonious, OUTSIDE hero-slider)
   ----------------------------------------------------------------------------
   The panel is now a SIBLING of .hero-slider (not a child), so none of the
   .hero-slider p/h1 { color: white !important } rules apply here. This
   eliminates all cascade conflicts — no !important needed.
   ============================================================================ */

/* Wrapper — positioned fixed so the panel stays anchored to the right edge
   of the viewport over the hero area, regardless of scroll position. */
.news-splash-wrapper {
    position: fixed;
    top: 0;
    right: 0;
    height: 100vh;
    pointer-events: none;
    z-index: 200;
    display: flex;
    align-items: center;
}

/* The panel itself */
.news-splash-panel {
    pointer-events: auto;
    position: relative;
    width: 380px;
    max-width: 92vw;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    /* Clean white surface with a soft emerald-tinted gradient */
    background: linear-gradient(160deg,
        #ffffff 0%,
        #f0fdf4 100%);
    border: 1px solid #d1fae5;
    border-right: none;
    border-top-left-radius: 20px;
    border-bottom-left-radius: 20px;
    box-shadow:
        -12px 0 40px rgba(16, 185, 129, 0.12),
        0 20px 50px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateX(100%);
    transition:
        transform 0.5s cubic-bezier(0.32, 0.72, 0.18, 1),
        opacity 0.4s ease,
        visibility 0.4s ease;
}
.news-splash-panel.active {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
}

/* ---- Header ---- */
.news-splash-panel-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
    color: #ffffff;
    flex-shrink: 0;
}
.news-splash-panel-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.22);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.news-splash-panel-icon i {
    color: #ffffff;
    font-size: 0.95rem;
}
.news-splash-panel-title {
    flex: 1;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #ffffff;
}
.news-splash-panel-close {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.20);
    border: none;
    color: #ffffff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    transition: all 0.3s ease;
    flex-shrink: 0;
}
.news-splash-panel-close:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: rotate(90deg);
}

/* ---- Card list (scrollable) ---- */
.news-splash-panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    background: #f8faf9;
    scrollbar-width: thin;
    scrollbar-color: #10b981 transparent;
}
.news-splash-panel-body::-webkit-scrollbar { width: 5px; }
.news-splash-panel-body::-webkit-scrollbar-thumb {
    background: #10b981;
    border-radius: 5px;
}
.news-splash-panel-body::-webkit-scrollbar-track { background: transparent; }

/* ---- News card ---- */
.news-splash-card {
    display: flex;
    flex-direction: column;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}
.news-splash-card:hover {
    border-color: #10b981;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.15);
    transform: translateY(-2px);
}

/* Card image — always rendered (placeholder if no image) */
.news-splash-card-img {
    width: 100%;
    height: 140px;
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, #ecfdf5 0%, #f0fdfa 100%);
}
.news-splash-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}
.news-splash-card:hover .news-splash-card-img img {
    transform: scale(1.05);
}
.news-splash-card-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.news-splash-card-img-placeholder i {
    font-size: 2rem;
    color: #10b981;
    opacity: 0.4;
}

/* Card body — text content */
.news-splash-card-body {
    padding: 12px 14px 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

/* Meta row — date + "New" tag */
.news-splash-card-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.news-splash-card-date {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.68rem;
    color: #d97706;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.news-splash-card-date i {
    font-size: 0.7rem;
}
.news-splash-card-tag {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 50px;
    background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
    color: #ffffff;
    font-size: 0.58rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

/* Title — dark slate, bold, 2-line clamp */
.news-splash-card-title {
    margin: 0;
    color: #0f172a;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 0.95rem;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.3s ease;
}
.news-splash-card:hover .news-splash-card-title {
    color: #10b981;
}

/* Excerpt — medium gray, 3-line clamp */
.news-splash-card-excerpt {
    margin: 0;
    color: #475569;
    font-size: 0.80rem;
    line-height: 1.55;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ---- Footer ---- */
.news-splash-panel-footer {
    padding: 10px 18px;
    border-top: 1px solid #e5e7eb;
    background: #ffffff;
    text-align: center;
    flex-shrink: 0;
}
.news-splash-panel-dismiss-btn {
    background: none;
    border: none;
    color: #10b981;
    padding: 0;
    font: inherit;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    transition: color 0.3s ease;
}
.news-splash-panel-dismiss-btn:hover {
    color: #06b6d4;
}

/* ---- Toggle chip (visible when panel is closed) ---- */
.news-splash-toggle {
    pointer-events: auto;
    position: absolute;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
    background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
    border: none;
    border-right: none;
    border-top-left-radius: 50px;
    border-bottom-left-radius: 50px;
    padding: 14px 10px;
    color: #ffffff;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    box-shadow: -4px 6px 16px rgba(16, 185, 129, 0.30);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    writing-mode: vertical-rl;
    text-orientation: mixed;
    opacity: 0;
    visibility: hidden;
}
.news-splash-toggle.visible {
    opacity: 1;
    visibility: visible;
}
.news-splash-toggle:hover {
    padding-right: 14px;
    box-shadow: -6px 8px 20px rgba(16, 185, 129, 0.40);
}
.news-splash-toggle i {
    font-size: 1rem;
    writing-mode: horizontal-tb;
}
.news-splash-panel.active ~ .news-splash-toggle {
    opacity: 0;
    visibility: hidden;
}

/* ---- Mobile: panel becomes bottom sheet ---- */
@media (max-width: 991px) {
    .news-splash-wrapper {
        position: fixed;
        top: auto;
        bottom: 0;
        right: 0;
        left: 0;
        height: auto;
        align-items: flex-end;
    }
    .news-splash-panel {
        width: 100%;
        max-width: 100%;
        max-height: 75vh;
        border-radius: 20px 20px 0 0;
        border: 1px solid #d1fae5;
        border-bottom: none;
        transform: translateY(100%);
    }
    .news-splash-panel.active {
        transform: translateY(0);
    }
    .news-splash-toggle {
        top: auto;
        bottom: 90px;
        right: 16px;
        transform: none;
        writing-mode: horizontal-tb;
        border-radius: 50px;
        padding: 10px 16px;
        flex-direction: row;
        border: none;
    }
    .news-splash-toggle i { font-size: 0.85rem; }
}

@media (max-width: 575px) {
    .news-splash-panel-header { padding: 12px 14px; }
    .news-splash-panel-title { font-size: 0.78rem; }
    .news-splash-card-img { height: 120px; }
    .news-splash-card-title { font-size: 0.88rem; }
    .news-splash-card-body { padding: 10px 12px 12px; }
}

"""

new_content = content[:start_idx] + new_section + "\n" + content[end_idx:]

with open(css_path, 'w') as f:
    f.write(new_content)

print(f"Done! New file size: {len(new_content)} chars")
print(f"CSS braces: open={new_content.count('{')}, close={new_content.count('}')}, balanced={new_content.count('{') == new_content.count('}')}")
