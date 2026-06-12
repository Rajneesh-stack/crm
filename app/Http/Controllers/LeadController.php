<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\LeadComment;
use App\Models\LeadFollowup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $q = Lead::query()->with(['assignedTo']);

        if ($user->isCounselor()) {
            $q->where('assigned_to', $user->id);
        }

        if ($s = $request->input('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('city', 'like', "%$s%")
                  ->orWhere('course', 'like', "%$s%");
            });
        }

        if ($status = $request->input('status')) {
            $q->where('status', $status);
        }

        if ($course = $request->input('course')) {
            $q->where('course', $course);
        }

        if ($filter = $request->input('filter')) {
            $today = Carbon::today();
            switch ($filter) {
                case 'today':
                    $q->whereDate('created_at', $today);
                    break;
                case 'today_followups':
                    $q->whereDate('next_followup_date', $today)
                      ->whereNotIn('status', ['converted', 'lost', 'junk']);
                    break;
                case 'overdue':
                    $q->whereDate('next_followup_date', '<', $today)
                      ->whereNotIn('status', ['converted', 'lost', 'junk']);
                    break;
                case 'converted':
                    $q->where('status', 'converted');
                    break;
                case 'good_lead':
                case 'fake_lead':
                case 'in_conversation':
                    $q->where('status', $filter);
                    break;
            }
        }

        if ($user->isAdmin() && $request->filled('counselor_id')) {
            $q->where('assigned_to', $request->input('counselor_id'));
        }

        $leads = $q->with(['assignedTo', 'statusChangedBy'])->latest()->paginate(20)->withQueryString();
        $counselors = User::where('role', 'counselor')->where('is_active', true)->orderBy('name')->get();
        $availableFields = DashboardController::availableLeadFields();
        $courses = \App\Models\Course::active()->get();

        return view('leads.index', compact('leads', 'counselors', 'availableFields', 'courses'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name'   => ['required', 'string', 'max:191'],
            'phone'  => ['required', 'string', 'max:30'],
            'source' => ['required', 'string', 'max:191'],
        ];

        // Optional fields - only validate the ones submitted
        $optional = [
            'alternate_phone' => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:191',
            'whatsapp'        => 'nullable|string|max:30',
            'course'          => 'nullable|string|max:191',
            'sub_course'      => 'nullable|string|max:191',
            'mode'            => 'nullable|string|max:50',
            'preferred_batch' => 'nullable|string|max:191',
            'budget'          => 'nullable|numeric|min:0',
            'sub_source'      => 'nullable|string|max:191',
            'campaign'        => 'nullable|string|max:191',
            'utm_source'      => 'nullable|string|max:191',
            'utm_medium'      => 'nullable|string|max:191',
            'utm_campaign'    => 'nullable|string|max:191',
            'referrer_name'   => 'nullable|string|max:191',
            'referrer_phone'  => 'nullable|string|max:30',
            'date_of_birth'   => 'nullable|date',
            'gender'          => 'nullable|in:male,female,other',
            'occupation'      => 'nullable|string|max:191',
            'qualification'   => 'nullable|string|max:191|in:'.implode(',', array_keys(Lead::QUALIFICATIONS)),
            'passing_year'    => 'nullable|string|max:10',
            'institute'       => 'nullable|string|max:191',
            'company'         => 'nullable|string|max:191',
            'designation'     => 'nullable|string|max:191',
            'experience_years'=> 'nullable|numeric|min:0',
            'address'         => 'nullable|string',
            'city'            => 'nullable|string|max:100',
            'state'           => 'nullable|string|max:100',
            'country'         => 'nullable|string|max:100',
            'pincode'         => 'nullable|string|max:20',
            'status'          => 'nullable|in:'.implode(',', array_keys(Lead::STATUSES)),
            'priority'        => 'nullable|in:low,medium,high',
            'lead_score'      => 'nullable|integer|min:0|max:100',
            'next_followup_date' => 'nullable|date',
            'next_followup_time' => 'nullable|date_format:H:i',
            'assigned_to'     => 'nullable|exists:users,id',
            'message'         => 'nullable|string',
            'notes'           => 'nullable|string',
            'tags'            => 'nullable|string',
        ];

        $data = $request->validate(array_merge($rules, $optional));

        $user = auth()->user();

        // counselor cannot set assigned_to for someone else; default to self
        if ($user->isCounselor()) {
            $data['assigned_to'] = $user->id;
        }
        if (empty($data['assigned_to']) && $user->isCounselor()) {
            $data['assigned_to'] = $user->id;
        }

        $data['created_by'] = $user->id;
        if (!empty($data['assigned_to'])) {
            $data['assigned_by'] = $user->id;
            $data['assigned_at'] = now();
        }
        // Record initial status setter
        $data['status_changed_by'] = $user->id;
        $data['status_changed_at'] = now();

        if (!empty($data['tags']) && is_string($data['tags'])) {
            $data['tags'] = collect(explode(',', $data['tags']))->map(fn($t) => trim($t))->filter()->values()->all();
        }

        $lead = DB::transaction(function () use ($data, $user) {
            $lead = Lead::create($data);

            Activity::log([
                'lead_id'     => $lead->id,
                'user_id'     => $user->id,
                'action'      => 'lead_created',
                'description' => "Lead '{$lead->name}' created",
                'to_user_id'  => $lead->assigned_to,
            ]);

            if ($lead->assigned_to && $lead->assigned_to !== $user->id) {
                Activity::log([
                    'lead_id'     => $lead->id,
                    'user_id'     => $user->id,
                    'action'      => 'assigned',
                    'description' => "Lead assigned to ".optional($lead->assignedTo)->name,
                    'to_user_id'  => $lead->assigned_to,
                ]);
            }

            // Always schedule a followup. If no next_followup_date provided, default to today
            // so the lead automatically shows in "Today's Follow-ups".
            $followupDate = $lead->next_followup_date ?? now()->toDateString();
            if (empty($lead->next_followup_date)) {
                $lead->next_followup_date = $followupDate;
                $lead->save();
            }
            LeadFollowup::create([
                'lead_id'        => $lead->id,
                'user_id'        => $lead->assigned_to ?? $user->id,
                'scheduled_date' => $followupDate,
                'scheduled_time' => $lead->next_followup_time,
                'status'         => 'pending',
            ]);
            Activity::log([
                'lead_id'     => $lead->id,
                'user_id'     => $user->id,
                'action'      => 'followup_added',
                'description' => "Follow-up scheduled for ".\Carbon\Carbon::parse($followupDate)->format('d M Y'),
            ]);

            return $lead;
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'lead' => $lead, 'redirect' => route('leads.show', $lead)]);
        }

        return redirect()->route('leads.show', $lead)->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead)
    {
        $user = auth()->user();
        if ($user->isCounselor() && $lead->assigned_to !== $user->id) {
            abort(403, 'This lead is not assigned to you.');
        }

        $lead->load(['assignedTo', 'creator', 'statusChangedBy', 'comments.user', 'followups.user', 'activities.user', 'activities.fromUser', 'activities.toUser']);

        $counselors  = User::where('role', 'counselor')->where('is_active', true)->orderBy('name')->get();
        $commentsCount = $lead->comments->count();
        $followupsCount = $lead->followups->count();
        $completedFollowups = $lead->followups->where('status', 'completed')->count();

        $communications = \App\Models\Communication::where('lead_id', $lead->id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Templates: prefer DB (admin-managed), fall back to config if table empty for fresh installs.
        $waRows    = \App\Models\MessageTemplate::channel('whatsapp')->active()->get();
        $emailRows = \App\Models\MessageTemplate::channel('email')->active()->get();

        $messagingTemplates = $waRows->isNotEmpty()
            ? $waRows->mapWithKeys(fn ($t) => [$t->key => ['label' => $t->label, 'body' => $t->body]])->all()
            : config('messaging.text_templates', []);

        $emailTemplates = $emailRows->isNotEmpty()
            ? $emailRows->mapWithKeys(fn ($t) => [$t->key => [
                'label'       => $t->label,
                'subject'     => $t->subject,
                'body'        => $t->body,
                'attachments' => collect($t->attachments ?? [])->map(fn ($p) => basename($p))->all(),
            ]])->all()
            : config('messaging.email_templates', []);

        $whatsappReady = app(\App\Services\WhatsAppService::class)->isConfigured();

        return view('leads.show', compact(
            'lead', 'counselors', 'commentsCount', 'followupsCount', 'completedFollowups',
            'communications', 'messagingTemplates', 'emailTemplates', 'whatsappReady'
        ));
    }

    public function edit(Lead $lead)
    {
        $user = auth()->user();
        if ($user->isCounselor() && $lead->assigned_to !== $user->id) {
            abort(403, 'This lead is not assigned to you.');
        }
        $counselors = User::where('role', 'counselor')->where('is_active', true)->orderBy('name')->get();
        $courses    = \App\Models\Course::active()->get();
        return view('leads.edit', compact('lead', 'counselors', 'courses'));
    }

    public function update(Request $request, Lead $lead)
    {
        $user = auth()->user();
        if ($user->isCounselor() && $lead->assigned_to !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:191'],
            'phone' => ['required', 'string', 'max:30'],
            'alternate_phone' => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:191',
            'whatsapp'        => 'nullable|string|max:30',
            'course'          => 'nullable|string|max:191',
            'sub_course'      => 'nullable|string|max:191',
            'mode'            => 'nullable|string|max:50',
            'preferred_batch' => 'nullable|string|max:191',
            'budget'          => 'nullable|numeric|min:0',
            // Quick Update (sidebar) sends only status/priority — source isn't included.
            // The full Edit Lead form always sends source. Use `sometimes` so:
            //   - If source is submitted, it must be valid.
            //   - If absent (quick update), validation skips it and existing value stays.
            'source'          => 'sometimes|required|string|max:191',
            'date_of_birth'   => 'nullable|date',
            'gender'          => 'nullable|in:male,female,other',
            'occupation'      => 'nullable|string|max:191',
            'qualification'   => 'nullable|string|max:191|in:'.implode(',', array_keys(Lead::QUALIFICATIONS)),
            'company'         => 'nullable|string|max:191',
            'designation'     => 'nullable|string|max:191',
            'address'         => 'nullable|string',
            'city'            => 'nullable|string|max:100',
            'state'           => 'nullable|string|max:100',
            'country'         => 'nullable|string|max:100',
            'pincode'         => 'nullable|string|max:20',
            'status'          => 'nullable|in:'.implode(',', array_keys(Lead::STATUSES)),
            'priority'        => 'nullable|in:low,medium,high',
            'lead_score'      => 'nullable|integer|min:0|max:100',
            'next_followup_date' => 'nullable|date',
            'next_followup_time' => 'nullable|date_format:H:i',
            'message'         => 'nullable|string',
            'notes'           => 'nullable|string',
            'lost_reason'     => 'nullable|string|max:255',
            'conversion_amount' => 'nullable|numeric|min:0',
        ]);

        $oldStatus = $lead->status;

        DB::transaction(function () use ($lead, $data, $oldStatus, $user) {
            $lead->fill($data);

            $closedStatuses = ['converted', 'lost', 'junk'];

            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                if ($data['status'] === 'converted') {
                    $lead->converted_at = now();
                }
                if (in_array($data['status'], $closedStatuses, true)) {
                    // Close lead: clear scheduled followup date so it won't appear in lists
                    $lead->next_followup_date = null;
                    $lead->next_followup_time = null;
                }
                // Track WHO marked the new status and WHEN
                $lead->status_changed_by = $user->id;
                $lead->status_changed_at = now();
            }

            $lead->save();

            // When lead is closed (converted/lost/junk), auto-complete all its pending
            // followups so it disappears from Today's Follow-ups & Overdue.
            if (isset($data['status']) && in_array($data['status'], $closedStatuses, true) && $data['status'] !== $oldStatus) {
                \App\Models\LeadFollowup::where('lead_id', $lead->id)
                    ->where('status', 'pending')
                    ->update([
                        'status'       => 'completed',
                        'completed_at' => now(),
                        'remark'       => 'Auto-closed: lead marked '.$data['status'],
                    ]);
            }

            Activity::log([
                'lead_id'     => $lead->id,
                'user_id'     => $user->id,
                'action'      => 'lead_updated',
                'description' => "Lead '{$lead->name}' updated",
            ]);

            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                Activity::log([
                    'lead_id'     => $lead->id,
                    'user_id'     => $user->id,
                    'action'      => $data['status'] === 'converted' ? 'converted' : ($data['status'] === 'lost' ? 'lost' : 'status_changed'),
                    'description' => "Status changed from ".(Lead::STATUSES[$oldStatus] ?? $oldStatus)." to ".(Lead::STATUSES[$data['status']] ?? $data['status']),
                    'meta'        => ['from' => $oldStatus, 'to' => $data['status']],
                ]);
            }
        });

        return redirect()->route('leads.show', $lead)->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }

    public function reassign(Request $request, Lead $lead)
    {
        $user = auth()->user();

        // Admin can reassign any lead. Counselor can reassign only leads currently assigned to them.
        if (!$user->isAdmin() && $lead->assigned_to !== $user->id) {
            abort(403, 'You can only reassign leads that are assigned to you.');
        }

        $data = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
            'note'        => ['nullable', 'string', 'max:500'],
        ]);

        $target = User::find($data['assigned_to']);
        if (!$target || $target->role !== 'counselor' || !$target->is_active) {
            return back()->withErrors(['assigned_to' => 'Please pick an active counselor.']);
        }

        $from = $lead->assigned_to;
        if ((int)$from === (int)$data['assigned_to']) {
            return back()->with('warning', 'Lead is already assigned to that counselor.');
        }

        $fromUser = $from ? User::find($from) : null;
        $toUser   = User::find($data['assigned_to']);

        DB::transaction(function () use ($lead, $data, $from, $toUser, $fromUser, $request) {
            $lead->assigned_to = $data['assigned_to'];
            $lead->assigned_by = auth()->id();
            $lead->assigned_at = now();
            $lead->save();

            // also reassign open followups
            LeadFollowup::where('lead_id', $lead->id)
                ->where('status', 'pending')
                ->update(['user_id' => $data['assigned_to']]);

            Activity::log([
                'lead_id'      => $lead->id,
                'user_id'      => auth()->id(),
                'action'       => $from ? 'reassigned' : 'assigned',
                'description'  => $from
                    ? "Lead reassigned from ".optional($fromUser)->name." to {$toUser->name}".($data['note'] ?? null ? " — {$data['note']}" : '')
                    : "Lead assigned to {$toUser->name}".($data['note'] ?? null ? " — {$data['note']}" : ''),
                'from_user_id' => $from,
                'to_user_id'   => $toUser->id,
                'meta'         => ['note' => $data['note'] ?? null],
            ]);
        });

        return back()->with('success', 'Lead assignment updated.');
    }

    public function addComment(Request $request, Lead $lead)
    {
        $user = auth()->user();
        if ($user->isCounselor() && $lead->assigned_to !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
            'outcome' => ['nullable', 'in:call_not_picked,call_back_later,interested,not_interested,wrong_number,switched_off,busy,already_enrolled,demo_done,email_sent,whatsapp_sent,visited,other'],
            'next_followup_date' => ['nullable', 'date'],
            'next_followup_time' => ['nullable', 'date_format:H:i'],
            'mark_completed_followup_id' => ['nullable', 'exists:lead_followups,id'],
        ]);

        DB::transaction(function () use ($lead, $data, $user) {
            $comment = LeadComment::create([
                'lead_id'  => $lead->id,
                'user_id'  => $user->id,
                'comment'  => $data['comment'],
                'outcome'  => $data['outcome'] ?? null,
                'next_followup_date' => $data['next_followup_date'] ?? null,
                'next_followup_time' => $data['next_followup_time'] ?? null,
            ]);

            $lead->last_contacted_at = now();
            $lead->followup_count = ($lead->followup_count ?? 0) + 1;

            if (!empty($data['next_followup_date'])) {
                $lead->next_followup_date = $data['next_followup_date'];
                $lead->next_followup_time = $data['next_followup_time'] ?? null;
                $lead->status = $lead->status === 'new' ? 'contacted' : $lead->status;

                LeadFollowup::create([
                    'lead_id'        => $lead->id,
                    'user_id'        => $lead->assigned_to ?? $user->id,
                    'scheduled_date' => $data['next_followup_date'],
                    'scheduled_time' => $data['next_followup_time'] ?? null,
                    'status'         => 'pending',
                ]);
            } else {
                $lead->status = $lead->status === 'new' ? 'contacted' : $lead->status;
            }

            $lead->save();

            // Mark today's follow-up complete if requested or if this is a call activity
            if (!empty($data['mark_completed_followup_id'])) {
                LeadFollowup::where('id', $data['mark_completed_followup_id'])
                    ->update(['status' => 'completed', 'completed_at' => now(), 'remark' => $data['comment']]);
            } else {
                // Auto-complete the earliest pending followup for today
                LeadFollowup::where('lead_id', $lead->id)
                    ->where('status', 'pending')
                    ->whereDate('scheduled_date', '<=', now()->toDateString())
                    ->orderBy('scheduled_date')
                    ->limit(1)
                    ->update(['status' => 'completed', 'completed_at' => now(), 'remark' => $data['comment']]);
            }

            Activity::log([
                'lead_id'     => $lead->id,
                'user_id'     => $user->id,
                'action'      => 'comment_added',
                'description' => "Comment added".($data['outcome'] ?? null ? " ({$data['outcome']})" : '').": ".\Illuminate\Support\Str::limit($data['comment'], 80),
                'meta'        => ['comment_id' => $comment->id, 'outcome' => $data['outcome'] ?? null],
            ]);

            if (!empty($data['next_followup_date'])) {
                Activity::log([
                    'lead_id'     => $lead->id,
                    'user_id'     => $user->id,
                    'action'      => 'followup_added',
                    'description' => "Next follow-up set to ".\Carbon\Carbon::parse($data['next_followup_date'])->format('d M Y'),
                ]);
            }
        });

        return back()->with('success', 'Comment added & follow-up updated.');
    }
}
