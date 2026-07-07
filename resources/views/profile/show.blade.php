@extends('layouts.app')
@section('title', 'ملفي الشخصي')
@section('content')
<div class="mx-auto max-w-3xl space-y-4">
    <div class="overflow-hidden rounded-2xl bg-white shadow-card">
        <div class="flex items-center gap-4 border-b border-ink/8 bg-ink px-6 py-5 text-white">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-brass text-xl font-bold">
                {{ mb_substr($user->name, 0, 1) }}
            </div>
            <div>
                <h2 class="font-display text-lg font-bold">{{ $user->name }}</h2>
                <p class="text-sm text-white/70">
                    @foreach ($user->roles as $role)<span class="text-brass">{{ $role->name }}</span>@endforeach
                </p>
            </div>
            <a href="{{ route('profile.edit') }}" class="mr-auto rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold hover:bg-white/20">تعديل بياناتي</a>
        </div>
        <dl class="grid grid-cols-2 gap-px bg-ink/5">
            @foreach ([
                'البريد الإلكتروني' => $user->email,
                'الجوال' => $user->phone ?: '—',
                'الحالة' => $user->is_active ? 'نشط' : 'موقوف',
                'تاريخ الانضمام' => $user->created_at->format('Y-m-d'),
            ] as $k => $v)
                <div class="bg-white px-6 py-4">
                    <dt class="text-[12px] text-ink-muted">{{ $k }}</dt>
                    <dd class="mt-0.5 font-semibold text-ink" dir="{{ $k === 'البريد الإلكتروني' || $k === 'الجوال' ? 'ltr' : 'rtl' }}">{{ $v }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl bg-white p-5 shadow-card">
            <p class="text-[12px] text-ink-muted">عقود أنشأتها</p>
            <p class="mt-1 font-display text-3xl font-bold text-ink">{{ $user->contracts_count }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card">
            <p class="text-[12px] text-ink-muted">تراخيص أنشأتها</p>
            <p class="mt-1 font-display text-3xl font-bold text-ink">{{ $user->licenses_count }}</p>
        </div>
    </div>
</div>
@endsection
