<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

class SeedSettings extends Command
{
    protected $signature = 'settings:seed';
    protected $description = 'Seed default settings into the database';

    public function handle(): int
    {
        $settings = [
            // General
            ['key' => 'school_name', 'value' => 'School of Redemption', 'group' => 'general', 'type' => 'text', 'description' => 'The official name of the school displayed on the website'],
            ['key' => 'school_tagline', 'value' => 'Excellence in Education', 'group' => 'general', 'type' => 'text', 'description' => 'School tagline or motto'],
            ['key' => 'school_description', 'value' => 'At School of Redemption, we nurture each student\'s potential through excellence in education, character development, and innovative learning methodologies that prepare them for tomorrow\'s challenges.', 'group' => 'general', 'type' => 'textarea', 'description' => 'Brief description for homepage and SEO'],
            ['key' => 'school_logo', 'value' => '', 'group' => 'appearance', 'type' => 'file', 'description' => 'School logo image (upload via Settings page)'],

            // Contact
            ['key' => 'school_phone', 'value' => '+251 11 234 5678', 'group' => 'contact', 'type' => 'text', 'description' => 'Main phone number'],
            ['key' => 'school_email', 'value' => 'info@schoolofredemption.edu', 'group' => 'contact', 'type' => 'text', 'description' => 'Main email address'],
            ['key' => 'school_address', 'value' => 'Addis Ababa, Ethiopia', 'group' => 'contact', 'type' => 'text', 'description' => 'School physical address'],
            ['key' => 'school_website', 'value' => 'https://schoolofredemption.edu', 'group' => 'contact', 'type' => 'text', 'description' => 'School website URL'],

            // Academic
            ['key' => 'total_students', 'value' => '1500+', 'group' => 'academic', 'type' => 'text', 'description' => 'Number displayed on website'],
            ['key' => 'total_teachers', 'value' => '120+', 'group' => 'academic', 'type' => 'text', 'description' => 'Number displayed on website'],
            ['key' => 'university_acceptance', 'value' => '98%', 'group' => 'academic', 'type' => 'text', 'description' => 'University acceptance rate displayed on website'],
            ['key' => 'years_of_excellence', 'value' => '25+', 'group' => 'academic', 'type' => 'text', 'description' => 'Years of excellence displayed on website'],

            // Social Media
            ['key' => 'facebook_url', 'value' => '', 'group' => 'social', 'type' => 'text', 'description' => 'Facebook page URL'],
            ['key' => 'twitter_url', 'value' => '', 'group' => 'social', 'type' => 'text', 'description' => 'Twitter/X profile URL'],
            ['key' => 'youtube_url', 'value' => '', 'group' => 'social', 'type' => 'text', 'description' => 'YouTube channel URL'],
            ['key' => 'telegram_url', 'value' => '', 'group' => 'social', 'type' => 'text', 'description' => 'Telegram channel URL'],
            ['key' => 'instagram_url', 'value' => '', 'group' => 'social', 'type' => 'text', 'description' => 'Instagram profile URL'],
            ['key' => 'linkedin_url', 'value' => '', 'group' => 'social', 'type' => 'text', 'description' => 'LinkedIn page URL'],

            // About Page
            ['key' => 'about_description', 'value' => 'School of Redemption has been at the forefront of educational excellence for over two decades. Founded on the principles of integrity, innovation, and inclusivity, we have grown into an institution that shapes the leaders of tomorrow.', 'group' => 'about', 'type' => 'textarea', 'description' => 'About section description'],
            ['key' => 'about_mission', 'value' => 'To provide quality education that empowers students to become responsible, innovative, and compassionate leaders of tomorrow.', 'group' => 'about', 'type' => 'textarea', 'description' => 'Mission statement'],
            ['key' => 'about_vision', 'value' => 'To be a leading institution of academic excellence, fostering holistic development and preparing students for global challenges.', 'group' => 'about', 'type' => 'textarea', 'description' => 'Vision statement'],
            ['key' => 'about_image', 'value' => '', 'group' => 'about', 'type' => 'file', 'description' => 'About page image (upload via Web Content page)'],
            ['key' => 'about_students_count', 'value' => '500+', 'group' => 'about', 'type' => 'text', 'description' => 'Number of students displayed on About page'],
            ['key' => 'about_years_experience', 'value' => '15+', 'group' => 'about', 'type' => 'text', 'description' => 'Years of experience displayed on About page'],
            ['key' => 'about_programs', 'value' => '8', 'group' => 'about', 'type' => 'text', 'description' => 'Number of academic programs displayed on About page'],
            ['key' => 'about_success_rate', 'value' => '95%', 'group' => 'about', 'type' => 'text', 'description' => 'Success rate displayed on About page'],

            // Website
            ['key' => 'cta_title', 'value' => 'Ready to Begin Your Journey?', 'group' => 'website', 'type' => 'text', 'description' => 'Call-to-action section title'],
            ['key' => 'cta_description', 'value' => 'Join our community of learners and discover the transformative power of education at School of Redemption. Admissions are now open for the upcoming academic year.', 'group' => 'website', 'type' => 'textarea', 'description' => 'Call-to-action section description'],
            ['key' => 'cta_button_text', 'value' => 'Apply Now', 'group' => 'website', 'type' => 'text', 'description' => 'CTA button text'],
            ['key' => 'cta_button_url', 'value' => '#contact', 'group' => 'website', 'type' => 'text', 'description' => 'CTA button link'],
            ['key' => 'footer_text', 'value' => 'School of Redemption. All rights reserved.', 'group' => 'website', 'type' => 'text', 'description' => 'Footer copyright text'],

            // Email Settings
            ['key' => 'mail_host', 'value' => '', 'group' => 'email', 'type' => 'text', 'description' => 'SMTP host'],
            ['key' => 'mail_port', 'value' => '587', 'group' => 'email', 'type' => 'number', 'description' => 'SMTP port'],
            ['key' => 'mail_username', 'value' => '', 'group' => 'email', 'type' => 'text', 'description' => 'SMTP username'],
            ['key' => 'mail_from_address', 'value' => '', 'group' => 'email', 'type' => 'text', 'description' => 'From email address'],
            ['key' => 'mail_from_name', 'value' => 'School of Redemption', 'group' => 'email', 'type' => 'text', 'description' => 'From name'],

            // Fee Settings
            ['key' => 'fee_due_day', 'value' => '10', 'group' => 'fees', 'type' => 'number', 'description' => 'Day of month when fees are due (Ethiopian calendar)'],
            ['key' => 'fee_late_penalty', 'value' => '0', 'group' => 'fees', 'type' => 'number', 'description' => 'Late payment penalty percentage'],
            ['key' => 'fee_currency', 'value' => 'ETB', 'group' => 'fees', 'type' => 'text', 'description' => 'Currency symbol'],

            // Why Choose Us Section
            ['key' => 'wcu_section_title', 'value' => 'Excellence in Every Aspect', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Why Choose Us section title'],
            ['key' => 'wcu_section_subtitle', 'value' => 'We provide a comprehensive educational experience that goes beyond academics, focusing on holistic development and character building.', 'group' => 'why_choose_us', 'type' => 'textarea', 'description' => 'Why Choose Us section subtitle'],
            ['key' => 'wcu_1_icon', 'value' => 'fas fa-chalkboard-teacher', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 1 icon class (Font Awesome)'],
            ['key' => 'wcu_1_title', 'value' => 'Expert Faculty', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 1 title'],
            ['key' => 'wcu_1_description', 'value' => 'Our teachers are highly qualified professionals with years of experience, dedicated to nurturing each student\'s unique potential.', 'group' => 'why_choose_us', 'type' => 'textarea', 'description' => 'Feature 1 description'],
            ['key' => 'wcu_2_icon', 'value' => 'fas fa-microscope', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 2 icon class (Font Awesome)'],
            ['key' => 'wcu_2_title', 'value' => 'Modern Facilities', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 2 title'],
            ['key' => 'wcu_2_description', 'value' => 'State-of-the-art laboratories, libraries, sports facilities, and technology-enabled classrooms provide the perfect learning environment.', 'group' => 'why_choose_us', 'type' => 'textarea', 'description' => 'Feature 2 description'],
            ['key' => 'wcu_3_icon', 'value' => 'fas fa-users', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 3 icon class (Font Awesome)'],
            ['key' => 'wcu_3_title', 'value' => 'Small Class Sizes', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 3 title'],
            ['key' => 'wcu_3_description', 'value' => 'Limited student-teacher ratio ensures personalized attention and customized learning approaches for every student.', 'group' => 'why_choose_us', 'type' => 'textarea', 'description' => 'Feature 3 description'],
            ['key' => 'wcu_4_icon', 'value' => 'fas fa-palette', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 4 icon class (Font Awesome)'],
            ['key' => 'wcu_4_title', 'value' => 'Holistic Development', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 4 title'],
            ['key' => 'wcu_4_description', 'value' => 'Beyond academics, we focus on arts, sports, leadership, and character development to create well-rounded individuals.', 'group' => 'why_choose_us', 'type' => 'textarea', 'description' => 'Feature 4 description'],
            ['key' => 'wcu_5_icon', 'value' => 'fas fa-globe', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 5 icon class (Font Awesome)'],
            ['key' => 'wcu_5_title', 'value' => 'Global Perspective', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 5 title'],
            ['key' => 'wcu_5_description', 'value' => 'International curriculum standards and exchange programs prepare students for success in a globalized world.', 'group' => 'why_choose_us', 'type' => 'textarea', 'description' => 'Feature 5 description'],
            ['key' => 'wcu_6_icon', 'value' => 'fas fa-award', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 6 icon class (Font Awesome)'],
            ['key' => 'wcu_6_title', 'value' => 'Proven Track Record', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Feature 6 title'],
            ['key' => 'wcu_6_description', 'value' => 'Our students consistently achieve top scores in national examinations and gain admission to prestigious universities.', 'group' => 'why_choose_us', 'type' => 'textarea', 'description' => 'Feature 6 description'],

            // Academic Programs Section
            ['key' => 'programs_section_title', 'value' => 'Pathways to Success', 'group' => 'programs', 'type' => 'text', 'description' => 'Programs section title'],
            ['key' => 'programs_section_subtitle', 'value' => 'Our comprehensive curriculum is designed to challenge and inspire students at every stage of their educational journey.', 'group' => 'programs', 'type' => 'textarea', 'description' => 'Programs section subtitle'],
            ['key' => 'program_1_image', 'value' => '', 'group' => 'programs', 'type' => 'file', 'description' => 'Program 1 image (upload)'],
            ['key' => 'program_1_tag', 'value' => 'Ages 3-5', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 1 tag/label'],
            ['key' => 'program_1_title', 'value' => 'Early Childhood Education', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 1 title'],
            ['key' => 'program_1_description', 'value' => 'A nurturing environment where young learners develop foundational skills through play-based learning and creative exploration.', 'group' => 'programs', 'type' => 'textarea', 'description' => 'Program 1 description'],
            ['key' => 'program_2_image', 'value' => '', 'group' => 'programs', 'type' => 'file', 'description' => 'Program 2 image (upload)'],
            ['key' => 'program_2_tag', 'value' => 'Grades 1-8', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 2 tag/label'],
            ['key' => 'program_2_title', 'value' => 'Primary & Middle School', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 2 title'],
            ['key' => 'program_2_description', 'value' => 'Building strong academic foundations while fostering curiosity, critical thinking, and social-emotional development.', 'group' => 'programs', 'type' => 'textarea', 'description' => 'Program 2 description'],
            ['key' => 'program_3_image', 'value' => '', 'group' => 'programs', 'type' => 'file', 'description' => 'Program 3 image (upload)'],
            ['key' => 'program_3_tag', 'value' => 'Grades 9-12', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 3 tag/label'],
            ['key' => 'program_3_title', 'value' => 'High School', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 3 title'],
            ['key' => 'program_3_description', 'value' => 'Rigorous college-preparatory curriculum with advanced placement courses and specialized tracks in sciences, arts, and humanities.', 'group' => 'programs', 'type' => 'textarea', 'description' => 'Program 3 description'],
            ['key' => 'program_4_image', 'value' => '', 'group' => 'programs', 'type' => 'file', 'description' => 'Program 4 image (upload)'],
            ['key' => 'program_4_tag', 'value' => 'All Ages', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 4 tag/label'],
            ['key' => 'program_4_title', 'value' => 'Extracurricular Programs', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 4 title'],
            ['key' => 'program_4_description', 'value' => 'From robotics to debate, music to sports — our diverse extracurricular offerings help students discover their passions.', 'group' => 'programs', 'type' => 'textarea', 'description' => 'Program 4 description'],

            // Appearance
            ['key' => 'primary_color', 'value' => '#0d0d2b', 'group' => 'appearance', 'type' => 'text', 'description' => 'Primary brand color (hex)'],
            ['key' => 'secondary_color', 'value' => '#c9a84c', 'group' => 'appearance', 'type' => 'text', 'description' => 'Secondary brand color (hex)'],
            ['key' => 'show_slider', 'value' => '1', 'group' => 'appearance', 'type' => 'boolean', 'description' => 'Show hero slider on homepage'],
            ['key' => 'show_stats', 'value' => '1', 'group' => 'appearance', 'type' => 'boolean', 'description' => 'Show stats on homepage'],
            ['key' => 'show_team', 'value' => '1', 'group' => 'appearance', 'type' => 'boolean', 'description' => 'Show team section on homepage'],
            ['key' => 'show_gallery', 'value' => '1', 'group' => 'appearance', 'type' => 'boolean', 'description' => 'Show gallery on homepage'],
        ];

        $count = 0;
        foreach ($settings as $setting) {
            $created = Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
            if ($created->wasRecentlyCreated) {
                $count++;
            }
        }

        $this->info("Settings seeded successfully! {$count} new settings created, " . (count($settings) - $count) . " existing settings updated.");

        return self::SUCCESS;
    }
}
