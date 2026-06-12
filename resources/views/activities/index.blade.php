@extends('layouts.app')
@section('title','Activity Log')
@section('page-title','Activity Log')

@section('content')
  <div class="card mb-5">
    <div class="card-body">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-{{ auth()->user()->isAdmin() ? '5' : '4' }} gap-3">
        <div>
          <label class="form-label">Action</label>
          <select name="action" class="form-select">
            <option value="">All</option>
            @foreach(['lead_created','lead_updated','assigned','reassigned','status_changed','comment_added','followup_added','converted','lost','bulk_imported','whatsapp_sent','whatsapp_received','email_sent'] as $a)
              <option value="{{ $a }}" @selected(request('action')===$a)>{{ ucfirst(str_replace('_',' ',$a)) }}</option>
            @endforeach
          </select>
        </div>
        @if(auth()->user()->isAdmin())
          <div>
            <label class="form-label">Counselor</label>
            <select name="counselor_id" class="form-select">
              <option value="">All Counselors</option>
              @foreach($counselors as $c)
                <option value="{{ $c->id }}" @selected(request('counselor_id')==$c->id)>
                  {{ $c->name }}{{ !$c->is_active ? ' (inactive)' : '' }}
                </option>
              @endforeach
            </select>
          </div>
        @endif
        <div>
          <label class="form-label">From</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">To</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-input">
        </div>
        <div class="flex items-end gap-2">
          <button class="btn btn-dark">Filter</button>
          <a href="{{ route('activities.index') }}" class="btn btn-ghost">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body p-0">
      @forelse($activities as $a)
        <div class="px-5 py-3 border-b border-[#f3eede] flex gap-3 hover:bg-[#fdfaf0] items-start">
          <div class="timeline-dot {{ $a->color }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm text-ink-800">{{ $a->description }}</div>
            <div class="text-[11px] text-gray-500 mt-0.5">
              <span class="badge bg-gold-50 text-gold-800 border-gold-200">{{ str_replace('_',' ',$a->action) }}</span>
              by <strong>{{ $a->user->name ?? 'System' }}</strong>
              @if($a->fromUser && $a->toUser) · from {{ $a->fromUser->name }} → {{ $a->toUser->name }}@endif
              · {{ $a->created_at->format('d M Y, h:i A') }}
              · {{ $a->created_at->diffForHumans() }}
            </div>
          </div>
          @if($a->lead)
            <a href="{{ route('leads.show',$a->lead_id) }}" class="flex-shrink-0 text-xs text-gold-700 font-semibold hover:underline whitespace-nowrap" title="Open lead">
              View →
            </a>
          @endif
        </div>
      @empty
        <div class="px-5 py-14 text-center text-gray-500">No activity to show.</div>
      @endforelse
    </div>
    @if($activities->hasPages())
      <div class="p-4 border-t border-[#f3eede]">{{ $activities->links() }}</div>
    @endif
  </div>
@endsection
