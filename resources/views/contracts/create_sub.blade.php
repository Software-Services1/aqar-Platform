@extends('layouts.app')
@section('title', 'عقد فرعي جديد')
@section('subtitle', 'مشتق من العقد رقم ' . $parent->contract_number . ' — لشركة أخرى')
@section('content')
<div class="mx-auto max-w-3xl">
    {{-- بانر التوضيح --}}
    <div class="mb-4 flex items-start gap-3 rounded-2xl border border-brass/25 bg-brass/8 p-4">
        <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brass/15 text-brass">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10M7 17L17 7"/></svg>
        </span>
        <div class="text-[13px] leading-relaxed">
            <p class="font-semibold text-ink">عقد فرعي مشتق من: «{{ $parent->project_name }}» (رقم {{ $parent->contract_number }})</p>
            <p class="text-ink-muted">تم نسخ بيانات العقد الأصل بالكامل. أدخل <b>رقم عقد جديد</b> وعدّل ما يلزم لشركة الطرف الآخر. هذا العقد الفرعي <b>لا يتطلّب إصدار ترخيص</b> ولن تُرسَل إشعارات للموظفين — الغرض تنظيمي وحفظ العمل فقط.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('contracts.sub.store', $parent) }}" class="rounded-2xl bg-white p-6 shadow-card">
        @csrf

        {{-- بيانات الشركة الخارجية --}}
        <div class="mb-5 rounded-xl border border-brass/20 bg-brass/5 p-4">
            <p class="mb-3 flex items-center gap-2 text-[13px] font-bold text-ink">
                <svg class="h-4 w-4 text-brass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21V5a1 1 0 011-1h9a1 1 0 011 1v16M15 21V9h4a1 1 0 011 1v11M8 8h3M8 12h3M8 16h3"/></svg>
                بيانات الشركة الخارجية
            </p>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="lbl">اسم الشركة</label>
                    <input name="ext_company_name" value="{{ old('ext_company_name') }}" required class="inp" list="ext-companies" placeholder="اسم الشركة الطرف الآخر">
                    <datalist id="ext-companies">
                        @foreach ($externalCompanies as $co)
                            <option value="{{ $co->name }}">{{ $co->phone }}</option>
                        @endforeach
                    </datalist>
                    @error('ext_company_name')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl">الشخص المسؤول عن العقد</label>
                    <input name="ext_contact_person" value="{{ old('ext_contact_person') }}" class="inp" placeholder="اسم المسؤول">
                    @error('ext_contact_person')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl">رقم الجوال</label>
                    <input name="ext_phone" value="{{ old('ext_phone') }}" class="inp" dir="ltr" placeholder="05xxxxxxxx">
                    @error('ext_phone')<p class="err">{{ $message }}</p>@enderror
                </div>
            </div>
            <p class="mt-2 text-[11px] text-ink-muted">إن أدخلت اسم شركة موجودة مسبقاً فسيُعاد استخدامها تلقائياً.</p>
        </div>

        @include('contracts._form')
        <div class="mt-6 flex gap-3 border-t border-ink/8 pt-5">
            <button class="rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">حفظ العقد الفرعي</button>
            <a href="{{ route('contracts.show', $parent) }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">إلغاء</a>
        </div>
    </form>
</div>
@endsection
