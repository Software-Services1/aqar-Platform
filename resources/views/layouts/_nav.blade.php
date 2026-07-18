@php
    $r = Route::currentRouteName();
    $u = auth()->user();
    $showAdmin = $u->isManager()
        || $u->canAny(['manage-employees','manage-representatives','manage-external-companies','manage-roles','manage-settings']);
@endphp

<nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5 text-sm" aria-label="القائمة الرئيسية">
    <x-nav-link href="{{ route('dashboard') }}" :active="$r === 'dashboard'" icon="grid">لوحة المعلومات</x-nav-link>
    <x-nav-link href="{{ route('contracts.index') }}" :active="str_starts_with($r,'contracts')" icon="doc">عقود الوساطة</x-nav-link>
    <x-nav-link href="{{ route('licenses.index') }}" :active="str_starts_with($r,'licenses')" icon="badge">التراخيص الإعلانية</x-nav-link>
    @can('view-reports')
        <x-nav-link href="{{ route('reports.index') }}" :active="str_starts_with($r,'reports')" icon="chart">التقارير</x-nav-link>
    @endcan
    <x-nav-link href="{{ route('notifications.index') }}" :active="str_starts_with($r,'notifications')" icon="bell">الإشعارات</x-nav-link>
    <x-nav-link href="{{ route('profile.show') }}" :active="str_starts_with($r,'profile')" icon="user">ملفي الشخصي</x-nav-link>

    @if($showAdmin)
        <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-white/35">الإدارة</p>
        @can('manage-employees')
            <x-nav-link href="{{ route('employees.index') }}" :active="str_starts_with($r,'employees')" icon="users">الموظفون</x-nav-link>
        @endcan
        @can('manage-representatives')
            <x-nav-link href="{{ route('representatives.index') }}" :active="str_starts_with($r,'representatives')" icon="idcard">المناديب</x-nav-link>
        @endcan
        @can('manage-external-companies')
            <x-nav-link href="{{ route('external-companies.index') }}" :active="str_starts_with($r,'external-companies')" icon="building">الشركات الخارجية</x-nav-link>
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
        <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-white/60 transition hover:bg-white/5 hover:text-white">
            <span class="ico" aria-hidden="true">↩</span> تسجيل الخروج
        </button>
    </form>
</div>
