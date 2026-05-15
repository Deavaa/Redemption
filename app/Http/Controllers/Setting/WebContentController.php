<?php
namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebContentController extends Controller
{
    public function index()
    {
        $groups = [
            'general' => Setting::where('group', 'general')->get(),
            'contact' => Setting::where('group', 'contact')->get(),
            'about' => Setting::where('group', 'about')->get(),
            'website' => Setting::where('group', 'website')->get(),
            'social' => Setting::where('group', 'social')->get(),
            'appearance' => Setting::where('group', 'appearance')->get(),
        ];
        $groupLabels = [
            'general' => 'General Settings',
            'contact' => 'Contact Information',
            'about' => 'About Page Content',
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

        $path = $request->file('file')->store('settings', 'public');
        Setting::updateOrCreate(['key' => $key], ['value' => $path, 'group' => 'appearance']);

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
