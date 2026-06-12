<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkUploadController extends Controller
{
    public function show()
    {
        $counselors = User::where('role', 'counselor')->where('is_active', true)->orderBy('name')->get();
        return view('leads.bulk-upload', compact('counselors'));
    }

    public function sample(): StreamedResponse
    {
        $headers = [
            'name','phone','email','alternate_phone','whatsapp','course','sub_course','mode','preferred_batch','budget',
            'source','sub_source','campaign','utm_source','utm_medium','utm_campaign','referrer_name','referrer_phone',
            'date_of_birth','gender','occupation','qualification','passing_year','institute','company','designation','experience_years',
            'address','city','state','country','pincode',
            'status','priority','lead_score','next_followup_date','message','notes','tags'
        ];

        return response()->streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            fputcsv($out, [
                'Aman Sharma','9876543210','aman@example.com','','9876543210','MBA','Marketing','online','Jan 2026','120000',
                'facebook','','','fb','cpc','jan_mba','','',
                '2000-05-12','male','Student','B.Com','2022','DU','','','',
                'A-12 Sector 22','Delhi','Delhi','India','110001',
                'new','high','60','2026-05-15','Wants weekend batch','Hot lead','mba,delhi'
            ]);
            fclose($out);
        }, 'leads_sample.csv', ['Content-Type' => 'text/csv']);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'default_source' => ['nullable', 'string', 'max:100'],
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->with('error', 'Could not read uploaded file.');
        }

        $headers = fgetcsv($handle);
        $headers = array_map(fn($h) => trim(strtolower($h)), $headers);

        $assignedTo = $request->input('assigned_to');
        if (auth()->user()->isCounselor()) {
            $assignedTo = auth()->id();
        }
        $defaultSource = $request->input('default_source');

        $inserted = 0;
        $skipped = 0;
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (count($row) === 1 && empty(trim($row[0]))) continue;

                $data = [];
                foreach ($headers as $i => $h) {
                    $data[$h] = isset($row[$i]) ? trim($row[$i]) : null;
                    if ($data[$h] === '') $data[$h] = null;
                }

                // If row has no source but a default_source is provided in form, use it
                if (empty($data['source']) && $defaultSource) {
                    $data['source'] = $defaultSource;
                }

                $validator = Validator::make($data, [
                    'name'   => ['required', 'string', 'max:191'],
                    'phone'  => ['required', 'string', 'max:30'],
                    'source' => ['required', 'string', 'max:191'],
                    'email'  => ['nullable', 'email'],
                ], [
                    'source.required' => 'Source is required (set "default_source" in the upload form to apply to rows missing this column).',
                ]);

                if ($validator->fails()) {
                    $skipped++;
                    $errors[] = "Row $rowNum: ".implode(', ', $validator->errors()->all());
                    continue;
                }

                $allowed = [
                    'name','phone','email','alternate_phone','whatsapp','course','sub_course','mode','preferred_batch','budget',
                    'source','sub_source','campaign','utm_source','utm_medium','utm_campaign','referrer_name','referrer_phone',
                    'date_of_birth','gender','occupation','qualification','passing_year','institute','company','designation','experience_years',
                    'address','city','state','country','pincode',
                    'status','priority','lead_score','next_followup_date','message','notes','tags'
                ];

                $insert = array_intersect_key($data, array_flip($allowed));
                if (!empty($insert['tags'])) {
                    $insert['tags'] = collect(explode(',', $insert['tags']))->map(fn($t) => trim($t))->filter()->values()->all();
                }
                if (empty($insert['source']) && $defaultSource) $insert['source'] = $defaultSource;
                if (empty($insert['status'])) $insert['status'] = 'new';
                if (empty($insert['priority'])) $insert['priority'] = 'medium';
                if (empty($insert['country'])) $insert['country'] = 'India';

                $insert['created_by'] = auth()->id();
                if ($assignedTo) {
                    $insert['assigned_to'] = $assignedTo;
                    $insert['assigned_by'] = auth()->id();
                    $insert['assigned_at'] = now();
                }

                $lead = Lead::create($insert);
                $inserted++;

                // Auto-create followup so this lead shows in Today's Follow-ups
                // (or on the specified date) and rolls into Overdue tomorrow if no comment is added.
                $fDate = !empty($insert['next_followup_date']) ? $insert['next_followup_date'] : now()->toDateString();
                if (empty($lead->next_followup_date)) {
                    $lead->next_followup_date = $fDate;
                    $lead->save();
                }
                LeadFollowup::create([
                    'lead_id'        => $lead->id,
                    'user_id'        => $lead->assigned_to ?? auth()->id(),
                    'scheduled_date' => $fDate,
                    'status'         => 'pending',
                ]);

                Activity::log([
                    'lead_id'     => $lead->id,
                    'user_id'     => auth()->id(),
                    'action'      => 'bulk_imported',
                    'description' => "Lead '{$lead->name}' imported via bulk upload",
                    'to_user_id'  => $lead->assigned_to,
                ]);
            }

            DB::commit();
            fclose($handle);
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            return back()->with('error', 'Bulk upload failed: '.$e->getMessage());
        }

        return back()->with('success', "Imported $inserted leads. Skipped $skipped.")->with('errors_list', $errors);
    }
}
