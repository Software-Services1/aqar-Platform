<div>
    {{-- شريط الأدوات: بحث + فلاتر --}}
    <div class="rounded-2xl bg-white p-4 shadow-card">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[220px]">
                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                <input type="search" wire:model.live.debounce.300ms="search"
                       placeholder="ابحث برقم العقد، المشروع، المطوّر، الجوال…"
                       class="w-full rounded-xl border border-ink/10 bg-paper/50 py-2.5 pr-10 pl-9 text-sm outline-none focus:border-brass focus:ring-2 focus:ring-brass/20">
                <svg wire:loading wire:target="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-brass" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/>
                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>

            <select wire:model.live="state" class="rounded-xl border border-ink/10 bg-white py-2.5 px-3 text-sm outline-none focus:border-brass">
                <option value="">كل الحالات</option>
                <option value="active">نشط</option>
                <option value="expiring">قارب الانتهاء</option>
                <option value="pending">بانتظار الموافقة</option>
                <option value="finished">منتهي</option>
                <option value="expired">انتهت دون موافقة</option>
                <option value="cancelled">ملغي</option>
                <option value="no_license">بدون ترخيص</option>
                <option value="unpublished">ترخيص غير منشور بالكامل</option>
                <option value="draft">مسودة (بيانات ناقصة)</option>
            </select>

            <select wire:model.live="type" class="rounded-xl border border-ink/10 bg-white py-2.5 px-3 text-sm outline-none focus:border-brass">
                <option value="">كل الأنواع</option>
                @foreach ($types as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>

            @if ($canManageContracts)
                <input type="text" wire:model.live.debounce.300ms="responsible" placeholder="اسم المسؤول…"
                       class="w-40 rounded-xl border border-ink/10 bg-white py-2.5 px-3 text-sm outline-none focus:border-brass">
            @endif

            @if ($search || $state || $type || $responsible)
                <button wire:click="clearFilters" class="rounded-xl px-3 py-2.5 text-sm font-medium text-ink-muted hover:text-danger">مسح الفلاتر ✕</button>
            @endif

            @can('manage-contracts')
                <a href="{{ route('contracts.create') }}" wire:navigate class="mr-auto inline-flex items-center gap-2 rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-ink-soft">
                    <span class="text-lg leading-none">+</span> عقد جديد
                </a>
            @endcan
        </div>
    </div>

    {{-- الجدول --}}
    <div class="relative mt-4 overflow-hidden rounded-2xl bg-white shadow-card">
        {{-- شريط تقدّم علوي أثناء التحميل --}}
        <div wire:loading class="absolute inset-x-0 top-0 z-20 h-0.5 overflow-hidden bg-brass/20">
            <div class="h-full w-1/3 animate-pulse bg-brass"></div>
        </div>
        <div class="max-h-[65vh] overflow-auto busy" wire:loading.class="opacity-60">
            <table class="table-sticky w-full text-sm">
                <thead>
                    <tr class="border-b border-ink/8 text-right text-[12px] text-ink-muted">
                        <th class="py-3 pr-5"><button wire:click="sortBy('contract_number')" class="font-semibold hover:text-ink">رقم العقد</button></th>
                        <th class="px-3 py-3"><button wire:click="sortBy('project_name')" class="font-semibold hover:text-ink">المشروع / المطوّر</button></th>
                        <th class="px-3 py-3">النوع</th>
                        <th class="px-3 py-3">المسؤول</th>
                        <th class="px-3 py-3"><button wire:click="sortBy('end_date')" class="font-semibold hover:text-ink">الانتهاء</button></th>
                        <th class="px-3 py-3">المتبقّي</th>
                        <th class="px-3 py-3">التراخيص</th>
                        <th class="px-3 py-3">الحالة</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/5">
                    @forelse ($contracts as $c)
                        @php
                            $spine = ['active'=>'#16a34a','expiring'=>'#dc2626','pending'=>'#2563eb','finished'=>'#0d9488','expired'=>'#6b7280','cancelled'=>'#9333ea','draft'=>'#94a3b8'][$c->visual_state] ?? '#6b7280';
                            $rowBg = ['expiring'=>'bg-danger/4','expired'=>'bg-gone/4','cancelled'=>'bg-purple-50/40'][$c->visual_state] ?? '';
                        @endphp
                        <tr class="group transition hover:bg-paper/60 {{ $rowBg }}" style="box-shadow: inset -4px 0 0 {{ $spine }};">
                            <td class="py-3 pr-5 font-mono text-[13px] font-semibold tabular-nums text-ink">{{ $c->contract_number }}</td>
                            <td class="px-3 py-3">
                                <p class="flex items-center gap-1.5 font-semibold text-ink">
                                    {{ $c->project_name }}
                                    @if($c->is_draft)
                                        <span title="مسودة — بيانات ناقصة" class="inline-flex items-center text-amber-600">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                                        </span>
                                    @endif
                                </p>
                                <p class="text-[12px] text-ink-muted">{{ $c->developer_name ?: ($c->is_draft ? "—" : "") }}@if($c->neighborhood) · {{ $c->neighborhood }}@endif</p>
                            </td>
                            <td class="px-3 py-3 text-[12px] text-ink-muted">{{ $c->type_label }}<span class="mx-1 text-ink/20">·</span>{{ $c->transaction_label }}</td>
                            <td class="px-3 py-3 text-ink-muted">{{ $c->responsible_name ?: '—' }}</td>
                            <td class="px-3 py-3 tabular-nums text-ink-muted">{{ optional($c->end_date)->format('Y-m-d') ?: '—' }}</td>
                            <td class="px-3 py-3">
                                @if (is_null($c->days_remaining))
                                    <span class="text-ink-muted">—</span>
                                @elseif ($c->days_remaining < 0)
                                    <span class="text-gone">— انتهى</span>
                                @else
                                    <span class="font-semibold tabular-nums {{ $c->is_expiring_soon ? 'text-danger' : 'text-ink' }}">{{ $c->days_remaining }}</span>
                                    <span class="text-[11px] text-ink-muted">يوم</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                @php $psum = $c->publish_summary; @endphp
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center rounded-full bg-ink/5 px-2 py-0.5 text-[12px] font-semibold text-ink-muted">{{ $c->licenses->count() }}</span>
                                    @if($c->licenses->count() === 0)
                                        <span class="text-[11px] font-semibold text-danger">بلا ترخيص</span>
                                    @elseif($psum === 'none')
                                        <span class="text-[11px] font-semibold text-danger">غير منشور</span>
                                    @elseif($psum === 'partial')
                                        <span class="text-[11px] font-semibold text-amber-600">نشر جزئي</span>
                                    @else
                                        <span class="text-[11px] font-semibold text-ok">منشور</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-3"><x-status-pill :state="$c->visual_state" /></td>
                            <td class="px-3 py-3 text-left">
                                <a href="{{ route('contracts.show', $c) }}" wire:navigate class="rounded-lg px-3 py-1.5 text-[12px] font-semibold text-brass opacity-0 transition group-hover:opacity-100 hover:bg-brass/10">عرض ←</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-14 text-center">
                            <span class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-paper text-ink-muted">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l5 5v15H6z"/><path d="M15 2v5h5"/><path d="M9 13h6M9 17h4"/></svg>
                            </span>
                            <p class="font-semibold text-ink">
                                @if ($search || $state || $type || $responsible)
                                    لا توجد نتائج مطابقة للفلاتر
                                @else
                                    لا توجد عقود بعد
                                @endif
                            </p>
                            <p class="mt-1 text-[13px] text-ink-muted">
                                @if ($search || $state || $type || $responsible)
                                    جرّب تعديل البحث أو إزالة بعض الفلاتر.
                                @else
                                    ابدأ بإضافة أول عقد وساطة لإدارته ومتابعة تراخيصه.
                                @endif
                            </p>
                            @if ($search || $state || $type || $responsible)
                                <button wire:click="clearFilters" class="mt-3 inline-block rounded-xl border border-ink/12 px-4 py-2 text-sm font-semibold text-ink hover:bg-paper">مسح الفلاتر</button>
                            @else
                                @can('manage-contracts')
                                    <a href="{{ route('contracts.create') }}" wire:navigate class="mt-3 inline-block rounded-xl bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">أضف أول عقد</a>
                                @endcan
                            @endif
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-ink/5 px-4 py-3">{{ $contracts->links() }}</div>
    </div>
</div>
