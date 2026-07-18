@extends('layouts.app')
@section('title', 'إضافة موظف جديد')
@section('subtitle', 'سيُنشأ حساب الموظف تلقائياً مع كلمة مرور مؤقتة')
@section('content')
<form method="POST" action="{{ route('employees.store') }}" class="mx-auto max-w-xl rounded-2xl bg-white p-6 shadow-card">
    @csrf
    <div class="space-y-4">
        <div><label class="lbl">الاسم</label><input name="name" value="{{ old('name') }}" required class="inp">@error('name')<p class="err">{{ $message }}</p>@enderror</div>
        <div><label class="lbl">البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email') }}" required class="inp" dir="ltr">@error('email')<p class="err">{{ $message }}</p>@enderror</div>
        <div><label class="lbl">الجوال</label><input name="phone" value="{{ old('phone') }}" class="inp" dir="ltr"></div>
        <div><label class="lbl">الدور</label>
            <select name="role" class="inp">
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(old('role','employee') === $role->name)>{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role')<p class="err">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="mt-6 flex gap-3 border-t border-ink/8 pt-5">
        <button class="rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">إنشاء الحساب</button>
        <a href="{{ route('employees.index') }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">إلغاء</a>
    </div>
</form>
@endsection
