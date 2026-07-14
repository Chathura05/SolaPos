<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function store(Request $request)
    {
        $data = $request->except('_token', 'company_logo');
        
        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $request->validate([
                'company_logo' => 'image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            ]);

            // Delete old logo if exists
            $oldLogo = setting('company_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('company_logo')->store('logos', 'public');
            $data['company_logo'] = $path;
        }

        // Handle logo removal
        if ($request->has('remove_logo') && $request->remove_logo) {
            $oldLogo = setting('company_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $data['company_logo'] = '';
        }

        setting($data);

        return back()->with('success', 'Settings updated successfully.');
    }
}
