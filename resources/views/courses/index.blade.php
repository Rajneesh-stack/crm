@extends('layouts.app')
@section('title','Courses')
@section('page-title','Courses')

@section('content')
  <div class="card">
    <div class="card-header">
      <span>{{ $courses->count() }} courses</span>
      <button onclick="document.getElementById('addCourseModal').classList.remove('hidden')" class="btn btn-primary btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Add Course
      </button>
    </div>
    <div class="card-body p-0">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-xs uppercase tracking-wider text-gold-700 border-b border-[#f3eede] bg-[#fdfaf0]/40">
            <th class="px-5 py-3 text-left">#</th>
            <th class="px-5 py-3 text-left">Course Name</th>
            <th class="px-5 py-3 text-center">Status</th>
            <th class="px-5 py-3 text-center">Leads</th>
            <th class="px-5 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($courses as $c)
            <tr class="border-b border-[#f3eede]" id="row-{{ $c->id }}">
              <td class="px-5 py-3 text-gray-500">{{ $c->sort_order }}</td>
              <td class="px-5 py-3 font-semibold">{{ $c->name }}</td>
              <td class="px-5 py-3 text-center">
                @if($c->is_active)
                  <span class="badge bg-emerald-100 text-emerald-700 border-emerald-300">Active</span>
                @else
                  <span class="badge bg-gray-200 text-gray-700">Inactive</span>
                @endif
              </td>
              <td class="px-5 py-3 text-center">{{ $c->lead_count }}</td>
              <td class="px-5 py-3 text-right">
                <button onclick="document.getElementById('edit-{{ $c->id }}').classList.toggle('hidden')" class="btn btn-ghost btn-sm">Edit</button>
                <form method="POST" action="{{ route('courses.destroy', $c) }}" class="inline" onsubmit="return confirm('Delete this course? (Will be deactivated if in use)')">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
            <tr id="edit-{{ $c->id }}" class="hidden bg-[#fdfaf0]/30">
              <td colspan="5" class="px-5 py-4">
                <form method="POST" action="{{ route('courses.update', $c) }}" class="flex flex-wrap items-end gap-3">
                  @csrf @method('PUT')
                  <div class="flex-1 min-w-[200px]">
                    <label class="form-label">Course Name</label>
                    <input type="text" name="name" value="{{ $c->name }}" class="form-input" required>
                  </div>
                  <div>
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ $c->sort_order }}" class="form-input" style="width:90px;" min="0">
                  </div>
                  <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked($c->is_active)> Active
                  </label>
                  <button class="btn btn-primary btn-sm">Save</button>
                  <button type="button" onclick="document.getElementById('edit-{{ $c->id }}').classList.add('hidden')" class="btn btn-ghost btn-sm">Cancel</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No courses yet. Click "Add Course" to start.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div id="addCourseModal" class="modal-backdrop hidden">
    <div class="modal" style="max-width:480px;">
      <div class="modal-header">
        <h3 class="font-serif text-2xl text-ink-900">Add Course</h3>
        <button onclick="document.getElementById('addCourseModal').classList.add('hidden')" class="btn btn-ghost btn-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <form method="POST" action="{{ route('courses.store') }}">
        @csrf
        <div class="modal-body space-y-3">
          <div>
            <label class="form-label">Course Name <span class="text-rose-500">*</span></label>
            <input name="name" class="form-input" placeholder="e.g. PG Diploma in Data Science" required maxlength="191">
          </div>
          <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" checked> Active (show in lead forms)
          </label>
        </div>
        <div class="modal-footer">
          <button type="button" onclick="document.getElementById('addCourseModal').classList.add('hidden')" class="btn btn-ghost">Cancel</button>
          <button class="btn btn-primary">Create Course</button>
        </div>
      </form>
    </div>
  </div>
@endsection
