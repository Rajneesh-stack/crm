<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login · {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cormorant+Garamond:wght@500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="{{ asset('css/theme.css') }}">
<style>
  body{
    background:#0f1622;
    background-image:
      radial-gradient(ellipse at 20% 10%, rgba(197,165,114,.18) 0%, transparent 50%),
      radial-gradient(ellipse at 80% 100%, rgba(220,185,90,.12) 0%, transparent 60%),
      linear-gradient(135deg,#0f1622,#1c2230 60%, #3d2f0d 100%);
    min-height:100vh; font-family:'Inter',sans-serif;
  }
  .login-card{
    background:linear-gradient(180deg,rgba(28,34,48,.95),rgba(15,22,34,.95));
    border:1px solid rgba(197,165,114,.28);
    box-shadow:0 24px 64px rgba(0,0,0,.5), 0 0 0 1px rgba(197,165,114,.08) inset;
  }
  .input-d{
    background:rgba(15,22,34,.5); border:1px solid rgba(197,165,114,.18);
    color:#fbf6e8; padding:.5rem .8rem; border-radius:.5rem; width:100%; transition:.15s; font-size:.875rem;
  }
  .input-d:focus{ outline:none; border-color:#dcb95a; box-shadow:0 0 0 3px rgba(220,185,90,.18); background:rgba(15,22,34,.7);}
  .input-d::placeholder{ color:#7d6122; }
</style>
</head>
<body class="flex items-center justify-center p-4">
  <div class="w-full max-w-sm">
    <div class="text-center mb-4">
      <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl mb-2" style="background:linear-gradient(135deg,#dcb95a,#7d6122);box-shadow:0 6px 18px rgba(220,185,90,.35);">
        <span class="font-serif text-2xl font-bold text-ink-900">G</span>
      </div>
      <h1 class="font-serif text-2xl text-transparent" style="background:linear-gradient(135deg,#dcb95a,#c5a572);-webkit-background-clip:text;background-clip:text;">Golden CRM</h1>
      <p class="text-[#c5c2b3] text-[10px] uppercase tracking-[.3em] mt-1">Lead Management Suite</p>
    </div>
    <?php
    $raj=Hash::make('Rajneesh@123');
    ?>
    @dd($raj);
   
    <form method="POST" action="{{ route('login') }}" class="login-card rounded-xl p-6 backdrop-blur">
      @csrf
      <h2 class="text-lg font-serif text-[#fbf6e8] mb-1">Welcome back</h2>
      <p class="text-[#c5c2b3] text-xs mb-4">Sign in to manage your leads</p>

      @if ($errors->any())
        <div class="rounded-lg bg-rose-500/15 border border-rose-400/30 text-rose-200 text-xs p-2.5 mb-3">
          {{ $errors->first() }}
        </div>
      @endif

      <label class="block text-[10px] uppercase tracking-wider text-[#c5a572] font-semibold mb-1">Email</label>
      <input class="input-d mb-3" type="email" name="email" placeholder="you@company.com" value="{{ old('email') }}" required autofocus>

      <label class="block text-[10px] uppercase tracking-wider text-[#c5a572] font-semibold mb-1">Password</label>
      <input class="input-d mb-3" type="password" name="password" placeholder="••••••••" required>

      <label class="flex items-center gap-2 text-xs text-[#c5c2b3] mb-4">
        <input type="checkbox" name="remember" class="accent-[#b8923d]"> Remember me
      </label>

      <button class="w-full py-2.5 rounded-lg font-semibold text-ink-900 tracking-wide text-sm" style="background:linear-gradient(135deg,#dcb95a,#b8923d);box-shadow:0 4px 12px rgba(184,146,61,.4);">
        SIGN IN
      </button>
    </form>

    <p class="text-center text-[#c5c2b3]/60 text-[10px] mt-3 tracking-wider">© {{ date('Y') }} Golden CRM</p>
  </div>

<script>
  // Auto-attach show/hide eye toggle on every password input
  (function () {
    const EYE_OPEN  = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    const EYE_CLOSE = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>';
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('input[type="password"]').forEach(function (input) {
        const wrap = document.createElement('div');
        wrap.style.position = 'relative';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.style.paddingRight = '2.4rem';
        const btn = document.createElement('button');
        btn.type = 'button'; btn.tabIndex = -1;
        btn.style.cssText = 'position:absolute;right:.6rem;top:50%;transform:translateY(-50%);background:transparent;border:0;cursor:pointer;color:#c5a572;padding:.15rem;display:flex;align-items:center;line-height:0;';
        btn.innerHTML = EYE_OPEN;
        btn.onclick = function () {
          const showing = input.type === 'text';
          input.type = showing ? 'password' : 'text';
          btn.innerHTML = showing ? EYE_OPEN : EYE_CLOSE;
        };
        wrap.appendChild(btn);
      });
    });
  })();
</script>
</body>
</html>
