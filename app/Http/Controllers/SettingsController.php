<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'allow_registration' => Setting::bool('allow_registration', false),
            'company_name' => Setting::get('company_name', config('app.name', 'ERP System')),
            'company_address' => Setting::get('company_address'),
            'company_phone' => Setting::get('company_phone'),
            'company_email' => Setting::get('company_email'),
            'company_tax_number' => Setting::get('company_tax_number'),
            'company_website' => Setting::get('company_website'),
            'company_logo_url' => Setting::url('company_logo'),
            'favicon_url' => Setting::url('favicon'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'allow_registration' => 'nullable|boolean',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_tax_number' => 'nullable|string|max:100',
            'company_website' => 'nullable|string|max:255',
            'company_logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:512',
        ]);

        Setting::set('allow_registration', $request->boolean('allow_registration') ? '1' : '0');
        Setting::setOrForget('company_name', $validated['company_name'] ?? null);
        Setting::setOrForget('company_address', $validated['company_address'] ?? null);
        Setting::setOrForget('company_phone', $validated['company_phone'] ?? null);
        Setting::setOrForget('company_email', $validated['company_email'] ?? null);
        Setting::setOrForget('company_tax_number', $validated['company_tax_number'] ?? null);
        Setting::setOrForget('company_website', $validated['company_website'] ?? null);

        $this->storeUpload($request, 'company_logo', 'branding');
        $this->storeUpload($request, 'favicon', 'branding');

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully!');
    }

    /**
     * Store an uploaded file for a given setting key, replacing whatever it
     * previously pointed to. No-op if the field wasn't submitted this time.
     */
    private function storeUpload(Request $request, string $field, string $directory): void
    {
        if (! $request->hasFile($field)) {
            return;
        }

        $oldPath = Setting::get($field);
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file($field)->store($directory, 'public');
        Setting::set($field, $path);
    }
}
