<?php

namespace App\Http\Controllers\Slider;

use App\Http\Controllers\Controller;
use App\Models\SliderAlert;
use Illuminate\Http\Request;

class SliderAlertController extends Controller
{
    public function index(Request $r)
    {
        $q = SliderAlert::query();
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('message', 'LIKE', "%$s%");
        }
        $data = $q->orderBy('sort_order')->paginate(20);
        $totalAlerts = SliderAlert::count();
        $activeAlerts = SliderAlert::where('is_active', true)->count();
        $inactiveAlerts = SliderAlert::where('is_active', false)->count();

        return view('admin.SliderAlert.index', compact('data', 'totalAlerts', 'activeAlerts', 'inactiveAlerts'));
    }

    public function create()
    {
        $typeStyles = SliderAlert::getTypeStyles();
        return view('admin.SliderAlert.create', compact('typeStyles'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'message'    => 'required|string|max:500',
            'icon'       => 'nullable|string|max:100',
            'type'       => 'required|string|in:info,success,warning,danger,primary',
            'bg_color'   => 'required|string|max:20',
            'text_color' => 'required|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data = $r->only(['message', 'icon', 'type', 'bg_color', 'text_color', 'sort_order']);
        $data['is_active'] = $r->has('is_active') ? 1 : 0;

        // Default icon based on type if not provided
        if (empty($data['icon'])) {
            $typeStyles = SliderAlert::getTypeStyles();
            $data['icon'] = $typeStyles[$data['type']]['icon'] ?? 'fa-bullhorn';
        }

        SliderAlert::create($data);

        return redirect()->route('admin.slider-alerts.index')
            ->with('success', 'Alert message created successfully.');
    }

    public function edit(SliderAlert $sliderAlert)
    {
        $typeStyles = SliderAlert::getTypeStyles();
        return view('admin.SliderAlert.edit', ['item' => $sliderAlert, 'typeStyles' => $typeStyles]);
    }

    public function update(Request $r, SliderAlert $sliderAlert)
    {
        $r->validate([
            'message'    => 'required|string|max:500',
            'icon'       => 'nullable|string|max:100',
            'type'       => 'required|string|in:info,success,warning,danger,primary',
            'bg_color'   => 'required|string|max:20',
            'text_color' => 'required|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data = $r->only(['message', 'icon', 'type', 'bg_color', 'text_color', 'sort_order']);
        $data['is_active'] = $r->has('is_active') ? 1 : 0;

        if (empty($data['icon'])) {
            $typeStyles = SliderAlert::getTypeStyles();
            $data['icon'] = $typeStyles[$data['type']]['icon'] ?? 'fa-bullhorn';
        }

        $sliderAlert->update($data);

        return redirect()->route('admin.slider-alerts.index')
            ->with('success', 'Alert message updated successfully.');
    }

    public function destroy(SliderAlert $sliderAlert)
    {
        $sliderAlert->delete();
        return back()->with('success', 'Alert message deleted successfully.');
    }

    /**
     * Toggle alert active/inactive status via AJAX
     */
    public function toggle(SliderAlert $sliderAlert)
    {
        $sliderAlert->update(['is_active' => !$sliderAlert->is_active]);
        return response()->json([
            'success' => true,
            'is_active' => $sliderAlert->is_active,
            'message' => $sliderAlert->is_active ? 'Alert activated.' : 'Alert deactivated.',
        ]);
    }
}
