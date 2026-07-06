@extends('layouts.app')
@section('title', 'التقارير')
@section('subtitle', 'فلترة العقود حسب التاريخ والمسؤول والحي والنوع والمندوب والحالة')
@section('content')

{{-- شريط الفلاتر --}}
<form method="GET" action="{{ route('reports.index') }}" class="rounded-2xl bg-white p-5 shadow-card">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label class="lbl">من تاريخ</label>
            <input type="date" name="from" value="{{ $filters['from'] }}" class="inp">
        </div>
        <div>
            <label class="lbl">إلى تاريخ</label>
            <input type="date" name="to" value="{{ $filters['to'] }}" class="inp">
        </div>
        <div>
            <label class="lbl">المسؤول عن العقد</label>
            <select name="employee_id" class="inp">
                <option value="">الكل</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}" @selected($filters['employee_id'] == $emp->id)>{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="lbl">المندوب</label>
            <select name="representative_id" class="inp">
                <option value="">الكل</option>
                @foreach ($representatives as $rep)
                    <option value="{{ $rep->id }}" @selected($filters['representative_id'] == $rep->id)>{{ $rep->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="lbl">الحي</label>
            <select name="neighborhood" class="inp">
                <option value="">الكل</option>
                @foreach ($neighborhoods as $n)
                    <option value="{{ $n }}" @selected($filters['neighborhood'] === $n)>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="lbl">نوع العقد</label>
            <select name="contract_type" class="inp">
                <option value="">الكل</option>
                @foreach ($types as $k => $v)
                    <option value="{{ $k }}" @selected($filters['contract_type'] === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="lbl">حالة العقد</label>
            <select name="status" class="inp">
                <option value="">الكل</option>
                @foreach ($statuses as $k => $v)
                    <option value="{{ $k }}" @selected($filters['status'] === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="lbl">حالة الترخيص/النشر</label>
            <select name="license_state" class="inp">
                <option value="">الكل</option>
                @foreach ($licenseStates as $k => $v)
                    <option value="{{ $k }}" @selected($filters['license_state'] === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button class="flex-1 rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">تطبيق</button>
            <a href="{{ route('reports.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">مسح</a>
        </div>
    </div>
</form>

{{-- ملخّص --}}
<div class="mt-4 grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
    @php
        $cards = [
            ['الإجمالي', $summary['total'], 'text-ink'],
            ['تمت الموافقة', $summary['approved'], 'text-ok'],
            ['منتهي', $summary['finished'], 'text-teal-600'],
            ['بانتظار الموافقة', $summary['pending'], 'text-blue-600'],
            ['منتهية', $summary['expired'], 'text-gone'],
            ['ملغية', $summary['cancelled'], 'text-purple-600'],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $cls])
        <div class="rounded-2xl bg-white p-4 shadow-card">
            <p class="text-[12px] text-ink-muted">{{ $label }}</p>
            <p class="mt-1 font-display text-2xl font-bold {{ $cls }} tabular-nums">{{ $value }}</p>
        </div>
    @endforeach
</div>

{{-- النتائج --}}
<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-ink/8 text-right text-[12px] text-ink-muted">
                <th class="py-3 pr-5">رقم العقد</th><th class="px-3 py-3">المشروع</th><th class="px-3 py-3">النوع</th>
                <th class="px-3 py-3">الحي</th><th class="px-3 py-3">المسؤول</th><th class="px-3 py-3">المندوب</th>
                <th class="px-3 py-3">البداية</th><th class="px-3 py-3">الانتهاء</th><th class="px-3 py-3">النشر</th><th class="px-3 py-3">الحالة</th>
            </tr></thead>
            <tbody class="divide-y divide-ink/5">
                @forelse ($results as $c)
                    <tr class="hover:bg-paper/60">
                        <td class="py-3 pr-5 font-mono text-[13px] font-semibold">
                            <a href="{{ route('contracts.show', $c) }}" class="text-brass hover:underline">{{ $c->contract_number }}</a>
                        </td>
                        <td class="px-3 py-3 font-semibold text-ink">{{ $c->project_name }}</td>
                        <td class="px-3 py-3 text-ink-muted">{{ $c->type_label }}</td>
                        <td class="px-3 py-3 text-ink-muted">{{ $c->neighborhood ?: '—' }}</td>
                        <td class="px-3 py-3 text-ink-muted">{{ $c->employee?->name ?: '—' }}</td>
                        <td class="px-3 py-3 text-ink-muted">{{ $c->representative?->name ?: '—' }}</td>
                        <td class="px-3 py-3 tabular-nums text-ink-muted">{{ $c->start_date->format('Y-m-d') }}</td>
                        <td class="px-3 py-3 tabular-nums text-ink-muted">{{ $c->end_date->format('Y-m-d') }}</td>
                        @php $psum = $c->publish_summary;
                          $pmap = [null=>['بلا ترخيص','text-danger'],'none'=>['غير منشور','text-danger'],'partial'=>['جزئي','text-amber-600'],'full'=>['منشور','text-ok']]; @endphp
                        <td class="px-3 py-3 text-[12px] font-semibold {{ $pmap[$psum][1] }}">{{ $pmap[$psum][0] }}</td>
                        <td class="px-3 py-3"><x-status-pill :state="$c->visual_state" /></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="py-12 text-center text-ink-muted">لا توجد نتائج مطابقة للفلاتر.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-ink/5 px-4 py-3">{{ $results->links() }}</div>
</div>
<style>.lbl{display:block;margin-bottom:.35rem;font-size:.78rem;font-weight:600;color:#22324d}.inp{width:100%;border:1px solid rgba(27,42,65,.12);border-radius:.75rem;padding:.55rem .7rem;font-size:.85rem;outline:none}.inp:focus{border-color:#A77C3C;box-shadow:0 0 0 3px rgba(167,124,60,.15)}</style>
@endsection
