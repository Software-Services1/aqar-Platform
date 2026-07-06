@props(['state'])
@php
$map = [
  'active'    => ['نشط', 'text-ok bg-ok/10 ring-ok/20'],
  'pending'   => ['بانتظار الموافقة', 'text-blue-600 bg-blue-50 ring-blue-200'],
  'expiring'  => ['قارب الانتهاء', 'text-danger bg-danger/10 ring-danger/20'],
  'finished'  => ['منتهي', 'text-teal-600 bg-teal-50 ring-teal-200'],
  'expired'   => ['انتهت دون موافقة', 'text-gone bg-gone/10 ring-gone/20'],
  'cancelled' => ['ملغي', 'text-purple-600 bg-purple-50 ring-purple-200'],
];
[$label, $cls] = $map[$state] ?? ['—', 'text-ink-muted bg-ink/5 ring-ink/10'];
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $cls }}">
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $label }}
</span>
