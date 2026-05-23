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
        // Get active sliders ordered by sort_order
        $sliders = Slider::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Get active team members ordered by sort_order (limit to 4 for homepage)
        $teamMembers = TeamMember::where('is_active', true)
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        // Get active gallery images ordered by sort_order (limit to 6 for homepage)
        $galleryImages = GalleryImage::where('is_active', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        // Get website-visible videos from Video Library (limit to 6 for homepage)
        $websiteVideos = collect();
        try {
            $websiteVideos = VideoLibrary::forWebsite()
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        } catch (\Exception $e) {}

        // Also get GalleryVideo entries for the website
        $galleryVideos = collect();
        try {
            $galleryVideos = GalleryVideo::where('is_active', true)
                ->orderBy('sort_order')
                ->limit(6)
                ->get();
        } catch (\Exception $e) {}

        // Get ALL settings from database - used throughout the website
        $settings = $this->getWebsiteSettings();

        // Get latest news for the news banner
        $latestNews = collect();
        try {
            $latestNews = \App\Models\News::visibleOnWebsite()->limit(3)->get();
        } catch (\Exception $e) {}

        return view('welcome', compact('sliders', 'teamMembers', 'galleryImages', 'websiteVideos', 'galleryVideos', 'settings', 'latestNews'));
    }

    /**
     * Get all settings needed for the public website layout
     */
    private function getWebsiteSettings(): array
    {
        return [
            // General
            'school_name' => Setting::get('school_name', 'School of Redemption'),
            'school_tagline' => Setting::get('school_tagline', 'Excellence in Education'),
            'school_description' => Setting::get('school_description', 'At School of Redemption, we nurture each student\'s potential through excellence in education, character development, and innovative learning methodologies that prepare them for tomorrow\'s challenges.'),

            // Contact
            'school_phone' => Setting::get('school_phone', '+251 11 234 5678'),
            'school_email' => Setting::get('school_email', 'info@schoolofredemption.edu'),
            'school_address' => Setting::get('school_address', 'Addis Ababa, Ethiopia'),
            'school_website' => Setting::get('school_website', 'https://schoolofredemption.edu'),

            // Academic Stats
            'total_students' => Setting::get('total_students', '1500+'),
            'total_teachers' => Setting::get('total_teachers', '120+'),
            'university_acceptance' => Setting::get('university_acceptance', '98%'),
            'years_of_excellence' => Setting::get('years_of_excellence', '25+'),

            // About
            'about_description' => Setting::get('about_description', 'School of Redemption has been at the forefront of educational excellence for over two decades. Founded on the principles of integrity, innovation, and inclusivity, we have grown into an institution that shapes the leaders of tomorrow.'),
            'about_mission' => Setting::get('about_mission', 'To provide quality education that empowers students to become responsible, innovative, and compassionate leaders of tomorrow.'),
            'about_vision' => Setting::get('about_vision', 'To be a leading institution of academic excellence, fostering holistic development and preparing students for global challenges.'),

            // Social Media
            'facebook_url' => Setting::get('facebook_url', ''),
            'twitter_url' => Setting::get('twitter_url', ''),
            'youtube_url' => Setting::get('youtube_url', ''),
            'telegram_url' => Setting::get('telegram_url', ''),
            'instagram_url' => Setting::get('instagram_url', ''),
            'linkedin_url' => Setting::get('linkedin_url', ''),

            // Website
            'cta_title' => Setting::get('cta_title', 'Ready to Begin Your Journey?'),
            'cta_description' => Setting::get('cta_description', 'Join our community of learners and discover the transformative power of education at School of Redemption. Admissions are now open for the upcoming academic year.'),
            'cta_button_text' => Setting::get('cta_button_text', 'Apply Now'),
            'cta_button_url' => Setting::get('cta_button_url', '#contact'),
            'footer_text' => Setting::get('footer_text', 'School of Redemption. All rights reserved.'),

            // Appearance
            'school_logo' => Setting::get('school_logo', ''),
            'primary_color' => Setting::get('primary_color', '#0d0d2b'),
            'secondary_color' => Setting::get('secondary_color', '#c9a84c'),
            'show_slider' => Setting::get('show_slider', '1'),
            'show_stats' => Setting::get('show_stats', '1'),
            'show_team' => Setting::get('show_team', '1'),
            'show_gallery' => Setting::get('show_gallery', '1'),
        ];
    }

    /**
     * Show the full gallery page (photos + videos)
     */
    public function gallery()
    {
        $settings = $this->getWebsiteSettings();

        // Get all active gallery images
        $galleryImages = GalleryImage::where('is_active', true)
            ->orderBy('sort_order')
            ->paginate(12, ['*'], 'photos');

        // Get website-visible videos from Video Library
        $websiteVideos = collect();
        try {
            $websiteVideos = VideoLibrary::forWebsite()
                ->orderBy('created_at', 'desc')
                ->paginate(12, ['*'], 'videos');
        } catch (\Exception $e) {}

        // Also get GalleryVideo entries
        $galleryVideos = collect();
        try {
            $galleryVideos = GalleryVideo::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        } catch (\Exception $e) {}

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
        return view('contact', compact('settings'));
    }

    /**
     * Show the team page
     */
    public function team()
    {
        $settings = $this->getWebsiteSettings();

        $teamMembers = TeamMember::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('team', compact('settings', 'teamMembers'));
    }
}
