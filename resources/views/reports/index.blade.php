@extends('layouts.app')
@section('title','Reports')
@section('page-title','Reports')

@section('header-actions')
  <a href="{{ route('reports.export', request()->query()) }}" class="btn btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
    Export CSV
  </a>
@endsection

@section('content')

{{-- FILTERS --}}
<div class="card mb-5">
  <div class="card-header">Filters</div>
  <div class="card-body">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-{{ auth()->user()->isAdmin() ? '5' : '4' }} gap-3">
      <div>
        <label class="form-label">Course</label>
        <select name="course" class="form-select">
          <option value="">All Courses</option>
          @foreach($courses as $c)
            <option value="{{ $c->name }}" @selected(request('course')===$c->name)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>

      @if(auth()->user()->isAdmin())
        <div>
          <label class="form-label">Counselor</label>
          <select name="counselor_id" class="form-select">
            <option value="">All Counselors</option>
            @foreach($counselors as $c)
              <option value="{{ $c->id }}" @selected(request('counselor_id')==$c->id)>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
      @endif

      <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">All Status</option>
          @foreach(\App\Models\Lead::STATUSES as $k=>$v)
            <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-input">
      </div>

      <div>
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-input">
      </div>

      <div class="md:col-span-{{ auth()->user()->isAdmin() ? '5' : '4' }} flex justify-end gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-ghost">Reset</a>
        <button class="btn btn-primary">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          Apply Filters
        </button>
      </div>
    </form>
  </div>
</div>

{{-- SUMMARY CARDS --}}
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-5">
  <div class="card-body bg-white rounded-xl border border-[#f3eede] text-center py-4">
    <div class="text-2xl font-bold text-ink-900">{{ $summary['total'] }}</div>
    <div class="text-[10px] uppercase tracking-wider text-gold-700 font-semibold">Total</div>
  </div>
  <div class="card-body bg-white rounded-xl border border-[#f3eede] text-center py-4">
    <div class="text-2xl font-bold text-blue-600">{{ $summary['new'] }}</div>
    <div class="text-[10px] uppercase tracking-wider text-gold-700 font-semibold">New</div>
  </div>
  <div class="card-body bg-white rounded-xl border border-[#f3eede] text-center py-4">
    <div class="text-2xl font-bold text-emerald-700">{{ $summary['good_lead'] }}</div>
    <div class="text-[10px] uppercase tracking-wider text-gold-700 font-semibold">Good Lead</div>
  </div>
  <div class="card-body bg-white rounded-xl border border-[#f3eede] text-center py-4">
    <div class="text-2xl font-bold text-sky-600">{{ $summary['in_conversation'] }}</div>
    <div class="text-[10px] uppercase tracking-wider text-gold-700 font-semibold">In Conversation</div>
  </div>
  <div class="card-body bg-white rounded-xl border border-[#f3eede] text-center py-4">
    <div class="text-2xl font-bold text-emerald-600">{{ $summary['converted'] }}</div>
    <div class="text-[10px] uppercase tracking-wider text-gold-700 font-semibold">Converted</div>
  </div>
  <div class="card-body bg-white rounded-xl border border-[#f3eede] text-center py-4">
    <div class="text-2xl font-bold text-rose-600">{{ $summary['lost'] }}</div>
    <div class="text-[10px] uppercase tracking-wider text-gold-700 font-semibold">Lost</div>
  </div>
  <div class="card-body bg-white rounded-xl border border-[#f3eede] text-center py-4">
    <div class="text-2xl font-bold text-red-700">{{ $summary['fake_lead'] }}</div>
    <div class="text-[10px] uppercase tracking-wider text-gold-700 font-semibold">Fake Lead</div>
  </div>
</div>

{{-- RESULTS TABLE --}}
<div class="card overflow-hidden">
  <div class="card-header">
    <span>Results ({{ $leads->total() }})</span>
    <a href="{{ route('reports.export', request()->query()) }}" class="text-xs font-semibold text-gold-700 hover:underline">Export this view →</a>
  </div>
  <div class="overflow-x-auto">
    <table class="table">
      <thead>
        <tr>
          <th>Lead</th>
          <th>Phone</th>
          <th>Course</th>
          <th>Source</th>
          @if(auth()->user()->isAdmin())<th>Counselor</th>@endif
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        @forelse($leads as $l)
          <tr class="cursor-pointer" onclick="window.location='{{ route('leads.show',$l) }}'">
            <td>
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white text-xs" style="background:linear-gradient(135deg,#b8923d,#7d6122);">
                  {{ strtoupper(substr($l->name,0,1)) }}
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-semibold text-ink-800">{{ $l->name }}</span>
                    <span class="badge {{ $l->status_color }} text-[10px]" style="padding:.1rem .45rem;">{{ $l->status_label }}</span>
                    @if($l->statusChangedBy)
                      <span class="text-[10px] text-gray-500 italic">by {{ $l->statusChangedBy->name }}</span>
                    @endif
                  </div>
                  @if($l->email)<div class="text-[11px] text-gray-500">{{ $l->email }}</div>@endif
                </div>
              </div>
            </td>
            <td>{{ $l->phone }}</td>
            <td>{{ $l->course ?: '—' }}</td>
            <td><span class="text-xs">{{ $l->source ? ucfirst(str_replace('_',' ',$l->source)) : '—' }}</span></td>
            @if(auth()->user()->isAdmin())
              <td>
                @if($l->assignedTo)
                  <span class="text-sm">{{ $l->assignedTo->name }}</span>
                @else
                  <span class="text-xs text-gray-400">Unassigned</span>
                @endif
              </td>
            @endif
            <td class="text-xs text-gray-500">{{ $l->created_at->format('d M Y') }}</td>
          </tr>
        @empty
          <tr><td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}" class="text-center py-10 text-gray-500">
            No leads match the current filters. Try adjusting the date range or status.
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($leads->hasPages())
    <div class="px-5 py-3 border-t border-[#f3eede]">{{ $leads->links() }}</div>
  @endif
</div>
@endsection
