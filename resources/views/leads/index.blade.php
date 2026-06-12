@extends('layouts.app')
@section('title','Leads')
@section('page-title','Leads')

@section('header-actions')
  <button onclick="document.getElementById('addLeadModal').classList.remove('hidden')" class="btn btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
    Add Lead
  </button>
@endsection

@section('content')
  <div class="card mb-5">
    <div class="card-body">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <div class="md:col-span-2">
          <label class="form-label">Search</label>
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, phone, email, city, course..." class="form-input">
        </div>
        <div>
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="">All</option>
            @foreach(\App\Models\Lead::STATUSES as $k=>$v)
              <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
            @endforeach
          </select>
        </div>
        @if(auth()->user()->isAdmin())
          <div>
            <label class="form-label">Counselor</label>
            <select name="counselor_id" class="form-select">
              <option value="">All</option>
              @foreach($counselors as $c)
                <option value="{{ $c->id }}" @selected(request('counselor_id')==$c->id)>{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
        @endif
        <div>
          <label class="form-label">Course</label>
          <select name="course" class="form-select">
            <option value="">All Courses</option>
            @foreach($courses ?? [] as $c)
              <option value="{{ $c->name }}" @selected(request('course')===$c->name)>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="form-label">Filter</label>
          <select name="filter" class="form-select">
            <option value="">All</option>
            <option value="today" @selected(request('filter')==='today')>Today's Leads</option>
            <option value="today_followups" @selected(request('filter')==='today_followups')>Today's Follow-ups</option>
            <option value="overdue" @selected(request('filter')==='overdue')>Overdue</option>
            <option value="good_lead" @selected(request('filter')==='good_lead')>Good Lead</option>
            <option value="in_conversation" @selected(request('filter')==='in_conversation')>In Conversation</option>
            <option value="fake_lead" @selected(request('filter')==='fake_lead')>Fake Lead</option>
            <option value="converted" @selected(request('filter')==='converted')>Converted</option>
          </select>
        </div>
        <div class="md:col-span-5 flex gap-2">
          <button type="submit" class="btn btn-dark">Filter</button>
          <a href="{{ route('leads.index') }}" class="btn btn-ghost">Reset</a>
          @if(request('filter')||request('status')||request('search')||request('counselor_id'))
            <span class="ml-2 text-xs text-gray-500 self-center">Showing {{ $leads->total() }} of total matches</span>
          @endif
        </div>
      </form>
    </div>
  </div>

  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="table">
        <thead>
          <tr>
            <th>Lead</th>
            <th>Phone</th>
            <th>Course</th>
            <th>Source</th>
            <th>Next Follow-up</th>
            <th>Counselor</th>
            <th>Added</th>
          </tr>
        </thead>
        <tbody>
          @forelse($leads as $lead)
            <tr class="cursor-pointer" onclick="window.location='{{ route('leads.show',$lead) }}'">
              <td>
                <div class="flex items-center gap-2">
                  <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-white text-xs" style="background:linear-gradient(135deg,#b8923d,#7d6122);">
                    {{ strtoupper(substr($lead->name,0,1)) }}
                  </div>
                  <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span class="font-semibold text-ink-800">{{ $lead->name }}</span>
                      <span class="badge {{ $lead->status_color }} text-[10px]" style="padding:.1rem .45rem;">{{ $lead->status_label }}</span>
                      @if($lead->statusChangedBy)
                        <span class="text-[10px] text-gray-500 italic">by {{ $lead->statusChangedBy->name }}</span>
                      @endif
                    </div>
                    @if($lead->email)<div class="text-[11px] text-gray-500">{{ $lead->email }}</div>@endif
                  </div>
                </div>
              </td>
              <td>{{ $lead->phone }}</td>
              <td>{{ $lead->course ?? '—' }}</td>
              <td><span class="text-xs">{{ $lead->source ? ucfirst(str_replace('_',' ',$lead->source)) : '—' }}</span></td>
              <td>
                @if($lead->next_followup_date)
                  @php $past = $lead->next_followup_date->isPast() && !$lead->next_followup_date->isToday(); @endphp
                  <span class="{{ $past ? 'text-rose-600 font-semibold' : ($lead->next_followup_date->isToday() ? 'text-amber-600 font-semibold' : 'text-gray-700') }}">
                    {{ $lead->next_followup_date->format('d M Y') }}
                  </span>
                @else
                  <span class="text-gray-400">—</span>
                @endif
              </td>
              <td>
                @if($lead->assignedTo)
                  <span class="inline-flex items-center gap-1.5">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-white text-[10px]" style="background:linear-gradient(135deg,#b8923d,#7d6122);">
                      {{ strtoupper(substr($lead->assignedTo->name,0,1)) }}
                    </span>
                    <span class="text-xs">{{ $lead->assignedTo->name }}</span>
                  </span>
                @else
                  <span class="text-xs text-gray-400">Unassigned</span>
                @endif
              </td>
              <td><span class="text-xs text-gray-500">{{ $lead->created_at->diffForHumans() }}</span></td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center py-10 text-gray-500">No leads found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($leads->hasPages())
      <div class="p-4 border-t border-[#f3eede]">{{ $leads->links() }}</div>
    @endif
  </div>

  @include('leads.partials.add-lead-modal', ['availableFields'=>$availableFields,'counselors'=>$counselors])
@endsection
