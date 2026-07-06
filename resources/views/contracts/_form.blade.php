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
        <select name="employee_id" class="inp">
            <option value="">— غير محدد —</option>
            @foreach ($employees as $emp)
                <option value="{{ $emp->id }}" @selected(old('employee_id', $c?->employee_id) == $emp->id)>{{ $emp->name }}</option>
            @endforeach
        </select>
        @error('employee_id')<p class="err">{{ $message }}</p>@enderror
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
</div>
