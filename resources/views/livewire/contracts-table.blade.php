<div>
    {{-- شريط الأدوات: بحث + فلاتر --}}
    <div class="rounded-2xl bg-white p-4 shadow-card">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[220px]">
                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                <input type="search" wire:model.live.debounce.300ms="search"
                       placeholder="ابحث برقم العقد، المشروع، المطوّر، الجوال…"
                       class="w-full rounded-xl border border-ink/10 bg-paper/50 py-2.5 pr-10 pl-3 text-sm outline-none focus:border-brass focus:ring-2 focus:ring-brass/20">
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
                <a href="{{ route('contracts.create') }}" class="mr-auto inline-flex items-center gap-2 rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-ink-soft">
                    <span class="text-lg leading-none">+</span> عقد جديد
                </a>
            @endcan
        </div>
    </div>

    {{-- الجدول --}}
    <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
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
                            $spine = ['active'=>'#16a34a','expiring'=>'#dc2626','pending'=>'#2563eb','finished'=>'#0d9488','expired'=>'#6b7280','cancelled'=>'#9333ea'][$c->visual_state] ?? '#6b7280';
                            $rowBg = ['expiring'=>'bg-danger/4','expired'=>'bg-gone/4','cancelled'=>'bg-purple-50/40'][$c->visual_state] ?? '';
                        @endphp
                        <tr class="group transition hover:bg-paper/60 {{ $rowBg }}" style="box-shadow: inset -4px 0 0 {{ $spine }};">
                            <td class="py-3 pr-5 font-mono text-[13px] font-semibold tabular-nums text-ink">{{ $c->contract_number }}</td>
                            <td class="px-3 py-3">
                                <p class="font-semibold text-ink">{{ $c->project_name }}</p>
                                <p class="text-[12px] text-ink-muted">{{ $c->developer_name }}@if($c->neighborhood) · {{ $c->neighborhood }}@endif</p>
                            </td>
                            <td class="px-3 py-3 text-[12px] text-ink-muted">{{ $c->type_label }}<span class="mx-1 text-ink/20">·</span>{{ $c->transaction_label }}</td>
                            <td class="px-3 py-3 text-ink-muted">{{ $c->responsible_name ?: '—' }}</td>
                            <td class="px-3 py-3 tabular-nums text-ink-muted">{{ $c->end_date->format('Y-m-d') }}</td>
                            <td class="px-3 py-3">
                                @if ($c->days_remaining < 0)
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
                                <a href="{{ route('contracts.show', $c) }}" class="rounded-lg px-3 py-1.5 text-[12px] font-semibold text-brass opacity-0 transition group-hover:opacity-100 hover:bg-brass/10">عرض ←</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-16 text-center">
                            <p class="text-ink-muted">لا توجد عقود مطابقة.</p>
                            @can('manage-contracts')
                                <a href="{{ route('contracts.create') }}" class="mt-2 inline-block text-sm font-semibold text-brass hover:underline">أضف أول عقد</a>
                            @endcan
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-ink/5 px-4 py-3">{{ $contracts->links() }}</div>
    </div>
</div>
