@extends('layouts.app')
@section('title', 'تعديل الموظف')
@section('subtitle', $employee->name)
@section('content')
<form method="POST" action="{{ route('employees.update', $employee) }}" class="mx-auto max-w-xl rounded-2xl bg-white p-6 shadow-card">
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
    </div>
    <div class="mt-6 flex gap-3 border-t border-ink/8 pt-5">
        <button class="rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">حفظ التعديلات</button>
        <a href="{{ route('employees.index') }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">إلغاء</a>
    </div>
</form>
<style>.lbl{display:block;margin-bottom:.35rem;font-size:.8rem;font-weight:600;color:#22324d}.inp{width:100%;border:1px solid rgba(27,42,65,.12);border-radius:.75rem;padding:.6rem .75rem;font-size:.875rem;outline:none}.inp:focus{border-color:#A77C3C;box-shadow:0 0 0 3px rgba(167,124,60,.15)}.err{color:#dc2626;font-size:.75rem;margin-top:.25rem}</style>
@endsection
