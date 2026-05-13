<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        // Ensure all required settings exist
        $this->ensureSettingsExist();

        $settings = Setting::all()->groupBy('group');
        $groupLabels = [
            'general'    => 'General Settings',
            'website'    => 'Website & Branding',
            'academic'   => 'Academic Settings',
            'contact'    => 'Contact Information',
            'email'      => 'Email Configuration',
            'social'     => 'Social Media Links',
            'about'      => 'About Page Content',
            'appearance' => 'Appearance',
            'fees'       => 'Fee Settings',
            'finance'    => 'Income, Expense & Budget',
        ];

        return view('admin.Setting.index', compact('settings', 'groupLabels'));
    }

    public function update(Request $request)
    {
        $d = $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($d['settings'] as $k => $v) {
            $p = explode('__', $k, 2);
            Setting::updateOrCreate(
                ['key' => $p[1] ?? $k],
                ['value' => $v ?? '', 'group' => $p[0] ?? 'general']
            );
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Upload a logo file and return the path.
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::where('key', 'school_logo')->value('value');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('logo')->store('logos', 'public');
            Setting::updateOrCreate(
                ['key' => 'school_logo', 'group' => 'general'],
                ['value' => $path, 'type' => 'file', 'description' => 'School logo image']
            );

            return redirect()->back()->with('success', 'Logo uploaded successfully.');
        }

        return redirect()->back()->with('error', 'No file was uploaded.');
    }

    /**
     * Upload a favicon file and return the path.
     */
    public function uploadFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:jpeg,png,jpg,gif,svg,ico,webp|max:1024',
        ]);

        if ($request->hasFile('favicon')) {
            $oldFavicon = Setting::where('key', 'favicon')->value('value');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }

            $path = $request->file('favicon')->store('logos', 'public');
            Setting::updateOrCreate(
                ['key' => 'favicon', 'group' => 'website'],
                ['value' => $path, 'type' => 'file', 'description' => 'Website favicon image']
            );

            return redirect()->back()->with('success', 'Favicon uploaded successfully.');
        }

        return redirect()->back()->with('error', 'No file was uploaded.');
    }

    /**
     * Ensure all required settings exist in the database.
     */
    private function ensureSettingsExist(): void
    {
        $required = [
            // General
            ['key' => 'school_name',       'value' => 'School of Redemption', 'group' => 'general', 'type' => 'text',     'description' => 'Official school name displayed across the system'],
            ['key' => 'school_motto',      'value' => '',                     'group' => 'general', 'type' => 'text',     'description' => 'School motto or tagline'],
            ['key' => 'school_logo',       'value' => '',                     'group' => 'general', 'type' => 'file',     'description' => 'School logo image (upload below)'],
            ['key' => 'established_year',  'value' => '',                     'group' => 'general', 'type' => 'number',   'description' => 'Year the school was established'],
            ['key' => 'timezone',          'value' => 'Africa/Addis_Ababa',   'group' => 'general', 'type' => 'text',     'description' => 'System timezone'],

            // Website & Branding
            ['key' => 'website_url',       'value' => '',                     'group' => 'website', 'type' => 'text',     'description' => 'School website URL'],
            ['key' => 'primary_color',     'value' => '#4361ee',              'group' => 'website', 'type' => 'text',     'description' => 'Primary brand color (hex)'],
            ['key' => 'secondary_color',   'value' => '#3a0ca3',              'group' => 'website', 'type' => 'text',     'description' => 'Secondary brand color (hex)'],
            ['key' => 'favicon',           'value' => '',                     'group' => 'website', 'type' => 'file',     'description' => 'Website favicon (upload below)'],
            ['key' => 'show_branches',     'value' => '1',                    'group' => 'website', 'type' => 'boolean',  'description' => 'Show branch list on public website'],

            // Academic
            ['key' => 'grading_scale',     'value' => 'standard',             'group' => 'academic', 'type' => 'text',     'description' => 'Grading scale: standard or custom'],
            ['key' => 'pass_mark',         'value' => '40',                   'group' => 'academic', 'type' => 'number',   'description' => 'Minimum passing mark'],
            ['key' => 'max_mark',          'value' => '100',                  'group' => 'academic', 'type' => 'number',   'description' => 'Maximum marks per subject'],
            ['key' => 'auto_roll_number',  'value' => '1',                    'group' => 'academic', 'type' => 'boolean',  'description' => 'Automatically generate roll numbers'],
            ['key' => 'auto_admission',    'value' => '1',                    'group' => 'academic', 'type' => 'boolean',  'description' => 'Automatically generate admission numbers'],
            ['key' => 'calendar_type',     'value' => 'ethiopian',            'group' => 'academic', 'type' => 'text',     'description' => 'Calendar system: ethiopian or gregorian'],

            // Contact
            ['key' => 'phone',             'value' => '',                     'group' => 'contact',  'type' => 'text',     'description' => 'Primary phone number'],
            ['key' => 'email',             'value' => '',                     'group' => 'contact',  'type' => 'text',     'description' => 'Primary email address'],
            ['key' => 'address',           'value' => '',                     'group' => 'contact',  'type' => 'textarea', 'description' => 'School physical address'],
            ['key' => 'map_embed_url',     'value' => '',                     'group' => 'contact',  'type' => 'textarea', 'description' => 'Google Maps embed URL'],

            // Email Configuration
            ['key' => 'mail_driver',       'value' => 'smtp',                 'group' => 'email',    'type' => 'text',     'description' => 'Mail driver: smtp, sendmail, mailgun, etc.'],
            ['key' => 'mail_host',         'value' => '',                     'group' => 'email',    'type' => 'text',     'description' => 'SMTP host (e.g. smtp.gmail.com)'],
            ['key' => 'mail_port',         'value' => '587',                  'group' => 'email',    'type' => 'number',   'description' => 'SMTP port (587 for TLS)'],
            ['key' => 'mail_username',     'value' => '',                     'group' => 'email',    'type' => 'text',     'description' => 'SMTP username / email'],
            ['key' => 'mail_password',     'value' => '',                     'group' => 'email',    'type' => 'text',     'description' => 'SMTP password'],
            ['key' => 'mail_encryption',   'value' => 'tls',                  'group' => 'email',    'type' => 'text',     'description' => 'Encryption: tls or ssl'],
            ['key' => 'mail_from_address', 'value' => '',                     'group' => 'email',    'type' => 'text',     'description' => 'From email address'],
            ['key' => 'mail_from_name',    'value' => 'School of Redemption', 'group' => 'email',    'type' => 'text',     'description' => 'From name for emails'],

            // Social
            ['key' => 'facebook',          'value' => '',                     'group' => 'social',   'type' => 'text',     'description' => 'Facebook page URL'],
            ['key' => 'twitter',           'value' => '',                     'group' => 'social',   'type' => 'text',     'description' => 'Twitter/X profile URL'],
            ['key' => 'youtube',           'value' => '',                     'group' => 'social',   'type' => 'text',     'description' => 'YouTube channel URL'],
            ['key' => 'telegram',          'value' => '',                     'group' => 'social',   'type' => 'text',     'description' => 'Telegram channel URL'],

            // About
            ['key' => 'about_text',        'value' => '',                     'group' => 'about',    'type' => 'textarea', 'description' => 'About the school text for the public website'],
            ['key' => 'mission',           'value' => '',                     'group' => 'about',    'type' => 'textarea', 'description' => 'School mission statement'],
            ['key' => 'vision',            'value' => '',                     'group' => 'about',    'type' => 'textarea', 'description' => 'School vision statement'],

            // Appearance
            ['key' => 'dark_mode',         'value' => '0',                    'group' => 'appearance', 'type' => 'boolean', 'description' => 'Enable dark mode theme'],
            ['key' => 'compact_sidebar',   'value' => '0',                    'group' => 'appearance', 'type' => 'boolean', 'description' => 'Use compact sidebar layout'],

            // Fees
            ['key' => 'currency',          'value' => 'ETB',                  'group' => 'fees',     'type' => 'text',     'description' => 'Currency code (ETB for Ethiopian Birr)'],
            ['key' => 'currency_symbol',   'value' => 'Br',                   'group' => 'fees',     'type' => 'text',     'description' => 'Currency symbol to display'],
            ['key' => 'fee_due_day',       'value' => '10',                   'group' => 'fees',     'type' => 'number',   'description' => 'Day of month for fee due date (Ethiopian calendar: 10th)'],
            ['key' => 'late_fee_amount',   'value' => '0',                    'group' => 'fees',     'type' => 'number',   'description' => 'Late fee penalty amount'],
            ['key' => 'auto_generate_fees', 'value' => '0',                   'group' => 'fees',     'type' => 'boolean',  'description' => 'Auto-generate monthly fee records'],
            ['key' => 'fee_reminder_days', 'value' => '3',                    'group' => 'fees',     'type' => 'number',   'description' => 'Days before due date to send reminder'],

            // Finance
            ['key' => 'finance_year_start', 'value' => '',                    'group' => 'finance',  'type' => 'text',     'description' => 'Financial year start (Ethiopian: Meskerem 1)'],
            ['key' => 'finance_year_end',   'value' => '',                    'group' => 'finance',  'type' => 'text',     'description' => 'Financial year end'],
            ['key' => 'budget_approval',    'value' => '0',                   'group' => 'finance',  'type' => 'boolean',  'description' => 'Require approval for budget entries'],
            ['key' => 'expense_categories', 'value' => 'Salary,Utilities,Rent,Maintenance,Supplies,Transport,Events,Other', 'group' => 'finance', 'type' => 'textarea', 'description' => 'Comma-separated expense categories'],
            ['key' => 'income_categories',  'value' => 'Tuition,Donation,Registration,Other', 'group' => 'finance', 'type' => 'textarea', 'description' => 'Comma-separated income categories'],
        ];

        foreach ($required as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key'], 'group' => $setting['group']],
                [
                    'value'       => $setting['value'],
                    'type'        => $setting['type'],
                    'description' => $setting['description'],
                ]
            );
        }
    }
}
