@extends('layouts.app')
@section('title', 'تعديل الموظف')
@section('subtitle', $employee->name)
@section('content')
<div class="mx-auto max-w-xl space-y-4">
    <form method="POST" action="{{ route('employees.update', $employee) }}" class="rounded-2xl bg-white p-6 shadow-card">
        @csrf @method('PUT')
        <div class="space-y-4">
            <div><label class="lbl">الاسم</label><input name="name" value="{{ old('name', $employee->name) }}" required class="inp">@error('name')<p class="err">{{ $message }}</p>@enderror</div>
            <div><label class="lbl">البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email', $employee->email) }}" required class="inp" dir="ltr">@error('email')<p class="err">{{ $message }}</p>@enderror</div>
            <div><label class="lbl">الجوال</label><input name="phone" value="{{ old('phone', $employee->phone) }}" class="inp" dir="ltr"></div>
            <div><label class="lbl">الدور</label>
                <select name="role" class="inp">
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected(old('role', $employee->roles->pluck('name')->first()) === $role->name)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role')<p class="err">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-ink">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $employee->is_active)) class="h-4 w-4 rounded border-ink/20 text-brass">
                الحساب نشط
            </label>

            {{-- تغيير كلمة المرور من قِبل الأدمن --}}
            <div class="rounded-xl border border-ink/8 bg-paper/50 p-4">
                <p class="mb-3 flex items-center gap-2 text-[13px] font-semibold text-ink">
                    <svg class="h-4 w-4 text-brass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                    تغيير كلمة المرور <span class="font-normal text-ink-muted">(اتركها فارغة للإبقاء عليها)</span>
                </p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div><label class="lbl">كلمة مرور جديدة</label><input type="password" name="password" class="inp" dir="ltr" autocomplete="new-password">@error('password')<p class="err">{{ $message }}</p>@enderror</div>
                    <div><label class="lbl">تأكيد كلمة المرور</label><input type="password" name="password_confirmation" class="inp" dir="ltr" autocomplete="new-password"></div>
                </div>
            </div>
        </div>
        <div class="mt-6 flex gap-3 border-t border-ink/8 pt-5">
            <button class="rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">حفظ التعديلات</button>
            <a href="{{ route('employees.index') }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">إلغاء</a>
        </div>
    </form>

    {{-- منطقة الخطر: حذف الموظف --}}
    @if($employee->id !== auth()->id())
    <div class="rounded-2xl border border-danger/25 bg-danger/5 p-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-[13px] font-semibold text-danger">حذف الموظف</p>
                <p class="text-[12px] text-ink-muted">سيؤدي هذا إلى حذف الحساب نهائياً. لا يمكن التراجع.</p>
            </div>
            <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('حذف الموظف «{{ $employee->name }}» نهائياً؟')">
                @csrf @method('DELETE')
                <button class="rounded-xl border border-danger/40 px-4 py-2 text-[13px] font-semibold text-danger hover:bg-danger hover:text-white transition">حذف نهائي</button>
            </form>
        </div>
    </div>
    @endif
</div>
<style>.lbl{display:block;margin-bottom:.35rem;font-size:.8rem;font-weight:600;color:#22324d}.inp{width:100%;border:1px solid rgba(27,42,65,.12);border-radius:.75rem;padding:.6rem .75rem;font-size:.875rem;outline:none}.inp:focus{border-color:#1499B0;box-shadow:0 0 0 3px rgba(20,153,176,.15)}.err{color:#dc2626;font-size:.75rem;margin-top:.25rem}</style>
@endsection
