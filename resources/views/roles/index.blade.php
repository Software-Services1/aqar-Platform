@extends('layouts.app')
@section('title', 'الأدوار والصلاحيات')
@section('subtitle', 'إنشاء أدوار وتخصيص صلاحياتها ديناميكياً')
@section('content')
<div class="grid gap-4 lg:grid-cols-3">
    {{-- إنشاء دور جديد --}}
    <div class="lg:col-span-1">
        <div class="rounded-2xl bg-white p-5 shadow-card">
            <h2 class="font-display font-bold text-ink">دور جديد</h2>
            <form method="POST" action="{{ route('roles.store') }}" class="mt-4 space-y-3">
                @csrf
                <div><label class="lbl">اسم الدور</label><input name="name" value="{{ old('name') }}" required class="inp" placeholder="مثال: مشرف">@error('name')<p class="err">{{ $message }}</p>@enderror</div>
                <div>
                    <label class="lbl">الصلاحيات</label>
                    <div class="space-y-2 rounded-xl border border-ink/10 p-3">
                        @foreach ($permissions as $perm)
                            <label class="flex items-center gap-2 text-[13px] text-ink">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="h-4 w-4 rounded border-ink/20 text-brass">
                                {{ $permissionLabels[$perm->name] ?? $perm->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <button class="w-full rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">إنشاء الدور</button>
            </form>
        </div>
    </div>

    {{-- قائمة الأدوار --}}
    <div class="lg:col-span-2 space-y-4">
        @foreach ($roles as $role)
            @php $isCore = in_array($role->name, ['manager','employee']); @endphp
            <div class="rounded-2xl bg-white p-5 shadow-card" x-data="{ editing: false }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-ink/10 px-3 py-1 text-sm font-bold text-ink">{{ $role->name }}</span>
                        <span class="text-[12px] text-ink-muted">{{ $role->users_count }} مستخدم</span>
                        @if($isCore)<span class="text-[11px] text-brass">(أساسي)</span>@endif
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="editing = !editing" class="text-[13px] font-semibold text-brass hover:underline">تعديل الصلاحيات</button>
                        @unless($isCore)
                            <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('حذف الدور؟')">
                                @csrf @method('DELETE')
                                <button class="text-[13px] font-semibold text-danger hover:underline">حذف</button>
                            </form>
                        @endunless
                    </div>
                </div>

                {{-- الصلاحيات الحالية (عرض) --}}
                <div class="mt-3 flex flex-wrap gap-1.5" x-show="!editing">
                    @forelse ($role->permissions as $perm)
                        <span class="rounded-full bg-brass/10 px-2.5 py-0.5 text-[12px] font-medium text-brass">{{ $permissionLabels[$perm->name] ?? $perm->name }}</span>
                    @empty
                        <span class="text-[12px] text-ink-muted">لا توجد صلاحيات.</span>
                    @endforelse
                </div>

                {{-- نموذج التعديل --}}
                <form method="POST" action="{{ route('roles.update', $role) }}" class="mt-3" x-show="editing" style="display:none">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="lbl">اسم الدور</label>
                        <input name="name" value="{{ $role->name }}" required class="inp" @disabled($isCore)>
                        @if($isCore)<p class="mt-1 text-[11px] text-ink-muted">لا يمكن تغيير اسم الأدوار الأساسية.</p>@endif
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($permissions as $perm)
                            <label class="flex items-center gap-2 rounded-lg border border-ink/8 px-3 py-2 text-[13px] text-ink">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                       @checked($role->permissions->contains('name', $perm->name))
                                       class="h-4 w-4 rounded border-ink/20 text-brass">
                                {{ $permissionLabels[$perm->name] ?? $perm->name }}
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button class="rounded-xl bg-ink px-5 py-2 text-sm font-semibold text-white hover:bg-ink-soft">حفظ</button>
                        <button type="button" @click="editing = false" class="rounded-xl px-4 py-2 text-sm text-ink-muted hover:bg-paper">إلغاء</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>
<style>.lbl{display:block;margin-bottom:.35rem;font-size:.8rem;font-weight:600;color:#22324d}.inp{width:100%;border:1px solid rgba(27,42,65,.12);border-radius:.75rem;padding:.6rem .75rem;font-size:.875rem;outline:none}.inp:focus{border-color:#A77C3C;box-shadow:0 0 0 3px rgba(167,124,60,.15)}.err{color:#dc2626;font-size:.75rem;margin-top:.25rem}</style>
@endsection
