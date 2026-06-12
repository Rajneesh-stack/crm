<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        $leadQuery   = Lead::query();
        // Followup counts/lists must ignore closed leads (converted, lost, junk).
        $followQuery = LeadFollowup::query()
            ->whereHas('lead', fn ($q) => $q->whereNotIn('status', ['converted', 'lost', 'junk']));

        if ($user->isCounselor()) {
            $leadQuery->where('assigned_to', $user->id);
            $followQuery->where('user_id', $user->id);
        }

        $todayLeadsCount = (clone $leadQuery)
            ->whereDate('created_at', $today)
            ->count();

        $todayFollowupsCount = (clone $followQuery)
            ->whereDate('scheduled_date', $today)
            ->where('status', 'pending')
            ->count();

        $overdueFollowupsCount = (clone $followQuery)
            ->whereDate('scheduled_date', '<', $today)
            ->where('status', 'pending')
            ->count();

        $convertedCount = (clone $leadQuery)
            ->where('status', 'converted')
            ->count();

        $totalLeads = (clone $leadQuery)->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedCount / $totalLeads) * 100, 1) : 0;

        // Today's follow-ups list
        $todayFollowups = (clone $followQuery)
            ->whereDate('scheduled_date', $today)
            ->where('status', 'pending')
            ->with(['lead', 'user'])
            ->orderBy('scheduled_time')
            ->take(10)
            ->get();

        $overdueFollowups = (clone $followQuery)
            ->whereDate('scheduled_date', '<', $today)
            ->where('status', 'pending')
            ->with(['lead', 'user'])
            ->orderBy('scheduled_date', 'desc')
            ->take(10)
            ->get();

        // Recent leads
        $recentLeadsQuery = Lead::query()->with('assignedTo')->latest()->take(8);
        if ($user->isCounselor()) {
            $recentLeadsQuery->where('assigned_to', $user->id);
        }
        $recentLeads = $recentLeadsQuery->get();

        // Counselors list (for admin's add lead modal & assignment select)
        $counselors = User::where('role', 'counselor')->where('is_active', true)->orderBy('name')->get();

        // Per-counselor performance (admin only)
        $counselorStats = collect();
        if ($user->isAdmin()) {
            $counselorStats = User::where('role', 'counselor')->get()->map(function ($c) use ($today) {
                return [
                    'user' => $c,
                    'total'        => Lead::where('assigned_to', $c->id)->count(),
                    'today'        => Lead::where('assigned_to', $c->id)->whereDate('created_at', $today)->count(),
                    'converted'    => Lead::where('assigned_to', $c->id)->where('status', 'converted')->count(),
                    'today_followups' => LeadFollowup::where('user_id', $c->id)->whereDate('scheduled_date', $today)->where('status', 'pending')->count(),
                    'overdue'      => LeadFollowup::where('user_id', $c->id)->whereDate('scheduled_date', '<', $today)->where('status', 'pending')->count(),
                ];
            });
        }

        // Recent activities
        $activitiesQuery = Activity::query()->with(['user', 'lead', 'fromUser', 'toUser'])->latest()->take(10);
        if ($user->isCounselor()) {
            $activitiesQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('to_user_id', $user->id)
                  ->orWhere('from_user_id', $user->id)
                  ->orWhereHas('lead', fn($lq) => $lq->where('assigned_to', $user->id));
            });
        }
        $recentActivities = $activitiesQuery->get();

        // Fields available for "Add Lead" checkbox-driven form
        $availableFields = $this->availableLeadFields();
        $courses = \App\Models\Course::active()->get();

        // Extra status counts for the new dashboard cards
        $goodLeadCount      = (clone $leadQuery)->where('status', 'good_lead')->count();
        $fakeLeadCount      = (clone $leadQuery)->where('status', 'fake_lead')->count();
        $inConversationCount= (clone $leadQuery)->where('status', 'in_conversation')->count();

        return view('dashboard', compact(
            'todayLeadsCount', 'todayFollowupsCount', 'overdueFollowupsCount', 'convertedCount',
            'conversionRate', 'totalLeads',
            'todayFollowups', 'overdueFollowups', 'recentLeads',
            'counselors', 'counselorStats', 'recentActivities', 'availableFields', 'courses',
            'goodLeadCount', 'fakeLeadCount', 'inConversationCount'
        ));
    }

    public static function availableLeadFields(): array
    {
        // Note: name, phone, source, course, qualification, date_of_birth, gender are
        // always-visible in the Add Lead form (not in this checkbox list).
        return [
            'Contact' => [
                'alternate_phone' => 'Alternate Phone',
                'email'           => 'Email',
                'whatsapp'        => 'WhatsApp Number',
            ],
            'Course / Interest' => [
                'sub_course'      => 'Sub Course',
                'mode'            => 'Mode (Online/Offline)',
                'preferred_batch' => 'Preferred Batch',
                'budget'          => 'Budget',
            ],
            'Source' => [
                'sub_source'     => 'Sub Source',
                'campaign'       => 'Campaign',
                'utm_source'     => 'UTM Source',
                'utm_medium'     => 'UTM Medium',
                'utm_campaign'   => 'UTM Campaign',
                'referrer_name'  => 'Referrer Name',
                'referrer_phone' => 'Referrer Phone',
            ],
            'Personal' => [
                'occupation'      => 'Occupation',
                'passing_year'    => 'Passing Year',
                'institute'       => 'Institute',
                'company'         => 'Company',
                'designation'     => 'Designation',
                'experience_years'=> 'Experience (years)',
            ],
            'Address' => [
                'address' => 'Address',
                'city'    => 'City',
                'state'   => 'State',
                'country' => 'Country',
                'pincode' => 'Pincode',
            ],
            'CRM Details' => [
                'status'             => 'Status',
                'priority'           => 'Priority',
                'lead_score'         => 'Lead Score (0-100)',
                'next_followup_date' => 'Next Follow-up Date',
                'next_followup_time' => 'Next Follow-up Time',
                'assigned_to'        => 'Assign to Counselor',
            ],
            'Notes' => [
                'message' => 'Message / Enquiry',
                'notes'   => 'Internal Notes',
                'tags'    => 'Tags (comma separated)',
            ],
        ];
    }
}
