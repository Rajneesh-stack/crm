@extends('layouts.app')
@section('title','Counselors')
@section('page-title','Counselors')

@section('header-actions')
  <button onclick="document.getElementById('addCounselorModal').classList.remove('hidden')" class="btn btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
    Add Counselor
  </button>
@endsection

@section('content')
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($counselors as $c)
      <div class="card">
        <div class="card-body">
          <div class="flex items-start gap-3">
            <div class="w-14 h-14 rounded-full flex items-center justify-center font-bold text-white text-lg" style="background:linear-gradient(135deg,#b8923d,#7d6122);">
              {{ strtoupper(substr($c->name,0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <h3 class="font-semibold text-ink-900 truncate">{{ $c->name }}</h3>
                @if($c->is_active)
                  <span class="badge bg-emerald-100 text-emerald-700 border-emerald-300">Active</span>
                @else
                  <span class="badge bg-gray-200 text-gray-700">Inactive</span>
                @endif
              </div>
              <div class="text-xs text-gray-500 truncate">{{ $c->email }}</div>
              @if($c->phone)<div class="text-xs text-gray-500">{{ $c->phone }}</div>@endif
            </div>
            <div class="flex gap-1">
              {{-- Quick Activate/Deactivate toggle --}}
              <form method="POST" action="{{ route('counselors.toggle', $c) }}" onsubmit="return confirm('{{ $c->is_active ? 'Deactivate' : 'Activate' }} {{ $c->name }}?')">
                @csrf @method('PATCH')
                @if($c->is_active)
                  <button class="btn btn-ghost btn-sm" style="color:#b8923d;" title="Deactivate">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                  </button>
                @else
                  <button class="btn btn-ghost btn-sm" style="color:#059669;" title="Activate">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                  </button>
                @endif
              </form>
              <button onclick="document.getElementById('editC{{ $c->id }}').classList.toggle('hidden')" class="btn btn-ghost btn-sm">Edit</button>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-[#f3eede] text-center">
            <div>
              <div class="text-xl font-bold">{{ $c->assigned_leads_count }}</div>
              <div class="text-[10px] uppercase tracking-wider text-gold-700">Leads</div>
            </div>
            <div>
              <div class="text-xl font-bold text-blue-600">{{ $c->today_count }}</div>
              <div class="text-[10px] uppercase tracking-wider text-gold-700">Today</div>
            </div>
            <div>
              <div class="text-xl font-bold text-emerald-600">{{ $c->converted_count }}</div>
              <div class="text-[10px] uppercase tracking-wider text-gold-700">Converted</div>
            </div>
          </div>

          <div id="editC{{ $c->id }}" class="hidden mt-4 pt-4 border-t border-[#f3eede] space-y-2">
            {{-- Edit form (no longer has Deactivate button nested inside) --}}
            <form method="POST" action="{{ route('counselors.update', $c) }}" class="space-y-2">
              @csrf @method('PUT')
              <input type="text" name="name" value="{{ $c->name }}" class="form-input" placeholder="Name" required>
              <input type="email" name="email" value="{{ $c->email }}" class="form-input" placeholder="Email" required>
              <input type="text" name="phone" value="{{ $c->phone }}" class="form-input" placeholder="Phone">
              <input type="password" name="password" class="form-input" placeholder="New password (optional)">
              <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($c->is_active)> Active</label>
              <button class="btn btn-primary btn-sm w-full">Save Changes</button>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="col-span-3 card"><div class="card-body text-center text-gray-500 py-10">No counselors yet. Click "Add Counselor" to start.</div></div>
    @endforelse
  </div>

  <div id="addCounselorModal" class="modal-backdrop hidden">
    <div class="modal" style="max-width:500px;">
      <div class="modal-header">
        <h3 class="font-serif text-2xl text-ink-900">Add Counselor</h3>
        <button onclick="document.getElementById('addCounselorModal').classList.add('hidden')" class="btn btn-ghost btn-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <form method="POST" action="{{ route('counselors.store') }}">
        @csrf
        <div class="modal-body space-y-3">
          <div>
            <label class="form-label">Name <span class="text-rose-500">*</span></label>
            <input name="name" class="form-input" required>
          </div>
          <div>
            <label class="form-label">Email <span class="text-rose-500">*</span></label>
            <input type="email" name="email" class="form-input" required>
          </div>
          <div>
            <label class="form-label">Phone</label>
            <input name="phone" class="form-input">
          </div>
          <div>
            <label class="form-label">Password <span class="text-rose-500">*</span></label>
            <input type="password" name="password" class="form-input" required minlength="6">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" onclick="document.getElementById('addCounselorModal').classList.add('hidden')" class="btn btn-ghost">Cancel</button>
          <button class="btn btn-primary">Create Counselor</button>
        </div>
      </form>
    </div>
  </div>
@endsection
