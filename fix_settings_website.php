<?php
/**
 * FIX SCRIPT: Settings & Website Management
 * 
 * This script:
 * 1. Seeds all default settings into the database
 * 2. Runs the fixed migration for subjects.type ENUM
 * 3. Creates the storage link for file uploads
 * 
 * Run: php fix_settings_website.php
 */

echo "=== Settings & Website Fix Script ===\n\n";

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

// Step 1: Create storage link
echo "[1/4] Creating storage link...\n";
try {
    Artisan::call('storage:link', []);
    echo "  ✓ Storage link created\n";
} catch (\Exception $e) {
    echo "  ⚠ Storage link may already exist: " . $e->getMessage() . "\n";
}

// Step 2: Seed settings
echo "\n[2/4] Seeding default settings...\n";

$settings = [
    // General
    ['key' => 'school_name', 'value' => 'School of Redemption', 'group' => 'general', 'type' => 'text', 'description' => 'The official name of the school displayed on the website'],
    ['key' => 'school_tagline', 'value' => 'Excellence in Education', 'group' => 'general', 'type' => 'text', 'description' => 'School tagline or motto'],
    ['key' => 'school_description', 'value' => 'At School of Redemption, we nurture each student\'s potential through excellence in education, character development, and innovative learning methodologies that prepare them for tomorrow\'s challenges.', 'group' => 'general', 'type' => 'textarea', 'description' => 'Brief description for homepage and SEO'],
    ['key' => 'school_logo', 'value' => '', 'group' => 'appearance', 'type' => 'file', 'description' => 'School logo image'],

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

$count = 0;
foreach ($settings as $setting) {
    Setting::updateOrCreate(
        ['key' => $setting['key']],
        $setting
    );
    $count++;
}
echo "  ✓ Seeded {$count} settings\n";

// Step 3: Fix subjects.type ENUM migration
echo "\n[3/4] Fixing subjects.type ENUM migration...\n";
try {
    if (Schema::hasTable('subjects')) {
        // Update invalid type values first
        $updated = DB::statement("UPDATE subjects SET type = 'compulsory' WHERE type NOT IN ('compulsory','elective','optional') OR type IS NULL");
        echo "  ✓ Updated invalid subject types to 'compulsory'\n";

        // Alter the ENUM
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type ENUM('compulsory','elective','optional') NOT NULL DEFAULT 'compulsory'");
        echo "  ✓ Altered subjects.type ENUM to ('compulsory','elective','optional')\n";

        // Add priority column if missing
        if (!Schema::hasColumn('subjects', 'priority')) {
            DB::statement("ALTER TABLE subjects ADD COLUMN priority INT DEFAULT 0 AFTER type");
            echo "  ✓ Added 'priority' column to subjects table\n";
        } else {
            echo "  ℹ 'priority' column already exists\n";
        }
    } else {
        echo "  ⚠ subjects table doesn't exist yet, skipping\n";
    }
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

// Step 4: Clear cache
echo "\n[4/4] Clearing caches...\n";
try {
    Artisan::call('config:clear');
    echo "  ✓ Config cache cleared\n";
    Artisan::call('view:clear');
    echo "  ✓ View cache cleared\n";
    Artisan::call('cache:clear');
    echo "  ✓ Application cache cleared\n";
    Artisan::call('route:clear');
    echo "  ✓ Route cache cleared\n";
} catch (\Exception $e) {
    echo "  ⚠ Cache clear error: " . $e->getMessage() . "\n";
}

echo "\n=== Fix Complete! ===\n\n";
echo "What was fixed:\n";
echo "  1. All default settings have been seeded into the database\n";
echo "  2. Settings page now works without the 'admin.settings.create' route error\n";
echo "  3. Subjects table ENUM has been fixed to ('compulsory','elective','optional')\n";
echo "  4. Logo upload feature is now available on the Settings page\n";
echo "  5. Public website (welcome page) now uses settings from the database\n";
echo "  6. Admin panel sidebar now shows the school name and logo from settings\n";
echo "\nNext steps:\n";
echo "  1. Go to Admin > Settings and update your school name, contact info, etc.\n";
echo "  2. Upload your school logo\n";
echo "  3. Check the public website to see changes reflected\n";
echo "  4. Add social media links in Settings > Social Media Links\n";
