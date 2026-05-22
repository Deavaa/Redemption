<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\SystemModule;
use Illuminate\Http\Request;

class SystemModuleController extends Controller
{
    public function index()
    {
        $modules = SystemModule::orderBy('group')->orderBy('sort_order')->get();
        $groups = SystemModule::getGroups();

        // Auto-seed if empty
        if ($modules->count() === 0) {
            foreach (SystemModule::defaultModules() as $mod) {
                SystemModule::create(array_merge($mod, ['is_enabled' => true, 'description' => '']));
            }
            $modules = SystemModule::orderBy('group')->orderBy('sort_order')->get();
        }

        $groupedModules = $modules->groupBy('group');

        return view('admin.System.modules', compact('groupedModules', 'groups'));
    }

    public function toggle(Request $request, SystemModule $systemModule)
    {
        $systemModule->update([
            'is_enabled' => !$systemModule->is_enabled,
        ]);

        $status = $systemModule->is_enabled ? 'enabled' : 'disabled';
        return redirect()->route('admin.system-modules.index')
            ->with('success', "Module '{$systemModule->name}' has been {$status}.");
    }

    public function updateAll(Request $request)
    {
        $modules = $request->input('modules', []);

        foreach ($modules as $id => $data) {
            $module = SystemModule::find($id);
            if ($module) {
                $module->update([
                    'is_enabled' => isset($data['is_enabled']),
                ]);
            }
        }

        return redirect()->route('admin.system-modules.index')
            ->with('success', 'Module settings updated successfully.');
    }
}
