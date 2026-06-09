<?php

namespace App\Http\Controllers;

use App\Models\AgentSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AgentBrandLogoController extends Controller
{
    public function show(AgentSetting $agentSetting): BinaryFileResponse
    {
        $user = Auth::user();

        if (!$user || (!$user->isAdmin() && $agentSetting->user_id !== $user->id)) {
            abort(403);
        }

        if (!$agentSetting->brand_logo_path || !Storage::disk('public')->exists($agentSetting->brand_logo_path)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($agentSetting->brand_logo_path);
        $mimeType = Storage::disk('public')->mimeType($agentSetting->brand_logo_path) ?: 'image/jpeg';

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
