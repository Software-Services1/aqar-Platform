<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة العقود') · {{ config('app.name', 'سهل') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"IBM Plex Sans Arabic"', 'sans-serif'],
                        display: ['Tajawal', 'sans-serif'],
                    },
                    colors: {
                        ink:    { DEFAULT: '#1B2A41', soft: '#22324d', muted: '#5b6b85' },
                        paper:  '#F1F5F7',
                        brass:  { DEFAULT: '#1499B0', soft: '#8AD7E4', dim: '#e2f4f7' },
                        ok:     '#16a34a',
                        warn:   '#d97706',
                        danger: '#dc2626',
                        gone:   '#6b7280',
                    },
                    boxShadow: {
                        card: '0 1px 2px rgba(27,42,65,.06), 0 8px 24px -12px rgba(27,42,65,.18)',
                    },
                },
            },
        };
    </script>
    <style>
        body { background:#F7F5F0; }
        .nav-link.active { background: rgba(196,154,91,.16); color:#fff; }
        .nav-link.active .ico { color:#C49A5B; }
        ::-webkit-scrollbar { width:10px; height:10px; }
        ::-webkit-scrollbar-thumb { background:#cfc8ba; border-radius:99px; }
        [x-cloak]{ display:none !important; }
    </style>
    @livewireStyles
</head>
<body class="font-sans text-ink antialiased">
<div class="flex min-h-screen">

    {{-- الشريط الجانبي --}}
    <aside class="hidden lg:flex w-64 shrink-0 flex-col bg-ink text-white/80">
        <div class="px-6 py-5 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-white/10">
                    <svg class="h-6 w-6" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 6.5h8.2L22 11v8.5a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2Z" stroke="#FFFFFF" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M16.8 6.5V11H22" stroke="#FFFFFF" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M11 12.5h5M11 15.5h4" stroke="#8AD7E4" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M10.5 19.5l3 3 6.5-7" stroke="#8AD7E4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <p class="font-display text-lg font-extrabold text-white leading-tight">سهل</p>
                    <p class="text-[11px] text-white/55">لإدارة عقود الوساطة</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 space-y-1 px-3 py-5 text-sm">
            @php $r = Route::currentRouteName(); @endphp
            <x-nav-link href="{{ route('dashboard') }}" :active="$r === 'dashboard'" icon="grid">لوحة المعلومات</x-nav-link>
            <x-nav-link href="{{ route('contracts.index') }}" :active="str_starts_with($r,'contracts')" icon="doc">عقود الوساطة</x-nav-link>
            <x-nav-link href="{{ route('licenses.index') }}" :active="str_starts_with($r,'licenses')" icon="badge">التراخيص الإعلانية</x-nav-link>
            @can('view-reports')
                <x-nav-link href="{{ route('reports.index') }}" :active="str_starts_with($r,'reports')" icon="chart">التقارير</x-nav-link>
            @endcan
            <x-nav-link href="{{ route('notifications.index') }}" :active="str_starts_with($r,'notifications')" icon="bell">الإشعارات</x-nav-link>
            <x-nav-link href="{{ route('profile.show') }}" :active="str_starts_with($r,'profile')" icon="user">ملفي الشخصي</x-nav-link>

            @php
                $u = auth()->user();
                $showAdmin = $u->isManager()
                    || $u->canAny(['manage-employees','manage-representatives','manage-roles','manage-settings']);
            @endphp
            @if($showAdmin)
                <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-white/35">الإدارة</p>
                @can('manage-employees')
                    <x-nav-link href="{{ route('employees.index') }}" :active="str_starts_with($r,'employees')" icon="users">الموظفون</x-nav-link>
                @endcan
                @can('manage-representatives')
                    <x-nav-link href="{{ route('representatives.index') }}" :active="str_starts_with($r,'representatives')" icon="idcard">المناديب</x-nav-link>
                @endcan
                @can('manage-roles')
                    <x-nav-link href="{{ route('roles.index') }}" :active="str_starts_with($r,'roles')" icon="shield">الأدوار والصلاحيات</x-nav-link>
                @endcan
                @can('manage-settings')
                    <x-nav-link href="{{ route('platforms.index') }}" :active="str_starts_with($r,'platforms')" icon="layers">المنصات الإعلانية</x-nav-link>
                    <x-nav-link href="{{ route('settings.edit') }}" :active="str_starts_with($r,'settings')" icon="gear">إعدادات النظام</x-nav-link>
                @endcan
            @endif
        </nav>

        <div class="border-t border-white/10 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-white/60 hover:bg-white/5 hover:text-white">
                    <span class="ico">↩</span> تسجيل الخروج
                </button>
            </form>
        </div>
    </aside>

    {{-- المحتوى --}}
    <div class="flex flex-1 flex-col min-w-0">
        <header class="sticky top-0 z-30 flex items-center justify-between gap-4 border-b border-ink/10 bg-paper/85 px-5 py-3 backdrop-blur">
            <div>
                <h1 class="font-display text-lg font-bold text-ink">@yield('title', 'لوحة المعلومات')</h1>
                @hasSection('subtitle')<p class="text-xs text-ink-muted">@yield('subtitle')</p>@endif
            </div>
            <div class="flex items-center gap-3">
                @livewire('notification-bell')
                <a href="{{ route('profile.show') }}" class="flex items-center gap-2 rounded-full bg-white px-3 py-1.5 shadow-card hover:bg-paper transition">
                    <div class="grid h-7 w-7 place-items-center rounded-full bg-ink text-xs font-bold text-white">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="hidden sm:block leading-tight">
                        <p class="text-xs font-semibold">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-brass">{{ auth()->user()->isManager() ? 'مدير' : 'موظف' }}</p>
                    </div>
                </a>
            </div>
        </header>

        <main class="flex-1 p-5">
            @if (session('success'))
                <div class="mb-4 flex items-start gap-2 rounded-xl border border-ok/25 bg-ok/8 px-4 py-3 text-sm text-ok">
                    <span>✓</span><span>{{ session('success') }}</span>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

@livewireScripts
@stack('scripts')
</body>
</html>
