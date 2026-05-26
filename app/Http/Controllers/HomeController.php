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
            'about_image' => Setting::get('about_image', ''),
            'about_students_count' => Setting::get('about_students_count', '500+'),
            'about_years_experience' => Setting::get('about_years_experience', '15+'),
            'about_programs' => Setting::get('about_programs', '8'),
            'about_success_rate' => Setting::get('about_success_rate', '95%'),

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

            // Why Choose Us
            'wcu_section_title' => Setting::get('wcu_section_title', 'Excellence in Every Aspect'),
            'wcu_section_subtitle' => Setting::get('wcu_section_subtitle', 'We provide a comprehensive educational experience that goes beyond academics, focusing on holistic development and character building.'),
            'wcu_1_icon' => Setting::get('wcu_1_icon', 'fas fa-chalkboard-teacher'),
            'wcu_1_title' => Setting::get('wcu_1_title', 'Expert Faculty'),
            'wcu_1_description' => Setting::get('wcu_1_description', 'Our teachers are highly qualified professionals with years of experience, dedicated to nurturing each student\'s unique potential.'),
            'wcu_2_icon' => Setting::get('wcu_2_icon', 'fas fa-microscope'),
            'wcu_2_title' => Setting::get('wcu_2_title', 'Modern Facilities'),
            'wcu_2_description' => Setting::get('wcu_2_description', 'State-of-the-art laboratories, libraries, sports facilities, and technology-enabled classrooms provide the perfect learning environment.'),
            'wcu_3_icon' => Setting::get('wcu_3_icon', 'fas fa-users'),
            'wcu_3_title' => Setting::get('wcu_3_title', 'Small Class Sizes'),
            'wcu_3_description' => Setting::get('wcu_3_description', 'Limited student-teacher ratio ensures personalized attention and customized learning approaches for every student.'),
            'wcu_4_icon' => Setting::get('wcu_4_icon', 'fas fa-palette'),
            'wcu_4_title' => Setting::get('wcu_4_title', 'Holistic Development'),
            'wcu_4_description' => Setting::get('wcu_4_description', 'Beyond academics, we focus on arts, sports, leadership, and character development to create well-rounded individuals.'),
            'wcu_5_icon' => Setting::get('wcu_5_icon', 'fas fa-globe'),
            'wcu_5_title' => Setting::get('wcu_5_title', 'Global Perspective'),
            'wcu_5_description' => Setting::get('wcu_5_description', 'International curriculum standards and exchange programs prepare students for success in a globalized world.'),
            'wcu_6_icon' => Setting::get('wcu_6_icon', 'fas fa-award'),
            'wcu_6_title' => Setting::get('wcu_6_title', 'Proven Track Record'),
            'wcu_6_description' => Setting::get('wcu_6_description', 'Our students consistently achieve top scores in national examinations and gain admission to prestigious universities.'),

            // Academic Programs
            'programs_section_title' => Setting::get('programs_section_title', 'Pathways to Success'),
            'programs_section_subtitle' => Setting::get('programs_section_subtitle', 'Our comprehensive curriculum is designed to challenge and inspire students at every stage of their educational journey.'),
            'program_1_image' => Setting::get('program_1_image', ''),
            'program_1_tag' => Setting::get('program_1_tag', 'Ages 3-5'),
            'program_1_title' => Setting::get('program_1_title', 'Early Childhood Education'),
            'program_1_description' => Setting::get('program_1_description', 'A nurturing environment where young learners develop foundational skills through play-based learning and creative exploration.'),
            'program_2_image' => Setting::get('program_2_image', ''),
            'program_2_tag' => Setting::get('program_2_tag', 'Grades 1-8'),
            'program_2_title' => Setting::get('program_2_title', 'Primary & Middle School'),
            'program_2_description' => Setting::get('program_2_description', 'Building strong academic foundations while fostering curiosity, critical thinking, and social-emotional development.'),
            'program_3_image' => Setting::get('program_3_image', ''),
            'program_3_tag' => Setting::get('program_3_tag', 'Grades 9-12'),
            'program_3_title' => Setting::get('program_3_title', 'High School'),
            'program_3_description' => Setting::get('program_3_description', 'Rigorous college-preparatory curriculum with advanced placement courses and specialized tracks in sciences, arts, and humanities.'),
            'program_4_image' => Setting::get('program_4_image', ''),
            'program_4_tag' => Setting::get('program_4_tag', 'All Ages'),
            'program_4_title' => Setting::get('program_4_title', 'Extracurricular Programs'),
            'program_4_description' => Setting::get('program_4_description', 'From robotics to debate, music to sports — our diverse extracurricular offerings help students discover their passions.'),

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
        $branches = \App\Models\Branch::where('is_active', true)
            ->orderBy('is_headquarters', 'desc')
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        return view('contact', compact('settings', 'branches'));
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
