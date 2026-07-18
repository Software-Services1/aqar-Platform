@php $c = $contract ?? null; @endphp
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="lbl">اسم المشروع</label>
        <input name="project_name" value="{{ old('project_name', $c?->project_name) }}" required class="inp">
        @error('project_name')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="lbl">رقم العقد <span class="text-ink-muted font-normal">(من المنصة الأخرى)</span></label>
        <input name="contract_number" value="{{ old('contract_number', $c?->contract_number) }}" required class="inp font-mono" placeholder="أدخل رقم العقد">
        @error('contract_number')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="lbl">اسم المطوّر</label>
        <input name="developer_name" value="{{ old('developer_name', $c?->developer_name) }}" required class="inp">
        @error('developer_name')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="lbl">جوال المطوّر</label>
        <input name="developer_phone" value="{{ old('developer_phone', $c?->developer_phone) }}" class="inp" dir="ltr">
    </div>
    <div>
        <label class="lbl">الحي</label>
        <input name="neighborhood" value="{{ old('neighborhood', $c?->neighborhood) }}" class="inp" placeholder="مثال: النرجس">
        @error('neighborhood')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="lbl">نوع العقد</label>
        <select name="contract_type" class="inp">
            @foreach ($types as $k => $v)
                <option value="{{ $k }}" @selected(old('contract_type', $c?->contract_type ?? 'brokerage') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        @error('contract_type')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="lbl">نوع الصفقة</label>
        <select name="transaction_type" class="inp">
            @foreach ($transactionTypes as $k => $v)
                <option value="{{ $k }}" @selected(old('transaction_type', $c?->transaction_type ?? 'sale') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        @error('transaction_type')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="lbl">المسؤول عن العقد</label>
        <input name="responsible_name" value="{{ old('responsible_name', $c?->responsible_name) }}" class="inp" placeholder="اسم المسؤول (قد لا يكون موظفاً بالنظام)">
        @error('responsible_name')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="lbl">جوال المندوب</label>
        <input name="responsible_phone" value="{{ old('responsible_phone', $c?->responsible_phone) }}" class="inp" dir="ltr" placeholder="05xxxxxxxx">
        @error('responsible_phone')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="lbl">المندوب</label>
        <select name="representative_id" class="inp">
            <option value="">— غير محدد —</option>
            @foreach ($representatives as $rep)
                <option value="{{ $rep->id }}" @selected(old('representative_id', $c?->representative_id) == $rep->id)>{{ $rep->name }}</option>
            @endforeach
        </select>
        @error('representative_id')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="lbl">تاريخ البداية</label>
        <input type="date" name="start_date" value="{{ old('start_date', $c?->start_date?->format('Y-m-d')) }}" required class="inp">
        @error('start_date')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="lbl">تاريخ الانتهاء</label>
        <input type="date" name="end_date" value="{{ old('end_date', $c?->end_date?->format('Y-m-d')) }}" required class="inp">
        @error('end_date')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="lbl">حالة العقد</label>
        <select name="approval_status" class="inp">
            @foreach ($statuses as $k => $v)
                <option value="{{ $k }}" @selected(old('approval_status', $c?->approval_status ?? 'pending') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        @error('approval_status')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div></div>
    <div class="sm:col-span-2">
        <label class="lbl">ملاحظات</label>
        <textarea name="notes" rows="3" class="inp">{{ old('notes', $c?->notes) }}</textarea>
    </div>

    @php
        $isCreate = ! (isset($c) && $c->exists);
        $allIds = $allEmployees->pluck('id')->map(fn ($i) => (string) $i)->all();
        $assignedIds = collect(old('assigned', $isCreate ? $allIds : $c->assignedEmployees->pluck('id')->all()))
            ->map(fn ($i) => (string) $i)->all();
    @endphp
    <div class="sm:col-span-2" x-data="{ ids: @js($allIds), selected: @js($assignedIds) }">
        <label class="lbl">إشعار / إظهار العقد للموظفين <span class="font-normal text-ink-muted">(الافتراضي: الكل)</span></label>
        <div class="rounded-xl border border-ink/12 p-3">
            <label class="mb-2 flex cursor-pointer items-center gap-2 border-b border-ink/8 pb-2 font-semibold text-ink">
                <input type="checkbox"
                       @change="selected = $event.target.checked ? [...ids] : []"
                       :checked="ids.length > 0 && selected.length === ids.length"
                       class="h-4 w-4 rounded border-ink/20 text-brass">
                الكل
            </label>
            <div class="max-h-40 space-y-1 overflow-y-auto">
                @forelse ($allEmployees as $emp)
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-paper">
                        <input type="checkbox" name="assigned[]" value="{{ $emp->id }}" x-model="selected"
                               class="h-4 w-4 rounded border-ink/20 text-brass">
                        <span class="text-[13px] text-ink">{{ $emp->name }}</span>
                    </label>
                @empty
                    <p class="text-[13px] text-ink-muted">لا يوجد موظفون.</p>
                @endforelse
            </div>
        </div>
        <p class="mt-1 text-[11px] text-ink-muted">سيُشعَر الموظفون المختارون فقط، وذلك عند اعتماد العقد.</p>
    </div>
</div>
