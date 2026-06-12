{{-- Add Lead Modal: side checkboxes drive which fields are shown --}}
<div id="addLeadModal" class="modal-backdrop hidden">
  <div class="modal">
    <div class="modal-header">
      <div>
        <h3 class="font-serif text-2xl text-ink-900">Add New Lead</h3>
        <p class="text-xs text-gray-500 mt-1">Tick the fields you want to fill — only checked fields will be saved.</p>
      </div>
      <button type="button" onclick="document.getElementById('addLeadModal').classList.add('hidden')" class="btn btn-ghost btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="addLeadForm" method="POST" action="{{ route('leads.store') }}" class="flex-1 overflow-hidden flex flex-col">
      @csrf
      <div class="flex-1 overflow-hidden grid grid-cols-1 md:grid-cols-[280px_1fr]">

        {{-- LEFT: Field checkbox panel --}}
        <aside class="bg-[#faf5e7] border-r border-[#ece6d4] overflow-y-auto scrollbar-thin p-4">
          <div class="text-xs uppercase tracking-wider text-gold-700 font-bold mb-2">Select Fields</div>
          <p class="text-[11px] text-gray-500 mb-3">Name & Phone are always required.</p>

          <div class="space-y-4">
            @foreach($availableFields as $group => $fields)
              <div>
                <div class="flex items-center justify-between mb-1">
                  <div class="text-[11px] uppercase tracking-wider font-semibold text-ink-700">{{ $group }}</div>
                  <button type="button" data-toggle-group="{{ \Illuminate\Support\Str::slug($group) }}" class="text-[10px] text-gold-700 font-semibold hover:underline">All</button>
                </div>
                <div class="space-y-1.5" data-group="{{ \Illuminate\Support\Str::slug($group) }}">
                  @foreach($fields as $key => $label)
                    <label class="field-pill" data-pill-for="{{ $key }}">
                      <input type="checkbox" data-field-toggle="{{ $key }}">
                      <span>{{ $label }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </aside>

        {{-- RIGHT: Actual form fields --}}
        <div class="overflow-y-auto scrollbar-thin p-5 bg-white">

          {{-- Required + commonly-used fields, always visible --}}
          <div class="mb-4 pb-4 border-b border-dashed border-gold-200">
            <div class="text-xs uppercase tracking-wider text-gold-700 font-bold mb-2">Basic Info</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="form-label">Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" class="form-input" required maxlength="191">
              </div>
              <div>
                <label class="form-label">Phone <span class="text-rose-500">*</span></label>
                <input type="text" name="phone" class="form-input" required maxlength="30">
              </div>
              <div>
                <label class="form-label">Source <span class="text-rose-500">*</span></label>
                <select name="source" class="form-select" required>
                  <option value="">-- Select Source --</option>
                  @foreach(\App\Models\Lead::SOURCES as $s)
                    <option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="form-label">Course</label>
                <select name="course" class="form-select">
                  <option value="">-- Select Course --</option>
                  @foreach($courses ?? [] as $c)
                    <option value="{{ $c->name }}">{{ $c->name }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="form-label">Qualification</label>
                <select name="qualification" class="form-select">
                  <option value="">-- Select --</option>
                  @foreach(\App\Models\Lead::QUALIFICATIONS as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-input">
              </div>
              <div>
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                  <option value="">-- Select --</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>
          </div>

          {{-- Dynamic optional fields - hidden until checkbox ticked --}}
          @foreach($availableFields as $group => $fields)
            <div class="mb-3 hidden" data-field-group="{{ \Illuminate\Support\Str::slug($group) }}">
              <div class="text-xs uppercase tracking-wider text-gold-700 font-bold mb-2">{{ $group }}</div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($fields as $key => $label)
                  <div class="hidden" data-field-wrap="{{ $key }}">
                    <label class="form-label">{{ $label }}</label>
                    @switch($key)
                      @case('message')
                      @case('notes')
                      @case('address')
                        <textarea name="{{ $key }}" rows="2" class="form-textarea"></textarea>
                        @break
                      @case('gender')
                        <select name="gender" class="form-select">
                          <option value="">-- Select --</option>
                          <option value="male">Male</option>
                          <option value="female">Female</option>
                          <option value="other">Other</option>
                        </select>
                        @break
                      @case('mode')
                        <select name="mode" class="form-select">
                          <option value="">-- Select --</option>
                          <option value="online">Online</option>
                          <option value="offline">Offline</option>
                          <option value="hybrid">Hybrid</option>
                        </select>
                        @break
                      @case('status')
                        <select name="status" class="form-select">
                          @foreach(\App\Models\Lead::STATUSES as $k=>$v)<option value="{{ $k }}" @if($k==='new')selected @endif>{{ $v }}</option>@endforeach
                        </select>
                        @break
                      @case('priority')
                        <select name="priority" class="form-select">
                          <option value="low">Low</option>
                          <option value="medium" selected>Medium</option>
                          <option value="high">High</option>
                        </select>
                        @break
                      @case('assigned_to')
                        @if(auth()->user()->isAdmin())
                          <select name="assigned_to" class="form-select">
                            <option value="">-- Unassigned --</option>
                            @foreach($counselors as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                          </select>
                        @else
                          <input type="text" disabled class="form-input bg-gray-100" value="{{ auth()->user()->name }} (you)">
                          <input type="hidden" name="assigned_to" value="{{ auth()->id() }}">
                        @endif
                        @break
                      @case('date_of_birth')
                      @case('next_followup_date')
                        <input type="date" name="{{ $key }}" class="form-input">
                        @break
                      @case('next_followup_time')
                        <input type="time" name="{{ $key }}" class="form-input">
                        @break
                      @case('lead_score')
                        <input type="number" name="lead_score" min="0" max="100" class="form-input">
                        @break
                      @case('budget')
                      @case('experience_years')
                        <input type="number" step="0.01" min="0" name="{{ $key }}" class="form-input">
                        @break
                      @default
                        <input type="text" name="{{ $key }}" class="form-input">
                    @endswitch
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach

          <div class="text-xs text-gray-500 italic mt-4">Tip: click a field name on the left to toggle it on/off. Saved lead will only include checked fields.</div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" onclick="document.getElementById('addLeadModal').classList.add('hidden')" class="btn btn-ghost">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          Save Lead
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const modal = document.getElementById('addLeadModal');
  if(!modal) return;

  modal.querySelectorAll('[data-field-toggle]').forEach(cb => {
    cb.addEventListener('change', () => toggleField(cb.dataset.fieldToggle, cb.checked));
  });

  modal.querySelectorAll('[data-toggle-group]').forEach(btn => {
    btn.addEventListener('click', () => {
      const slug = btn.dataset.toggleGroup;
      const grp  = modal.querySelector(`[data-group="${slug}"]`);
      const boxes = grp.querySelectorAll('input[type=checkbox]');
      const allChecked = Array.from(boxes).every(b => b.checked);
      boxes.forEach(b => {
        b.checked = !allChecked;
        toggleField(b.dataset.fieldToggle, b.checked);
      });
    });
  });

  function toggleField(key, on){
    const wrap = modal.querySelector(`[data-field-wrap="${key}"]`);
    const pill = modal.querySelector(`[data-pill-for="${key}"]`);
    if(!wrap) return;
    if(on){
      wrap.classList.remove('hidden');
      pill?.classList.add('checked');
      // also show the group
      const grp = wrap.closest('[data-field-group]');
      grp?.classList.remove('hidden');
    } else {
      wrap.classList.add('hidden');
      pill?.classList.remove('checked');
      // clear value so it isn't submitted
      wrap.querySelectorAll('input,textarea,select').forEach(el => {
        if(el.type === 'checkbox' || el.type === 'radio') el.checked = false;
        else if(el.type !== 'hidden') el.value = '';
      });
      // if all fields in group hidden, hide group title too
      const grp = wrap.closest('[data-field-group]');
      if(grp){
        const anyVisible = Array.from(grp.querySelectorAll('[data-field-wrap]')).some(w => !w.classList.contains('hidden'));
        grp.classList.toggle('hidden', !anyVisible);
      }
    }
  }

  // Before submit: disable unchecked field inputs so they aren't sent
  document.getElementById('addLeadForm').addEventListener('submit', function(e){
    modal.querySelectorAll('[data-field-wrap]').forEach(wrap => {
      if(wrap.classList.contains('hidden')){
        wrap.querySelectorAll('input,select,textarea').forEach(el => el.disabled = true);
      }
    });
  });
})();
</script>
@endpush
