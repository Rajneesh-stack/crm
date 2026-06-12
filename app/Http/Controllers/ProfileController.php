<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadComment;
use App\Models\LeadFollowup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        $stats = [
            'total_leads'      => Lead::where('assigned_to', $user->id)->count(),
            'converted'        => Lead::where('assigned_to', $user->id)->where('status', 'converted')->count(),
            'today_leads'      => Lead::where('assigned_to', $user->id)->whereDate('created_at', today())->count(),
            'today_followups'  => LeadFollowup::where('user_id', $user->id)->whereDate('scheduled_date', today())->where('status','pending')->count(),
            'overdue'          => LeadFollowup::where('user_id', $user->id)->whereDate('scheduled_date', '<', today())->where('status','pending')->count(),
            'comments_made'    => LeadComment::where('user_id', $user->id)->count(),
        ];

        return view('profile.show', compact('user', 'stats'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar'=> ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($data)->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function avatar(User $user)
    {
        if (!$user->avatar) {
            abort(404, 'No avatar set for user.');
        }

        // Strip any path traversal; allow only safe basename
        $safe = basename($user->avatar);

        // Collect candidate paths (Storage facade + direct paths) — first hit wins
        $candidates = [];
        foreach (['public', 'local'] as $disk) {
            try {
                if (Storage::disk($disk)->exists('avatars/'.$safe)) {
                    $candidates[] = Storage::disk($disk)->path('avatars/'.$safe);
                }
            } catch (\Throwable $e) {}
        }
        foreach ([
            storage_path('app/public/avatars/'.$safe),
            storage_path('app/avatars/'.$safe),
            storage_path('app/private/avatars/'.$safe),
            public_path('storage/avatars/'.$safe),
            public_path('uploads/avatars/'.$safe),
            base_path('storage/app/public/avatars/'.$safe),
        ] as $p) {
            $candidates[] = $p;
        }

        foreach (array_unique($candidates) as $path) {
            if (is_file($path) && is_readable($path)) {
                // Use streaming readfile — bypasses BinaryFileResponse / X-Sendfile quirks
                // that can cause 404s on some shared hosting setups.
                $mime = function_exists('mime_content_type') ? @mime_content_type($path) : null;
                if (!$mime) {
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    $mime = match ($ext) {
                        'png'  => 'image/png',
                        'gif'  => 'image/gif',
                        'webp' => 'image/webp',
                        'svg'  => 'image/svg+xml',
                        default => 'image/jpeg',
                    };
                }

                return response()->stream(function () use ($path) {
                    @readfile($path);
                }, 200, [
                    'Content-Type'   => $mime,
                    'Content-Length' => (string) filesize($path),
                    'Cache-Control'  => 'public, max-age=86400',
                ]);
            }
        }

        abort(404, 'Avatar not found: '.$safe);
    }

    // Diagnostic — only enabled for admins. Visit /profile/avatar-diag to see where prod stores files.
    public function avatarDiag()
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403);
        }

        $user = auth()->user();
        $safe = $user->avatar ? basename($user->avatar) : 'NO_AVATAR_IN_DB';

        $checks = [];
        $paths = [
            'storage/app/public/avatars/'  => storage_path('app/public/avatars/'.$safe),
            'storage/app/avatars/'         => storage_path('app/avatars/'.$safe),
            'storage/app/private/avatars/' => storage_path('app/private/avatars/'.$safe),
            'public/storage/avatars/'      => public_path('storage/avatars/'.$safe),
            'public/uploads/avatars/'      => public_path('uploads/avatars/'.$safe),
        ];
        foreach ($paths as $label => $p) {
            $checks[$label] = ['path' => $p, 'exists' => is_file($p)];
        }

        // Also check via Storage facade
        $disks = [];
        foreach (['public', 'local'] as $disk) {
            try {
                $disks[$disk] = [
                    'root'                  => config("filesystems.disks.$disk.root"),
                    'avatars_dir_listing'   => Storage::disk($disk)->files('avatars'),
                    'target_file_exists'    => Storage::disk($disk)->exists('avatars/'.$safe),
                ];
            } catch (\Throwable $e) {
                $disks[$disk] = ['error' => $e->getMessage()];
            }
        }

        return response()->json([
            'db_avatar_value'    => $user->avatar,
            'looking_for'        => $safe,
            'direct_path_checks' => $checks,
            'storage_disk_checks'=> $disks,
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function password(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }
}
