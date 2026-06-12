@extends('layouts.app')
@section('title','Message Templates')
@section('page-title','Message Templates')

@section('content')
<div class="card" id="tplCard">
  <div class="card-header p-0">
    <div class="flex w-full">
      <button type="button" data-tab="whatsapp" class="tpl-tab flex-1 px-5 py-3 text-sm font-semibold flex items-center justify-center gap-2 border-b-2 border-gold-500 text-ink-900 bg-[#fdfaf0]">
        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24z"/></svg>
        WhatsApp ({{ $whatsapp->count() }})
      </button>
      <button type="button" data-tab="email" class="tpl-tab flex-1 px-5 py-3 text-sm font-semibold flex items-center justify-center gap-2 border-b-2 border-transparent text-gray-500 hover:bg-[#fdfaf0]/50">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Email ({{ $email->count() }})
      </button>
    </div>
  </div>

  {{-- WHATSAPP TAB --}}
  <div data-tab-panel="whatsapp" class="p-5 space-y-4">
    <div class="flex items-center justify-between">
      <div class="text-xs text-gray-500">
        Variables you can use: <code>{name}</code> <code>{course}</code> <code>{counselor}</code> <code>{phone}</code> <code>{email}</code>
      </div>
      <button onclick="document.getElementById('addWaModal').classList.remove('hidden')" class="btn btn-primary btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Add WhatsApp Template
      </button>
    </div>

    @forelse($whatsapp as $t)
      <div class="border border-[#f3eede] rounded-lg overflow-hidden">
        <div class="flex items-center justify-between bg-[#fdfaf0]/40 px-4 py-2.5 border-b border-[#f3eede]">
          <div class="flex items-center gap-2">
            <span class="font-semibold text-ink-800">{{ $t->label }}</span>
            <span class="text-[10px] text-gray-500 font-mono">{{ $t->key }}</span>
            @if(!$t->is_active)<span class="badge bg-gray-200 text-gray-700">Inactive</span>@endif
          </div>
          <div class="flex gap-1">
            <button onclick="document.getElementById('edit-{{ $t->id }}').classList.toggle('hidden')" class="btn btn-ghost btn-sm">Edit</button>
            <form method="POST" action="{{ route('templates.destroy',$t) }}" class="inline" onsubmit="return confirm('Delete this template?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </div>
        <div class="px-4 py-3 text-sm whitespace-pre-wrap text-ink-800 bg-white">{{ $t->body }}</div>

        <div id="edit-{{ $t->id }}" class="hidden p-4 bg-[#fdfaf0]/30 border-t border-[#f3eede]">
          <form method="POST" action="{{ route('templates.update',$t) }}" class="space-y-3">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="sm:col-span-2">
                <label class="form-label">Label</label>
                <input type="text" name="label" value="{{ $t->label }}" class="form-input" required>
              </div>
              <div>
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" value="{{ $t->sort_order }}" class="form-input" min="0">
              </div>
            </div>
            <div>
              <label class="form-label">Body</label>
              <textarea name="body" rows="4" class="form-textarea" required>{{ $t->body }}</textarea>
            </div>
            <div class="flex items-center justify-between">
              <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked($t->is_active)> Active
              </label>
              <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('edit-{{ $t->id }}').classList.add('hidden')" class="btn btn-ghost btn-sm">Cancel</button>
                <button class="btn btn-primary btn-sm">Save Changes</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    @empty
      <div class="text-center text-gray-500 py-10">No WhatsApp templates yet. Click "Add WhatsApp Template" to start.</div>
    @endforelse
  </div>

  {{-- EMAIL TAB --}}
  <div data-tab-panel="email" class="p-5 space-y-4" style="display:none;">
    <div class="flex items-center justify-between">
      <div class="text-xs text-gray-500">
        Variables: <code>{name}</code> <code>{course}</code> <code>{counselor}</code>
      </div>
      <button onclick="document.getElementById('addEmailModal').classList.remove('hidden')" class="btn btn-primary btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Add Email Template
      </button>
    </div>

    @forelse($email as $t)
      <div class="border border-[#f3eede] rounded-lg overflow-hidden">
        <div class="flex items-center justify-between bg-[#fdfaf0]/40 px-4 py-2.5 border-b border-[#f3eede]">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-semibold text-ink-800">{{ $t->label }}</span>
            <span class="text-[10px] text-gray-500 font-mono">{{ $t->key }}</span>
            @if(!$t->is_active)<span class="badge bg-gray-200 text-gray-700">Inactive</span>@endif
            @if(!empty($t->attachments))
              <span class="badge bg-blue-50 text-blue-700 border-blue-200 text-[10px]">
                📎 {{ count($t->attachments) }} attachment{{ count($t->attachments) > 1 ? 's' : '' }}
              </span>
            @endif
          </div>
          <div class="flex gap-1">
            <button onclick="document.getElementById('edit-{{ $t->id }}').classList.toggle('hidden')" class="btn btn-ghost btn-sm">Edit</button>
            <form method="POST" action="{{ route('templates.destroy',$t) }}" class="inline" onsubmit="return confirm('Delete this template and its attached files?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </div>
        <div class="px-4 py-3 bg-white">
          <div class="text-xs text-gray-500 mb-1">Subject:</div>
          <div class="text-sm font-semibold text-ink-800 mb-2">{{ $t->subject }}</div>
          <div class="text-xs text-gray-500 mb-1">Body:</div>
          <div class="text-sm whitespace-pre-wrap text-ink-800">{{ $t->body }}</div>
          @if(!empty($t->attachments))
            <div class="mt-3 pt-3 border-t border-dashed border-gold-200">
              <div class="text-xs text-gray-500 mb-1">Attachments:</div>
              <ul class="space-y-1">
                @foreach($t->attachments as $att)
                  <li class="text-xs flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <a href="{{ asset('storage/'.$att) }}" target="_blank" class="text-gold-700 hover:underline truncate">{{ basename($att) }}</a>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>

        <div id="edit-{{ $t->id }}" class="hidden p-4 bg-[#fdfaf0]/30 border-t border-[#f3eede]">
          <form method="POST" action="{{ route('templates.update',$t) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="sm:col-span-2">
                <label class="form-label">Label</label>
                <input type="text" name="label" value="{{ $t->label }}" class="form-input" required>
              </div>
              <div>
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" value="{{ $t->sort_order }}" class="form-input" min="0">
              </div>
            </div>
            <div>
              <label class="form-label">Subject</label>
              <input type="text" name="subject" value="{{ $t->subject }}" class="form-input" required>
            </div>
            <div>
              <label class="form-label">Body</label>
              <textarea name="body" rows="6" class="form-textarea" required>{{ $t->body }}</textarea>
            </div>

            {{-- EXISTING ATTACHMENTS --}}
            @if(!empty($t->attachments))
              <div>
                <label class="form-label">Existing Attachments</label>
                <div class="space-y-1.5 bg-white border border-[#f3eede] rounded-lg p-3">
                  @foreach($t->attachments as $att)
                    <label class="flex items-center gap-2 text-xs">
                      <input type="checkbox" name="remove_attachment[]" value="{{ $att }}">
                      <a href="{{ asset('storage/'.$att) }}" target="_blank" class="text-gold-700 hover:underline">{{ basename($att) }}</a>
                      <span class="text-gray-400 ml-auto">tick to delete</span>
                    </label>
                  @endforeach
                </div>
              </div>
            @endif

            {{-- ADD NEW ATTACHMENTS --}}
            <div>
              <label class="form-label">Add New Attachments</label>
              <input type="file" name="attachments[]" multiple class="form-input">
              <div class="text-[11px] text-gray-500 mt-1">Max 10 MB per file. Multiple files allowed (Ctrl/Cmd + click).</div>
            </div>

            <div class="flex items-center justify-between">
              <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked($t->is_active)> Active
              </label>
              <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('edit-{{ $t->id }}').classList.add('hidden')" class="btn btn-ghost btn-sm">Cancel</button>
                <button class="btn btn-primary btn-sm">Save Changes</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    @empty
      <div class="text-center text-gray-500 py-10">No email templates yet. Click "Add Email Template" to start.</div>
    @endforelse
  </div>
</div>

{{-- ADD WHATSAPP MODAL --}}
<div id="addWaModal" class="modal-backdrop hidden">
  <div class="modal" style="max-width:600px;">
    <div class="modal-header">
      <h3 class="font-serif text-2xl text-ink-900">New WhatsApp Template</h3>
      <button onclick="document.getElementById('addWaModal').classList.add('hidden')" class="btn btn-ghost btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form method="POST" action="{{ route('templates.store') }}">
      @csrf
      <input type="hidden" name="channel" value="whatsapp">
      <div class="modal-body space-y-3">
        <div>
          <label class="form-label">Label <span class="text-rose-500">*</span></label>
          <input type="text" name="label" class="form-input" placeholder="e.g. Welcome message" required>
        </div>
        <div>
          <label class="form-label">Body <span class="text-rose-500">*</span></label>
          <textarea name="body" rows="5" class="form-textarea" placeholder="Hello {name}, thanks for ..." required></textarea>
          <div class="text-[11px] text-gray-500 mt-1">Use <code>{name}</code>, <code>{course}</code>, <code>{counselor}</code> as placeholders.</div>
        </div>
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" name="is_active" value="1" checked> Active
        </label>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="document.getElementById('addWaModal').classList.add('hidden')" class="btn btn-ghost">Cancel</button>
        <button class="btn btn-primary">Create Template</button>
      </div>
    </form>
  </div>
</div>

{{-- ADD EMAIL MODAL --}}
<div id="addEmailModal" class="modal-backdrop hidden">
  <div class="modal" style="max-width:640px;">
    <div class="modal-header">
      <h3 class="font-serif text-2xl text-ink-900">New Email Template</h3>
      <button onclick="document.getElementById('addEmailModal').classList.add('hidden')" class="btn btn-ghost btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form method="POST" action="{{ route('templates.store') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="channel" value="email">
      <div class="modal-body space-y-3">
        <div>
          <label class="form-label">Label <span class="text-rose-500">*</span></label>
          <input type="text" name="label" class="form-input" placeholder="e.g. Brochure follow-up" required>
        </div>
        <div>
          <label class="form-label">Subject <span class="text-rose-500">*</span></label>
          <input type="text" name="subject" class="form-input" placeholder="Following up on {course}" required>
        </div>
        <div>
          <label class="form-label">Body <span class="text-rose-500">*</span></label>
          <textarea name="body" rows="8" class="form-textarea" placeholder="Hi {name}, ..." required></textarea>
          <div class="text-[11px] text-gray-500 mt-1">Use <code>{name}</code>, <code>{course}</code>, <code>{counselor}</code> as placeholders.</div>
        </div>
        <div>
          <label class="form-label">Attachments</label>
          <input type="file" name="attachments[]" multiple class="form-input">
          <div class="text-[11px] text-gray-500 mt-1">Optional. Max 10 MB per file. Multiple files allowed.</div>
        </div>
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" name="is_active" value="1" checked> Active
        </label>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="document.getElementById('addEmailModal').classList.add('hidden')" class="btn btn-ghost">Cancel</button>
        <button class="btn btn-primary">Create Template</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function () {
    const tabs   = document.querySelectorAll('#tplCard .tpl-tab');
    const panels = document.querySelectorAll('#tplCard [data-tab-panel]');
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
  })();
</script>
@endsection
