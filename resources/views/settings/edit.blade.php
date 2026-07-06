@extends('layouts.app')
@section('title', 'إعدادات النظام')
@section('subtitle', 'التحكم في أيام التنبيه وألوان حالات العقود')
@section('content')
<form method="POST" action="{{ route('settings.update') }}"
      x-data="{
          colors: {
              active:    '{{ $settings['color_active'] }}',
              pending:   '{{ $settings['color_pending'] }}',
              expiring:  '{{ $settings['color_expiring'] }}',
              finished:  '{{ $settings['color_finished'] }}',
              expired:   '{{ $settings['color_expired'] }}',
              cancelled: '{{ $settings['color_cancelled'] }}'
          }
      }"
      class="mx-auto max-w-4xl space-y-5">
    @csrf @method('PUT')

    <div class="rounded-2xl bg-white p-6 shadow-card">
        <h2 class="font-display text-base font-bold text-ink">أيام التنبيه</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-[13px] font-semibold text-ink">التنبيه قبل انتهاء الترخيص (أيام)</label>
                <input type="number" name="alert_days" min="1" max="90" required
                       value="{{ old('alert_days', $settings['alert_days']) }}"
                       class="w-full rounded-xl border border-ink/12 bg-paper/40 px-3 py-2.5 text-sm focus:border-brass focus:ring-2 focus:ring-brass/20 focus:outline-none">
                @error('alert_days')<p class="mt-1 text-[12px] text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-[13px] font-semibold text-ink">مهلة العقد المعلّق قبل اعتباره منتهياً (أيام)</label>
                <input type="number" name="pending_expiry_days" min="1" max="90" required
                       value="{{ old('pending_expiry_days', $settings['pending_expiry_days']) }}"
                       class="w-full rounded-xl border border-ink/12 bg-paper/40 px-3 py-2.5 text-sm focus:border-brass focus:ring-2 focus:ring-brass/20 focus:outline-none">
                @error('pending_expiry_days')<p class="mt-1 text-[12px] text-danger">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-card">
        <h2 class="font-display text-base font-bold text-ink">ألوان حالات العقود</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @php
                $swatches = [
                    ['color_active',    'active',    'عقد نشط'],
                    ['color_pending',   'pending',   'بانتظار الموافقة'],
                    ['color_expiring',  'expiring',  'قرب الانتهاء'],
                    ['color_finished',  'finished',  'منتهي'],
                    ['color_expired',   'expired',   'انتهت دون موافقة'],
                    ['color_cancelled', 'cancelled', 'ملغي'],
                ];
            @endphp
            @foreach ($swatches as [$key, $jsKey, $label])
                <div class="flex items-center gap-3 rounded-xl border border-ink/8 p-3">
                    <input type="color" name="{{ $key }}" value="{{ old($key, $settings[$key]) }}"
                           x-model="colors.{{ $jsKey }}"
                           class="h-11 w-11 cursor-pointer rounded-lg border border-ink/10 bg-transparent p-0.5">
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-semibold text-ink">{{ $label }}</p>
                        <p class="text-[11px] uppercase text-ink-muted" x-text="colors.{{ $jsKey }}" dir="ltr"></p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <p class="mb-2 text-[12px] font-semibold text-ink-muted">معاينة مباشرة</p>
            <div class="overflow-hidden rounded-xl border border-ink/8">
                @php
                    $previews = [
                        ['active',    'عقد نشط',          'يتبقّى 42 يوم'],
                        ['pending',   'بانتظار الموافقة', 'بانتظار اعتماد الإدارة'],
                        ['expiring',  'قرب الانتهاء',     'يتبقّى 4 أيام'],
                        ['finished',  'منتهي',            'تم إنهاء العقد'],
                        ['expired',   'انتهت دون موافقة', 'انتهت المدة'],
                        ['cancelled', 'ملغي',             'تم إلغاء العقد'],
                    ];
                @endphp
                @foreach ($previews as [$jsKey, $title, $sub])
                    <div class="flex items-center justify-between gap-3 border-b border-ink/5 bg-white px-4 py-3 last:border-0"
                         :style="`box-shadow: inset 4px 0 0 ${colors.{{ $jsKey }}}`">
                        <div>
                            <p class="text-sm font-semibold text-ink">مشروع نموذجي #{{ $loop->iteration }}</p>
                            <p class="text-[11px] text-ink-muted">{{ $sub }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-[12px] font-semibold text-white"
                              :style="`background-color: ${colors.{{ $jsKey }}}`">{{ $title }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button class="rounded-xl bg-ink px-6 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">حفظ الإعدادات</button>
    </div>
</form>
@endsection
