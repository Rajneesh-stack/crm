<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CounselorController extends Controller
{
    public function index()
    {
        $counselors = User::where('role', 'counselor')
            ->withCount(['assignedLeads'])
            ->orderBy('name')
            ->get()
            ->map(function ($c) {
                $c->converted_count = Lead::where('assigned_to', $c->id)->where('status', 'converted')->count();
                $c->today_count = Lead::where('assigned_to', $c->id)->whereDate('created_at', today())->count();
                return $c;
            });

        return view('counselors.index', compact('counselors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:191'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'password'  => Hash::make($data['password']),
            'role'      => 'counselor',
            'is_active' => true,
        ]);

        return back()->with('success', 'Counselor added.');
    }

    public function update(Request $request, User $counselor)
    {
        abort_unless($counselor->role === 'counselor', 404);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:191'],
            'email'    => ['required', 'email', Rule::unique('users','email')->ignore($counselor->id)],
            'phone'    => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active'=> ['nullable', 'boolean'],
        ]);

        $counselor->name  = $data['name'];
        $counselor->email = $data['email'];
        $counselor->phone = $data['phone'] ?? null;
        $counselor->is_active = $request->boolean('is_active');
        if (!empty($data['password'])) {
            $counselor->password = Hash::make($data['password']);
        }
        $counselor->save();

        return back()->with('success', 'Counselor updated.');
    }

    public function destroy(User $counselor)
    {
        abort_unless($counselor->role === 'counselor', 404);
        $counselor->is_active = false;
        $counselor->save();
        return back()->with('success', 'Counselor deactivated.');
    }

    public function toggleActive(User $counselor)
    {
        abort_unless($counselor->role === 'counselor', 404);
        $counselor->is_active = !$counselor->is_active;
        $counselor->save();
        return back()->with('success', $counselor->name . ' is now ' . ($counselor->is_active ? 'active' : 'inactive') . '.');
    }
}
