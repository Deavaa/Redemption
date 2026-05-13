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
        $settings = Setting::all()->groupBy('group');
        $groupLabels = [
            'general' => 'General Settings',
            'academic' => 'Academic Settings',
            'contact' => 'Contact Information',
            'social' => 'Social Media Links',
            'about' => 'About Page Content',
            'appearance' => 'Appearance',
            'email' => 'Email Settings',
            'fees' => 'Fee Settings',
            'website' => 'Website Settings',
        ];
        return view('admin.settings', compact('settings', 'groupLabels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'group' => 'required|string',
            'key' => 'required|string|regex:/^[a-z0-9_]+$/',
            'value' => 'nullable|string',
            'type' => 'required|in:text,number,boolean,textarea,file,json',
            'description' => 'nullable|string',
        ]);

        // Check if key already exists in this group
        $exists = Setting::where('key', $data['key'])->where('group', $data['group'])->exists();
        if ($exists) {
            return redirect()->back()->with('error', "Setting '{$data['key']}' already exists in group '{$data['group']}'.");
        }

        Setting::create($data);

        return redirect()->route('admin.settings.index')->with('success', 'Setting added successfully.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($data['settings'] as $k => $v) {
            $p = explode('__', $k, 2);
            $key = $p[1] ?? $k;
            $group = $p[0] ?? 'general';
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $v ?? '', 'group' => $group]
            );
        }

        // Handle file uploads in the same form
        foreach ($request->allFiles() as $fileKey => $file) {
            if (str_starts_with($fileKey, 'file_')) {
                $settingKey = substr($fileKey, 5); // remove 'file_' prefix
                $p = explode('__', $settingKey, 2);
                $key = $p[1] ?? $settingKey;
                $group = $p[0] ?? 'general';

                $path = $file->store('settings', 'public');
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $path, 'group' => $group]
                );
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:2048|mimes:png,jpg,jpeg,svg,webp',
        ]);

        // Delete old logo if exists
        $oldLogo = Setting::get('school_logo');
        if ($oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        $path = $request->file('logo')->store('settings', 'public');
        Setting::updateOrCreate(
            ['key' => 'school_logo'],
            ['value' => $path, 'group' => 'appearance']
        );

        return redirect()->back()->with('success', 'School logo uploaded successfully.');
    }

    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);

        // Delete file if it's a file type
        if ($setting->type === 'file' && $setting->value) {
            Storage::disk('public')->delete($setting->value);
        }

        $setting->delete();

        return redirect()->back()->with('success', 'Setting deleted successfully.');
    }
}
