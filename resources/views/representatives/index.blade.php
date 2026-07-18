@extends('layouts.app')
@section('title', 'المناديب')
@section('subtitle', 'إضافة وتعديل وحذف بيانات المناديب')
@section('content')
<div class="grid gap-4 lg:grid-cols-3">
    {{-- إضافة مندوب --}}
    <div class="lg:col-span-1">
        <div class="rounded-2xl bg-white p-5 shadow-card">
            <h2 class="font-display font-bold text-ink">إضافة مندوب</h2>
            <form method="POST" action="{{ route('representatives.store') }}" class="mt-4 space-y-3">
                @csrf
                <div><label class="lbl">الاسم</label><input name="name" value="{{ old('name') }}" required class="inp">@error('name')<p class="err">{{ $message }}</p>@enderror</div>
                <div><label class="lbl">الجوال</label><input name="phone" value="{{ old('phone') }}" class="inp" dir="ltr">@error('phone')<p class="err">{{ $message }}</p>@enderror</div>
                <button class="w-full rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">إضافة</button>
            </form>
        </div>
    </div>

    {{-- قائمة المناديب --}}
    <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl bg-white shadow-card">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-ink/8 text-right text-[12px] text-ink-muted">
                    <th class="py-3 pr-5">الاسم</th><th class="px-3 py-3">الجوال</th><th class="px-3 py-3">عقود</th>
                    <th class="px-3 py-3">الحالة</th><th class="px-3 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-ink/5">
                    @forelse ($representatives as $rep)
                        <tr class="hover:bg-paper/60" x-data="{ editing: false }">
                            {{-- عرض --}}
                            <template x-if="!editing">
                                <td class="py-3 pr-5 font-semibold text-ink">{{ $rep->name }}</td>
                            </template>
                            {{-- نموذج التعديل المضمّن --}}
                            <td class="py-3 pr-5" x-show="editing" style="display:none">
                                <form method="POST" action="{{ route('representatives.update', $rep) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf @method('PUT')
                                    <input name="name" value="{{ $rep->name }}" required class="inp-sm w-28">
                                    <input name="phone" value="{{ $rep->phone }}" class="inp-sm w-28" dir="ltr">
                                    <label class="flex items-center gap-1 text-[12px]">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" @checked($rep->is_active)> نشط
                                    </label>
                                    <button class="rounded-lg bg-ink px-3 py-1.5 text-[12px] font-semibold text-white">حفظ</button>
                                </form>
                            </td>
                            <td class="px-3 py-3 text-ink-muted" dir="ltr" x-show="!editing">{{ $rep->phone ?: '—' }}</td>
                            <td class="px-3 py-3 tabular-nums" x-show="!editing">{{ $rep->contracts_count }}</td>
                            <td class="px-3 py-3" x-show="!editing">
                                <span class="{{ $rep->is_active ? 'text-ok' : 'text-gone' }} text-[12px] font-semibold">{{ $rep->is_active ? 'نشط' : 'موقوف' }}</span>
                            </td>
                            <td class="px-3 py-3 text-left whitespace-nowrap" x-show="!editing">
                                <button type="button" @click="editing = true" class="text-[13px] font-semibold text-brass hover:underline">تعديل</button>
                                <form method="POST" action="{{ route('representatives.destroy', $rep) }}" class="inline" onsubmit="return confirm('حذف المندوب؟')">
                                    @csrf @method('DELETE')
                                    <button class="mr-2 text-[13px] font-semibold text-danger hover:underline">حذف</button>
                                </form>
                            </td>
                            <td x-show="editing" colspan="4" style="display:none">
                                <button type="button" @click="editing = false" class="px-3 text-[13px] text-ink-muted hover:text-danger">إلغاء</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-ink-muted">لا يوجد مناديب بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
