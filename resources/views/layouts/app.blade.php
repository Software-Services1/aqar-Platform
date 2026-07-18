<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1B2A41">
    <title>@yield('title', 'لوحة المعلومات') · {{ config('app.name', 'سهل') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @livewireStyles
</head>
<body class="font-sans text-ink antialiased">

<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:right-3 focus:z-50 focus:rounded-lg focus:bg-ink focus:px-4 focus:py-2 focus:text-white">تخطٍّ إلى المحتوى</a>

<div class="flex min-h-screen" x-data="{ nav: false }" @keydown.escape.window="nav = false" @nav-close.window="nav = false">

    {{-- الشريط الجانبي (سطح المكتب) --}}
    <aside class="hidden w-64 shrink-0 flex-col bg-ink text-white/80 lg:flex">
        <div class="border-b border-white/10 px-6 py-5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-white/10">
                    <svg class="h-6 w-6" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M9 6.5h8.2L22 11v8.5a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2Z" stroke="#FFFFFF" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M16.8 6.5V11H22" stroke="#FFFFFF" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M11 12.5h5M11 15.5h4" stroke="#8AD7E4" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M10.5 19.5l3 3 6.5-7" stroke="#8AD7E4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>
                    <span class="block font-display text-lg font-extrabold leading-tight text-white">سهل</span>
                    <span class="block text-[11px] text-white/55">لإدارة عقود الوساطة</span>
                </span>
            </a>
        </div>
        @include('layouts._nav')
    </aside>

    {{-- درج التنقّل (الجوال واللوحي) --}}
    <div x-cloak x-show="nav" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="القائمة">
        <div x-show="nav" x-transition.opacity @click="nav = false" class="absolute inset-0 bg-ink/60 backdrop-blur-sm"></div>
        <aside x-show="nav"
               x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
               class="absolute inset-y-0 right-0 flex w-72 max-w-[85%] flex-col bg-ink text-white/80 shadow-2xl">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-white/10">
                        <svg class="h-5 w-5" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M9 6.5h8.2L22 11v8.5a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2Z" stroke="#FFFFFF" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M16.8 6.5V11H22" stroke="#FFFFFF" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M11 12.5h5M11 15.5h4" stroke="#8AD7E4" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M10.5 19.5l3 3 6.5-7" stroke="#8AD7E4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="font-display text-base font-extrabold text-white">سهل</span>
                </div>
                <button type="button" @click="nav = false" class="grid h-9 w-9 place-items-center rounded-lg text-white/70 hover:bg-white/10 hover:text-white" aria-label="إغلاق القائمة">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            @include('layouts._nav')
        </aside>
    </div>

    {{-- المحتوى --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-ink/10 bg-paper/85 px-4 py-3 backdrop-blur sm:px-5">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button" @click="nav = true" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-ink shadow-card lg:hidden" aria-label="فتح القائمة">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <h1 class="truncate font-display text-base font-bold text-ink sm:text-lg">@yield('title', 'لوحة المعلومات')</h1>
                    @hasSection('subtitle')<p class="truncate text-xs text-ink-muted">@yield('subtitle')</p>@endif
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                @livewire('notification-bell')
                <a href="{{ route('profile.show') }}" class="flex items-center gap-2 rounded-full bg-white px-2 py-1.5 shadow-card transition hover:bg-paper sm:px-3">
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-ink text-xs font-bold text-white">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span class="hidden leading-tight sm:block">
                        <span class="block text-xs font-semibold">{{ auth()->user()->name }}</span>
                        <span class="block text-[10px] text-brass">{{ auth()->user()->isManager() ? 'مدير' : 'موظف' }}</span>
                    </span>
                </a>
            </div>
        </header>

        <main id="main" class="flex-1 p-4 sm:p-5">
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.opacity role="status"
                     class="toast-in mb-4 flex items-start gap-2 rounded-xl border border-ok/25 bg-ok/8 px-4 py-3 text-sm text-ok">
                    <span aria-hidden="true">✓</span><span class="flex-1">{{ session('success') }}</span>
                    <button type="button" @click="show = false" aria-label="إغلاق">✕</button>
                </div>
            @endif
            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)" x-transition.opacity role="alert"
                     class="toast-in mb-4 flex items-start gap-2 rounded-xl border border-danger/25 bg-danger/8 px-4 py-3 text-sm text-danger">
                    <span aria-hidden="true">⚠</span><span class="flex-1">{{ session('error') }}</span>
                    <button type="button" @click="show = false" aria-label="إغلاق">✕</button>
                </div>
            @endif

            @yield('content')
        </main>

        @auth
            @livewire('messages-badge')
        @endauth
    </div>
</div>

@livewireScripts
<script>
    // إغلاق درج التنقّل بعد الانتقال (تجربة أنظف على الجوال)
    document.addEventListener('livewire:navigated', () => {
        window.dispatchEvent(new CustomEvent('nav-close'));
        window.scrollTo({ top: 0 });
    });
</script>
@stack('scripts')
</body>
</html>
