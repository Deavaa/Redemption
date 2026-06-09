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
        ['key' => 'programs_count', 'value' => '4', 'group' => 'programs', 'type' => 'text', 'description' => 'Number of programs'],
        ['key' => 'programs_section_title', 'value' => 'Pathways to Success', 'group' => 'programs', 'type' => 'text', 'description' => 'Programs section title'],
        ['key' => 'programs_section_subtitle', 'value' => 'Our comprehensive curriculum is designed to challenge and inspire students at every stage of their educational journey.', 'group' => 'programs', 'type' => 'textarea', 'description' => 'Programs section subtitle'],
        ['key' => 'program_1_visible', 'value' => '1', 'group' => 'programs', 'type' => 'boolean', 'description' => 'Program 1 visible'],
        ['key' => 'program_1_image', 'value' => '', 'group' => 'programs', 'type' => 'file', 'description' => 'Program 1 image (upload)'],
        ['key' => 'program_1_tag', 'value' => 'Ages 3-5', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 1 tag/label'],
        ['key' => 'program_1_title', 'value' => 'Early Childhood Education', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 1 title'],
        ['key' => 'program_1_description', 'value' => 'A nurturing environment where young learners develop foundational skills through play-based learning and creative exploration.', 'group' => 'programs', 'type' => 'textarea', 'description' => 'Program 1 description'],
        ['key' => 'program_2_visible', 'value' => '1', 'group' => 'programs', 'type' => 'boolean', 'description' => 'Program 2 visible'],
        ['key' => 'program_2_image', 'value' => '', 'group' => 'programs', 'type' => 'file', 'description' => 'Program 2 image (upload)'],
        ['key' => 'program_2_tag', 'value' => 'Grades 1-8', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 2 tag/label'],
        ['key' => 'program_2_title', 'value' => 'Primary & Middle School', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 2 title'],
        ['key' => 'program_2_description', 'value' => 'Building strong academic foundations while fostering curiosity, critical thinking, and social-emotional development.', 'group' => 'programs', 'type' => 'textarea', 'description' => 'Program 2 description'],
        ['key' => 'program_3_visible', 'value' => '1', 'group' => 'programs', 'type' => 'boolean', 'description' => 'Program 3 visible'],
        ['key' => 'program_3_image', 'value' => '', 'group' => 'programs', 'type' => 'file', 'description' => 'Program 3 image (upload)'],
        ['key' => 'program_3_tag', 'value' => 'Grades 9-12', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 3 tag/label'],
        ['key' => 'program_3_title', 'value' => 'High School', 'group' => 'programs', 'type' => 'text', 'description' => 'Program 3 title'],
        ['key' => 'program_3_description', 'value' => 'Rigorous college-preparatory curriculum with advanced placement courses and specialized tracks in sciences, arts, and humanities.', 'group' => 'programs', 'type' => 'textarea', 'description' => 'Program 3 description'],
        ['key' => 'program_4_visible', 'value' => '1', 'group' => 'programs', 'type' => 'boolean', 'description' => 'Program 4 visible'],
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

        // Ensure visible toggles exist for all current programs
        $programsCount = (int) Setting::get('programs_count', 4);
        for ($i = 1; $i <= $programsCount; $i++) {
            Setting::firstOrCreate(
                ['key' => "program_{$i}_visible"],
                ['value' => '1', 'group' => 'programs', 'type' => 'boolean', 'description' => "Program {$i} visible"]
            );
            Setting::firstOrCreate(
                ['key' => "program_{$i}_image"],
                ['value' => '', 'group' => 'programs', 'type' => 'file', 'description' => "Program {$i} image (upload)"]
            );
            Setting::firstOrCreate(
                ['key' => "program_{$i}_tag"],
                ['value' => '', 'group' => 'programs', 'type' => 'text', 'description' => "Program {$i} tag/label"]
            );
            Setting::firstOrCreate(
                ['key' => "program_{$i}_title"],
                ['value' => '', 'group' => 'programs', 'type' => 'text', 'description' => "Program {$i} title"]
            );
            Setting::firstOrCreate(
                ['key' => "program_{$i}_description"],
                ['value' => '', 'group' => 'programs', 'type' => 'textarea', 'description' => "Program {$i} description"]
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

        $programsCount = (int) Setting::get('programs_count', 4);

        return view('admin.web-content.index', compact('groups', 'groupLabels', 'programsCount'));
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

    /**
     * Add a new program slot
     */
    public function addProgram()
    {
        $programsCount = (int) Setting::get('programs_count', 4);
        $newIndex = $programsCount + 1;

        // Create all settings for the new program
        Setting::updateOrCreate(['key' => "program_{$newIndex}_visible"], ['value' => '1', 'group' => 'programs', 'type' => 'boolean', 'description' => "Program {$newIndex} visible"]);
        Setting::updateOrCreate(['key' => "program_{$newIndex}_image"], ['value' => '', 'group' => 'programs', 'type' => 'file', 'description' => "Program {$newIndex} image (upload)"]);
        Setting::updateOrCreate(['key' => "program_{$newIndex}_tag"], ['value' => '', 'group' => 'programs', 'type' => 'text', 'description' => "Program {$newIndex} tag/label"]);
        Setting::updateOrCreate(['key' => "program_{$newIndex}_title"], ['value' => 'New Program', 'group' => 'programs', 'type' => 'text', 'description' => "Program {$newIndex} title"]);
        Setting::updateOrCreate(['key' => "program_{$newIndex}_description"], ['value' => '', 'group' => 'programs', 'type' => 'textarea', 'description' => "Program {$newIndex} description"]);

        // Update count
        Setting::updateOrCreate(['key' => 'programs_count'], ['value' => (string) $newIndex, 'group' => 'programs', 'type' => 'text', 'description' => 'Number of programs']);

        return redirect()->route('admin.web-content.index')->with('success', "Program {$newIndex} added. Edit the details below.");
    }

    /**
     * Remove the last program slot
     */
    public function removeProgram()
    {
        $programsCount = (int) Setting::get('programs_count', 4);

        if ($programsCount <= 1) {
            return redirect()->route('admin.web-content.index')->with('error', 'You must have at least 1 program.');
        }

        // Delete all settings for the last program
        $index = $programsCount;
        Setting::where('key', "program_{$index}_visible")->delete();
        Setting::where('key', "program_{$index}_image")->delete();
        Setting::where('key', "program_{$index}_tag")->delete();
        Setting::where('key', "program_{$index}_title")->delete();
        Setting::where('key', "program_{$index}_description")->delete();

        // Update count
        Setting::updateOrCreate(['key' => 'programs_count'], ['value' => (string) ($programsCount - 1), 'group' => 'programs', 'type' => 'text', 'description' => 'Number of programs']);

        return redirect()->route('admin.web-content.index')->with('success', "Program {$index} removed.");
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
