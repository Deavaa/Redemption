<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\TeamMember;
use App\Models\GalleryImage;
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

        // Get settings for the homepage
        $settings = [
            'school_name' => Setting::get('school_name', 'School of Redemption'),
            'school_tagline' => Setting::get('school_tagline', 'Excellence in Education'),
            'school_description' => Setting::get('school_description', 'At School of Redemption, we nurture each student\'s potential through excellence in education, character development, and innovative learning methodologies that prepare them for tomorrow\'s challenges.'),
            'school_phone' => Setting::get('school_phone', '+1 (234) 567-890'),
            'school_email' => Setting::get('school_email', 'info@schoolofredemption.edu'),
            'school_address' => Setting::get('school_address', '123 Education Street, City'),
            'total_students' => Setting::get('total_students', '1500+'),
            'total_teachers' => Setting::get('total_teachers', '120+'),
            'university_acceptance' => Setting::get('university_acceptance', '98%'),
            'years_of_excellence' => Setting::get('years_of_excellence', '25+'),
            'about_description' => Setting::get('about_description', 'School of Redemption has been at the forefront of educational excellence for over two decades. Founded on the principles of integrity, innovation, and inclusivity, we have grown into an institution that shapes the leaders of tomorrow.'),
            'cta_title' => Setting::get('cta_title', 'Ready to Begin Your Journey?'),
            'cta_description' => Setting::get('cta_description', 'Join our community of learners and discover the transformative power of education at School of Redemption. Admissions are now open for the 2026 academic year.'),
        ];

        return view('welcome', compact('sliders', 'teamMembers', 'galleryImages', 'settings'));
    }
}