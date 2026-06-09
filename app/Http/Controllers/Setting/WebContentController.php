<?php
namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebContentController extends Controller
{
    /**
     * Default settings that should exist in the database.
     * If any are missing, they'll be auto-created so the admin form always shows them.
     */
    protected static $defaultSettings = [
        // Programs
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
        // Why Choose Us
        ['key' => 'wcu_section_title', 'value' => 'Why Choose Us', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Section title'],
        ['key' => 'wcu_section_subtitle', 'value' => 'What makes our school exceptional', 'group' => 'why_choose_us', 'type' => 'text', 'description' => 'Section subtitle'],
        // Homepage Stats
        ['key' => 'stat_students', 'value' => '1500+', 'group' => 'academic', 'type' => 'text', 'description' => 'Students count display'],
        ['key' => 'stat_teachers', 'value' => '120+', 'group' => 'academic', 'type' => 'text', 'description' => 'Teachers count display'],
        ['key' => 'stat_years', 'value' => '25+', 'group' => 'academic', 'type' => 'text', 'description' => 'Years of excellence display'],
        ['key' => 'stat_pass_rate', 'value' => '98%', 'group' => 'academic', 'type' => 'text', 'description' => 'Pass rate display'],
        // About
        ['key' => 'about_description', 'value' => '', 'group' => 'about', 'type' => 'textarea', 'description' => 'About page description'],
        ['key' => 'about_mission', 'value' => '', 'group' => 'about', 'type' => 'textarea', 'description' => 'Mission statement'],
        ['key' => 'about_vision', 'value' => '', 'group' => 'about', 'type' => 'textarea', 'description' => 'Vision statement'],
    ];

    public function index()
    {
        // Auto-seed missing settings so the form always shows all fields
        foreach (self::$defaultSettings as $default) {
            Setting::firstOrCreate(
                ['key' => $default['key']],
                [
                    'value' => $default['value'],
                    'group' => $default['group'],
                    'type' => $default['type'],
                    'description' => $default['description'],
                ]
            );
        }

        $groups = [
            'general' => Setting::where('group', 'general')->get(),
            'contact' => Setting::where('group', 'contact')->get(),
            'academic' => Setting::where('group', 'academic')->get(),
            'about' => Setting::where('group', 'about')->get(),
            'why_choose_us' => Setting::where('group', 'why_choose_us')->get(),
            'programs' => Setting::where('group', 'programs')->get(),
            'website' => Setting::where('group', 'website')->get(),
            'social' => Setting::where('group', 'social')->get(),
            'appearance' => Setting::where('group', 'appearance')->get(),
        ];
        $groupLabels = [
            'general' => 'General Settings',
            'contact' => 'Contact Information',
            'academic' => 'Homepage Stats',
            'about' => 'About Page Content',
            'why_choose_us' => 'Why Choose Us Section',
            'programs' => 'Academic Programs Section',
            'website' => 'Website Content',
            'social' => 'Social Media Links',
            'appearance' => 'Appearance & Branding',
        ];
        return view('admin.web-content.index', compact('groups', 'groupLabels'));
    }

    public function update(Request $request)
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            if (str_starts_with($key, 'setting_')) {
                $settingKey = substr($key, 8);
                Setting::updateOrCreate(
                    ['key' => $settingKey],
                    ['value' => $value ?? '']
                );
            }
        }
        return redirect()->route('admin.web-content.index')->with('success', 'Web content updated successfully.');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120',
            'setting_key' => 'required|string',
        ]);

        $key = $request->setting_key;
        $old = Setting::get($key);
        if ($old) {
            Storage::disk('public')->delete($old);
            // Also delete from public/storage fallback
            $oldPublicPath = public_path('storage/' . $old);
            if (file_exists($oldPublicPath)) unlink($oldPublicPath);
        }

        // Determine the correct group from the existing setting, default to 'appearance'
        $existingSetting = Setting::where('key', $key)->first();
        $group = $existingSetting ? $existingSetting->group : 'appearance';

        $path = $request->file('file')->store('settings', 'public');
        Setting::updateOrCreate(['key' => $key], ['value' => $path, 'group' => $group]);

        // Copy to public/storage fallback
        try {
            $sourcePath = Storage::disk('public')->path($path);
            $destPath = public_path('storage/' . $path);
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) mkdir($destDir, 0755, true);
            if (file_exists($sourcePath)) copy($sourcePath, $destPath);
        } catch (\Exception $e) {
            \Log::warning('Failed to copy to public storage: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'path' => $path, 'url' => asset('storage/' . $path)]);
    }
}
