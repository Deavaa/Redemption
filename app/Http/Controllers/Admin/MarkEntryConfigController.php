<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarkEntryConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MarkEntryConfigController extends Controller
{
    /**
     * Display the mark entry configuration page
     */
    public function index()
    {
        // Seed defaults if no config exists
        if (MarkEntryConfig::count() === 0) {
            MarkEntryConfig::seedDefaults();
        }

        $configs = MarkEntryConfig::orderBy('category')->orderBy('sort_order')->get();

        // Group by category
        $grouped = $configs->groupBy('category');

        // Parse JSON values for display
        foreach ($configs as $config) {
            if ($config->type === 'json') {
                $config->parsed_value = json_decode($config->value, true);
            }
        }

        return view('admin.mark-entry-configs.index', compact('grouped', 'configs'));
    }

    /**
     * Update the configuration
     */
    public function update(Request $request)
    {
        $data = $request->all();

        // Update simple settings (text, number, boolean)
        $simpleFields = ['ca_weight', 'exam_weight', 'rounding_precision', 'input_precision', 'pass_mark'];
        foreach ($simpleFields as $field) {
            if (isset($data[$field])) {
                MarkEntryConfig::updateOrCreate(
                    ['name' => $field],
                    ['value' => $data[$field]]
                );
            }
        }

        // Update mark fields (JSON)
        if (isset($data['mark_fields_json'])) {
            $markFields = json_decode($data['mark_fields_json'], true);
            if (is_array($markFields)) {
                MarkEntryConfig::updateOrCreate(
                    ['name' => 'mark_fields'],
                    ['value' => json_encode($markFields)]
                );
            }
        }

        // Update grade scale (JSON)
        if (isset($data['grade_scale_json'])) {
            $gradeScale = json_decode($data['grade_scale_json'], true);
            if (is_array($gradeScale)) {
                MarkEntryConfig::updateOrCreate(
                    ['name' => 'grade_scale'],
                    ['value' => json_encode($gradeScale)]
                );
            }
        }

        // Clear cache
        MarkEntryConfig::clearCache();

        return redirect()->route('admin.mark-entry-configs.index')
            ->with('success', 'Mark entry configuration updated successfully.');
    }

    /**
     * Reset to defaults
     */
    public function reset()
    {
        MarkEntryConfig::query()->delete();
        MarkEntryConfig::seedDefaults();
        MarkEntryConfig::clearCache();

        return redirect()->route('admin.mark-entry-configs.index')
            ->with('success', 'Mark entry configuration reset to defaults.');
    }

    /**
     * Get config as JSON for API/frontend
     */
    public function apiGetConfig()
    {
        return response()->json(MarkEntryConfig::getFrontendConfig());
    }
}
