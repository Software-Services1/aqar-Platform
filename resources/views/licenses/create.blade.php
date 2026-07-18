@extends('layouts.app')
@section('title', 'إنشاء ترخيص إعلاني')
@section('subtitle', 'للعقد: ' . $contract->project_name . ' (رقم ' . $contract->contract_number . ')')
@section('content')
<form method="POST" action="{{ route('licenses.store', $contract) }}" class="mx-auto max-w-2xl rounded-2xl bg-white p-6 shadow-card"
      x-data="{ selected: @js(array_keys(old('links', []))) }">
    @csrf
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="lbl">رقم الترخيص <span class="text-ink-muted font-normal">(من المنصة الأخرى)</span></label>
            <input name="license_number" value="{{ old('license_number') }}" required class="inp font-mono" placeholder="أدخل رقم الترخيص">
            @error('license_number')<p class="err">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="lbl">حالة الترخيص</label>
            <select name="status" class="inp">
                @foreach ($statuses as $k => $v)
                    <option value="{{ $k }}" @selected(old('status', 'created_unpublished') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        @if ($employees->isNotEmpty())
            <div class="sm:col-span-2">
                <label class="lbl">الموظف <span class="text-ink-muted font-normal">(إنشاء نيابةً عن موظف)</span></label>
                <select name="employee_id" class="inp">
                    <option value="">— أنا ({{ auth()->user()->name }}) —</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(old('employee_id') == $emp->id)>{{ $emp->name }}</option>
                    @endforeach
                </select>
                @error('employee_id')<p class="err">{{ $message }}</p>@enderror
            </div>
        @endif

        <div>
            <label class="lbl">تاريخ الإصدار</label>
            <input type="date" name="issue_date" value="{{ old('issue_date', now()->format('Y-m-d')) }}" required class="inp">
            @error('issue_date')<p class="err">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="lbl">تاريخ انتهاء الترخيص</label>
            <input type="date" name="expiry_date" value="{{ old('expiry_date', $contract->end_date->format('Y-m-d')) }}" class="inp">
            @error('expiry_date')<p class="err">{{ $message }}</p>@enderror
        </div>

        {{-- المنصات + رابط الإعلان الإجباري لكل منصة مختارة --}}
        <div class="sm:col-span-2">
            <label class="lbl">المنصات المنشور عليها <span class="text-ink-muted font-normal">(رابط الإعلان مطلوب لكل منصة)</span></label>
            <div class="space-y-2 rounded-xl border border-ink/12 p-3">
                @forelse ($platforms as $p)
                    <div class="rounded-lg border border-ink/8 p-2.5">
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="checkbox" name="platforms[]" value="{{ $p->name }}" x-model="selected"
                                   class="h-4 w-4 rounded border-ink/20 text-brass">
                            <span class="text-[14px] font-semibold text-ink">{{ $p->name }}</span>
                        </label>
                        <div x-show="selected.includes(@js($p->name))" x-cloak class="mt-2">
                            <input type="url" name="links[{{ $p->name }}]" value="{{ old('links.'.$p->name) }}"
                                   x-bind:required="selected.includes(@js($p->name))"
                                   placeholder="https://رابط الإعلان على {{ $p->name }}"
                                   class="inp text-[13px]" dir="ltr">
                            @error('links.'.$p->name)<p class="err">{{ $message }}</p>@enderror
                        </div>
                    </div>
                @empty
                    <p class="text-[13px] text-ink-muted">لا توجد منصات مفعّلة. أضِفها من تبويبة المنصات.</p>
                @endforelse
            </div>
            <p class="mt-1.5 text-[12px] text-ink-muted">المحدّد: <span x-text="selected.length"></span> منصة</p>
        </div>

        <div class="sm:col-span-2">
            <label class="lbl">ملاحظات</label>
            <textarea name="notes" rows="2" class="inp">{{ old('notes') }}</textarea>
        </div>
    </div>
    <div class="mt-6 flex gap-3 border-t border-ink/8 pt-5">
        <button class="rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">حفظ الترخيص</button>
        <a href="{{ route('contracts.show', $contract) }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">إلغاء</a>
    </div>
</form>
@endsection
