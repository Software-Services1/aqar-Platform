@extends('layouts.app')
@section('title', 'الشركات الخارجية')
@section('subtitle', 'الشركات التي تُنشأ لها عقود وساطة فرعية')
@section('content')
<div class="grid gap-4 lg:grid-cols-3">
    {{-- إضافة شركة --}}
    <div class="lg:col-span-1">
        <div class="rounded-2xl bg-white p-5 shadow-card">
            <h2 class="font-display font-bold text-ink">إضافة شركة</h2>
            <form method="POST" action="{{ route('external-companies.store') }}" class="mt-4 space-y-3">
                @csrf
                <div><label class="lbl">اسم الشركة</label><input name="name" value="{{ old('name') }}" required class="inp">@error('name')<p class="err">{{ $message }}</p>@enderror</div>
                <div><label class="lbl">الشخص المسؤول</label><input name="contact_person" value="{{ old('contact_person') }}" class="inp">@error('contact_person')<p class="err">{{ $message }}</p>@enderror</div>
                <div><label class="lbl">رقم الجوال</label><input name="phone" value="{{ old('phone') }}" class="inp" dir="ltr">@error('phone')<p class="err">{{ $message }}</p>@enderror</div>
                <button class="w-full rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">إضافة</button>
            </form>
        </div>
    </div>

    {{-- القائمة --}}
    <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl bg-white shadow-card">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-ink/8 text-right text-[12px] text-ink-muted">
                    <th class="py-3 pr-5">الشركة</th><th class="px-3 py-3">المسؤول</th><th class="px-3 py-3">الجوال</th>
                    <th class="px-3 py-3">عقود فرعية</th><th class="px-3 py-3">الحالة</th><th class="px-3 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-ink/5">
                    @forelse ($companies as $co)
                        <tr class="hover:bg-paper/60" x-data="{ editing: false }">
                            <td class="py-3 pr-5" :class="editing && 'align-top'">
                                <span x-show="!editing" class="font-semibold text-ink">{{ $co->name }}</span>
                                <form x-show="editing" x-cloak method="POST" action="{{ route('external-companies.update', $co) }}" class="flex flex-wrap items-center gap-2" id="co-{{ $co->id }}">
                                    @csrf @method('PUT')
                                    <input name="name" value="{{ $co->name }}" required class="inp-sm w-32" placeholder="الشركة">
                                    <input name="contact_person" value="{{ $co->contact_person }}" class="inp-sm w-28" placeholder="المسؤول">
                                    <input name="phone" value="{{ $co->phone }}" class="inp-sm w-28" dir="ltr" placeholder="الجوال">
                                    <label class="flex items-center gap-1 text-[12px]">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" @checked($co->is_active)> نشط
                                    </label>
                                    <button class="rounded-lg bg-ink px-3 py-1.5 text-[12px] font-semibold text-white">حفظ</button>
                                    <button type="button" @click="editing=false" class="text-[12px] text-ink-muted hover:text-danger">إلغاء</button>
                                </form>
                            </td>
                            <td class="px-3 py-3 text-ink-muted" x-show="!editing">{{ $co->contact_person ?: '—' }}</td>
                            <td class="px-3 py-3 text-ink-muted" dir="ltr" x-show="!editing">{{ $co->phone ?: '—' }}</td>
                            <td class="px-3 py-3 tabular-nums" x-show="!editing">{{ $co->contracts_count }}</td>
                            <td class="px-3 py-3" x-show="!editing"><span class="{{ $co->is_active ? 'text-ok' : 'text-gone' }} text-[12px] font-semibold">{{ $co->is_active ? 'نشط' : 'موقوف' }}</span></td>
                            <td class="px-3 py-3 text-left whitespace-nowrap" x-show="!editing">
                                <button type="button" @click="editing=true" class="text-[13px] font-semibold text-brass hover:underline">تعديل</button>
                                <form method="POST" action="{{ route('external-companies.destroy', $co) }}" class="inline" onsubmit="return confirm('حذف الشركة؟')">
                                    @csrf @method('DELETE')
                                    <button class="mr-2 text-[13px] font-semibold text-danger hover:underline">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-ink-muted">لا توجد شركات خارجية بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
