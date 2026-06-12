@extends('layouts.app')
@section('title','My Profile')
@section('page-title','My Profile')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

  {{-- LEFT: profile card --}}
  <div class="space-y-5">
    <div class="card">
      <div class="card-body text-center">
        <div class="relative inline-block">
          @php $initial = strtoupper(substr($user->name,0,1)); @endphp
          @if($user->avatar_url)
            <img src="{{ $user->avatar_url }}" class="w-28 h-28 rounded-full object-cover border-4 border-gold-200 mx-auto" alt="avatar"
                 onerror="this.outerHTML='<div class=\'w-28 h-28 rounded-full mx-auto flex items-center justify-center font-bold text-white text-4xl\' style=\'background:linear-gradient(135deg,#b8923d,#7d6122);\'>{{ $initial }}</div>'">
          @else
            <div class="w-28 h-28 rounded-full mx-auto flex items-center justify-center font-bold text-white text-4xl" style="background:linear-gradient(135deg,#b8923d,#7d6122);">
              {{ $initial }}
            </div>
          @endif
        </div>
        <h2 class="font-serif text-2xl text-ink-900 mt-3">{{ $user->name }}</h2>
        <div class="text-sm text-gray-500">{{ $user->email }}</div>
        @if($user->phone)<div class="text-xs text-gold-700 mt-1">{{ $user->phone }}</div>@endif
        <div class="mt-3">
          <span class="badge {{ $user->isAdmin() ? 'bg-purple-100 text-purple-700 border-purple-300' : 'bg-gold-50 text-gold-800 border-gold-200' }}">
            {{ ucfirst($user->role) }}
          </span>
          @if($user->is_active)
            <span class="badge bg-emerald-100 text-emerald-700 border-emerald-300 ml-1">Active</span>
          @else
            <span class="badge bg-gray-200 text-gray-700 ml-1">Inactive</span>
          @endif
        </div>
        <div class="text-[11px] text-gray-400 mt-3">Member since {{ $user->created_at->format('d M Y') }}</div>
      </div>
    </div>

    @if($user->isCounselor())
      <div class="card">
        <div class="card-header">My Performance</div>
        <div class="card-body grid grid-cols-2 gap-3 text-center">
          <div class="p-3 rounded-lg bg-gold-50">
            <div class="text-2xl font-bold text-ink-900">{{ $stats['total_leads'] }}</div>
            <div class="text-[10px] uppercase tracking-wider text-gold-700 font-semibold">Total Leads</div>
          </div>
          <div class="p-3 rounded-lg bg-blue-50">
            <div class="text-2xl font-bold text-blue-700">{{ $stats['today_leads'] }}</div>
            <div class="text-[10px] uppercase tracking-wider text-blue-700 font-semibold">Today's Leads</div>
          </div>
          <div class="p-3 rounded-lg bg-amber-50">
            <div class="text-2xl font-bold text-amber-700">{{ $stats['today_followups'] }}</div>
            <div class="text-[10px] uppercase tracking-wider text-amber-700 font-semibold">Today's Follow-ups</div>
          </div>
          <div class="p-3 rounded-lg bg-rose-50">
            <div class="text-2xl font-bold text-rose-700">{{ $stats['overdue'] }}</div>
            <div class="text-[10px] uppercase tracking-wider text-rose-700 font-semibold">Overdue</div>
          </div>
          <div class="p-3 rounded-lg bg-emerald-50">
            <div class="text-2xl font-bold text-emerald-700">{{ $stats['converted'] }}</div>
            <div class="text-[10px] uppercase tracking-wider text-emerald-700 font-semibold">Converted</div>
          </div>
          <div class="p-3 rounded-lg bg-purple-50">
            <div class="text-2xl font-bold text-purple-700">{{ $stats['comments_made'] }}</div>
            <div class="text-[10px] uppercase tracking-wider text-purple-700 font-semibold">Comments</div>
          </div>
        </div>
      </div>
    @endif
  </div>

  {{-- RIGHT: forms --}}
  <div class="lg:col-span-2 space-y-5">
    <div class="card">
      <div class="card-header">Edit Profile</div>
      <div class="card-body">
        @if ($errors->updateProfile->any())
          <div class="alert alert-danger">{{ $errors->updateProfile->first() }}</div>
        @endif
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
          @csrf @method('PUT')
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="form-label">Full Name <span class="text-rose-500">*</span></label>
              <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
            </div>
            <div>
              <label class="form-label">Email <span class="text-xs text-gray-400 normal-case">(cannot be changed)</span></label>
              <input type="email" class="form-input bg-gray-100 cursor-not-allowed" value="{{ $user->email }}" disabled readonly>
            </div>
            <div>
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}">
            </div>
            <div>
              <label class="form-label">Profile Picture</label>
              <input type="file" name="avatar" accept="image/*" class="form-input">
              <div class="text-xs text-gray-500 mt-1">Max 2 MB. JPG/PNG.</div>
            </div>
          </div>
          <div class="flex justify-end">
            <button class="btn btn-primary">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Save Profile
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">Change Password</div>
      <div class="card-body">
        @if($errors->any() && $errors->has('current_password'))
          <div class="alert alert-danger">{{ $errors->first('current_password') }}</div>
        @endif
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
          @csrf @method('PUT')
          <div>
            <label class="form-label">Current Password <span class="text-rose-500">*</span></label>
            <input type="password" name="current_password" class="form-input" required>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="form-label">New Password <span class="text-rose-500">*</span></label>
              <input type="password" name="password" class="form-input" required minlength="6">
              <div class="text-xs text-gray-500 mt-1">Minimum 6 characters.</div>
            </div>
            <div>
              <label class="form-label">Confirm New Password <span class="text-rose-500">*</span></label>
              <input type="password" name="password_confirmation" class="form-input" required minlength="6">
            </div>
          </div>
          <div class="flex justify-end">
            <button class="btn btn-dark">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              Update Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
