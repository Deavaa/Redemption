# Redemption — Multi-Agent Worklog

This file is the shared coordination log for all sub-agents working on the
`/home/z/my-project/Redemption` Laravel project. Every agent MUST read this
file before starting and MUST append their findings at the end using the
standard template.

---
Task ID: 2-d
Agent: Explore (website + CSS/JS audit)
Task: Audit public-facing website views and assets for modernization

Work Log:
- Audited 10 view files (home, about, contact, team, gallery, app/download, welcome, layouts/website, partials/logo, errors/403, errors/419)
- Audited 4 CSS files (website.css, homepage.css, design-tokens.css, modern-components.css)
- Audited 3 JS files (website.js, homepage.js, pwa-install.js) + manifest.json + sw.js
- Audited routes/web.php public section + HomeController + AppController
- Found 38 modernization opportunities and 22 concrete bugs/issues

Stage Summary:

==============================================================================
0. CRITICAL FINDINGS (read first)
==============================================================================

🔴 CRITICAL BUG #1 — `public/css/website.css` IS BROKEN AT LINE 1
   The file starts with `};` (orphaned closing brace) and then declares CSS
   custom properties WITHOUT a `:root {` opener. Line 2 contains literal
   Blade syntax `{{ $settings['secondary_color'] ?? '#D97706' }}` and
   `{{ $primaryR }}, {{ $primaryG }}, {{ $primaryB }}` which is served as
   raw text because Blade does NOT process static files in /public/.
   Impact:
   - The entire `:root` block in website.css is dropped by the browser
     (invalid selector + orphan declarations).
   - EVERY rule that references `rgba({{ $primaryR }}, {{ $primaryG }}, {{ $primaryB }}, …)`
     is also dropped — this includes `.navbar`, `.navbar.scrolled`,
     `.page-hero`, and several others. The navbar background, page-hero
     gradient, and mobile-drawer gradient are ALL unstyled on the live site.
   - The layout's inline `<style>` block (in layouts/website.blade.php)
     DOES define `--primary-color`, `--secondary-color`, `--primary-rgb`
     correctly via Blade, so those tokens still resolve — but every
     website.css rule that re-uses Blade `{{ }}` placeholders is dead.
   Fix: rewrite website.css as a plain static CSS file (no Blade). Either
   hardcode the default color values or rely solely on `var(--primary-color)`
   / `var(--primary-rgb)` set by the layout's inline :root block.

🔴 CRITICAL BUG #2 — TWO CONFLICTING LAYOUTS, NEITHER CONSISTENT
   The project has TWO layout templates for public pages:
   (a) `layouts/website.blade.php`  — used by welcome, about, contact, team,
       gallery. Green + gold theme. Loads Bootstrap 5 + Font Awesome 6.5.1 +
       Montserrat/Playfair fonts + design-tokens.css + website.css (broken).
       NO i18n (all strings hardcoded English). NO language switcher.
   (b) `layouts/app.blade.php`      — used ONLY by `home.blade.php` (the
       emergency fallback homepage). Navy + amber theme. Self-contained
       inline CSS (no Bootstrap). Uses `__('app.*')` translation keys
       throughout. HAS a language switcher dropdown. Modern, polished,
       fully internationalized — but never rolled out to the main pages.
   `home.blade.php` (fallback) uses Bootstrap classes (`.row`, `.col-md-4`,
   `.card`, `.btn-gold`, `.gold-text`, `.hero`, `.stitle`) that are NOT
   defined anywhere — `layouts/app.blade.php` doesn't load Bootstrap, so
   the fallback homepage renders COMPLETELY UNSTYLED.
   Fix: pick ONE layout, consolidate. `layouts/app.blade.php` is the more
   modern, i18n-ready foundation — recommend extending it to all public
   pages and deleting `layouts/website.blade.php` + `website.css`.

🔴 CRITICAL BUG #3 — `app/download.blade.php` IS A THIRD, OFF-BRAND THEME
   The download page is a 906-line standalone HTML file (no @extends) with
   its own complete inline `<style>` block using an INDIGO (#6366f1) +
   dark-navy (#0f172a) palette, Inter font, and its own navbar/footer.
   It does NOT match the green/gold website theme at all. Users clicking
   "Get App" in the website navbar are jarringly transported to a page
   that looks like a different product.
   Also: `<meta name="theme-color" content="#6366f1">` here vs.
   `#047857` in website layout — browsers will show different
   status-bar colors depending on the page.

🔴 CRITICAL BUG #4 — NO i18n ON THE PUBLIC WEBSITE
   `lang/en/app.php` and `lang/am/app.php` already define keys for
   `home`, `about`, `gallery`, `contact`, `team`, `quick_links`,
   `programs`, `contact_us`, `all_rights_reserved`, `footer_about`,
   `login`, `school_name`, `brand_pre`, `brand_name`, etc. — but NONE of
   the public views use `__('app.*')`. Every string is hardcoded English.
   The Amharic locale exists but switching to it changes nothing on the
   public site. (Only `layouts/app.blade.php` uses the translations —
   see Bug #2.) The `lang.switch` route exists and works, but is useless
   for public visitors.

==============================================================================
1. PER-VIEW AUDIT
==============================================================================

──────────────────────────────────────────────────────────────────────────────
1.1  resources/views/welcome.blade.php  (916 lines — PRIMARY HOMEPAGE)
──────────────────────────────────────────────────────────────────────────────
Structure:
  @extends('layouts.website') + @push('styles') homepage.css
  Sections: announcement ticker bar (before-nav), hero carousel slider
  with 3 default Unsplash fallback slides, slider-bottom alerts overlay,
  news splash modal (auto-opens after 2s, sessionStorage-dismissed),
  animated counters section, features/"Why Choose Us" (6 cards from
  settings wcu_1..6), about split-layout, programs horizontal scroll,
  gallery masonry (6 images), video section (YouTube modal), team
  (4 cards), CTA "Visit Us in Person", contact form + info cards.
  @push('scripts') homepage.js

Visual/UI style:
  - Framework: Bootstrap 5.3.3 (CDN) + Font Awesome 6.5.1 + Google Fonts
    (Playfair Display + Montserrat). NO Tailwind.
  - Uses CSS variables from layout's inline :root (--primary-color,
    --secondary-color, --primary-rgb, --text-light, --white, --light-bg).
    Does NOT use design-tokens.css variables (--color-primary, etc.).
  - Palette: green primary (--primary-color from settings, default
    #1B5E20) + gold secondary (#D4A017 from settings) + white/light-bg.
    BUT website.css tries to override --primary-color to #0A0F1E (navy)
    — see Bug #1; the override is dropped because the :root block is
    broken, so green actually wins.
  - Typography: Playfair Display for headings (h1-h6), Montserrat for
    body. Both loaded from Google Fonts.
  - Layout patterns: full-height hero carousel (100vh), glassmorphic
    feature cards with gradient border on hover, horizontal-scroll
    program cards with snap, CSS columns masonry gallery, two-column
    contact form.
  - Mobile responsiveness: yes — multiple @media (max-width: 991px),
    (max-width: 768px), (max-width: 575px) blocks in homepage.css.
    Viewport meta present in layout.

Modernization opportunities:
  - Replace inline `style="font-family:'Montserrat',sans-serif;…"` on
    every form label (lines 834, 838, 842, 846, 856) with a utility
    class `.form-label` styled in CSS.
  - Hero carousel is heavy (3 full-screen images + Bootstrap carousel
    JS). Consider a single static hero with a CSS gradient overlay +
    animated CTAs for faster LCP.
  - News splash modal auto-opens after 2s on EVERY visit (session-only
    dismissal) — intrusive; replace with a dismissible toast or move
    to a dedicated /news page.
  - Add `loading="lazy"` to ALL `<img>` (most have it, but team
    section line 716 has it; verify all). Add `width`/`height`
    attributes to prevent CLS.
  - Add `decoding="async"` to images.
  - Add `aria-label` to carousel controls (currently only has
    `carousel-control-prev-icon` spans with no text).
  - Replace `onclick="openVideoModal('…')"` (line 579, 624, 660) with
    addEventListener in homepage.js — current approach pollutes global
    scope and is CSP-unsafe.
  - Add `rel="noopener noreferrer"` to external YouTube links.
  - Add `<meta name="description">` (layout has it but welcome doesn't
    override; the layout's description is just school_name + tagline).
  - Add JSON-LD structured data (School / EducationalOrganization
    schema) for SEO.
  - The hero's only CTA is "Login" — should be "Apply Now" /
    "Discover More" / "Visit Campus" for a marketing site.
  - Add a sticky CTA bar on mobile ("Apply Now" / "Call Us").
  - Add scroll-spy to highlight active nav section.
  - Add dark-mode toggle (design-tokens.css already has dark-mode
    overrides but no toggle UI exists).

Bugs/issues:
  - Line 153: `$settings['university_acceptance']` is a string like
    "98%"; `preg_replace('/[^0-9]/', '', …)` extracts "98". OK but
    fragile — if setting is empty string, preg_replace returns "" and
    (int)"" === 0, counter shows 0%. Controller provides default "98%".
  - Line 463: `$settings['programs_count']` is NOT in the controller's
    `getWebsiteSettings()` defaults list — view falls back to `?? 4`.
    Should be added to defaults for consistency.
  - Line 422: about image fallback uses external Unsplash URL
    (external dependency; will break offline / if Unsplash blocks).
  - Lines 92, 117, 142, 478, 521-551: external Unsplash image URLs as
    fallbacks — same external-dependency issue.
  - Line 736-796: empty-state team cards use external Unsplash photos
    and fake names ("Dr. Sarah Johnson", "Prof. Michael Chen", etc.)
    — these are placeholder/dummy data that should be removed or
    replaced with a "No team members yet" empty state.
  - Line 811: `<a href="tel:{{ preg_replace('/[^0-9+]/', '', …) }}">`
    — OK, but if `school_phone` is empty, the `tel:` link is broken.
  - Line 442: `<a href="#contact">Discover More</a>` — anchor link,
    relies on homepage.js smooth-scroll. OK.
  - Line 808: `<a href="#contact">Find Our Campus</a>` — same.
  - Inline `<style>` block for news splash (lines 248-279) and inline
    `<script>` (lines 280-301) — should be extracted to a partial or
    homepage.js.
  - Inline `<style>` for ticker (lines 20-29) + inline `<script>`
    (lines 30-40) — should be extracted.
  - The announcement ticker fetches `/api/public/announcements` on
    every page load (homepage.js line 154) — no caching, adds latency.

──────────────────────────────────────────────────────────────────────────────
1.2  resources/views/home.blade.php  (50 lines — FALLBACK HOMEPAGE)
──────────────────────────────────────────────────────────────────────────────
Structure:
  @extends('layouts.app') — the OTHER layout (navy/amber, i18n).
  Sections: hero ("Welcome to School of Redemption" + Discover More btn),
  "Why Choose Us" 3-card grid, "Our Programs" 4-card grid, stats band
  (1500+ / 120+ / 25+ / 98%), final CTA "Ready to Join Our Family?".

Visual/UI style:
  - Framework: expects Bootstrap (uses `.container`, `.row`, `.col-md-4`,
    `.card`, `.btn`, `.fas`) BUT `layouts/app.blade.php` does NOT load
    Bootstrap — only Font Awesome + Google Fonts (Inter + Playfair).
  - Uses custom classes `.hero`, `.btn-gold`, `.gold-text`, `.section`,
    `.stitle`, `.bg-light` — NONE of these are defined in layouts.app's
    inline CSS. `.bg-light` is Bootstrap (not loaded). So the page is
    almost entirely UNSTYLED.
  - Layout patterns: would be hero + card grids + stats band IF Bootstrap
    were loaded.
  - Mobile: viewport meta in layout; no specific @media in this view.

Modernization opportunities:
  - This file is only used as an emergency fallback when welcome.blade.php
    throws. Recommend EITHER:
    (a) Delete it and make the emergency fallback a plain-text page
        (the controller already has `renderEmergencyHomepage` for that).
    (b) Rewrite it to use the same layout + design tokens as welcome.
  - All stats are hardcoded ("1500+", "120+", "25+", "98%") — should
    come from settings.
  - All text is hardcoded English — no `__()` calls (unlike the rest of
    layouts/app which IS internationalized).

Bugs/issues:
  - Extends `layouts.app` which doesn't load Bootstrap → broken layout.
  - Uses `url('about')` and `url('contact')` instead of `route('about')`
    / `route('contact')` — works but not idiomatic.
  - `<i class="fas fa-arrow-right ms-2">` — `ms-2` is a Bootstrap
    spacing utility (not loaded), so no margin.
  - Inline `style="background:linear-gradient(135deg,#1E90FF,#1565C0);
    color:#fff;text-align:center"` (line 39) — hardcoded blue gradient
    that doesn't match the green/gold brand.
  - Inline `style="font-family:'Playfair Display',serif"` (line 47).

──────────────────────────────────────────────────────────────────────────────
1.3  resources/views/about.blade.php  (188 lines)
──────────────────────────────────────────────────────────────────────────────
Structure:
  @extends('layouts.website'). Sections: page-hero with breadcrumb,
  mission & vision split-layout (text + image), about-counters section
  (4 animated stats on green background with radial dot pattern), core
  values 4-card grid. @push('scripts') inline IIFE for counter animation.

Visual/UI style:
  - Framework: Bootstrap 5 (via layout) + Font Awesome + design tokens.
  - Uses CSS vars: --primary-color, --secondary-color, --white,
    --light-bg, --text-light. Does NOT use design-tokens.css vars
    (--color-primary etc.).
  - Palette: green primary + gold secondary (consistent with layout).
  - Typography: Playfair Display for stat numbers, Montserrat for body.
  - Layout: split 2-col (col-lg-6), 4-col stat grid, 4-col card grid.
  - Mobile: relies on layout's @media queries; no view-specific ones.

Modernization opportunities:
  - MASSIVE inline-style pollution: nearly every element has
    `style="padding:5rem 0;background:var(--white);"` etc. (lines 21,
    45, 55, 56, 59, 62, 66-68, 70, 73, 77-79, 81, 84, 87-89, 91, 94,
    103, 112, 119, 126, 133, …). Extract to utility classes
    (`.section-pad`, `.bg-white`, `.bg-light`, `.text-center`).
  - The 4 stat cards are 100% duplicated markup (lines 54-97) — extract
    to a Blade partial or @foreach loop.
  - Counter animation is duplicated between about.blade.php (inline)
    and website.js (`.counter` observer). Consolidate into website.js.
  - Add `loading="lazy"` to about_image (line 34, 36).
  - Add `width`/`height` to prevent CLS.
  - Fallback Unsplash image (line 36) — external dependency.
  - Add `aria-label` to social icons (already done in layout's footer).
  - Breadcrumb uses Bootstrap `.breadcrumb` — OK.
  - Add scroll-triggered reveal (`class="reveal"`) — already has
    `reveal-left`/`reveal-right` on some elements but the observer is
    in website.js which IS loaded. Good.

Bugs/issues:
  - Line 34: `alt="About {{ $settings['school_name'] ?? 'our school' }}"`
    — OK, has alt text.
  - Line 36: Unsplash fallback image — external dependency.
  - Inline `<script>` (lines 145-186) duplicates the counter animation
    logic from website.js — if both run on the same element, double
    animation. Here it targets `.about-counter-item h3` (different
    selector), so no conflict, but it's still code duplication.
  - Hardcoded English: "Our Mission", "A Legacy of Academic Excellence",
    "Our Vision", "Numbers That Speak", "Our Impact", "Students
    Enrolled", "Years of Experience", "Academic Programs", "Success
    Rate", "Core Values", "What We Stand For", "Excellence", "Integrity",
    "Innovation", "Service" — none use `__()`.

──────────────────────────────────────────────────────────────────────────────
1.4  resources/views/contact.blade.php  (278 lines)
──────────────────────────────────────────────────────────────────────────────
Structure:
  @extends('layouts.website'). @push('styles') inline <style> for
  branch-card / branch-map. Sections: page-hero, contact info cards
  (address/phone/email/hours) + social links, contact form (CSRF
  included), campuses/branches map section (Google Maps iframe or GPS
  embed).

Visual/UI style:
  - Framework: Bootstrap 5 + Font Awesome + design tokens.
  - Uses CSS vars: --primary-color, --secondary-color, --white,
    --text-light, --text-muted, --light-bg.
  - Palette: green + gold (consistent).
  - Typography: Montserrat for headings (inline style overrides).
  - Layout: 2-col (col-lg-5 info + col-lg-7 form), responsive branch
    grid (col-lg-4/6/8 based on count).
  - Mobile: inline @media in view-level CSS? No — relies on Bootstrap
    grid + layout media queries.

Modernization opportunities:
  - Extract inline `<style>` (lines 6-100) to a `contact.css` or merge
    into website.css.
  - Inline styles on every form label: `style="font-weight:500;
    font-size:0.9rem;"` (lines 182, 186, 190, 194, 198) — extract to
    `.form-label` class.
  - Inline style on submit button: `style="padding:0.75rem 2rem;"`
    (line 202) — unnecessary; `.btn-hero-primary` already has padding.
  - Add `aria-required="true"` to required fields (currently only
    `required` attribute).
  - Add `autocomplete` attributes (`name`, `email`, `tel`) to inputs.
  - Add `<fieldset>`/`<legend>` around the form for a11y.
  - Add form validation feedback (Bootstrap `.invalid-feedback`).
  - Add a loading state on submit (disable button, show spinner).
  - Branch cards: add `loading="lazy"` to iframe (already has it — good).
  - Add `referrer-policy` to iframe (already has it — good).
  - Replace `@isset($branches)` with `@if(isset($branches) && $branches->count())`.

Bugs/issues:
  - ✅ CSRF is present: `@csrf` on line 179. Good.
  - ✅ Form action uses `route('contact.store')`. Good.
  - Line 230: `<iframe src="{{ $branch->map_embed_url }}">` —
    `map_embed_url` is user-editable admin input; if it contains
    `javascript:` or a malicious URL, this is an XSS vector. Should
    validate/escape. (Blade `{{ }}` escapes, so the src attribute is
    safe from attribute injection, but the URL itself could be a
    phishing page.)
  - Hardcoded English throughout — no `__()`.
  - Line 152: "Mon - Fri: 8:00 AM - 5:00 PM" hardcoded — but
    welcome.blade.php says "7:30 AM - 4:00 PM". Inconsistent hours.
  - Line 264: Google Maps directions link uses `&` unescaped in URL
    (`?api=1&destination=…`) — should be `&amp;` in HTML, but Blade/
    browsers handle it. Minor.
  - Branch card column width logic (line 226) is clever but fragile:
    `$branches->count() === 1 ? '8 offset-lg-2' : (count === 2 ? '6' : '4')`
    — if there are 5+ branches, all get col-lg-4 (3 per row), which
    leaves an uneven last row. Acceptable.

──────────────────────────────────────────────────────────────────────────────
1.5  resources/views/team.blade.php  (249 lines)
──────────────────────────────────────────────────────────────────────────────
Structure:
  @extends('layouts.website'). @push('styles') inline <style> for
  team-card / team-avatar / team-social-overlay. Sections: page-hero,
  team grid (4 cols). @else branch with 4 hardcoded placeholder members
  (Unsplash photos + fake names) when no $teamMembers.

Visual/UI style:
  - Framework: Bootstrap 5 + Font Awesome + design tokens.
  - Palette: green + gold. Avatar has gold border on hover.
  - Layout: 4-col grid (col-lg-3 col-md-6).
  - Mobile: inline `@media (max-width: 575px)` shrinks avatar.

Modernization opportunities:
  - Extract inline `<style>` to website.css or a team partial.
  - The placeholder team members (lines 182-244) use FAKE names
    ("School Principal", "Vice Principal", "Head of Student Affairs",
    "Athletics Director") and Unsplash stock photos — these are
    misleading. Replace with a proper empty state ("No team members
    yet. Check back soon.").
  - Add `loading="lazy"` to avatar images (line 160, 187, 201, 216, 232).
  - Add `width`/`height` to avatars to prevent CLS.
  - `onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"`
    (line 160) — inline JS fallback to show initial letter; works but
    fragile. Better: use `<picture>` or a JS observer in website.js.
  - Add `aria-label` to email/phone social links (currently
    `title="Email"` / `title="Call"` — title attribute is not a good
    a11y mechanism; use `aria-label`).

Bugs/issues:
  - Line 60: `.team-avatar-initial { color: var(--primary); }` — but
    `--primary` is NOT defined anywhere (the layout defines
    `--primary-color`, not `--primary`). So the initial letter has no
    explicit color → inherits. BUG.
  - Hardcoded English: "Our Team", "Meet the dedicated leaders…",
    "Leadership Team", "Meet Our Educators", "Our dedicated team…".
  - Line 161: `substr($member->name, 0, 1)` — if name is empty, returns
    empty string. Edge case.

──────────────────────────────────────────────────────────────────────────────
1.6  resources/views/gallery.blade.php  (518 lines)
──────────────────────────────────────────────────────────────────────────────
Structure:
  @extends('layouts.website'). @push('styles') large inline <style>
  (lines 6-327) for gallery-hero, video-highlights, photo-gallery
  masonry, lightbox, pagination. Sections: compact gallery-hero,
  video highlights (YouTube iframes from VideoLibrary + GalleryVideo),
  photo gallery masonry (4-col CSS columns), lightbox overlay,
  pagination. @push('scripts') inline lightbox JS.

Visual/UI style:
  - Framework: Bootstrap 5 + Font Awesome + design tokens.
  - Uses CSS vars: --primary-color, --primary-rgb, --secondary-color,
    --white, --light-bg, --text-light, --text-dark, --text-muted.
  - Palette: green + gold (consistent).
  - Layout: 4-col masonry (CSS columns), responsive to 3/2/2 cols.
  - Mobile: inline @media (max-width: 1199px/991px/575px).

Modernization opportunities:
  - The gallery-hero (lines 8-63) overrides the standard .page-hero
    with a COMPACT version (font-size: 1rem, padding: 0.8rem) — this is
    a deliberate design choice but looks odd next to other pages' hero.
    Standardize on .page-hero.
  - Extract inline <style> (320+ lines) to a `gallery.css`.
  - Lightbox: add keyboard navigation (left/right arrows), swipe
    gestures on mobile, image counter ("3 of 12"), preloading.
  - Add `loading="lazy"` to gallery images (line 432 has it — good).
  - Add `decoding="async"`.
  - Masonry uses CSS `columns` which causes reading-order issues
    (left-to-right then top-to-bottom). Consider CSS Grid masonry or
    JS-based masonry for better ordering.
  - Video iframes load immediately (no lazy loading) — use `loading=
    "lazy"` or facades (click-to-play thumbnail).
  - Pagination uses Bootstrap `.pagination` with custom styling — OK.

Bugs/issues:
  - Line 11: `rgba(var(--primary-rgb), 0.98)` — depends on
    `--primary-rgb` being defined (it is, in layout's inline :root).
    OK.
  - Line 388: inline `@php` regex to extract YouTube ID from URL —
    duplicated from welcome.blade.php line 654. Extract to a helper
    or Blade component.
  - `openLightbox(this)` is a global function (line 479) — pollutes
    global scope, CSP-unsafe. Move to addEventListener.
  - Line 432: `file_exists(public_path('storage/' . $image->image_path))`
    — disk I/O on every render; cache the result or trust the DB.
  - Hardcoded English: "Our Gallery", "Explore moments…", "Video
    Highlights", "Watch Our Stories", "Photo Gallery", "Campus Life &
    Moments", "No Photos Yet", "Check back soon…", "Map not available".
  - Line 458: `$galleryImages->withQueryString()->links()` — uses
    Laravel pagination. The view `vendor/pagination/default.blade.php`
    exists. OK.

──────────────────────────────────────────────────────────────────────────────
1.7  resources/views/app/download.blade.php  (906 lines)
──────────────────────────────────────────────────────────────────────────────
Structure:
  STANDALONE HTML (no @extends). <head> with PWA manifest + theme-color
  + Bootstrap 5.3.0 + Font Awesome 6.4.0 + Inter font. Inline <style>
  (lines 25-555) — 530 lines of CSS for :root vars, navbar, hero, install
  cards, features grid, FAQ, footer, PWA install banner. Body: app-nav,
  hero (icon + h1 + badges), detection banner, install section (training
  app banner + Android card + iOS card), features grid (9 cards), FAQ
  (5 items), footer, PWA install banner, inline <script> (SW register +
  beforeinstallprompt + platform detection).

Visual/UI style:
  - Framework: Bootstrap 5.3.0 + Font Awesome 6.4.0 (OLDER versions
    than the website's 5.3.3 / 6.5.1 — version drift).
  - Palette: INDIGO (#6366f1) + dark navy (#0f172a) + accent amber
    (#f59e0b) + green (#10b981) + blue (#3b82f6). COMPLETELY DIFFERENT
    from the website's green + gold.
  - Typography: Inter only (no Playfair Display).
  - Layout: centered hero, 2-col install cards, 3-col features grid,
    stacked FAQ.
  - Mobile: `@media (max-width: 640px)` for install grid; otherwise
    relies on Bootstrap.

Modernization opportunities:
  - This page should be REBUILT to extend `layouts.website` (or the
    consolidated layout) so it shares the navbar, footer, and brand.
  - Replace the indigo palette with the website's green + gold.
  - Move the 530-line inline <style> to `public/css/download.css`.
  - Move the inline <script> to `public/js/download.js`.
  - The 9 feature cards (lines 697-742) have inline `style="background:
    #d1fae5;color:#10b981;"` etc. — extract to CSS classes
    (`.feature-icon.green`, `.feature-icon.blue`, …).
  - FAQ uses `onclick="this.parentElement.classList.toggle('open')"`
    (lines 749, 757, 765, 773, 781) — should use `<details>`/`<summary>`
    for native a11y, or addEventListener.
  - Add `aria-expanded` to FAQ buttons.
  - Add structured data (FAQPage schema) for SEO.
  - The "Download APK (8 MB)" label (line 641) is hardcoded — should
    reflect actual file size.
  - The iOS instructions (lines 662-679) are correct but could use
    visual icons/screenshots.
  - Add a QR code for desktop-to-mobile install.

Bugs/issues:
  - Theme-color mismatch: `#6366f1` here vs `#047857` in
    website.blade.php (and `#059669` in layouts/app.blade.php).
    THREE different theme-colors across the app.
  - Bootstrap version drift: 5.3.0 here vs 5.3.3 in website layout.
  - Font Awesome version drift: 6.4.0 here vs 6.5.1 in website layout.
  - Line 18: `<link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">`
    — OK.
  - Line 19: `<link rel="icon" type="image/png" sizes="192x192" href="…">`
    — only one favicon size declared; website layout doesn't declare
    any `<link rel="icon">` (relies on /favicon.ico). Inconsistent.
  - Line 815: `navigator.serviceWorker.register('{{ asset("sw.js") }}',
    { scope: '/' })` — scope '/' may fail if the app is in a
    subdirectory (the SW can only control its own directory + below).
    The dynamic manifest uses `url('/')` for scope, which may or may
    not match.
  - Line 826: `let deferredPrompt = null;` — uses `let` at top level
    (not in a module) → still pollutes global scope.
  - Line 863: `window.location.href = '{{ url("/login") }}';` —
    hardcoded Blade in inline JS; works but fragile.
  - Line 805: `onclick="installPWA()"` and `onclick="dismissBanner()"`
    — global function calls, CSP-unsafe.
  - Line 599: inline `style="background:linear-gradient(135deg,rgba(99,102,241,0.15)…"`
    — heavy inline style.
  - Hardcoded English throughout — no `__()`.
  - Line 81: `AppController::download()` does
    `Setting::pluck('value', 'key')->toArray()` — if settings table
    doesn't exist, throws and 500s. Should be wrapped in try-catch
    like HomeController does.

──────────────────────────────────────────────────────────────────────────────
1.8  resources/views/welcome.blade.php  — covered in §1.1 above.
──────────────────────────────────────────────────────────────────────────────

──────────────────────────────────────────────────────────────────────────────
1.9  resources/views/layouts/website.blade.php  (240 lines)
──────────────────────────────────────────────────────────────────────────────
Structure:
  Full HTML shell. <head>: CSRF meta, viewport, description, title,
  PWA manifest link, theme-color #047857, apple-mobile-web-app metas,
  preconnect to CDNs, Bootstrap 5.3.3, Font Awesome 6.5.1, Google
  Fonts (Playfair Display + Montserrat), inline :root with Blade-
  computed --primary-color/--secondary-color/--primary-rgb, then
  design-tokens.css + website.css + @stack('styles'). <body>: custom
  cursor divs, @yield('before-nav'), fixed-top navbar with brand +
  desktop nav links + mobile-login-pill + hamburger, mobile drawer
  overlay + drawer, @yield('after-nav'), <main>@yield('content'),
  footer (4 cols: brand+social, quick links, programs, newsletter),
  footer-bottom (copyright + privacy/terms links), back-to-top button,
  Bootstrap 5.3.3 bundle JS (deferred), website.js (deferred),
  @stack('scripts').

Visual/UI style:
  - Framework: Bootstrap 5.3.3 + Font Awesome 6.5.1 + custom CSS.
  - Palette: green primary (from settings, default #1B5E20) + gold
    secondary (default #D4A017). Inline :root overrides for these.
  - Typography: Playfair Display for headings, Montserrat for body.
  - Layout: fixed navbar (80px), max-width container, 4-col footer.
  - Mobile: hamburger → right-side drawer; multiple @media in
    website.css (broken) and homepage.css.

Modernization opportunities:
  - Consolidate with layouts/app.blade.php (which has i18n + language
    switcher). Pick ONE layout.
  - Add language switcher dropdown (layouts.app has the pattern).
  - Replace custom cursor (`.cursor-dot` / `.cursor-ring`) — it's a
    gimmick that hurts UX (hides the native cursor, adds JS overhead,
    confusing on forms). Remove or make opt-in.
  - Add a sticky CTA bar on mobile.
  - Add `aria-current="page"` to active nav links (currently uses
    `.active` class only).
  - Add a skip-to-content link for a11y (`<a href="#main"
    class="skip-link">Skip to content</a>`).
  - Add `<meta name="theme-color" media="(prefers-color-scheme:
    light)">` and dark variant.
  - Newsletter form (line 212) is DEAD (`onsubmit="event.preventDefault();"`
    with no backend) — either wire it up or remove it.
  - Replace inline `style="margin-top:8px;background:#6366f1;"` on
    mobile "Get the App" button (line 138) with a CSS class.
  - Preload the hero image / font-display: swap (already using
    display=swap in the Google Fonts URL — good).
  - Add `<link rel="preload" as="image">` for the hero background.
  - Add Open Graph + Twitter Card meta tags for social sharing.
  - Add `<link rel="canonical">`.

Bugs/issues:
  - Line 8: `<html lang="en">` — hardcoded; should be
    `{{ app()->getLocale() }}` (layouts/app.blade.php does this
    correctly).
  - Line 18: `<meta name="theme-color" content="#047857">` — but the
    dynamic manifest from AppController returns `theme_color: #6366f1`.
    MISMATCH between layout theme-color and manifest theme-color.
  - Line 53-54: loads design-tokens.css then website.css. But
    website.css is broken (Bug #1) — its :root block is dropped.
  - Line 65: `<nav class="navbar navbar-dark fixed-top" id="navbar">`
    — Bootstrap navbar-dark, but the custom .navbar background
    (`rgba({{ $primaryR }}, {{ $primaryG }}, {{ $primaryB }}, 0.92)`)
    is in website.css which is broken → navbar may render with NO
    background color (transparent), making text unreadable over hero.
  - Line 69: logo `<img>` has `onerror="this.style.display='none'"`
    — inline JS, CSP-unsafe.
  - Line 138: `style="margin-top:8px;background:#6366f1;"` — hardcoded
    indigo on a green/gold page.
  - Line 158: footer logo also has `onerror="this.style.display='none'"`.
  - Line 212: newsletter form is dead (no action, no backend).
  - Line 221-223: "Privacy Policy" and "Terms of Service" links both
    point to `route('home')` — misleading/broken. Either create those
    pages or remove the links.
  - Line 235: `<script src="…bootstrap.bundle.min.js" defer>` — good
    (deferred). But Bootstrap is loaded as a CDN script (not via Vite)
    — version drift risk.
  - NO `<meta name="csrf-token">` on the website layout? Actually
    line 10 has it: `<meta name="csrf-token" content="{{ csrf_token() }}">`
    — good. But the contact form in welcome.blade.php uses `@csrf`
    which works without the meta. The meta is for AJAX.
  - NO service worker registration in website layout — SW is only
    registered in download.blade.php. So PWA offline support is NOT
    available on the main website pages.
  - NO `pwa-install.js` loaded — that file is dead code (only loaded
    by admin layout).

──────────────────────────────────────────────────────────────────────────────
1.10  resources/views/partials/logo.blade.php  (3 lines)
──────────────────────────────────────────────────────────────────────────────
Structure: A single `@php` block that calls `Setting::getLogoUrl()` and
stores it in `$logoUrl`. Does NOT actually render anything. Appears to
be an unfinished partial — it sets a variable but doesn't output an
`<img>` tag. Probably intended to be `@include('partials.logo')` from
other views, but no view includes it (verified via grep).

Bugs/issues:
  - Dead code — not included anywhere.
  - Doesn't render anything — incomplete.
  - Recommend deletion or completion.

──────────────────────────────────────────────────────────────────────────────
1.11  resources/views/errors/403.blade.php  (102 lines)
──────────────────────────────────────────────────────────────────────────────
Structure: Standalone HTML (no @extends). Inline <style> with flat-UI
red/blue/gray theme. Body: centered card with lock emoji, "403" code,
"Access Denied" title, error message, conditional teacher notice,
"Back to Dashboard" + "Go Back" buttons (auth) or "Go to Login" (guest).

Visual/UI style:
  - Framework: NONE (no Bootstrap, no Font Awesome — uses emoji 🔒 and
    HTML entities).
  - Palette: red #e74c3c, blue #3498db/#2980b9, gray #95a5a6/#7f8c8d,
    background gradient #f5f7fa→#c3cfe2. COMPLETELY OFF-BRAND (flat-UI
    2014 era).
  - Typography: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif.
  - Layout: centered card, max-width 500px.
  - Mobile: viewport meta present; no @media.

Modernization opportunities:
  - Rebuild to extend `layouts.website` (or a minimal error layout)
    so it shares the brand.
  - Use Font Awesome icons (already loaded by layout) instead of emoji.
  - Use design-tokens.css colors.
  - Add a search/sitemap link.
  - Add `aria-live="assertive"` for screen readers.

Bugs/issues:
  - Line 87: `{{ $exception->getMessage() }}` — assumes `$exception`
    is passed. Laravel's default 4xx error pages DO receive
    `$exception`. OK.
  - Line 90: `auth()->user()->role === 'teacher'` — assumes User has
    a `role` property. OK if the model has it.
  - Line 95: `route('admin.dashboard')` — assumes route exists. OK.
  - Line 96: `javascript:history.back()` — CSP-unsafe in some
    configs; better to use a real back link or `<a href="#" onclick="history.back();return false;">`.
  - Hardcoded English: "Access Denied", "Back to Dashboard", "Go
    Back", "Go to Login", "Only home room teachers have this access."
    — no `__()`.

──────────────────────────────────────────────────────────────────────────────
1.12  resources/views/errors/419.blade.php  (90 lines)
──────────────────────────────────────────────────────────────────────────────
Structure: Standalone HTML (no @extends). Inline <style> with
blue-gradient theme. Body: message box with clock emoji ⏰, "Session
Expired" h2, message, "Go to Login Now" link, countdown "Redirecting
in X seconds...". Inline <script> for countdown + redirect.

Visual/UI style:
  - Framework: NONE.
  - Palette: gradient #2c3e50→#3498db (off-brand), accent #f39c12.
  - Typography: Segoe UI, Tahoma, sans-serif.
  - Layout: centered card, max-width 400px.
  - Mobile: viewport meta; no @media.

Modernization opportunities:
  - Same as 403 — rebuild on brand layout.
  - Use design tokens.
  - Add a "Reload page" button (sometimes 419 is transient).

Bugs/issues:
  - Line 72: `var loginUrl = '{{ route("login") }}';` — Blade-in-JS,
    works but fragile (if route contains a quote, breaks). Use a data
    attribute instead.
  - Line 75-78: appends `?redirect=…` to login URL — good for UX.
  - Hardcoded English: "Session Expired", "Your session has expired
    due to inactivity…", "Go to Login Now", "Redirecting in X
    seconds...".

==============================================================================
2. CSS FILE AUDIT
==============================================================================

──────────────────────────────────────────────────────────────────────────────
2.1  public/css/design-tokens.css  (337 lines)
──────────────────────────────────────────────────────────────────────────────
Summary: A well-structured design-system foundation. Defines:
  - Color palette: primary emerald #047857, accent gold #D97706, sidebar
    dark green #0C1F17, body bg #F8FAF9, card bg #FFFFFF, semantic
    colors (success/warning/danger/info), role-based accent variants
    (admin/student/parent/teacher).
  - Typography: Inter font family, sizes xs→4xl, weights light→extrabold,
    line heights, letter spacing.
  - Spacing: 4px-base scale (0 → 16 = 0 → 64px).
  - Border radius: sm 6px → full 9999px.
  - Shadows: sm/md/lg/xl + colored variants.
  - Transitions: fast/normal/slow + presets.
  - Z-index scale (dropdown 1000 → toast 1080).
  - Layout constants (sidebar width, header height, etc.).
  - Focus ring.
  - Role-accent override classes (.role-admin, .role-student, …).
  - Dark-mode overrides ([data-theme="dark"], .dark).
  - Breakpoint documentation (comments only).

Quality: HIGH. Well-commented, consistent naming, no duplicates.
Issues:
  - NOT actually used by the public website views. The website layout
    loads it (`<link rel="stylesheet" href="…/design-tokens.css">`)
    BUT none of the website CSS (website.css, homepage.css, inline
    styles) reference `--color-primary` etc. — they all use the
    layout's inline `--primary-color`/`--secondary-color` tokens
    instead. So design-tokens.css is loaded but IGNORED on the
    website. Wasted HTTP request + missed opportunity.
  - Modernization: refactor website.css + homepage.css to USE these
    tokens. Replace `--primary-color` (layout inline) with
    `--color-primary` (design-tokens). Replace `--secondary-color`
    with `--color-accent`. Etc.

──────────────────────────────────────────────────────────────────────────────
2.2  public/css/website.css  (823 lines) — BROKEN, see Bug #1
──────────────────────────────────────────────────────────────────────────────
Summary: Intended to style the website layout shell (navbar, mobile
drawer, page-hero, section-header, footer, newsletter, back-to-top,
scroll-reveal animations). Contains:
  - :root block (BROKEN — missing `:root {` opener, line 1 is `};`,
    line 2 contains Blade `{{ }}` syntax that's served as raw text).
  - Global reset, body font (Montserrat), heading font (Playfair).
  - Custom cursor (.cursor-dot, .cursor-ring).
  - Navbar (.navbar with broken `rgba({{ $primaryR }}, …)` background,
    .navbar.scrolled, .navbar-brand, .nav-link with hover underline,
    .btn-nav-portal, .mobile-login-pill).
  - Mobile drawer (.mobile-drawer, .mobile-drawer-overlay,
    .mobile-drawer-close, .mobile-nav-links, .mobile-login-btn,
    .hamburger-btn).
  - @media (max-width: 991px) and (max-width: 575px) for navbar.
  - Page hero (.page-hero with broken `rgba({{ $primaryR }}, …)`
    gradient, .page-hero h1/p/breadcrumb).
  - Section header (.section-header, .section-badge).
  - Ornamental divider.
  - Footer (.footer, .footer-brand, .footer-links, .social-links,
    .newsletter-form, .footer-bottom).
  - Back-to-top button.
  - Scroll reveal (.reveal, .reveal-left, .reveal-right, .reveal-scale,
    .revealed).
  - Responsive @media blocks.
  - fadeInUp animation + delay-1..4 classes.

Duplicates (vs homepage.css):
  - .footer, .footer-brand, .footer-links, .social-links,
    .newsletter-form, .footer-bottom — DUPLICATED in homepage.css
    (with slightly different values, e.g. website.css uses
    var(--teal) for social hover, homepage.css uses var(--secondary-color)).
  - .section-header, .section-badge — DUPLICATED.
  - .back-to-top — DUPLICATED (website.css: teal bg; homepage.css:
    gold bg).
  - .reveal, .reveal-left, .reveal-right, .reveal-scale, .revealed —
    DUPLICATED.
  - @keyframes fadeInUp, .animate-fade-up, .delay-1..4 — DUPLICATED.
  - This means on the homepage (which loads BOTH website.css and
    homepage.css), there are TWO conflicting definitions of these
    classes. homepage.css loads second (via @push('styles') after
    website.css in <head>), so homepage.css wins. On OTHER pages
    (about/contact/team/gallery), only website.css loads.

Dead CSS:
  - .ornament-divider (defined but never used in any view).
  - .cursor-dot / .cursor-ring (used by layout but should be removed).
  - Various delay classes if no element uses them.

Bugs:
  - Line 1: `};` — orphaned closing brace. INVALID CSS.
  - Line 2: `{{ $settings['secondary_color'] ?? '#D97706' }}` —
    Blade syntax in a static .css file. Served as raw text. INVALID.
  - Lines 3, 110, 116, 124, 405: `{{ $primaryR }}`, `{{ $primaryG }}`,
    `{{ $primaryB }}` — same Blade-in-static-CSS bug. Causes the
    .navbar background, .navbar.scrolled background, .page-hero
    background, and .mobile-drawer gradient to be DROPPED.
  - Line 4: `--primary-color: #0A0F1E;` — would override the layout's
    green primary with navy, BUT this declaration is inside the
    broken :root block, so it's also dropped. (Lucky accident —
    green wins.)
  - Indentation: every line has 8 leading spaces (was copy-pasted
    from a <style> block). Cosmetic but bloats the file.

──────────────────────────────────────────────────────────────────────────────
2.3  public/css/homepage.css  (1559 lines)
──────────────────────────────────────────────────────────────────────────────
Summary: Styles the welcome.blade.php homepage. Contains:
  - Slider bottom overlay (alerts).
  - Hero slider (full-height carousel, hero-slide, hero-overlay,
    hero-dot-grid, carousel controls, indicators, hero-content,
    hero-badge, hero-text-reveal animation, hero h1/p/buttons/stats).
  - Section dividers (clip-path).
  - Section header + badge (DUPLICATED from website.css).
  - Animated counters section.
  - Features (glassmorphic feature-card with gradient border on hover
    via mask-composite).
  - About split layout (image + badge + text + features list).
  - Programs horizontal scroll (scroll-snap, program-card, program-
    image, program-content, program-tag, program-link, scroll nav).
  - Gallery masonry (3-col CSS columns).
  - Video section (video-card, video-thumb, video-play-btn,
    video-modal-overlay).
  - Team (team-card, team-avatar, team-social-overlay) — DUPLICATED
    in team.blade.php inline <style>.
  - CTA (animated gradient background).
  - Contact (contact-form-wrapper, contact-info-cards).
  - Footer, newsletter, back-to-top, scroll-reveal — ALL DUPLICATED
    from website.css.
  - Responsive @media blocks.
  - fadeInUp animation + delays — DUPLICATED.

Duplicates:
  - ~400 lines are duplicates of website.css (footer, back-to-top,
    reveal, section-header, fadeInUp, etc.).
  - team-card/team-avatar/team-social-overlay are duplicated in
    team.blade.php's inline <style> (~120 lines).

Dead CSS:
  - Possibly .section-divider-bottom (used? welcome has
    section-divider-top only).

Issues:
  - Massive duplication with website.css — should be consolidated.
  - Uses var(--secondary-color) and var(--primary-color) which come
    from the layout's inline :root (NOT from design-tokens.css).
  - All lines have 8 leading spaces (copy-paste artifact).

──────────────────────────────────────────────────────────────────────────────
2.4  public/css/modern-components.css  (1190 lines) — NOT used by website
──────────────────────────────────────────────────────────────────────────────
Summary: A comprehensive component library for ADMIN CRUD views
(.modern-page, .modern-page-header, .modern-breadcrumb, .modern-stats-row,
.modern-stat-card, .modern-card, .modern-badge, .modern-toggle,
.modern-search-box, .modern-table, .modern-btn, .modern-form, .modern-empty,
.modern-tabs, .modern-modal, .modern-toast, etc.). Uses design-tokens.css
variables (--color-primary, --color-card-bg, --radius-lg, --shadow-md, etc.).

Relevance to website audit: NONE. This file is loaded only by
layouts/admin.blade.php and one student view. It is NOT loaded by any
public website view. Mentioned here for completeness only.

Quality: HIGH. Well-organized, uses design tokens properly, good a11y
(focus states, aria where needed). Could serve as a MODEL for the
website redesign — the same token-based approach should be applied to
public-facing CSS.

==============================================================================
3. JS FILE AUDIT
==============================================================================

──────────────────────────────────────────────────────────────────────────────
3.1  public/js/website.js  (304 lines)
──────────────────────────────────────────────────────────────────────────────
Functionality:
  - Navbar scroll shrink + position fix (adjusts top when announcement
    ticker is visible).
  - Hamburger button visibility enforcement on mobile.
  - Scroll reveal IntersectionObserver (.reveal/.reveal-left/etc.).
  - Mobile drawer open/close (hamburger, overlay, close btn, ESC key,
    link click closes).
  - Back-to-top button show/hide + smooth scroll.
  - Custom cursor (desktop only, mousemove + rAF ring follow, hover
    scale on interactive elements).
  - Counter animation IntersectionObserver (.counter[data-target]).
  - Image lazy-loading fallback (only for browsers without native
    `loading="lazy"`).
  - Pull-to-refresh for mobile PWA (creates a #pullToRefreshIndicator
    div, touch handlers).

Quality: Reasonable. Uses IIFEs for scope isolation. Uses `var`
(old-style but consistent). Uses `{ passive: true }` on touch/scroll
listeners (good for perf).

Global namespace pollution:
  - All IIFEs → no global vars leaked. ✅
  - BUT relies on DOM IDs (#navbar, #hamburgerBtn, #mobileDrawer,
    #mobileDrawerOverlay, #mobileDrawerClose, #backToTop, #cursorDot,
    #cursorRing, #announcementTicker). If any ID changes in the
    layout, JS silently bails.

Deprecated APIs: None.

Bugs/issues:
  - Line 252-255: pull-to-refresh indicator is created on EVERY page
    (even desktop) and appended to <body>, even though touch handlers
    bail on `window.innerWidth >= 769`. Wasteful DOM node on desktop.
  - Line 30-34: hamburger button visibility is force-set via inline
    `style.display/visibility/opacity` — overrides CSS. Fragile.
  - No error handling around IntersectionObserver (line 56) — if
    IntersectionObserver is unsupported (very old browsers), the
    `.reveal` elements stay at opacity:0 (invisible). Should add a
    fallback that adds `.revealed` to all.
  - Custom cursor: `mix-blend-mode: difference` on .cursor-dot
    (website.css line 60) — cool effect but can make the cursor
    invisible over certain backgrounds. Also, hiding the native
    cursor is bad UX. Recommend removal.

──────────────────────────────────────────────────────────────────────────────
3.2  public/js/homepage.js  (195 lines)
──────────────────────────────────────────────────────────────────────────────
Functionality:
  - Smooth scroll for anchor links (`a[href^="#"]`) with 80px offset
    + close mobile drawer.
  - Counter animation (DUPLICATED from website.js — both observe
    `.counter[data-target]`).
  - Video modal (openVideoModal global function, close on btn/overlay/
    ESC).
  - Programs horizontal scroll nav (left/right buttons, scrollBy 360px).
  - Hero parallax (scroll listener, backgroundPositionY shift).
  - Announcement ticker (fetch /api/public/announcements, build HTML,
    duplicate content for infinite scroll, set animationDuration based
    on width).

Quality: Mixed. The first block (smooth scroll, lines 1-20) is NOT
wrapped in an IIFE — it runs immediately and pollutes global scope
with the `anchor` variable in a loop. The rest uses IIFEs.

Global namespace pollution:
  - `openVideoModal` (line 62) — global function, called from
    `onclick="openVideoModal('…')"` in welcome.blade.php. CSP-unsafe.
  - Smooth-scroll block (lines 1-20) — no IIFE; `anchor` leaks.

Deprecated APIs: None.

Bugs/issues:
  - Counter animation is DUPLICATED between website.js and homepage.js.
    On the homepage, BOTH run — website.js observer and homepage.js
    observer both observe `.counter`. Whichever fires first wins;
    the second's `unobserve` is a no-op. Wasteful. Consolidate.
  - Line 154: `fetch('/api/public/announcements', …)` — hardcoded
    URL (not via `url()` or route). Breaks if app is in a subdirectory.
    Should be `fetch('{{ route("api.public.announcements") }}', …)`
    or use a data attribute.
  - Line 177: `trackEl.scrollWidth` is read BEFORE the content is
    rendered (right after setting innerHTML). May return 0 in some
    browsers. Should wrap in `requestAnimationFrame`.
  - Line 178: `trackEl.parentElement.offsetWidth` — assumes parent
    has explicit width. OK in flex container.
  - Hero parallax (line 116-127): scroll listener without throttle/
    debounce — can jank on low-end devices. Use rAF.
  - No `DOMContentLoaded` wrapper — script is loaded at bottom of
    page (via @push('scripts')), so DOM is ready. OK.

──────────────────────────────────────────────────────────────────────────────
3.3  public/js/pwa-install.js  (109 lines) — DEAD CODE on website
──────────────────────────────────────────────────────────────────────────────
Functionality:
  - Listens for `beforeinstallprompt`, shows a toast after 5s delay
    (if not dismissed in last 7 days, not in standalone mode).
  - Handles install button click → `deferredPrompt.prompt()`.
  - Tracks `appinstalled` event → success toast.

Relevance to website: NONE. This file is loaded only by
layouts/admin.blade.php. It is NOT loaded by layouts/website.blade.php
or app/download.blade.php (which has its own inline PWA install logic).
DEAD CODE for the public website.

Quality: Clean. IIFE, `'use strict'`, localStorage/sessionStorage for
state, proper event listeners. Could be reused on the website if
wired in.

Issues:
  - Heavy use of inline `style="…"` in the injected toast HTML —
    should use CSS classes.
  - Injects a `<style>` element for `@keyframes slideInUp` on every
    run — should be in a CSS file.

==============================================================================
4. PWA AUDIT (manifest.json + sw.js)
==============================================================================

──────────────────────────────────────────────────────────────────────────────
4.1  public/manifest.json  (83 lines) — STATIC, likely unused
──────────────────────────────────────────────────────────────────────────────
Status: A static manifest.json exists at /public/manifest.json, BUT
the website layout and download page both link to the DYNAMIC manifest
at `/manifest.webmanifest` (route('app.manifest') →
AppController::manifest()). So this static file is likely NOT served
to browsers (it's shadowed by the dynamic route).

Content:
  - name: "Redemption - School Management System"
  - short_name: "Redemption"
  - start_url: "/school-of-redemption/public/admin"  ← HARDCODED
    SUBDIRECTORY PATH. Wrong for production, wrong for any non-
    /school-of-redemption/public/ deployment.
  - scope: "/school-of-redemption/public/"  ← same hardcoded path.
  - theme_color: #047857 (emerald) — matches website layout but NOT
    the dynamic manifest (#6366f1 indigo).
  - background_color: #0C1F17 (dark green) — matches design-tokens
    sidebar color.
  - display: standalone, orientation: portrait-primary.
  - icons: 8 sizes (72, 96, 128, 144, 152, 192, 384, 512), all with
    `/school-of-redemption/public/icons/…` hardcoded paths.
  - shortcuts: Mark Entry, Attendance, Students — all admin URLs.
  - categories: education, productivity.
  - prefer_related_applications: false.

Bugs:
  - All paths hardcoded to `/school-of-redemption/public/…` — only
    correct for one specific XAMPP subdirectory install. Breaks
    everywhere else.
  - start_url points to /admin (admin dashboard) — should be /login
    or / for a public PWA.
  - theme_color #047857 mismatches the dynamic manifest's #6366f1.
  - No `screenshots` array (the dynamic manifest has one).
  - No `id` field (recommended by Chrome).
  - Shortcut URLs are admin-only — a public PWA user wouldn't have
    access.

Recommendation: DELETE this static file. The dynamic
AppController::manifest() is the source of truth and handles paths
correctly via `url('/')` and `asset()`. BUT fix the dynamic manifest's
theme_color to match the website (#047857 emerald, not #6366f1 indigo)
and add `screenshots` for richer install UI.

──────────────────────────────────────────────────────────────────────────────
4.2  public/sw.js  (264 lines) — Service Worker v3 (CACHE_NAME redemption-v4)
──────────────────────────────────────────────────────────────────────────────
Functionality:
  - Install: caches static assets (./, ./login, ./manifest.json).
  - Activate: deletes old caches, claims clients.
  - Fetch handler:
    * Skips non-GET, API/keepalive/horizon/telescope requests.
    * CDN resources (jsdelivr, cdnjs, google fonts, ui-avatars):
      cache-first with background revalidate (stale-while-revalidate).
    * Navigation requests: network-first, fallback to cache, then
      fallback to './login'.
    * Static assets (CSS/JS/images/fonts): cache-first with background
      revalidate.
    * Default: network-first, fallback to cache, then 503.
  - Push notification handler (title, body, icon, badge, vibrate,
    actions: open/dismiss).
  - Notification click handler (focuses existing /admin client or
    opens new window).
  - Background sync handlers (attendance-sync, mark-entry-sync,
    data-sync) — STUB functions (empty bodies).

Caching strategy issues:
  - CACHE_DURATIONS object (lines 13-19) is DEFINED but NEVER USED.
    Dead code. (The actual caching is immediate, no TTL-based
    eviction.)
  - No cache size limit — cache grows unbounded. Should add a
    periodic cleanup.
  - No version-busting for HTML cache — but HTML is network-first, so
    OK.
  - The `fetchAndCache` helper (line 166) catches errors and returns
    `caches.match(request)` — but if the request was never cached,
    this returns `undefined`, which becomes the response → browser
    error. Should return a `new Response('Offline', {status:503})`
    fallback (as the default branch does).
  - Push notification icon: `./icons/icon-192x192.png` — relative
    path, depends on SW scope. OK if scope is '/'.

Bugs:
  - Line 5: `CACHE_NAME = 'redemption-v4'` — version comment says v3,
    cache name says v4. Minor inconsistency.
  - Background sync functions are EMPTY (lines 252-264) — registered
    but do nothing. Dead code or TODO.
  - SW is registered ONLY in app/download.blade.php (line 813-822),
    NOT in layouts/website.blade.php. So the SW is NOT active on the
    main public website (home, about, contact, team, gallery). PWA
    offline support is therefore only available after visiting the
    download page. SHOULD register SW in the website layout.
  - SW scope is `{ scope: '/' }` (download.blade.php line 815) — if
    the app is in a subdirectory, this fails (SW can only control
    its own directory + below). The dynamic manifest uses `url('/')`
    for scope, which is correct. Mismatch.
  - No `message` event handler for `SKIP_WAITING` — the SW calls
    `self.skipWaiting()` on install, which forces immediate activation,
    potentially breaking in-flight requests. Should prompt the user
    to reload instead.

==============================================================================
5. ROUTES AUDIT (public/website section of routes/web.php)
==============================================================================

Public routes (lines 107-146):
  GET  /                          → HomeController@index         → name('home')         → view('welcome')
  GET  /gallery                   → HomeController@gallery       → name('gallery')      → view('gallery')
  GET  /about                     → HomeController@about         → name('about')        → view('about')
  GET  /contact                   → HomeController@contact       → name('contact')      → view('contact')
  GET  /team                      → HomeController@team          → name('team')         → view('team')
  GET  /manifest.webmanifest      → AppController@manifest       → name('app.manifest') → JSON
  GET  /mobile-app                → AppController@download       → name('app.download') → view('app.download')
  GET  /mobile-app/download/apk   → AppController@downloadApk    → name('app.download.apk')
  GET  /mobile-app/download/training-apk → AppController@downloadTrainingApk → name('app.download.training_apk')
  POST contact                    → ContactMessageController@store → name('contact.store')
  GET  lang/{locale}              → LanguageController@switch    → name('lang.switch')
  GET  /login                     → AuthController@showLogin     → name('login')
  POST /login                     → AuthController@login
  POST /logout                    → AuthController@logout        → name('logout')
  GET  /password/forgot|email|reset/{token}  (password reset flows)
  POST /password/*
  POST telegram/webhook          → TelegramController@webhook
  GET  /session-test              → closure (diagnostic JSON)
  GET  /url-test                  → closure (diagnostic JSON)
  GET  storage/{path}             → MediaController@serve (storage fallback)
  GET  /{path}                    → closure (static file fallback for subdirectory hosting)

API route (routes/api.php):
  GET  /api/public/announcements  → CalendarEventController@apiAnnouncements  (used by ticker)

Data passed to views (HomeController):
  - welcome: sliders, sliderAlerts, teamMembers, galleryImages,
    websiteVideos, galleryVideos, settings, latestNews  ✅ all used
  - gallery: galleryImages (paginated), websiteVideos (paginated),
    galleryVideos (collection), settings  ✅ all used
  - about: settings  ✅
  - contact: settings, branches  ✅
  - team: settings, teamMembers  ✅
  - app.download: settings (via Setting::pluck, NOT via getWebsiteSettings) ⚠️

Route/view data issues:
  - ⚠️ AppController::download() does `Setting::pluck('value', 'key')->toArray()`
    — NOT wrapped in try-catch. If the settings table doesn't exist
    (fresh install), this throws and 500s the download page. Should
    use HomeController@getWebsiteSettings() pattern. (Note: the
    download view doesn't actually USE $settings, so this is a latent
    bug only.)
  - ⚠️ welcome.blade.php references $settings['programs_count'] (line 463)
    which is NOT in HomeController's defaults. View falls back to `?? 4`.
    Should be added to defaults.
  - ✅ All other $settings keys used in views are present in defaults.

Missing routes (linked from views but no route):
  - "Privacy Policy" link (website.blade.php line 221) → points to
    route('home') — no /privacy-policy route exists.
  - "Terms of Service" link (line 223) → points to route('home') —
    no /terms route exists.
  - Footer "Programs" links (line 202-205) → point to route('home').#programs
    anchor — works but not ideal.
  - welcome.blade.php line 442: `<a href="#contact">Discover More</a>`
    — anchor to #contact section on the SAME page. OK.
  - welcome.blade.php line 808: `<a href="#contact">Find Our Campus</a>`
    — same. OK.
  - team.blade.php placeholder social links (lines 189, 205, 221, 237,
    738-740, 754-756, 770-772, 786-788): `href="#"` — dead links.
  - layouts/app.blade.php footer social links (lines 575-578):
    `href="#"` — dead links.
  - layouts/app.blade.php footer "Programs", "Calendar", "Results"
    (lines 597, 599, 600): `href="#"` — dead links.
  - welcome.blade.php line 483: `<a href="#" class="program-link">Learn More</a>`
    — dead link.

Untranslated strings: ALL public view strings are hardcoded English.
See Bug #4. The lang/en/app.php and lang/am/app.php files have the
keys ready (home, about, gallery, contact, team, quick_links,
programs, contact_us, all_rights_reserved, footer_about, login,
school_name, brand_pre, brand_name) but views don't call `__()`.

==============================================================================
6. SUMMARY OF MODERNIZATION OPPORTUNITIES (38 total)
==============================================================================

HIGH PRIORITY (blocking):
  1.  Fix website.css line 1 corruption (rewrite as static CSS, no Blade).
  2.  Consolidate layouts/website.blade.php + layouts/app.blade.php into
      ONE layout (recommend extending layouts/app which has i18n).
  3.  Rebuild app/download.blade.php to extend the website layout (share
      navbar/footer/brand).
  4.  Wire i18n into ALL public views (use __('app.*') keys; lang files
      already exist).
  5.  Register service worker in the website layout (not just download page).
  6.  Delete static manifest.json; fix dynamic manifest theme_color to
      #047857 (emerald) to match website.
  7.  Fix `<html lang="en">` → `{{ app()->getLocale() }}` in website layout.
  8.  Fix theme-color mismatches across layouts (#047857 vs #6366f1 vs
      #059669).

MEDIUM PRIORITY (UX/consistency):
  9.  Refactor website.css + homepage.css to use design-tokens.css
      variables (--color-primary, --color-accent) instead of layout
      inline :root tokens.
  10. Remove ~400 lines of duplicate CSS between website.css and
      homepage.css (footer, back-to-top, reveal, section-header,
      fadeInUp).
  11. Extract inline <style> blocks from about/contact/team/gallery/
      welcome/download into dedicated CSS files or merge into website.css.
  12. Extract inline <script> blocks into JS files.
  13. Replace `onclick="..."` inline handlers with addEventListener
      (openVideoModal, openLightbox, installPWA, dismissBanner, FAQ
      toggle).
  14. Remove custom cursor (.cursor-dot/.cursor-ring) — bad UX.
  15. Remove or wire up the dead newsletter form.
  16. Remove or fix "Privacy Policy"/"Terms of Service" dead links.
  17. Replace fake placeholder team members (welcome + team) with a
      proper empty state.
  18. Replace external Unsplash fallback images with local placeholders
      (about, team, welcome hero, gallery).
  19. Consolidate counter animation (duplicated in website.js +
      homepage.js + about.blade.php inline).
  20. Add Open Graph + Twitter Card meta tags.
  21. Add JSON-LD structured data (EducationalOrganization, FAQPage).
  22. Add `<link rel="canonical">`.
  23. Add skip-to-content link for a11y.
  24. Add `aria-current="page"` to active nav links.
  25. Add `aria-label` / `aria-expanded` to interactive elements
      (FAQ, carousel controls, hamburger).
  26. Add `autocomplete` attributes to form inputs.
  27. Add `width`/`height` to images (prevent CLS).
  28. Add `decoding="async"` to images.
  29. Add `loading="lazy"` to all non-hero images + iframes.
  30. Use `<details>`/`<summary>` for FAQ instead of JS toggle.
  31. Add keyboard navigation to lightbox (left/right arrows).
  32. Add dark-mode toggle UI (design-tokens.css already has dark
      overrides).

LOW PRIORITY (polish):
  33. Add a sticky mobile CTA bar ("Apply Now" / "Call Us").
  34. Add scroll-spy to highlight active nav section.
  35. Add a QR code on the download page for desktop-to-mobile install.
  36. Replace hero carousel with a single static hero (faster LCP).
  37. Move news splash modal to a dedicated /news page or make it a
      dismissible toast.
  38. Add cache size limit + TTL eviction to sw.js.

==============================================================================
7. SUMMARY OF BUGS/ISSUES (22 total)
==============================================================================

CRITICAL (5):
  B1. website.css line 1 starts with `};` + contains Blade `{{ }}` syntax
      → :root block dropped + .navbar/.page-hero/.mobile-drawer
      backgrounds dropped. Navbar likely transparent over hero.
  B2. Two conflicting layouts (layouts.website green/gold no-i18n vs
      layouts.app navy/amber i18n). home.blade.php fallback uses
      layouts.app but expects Bootstrap (not loaded) → unstyled.
  B3. app/download.blade.php is a 3rd standalone theme (indigo) —
      jarring brand break.
  B4. No i18n on public website despite lang files existing.
  B5. Service worker NOT registered on website pages (only on
      download page) → PWA offline not available publicly.

HIGH (8):
  B6. Static manifest.json has hardcoded `/school-of-redemption/public/`
      paths (wrong for production). Dynamic manifest shadows it but
      has wrong theme_color (#6366f1 vs #047857).
  B7. `<html lang="en">` hardcoded in website layout (should use locale).
  B8. theme-color mismatch: #047857 (website) vs #6366f1 (download)
      vs #059669 (layouts.app).
  B9. team.blade.php line 60: `var(--primary)` is undefined (layout
      defines `--primary-color`, not `--primary`).
  B10. AppController::download() calls Setting::pluck() without
       try-catch → 500 if settings table missing.
  B11. welcome.blade.php uses $settings['programs_count'] which is
       not in HomeController defaults (falls back to 4).
  B12. Newsletter form (website layout line 212) is dead —
       `onsubmit="event.preventDefault();"` with no backend.
  B13. "Privacy Policy" + "Terms of Service" links point to
       route('home') — misleading.

MEDIUM (6):
  B14. homepage.js fetch('/api/public/announcements') uses hardcoded
       URL — breaks in subdirectory deployments.
  B15. Counter animation duplicated in website.js + homepage.js +
       about.blade.php inline — both observers fire on homepage.
  B16. openVideoModal / openLightbox / installPWA / dismissBanner are
       global functions (CSP-unsafe onclick handlers).
  B17. sw.js CACHE_DURATIONS object defined but never used (dead code).
  B18. sw.js background-sync handlers are empty stubs.
  B19. pwa-install.js is dead code on the website (only loaded by admin).

LOW (3):
  B20. Multiple external Unsplash image dependencies (about, team,
       welcome hero/gallery fallbacks).
  B21. Fake placeholder team member data (welcome + team views).
  B22. contact.blade.php "Office Hours" says "8:00 AM - 5:00 PM" but
       welcome.blade.php says "7:30 AM - 4:00 PM" — inconsistent.

==============================================================================
8. RECOMMENDED REDESIGN APPROACH (blueprint for next agent)
==============================================================================

Given the user's goal ("modernize the website part use modern template"),
the recommended approach is:

1. Adopt `layouts/app.blade.php` as the SINGLE public layout.
   - It already has: i18n (`__()` calls), language switcher, modern
     navy+amber palette, sticky navbar, 4-col footer, Inter font.
   - Add: design-tokens.css link, Bootstrap 5 (or refactor to utility-
     first CSS), Font Awesome 6.5.1, @stack('styles')/@stack('scripts'),
     service worker registration, PWA manifest link, theme-color meta.

2. Rewrite all public views to extend `layouts.app`:
   - welcome.blade.php → keep the rich sections but use design-tokens
     classes (--color-primary, --color-accent, --radius-xl, --shadow-lg).
   - about/contact/team/gallery → simple page-hero + content sections.
   - app/download.blade.php → extend layout, keep the install steps
     but use brand colors.
   - errors/403.blade.php, 419.blade.php → minimal branded error pages.

3. Consolidate CSS:
   - Delete website.css (broken) and homepage.css.
   - Create a new `public/css/website.css` that uses design-tokens.css
     variables exclusively (no Blade `{{ }}`, no inline :root overrides).
   - Move page-specific styles (gallery lightbox, team avatar overlay,
     contact branch cards) into the new website.css or per-page CSS
     files that are @push'd.

4. Consolidate JS:
   - Keep website.js (navbar, drawer, reveal, back-to-top, counters).
   - Merge homepage.js (smooth scroll, video modal, programs scroll,
     ticker) into website.js OR keep as a separate page-specific file.
   - Remove custom cursor code.
   - Wrap ALL code in IIFEs/modules; remove global function leaks.
   - Add SW registration to the layout (not per-page).

5. Wire i18n:
   - Replace ALL hardcoded English in views with `__('app.*')` calls.
   - Add missing keys to lang/en/app.php and lang/am/app.php (e.g.,
     'why_choose_us', 'our_programs', 'send_message', 'get_in_touch',
     'our_campuses', 'find_us', 'get_directions', 'video_highlights',
     'photo_gallery', 'no_photos_yet', 'session_expired', 'access_denied',
     'download_app', 'install', 'frequently_asked_questions', etc.).
   - Add a language switcher to the website navbar (copy from
     layouts/app.blade.php).

6. Fix PWA:
   - Delete static manifest.json.
   - Fix dynamic manifest theme_color to #047857 (emerald).
   - Register SW in the consolidated layout.
   - Add pwa-install.js to the layout (reuse the admin's PWA install
     toast).

7. Fix accessibility:
   - Add skip-to-content link.
   - Add aria-current/aria-label/aria-expanded where needed.
   - Ensure focus-visible styles on all interactive elements.
   - Use semantic HTML (`<details>` for FAQ, `<dialog>` for modals).

8. Performance:
   - Lazy-load all non-hero images.
   - Add width/height to images.
   - Preload hero image + critical fonts.
   - Consider replacing Bootstrap with a lighter utility CSS (Tailwind
     or custom) to reduce CSS payload.

This audit provides the complete blueprint. The next agent (Build/Modify)
can proceed to implement the redesign using this report as the spec.

---
Task ID: 3
Agent: Super Z (main, code-audit + fixes)
Task: Review/audit, fix bugs, modernize views, and commit & push changes for the Redemption Laravel project.

Work Log:
- Performed a thorough static audit of routes/web.php, app/Http/Middleware, ~107 controllers, 88 models, key Blade views, public/ assets, and config/ files.
- Identified 33 concrete issues across critical / high / medium / low priorities.
- Applied the following fixes in batches:

  Batch 1 — AuthController critical fixes:
    - Stopped logging the login identifier (PII) and CSRF token to laravel.log.
    - Fixed double-hash bug in submitResetPassword() and resetPasswordWithToken() — User model casts password as 'hashed', so Hash::make() caused login failures after a password reset.
    - Raised password minimum from 4 chars to Password::min(8)->mixedCase()->numbers()->symbols() for both self-service and token-based resets.
    - Imported Illuminate\Validation\Rules\Password as PasswordRule to avoid collision with the Password facade.

  Batch 2 — Admin/ProfileController fixes:
    - Raised min password to 8 chars with mixed-case + numbers + symbols.
    - Removed double-hash bug in updatePassword().
    - Removed the practice of returning the default password in plaintext via the flash message in resetUserPassword().

  Batch 3 — Route hardening (routes/web.php):
    - Deleted public /session-test and /url-test diagnostic endpoints (leaked server vars, session IDs, DB row counts).
    - Added throttle:5,1 to POST /login, /password/forgot, /password/verify-security, /password/reset, /password/reset-with-token.
    - Added throttle:3,1 to POST /password/email (reset-link sender — tighter to prevent email bombing).
    - Added throttle:10,1 to POST /contact (public form spam risk).
    - Added permission middleware to: students.comments.*, parents.search/add/link-student, students/generate-ids, mark-entries/api/save (CRITICAL — was writable marks endpoint with no permission check), media/upload.

  Batch 4 — CSRF cleanup (bootstrap/app.php):
    - Removed 'admin/session-diagnostic' from CSRF exclusions (it exposes session metadata).
    - Kept 'telegram/webhook' and 'admin/keepalive' exclusions only.

  Batch 5 — Disabled deploy/artisan-runner.php:
    - Replaced with a 404 stub. Previous version had hardcoded secret 'CHANGE_THIS_SECRET_12345' and allowed migrate:fresh / key:generate / db:seed via URL — full DB wipe or encryption-key invalidation risk if the secret was not changed (which it usually isn't).

  Batch 6 — Model $hidden for sensitive fields:
    - User: added security_question + security_answer to $hidden.
    - BankIntegration: added $hidden for api_key, api_secret, account_number, merchant_id (was completely missing).
    - EmailInboxSetting: added $hidden for imap_password, imap_username.
    - AssessmentQuestion: added $hidden for seb_config_key, seb_quit_password, seb_exam_keys.

  Batch 7 — XSS fixes in Blade:
    - welcome.blade.php: changed {!! $slider->subtitle !!} to {{ $slider->subtitle }} — admin-editable slider content was rendering as raw HTML on the public homepage.
    - admin/email-inbox/show.blade.php: replaced {!! $email_message->body_html !!} with a sandboxed iframe (srcdoc + sandbox="" with no allow-scripts) — email body is external untrusted content; embedded scripts could otherwise execute in the admin session.

  Batch 8 — Login view modernization (resources/views/auth/login.blade.php):
    - Extracted 241 lines of inline <style> into a new dedicated stylesheet at public/css/auth.css. Added prefers-reduced-motion media query and small-screen tweaks.
    - Removed the public hint that revealed the default student password was '123456' — anyone could log in as any student who hadn't changed their password.
    - Added proper <label for="..."> associations and aria-describedby for password-requirement help text.
    - Added autocomplete="username|current-password|new-password" attributes to all forms.
    - Added role="alert" / role="status" to flash messages.
    - Added a skip-to-login link for screen-reader / keyboard users.
    - Replaced inline-style mobile-app link with a reusable .app-download-link class.
    - Replaced {!! asset('sw.js') !!} interpolation in inline JS with @json() directive.
    - Replaced all hardcoded English strings ("Check Your Email", "Set New Password", "Verify your identity", etc.) with __('app.*') translation keys.
    - Added 30+ new translation keys to lang/en/app.php and lang/am/app.php (Amharic).

  Batch 9 — MediaController security hardening:
    - serve(): replaced weak str_replace(['../', '..\\'], '', $path) with realpath() containment check — single-pass substitution was bypassable (e.g. "....//" collapses back to "../").
    - upload(): added explicit rejection of ".."/"." path segments, normalized consecutive slashes, restricted allowed MIME types to a safe image allow-list, and stopped trusting the client-supplied file extension (now derived from the actual MIME type).

  Batch 10 — Mass-assignment defense-in-depth (AcademicYearController):
    - Replaced $request->all() with a private validateAcademicYear() helper that uses $request->validate() with explicit rules. The model's $fillable was already restrictive, but explicit validation is defense-in-depth.

  Batch 11 — Misc hygiene:
    - public/robots.txt: replaced empty Disallow with explicit Disallow for /admin, /student, /parent, /login, /password, /storage, etc., and Allow for public pages.
    - public/.user.ini: changed session.use_strict_mode from 0 to 1 (mitigates session fixation).
    - Deleted public/manifest.json (was superseded by AppController::manifest() route at /manifest.webmanifest; the static file had hardcoded wrong paths like /school-of-redemption/public/admin).
    - Deleted app/Http/Middleware/VerifyCsrfToken.php (dead code — bootstrap/app.php already handles CSRF exclusions via validateCsrfTokens()).
    - Updated public/sw.js to reference ./manifest.webmanifest instead of the deleted ./manifest.json.

Stage Summary:
- 33 audit findings addressed across 12 batches.
- Files modified: app/Http/Controllers/AuthController.php, app/Http/Controllers/Admin/ProfileController.php, app/Http/Controllers/AcademicYear/AcademicYearController.php, app/Http/Controllers/Media/MediaController.php, app/Models/User.php, app/Models/BankIntegration.php, app/Models/EmailInboxSetting.php, app/Models/AssessmentQuestion.php, bootstrap/app.php, routes/web.php, deploy/artisan-runner.php, public/robots.txt, public/.user.ini, public/sw.js, resources/views/auth/login.blade.php, resources/views/welcome.blade.php, resources/views/admin/email-inbox/show.blade.php, lang/en/app.php, lang/am/app.php.
- Files created: public/css/auth.css.
- Files deleted: public/manifest.json, app/Http/Middleware/VerifyCsrfToken.php.
- Not yet done (intentionally — out of scope for this round): Laravel Policies for object-level auth, splitting the 2425-line layouts/admin.blade.php into partials, removing AppServiceProvider auto-APP_KEY generation, adding global HSTS/CSP middleware, refactoring PermissionMiddleware's 478-line hardcoded arrays into config.
- Next step: commit and attempt push to origin/main (may require GitHub PAT).

---
Task ID: 9
Agent: main (Super Z)
Task: Fix mark entry "All Students" view not saving — user reported marks not saving in bulk view.

Work Log:
- Reviewed the bulk-save code path: lvSaveAll() → fetch POST /admin/mark-entries/api/bulk-save
- Inspected routes/web.php line 210-213: routes correctly defined OUTSIDE admin middleware group, named admin.mark-entries.api.bulk-save
- Inspected MarkEntryController::apiBulkSave() — manual validation, returns JSON, looks correct
- Inspected lvSaveAll() JS function in mark-entries/index.blade.php — logic looked correct
- Traced why server might return 422 (which would cause silent failure)
- FOUND ROOT CAUSE: line 2426 — `window.me_getCSRF = getGlobalCSRFToken;`
  - Function `getGlobalCSRFToken` is NEVER DEFINED in the file
  - Actual function is `getCSRF()` defined at line 838
  - This threw `ReferenceError: getGlobalCSRFToken is not defined` inside the IIFE
  - The IIFE then exited early, so ALL subsequent `window.me_*` assignments on
    lines 2427-2433 NEVER RAN:
      * window.me_updateCSRF = undefined
      * window.me_filterSubject = undefined → bulk save POSTed with empty subject_id
      * window.me_filterTerm = undefined → bulk save POSTed with empty term_id
      * window.me_filterClass = undefined
      * window.me_filterSection = undefined
      * window.me_bulkSaveUrl = undefined (fell back to /admin/mark-entries/api/bulk-save which is correct on Laravel but wrong on XAMPP subdirectory)
      * window.me_setGlobalSaveStatus = undefined → no status badge update on save error
  - Server returned 422 validation error (subject_id + term_id required)
  - JS catch() tried to call window.me_setGlobalSaveStatus (undefined) → silently swallowed
- Fix applied:
  - Changed `getGlobalCSRFToken` → `getCSRF` (the actual function name)
  - Added pre-flight check: if subject_id or term_id empty, show "Filter subject/term missing" status instead of failing silently
  - Added per-HTTP-status error messages (422/404/5xx) inside the response handler
  - Added console.error logging for debugging future issues
  - Added local me_students marks update on save success so the table reflects saved state
  - Modified catch() so it doesn't overwrite more specific error messages already set in the response handler
- Committed and pushed to GitHub as commit 9c3e293

Stage Summary:
- ROOT CAUSE: a typo — `getGlobalCSRFToken` (referenced) vs `getCSRF` (defined)
- This was a silent killer: it threw inside an IIFE so the rest of the IIFE didn't run,
  but the IIFE didn't crash the page (because the IIFE call itself is a single statement).
- The user saw: change marks in All Students view → nothing happens, no status update,
  no error message — because all the status-setter globals were undefined.
- Fix is single-character in spirit (rename function), but also added defensive logging
  and pre-flight validation so similar issues will be visible in the future.

---
Task ID: 10
Agent: main (Super Z)
Task: Fix bulk save for All Students view — was not saving AND status messages inconsistent with per-student save. User explicitly asked to use the SAME status messaging text as per-student save.

Work Log:
- Compared the per-student saveMark() (line 2180) with the bulk lvSaveAll() (line 2538)
- FOUND ROOT CAUSE #1 (save not working): bulk save was NOT sending `academic_year_id`
  - Per-student save appends `formData.append('academic_year_id', ayId)` at line 2199
  - Bulk save did NOT have this line — only sent subject_id, term_id, class_id, section_id
  - The controller apiBulkSave() at line 841 reads `$ayId = $input['academic_year_id'] ?? null`
  - Then at line 881: `if ($ayId) { $existingQuery->where('academic_year_id', $ayId); } else { $existingQuery->whereNull('academic_year_id'); }`
  - With ayId missing, controller filtered `whereNull('academic_year_id')` — didn't find
    existing records that per-student save had stored WITH an academic_year_id
  - Result: either insert failed (unique key violation) OR created orphan records
    without ayId → "save didn't work" from user perspective
- FOUND ROOT CAUSE #2 (inconsistent status): bulk save used various messages
  - Per-student save uses: 'Saving...', 'Saved ✓', 'Not Saved' (capital S)
  - Bulk save was using: 'Not saved' (lowercase), 'Network error',
    'Session expired', 'Validation failed', 'Route 404', 'Server error NNN', etc.
  - User explicitly asked to use the SAME text as per-student save
- Fixes applied (all in resources/views/admin/mark-entries/index.blade.php):
  1. Added `window.me_filterAy = filterAy;` to the IIFE globals (line 2428)
     — previously filterAy was NOT exposed, only filterSubject/Term/Class/Section
  2. Added `formData.append('academic_year_id', ayId);` to bulk save FormData
  3. Pre-flight check now also validates class_id and section_id (was only checking
     subject_id and term_id)
  4. All error paths now use 'Not Saved' (capital S) to match per-student save exactly:
     - Pre-flight failure: 'Not Saved'
     - HTTP 419/401/403: 'Not Saved'
     - HTTP 4xx/5xx: 'Not Saved'
     - Redirect-to-login: 'Not Saved'
     - success=false response: 'Not Saved'
     - Network error (catch): 'Not Saved'
  5. Console logging preserved for debugging — visible to developer but not to user
  6. 'Saving...' and 'Saved ✓' texts were already identical, no change needed
- Committed and pushed to GitHub as commit c7c8a7e

Stage Summary:
- ROOT CAUSE: missing `academic_year_id` in bulk save FormData
- This was missed in the previous fix (commit 9c3e293) which only fixed the
  `getGlobalCSRFToken` → `getCSRF` rename
- After both fixes (9c3e293 + c7c8a7e), bulk save should now:
  1. Actually persist marks to the database
  2. Show 'Saving...' → 'Saved ✓' on success
  3. Show 'Not Saved' on any failure (matching per-student save exactly)
- Filter values (ay, term, class, section, subject) are now ALL pre-flight validated
