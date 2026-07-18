<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول · {{ config('app.name', 'سهل') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="font-sans" style="background:#1B2A41">
<div class="flex min-h-screen items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center text-white">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-white/10">
                <svg class="h-9 w-9" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 6.5h8.2L22 11v8.5a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2Z" stroke="#FFFFFF" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M16.8 6.5V11H22" stroke="#FFFFFF" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M11 12.5h5M11 15.5h4" stroke="#8AD7E4" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M10.5 19.5l3 3 6.5-7" stroke="#8AD7E4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h1 class="mt-3 font-display text-2xl font-extrabold">سهل</h1>
            <p class="text-sm text-white/55">لإدارة عقود الوساطة</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4 rounded-2xl bg-white p-6 shadow-2xl">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">البريد الإلكتروني</label>
                <input name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" inputmode="email" dir="ltr"
                       class="w-full rounded-xl border border-ink/15 px-3 py-2.5 text-sm outline-none focus:border-brass focus:ring-2 focus:ring-brass/20">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">كلمة المرور</label>
                <input name="password" type="password" required autocomplete="current-password" dir="ltr"
                       class="w-full rounded-xl border border-ink/15 px-3 py-2.5 text-sm outline-none focus:border-brass focus:ring-2 focus:ring-brass/20">
            </div>
            @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <label class="flex items-center gap-2 text-sm text-ink/70"><input type="checkbox" name="remember" class="rounded"> تذكّرني</label>
            <button type="submit" data-submit class="w-full rounded-xl bg-ink py-2.5 font-semibold text-white transition hover:bg-brass disabled:opacity-70">دخول</button>
        </form>
        <p class="mt-4 text-center text-xs text-white/40">حساب تجريبي: manager@example.com / password</p>
    </div>
</div>
<script>
    // منع الإرسال المزدوج في استمارة الدخول
    document.querySelector('form')?.addEventListener('submit', (e) => {
        const b = e.target.querySelector('[data-submit]');
        if (b) { b.disabled = true; b.textContent = 'جارٍ الدخول…'; }
    });
</script>
</body>
</html>
