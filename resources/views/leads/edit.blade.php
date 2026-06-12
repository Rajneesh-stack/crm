@extends('layouts.app')
@section('title','Edit · '.$lead->name)
@section('page-title','Edit Lead')

@section('header-actions')
  <a href="{{ route('leads.show', $lead) }}" class="btn btn-ghost">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Back
  </a>
@endsection

@section('content')
<form method="POST" action="{{ route('leads.update', $lead) }}" class="space-y-5">
  @csrf @method('PUT')

  {{-- BASIC INFO --}}
  <div class="card">
    <div class="card-header">Basic Info</div>
    <div class="card-body">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="form-label">Full Name <span class="text-rose-500">*</span></label>
          <input type="text" name="name" value="{{ old('name', $lead->name) }}" class="form-input" required maxlength="191">
        </div>
        <div>
          <label class="form-label">Phone <span class="text-rose-500">*</span></label>
          <input type="text" name="phone" value="{{ old('phone', $lead->phone) }}" class="form-input" required maxlength="30">
        </div>
        <div>
          <label class="form-label">Alternate Phone</label>
          <input type="text" name="alternate_phone" value="{{ old('alternate_phone', $lead->alternate_phone) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Email</label>
          <input type="email" name="email" value="{{ old('email', $lead->email) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">WhatsApp Number</label>
          <input type="text" name="whatsapp" value="{{ old('whatsapp', $lead->whatsapp) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Source <span class="text-rose-500">*</span></label>
          <select name="source" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach(\App\Models\Lead::SOURCES as $s)
              <option value="{{ $s }}" @selected(old('source', $lead->source) === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </div>

  {{-- COURSE / INTEREST --}}
  <div class="card">
    <div class="card-header">Course / Interest</div>
    <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="form-label">Course</label>
        <select name="course" class="form-select">
          <option value="">-- Select --</option>
          @foreach($courses as $c)
            <option value="{{ $c->name }}" @selected(old('course', $lead->course) === $c->name)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="form-label">Sub Course</label>
        <input type="text" name="sub_course" value="{{ old('sub_course', $lead->sub_course) }}" class="form-input">
      </div>
      <div>
        <label class="form-label">Mode</label>
        <select name="mode" class="form-select">
          <option value="">-- Select --</option>
          @foreach(['online','offline','hybrid'] as $m)
            <option value="{{ $m }}" @selected(old('mode', $lead->mode) === $m)>{{ ucfirst($m) }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="form-label">Preferred Batch</label>
        <input type="text" name="preferred_batch" value="{{ old('preferred_batch', $lead->preferred_batch) }}" class="form-input">
      </div>
      <div>
        <label class="form-label">Budget</label>
        <input type="number" step="0.01" name="budget" value="{{ old('budget', $lead->budget) }}" class="form-input">
      </div>
    </div>
  </div>

  {{-- PERSONAL --}}
  <div class="card">
    <div class="card-header">Personal Details</div>
    <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($lead->date_of_birth)->format('Y-m-d')) }}" class="form-input">
      </div>
      <div>
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
          <option value="">-- Select --</option>
          @foreach(['male','female','other'] as $g)
            <option value="{{ $g }}" @selected(old('gender', $lead->gender) === $g)>{{ ucfirst($g) }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="form-label">Occupation</label>
        <input type="text" name="occupation" value="{{ old('occupation', $lead->occupation) }}" class="form-input">
      </div>
      <div>
        <label class="form-label">Qualification</label>
        <input type="text" name="qualification" value="{{ old('qualification', $lead->qualification) }}" class="form-input">
      </div>
      <div>
        <label class="form-label">Company</label>
        <input type="text" name="company" value="{{ old('company', $lead->company) }}" class="form-input">
      </div>
      <div>
        <label class="form-label">Designation</label>
        <input type="text" name="designation" value="{{ old('designation', $lead->designation) }}" class="form-input">
      </div>
    </div>
  </div>

  {{-- ADDRESS --}}
  <div class="card">
    <div class="card-header">Address</div>
    <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="md:col-span-3">
        <label class="form-label">Address</label>
        <textarea name="address" rows="2" class="form-textarea">{{ old('address', $lead->address) }}</textarea>
      </div>
      <div>
        <label class="form-label">City</label>
        <input type="text" name="city" value="{{ old('city', $lead->city) }}" class="form-input">
      </div>
      <div>
        <label class="form-label">State</label>
        <input type="text" name="state" value="{{ old('state', $lead->state) }}" class="form-input">
      </div>
      <div>
        <label class="form-label">Country</label>
        <input type="text" name="country" value="{{ old('country', $lead->country ?: 'India') }}" class="form-input">
      </div>
      <div>
        <label class="form-label">Pincode</label>
        <input type="text" name="pincode" value="{{ old('pincode', $lead->pincode) }}" class="form-input">
      </div>
    </div>
  </div>

  {{-- CRM --}}
  <div class="card">
    <div class="card-header">CRM</div>
    <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          @foreach(\App\Models\Lead::STATUSES as $k => $v)
            <option value="{{ $k }}" @selected(old('status', $lead->status) === $k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select">
          @foreach(['low','medium','high'] as $p)
            <option value="{{ $p }}" @selected(old('priority', $lead->priority) === $p)>{{ ucfirst($p) }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="form-label">Next Follow-up Date</label>
        <input type="date" name="next_followup_date" value="{{ old('next_followup_date', optional($lead->next_followup_date)->format('Y-m-d')) }}" class="form-input">
      </div>
      <div class="md:col-span-3">
        <label class="form-label">Message / Enquiry</label>
        <textarea name="message" rows="2" class="form-textarea">{{ old('message', $lead->message) }}</textarea>
      </div>
      <div class="md:col-span-3">
        <label class="form-label">Internal Notes</label>
        <textarea name="notes" rows="2" class="form-textarea">{{ old('notes', $lead->notes) }}</textarea>
      </div>
    </div>
  </div>

  <div class="flex justify-end gap-2">
    <a href="{{ route('leads.show', $lead) }}" class="btn btn-ghost">Cancel</a>
    <button class="btn btn-primary">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
      Save Changes
    </button>
  </div>
</form>
@endsection
