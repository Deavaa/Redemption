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
        try {
            return $this->renderHomepage();
        } catch (\Throwable $e) {
            // Log the actual error so we can diagnose it
            \Log::error('Homepage 500 Error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null,
            ]);

            // If the full homepage fails, try a minimal fallback
            try {
                return $this->renderMinimalHomepage();
            } catch (\Throwable $e2) {
                // Last resort: plain HTML
                return response($this->renderEmergencyHomepage($e), 500);
            }
        }
    }

    /**
     * Full homepage rendering with all features
     */
    private function renderHomepage()
    {
        // Wrap ALL database queries in try-catch so the homepage
        // still renders even if tables are missing on shared hosting.
        $sliders = collect();
        try {
            $sliders = Slider::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        } catch (\Throwable $e) {}

        $teamMembers = collect();
        try {
            $teamMembers = TeamMember::where('is_active', true)
                ->orderBy('sort_order')
                ->limit(4)
                ->get();
        } catch (\Throwable $e) {}

        $galleryImages = collect();
        try {
            $galleryImages = GalleryImage::where('is_active', true)
                ->orderBy('sort_order')
                ->limit(6)
                ->get();
        } catch (\Throwable $e) {}

        $websiteVideos = collect();
        try {
            $websiteVideos = VideoLibrary::forWebsite()
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        } catch (\Throwable $e) {}

        $galleryVideos = collect();
        try {
            $galleryVideos = GalleryVideo::where('is_active', true)
                ->orderBy('sort_order')
                ->limit(6)
                ->get();
        } catch (\Throwable $e) {}

        $settings = $this->getWebsiteSettings();

        $latestNews = collect();
        try {
            $latestNews = \App\Models\News::visibleOnWebsite()->limit(3)->get();
        } catch (\Throwable $e) {}

        return view('welcome', compact('sliders', 'teamMembers', 'galleryImages', 'websiteVideos', 'galleryVideos', 'settings', 'latestNews'));
    }

    /**
     * Minimal fallback homepage — no complex view, just basic HTML
     */
    private function renderMinimalHomepage()
    {
        $settings = $this->getWebsiteSettings();
        $schoolName = $settings['school_name'] ?? 'School of Redemption';
        $tagline = $settings['school_tagline'] ?? 'Excellence in Education';
        $loginUrl = url('/login');

        return response()->view('home', compact('settings'), 200);
    }

    /**
     * Emergency fallback — plain HTML when even the minimal view fails
     */
    private function renderEmergencyHomepage(\Throwable $originalError): string
    {
        $schoolName = 'School of Redemption';
        $loginUrl = '/login';
        $errorMsg = htmlspecialchars($originalError->getMessage());
        $errorFile = htmlspecialchars(basename($originalError->getFile()) . ':' . $originalError->getLine());

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$schoolName}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0d0d2b; color: #fff; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .container { text-align: center; padding: 2rem; max-width: 600px; }
        h1 { font-size: 2.5rem; color: #c9a84c; margin-bottom: 1rem; }
        p { color: rgba(255,255,255,0.7); margin-bottom: 1.5rem; line-height: 1.6; }
        .btn { display: inline-block; background: #c9a84c; color: #0d0d2b; padding: 0.75rem 2rem; border-radius: 50px; text-decoration: none; font-weight: 600; transition: background 0.3s; }
        .btn:hover { background: #e8b82e; }
        .error-info { margin-top: 2rem; padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 8px; font-size: 0.85rem; color: rgba(255,255,255,0.5); }
    </style>
</head>
<body>
    <div class="container">
        <h1>{$schoolName}</h1>
        <p>Welcome to School of Redemption. Our website is being updated. Please use the login portal to access the system.</p>
        <a href="{$loginUrl}" class="btn">Login to Portal</a>
        <div class="error-info">
            <p>Technical details: {$errorMsg} in {$errorFile}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get all settings needed for the public website layout.
     * Uses try-catch around each Setting::get() call so missing
     * database tables don't crash the entire page.
     */
    private function getWebsiteSettings(): array
    {
        $defaults = [
            // General
            'school_name' => 'School of Redemption',
            'school_tagline' => 'Excellence in Education',
            'school_description' => 'At School of Redemption, we nurture each student\'s potential through excellence in education, character development, and innovative learning methodologies that prepare them for tomorrow\'s challenges.',

            // Contact
            'school_phone' => '+251 11 234 5678',
            'school_email' => 'info@schoolofredemption.edu',
            'school_address' => 'Addis Ababa, Ethiopia',
            'school_website' => 'https://schoolofredemption.edu',

            // Academic Stats
            'total_students' => '1500+',
            'total_teachers' => '120+',
            'university_acceptance' => '98%',
            'years_of_excellence' => '25+',

            // About
            'about_description' => 'School of Redemption has been at the forefront of educational excellence for over two decades. Founded on the principles of integrity, innovation, and inclusivity, we have grown into an institution that shapes the leaders of tomorrow.',
            'about_mission' => 'To provide quality education that empowers students to become responsible, innovative, and compassionate leaders of tomorrow.',
            'about_vision' => 'To be a leading institution of academic excellence, fostering holistic development and preparing students for global challenges.',
            'about_image' => '',
            'about_students_count' => '500+',
            'about_years_experience' => '15+',
            'about_programs' => '8',
            'about_success_rate' => '95%',

            // Social Media
            'facebook_url' => '',
            'twitter_url' => '',
            'youtube_url' => '',
            'telegram_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',

            // Website
            'cta_title' => 'Ready to Begin Your Journey?',
            'cta_description' => 'Join our community of learners and discover the transformative power of education at School of Redemption. Admissions are now open for the upcoming academic year.',
            'cta_button_text' => 'Apply Now',
            'cta_button_url' => '#contact',
            'footer_text' => 'School of Redemption. All rights reserved.',

            // Why Choose Us
            'wcu_section_title' => 'Excellence in Every Aspect',
            'wcu_section_subtitle' => 'We provide a comprehensive educational experience that goes beyond academics, focusing on holistic development and character building.',
            'wcu_1_icon' => 'fas fa-chalkboard-teacher',
            'wcu_1_title' => 'Expert Faculty',
            'wcu_1_description' => 'Our teachers are highly qualified professionals with years of experience, dedicated to nurturing each student\'s unique potential.',
            'wcu_2_icon' => 'fas fa-microscope',
            'wcu_2_title' => 'Modern Facilities',
            'wcu_2_description' => 'State-of-the-art laboratories, libraries, sports facilities, and technology-enabled classrooms provide the perfect learning environment.',
            'wcu_3_icon' => 'fas fa-users',
            'wcu_3_title' => 'Small Class Sizes',
            'wcu_3_description' => 'Limited student-teacher ratio ensures personalized attention and customized learning approaches for every student.',
            'wcu_4_icon' => 'fas fa-palette',
            'wcu_4_title' => 'Holistic Development',
            'wcu_4_description' => 'Beyond academics, we focus on arts, sports, leadership, and character development to create well-rounded individuals.',
            'wcu_5_icon' => 'fas fa-globe',
            'wcu_5_title' => 'Global Perspective',
            'wcu_5_description' => 'International curriculum standards and exchange programs prepare students for success in a globalized world.',
            'wcu_6_icon' => 'fas fa-award',
            'wcu_6_title' => 'Proven Track Record',
            'wcu_6_description' => 'Our students consistently achieve top scores in national examinations and gain admission to prestigious universities.',

            // Academic Programs
            'programs_section_title' => 'Pathways to Success',
            'programs_section_subtitle' => 'Our comprehensive curriculum is designed to challenge and inspire students at every stage of their educational journey.',
            'program_1_image' => '',
            'program_1_tag' => 'Ages 3-5',
            'program_1_title' => 'Early Childhood Education',
            'program_1_description' => 'A nurturing environment where young learners develop foundational skills through play-based learning and creative exploration.',
            'program_2_image' => '',
            'program_2_tag' => 'Grades 1-8',
            'program_2_title' => 'Primary & Middle School',
            'program_2_description' => 'Building strong academic foundations while fostering curiosity, critical thinking, and social-emotional development.',
            'program_3_image' => '',
            'program_3_tag' => 'Grades 9-12',
            'program_3_title' => 'High School',
            'program_3_description' => 'Rigorous college-preparatory curriculum with advanced placement courses and specialized tracks in sciences, arts, and humanities.',
            'program_4_image' => '',
            'program_4_tag' => 'All Ages',
            'program_4_title' => 'Extracurricular Programs',
            'program_4_description' => 'From robotics to debate, music to sports — our diverse extracurricular offerings help students discover their passions.',

            // Appearance
            'school_logo' => '',
            'primary_color' => '#0d0d2b',
            'secondary_color' => '#c9a84c',
            'show_slider' => '1',
            'show_stats' => '1',
            'show_team' => '1',
            'show_gallery' => '1',
        ];

        // Try to load settings from database; fall back to defaults
        $settings = $defaults;
        try {
            foreach (array_keys($defaults) as $key) {
                $val = Setting::get($key, $defaults[$key]);
                if ($val !== null) {
                    $settings[$key] = $val;
                }
            }
        } catch (\Throwable $e) {
            // Settings table doesn't exist or DB is down — use all defaults
        }

        return $settings;
    }

    /**
     * Show the full gallery page (photos + videos)
     */
    public function gallery()
    {
        $settings = $this->getWebsiteSettings();

        $galleryImages = collect();
        try {
            $galleryImages = GalleryImage::where('is_active', true)
                ->orderBy('sort_order')
                ->paginate(12, ['*'], 'photos');
        } catch (\Throwable $e) {}

        $websiteVideos = collect();
        try {
            $websiteVideos = VideoLibrary::forWebsite()
                ->orderBy('created_at', 'desc')
                ->paginate(12, ['*'], 'videos');
        } catch (\Throwable $e) {}

        $galleryVideos = collect();
        try {
            $galleryVideos = GalleryVideo::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        } catch (\Throwable $e) {}

        return view('gallery', compact('galleryImages', 'websiteVideos', 'galleryVideos', 'settings'));
    }

    /**
     * Show the about page
     */
    public function about()
    {
        $settings = $this->getWebsiteSettings();
        return view('about', compact('settings'));
    }

    /**
     * Show the contact page
     */
    public function contact()
    {
        $settings = $this->getWebsiteSettings();
        $branches = collect();
        try {
            $branches = \App\Models\Branch::where('is_active', true)
                ->orderBy('is_headquarters', 'desc')
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        } catch (\Throwable $e) {}
        return view('contact', compact('settings', 'branches'));
    }

    /**
     * Show the team page
     */
    public function team()
    {
        $settings = $this->getWebsiteSettings();

        $teamMembers = collect();
        try {
            $teamMembers = TeamMember::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        } catch (\Throwable $e) {}

        return view('team', compact('settings', 'teamMembers'));
    }
}
