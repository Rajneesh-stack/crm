<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $leads = $this->buildQuery($request)
            ->with(['assignedTo', 'statusChangedBy'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        // Summary stats for the filtered set
        $summary = $this->summarize($request);

        $courses    = Course::active()->get();
        $counselors = User::where('role', 'counselor')->where('is_active', true)->orderBy('name')->get();

        return view('reports.index', compact('leads', 'summary', 'courses', 'counselors'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->buildQuery($request)->with(['assignedTo', 'statusChangedBy']);

        $filename = 'leads-report-' . now()->format('Ymd-His') . '.csv';

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Excel UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'ID', 'Name', 'Phone', 'Email', 'Course', 'Source', 'Status',
                'Status Marked By', 'Status Marked At',
                'Counselor', 'Priority', 'Next Follow-up', 'Created At',
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $l) {
                    fputcsv($handle, [
                        $l->id,
                        $l->name,
                        $l->phone,
                        $l->email,
                        $l->course,
                        $l->source,
                        Lead::STATUSES[$l->status] ?? $l->status,
                        optional($l->statusChangedBy)->name,
                        optional($l->status_changed_at)->format('Y-m-d H:i'),
                        optional($l->assignedTo)->name,
                        ucfirst($l->priority),
                        optional($l->next_followup_date)->format('Y-m-d'),
                        $l->created_at->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function buildQuery(Request $request)
    {
        $user = auth()->user();
        $q = Lead::query();

        // Counselor sees only their own leads
        if ($user->isCounselor()) {
            $q->where('assigned_to', $user->id);
        }

        if ($course = $request->input('course')) {
            $q->where('course', $course);
        }

        if ($status = $request->input('status')) {
            $q->where('status', $status);
        }

        if ($start = $request->input('start_date')) {
            $q->whereDate('created_at', '>=', $start);
        }
        if ($end = $request->input('end_date')) {
            $q->whereDate('created_at', '<=', $end);
        }

        // Admin-only: counselor filter
        if ($user->isAdmin() && $counselorId = $request->input('counselor_id')) {
            $q->where('assigned_to', $counselorId);
        }

        return $q;
    }

    protected function summarize(Request $request): array
    {
        $base = $this->buildQuery($request);

        return [
            'total'            => (clone $base)->count(),
            'converted'        => (clone $base)->where('status', 'converted')->count(),
            'good_lead'        => (clone $base)->where('status', 'good_lead')->count(),
            'in_conversation'  => (clone $base)->where('status', 'in_conversation')->count(),
            'fake_lead'        => (clone $base)->where('status', 'fake_lead')->count(),
            'lost'             => (clone $base)->where('status', 'lost')->count(),
            'new'              => (clone $base)->where('status', 'new')->count(),
        ];
    }
}
