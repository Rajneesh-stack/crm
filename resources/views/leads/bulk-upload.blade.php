@extends('layouts.app')
@section('title','Bulk Upload')
@section('page-title','Bulk Upload Leads')

@section('content')
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 card">
      <div class="card-header">Upload CSV file</div>
      <div class="card-body">
        <form method="POST" action="{{ route('leads.bulk.upload') }}" enctype="multipart/form-data" class="space-y-4">
          @csrf
          <div>
            <label class="form-label">CSV File <span class="text-rose-500">*</span></label>
            <input type="file" name="file" accept=".csv,text/csv" class="form-input" required>
            <div class="text-xs text-gray-500 mt-1">Max 5 MB. First row must be headers.</div>
          </div>

          @if(auth()->user()->isAdmin())
            <div>
              <label class="form-label">Assign all to counselor</label>
              <select name="assigned_to" class="form-select">
                <option value="">— Unassigned —</option>
                @foreach($counselors as $c)
                  <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
              </select>
            </div>
          @endif

          <div>
            <label class="form-label">Default Source <span class="text-rose-500">*</span> <span class="text-xs text-gray-500 normal-case">(applied when CSV row has no source)</span></label>
            <select name="default_source" class="form-select">
              <option value="">-- Pick a default --</option>
              @foreach(\App\Models\Lead::SOURCES as $s)
                <option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
              @endforeach
            </select>
            <div class="text-[11px] text-gray-500 mt-1">Source is mandatory for every lead. Rows missing this field will be skipped unless you set a default here.</div>
          </div>

          <div class="flex gap-2">
            <button class="btn btn-primary">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5m0 0l5 5m-5-5v12"/></svg>
              Import
            </button>
            <a href="{{ route('leads.bulk.sample') }}" class="btn btn-ghost">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
              Download Sample CSV
            </a>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">CSV Format</div>
      <div class="card-body text-sm space-y-3">
        <p class="text-gray-700">Required columns: <strong>name</strong>, <strong>phone</strong>.</p>
        <p>Optional columns supported:</p>
        <div class="bg-[#fbf6e8] border border-gold-200 rounded p-2 text-[11px] font-mono leading-relaxed">
          email, alternate_phone, whatsapp, course, sub_course, mode, preferred_batch, budget, source, sub_source, campaign, utm_source, utm_medium, utm_campaign, referrer_name, referrer_phone, date_of_birth, gender, occupation, qualification, passing_year, institute, company, designation, experience_years, address, city, state, country, pincode, status, priority, lead_score, next_followup_date, message, notes, tags
        </div>
        <p class="text-xs text-gray-500">Date format: <strong>YYYY-MM-DD</strong>. Tags comma-separated. Unknown columns are ignored.</p>
      </div>
    </div>
  </div>
@endsection
