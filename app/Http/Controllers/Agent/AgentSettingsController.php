<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AgentSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $settings = AgentSetting::firstOrCreate(['user_id' => $user->id]);
        return view('agent.settings.index', [
            'settings' => $settings,
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'brand_display_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'contact_whatsapp' => ['nullable', 'string', 'max:255'],
            // Enforce JPG/PNG only and max 1MB
            'brand_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:1024'],
        ]);

        $settings = AgentSetting::firstOrCreate(['user_id' => $user->id]);
        $settings->fill([
            'brand_display_name' => $validated['brand_display_name'] ?? $settings->brand_display_name,
            'contact_email' => $validated['contact_email'] ?? $settings->contact_email,
            'contact_phone' => $validated['contact_phone'] ?? $settings->contact_phone,
            'contact_whatsapp' => $validated['contact_whatsapp'] ?? $settings->contact_whatsapp,
        ]);

        if ($request->hasFile('brand_logo')) {
            // Delete old logo if present
            if ($settings->brand_logo_path && Storage::disk('public')->exists($settings->brand_logo_path)) {
                Storage::disk('public')->delete($settings->brand_logo_path);
            }
            // Store in /storage/app/public/agents with hashed filename
            $path = $request->file('brand_logo')->store('agents', 'public');
            $settings->brand_logo_path = $path;
        }

        $settings->save();

        return redirect()->route('agent.settings.index')->with('success', 'Agent settings updated.');
    }

    public function removeLogo()
    {
        $user = Auth::user();
        $settings = AgentSetting::firstOrCreate(['user_id' => $user->id]);
        if ($settings->brand_logo_path && Storage::disk('public')->exists($settings->brand_logo_path)) {
            Storage::disk('public')->delete($settings->brand_logo_path);
        }
        $settings->brand_logo_path = null;
        $settings->save();

        return redirect()->route('agent.settings.index')->with('success', 'Logo removed successfully.');
    }
}