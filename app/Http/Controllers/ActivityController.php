<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $q = Activity::query()->with(['user', 'lead', 'fromUser', 'toUser']);

        if ($user->isCounselor()) {
            $q->where(function ($w) use ($user) {
                $w->where('user_id', $user->id)
                  ->orWhere('to_user_id', $user->id)
                  ->orWhere('from_user_id', $user->id)
                  ->orWhereHas('lead', fn($lq) => $lq->where('assigned_to', $user->id));
            });
        }

        if ($action = $request->input('action')) {
            $q->where('action', $action);
        }

        // Counselor filter — admin only. Matches activities BY that counselor, or
        // OR involving them (assigned to / from), OR on leads currently assigned to them.
        if ($user->isAdmin() && $counselorId = $request->input('counselor_id')) {
            $q->where(function ($w) use ($counselorId) {
                $w->where('user_id', $counselorId)
                  ->orWhere('from_user_id', $counselorId)
                  ->orWhere('to_user_id', $counselorId)
                  ->orWhereHas('lead', fn($lq) => $lq->where('assigned_to', $counselorId));
            });
        }

        if ($from = $request->input('from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        $activities = $q->latest()->paginate(30)->withQueryString();

        $counselors = $user->isAdmin()
            ? User::where('role', 'counselor')->orderBy('name')->get()
            : collect();

        return view('activities.index', compact('activities', 'counselors'));
    }
}
