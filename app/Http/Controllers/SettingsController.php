<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'allow_registration' => Setting::bool('allow_registration', false),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        Setting::set('allow_registration', $request->boolean('allow_registration') ? '1' : '0');

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully!');
    }
}
