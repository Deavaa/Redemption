<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'school_name', 'value' => 'School of Redemption', 'group' => 'general', 'type' => 'text', 'description' => 'The official name of the school displayed on the website'],
            ['key' => 'school_name_am', 'value' => 'ስኩል ኦፍ ሪደምሽን', 'group' => 'general', 'type' => 'text', 'description' => 'School name in Amharic (የትምህርት ቤት ስም በአማርኛ)'],
            ['key' => 'school_tagline', 'value' => 'Excellence in Education', 'group' => 'general', 'type' => 'text', 'description' => 'School tagline or motto'],
            ['key' => 'school_description', 'value' => 'At School of Redemption, we nurture each student\'s potential through excellence in education, character development, and innovative learning methodologies that prepare them for tomorrow\'s challenges.', 'group' => 'general', 'type' => 'textarea', 'description' => 'Brief description for homepage and SEO'],
            ['key' => 'school_logo', 'value' => '', 'group' => 'appearance', 'type' => 'file', 'description' => 'School logo image (upload via Settings page)'],

            // Contact
            ['key' => 'school_phone', 'value' => '0112345678', 'group' => 'contact', 'type' => 'text', 'description' => 'Main phone number'],
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

            // Appearance
            ['key' => 'primary_color', 'value' => '#0d0d2b', 'group' => 'appearance', 'type' => 'text', 'description' => 'Primary brand color (hex)'],
            ['key' => 'secondary_color', 'value' => '#c9a84c', 'group' => 'appearance', 'type' => 'text', 'description' => 'Secondary brand color (hex)'],
            ['key' => 'show_slider', 'value' => '1', 'group' => 'appearance', 'type' => 'boolean', 'description' => 'Show hero slider on homepage'],
            ['key' => 'show_stats', 'value' => '1', 'group' => 'appearance', 'type' => 'boolean', 'description' => 'Show stats on homepage'],
            ['key' => 'show_team', 'value' => '1', 'group' => 'appearance', 'type' => 'boolean', 'description' => 'Show team section on homepage'],
            ['key' => 'show_gallery', 'value' => '1', 'group' => 'appearance', 'type' => 'boolean', 'description' => 'Show gallery on homepage'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Settings seeded successfully!');
    }
}
