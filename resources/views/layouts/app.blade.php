<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','Dashboard') · {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cormorant+Garamond:wght@500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme:{
      extend:{
        colors:{
          gold:{50:'#fbf6e8',100:'#f4e9c5',200:'#e9d28c',300:'#dcb95a',400:'#c9a13e',500:'#b8923d',600:'#9c7a2e',700:'#7d6122',800:'#5e4818'},
          ink:{50:'#f6f5f1',100:'#e8e6dd',200:'#c5c2b3',700:'#2a2f3a',800:'#1c2230',900:'#0f1622'},
          champagne:'#c5a572',
        },
        fontFamily:{
          serif:['"Cormorant Garamond"','Georgia','serif'],
          sans:['Inter','Segoe UI','sans-serif'],
        }
      }
    }
  }
</script>
<link rel="stylesheet" href="{{ asset('css/theme.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
@stack('head')
</head>
<body class="bg-[#f5f2ea] min-h-screen">
<div class="flex min-h-screen">
  {{-- SIDEBAR --}}
  <aside class="sidebar w-64 flex-shrink-0 hidden md:flex flex-col fixed inset-y-0 left-0 z-40">
    <div class="brand">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:linear-gradient(135deg,#dcb95a,#7d6122);">
        <span class="font-serif text-xl font-bold text-ink-900">G</span>
      </div>
      <div>
        <div class="font-serif text-xl gold-text leading-none">Golden CRM</div>
        <div class="text-[10px] uppercase tracking-widest text-ink-200/70">Lead Manager</div>
      </div>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1">
      <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h4v-6h6v6h4V10"/></svg>
        Dashboard
      </a>
      <a href="{{ route('leads.index') }}" class="nav-link {{ request()->routeIs('leads.index') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 014-4h1m5-4a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        All Leads
      </a>
      <a href="{{ route('leads.bulk.show') }}" class="nav-link {{ request()->routeIs('leads.bulk.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5m0 0l5 5m-5-5v12"/></svg>
        Bulk Upload
      </a>
      <a href="{{ route('activities.index') }}" class="nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Activity Log
      </a>
      <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Reports
      </a>
      @if(auth()->user()->isAdmin())
        <div class="mt-4 mb-1 px-3 text-[10px] uppercase tracking-widest text-ink-200/60">Admin</div>
        <a href="{{ route('counselors.index') }}" class="nav-link {{ request()->routeIs('counselors.*') ? 'active' : '' }}">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          Counselors
        </a>
        <a href="{{ route('courses.index') }}" class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          Courses
        </a>
        <a href="{{ route('templates.index') }}" class="nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Templates
        </a>
      @endif

      <div class="mt-4 mb-1 px-3 text-[10px] uppercase tracking-widest text-ink-200/60">Account</div>
      <a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        My Profile
      </a>
    </nav>

    <div class="p-3 border-t border-gold-700/30 text-xs text-ink-200/70">
      <div class="px-2">Logged in as</div>
      <div class="px-2 text-gold-200 font-semibold truncate">{{ auth()->user()->name }}</div>
      <div class="px-2 text-[11px] uppercase tracking-wider text-gold-400/80">{{ auth()->user()->role }}</div>
    </div>
  </aside>

  {{-- MAIN --}}
  <div class="flex-1 flex flex-col min-w-0 md:ml-64">
    <header class="bg-white border-b border-[#ece6d4] sticky top-0 z-30">
      <div class="flex items-center justify-between px-4 md:px-8 h-16">
        <div class="flex items-center gap-3">
          <button id="sidebarToggle" class="md:hidden p-2 rounded-lg hover:bg-gold-50">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <h1 class="font-serif text-2xl text-ink-900">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="flex items-center gap-3">
          @hasSection('header-actions') @yield('header-actions') @endif
          <div class="hidden md:flex items-center gap-3 pl-4 ml-2 border-l border-[#ece6d4]">
            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 hover:opacity-80" title="Edit profile">
              <div class="text-right">
                <div class="text-sm font-semibold text-ink-900">{{ auth()->user()->name }}</div>
                <div class="text-[10px] uppercase tracking-wider text-gold-700">{{ auth()->user()->role }}</div>
              </div>
              @php $initial = strtoupper(substr(auth()->user()->name,0,1)); @endphp
              @if(auth()->user()->avatar_url)
                <img src="{{ auth()->user()->avatar_url }}" class="w-10 h-10 rounded-full object-cover border-2 border-gold-200" alt=""
                     onerror="this.outerHTML='<div class=\'w-10 h-10 rounded-full flex items-center justify-center font-bold text-ink-900\' style=\'background:linear-gradient(135deg,#dcb95a,#b8923d);\'>{{ $initial }}</div>'">
              @else
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-ink-900" style="background:linear-gradient(135deg,#dcb95a,#b8923d);">
                  {{ $initial }}
                </div>
              @endif
            </a>
            <form method="POST" action="{{ route('logout') }}">@csrf
              <button class="btn btn-ghost btn-sm" title="Logout">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
              </button>
            </form>
          </div>
        </div>
      </div>
    </header>

    <main class="flex-1 p-4 md:p-8">
      @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
      @if (session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
      @if (session('errors_list') && count(session('errors_list')))
        <div class="alert alert-warning"><strong>Some rows were skipped:</strong>
          <ul class="list-disc ml-5 mt-1 text-xs">@foreach(session('errors_list') as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif
      @yield('content')
    </main>
    <footer class="text-center text-xs text-ink-200 py-3 border-t border-[#ece6d4] bg-white/50">
      Golden CRM · © {{ date('Y') }}
    </footer>
  </div>
</div>

<div id="modalRoot"></div>

<script>
  document.getElementById('sidebarToggle')?.addEventListener('click',()=>{
    document.querySelector('.sidebar')?.classList.toggle('hidden');
  });
  // CSRF token helper
  window.csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // Auto-attach show/hide eye toggle on every password input.
  // Add data-pw-skip="1" on any input you don't want enhanced.
  (function () {
    const EYE_OPEN  = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    const EYE_CLOSE = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>';

    function enhance(input) {
      if (input.dataset.pwEnhanced === '1' || input.dataset.pwSkip === '1') return;
      input.dataset.pwEnhanced = '1';

      const wrap = document.createElement('div');
      wrap.style.position = 'relative';
      wrap.className = 'pw-eye-wrap';
      input.parentNode.insertBefore(wrap, input);
      wrap.appendChild(input);
      input.style.paddingRight = '2.4rem';

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.tabIndex = -1;
      btn.setAttribute('aria-label', 'Show password');
      btn.style.cssText = 'position:absolute;right:.55rem;top:50%;transform:translateY(-50%);background:transparent;border:0;cursor:pointer;color:#8a7544;padding:.15rem;display:flex;align-items:center;line-height:0;';
      btn.innerHTML = EYE_OPEN;
      btn.addEventListener('click', function () {
        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        btn.innerHTML = showing ? EYE_OPEN : EYE_CLOSE;
        btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
      });
      wrap.appendChild(btn);
    }

    function scan(root) {
      (root || document).querySelectorAll('input[type="password"]').forEach(enhance);
    }

    document.addEventListener('DOMContentLoaded', () => scan());
    // Re-scan when DOM changes (e.g. modals/panels rendered dynamically)
    new MutationObserver(muts => muts.forEach(m => m.addedNodes.forEach(n => {
      if (n.nodeType === 1) scan(n);
    }))).observe(document.body, { childList: true, subtree: true });
  })();
</script>
@stack('scripts')
</body>
</html>
