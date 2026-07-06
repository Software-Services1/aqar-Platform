@extends('layouts.app')
@section('title', 'لوحة المعلومات')
@section('subtitle', $isManager ? 'نظرة شاملة على كل العقود والتراخيص' : 'نظرة على عقودك وتراخيصك الخاصة')

@section('content')
@php
$cards = [
  ['إجمالي العقود', $stats['contracts_total'], 'ink', 'M6 2h9l5 5v15H6z'],
  ['تمت الموافقة', $stats['approved'], 'ok', 'M9 12l2 2 4-4'],
  ['بانتظار الموافقة', $stats['pending'], 'blue-600', 'M12 8v4l3 2'],
  ['منتهي', $stats['finished'], 'teal-600', 'M5 12l4 4L19 6'],
  ['انتهت دون موافقة', $stats['expired'], 'gone', 'M6 6l12 12M18 6L6 18'],
  ['ملغية', $stats['cancelled'], 'purple-600', 'M4.9 4.9l14.2 14.2M12 3a9 9 0 100 18 9 9 0 000-18z'],
  ['عقود قرب الانتهاء', $stats['expiring_soon'], 'danger', 'M12 8v5M12 16h.01'],
  ['إجمالي التراخيص', $stats['licenses_total'], 'brass', 'M4 4h16v6H4zM4 14h10v6H4z'],
  ['تراخيص قرب الانتهاء', $stats['licenses_expiring'], 'danger', 'M12 9v4M12 17h.01'],
  ['المنصات المفعّلة', $stats['platforms'], 'brass', 'M12 2l9 5-9 5-9-5z'],
];
@endphp

{{-- تنبيهات الفجوات: عقود بلا ترخيص / غير منشورة --}}
@if ($stats['without_license'] > 0 || $stats['unpublished'] > 0)
<div class="mb-4 grid gap-3 sm:grid-cols-2">
    <a href="{{ route('contracts.index', ['state' => 'no_license']) }}" class="flex items-center justify-between rounded-2xl border border-danger/20 bg-danger/5 p-4 hover:bg-danger/10">
        <div>
            <p class="text-[13px] font-semibold text-danger">عقود بلا ترخيص</p>
            <p class="text-[11px] text-ink-muted">تحتاج إنشاء ترخيص</p>
        </div>
        <span class="font-display text-2xl font-bold text-danger tabular-nums">{{ $stats['without_license'] }}</span>
    </a>
    <a href="{{ route('contracts.index', ['state' => 'unpublished']) }}" class="flex items-center justify-between rounded-2xl border border-amber-200 bg-amber-50 p-4 hover:bg-amber-100">
        <div>
            <p class="text-[13px] font-semibold text-amber-700">تراخيص غير منشورة بالكامل</p>
            <p class="text-[11px] text-ink-muted">لم تُنشر على كل المنصات</p>
        </div>
        <span class="font-display text-2xl font-bold text-amber-700 tabular-nums">{{ $stats['unpublished'] }}</span>
    </a>
</div>
@endif

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
    @foreach ($cards as [$label, $value, $color, $path])
    <div class="group relative overflow-hidden rounded-2xl bg-white p-4 shadow-card">
        <div class="absolute inset-x-0 top-0 h-1 bg-{{ $color }}"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[12px] text-ink-muted">{{ $label }}</p>
                <p class="mt-1 font-display text-2xl font-extrabold tabular-nums text-ink">{{ number_format($value) }}</p>
            </div>
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-{{ $color }}/10 text-{{ $color }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}"/></svg>
            </span>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-5 grid gap-4 lg:grid-cols-3">
    <div class="rounded-2xl bg-white p-5 shadow-card lg:col-span-2">
        <h3 class="font-display font-bold text-ink">العقود المُنشأة — آخر 6 أشهر</h3>
        <div class="mt-3"><canvas id="monthlyChart" height="110"></canvas></div>
    </div>
    <div class="rounded-2xl bg-white p-5 shadow-card">
        <h3 class="font-display font-bold text-ink">توزيع حالات العقود</h3>
        <div class="mt-3"><canvas id="statusChart" height="160"></canvas></div>
    </div>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-3">
    <div class="rounded-2xl bg-white p-5 shadow-card lg:col-span-2">
        <h3 class="font-display font-bold text-ink">المنصات الإعلانية الأكثر استخداماً</h3>
        <div class="mt-3"><canvas id="platformChart" height="110"></canvas></div>
    </div>

    <div class="rounded-2xl bg-white p-5 shadow-card">
        <div class="flex items-center justify-between">
            <h3 class="font-display font-bold text-ink">آخر الإشعارات</h3>
            <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-brass hover:underline">عرض الكل</a>
        </div>
        <ul class="mt-3 space-y-2">
            @forelse ($notifications as $n)
                <li class="flex gap-3 rounded-xl border border-ink/5 bg-paper/60 p-3">
                    <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full {{ $n->type === 'expiring_license' ? 'bg-danger' : 'bg-brass' }}"></span>
                    <div>
                        <p class="text-[13px] leading-snug text-ink">{{ $n->message }}</p>
                        <p class="mt-1 text-[11px] text-ink-muted">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                </li>
            @empty
                <li class="rounded-xl border border-dashed border-ink/15 p-6 text-center text-sm text-ink-muted">
                    لا توجد إشعارات جديدة.
                </li>
            @endforelse
        </ul>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const grid = 'rgba(27,42,65,.07)';
    Chart.defaults.font.family = 'IBM Plex Sans Arabic';
    Chart.defaults.color = '#5b6b85';

    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: { labels: @json($monthlyChart['labels']),
            datasets: [{ data: @json($monthlyChart['data']),
                borderColor: '#A77C3C', backgroundColor: 'rgba(167,124,60,.12)',
                fill: true, tension: .4, pointRadius: 4, pointBackgroundColor: '#A77C3C', borderWidth: 2.5 }] },
        options: { plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: grid }, ticks: { precision: 0 } }, x: { grid: { display: false } } } },
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: { labels: @json($statusChart['labels']),
            datasets: [{ data: @json($statusChart['data']),
                backgroundColor: ['#2563eb', '#16a34a', '#0d9488', '#6b7280', '#9333ea'], borderWidth: 0 }] },
        options: { cutout: '64%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } } },
    });

    new Chart(document.getElementById('platformChart'), {
        type: 'bar',
        data: { labels: @json($platformChart['labels']),
            datasets: [{ data: @json($platformChart['data']),
                backgroundColor: '#1B2A41', borderRadius: 6, barThickness: 26 }] },
        options: { plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: grid }, ticks: { precision: 0 } }, x: { grid: { display: false } } } },
    });
</script>
@endpush
@endsection
