@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">⚙️ System Settings</h1>
        <p class="text-sm text-gray-500">Branding, invoices, and access settings for the whole system</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6 max-w-2xl">
        @csrf
        @method('PUT')

        <!-- Branding -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Branding</h2>
            <p class="text-sm text-gray-500 mb-4">
                Your business name, logo, and contact details. These appear in the sidebar, the browser tab, and on
                printed sales/purchase invoices.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}"
                           placeholder="e.g. Al-Noor Medical Gases"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="company_address" rows="2"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('company_address', $settings['company_address']) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}"
                           placeholder="e.g. +92 300 1234567"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email']) }}"
                           placeholder="e.g. info@yourcompany.com"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                    <input type="text" name="company_website" value="{{ old('company_website', $settings['company_website']) }}"
                           placeholder="e.g. www.yourcompany.com"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax Number (NTN / GST)</label>
                    <input type="text" name="company_tax_number" value="{{ old('company_tax_number', $settings['company_tax_number']) }}"
                           placeholder="e.g. NTN: 1234567-8"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Printed on invoices if filled in.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-16 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                            @if($settings['company_logo_url'])
                                <img src="{{ $settings['company_logo_url'] }}" alt="Logo" class="w-full h-full object-contain">
                            @else
                                <i class="fas fa-image text-gray-300 text-xl"></i>
                            @endif
                        </div>
                        <input type="file" name="company_logo" accept="image/*"
                               class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Shown in the sidebar and on printed invoices. PNG/JPG, up to 2MB.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Favicon</label>
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-16 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                            @if($settings['favicon_url'])
                                <img src="{{ $settings['favicon_url'] }}" alt="Favicon" class="w-8 h-8 object-contain">
                            @else
                                <i class="fas fa-star text-gray-300 text-xl"></i>
                            @endif
                        </div>
                        <input type="file" name="favicon" accept="image/*"
                               class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Shown in the browser tab. Square image, up to 512KB.</p>
                </div>
            </div>
        </div>

        <!-- Security & Access -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Security &amp; Access</h2>

            <div class="flex items-start justify-between py-4 border-t border-gray-200">
                <div class="pr-6">
                    <p class="font-medium text-gray-900">Allow Public Registration</p>
                    <p class="text-sm text-gray-500 mt-1">
                        When off, the <code class="bg-gray-100 px-1 rounded">/register</code> page is disabled and
                        the only way to create a new account is from the Users page. Recommended: off for an
                        internal system that handles financial data.
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                    <input type="checkbox" name="allow_registration" value="1"
                           {{ $settings['allow_registration'] ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-blue-600 transition-colors"></div>
                    <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5"></div>
                </label>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                <i class="fas fa-save mr-2"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
