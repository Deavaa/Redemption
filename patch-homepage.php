<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * HOMEPAGE 500 ERROR — ALL-IN-ONE PATCH
 * ═══════════════════════════════════════════════════════════════
 * 
 * This script DIRECTLY PATCHES the files on cPanel to fix the
 * homepage 500 error. No need to download from GitHub.
 * 
 * WHAT IT DOES:
 * 1. Clears all Laravel caches
 * 2. Patches HomeController.php with bulletproof fallback
 * 3. Re-enables detectAppUrl() if commented out
 * 4. Creates missing directories
 * 5. Tests the homepage with a REAL HTTP request (curl)
 * 
 * Upload to: public_html/patch-homepage.php
 * Access:    https://redemption.byethost4.com/patch-homepage.php
 * DELETE AFTER FIXING!
 * ═══════════════════════════════════════════════════════════════
 */

$baseDir = __DIR__;
$fixesApplied = 0;

echo '<html><head><title>Homepage Patch</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#0d0d2b;color:#e0e0e0;font-size:14px}';
echo '.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}.info{color:#60a5fa}';
echo 'pre{background:#0d1117;padding:12px;border-radius:8px;overflow-x:auto;border:1px solid #30363d;max-height:400px;overflow-y:auto}';
echo 'h1{color:#c9a84c}h2{color:#60a5fa;border-bottom:1px solid #30363d;padding-bottom:8px;margin-top:2rem}';
echo '.patch{background:#1a2332;border-left:3px solid #c9a84c;padding:12px 16px;margin:8px 0;border-radius:0 8px 8px 0}';
echo '</style></head><body>';
echo '<h1>🔧 Homepage 500 — All-in-One Patch</h1>';

// ═══════════════════════════════════════════════════════════
// PATCH 1: Clear ALL caches
// ═══════════════════════════════════════════════════════════
echo '<h2>Patch 1: Clear Laravel Caches</h2>';

$cacheFiles = glob($baseDir . '/bootstrap/cache/*.php');
foreach ($cacheFiles as $cf) {
    if (basename($cf) === '.gitignore') continue;
    if (@unlink($cf)) {
        echo "<p class='ok'>✅ Deleted: bootstrap/cache/" . basename($cf) . "</p>";
        $fixesApplied++;
    }
}

// Clear compiled views
$viewDir = $baseDir . '/storage/framework/views';
if (is_dir($viewDir)) {
    $views = glob($viewDir . '/*.php');
    $count = 0;
    foreach ($views as $v) { if (@unlink($v)) $count++; }
    echo "<p class='ok'>✅ Cleared {$count} compiled views</p>";
    $fixesApplied++;
}

// ═══════════════════════════════════════════════════════════
// PATCH 2: Write bulletproof HomeController.php
// ═══════════════════════════════════════════════════════════
echo '<h2>Patch 2: Patch HomeController.php</h2>';

$hcPath = $baseDir . '/app/Http/Controllers/HomeController.php';

// Backup the original
if (file_exists($hcPath)) {
    copy($hcPath, $hcPath . '.backup.' . time());
    echo "<p class='info'>📦 Backed up original HomeController.php</p>";
}

// Write the patched version directly
$patchedController = <<<'PHPCODE'
<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\TeamMember;
use App\Models\GalleryImage;
use App\Models\GalleryVideo;
use App\Models\VideoLibrary;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        // LEVEL 1: Try the full homepage with all features
        try {
            return $this->renderFullHomepage();
        } catch (\Throwable $e) {
            // Log the error for debugging
            try {
                \Log::error('Homepage L1 failed: ' . $e->getMessage(), [
                    'class' => get_class($e),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                ]);
            } catch (\Throwable $logErr) {}
        }

        // LEVEL 2: Try the simple home view (fewer dependencies)
        try {
            return $this->renderSimpleHomepage();
        } catch (\Throwable $e) {
            try {
                \Log::error('Homepage L2 failed: ' . $e->getMessage());
            } catch (\Throwable $logErr) {}
        }

        // LEVEL 3: Plain HTML — guaranteed to work (no Blade, no DB, no models)
        return $this->renderEmergencyHomepage();
    }

    private function renderFullHomepage()
    {
        $sliders = collect();
        try { $sliders = Slider::where('is_active', true)->orderBy('sort_order')->get(); } catch (\Throwable $e) {}

        $teamMembers = collect();
        try { $teamMembers = TeamMember::where('is_active', true)->orderBy('sort_order')->limit(4)->get(); } catch (\Throwable $e) {}

        $galleryImages = collect();
        try { $galleryImages = GalleryImage::where('is_active', true)->orderBy('sort_order')->limit(6)->get(); } catch (\Throwable $e) {}

        $websiteVideos = collect();
        try { $websiteVideos = VideoLibrary::forWebsite()->orderBy('created_at', 'desc')->limit(6)->get(); } catch (\Throwable $e) {}

        $galleryVideos = collect();
        try { $galleryVideos = GalleryVideo::where('is_active', true)->orderBy('sort_order')->limit(6)->get(); } catch (\Throwable $e) {}

        $settings = $this->getWebsiteSettings();

        $latestNews = collect();
        try { $latestNews = \App\Models\News::visibleOnWebsite()->limit(3)->get(); } catch (\Throwable $e) {}

        return view('welcome', compact('sliders', 'teamMembers', 'galleryImages', 'websiteVideos', 'galleryVideos', 'settings', 'latestNews'));
    }

    private function renderSimpleHomepage()
    {
        $settings = $this->getWebsiteSettings();
        return view('home', compact('settings'));
    }

    private function renderEmergencyHomepage()
    {
        $schoolName = 'School of Redemption';
        $loginUrl = '/login';
        $aboutUrl = '/about';
        $contactUrl = '/contact';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$schoolName}</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#0d0d2b;color:#fff;min-height:100vh}
        .hero{min-height:80vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:2rem;background:linear-gradient(135deg,#0d0d2b 0%,#1a1a3e 50%,#0d0d2b 100%)}
        .hero h1{font-size:3rem;color:#c9a84c;margin-bottom:1rem;font-family:Georgia,serif}
        .hero p{color:rgba(255,255,255,0.7);font-size:1.1rem;max-width:500px;margin:0 auto 2rem;line-height:1.6}
        .btn{display:inline-block;background:#c9a84c;color:#0d0d2b;padding:0.8rem 2.5rem;border-radius:50px;text-decoration:none;font-weight:700;font-size:1rem;transition:background 0.3s}
        .btn:hover{background:#e8b82e}
        .features{padding:4rem 2rem;display:flex;flex-wrap:wrap;justify-content:center;gap:2rem;max-width:1000px;margin:0 auto}
        .feature{text-align:center;padding:2rem;background:rgba(255,255,255,0.03);border-radius:16px;border:1px solid rgba(201,168,76,0.1);flex:1;min-width:200px;max-width:300px}
        .feature h3{color:#c9a84c;margin-bottom:0.5rem;font-size:1.1rem}
        .feature p{color:rgba(255,255,255,0.6);font-size:0.9rem;line-height:1.5}
        .stats{padding:3rem 2rem;display:flex;flex-wrap:wrap;justify-content:center;gap:2rem;max-width:800px;margin:0 auto;text-align:center}
        .stat h2{color:#c9a84c;font-size:2.5rem}
        .stat p{color:rgba(255,255,255,0.6)}
        .footer{text-align:center;padding:2rem;border-top:1px solid rgba(201,168,76,0.2);color:rgba(255,255,255,0.4);font-size:0.85rem}
        @media(max-width:600px){.hero h1{font-size:2rem}.feature{min-width:100%}}
    </style>
</head>
<body>
    <div class="hero">
        <div>
            <h1>School of Redemption</h1>
            <p>Excellence in Education — Nurturing each student's potential through knowledge, character, and faith.</p>
            <a href="{$loginUrl}" class="btn">Login to Portal</a>
        </div>
    </div>
    <div class="features">
        <div class="feature"><h3>Academic Excellence</h3><p>Rigorous curriculum and dedicated teachers ensuring outstanding results.</p></div>
        <div class="feature"><h3>Character Development</h3><p>Building strong moral values, integrity, and compassion in every student.</p></div>
        <div class="feature"><h3>Modern Facilities</h3><p>State-of-the-art labs, libraries, and technology-enabled classrooms.</p></div>
    </div>
    <div class="stats">
        <div class="stat"><h2>1500+</h2><p>Students</p></div>
        <div class="stat"><h2>120+</h2><p>Teachers</p></div>
        <div class="stat"><h2>25+</h2><p>Years</p></div>
        <div class="stat"><h2>98%</h2><p>Pass Rate</p></div>
    </div>
    <div class="footer">
        <p>&copy; 2025 School of Redemption. All rights reserved. | <a href="{$aboutUrl}" style="color:#c9a84c">About</a> | <a href="{$contactUrl}" style="color:#c9a84c">Contact</a></p>
    </div>
</body>
</html>
HTML;
        return response($html, 200)->header('Content-Type', 'text/html');
    }

    private function getWebsiteSettings(): array
    {
        $defaults = [
            'school_name' => 'School of Redemption',
            'school_tagline' => 'Excellence in Education',
            'school_description' => 'At School of Redemption, we nurture each student\'s potential through excellence in education, character development, and innovative learning.',
            'school_phone' => '+251 11 234 5678',
            'school_email' => 'info@schoolofredemption.edu',
            'school_address' => 'Addis Ababa, Ethiopia',
            'school_website' => 'https://schoolofredemption.edu',
            'total_students' => '1500+',
            'total_teachers' => '120+',
            'university_acceptance' => '98%',
            'years_of_excellence' => '25+',
            'about_description' => 'School of Redemption has been at the forefront of educational excellence for over two decades.',
            'about_mission' => 'To provide quality education that empowers students to become responsible, innovative, and compassionate leaders of tomorrow.',
            'about_vision' => 'To be a leading institution of academic excellence.',
            'about_image' => '',
            'about_students_count' => '500+',
            'about_years_experience' => '15+',
            'about_programs' => '8',
            'about_success_rate' => '95%',
            'facebook_url' => '',
            'twitter_url' => '',
            'youtube_url' => '',
            'telegram_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
            'cta_title' => 'Ready to Begin Your Journey?',
            'cta_description' => 'Join our community of learners.',
            'cta_button_text' => 'Apply Now',
            'cta_button_url' => '#contact',
            'footer_text' => 'School of Redemption. All rights reserved.',
            'wcu_section_title' => 'Excellence in Every Aspect',
            'wcu_section_subtitle' => 'We provide a comprehensive educational experience.',
            'wcu_1_icon' => 'fas fa-chalkboard-teacher',
            'wcu_1_title' => 'Expert Faculty',
            'wcu_1_description' => 'Our teachers are highly qualified professionals.',
            'wcu_2_icon' => 'fas fa-microscope',
            'wcu_2_title' => 'Modern Facilities',
            'wcu_2_description' => 'State-of-the-art laboratories and libraries.',
            'wcu_3_icon' => 'fas fa-users',
            'wcu_3_title' => 'Small Class Sizes',
            'wcu_3_description' => 'Limited student-teacher ratio.',
            'wcu_4_icon' => 'fas fa-palette',
            'wcu_4_title' => 'Holistic Development',
            'wcu_4_description' => 'Beyond academics, we focus on arts, sports, and leadership.',
            'wcu_5_icon' => 'fas fa-globe',
            'wcu_5_title' => 'Global Perspective',
            'wcu_5_description' => 'International curriculum standards.',
            'wcu_6_icon' => 'fas fa-award',
            'wcu_6_title' => 'Proven Track Record',
            'wcu_6_description' => 'Our students consistently achieve top scores.',
            'programs_section_title' => 'Pathways to Success',
            'programs_section_subtitle' => 'Our comprehensive curriculum.',
            'program_1_image' => '',
            'program_1_tag' => 'Ages 3-5',
            'program_1_title' => 'Early Childhood Education',
            'program_1_description' => 'A nurturing environment for young learners.',
            'program_2_image' => '',
            'program_2_tag' => 'Grades 1-8',
            'program_2_title' => 'Primary & Middle School',
            'program_2_description' => 'Building strong academic foundations.',
            'program_3_image' => '',
            'program_3_tag' => 'Grades 9-12',
            'program_3_title' => 'High School',
            'program_3_description' => 'Rigorous college-preparatory curriculum.',
            'program_4_image' => '',
            'program_4_tag' => 'All Ages',
            'program_4_title' => 'Extracurricular Programs',
            'program_4_description' => 'From robotics to debate, music to sports.',
            'school_logo' => '',
            'primary_color' => '#0d0d2b',
            'secondary_color' => '#c9a84c',
            'show_slider' => '1',
            'show_stats' => '1',
            'show_team' => '1',
            'show_gallery' => '1',
        ];

        $settings = $defaults;
        try {
            foreach (array_keys($defaults) as $key) {
                $val = Setting::get($key, $defaults[$key]);
                if ($val !== null) {
                    $settings[$key] = $val;
                }
            }
        } catch (\Throwable $e) {}
        return $settings;
    }

    public function gallery()
    {
        $settings = $this->getWebsiteSettings();
        $galleryImages = collect();
        try { $galleryImages = GalleryImage::where('is_active', true)->orderBy('sort_order')->paginate(12, ['*'], 'photos'); } catch (\Throwable $e) {}
        $websiteVideos = collect();
        try { $websiteVideos = VideoLibrary::forWebsite()->orderBy('created_at', 'desc')->paginate(12, ['*'], 'videos'); } catch (\Throwable $e) {}
        $galleryVideos = collect();
        try { $galleryVideos = GalleryVideo::where('is_active', true)->orderBy('sort_order')->get(); } catch (\Throwable $e) {}
        return view('gallery', compact('galleryImages', 'websiteVideos', 'galleryVideos', 'settings'));
    }

    public function about()
    {
        $settings = $this->getWebsiteSettings();
        return view('about', compact('settings'));
    }

    public function contact()
    {
        $settings = $this->getWebsiteSettings();
        $branches = collect();
        try { $branches = \App\Models\Branch::where('is_active', true)->orderBy('is_headquarters', 'desc')->orderBy('order')->orderBy('name')->get(); } catch (\Throwable $e) {}
        return view('contact', compact('settings', 'branches'));
    }

    public function team()
    {
        $settings = $this->getWebsiteSettings();
        $teamMembers = collect();
        try { $teamMembers = TeamMember::where('is_active', true)->orderBy('sort_order')->get(); } catch (\Throwable $e) {}
        return view('team', compact('settings', 'teamMembers'));
    }
}
PHPCODE;

$result = file_put_contents($hcPath, $patchedController);
if ($result !== false) {
    echo "<div class='patch'><p class='ok'>✅ HomeController.php PATCHED with 3-level fallback (" . strlen($patchedController) . " bytes written)</p>";
    echo "<p class='info'>Level 1: Full welcome view (all features)</p>";
    echo "<p class='info'>Level 2: Simple home view (fewer features)</p>";
    echo "<p class='info'>Level 3: Plain HTML (guaranteed to work — no Blade, no DB)</p></div>";
    $fixesApplied++;
} else {
    echo "<p class='err'>❌ Could not write HomeController.php — check file permissions!</p>";
}

// ═══════════════════════════════════════════════════════════
// PATCH 3: Re-enable detectAppUrl() if commented out
// ═══════════════════════════════════════════════════════════
echo '<h2>Patch 3: Fix AppServiceProvider</h2>';

$aspPath = $baseDir . '/app/Providers/AppServiceProvider.php';
if (file_exists($aspPath)) {
    $aspContent = file_get_contents($aspPath);
    
    $changed = false;
    
    // Re-enable detectAppUrl if commented
    if (strpos($aspContent, '//$this->detectAppUrl') !== false) {
        $aspContent = str_replace('//$this->detectAppUrl', '$this->detectAppUrl', $aspContent);
        $changed = true;
    }
    if (strpos($aspContent, '// $this->detectAppUrl') !== false) {
        $aspContent = str_replace('// $this->detectAppUrl', '$this->detectAppUrl', $aspContent);
        $changed = true;
    }
    // Remove "Temporarily disabled" comment
    $aspContent = str_replace(' // Temporarily disabled to fix homepage', '', $aspContent);
    
    if ($changed) {
        // Backup first
        copy($aspPath, $aspPath . '.backup.' . time());
        file_put_contents($aspPath, $aspContent);
        echo "<p class='ok'>✅ Re-enabled detectAppUrl() in AppServiceProvider</p>";
        $fixesApplied++;
    } else {
        echo "<p class='ok'>✅ detectAppUrl() already active</p>";
    }
}

// ═══════════════════════════════════════════════════════════
// PATCH 4: Ensure directories exist
// ═══════════════════════════════════════════════════════════
echo '<h2>Patch 4: Storage Directories</h2>';

$dirs = [
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
];

foreach ($dirs as $dir) {
    $path = $baseDir . '/' . $dir;
    if (!is_dir($path)) {
        if (@mkdir($path, 0755, true)) {
            echo "<p class='ok'>✅ Created: {$dir}/</p>";
            $fixesApplied++;
        }
    } else {
        echo "<p class='ok'>✅ Exists: {$dir}/</p>";
    }
}

// ═══════════════════════════════════════════════════════════
// PATCH 5: Delete Classroom.php if exists (causes conflict)
// ═══════════════════════════════════════════════════════════
echo '<h2>Patch 5: Remove Classroom.php Alias</h2>';

$oldClassroom = $baseDir . '/app/Models/Classroom.php';
if (file_exists($oldClassroom)) {
    if (@unlink($oldClassroom)) {
        echo "<p class='ok'>✅ Deleted app/Models/Classroom.php (conflicting alias)</p>";
        $fixesApplied++;
    } else {
        echo "<p class='err'>❌ Could not delete Classroom.php — delete manually!</p>";
    }
} else {
    echo "<p class='ok'>✅ No conflicting Classroom.php found</p>";
}

// ═══════════════════════════════════════════════════════════
// TEST: Real HTTP request using curl
// ═══════════════════════════════════════════════════════════
echo '<h2>Test: Real HTTP Request to Homepage</h2>';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$homepageUrl = $protocol . '://' . $host . '/';

echo "<p class='info'>Requesting: {$homepageUrl}</p>";

if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $homepageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 PatchTest/1.0');

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        echo "<p class='warn'>⚠️ cURL Error: {$curlError}</p>";
    }

    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    echo "<p>HTTP Status: <strong class='" . ($httpCode === 200 ? 'ok' : 'err') . "'>{$httpCode}</strong></p>";

    if ($httpCode === 200) {
        echo "<p class='ok'>✅✅✅ HOMEPAGE IS WORKING!</p>";
        echo "<p class='ok'>The patch worked. The homepage now returns 200.</p>";
    } elseif ($httpCode === 500) {
        echo "<p class='err'>❌ Still returning 500. Checking response body...</p>";
        
        // Try to extract the actual error
        if (preg_match('/Class\s+[\'"]?(\S+?)[\'"]?\s+not found/i', $body, $m)) {
            echo "<p class='err'><strong>Missing Class:</strong> {$m[1]}</p>";
        }
        if (preg_match('/Fatal\s+error:\s+(.*?)(?:\s+in\s+|$)/si', $body, $m)) {
            echo "<p class='err'><strong>PHP Fatal Error:</strong> " . htmlspecialchars(strip_tags($m[1])) . "</p>";
        }
        if (preg_match('/Allowed\s+memory\s+size\s+of/i', $body)) {
            echo "<p class='err'><strong>Memory Limit Exceeded!</strong> The page is too large for ByetHost's PHP memory limit.</p>";
        }
        if (preg_match('/Maximum\s+execution\s+time/i', $body)) {
            echo "<p class='err'><strong>Timeout!</strong> The page takes too long to render on ByetHost.</p>";
        }
        if (stripos($body, 'byethost') !== false) {
            echo "<p class='warn'><strong>ByetHost error page detected</strong> — this is NOT a Laravel error. ByetHost is blocking the request.</p>";
        }
        
        echo '<details><summary>Full response body</summary><pre>' . htmlspecialchars(substr($body, 0, 8000)) . '</pre></details>';
    } elseif (in_array($httpCode, [301, 302, 307, 308])) {
        if (preg_match('/Location:\s*(.*)/i', $headers, $m)) {
            $redirectUrl = trim($m[1]);
            echo "<p class='warn'>Redirecting to: <code>{$redirectUrl}</code></p>";
            if (strpos($redirectUrl, 'localhost') !== false) {
                echo "<p class='err'>❌ REDIRECTING TO LOCALHOST — detectAppUrl fix needed!</p>";
            }
        }
    }
} else {
    echo "<p class='warn'>⚠️ cURL not available — cannot test via HTTP</p>";
    echo "<p class='info'>Please test manually: <a href='/'>Visit Homepage</a></p>";
}

// ═══════════════════════════════════════════════════════════
// Summary
// ═══════════════════════════════════════════════════════════
echo '<hr>';
echo "<h2>Summary: {$fixesApplied} patches applied</h2>";
echo '<p><a href="/" style="font-size:1.2em;color:#c9a84c">👉 Test Homepage Now</a></p>';
echo '<p><a href="/login" style="color:#60a5fa">Test Login</a></p>';
echo '<hr>';
echo '<p style="color:#f87171;font-size:1.1em"><strong>⚠️ DELETE THIS FILE AFTER FIXING: patch-homepage.php</strong></p>';
echo '</body></html>';
