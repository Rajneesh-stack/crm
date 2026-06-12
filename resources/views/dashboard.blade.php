@extends('layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')

@section('header-actions')
  <button onclick="document.getElementById('addLeadModal').classList.remove('hidden')" class="btn btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
    Add Lead
  </button>
@endsection

@section('content')
  {{-- 4 STAT CARDS --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('leads.index',['filter'=>'today']) }}" class="stat-card info hover:no-underline">
      <div class="flex items-start justify-between">
        <div>
          <div class="stat-label">Today's Leads</div>
          <div class="stat-value mt-1">{{ $todayLeadsCount }}</div>
          <div class="stat-sub mt-1">New leads added today</div>
        </div>
        <span class="stat-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
        </span>
      </div>
    </a>

    <a href="{{ route('leads.index',['filter'=>'today_followups']) }}" class="stat-card warning hover:no-underline">
      <div class="flex items-start justify-between">
        <div>
          <div class="stat-label">Today's Follow-ups</div>
          <div class="stat-value mt-1">{{ $todayFollowupsCount }}</div>
          <div class="stat-sub mt-1">Pending calls today</div>
        </div>
        <span class="stat-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </span>
      </div>
    </a>

    <a href="{{ route('leads.index',['filter'=>'overdue']) }}" class="stat-card danger hover:no-underline">
      <div class="flex items-start justify-between">
        <div>
          <div class="stat-label">Overdue</div>
          <div class="stat-value mt-1">{{ $overdueFollowupsCount }}</div>
          <div class="stat-sub mt-1">Missed follow-ups</div>
        </div>
        <span class="stat-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3l-6.93-12a2 2 0 00-3.48 0l-6.93 12a2 2 0 001.74 3z"/></svg>
        </span>
      </div>
    </a>

    <a href="{{ route('leads.index',['filter'=>'converted']) }}" class="stat-card success hover:no-underline">
      <div class="flex items-start justify-between">
        <div>
          <div class="stat-label">Conversion</div>
          <div class="stat-value mt-1">{{ $convertedCount }} <span class="text-base font-medium text-gold-700">/ {{ $conversionRate }}%</span></div>
          <div class="stat-sub mt-1">Converted leads</div>
        </div>
        <span class="stat-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </span>
      </div>
    </a>
  </div>

  {{-- 3 ADDITIONAL CLASSIFICATION CARDS --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <a href="{{ route('leads.index',['filter'=>'good_lead']) }}" class="stat-card success hover:no-underline">
      <div class="flex items-start justify-between">
        <div>
          <div class="stat-label">Good Lead</div>
          <div class="stat-value mt-1">{{ $goodLeadCount ?? 0 }}</div>
          <div class="stat-sub mt-1">High-quality prospects</div>
        </div>
        <span class="stat-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
        </span>
      </div>
    </a>

    <a href="{{ route('leads.index',['filter'=>'in_conversation']) }}" class="stat-card info hover:no-underline">
      <div class="flex items-start justify-between">
        <div>
          <div class="stat-label">In Conversation</div>
          <div class="stat-value mt-1">{{ $inConversationCount ?? 0 }}</div>
          <div class="stat-sub mt-1">Active discussions</div>
        </div>
        <span class="stat-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </span>
      </div>
    </a>

    <a href="{{ route('leads.index',['filter'=>'fake_lead']) }}" class="stat-card danger hover:no-underline">
      <div class="flex items-start justify-between">
        <div>
          <div class="stat-label">Fake Lead</div>
          <div class="stat-value mt-1">{{ $fakeLeadCount ?? 0 }}</div>
          <div class="stat-sub mt-1">Marked as fake / spam</div>
        </div>
        <span class="stat-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </span>
      </div>
    </a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Today's followups list --}}
    <div class="card lg:col-span-2">
      <div class="card-header">
        <span>Today's Follow-ups</span>
        <a href="{{ route('leads.index',['filter'=>'today_followups']) }}" class="text-xs text-gold-700 font-semibold hover:underline">View all →</a>
      </div>
      <div class="card-body p-0 max-h-[420px] overflow-y-auto scrollbar-thin">
        @forelse($todayFollowups as $f)
          <a href="{{ route('leads.show',$f->lead) }}" class="flex items-center justify-between gap-3 px-4 py-3 border-b border-[#f3eede] hover:bg-[#fdfaf0]">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-white text-sm" style="background:linear-gradient(135deg,#b8923d,#7d6122);">
                {{ strtoupper(substr($f->lead->name ?? '?',0,1)) }}
              </div>
              <div class="min-w-0">
                <div class="font-semibold text-ink-800 truncate">{{ $f->lead->name ?? '—' }}</div>
                <div class="text-xs text-gray-500">{{ $f->lead->phone }} · {{ $f->lead->course ?? '—' }}</div>
              </div>
            </div>
            <div class="text-right">
              <span class="badge {{ $f->lead->status_color ?? '' }}">{{ $f->lead->status_label ?? '' }}</span>
              @if($f->scheduled_time)<div class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($f->scheduled_time)->format('h:i A') }}</div>@endif
            </div>
          </a>
        @empty
          <div class="px-4 py-10 text-center text-gray-500 text-sm">No follow-ups scheduled today. 🎉</div>
        @endforelse
      </div>
    </div>

    {{-- Activity log preview --}}
    <div class="card">
      <div class="card-header">
        <span>Recent Activity</span>
        <a href="{{ route('activities.index') }}" class="text-xs text-gold-700 font-semibold hover:underline">View all →</a>
      </div>
      <div class="card-body p-0 max-h-[420px] overflow-y-auto scrollbar-thin">
        @forelse($recentActivities as $a)
          <a href="{{ $a->lead ? route('leads.show',$a->lead_id) : '#' }}" class="px-4 py-3 border-b border-[#f3eede] flex gap-3 hover:bg-[#fdfaf0]">
            <div class="timeline-dot {{ $a->color }}">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-sm text-ink-800">{{ $a->description }}</div>
              <div class="text-[11px] text-gray-500 mt-1">
                {{ $a->user->name ?? 'System' }} ·
                <span title="{{ $a->created_at }}">{{ $a->created_at->diffForHumans() }}</span>
              </div>
            </div>
          </a>
        @empty
          <div class="px-4 py-10 text-center text-gray-500 text-sm">No activity yet.</div>
        @endforelse
      </div>
    </div>
  </div>

  @if($overdueFollowups->isNotEmpty())
    <div class="card mt-5 border-rose-200">
      <div class="card-header bg-rose-50/40">
        <span class="text-rose-700 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3l-6.93-12a2 2 0 00-3.48 0l-6.93 12a2 2 0 001.74 3z"/></svg>
          Overdue Follow-ups
        </span>
        <a href="{{ route('leads.index',['filter'=>'overdue']) }}" class="text-xs text-rose-700 font-semibold hover:underline">View all →</a>
      </div>
      <div class="card-body p-0">
        <table class="table">
          <thead><tr><th>Lead</th><th>Phone</th><th>Course</th><th>Scheduled</th><th>Counselor</th><th></th></tr></thead>
          <tbody>
            @foreach($overdueFollowups as $f)
              <tr>
                <td><a href="{{ route('leads.show',$f->lead) }}" class="font-semibold text-ink-800 hover:text-gold-700">{{ $f->lead->name }}</a></td>
                <td>{{ $f->lead->phone }}</td>
                <td>{{ $f->lead->course ?? '—' }}</td>
                <td><span class="text-rose-600 font-semibold">{{ $f->scheduled_date->format('d M Y') }}</span></td>
                <td>{{ $f->user->name ?? '—' }}</td>
                <td><a href="{{ route('leads.show',$f->lead) }}" class="btn btn-sm btn-ghost">Open</a></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  @if(auth()->user()->isAdmin() && $counselorStats->isNotEmpty())
    <div class="card mt-5">
      <div class="card-header"><span>Counselor Performance</span>
        <a href="{{ route('counselors.index') }}" class="text-xs text-gold-700 font-semibold hover:underline">Manage →</a>
      </div>
      <div class="card-body p-0 overflow-x-auto">
        <table class="table">
          <thead><tr><th>Counselor</th><th>Total Leads</th><th>Today</th><th>Today's Follow-ups</th><th>Overdue</th><th>Converted</th></tr></thead>
          <tbody>
            @foreach($counselorStats as $cs)
              <tr>
                <td>
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white text-xs" style="background:linear-gradient(135deg,#b8923d,#7d6122);">
                      {{ strtoupper(substr($cs['user']->name,0,1)) }}
                    </div>
                    <div>
                      <div class="font-semibold">{{ $cs['user']->name }}</div>
                      <div class="text-[11px] text-gray-500">{{ $cs['user']->email }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ $cs['total'] }}</td>
                <td>{{ $cs['today'] }}</td>
                <td>{{ $cs['today_followups'] }}</td>
                <td><span class="{{ $cs['overdue']>0 ? 'text-rose-600 font-semibold':'' }}">{{ $cs['overdue'] }}</span></td>
                <td class="text-emerald-600 font-semibold">{{ $cs['converted'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  @include('leads.partials.add-lead-modal', ['availableFields'=>$availableFields,'counselors'=>$counselors])
@endsection
