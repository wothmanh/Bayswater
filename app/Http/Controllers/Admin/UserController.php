<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AgentSetting;
use App\Models\Region; // Import Region
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $roles = ['admin' => 'Admin', 'agent' => 'Agent', 'calculator' => 'Calculator'];
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', ValidationRule::in(['admin', 'agent', 'calculator'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // If the created user is an Agent, optionally create/update AgentSetting
        if ($validated['role'] === 'agent') {
            $agentData = $request->validate([
                'brand_display_name' => ['nullable', 'string', 'max:255'],
                'contact_email' => ['nullable', 'email', 'max:255'],
                'contact_phone' => ['nullable', 'string', 'max:255'],
                'contact_whatsapp' => ['nullable', 'string', 'max:255'],
                // Accept JPG/PNG up to 1MB; support alias field names as well
                'brand_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:1024'],
                'agent_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:1024'],
                'agent_email' => ['nullable', 'email', 'max:255'],
                'agent_phone' => ['nullable', 'string', 'max:255'],
                'agent_whatsapp' => ['nullable', 'string', 'max:255'],
            ]);

            $settings = $user->agentSetting()->firstOrCreate(['user_id' => $user->id]);
            $settings->fill([
                'brand_display_name' => $agentData['brand_display_name'] ?? $settings->brand_display_name,
                'contact_email' => ($agentData['contact_email'] ?? $agentData['agent_email'] ?? $settings->contact_email),
                'contact_phone' => ($agentData['contact_phone'] ?? $agentData['agent_phone'] ?? $settings->contact_phone),
                'contact_whatsapp' => ($agentData['contact_whatsapp'] ?? $agentData['agent_whatsapp'] ?? $settings->contact_whatsapp),
            ]);

            if ($request->hasFile('brand_logo')) {
                $path = $request->file('brand_logo')->store('agents', 'public');
                $settings->brand_logo_path = $path;
            } elseif ($request->hasFile('agent_logo')) {
                $path = $request->file('agent_logo')->store('agents', 'public');
                $settings->brand_logo_path = $path;
            }

            $settings->save();
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $roles = ['admin' => 'Admin', 'agent' => 'Agent', 'calculator' => 'Calculator'];
        $regions = Region::orderBy('order')->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles', 'regions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', ValidationRule::in(['admin', 'agent', 'calculator'])],
            'regions' => ['nullable', 'array'],
            'regions.*' => ['exists:regions,id'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // Sync regions
        $user->regions()->sync($request->input('regions', []));

        if ($validated['role'] === 'agent') {
            $agentData = $request->validate([
                'brand_display_name' => ['nullable', 'string', 'max:255'],
                'contact_email' => ['nullable', 'email', 'max:255'],
                'contact_phone' => ['nullable', 'string', 'max:255'],
                'contact_whatsapp' => ['nullable', 'string', 'max:255'],
                // Enforce JPG/PNG only and max 1MB
                'brand_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:1024'],
            ]);

            $settings = $user->agentSetting()->firstOrCreate(['user_id' => $user->id]);
            $settings->fill([
                'brand_display_name' => $agentData['brand_display_name'] ?? $settings->brand_display_name,
                'contact_email' => $agentData['contact_email'] ?? $settings->contact_email,
                'contact_phone' => $agentData['contact_phone'] ?? $settings->contact_phone,
                'contact_whatsapp' => $agentData['contact_whatsapp'] ?? $settings->contact_whatsapp,
            ]);

            if ($request->hasFile('brand_logo')) {
                if ($settings->brand_logo_path && Storage::disk('public')->exists($settings->brand_logo_path)) {
                    Storage::disk('public')->delete($settings->brand_logo_path);
                }
                // Store in /storage/app/public/agents with hashed filename
                $path = $request->file('brand_logo')->store('agents', 'public');
                $settings->brand_logo_path = $path;
            }

            $settings->save();
        }

        return redirect()->route('admin.users.edit', $user)->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('admin.users.index');
            // ->with('success', 'User deleted successfully.');
    }

    /**
     * Remove only the agent's branding logo without deleting the user.
     */
    public function removeAgentLogo(User $user)
    {
        // Guard: ensure user has agent setting
        $setting = $user->agentSetting;
        if (!$setting) {
            return back()->with('error', 'No agent settings found for this user.');
        }

        // Delete stored file if exists
        if ($setting->brand_logo_path && Storage::disk('public')->exists($setting->brand_logo_path)) {
            Storage::disk('public')->delete($setting->brand_logo_path);
        }

        // Clear DB field
        $setting->brand_logo_path = null;
        $setting->save();

        return back()->with('success', 'Logo removed successfully.');
    }
}
