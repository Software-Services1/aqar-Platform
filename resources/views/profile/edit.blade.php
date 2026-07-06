@extends('layouts.app')
@section('title', 'تعديل ملفي الشخصي')
@section('content')
<form method="POST" action="{{ route('profile.update') }}" class="mx-auto max-w-xl rounded-2xl bg-white p-6 shadow-card">
    @csrf @method('PUT')
    <div class="space-y-4">
        <div><label class="lbl">الاسم</label><input name="name" value="{{ old('name', $user->name) }}" required class="inp">@error('name')<p class="err">{{ $message }}</p>@enderror</div>
        <div><label class="lbl">البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required class="inp" dir="ltr">@error('email')<p class="err">{{ $message }}</p>@enderror</div>
        <div><label class="lbl">الجوال</label><input name="phone" value="{{ old('phone', $user->phone) }}" class="inp" dir="ltr">@error('phone')<p class="err">{{ $message }}</p>@enderror</div>

        <div class="border-t border-ink/8 pt-4">
            <p class="mb-3 text-[13px] font-semibold text-ink">تغيير كلمة المرور <span class="font-normal text-ink-muted">(اتركها فارغة للإبقاء عليها)</span></p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="lbl">كلمة المرور الجديدة</label><input type="password" name="password" class="inp" dir="ltr">@error('password')<p class="err">{{ $message }}</p>@enderror</div>
                <div><label class="lbl">تأكيد كلمة المرور</label><input type="password" name="password_confirmation" class="inp" dir="ltr"></div>
            </div>
        </div>
    </div>
    <div class="mt-6 flex gap-3 border-t border-ink/8 pt-5">
        <button class="rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">حفظ</button>
        <a href="{{ route('profile.show') }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">إلغاء</a>
    </div>
</form>
<style>.lbl{display:block;margin-bottom:.35rem;font-size:.8rem;font-weight:600;color:#22324d}.inp{width:100%;border:1px solid rgba(27,42,65,.12);border-radius:.75rem;padding:.6rem .75rem;font-size:.875rem;outline:none}.inp:focus{border-color:#1499B0;box-shadow:0 0 0 3px rgba(20,153,176,.15)}.err{color:#dc2626;font-size:.75rem;margin-top:.25rem}</style>
@endsection
