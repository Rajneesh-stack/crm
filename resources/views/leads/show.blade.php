@extends('layouts.app')
@section('title', $lead->name)
@section('page-title','Lead Details')

@section('header-actions')
  <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('leads.index') }}" class="btn btn-ghost">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Back
  </a>
@endsection

@section('content')
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">

      {{-- Lead header --}}
      <div class="card">
        <div class="card-body">
          <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center font-bold text-white text-2xl flex-shrink-0" style="background:linear-gradient(135deg,#b8923d,#7d6122);">
              {{ strtoupper(substr($lead->name,0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <h2 class="font-serif text-3xl text-ink-900">{{ $lead->name }}</h2>
                <span class="badge {{ $lead->status_color }}">{{ $lead->status_label }}</span>
                @if($lead->statusChangedBy)
                  <span class="text-[11px] text-gray-500 italic">
                    by <strong class="text-gold-700 not-italic">{{ $lead->statusChangedBy->name }}</strong>
                    @if($lead->status_changed_at) · {{ $lead->status_changed_at->diffForHumans() }} @endif
                  </span>
                @endif
                @if($lead->priority==='high')<span class="badge bg-rose-100 text-rose-700 border-rose-300">High Priority</span>@endif
              </div>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3 text-sm">
                <div><div class="text-xs text-gray-500">Phone</div><div class="font-semibold">{{ $lead->phone }}</div></div>
                @if($lead->email)<div><div class="text-xs text-gray-500">Email</div><div class="font-semibold truncate">{{ $lead->email }}</div></div>@endif
                @if($lead->course)<div><div class="text-xs text-gray-500">Course</div><div class="font-semibold">{{ $lead->course }}</div></div>@endif
                @if($lead->source)<div><div class="text-xs text-gray-500">Source</div><div class="font-semibold">{{ ucfirst(str_replace('_',' ',$lead->source)) }}</div></div>@endif
              </div>
            </div>
            <a href="{{ route('leads.edit', $lead) }}" class="btn btn-primary btn-sm flex-shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Edit Lead
            </a>
          </div>

          {{-- Stats strip --}}
          @php
            $emailsSentTotal = $communications->where('channel','email')->where('status','sent')->count();
            $waSentTotal     = $communications->where('channel','whatsapp')->where('status','sent')->count();
          @endphp
          <div class="grid grid-cols-3 sm:grid-cols-5 gap-3 mt-5 pt-4 border-t border-[#f3eede]">
            <div class="text-center">
              <div class="text-2xl font-bold text-ink-900">{{ $followupsCount }}</div>
              <div class="text-[11px] uppercase tracking-wider text-gold-700 font-semibold">Follow-ups</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-emerald-600">{{ $completedFollowups }}</div>
              <div class="text-[11px] uppercase tracking-wider text-gold-700 font-semibold">Completed</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">{{ $commentsCount }}</div>
              <div class="text-[11px] uppercase tracking-wider text-gold-700 font-semibold">Comments</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600">{{ $emailsSentTotal }}</div>
              <div class="text-[11px] uppercase tracking-wider text-gold-700 font-semibold">Emails Sent</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-emerald-700">{{ $waSentTotal }}</div>
              <div class="text-[11px] uppercase tracking-wider text-gold-700 font-semibold">WhatsApps Sent</div>
            </div>
          </div>
        </div>
      </div>

      {{-- COMMUNICATION --}}
      @php
        $tplWa = collect($messagingTemplates)->mapWithKeys(fn($t,$k)=>[$k => strtr($t['body'], [
          '{name}'=>$lead->name, '{course}'=>$lead->course?:'our program',
          '{counselor}'=>auth()->user()->name, '{phone}'=>$lead->phone, '{email}'=>$lead->email,
        ])])->all();
        $tplEmail = collect($emailTemplates)->mapWithKeys(fn($t,$k)=>[$k => [
          'subject'=>strtr($t['subject'], ['{name}'=>$lead->name,'{course}'=>$lead->course?:'our program','{counselor}'=>auth()->user()->name]),
          'body'   =>strtr($t['body'],    ['{name}'=>$lead->name,'{course}'=>$lead->course?:'our program','{counselor}'=>auth()->user()->name]),
        ]])->all();
      @endphp
      <div class="card" id="commCard">
        <div class="card-header">
          <span class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.888-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884 0 2.225.651 3.891 1.746 5.634l-.999 3.648 3.743-.981z"/></svg>
            Communication
          </span>
          @if(!$whatsappReady)
            <span class="text-[10px] uppercase tracking-wider text-amber-600 font-semibold">WhatsApp not configured</span>
          @endif
        </div>

        {{-- TABS --}}
        <div class="flex border-b border-[#f3eede]">
          @php
            $waCount    = $communications->where('channel','whatsapp')->count();
            $emailCount = $communications->where('channel','email')->where('status','sent')->count();
            $emailFailed= $communications->where('channel','email')->where('status','failed')->count();
          @endphp
          <button type="button" data-tab="whatsapp" class="comm-tab flex-1 px-4 py-3 text-sm font-semibold flex items-center justify-center gap-2 transition border-b-2 border-gold-500 text-ink-900 bg-[#fdfaf0]">
            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24z"/></svg>
            WhatsApp
            @if($waCount > 0)<span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-700 rounded-full">{{ $waCount }}</span>@endif
          </button>
          <button type="button" data-tab="email" class="comm-tab flex-1 px-4 py-3 text-sm font-semibold flex items-center justify-center gap-2 transition border-b-2 border-transparent text-gray-500 hover:bg-[#fdfaf0]/50">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Email
            @if($emailCount > 0)<span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-700 rounded-full">{{ $emailCount }}</span>@endif
            @if($emailFailed > 0)<span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-700 rounded-full" title="{{ $emailFailed }} failed">⚠ {{ $emailFailed }}</span>@endif
          </button>
        </div>

        {{-- WHATSAPP TAB --}}
        <div data-tab-panel="whatsapp">
          {{-- chat history --}}
          <div class="px-5 py-4 border-b border-[#f3eede] bg-[#fdfaf0]/30" style="max-height:280px; overflow-y:auto;" id="waHistory">
            @php $waMsgs = $communications->where('channel','whatsapp'); @endphp
            @forelse($waMsgs as $m)
              <div class="flex {{ $m->direction==='out' ? 'justify-end' : 'justify-start' }} mb-2">
                <div class="max-w-[80%] px-3 py-2 rounded-lg shadow-sm
                    {{ $m->direction==='out' ? 'bg-emerald-100 text-emerald-900' : 'bg-white text-ink-900 border border-[#f3eede]' }}">
                  <div class="text-sm whitespace-pre-wrap">{{ $m->body }}</div>
                  <div class="text-[10px] text-gray-500 mt-1 flex items-center gap-1 justify-end">
                    {{ $m->created_at->format('d M, h:i A') }}
                    @if($m->status==='sent')<svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>@endif
                    @if($m->status==='failed')<span class="text-rose-600 font-semibold">Failed</span>@endif
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-xs text-gray-400 py-6">No WhatsApp messages yet. Send the first one below.</div>
            @endforelse
          </div>

          {{-- compose --}}
          <form method="POST" action="{{ route('leads.whatsapp.send',$lead) }}" class="p-5 space-y-3">
            @csrf
            <div class="flex flex-wrap items-center gap-2">
              <span class="text-xs font-semibold text-gray-500">To:</span>
              <input type="text" name="to_address" value="{{ $lead->phone }}" required maxlength="30" pattern="[0-9+\-\s()]{6,}"
                     class="form-input font-mono" style="flex:1; min-width:180px; padding:.35rem .6rem; font-size:.85rem;"
                     placeholder="e.g. +91 98765 43210">
              <span class="text-[10px] text-gray-400">10 digits → +91 auto-added</span>
            </div>
            <div>
              <label class="form-label flex items-center justify-between">
                <span>Message</span>
                <select id="waTemplateSel" class="form-select" style="max-width:200px;">
                  <option value="">Insert template…</option>
                  @foreach($messagingTemplates as $key => $t)
                    <option value="{{ $key }}">{{ $t['label'] }}</option>
                  @endforeach
                </select>
              </label>
              <textarea name="body" id="waBody" rows="3" placeholder="Type a message…" class="form-textarea" required></textarea>
              <input type="hidden" name="template_key" id="waTemplateKey" value="">
              <div class="text-[11px] text-gray-500 mt-1">
                Tip: WhatsApp Cloud API allows free-text only within 24 hrs of the customer's last reply. For first outreach, use an approved template configured in Meta Business Manager.
              </div>
            </div>
            <div class="flex justify-end">
              <button class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Send WhatsApp
              </button>
            </div>
          </form>
        </div>

        {{-- EMAIL TAB --}}
        <div data-tab-panel="email" style="display:none;">
          {{-- email history --}}
          <div class="px-5 py-4 border-b border-[#f3eede] bg-[#fdfaf0]/30" style="max-height:240px; overflow-y:auto;">
            @php $emailMsgs = $communications->where('channel','email'); @endphp
            @forelse($emailMsgs as $m)
              <div class="p-3 mb-2 rounded-lg border border-[#f3eede] bg-white">
                <div class="flex justify-between items-start text-xs mb-1">
                  <div class="font-semibold text-ink-800">{{ $m->subject }}</div>
                  <div class="text-gray-500">{{ $m->created_at->format('d M, h:i A') }}</div>
                </div>
                <div class="text-xs text-gray-500 mb-1">to {{ $m->to_address }} · by {{ $m->user->name ?? '—' }}
                  @if($m->status==='sent')<span class="text-emerald-600">· sent ✓</span>@endif
                  @if($m->status==='failed')<span class="text-rose-600">· failed</span>@endif
                </div>
                <div class="text-xs whitespace-pre-wrap text-ink-800 line-clamp-3">{{ $m->body }}</div>
              </div>
            @empty
              <div class="text-center text-xs text-gray-400 py-6">No emails sent yet.</div>
            @endforelse
          </div>

          {{-- compose --}}
          <form id="emailForm" method="POST" action="{{ route('leads.email.send',$lead) }}" class="p-5 space-y-3">
            @csrf
            <div class="flex flex-wrap items-center gap-2">
              <span class="text-xs font-semibold text-gray-500">To:</span>
              <input type="email" name="to_address" value="{{ $lead->email }}" required maxlength="191"
                     class="form-input" style="flex:1; min-width:200px; padding:.35rem .6rem; font-size:.85rem;"
                     placeholder="recipient@example.com">
            </div>
            <div>
              <label class="form-label flex items-center justify-between">
                <span>Template</span>
                <select id="emailTemplateSel" class="form-select" style="max-width:200px;">
                  <option value="">— Pick template —</option>
                  @foreach($emailTemplates as $key => $t)
                    <option value="{{ $key }}">{{ $t['label'] }}</option>
                  @endforeach
                </select>
              </label>
              <input type="hidden" name="template_key" id="emailTemplateKey" value="">
            </div>
            <div>
              <label class="form-label">Subject</label>
              <input type="text" name="subject" id="emailSubject" class="form-input" required>
            </div>
            <div>
              <label class="form-label">Body</label>
              <textarea name="body" id="emailBody" rows="6" class="form-textarea" required></textarea>
            </div>
            <div class="flex justify-end">
              <button class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Send Email
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- Add comment / followup --}}
      <div class="card">
        <div class="card-header">Add Comment / Update Follow-up</div>
        <div class="card-body">
          <form method="POST" action="{{ route('leads.comments.store',$lead) }}" class="space-y-3">
            @csrf
            <div>
              <label class="form-label">Comment <span class="text-rose-500">*</span></label>
              <textarea name="comment" rows="3" placeholder="e.g. Call not picked, will retry tomorrow..." class="form-textarea" required></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="form-label">Outcome</label>
                <select name="outcome" class="form-select">
                  <option value="">— Select —</option>
                  <option value="call_not_picked">Call Not Picked</option>
                  <option value="call_back_later">Call Back Later</option>
                  <option value="interested">Interested</option>
                  <option value="not_interested">Not Interested</option>
                  <option value="wrong_number">Wrong Number</option>
                  <option value="switched_off">Switched Off</option>
                  <option value="busy">Busy</option>
                  <option value="already_enrolled">Already Enrolled</option>
                  <option value="demo_done">Demo Done</option>
                  <option value="email_sent">Email Sent</option>
                  <option value="whatsapp_sent">WhatsApp Sent</option>
                  <option value="visited">Visited</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <label class="form-label">Next Follow-up Date</label>
                <input type="date" name="next_followup_date" class="form-input" min="{{ today()->toDateString() }}">
              </div>
              <div>
                <label class="form-label">Next Follow-up Time</label>
                <input type="time" name="next_followup_time" class="form-input">
              </div>
            </div>
            <div class="flex justify-end">
              <button class="btn btn-primary">Save Comment</button>
            </div>
          </form>
        </div>
      </div>

      {{-- Comments timeline --}}
      <div class="card">
        <div class="card-header">
          <span>Comments ({{ $lead->comments->count() }})</span>
        </div>
        <div class="card-body p-0" style="max-height:380px; overflow-y:auto;">
          @forelse($lead->comments as $c)
            <div class="px-5 py-4 border-b border-[#f3eede] flex gap-3">
              <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-white text-xs flex-shrink-0" style="background:linear-gradient(135deg,#b8923d,#7d6122);">
                {{ strtoupper(substr($c->user->name ?? '?',0,1)) }}
              </div>
              <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                  <span class="font-semibold text-ink-800">{{ $c->user->name ?? '—' }}</span>
                  @if($c->outcome)<span class="badge bg-gold-50 text-gold-800 border-gold-200">{{ str_replace('_',' ',$c->outcome) }}</span>@endif
                  <span class="text-[11px] text-gray-500">{{ $c->created_at->format('d M Y · h:i A') }}</span>
                </div>
                <div class="text-sm text-ink-800 whitespace-pre-wrap">{{ $c->comment }}</div>
                @if($c->next_followup_date)
                  <div class="text-xs text-gold-700 mt-1">📅 Next: {{ $c->next_followup_date->format('d M Y') }} @if($c->next_followup_time) at {{ \Carbon\Carbon::parse($c->next_followup_time)->format('h:i A') }}@endif</div>
                @endif
              </div>
            </div>
          @empty
            <div class="px-5 py-10 text-center text-gray-500 text-sm">No comments yet. Add the first one above.</div>
          @endforelse
        </div>
      </div>

      {{-- Activity --}}
      <div class="card">
        <div class="card-header">Activity Timeline</div>
        <div class="card-body p-0" style="max-height:380px; overflow-y:auto;">
          @forelse($lead->activities as $a)
            <div class="px-5 py-3 border-b border-[#f3eede] flex gap-3">
              <div class="timeline-dot {{ $a->color }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <div class="flex-1">
                <div class="text-sm text-ink-800">{{ $a->description }}</div>
                <div class="text-[11px] text-gray-500 mt-0.5">
                  {{ $a->user->name ?? 'System' }} · {{ $a->created_at->diffForHumans() }}
                </div>
              </div>
            </div>
          @empty
            <div class="px-5 py-8 text-center text-gray-500 text-sm">No activity yet.</div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- SIDEBAR --}}
    <div class="space-y-5">
      @php
        $canReassign = auth()->user()->isAdmin() || ($lead->assigned_to === auth()->id());
      @endphp
      <div class="card">
        <div class="card-header">
          <span>Assignment</span>
          @if($canReassign)
            <button onclick="document.getElementById('reassignModal').classList.toggle('hidden')" class="text-xs font-semibold text-gold-700 hover:underline">Change →</button>
          @endif
        </div>
        <div class="card-body">
          @if($lead->assignedTo)
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white" style="background:linear-gradient(135deg,#b8923d,#7d6122);">
                {{ strtoupper(substr($lead->assignedTo->name,0,1)) }}
              </div>
              <div>
                <div class="font-semibold">{{ $lead->assignedTo->name }}</div>
                <div class="text-xs text-gray-500">{{ $lead->assignedTo->email }}</div>
                @if($lead->assigned_at)<div class="text-xs text-gold-700">Since {{ $lead->assigned_at->format('d M Y') }}</div>@endif
              </div>
            </div>
          @else
            <div class="text-gray-500 text-sm">Unassigned</div>
          @endif

          @if($canReassign)
            <div id="reassignModal" class="hidden mt-4 pt-4 border-t border-[#f3eede]">
              <form method="POST" action="{{ route('leads.reassign',$lead) }}">
                @csrf
                <label class="form-label">Reassign to</label>
                <select name="assigned_to" class="form-select mb-2" required>
                  <option value="">-- Select counselor --</option>
                  @foreach($counselors as $c)
                    @if($c->id !== auth()->id() || auth()->user()->isAdmin())
                      <option value="{{ $c->id }}" @selected($c->id===$lead->assigned_to)>{{ $c->name }}</option>
                    @endif
                  @endforeach
                </select>
                <input type="text" name="note" class="form-input mb-2" placeholder="Reason / note (optional)">
                <button class="btn btn-primary btn-sm w-full">Update Assignment</button>
              </form>
            </div>
          @endif
        </div>
      </div>

      <div class="card">
        <div class="card-header">Lead Details</div>
        <div class="card-body space-y-2 text-sm">
          @php
            $rows = [
              'WhatsApp' => $lead->whatsapp,
              'Alt. Phone' => $lead->alternate_phone,
              'Sub Course' => $lead->sub_course,
              'Mode' => $lead->mode,
              'Preferred Batch' => $lead->preferred_batch,
              'Budget' => $lead->budget ? '₹'.number_format($lead->budget) : null,
              'Sub Source' => $lead->sub_source,
              'Campaign' => $lead->campaign,
              'UTM' => collect([$lead->utm_source,$lead->utm_medium,$lead->utm_campaign])->filter()->implode(' / '),
              'Referrer' => $lead->referrer_name ? $lead->referrer_name.($lead->referrer_phone?' ('.$lead->referrer_phone.')':'') : null,
              'DOB' => $lead->date_of_birth?->format('d M Y'),
              'Gender' => $lead->gender ? ucfirst($lead->gender) : null,
              'Occupation' => $lead->occupation,
              'Qualification' => $lead->qualification.($lead->passing_year?' ('.$lead->passing_year.')':''),
              'Institute' => $lead->institute,
              'Company' => $lead->company.($lead->designation?' — '.$lead->designation:''),
              'Experience' => $lead->experience_years ? $lead->experience_years.' yrs' : null,
              'City' => $lead->city,
              'State' => $lead->state,
              'Country' => $lead->country,
              'Pincode' => $lead->pincode,
              'Address' => $lead->address,
              'Lead Score' => $lead->lead_score ?: null,
              'Created By' => $lead->creator?->name,
            ];
          @endphp
          @foreach($rows as $label => $val)
            @if(!empty($val))
              <div class="flex justify-between gap-3">
                <span class="text-gray-500 text-xs uppercase tracking-wider">{{ $label }}</span>
                <span class="text-ink-800 text-right">{{ $val }}</span>
              </div>
            @endif
          @endforeach
          @if($lead->message)
            <div class="pt-2 border-t border-[#f3eede]">
              <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Message</div>
              <div class="text-ink-800 whitespace-pre-wrap">{{ $lead->message }}</div>
            </div>
          @endif
          @if($lead->notes)
            <div class="pt-2 border-t border-[#f3eede]">
              <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Internal Notes</div>
              <div class="text-ink-800 whitespace-pre-wrap">{{ $lead->notes }}</div>
            </div>
          @endif
        </div>
      </div>

      <div class="card">
        <div class="card-header">Quick Update</div>
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger text-xs">{{ $errors->first() }}</div>
          @endif
          <form method="POST" action="{{ route('leads.update',$lead) }}" class="space-y-3">
            @csrf @method('PUT')
            <input type="hidden" name="name"  value="{{ $lead->name }}">
            <input type="hidden" name="phone" value="{{ $lead->phone }}">
            <div>
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                @foreach(\App\Models\Lead::STATUSES as $k=>$v)
                  <option value="{{ $k }}" @selected($lead->status===$k)>{{ $v }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="form-label">Priority</label>
              <select name="priority" class="form-select">
                <option value="low" @selected($lead->priority==='low')>Low</option>
                <option value="medium" @selected($lead->priority==='medium')>Medium</option>
                <option value="high" @selected($lead->priority==='high')>High</option>
              </select>
            </div>
            <button class="btn btn-primary w-full">Save Changes</button>
          </form>
        </div>
      </div>

      @if(auth()->user()->isAdmin())
        <form method="POST" action="{{ route('leads.destroy',$lead) }}" onsubmit="return confirm('Delete this lead?')">
          @csrf @method('DELETE')
          <button class="btn btn-danger w-full">Delete Lead</button>
        </form>
      @endif
    </div>
  </div>

  @php
    $waTplJson    = json_encode($tplWa,    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $emailTplJson = json_encode($tplEmail, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
  @endphp
  <script>
    (function () {
      // ----- Tab switching -----
      const tabs   = document.querySelectorAll('#commCard .comm-tab');
      const panels = document.querySelectorAll('#commCard [data-tab-panel]');
      tabs.forEach(btn => btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        tabs.forEach(b => {
          const active = b.dataset.tab === tab;
          b.classList.toggle('border-gold-500', active);
          b.classList.toggle('border-transparent', !active);
          b.classList.toggle('text-ink-900', active);
          b.classList.toggle('bg-[#fdfaf0]', active);
          b.classList.toggle('text-gray-500', !active);
        });
        panels.forEach(p => p.style.display = (p.dataset.tabPanel === tab) ? '' : 'none');
      }));

      // Auto-scroll WhatsApp history to bottom on load
      const waHist = document.getElementById('waHistory');
      if (waHist) waHist.scrollTop = waHist.scrollHeight;

      // ----- WhatsApp template insertion -----
      const waTpl = {!! $waTplJson !!};
      const waSel = document.getElementById('waTemplateSel');
      if (waSel) {
        waSel.addEventListener('change', () => {
          const k = waSel.value;
          if (k && waTpl[k]) {
            document.getElementById('waBody').value = waTpl[k];
            document.getElementById('waTemplateKey').value = k;
          }
        });
      }

      // ----- Email template insertion -----
      const emailTpl = {!! $emailTplJson !!};
      const emailSel = document.getElementById('emailTemplateSel');
      if (emailSel) {
        emailSel.addEventListener('change', () => {
          const k = emailSel.value;
          if (k && emailTpl[k]) {
            document.getElementById('emailSubject').value = emailTpl[k].subject;
            document.getElementById('emailBody').value    = emailTpl[k].body;
            document.getElementById('emailTemplateKey').value = k;
          }
        });
      }
    })();
  </script>
@endsection
