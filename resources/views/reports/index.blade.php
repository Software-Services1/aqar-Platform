@extends('layouts.app')
@section('title', 'التقارير')
@section('subtitle', $reportType === 'licenses' ? 'تقرير التراخيص الإعلانية لكل مستخدم' : 'تقرير عقود الوساطة')
@section('content')

{{-- مبدّل نوع التقرير --}}
<div class="mb-4 inline-flex rounded-xl border border-ink/10 bg-white p-1 shadow-card">
    <a href="{{ route('reports.index', ['report_type' => 'contracts']) }}"
       class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $reportType === 'contracts' ? 'bg-ink text-white' : 'text-ink-muted hover:text-ink' }}">
        تقرير العقود
    </a>
    <a href="{{ route('reports.index', ['report_type' => 'licenses']) }}"
       class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $reportType === 'licenses' ? 'bg-ink text-white' : 'text-ink-muted hover:text-ink' }}">
        تقرير التراخيص
    </a>
</div>

@if ($reportType === 'licenses')
    {{-- ============ تقرير التراخيص ============ --}}
    <form method="GET" action="{{ route('reports.index') }}" class="rounded-2xl bg-white p-5 shadow-card">
        <input type="hidden" name="report_type" value="licenses">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div><label class="lbl">من تاريخ الإصدار</label><input type="date" name="from" value="{{ $filters['from'] }}" class="inp"></div>
            <div><label class="lbl">إلى تاريخ الإصدار</label><input type="date" name="to" value="{{ $filters['to'] }}" class="inp"></div>
            <div>
                <label class="lbl">الموظف / المستخدم (منشئ الترخيص)</label>
                <select name="employee_id" class="inp">
                    <option value="">كل المستخدمين</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" @selected($filters['employee_id'] == $emp->id)>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="lbl">حالة الترخيص</label>
                <select name="status" class="inp">
                    <option value="">الكل</option>
                    @foreach ($licenseStatuses as $k => $v)
                        <option value="{{ $k }}" @selected($filters['status'] === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="lbl">حالة النشر</label>
                <select name="publish_state" class="inp">
                    <option value="">الكل</option>
                    @foreach ($publishStates as $k => $v)
                        <option value="{{ $k }}" @selected($filters['publish_state'] === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 lg:col-span-3">
                <button class="rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">تطبيق</button>
                <a href="{{ route('reports.index', ['report_type' => 'licenses']) }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">مسح</a>
            </div>
        </div>
    </form>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([['إجمالي التراخيص',$licenseSummary['total'],'text-ink'],['لم يُنشر',$licenseSummary['none'],'text-danger'],['نشر جزئي',$licenseSummary['partial'],'text-amber-600'],['منشور بالكامل',$licenseSummary['full'],'text-ok']] as [$l,$val,$cls])
            <div class="rounded-2xl bg-white p-4 shadow-card">
                <p class="text-[12px] text-ink-muted">{{ $l }}</p>
                <p class="mt-1 font-display text-2xl font-bold {{ $cls }} tabular-nums">{{ $val }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-ink/8 text-right text-[12px] text-ink-muted">
                    <th class="py-3 pr-5">رقم الترخيص</th><th class="px-3 py-3">المستخدم</th><th class="px-3 py-3">العقد</th>
                    <th class="px-3 py-3">الإصدار</th><th class="px-3 py-3">الانتهاء</th><th class="px-3 py-3">المنصات</th>
                    <th class="px-3 py-3">النشر</th><th class="px-3 py-3">الحالة</th>
                </tr></thead>
                <tbody class="divide-y divide-ink/5">
                    @php $pmap = ['none'=>['لم يُنشر','text-danger'],'partial'=>['جزئي','text-amber-600'],'full'=>['منشور','text-ok']]; @endphp
                    @forelse ($licenseResults as $lic)
                        <tr class="hover:bg-paper/60">
                            <td class="py-3 pr-5 font-mono font-semibold">{{ $lic->license_number }}</td>
                            <td class="px-3 py-3 text-ink">{{ $lic->employee?->name ?: '—' }}</td>
                            <td class="px-3 py-3">
                                <a href="{{ route('contracts.show', $lic->contract) }}" class="text-brass hover:underline">{{ $lic->contract->project_name }}</a>
                                <span class="block text-[11px] font-mono text-ink-muted">{{ $lic->contract->contract_number }}</span>
                            </td>
                            <td class="px-3 py-3 tabular-nums text-ink-muted">{{ $lic->issue_date->format('Y-m-d') }}</td>
                            <td class="px-3 py-3 tabular-nums text-ink-muted">{{ $lic->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-3 py-3 tabular-nums text-ink-muted">{{ $lic->published_count }}</td>
                            <td class="px-3 py-3 text-[12px] font-semibold {{ $pmap[$lic->publish_state][1] }}">{{ $pmap[$lic->publish_state][0] }}</td>
                            <td class="px-3 py-3 text-[12px] font-semibold text-ink-muted">{{ $lic->status_label }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-12 text-center text-ink-muted">لا توجد تراخيص مطابقة للفلاتر.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-ink/5 px-4 py-3">{{ $licenseResults->links() }}</div>
    </div>

@else
    {{-- ============ تقرير العقود ============ --}}
    <form method="GET" action="{{ route('reports.index') }}" class="rounded-2xl bg-white p-5 shadow-card">
        <input type="hidden" name="report_type" value="contracts">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div><label class="lbl">من تاريخ</label><input type="date" name="from" value="{{ $filters['from'] }}" class="inp"></div>
            <div><label class="lbl">إلى تاريخ</label><input type="date" name="to" value="{{ $filters['to'] }}" class="inp"></div>
            <div>
                <label class="lbl">الموظف المسؤول</label>
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
                <label class="lbl">الشركة الخارجية</label>
                <select name="external_company_id" class="inp">
                    <option value="">الكل</option>
                    @foreach ($externalCompanies as $co)
                        <option value="{{ $co->id }}" @selected($filters['external_company_id'] == $co->id)>{{ $co->name }}</option>
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
                <label class="lbl">نوع الصفقة</label>
                <select name="transaction_type" class="inp">
                    <option value="">الكل</option>
                    @foreach ($transactionTypes as $k => $v)
                        <option value="{{ $k }}" @selected($filters['transaction_type'] === $k)>{{ $v }}</option>
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
                <a href="{{ route('reports.index', ['report_type' => 'contracts']) }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">مسح</a>
            </div>
        </div>
    </form>

    <div class="mt-4 grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ([['الإجمالي',$summary['total'],'text-ink'],['تمت الموافقة',$summary['approved'],'text-ok'],['منتهي',$summary['finished'],'text-teal-600'],['بانتظار الموافقة',$summary['pending'],'text-blue-600'],['منتهية',$summary['expired'],'text-gone'],['ملغية',$summary['cancelled'],'text-purple-600']] as [$l,$val,$cls])
            <div class="rounded-2xl bg-white p-4 shadow-card">
                <p class="text-[12px] text-ink-muted">{{ $l }}</p>
                <p class="mt-1 font-display text-2xl font-bold {{ $cls }} tabular-nums">{{ $val }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-ink/8 text-right text-[12px] text-ink-muted">
                    <th class="py-3 pr-5">رقم العقد</th><th class="px-3 py-3">المشروع</th><th class="px-3 py-3">النوع/الصفقة</th>
                    <th class="px-3 py-3">الحي</th><th class="px-3 py-3">المسؤول</th><th class="px-3 py-3">المندوب</th>
                    <th class="px-3 py-3">الانتهاء</th><th class="px-3 py-3">النشر</th><th class="px-3 py-3">الحالة</th>
                </tr></thead>
                <tbody class="divide-y divide-ink/5">
                    @php $pmap = [null=>['بلا ترخيص','text-danger'],'none'=>['غير منشور','text-danger'],'partial'=>['جزئي','text-amber-600'],'full'=>['منشور','text-ok']]; @endphp
                    @forelse ($results as $c)
                        <tr class="hover:bg-paper/60">
                            <td class="py-3 pr-5 font-mono text-[13px] font-semibold">
                                <a href="{{ route('contracts.show', $c) }}" class="text-brass hover:underline">{{ $c->contract_number }}</a>
                                @if($c->is_subcontract)<span class="mr-1 rounded bg-brass/10 px-1 text-[10px] text-brass">فرعي</span>@endif
                            </td>
                            <td class="px-3 py-3 font-semibold text-ink">{{ $c->project_name }}@if($c->externalCompany)<span class="block text-[11px] font-normal text-ink-muted">{{ $c->externalCompany->name }}</span>@endif</td>
                            <td class="px-3 py-3 text-ink-muted">{{ $c->type_label }} · {{ $c->transaction_label }}</td>
                            <td class="px-3 py-3 text-ink-muted">{{ $c->neighborhood ?: '—' }}</td>
                            <td class="px-3 py-3 text-ink-muted">{{ $c->employee?->name ?: '—' }}</td>
                            <td class="px-3 py-3 text-ink-muted">{{ $c->representative?->name ?: '—' }}</td>
                            <td class="px-3 py-3 tabular-nums text-ink-muted">{{ $c->end_date->format('Y-m-d') }}</td>
                            <td class="px-3 py-3 text-[12px] font-semibold {{ $pmap[$c->publish_summary][1] }}">{{ $pmap[$c->publish_summary][0] }}</td>
                            <td class="px-3 py-3"><x-status-pill :state="$c->visual_state" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-12 text-center text-ink-muted">لا توجد نتائج مطابقة للفلاتر.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-ink/5 px-4 py-3">{{ $results->links() }}</div>
    </div>
@endif

<style>.lbl{display:block;margin-bottom:.35rem;font-size:.78rem;font-weight:600;color:#22324d}.inp{width:100%;border:1px solid rgba(27,42,65,.12);border-radius:.75rem;padding:.55rem .7rem;font-size:.85rem;outline:none}.inp:focus{border-color:#1499B0;box-shadow:0 0 0 3px rgba(20,153,176,.15)}</style>
@endsection
