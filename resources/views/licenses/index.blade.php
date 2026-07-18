@extends('layouts.app')
@section('title', 'التراخيص الإعلانية')
@section('subtitle', auth()->user()->isManager() ? 'كل التراخيص في النظام' : 'تراخيصي')
@section('content')
@php
$pub = ['none' => ['لم يُنشر', 'text-danger bg-danger/10'], 'partial' => ['نشر جزئي', 'text-amber-600 bg-amber-50'], 'full' => ['منشور بالكامل', 'text-ok bg-ok/10']];
@endphp

{{-- فلتر فترة التاريخ --}}
<form method="GET" action="{{ route('licenses.index') }}" class="mb-4 rounded-2xl bg-white p-4 shadow-card">
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <label class="lbl">من تاريخ الإصدار</label>
            <input type="date" name="from" value="{{ $from }}" class="inp">
        </div>
        <div>
            <label class="lbl">إلى تاريخ الإصدار</label>
            <input type="date" name="to" value="{{ $to }}" class="inp">
        </div>
        @if ($employees->isNotEmpty())
        <div>
            <label class="lbl">اسم الموظف</label>
            <select name="employee_id" class="inp">
                <option value="">كل الموظفين</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}" @selected($employeeId == $emp->id)>{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button class="rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">تطبيق</button>
        @if($from || $to || $employeeId)
            <a href="{{ route('licenses.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">مسح</a>
        @endif
    </div>
</form>

<div class="overflow-hidden rounded-2xl bg-white shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-ink/8 text-right text-[12px] text-ink-muted">
                <th class="py-3 pr-5">رقم الترخيص</th><th class="px-3 py-3">الموظف</th><th class="px-3 py-3">العقد</th>
                <th class="px-3 py-3">الإصدار</th><th class="px-3 py-3">الانتهاء</th><th class="px-3 py-3">المنصات</th>
                <th class="px-3 py-3">النشر</th><th class="px-3 py-3">الحالة</th><th class="px-3 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-ink/5">
                @forelse ($licenses as $lic)
                    @php $exp = $lic->is_expiring_soon; [$pl,$pc] = $pub[$lic->publish_state]; @endphp
                    <tr class="hover:bg-paper/60 {{ $exp ? 'bg-danger/4' : '' }}" style="box-shadow: inset -4px 0 0 {{ $exp ? '#dc2626' : 'transparent' }};">
                        <td class="py-3 pr-5 font-mono font-semibold">{{ $lic->license_number }}</td>
                        <td class="px-3 py-3 text-ink">{{ $lic->employee?->name ?: '—' }}</td>
                        <td class="px-3 py-3">
                            <a href="{{ route('contracts.show', $lic->contract) }}" class="text-brass hover:underline">{{ $lic->contract->project_name }}</a>
                            <span class="block text-[11px] font-mono text-ink-muted">{{ $lic->contract->contract_number }}</span>
                        </td>
                        <td class="px-3 py-3 text-ink-muted tabular-nums">{{ $lic->issue_date->format('Y-m-d') }}</td>
                        <td class="px-3 py-3 text-ink-muted tabular-nums">{{ $lic->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-3 py-3 text-[12px]">
                            @forelse ($lic->platforms ?? [] as $pf)
                                <a href="{{ $pf['url'] ?? '#' }}" target="_blank" class="ml-1 inline-block rounded-full bg-brass/10 px-2 py-0.5 text-brass hover:underline">{{ $pf['name'] }}</a>
                            @empty
                                <span class="text-ink-muted">—</span>
                            @endforelse
                        </td>
                        <td class="px-3 py-3"><span class="rounded-full px-2 py-0.5 text-[12px] font-semibold {{ $pc }}">{{ $pl }}</span></td>
                        <td class="px-3 py-3 text-[12px] font-semibold text-ink-muted">{{ $lic->status_label }}</td>
                        <td class="px-3 py-3 text-left whitespace-nowrap">
                            <a href="{{ route('licenses.edit', $lic) }}" class="text-[13px] font-semibold text-brass hover:underline">تعديل</a>
                            <form method="POST" action="{{ route('licenses.destroy', $lic) }}" class="inline" onsubmit="return confirm('حذف الترخيص؟')">
                                @csrf @method('DELETE')
                                <button class="mr-2 text-[13px] font-semibold text-danger hover:underline">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-12 text-center text-ink-muted">لا توجد تراخيص مطابقة.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-ink/5 px-4 py-3">{{ $licenses->links() }}</div>
</div>
@endsection
