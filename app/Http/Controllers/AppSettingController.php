<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = $request->input('settings', []);

        foreach ($settings as $key => $value) {
            $setting = AppSetting::where('key', $key)->first();

            if ($setting) {
                // Handle boolean checkboxes
                if ($setting->type === 'boolean') {
                    $value = isset($value) && $value ? 'true' : 'false';
                }

                $setting->value = $value;
                $setting->save();
            }
        }

        AppSetting::clearCache();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Settings have been saved.']);
        }

        return redirect()->route('settings.index')
            ->with('status', 'Settings have been saved.');
    }
}
