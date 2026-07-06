@extends('layouts.app')
@section('title', $contract->project_name)
@section('subtitle', 'عقد رقم ' . $contract->contract_number)
@section('content')
@php
    $vs = $contract->visual_state;
    $canManageLic = auth()->user()->isManager() || auth()->user()->can('manage-licenses');
    $isCancelled = $contract->approval_status === 'cancelled';
@endphp

<div class="grid gap-4 lg:grid-cols-3">
    {{-- تفاصيل العقد --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="overflow-hidden rounded-2xl bg-white shadow-card">
            <div class="flex items-center justify-between border-b border-ink/8 px-6 py-4">
                <div class="flex items-center gap-3">
                    <x-status-pill :state="$vs" />
                    <h2 class="font-display text-lg font-bold text-ink">{{ $contract->project_name }}</h2>
                </div>
                @can('manage-contracts')
                    <div class="flex gap-2">
                        <a href="{{ route('contracts.edit', $contract) }}" class="rounded-lg bg-paper px-3 py-1.5 text-[13px] font-semibold text-ink hover:bg-ink/5">تعديل</a>
                        <form method="POST" action="{{ route('contracts.destroy', $contract) }}" onsubmit="return confirm('حذف هذا العقد نهائياً؟')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg px-3 py-1.5 text-[13px] font-semibold text-danger hover:bg-danger/10">حذف</button>
                        </form>
                    </div>
                @endcan
            </div>
            <dl class="grid grid-cols-2 gap-px bg-ink/5 sm:grid-cols-3">
                @foreach ([
                    'رقم العقد'      => $contract->contract_number,
                    'نوع العقد'      => $contract->type_label,
                    'الحي'           => $contract->neighborhood ?: '—',
                    'المطوّر'        => $contract->developer_name,
                    'جوال المطوّر'   => $contract->developer_phone ?: '—',
                    'المسؤول عن العقد' => $contract->employee?->name ?: '—',
                    'المندوب'        => $contract->representative?->name ?: '—',
                    'منشئ العقد'     => $contract->creator?->name ?: '—',
                    'حالة العقد'     => $contract->status_label,
                    'تاريخ البداية'  => $contract->start_date->format('Y-m-d'),
                    'تاريخ الانتهاء' => $contract->end_date->format('Y-m-d'),
                    'الأيام المتبقية' => $contract->days_remaining < 0 ? 'انتهى' : $contract->days_remaining.' يوم',
                ] as $k => $v)
                    <div class="bg-white px-5 py-3">
                        <dt class="text-[12px] text-ink-muted">{{ $k }}</dt>
                        <dd class="mt-0.5 font-semibold text-ink">{{ $v }}</dd>
                    </div>
                @endforeach
            </dl>
            @if ($contract->notes)
                <div class="border-t border-ink/8 px-5 py-4">
                    <p class="text-[12px] text-ink-muted">ملاحظات</p>
                    <p class="mt-1 text-sm text-ink">{{ $contract->notes }}</p>
                </div>
            @endif
        </div>

        {{-- كل تراخيص الموظفين (للمدير) --}}
        @if ($canManageLic)
            <div class="overflow-hidden rounded-2xl bg-white shadow-card">
                <div class="flex items-center justify-between border-b border-ink/8 px-5 py-3">
                    <p class="font-display font-bold text-ink">تراخيص الموظفين <span class="text-ink-muted">({{ $contract->licenses->count() }})</span></p>
                    @unless($isCancelled)
                        <a href="{{ route('licenses.create', $contract) }}" class="rounded-lg bg-ink px-3 py-1.5 text-[13px] font-semibold text-white hover:bg-ink-soft">+ إضافة ترخيص</a>
                    @endunless
                </div>
                @if ($contract->licenses->isEmpty())
                    <p class="px-5 py-6 text-center text-sm text-ink-muted">لا توجد تراخيص بعد.</p>
                @else
                    <table class="w-full text-sm">
                        <thead><tr class="border-b border-ink/8 text-right text-[12px] text-ink-muted">
                            <th class="py-2.5 pr-5">الموظف</th><th class="px-3 py-2.5">رقم الترخيص</th>
                            <th class="px-3 py-2.5">الانتهاء</th><th class="px-3 py-2.5">النشر</th><th class="px-3 py-2.5">الحالة</th><th class="px-3 py-2.5"></th>
                        </tr></thead>
                        <tbody class="divide-y divide-ink/5">
                            @foreach ($contract->licenses as $lic)
                                <tr class="hover:bg-paper/60">
                                    <td class="py-2.5 pr-5 font-semibold text-ink">{{ $lic->employee?->name }}</td>
                                    <td class="px-3 py-2.5 font-mono text-ink-muted">{{ $lic->license_number }}</td>
                                    <td class="px-3 py-2.5">{{ optional($lic->expiry_date)->format('Y-m-d') ?: '—' }}</td>
                                    @php $ps = ['none'=>['لم يُنشر','text-danger'],'partial'=>['جزئي','text-amber-600'],'full'=>['كامل','text-ok']][$lic->publish_state]; @endphp
                                    <td class="px-3 py-2.5"><span class="{{ $ps[1] }} font-semibold">{{ $ps[0] }}</span></td>
                                    <td class="px-3 py-2.5"><span class="text-ink-muted font-semibold">{{ $lic->status_label }}</span></td>
                                    <td class="px-3 py-2.5 text-left">
                                        <a href="{{ route('licenses.edit', $lic) }}" class="text-[13px] font-semibold text-brass hover:underline">تعديل</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif
    </div>

    {{-- ترخيصي + التنبيهات --}}
    <div class="space-y-4">
        <div class="rounded-2xl bg-white p-5 shadow-card">
            <h3 class="font-display font-bold text-ink">ترخيصي لهذا العقد</h3>
            @if ($myLicense)
                <div class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-ink-muted">رقم الترخيص</span><span class="font-mono font-semibold">{{ $myLicense->license_number }}</span></div>
                    <div class="flex justify-between"><span class="text-ink-muted">الإصدار</span><span>{{ $myLicense->issue_date->format('Y-m-d') }}</span></div>
                    <div class="flex justify-between"><span class="text-ink-muted">الانتهاء</span><span>{{ optional($myLicense->expiry_date)->format('Y-m-d') ?: '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-ink-muted">الحالة</span><span class="text-ink font-semibold">{{ $myLicense->status_label }}</span></div>
                    @php $mps = ['none'=>['لم يُنشر','text-danger'],'partial'=>['نشر جزئي','text-amber-600'],'full'=>['منشور بالكامل','text-ok']][$myLicense->publish_state]; @endphp
                    <div class="flex justify-between"><span class="text-ink-muted">النشر</span><span class="{{ $mps[1] }} font-semibold">{{ $mps[0] }}</span></div>
                </div>
                @if ($myLicense->platforms)
                    <div class="mt-3 border-t border-ink/8 pt-3">
                        <p class="mb-2 text-[12px] text-ink-muted">المنصات</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($myLicense->platforms as $p)
                                <a href="{{ $p['url'] ?? '#' }}" target="_blank" class="rounded-full bg-brass/10 px-2.5 py-0.5 text-[12px] font-medium text-brass hover:underline">{{ $p['name'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="mt-4 flex gap-2 border-t border-ink/8 pt-3">
                    <a href="{{ route('licenses.edit', $myLicense) }}" class="flex-1 rounded-lg bg-paper px-3 py-2 text-center text-[13px] font-semibold text-ink hover:bg-ink/5">تعديل</a>
                    <form method="POST" action="{{ route('licenses.destroy', $myLicense) }}" class="flex-1" onsubmit="return confirm('حذف ترخيصك؟')">
                        @csrf @method('DELETE')
                        <button class="w-full rounded-lg px-3 py-2 text-[13px] font-semibold text-danger hover:bg-danger/10">حذف</button>
                    </form>
                </div>
            @else
                <div class="mt-3 rounded-xl border border-dashed border-brass/40 bg-brass/5 p-4 text-center">
                    <p class="text-sm text-ink-muted">لم تنشئ ترخيصك لهذا العقد بعد.</p>
                    @unless($isCancelled)
                        <a href="{{ route('licenses.create', $contract) }}" class="mt-2 inline-block rounded-lg bg-ink px-4 py-2 text-[13px] font-semibold text-white hover:bg-ink-soft">+ إنشاء ترخيصي</a>
                    @else
                        <p class="mt-1 text-[12px] text-purple-600">العقد ملغي — لا يمكن إنشاء ترخيص.</p>
                    @endunless
                </div>
            @endif
        </div>

        @if ($contract->is_expiring_soon)
            <div class="rounded-2xl border border-danger/20 bg-danger/5 p-4">
                <p class="text-sm font-semibold text-danger">⚠ هذا العقد يقترب من الانتهاء خلال {{ $contract->days_remaining }} يوم.</p>
            </div>
        @endif
    </div>
</div>

<div class="mt-4"><a href="{{ route('contracts.index') }}" class="text-sm font-semibold text-brass hover:underline">→ العودة للقائمة</a></div>
@endsection
