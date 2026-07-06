@extends('layouts.app')
@section('title', 'المنصات الإعلانية')
@section('subtitle', 'إدارة منصات النشر المتاحة للتراخيص')
@section('content')
<div class="grid gap-5 lg:grid-cols-3">

    {{-- نموذج الإضافة --}}
    <div class="lg:col-span-1">
        <div class="rounded-2xl bg-white p-5 shadow-card">
            <h2 class="font-display text-base font-bold text-ink">إضافة منصة جديدة</h2>
            <p class="mt-1 text-xs text-ink-muted">تظهر المنصة في قائمة الاختيار عند إنشاء ترخيص.</p>

            <form method="POST" action="{{ route('platforms.store') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-[13px] font-semibold text-ink">اسم المنصة</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="مثال: عقار، بيوت، إنستجرام"
                           class="w-full rounded-xl border border-ink/12 bg-paper/40 px-3 py-2.5 text-sm focus:border-brass focus:ring-2 focus:ring-brass/20 focus:outline-none">
                    @error('name')<p class="mt-1 text-[12px] text-danger">{{ $message }}</p>@enderror
                </div>
                <button class="w-full rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">
                    + إضافة المنصة
                </button>
            </form>
        </div>
    </div>

    {{-- قائمة المنصات --}}
    <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl bg-white shadow-card">
            <div class="flex items-center justify-between border-b border-ink/8 px-5 py-3">
                <p class="font-display font-bold text-ink">المنصات المسجّلة</p>
                <span class="rounded-full bg-paper px-2.5 py-0.5 text-[12px] font-semibold text-ink-muted">{{ $platforms->count() }}</span>
            </div>

            @if ($platforms->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-ink-muted">لا توجد منصات بعد — أضف أول منصة من النموذج.</p>
            @else
                <ul class="divide-y divide-ink/5">
                    @foreach ($platforms as $platform)
                        <li x-data="{ editing: false }" class="px-5 py-3.5">
                            <div class="flex items-center justify-between gap-3">
                                {{-- العرض --}}
                                <div x-show="!editing" class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-brass/12 font-display text-sm font-bold text-brass">
                                        {{ mb_substr($platform->name, 0, 1) }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-ink">{{ $platform->name }}</p>
                                        <p class="text-[11px] text-ink-muted" dir="ltr">{{ $platform->slug }}</p>
                                    </div>
                                </div>

                                {{-- التعديل --}}
                                <form x-show="editing" x-cloak method="POST" action="{{ route('platforms.update', $platform) }}"
                                      class="flex flex-1 items-center gap-2">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="is_active" value="{{ $platform->is_active ? 1 : 0 }}">
                                    <input type="text" name="name" value="{{ $platform->name }}" required
                                           class="flex-1 rounded-lg border border-ink/12 px-3 py-1.5 text-sm focus:border-brass focus:ring-2 focus:ring-brass/20 focus:outline-none">
                                    <button class="rounded-lg bg-ink px-3 py-1.5 text-[13px] font-semibold text-white hover:bg-ink-soft">حفظ</button>
                                    <button type="button" @click="editing = false" class="rounded-lg px-2 py-1.5 text-[13px] text-ink-muted hover:text-ink">إلغاء</button>
                                </form>

                                {{-- الإجراءات --}}
                                <div x-show="!editing" class="flex items-center gap-2">
                                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $platform->is_active ? 'bg-ok/12 text-ok' : 'bg-gone/12 text-gone' }}">
                                        {{ $platform->is_active ? 'مفعّلة' : 'موقوفة' }}
                                    </span>

                                    {{-- تبديل التفعيل --}}
                                    <form method="POST" action="{{ route('platforms.update', $platform) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="name" value="{{ $platform->name }}">
                                        <input type="hidden" name="is_active" value="{{ $platform->is_active ? 0 : 1 }}">
                                        <button class="rounded-lg p-1.5 text-ink-muted hover:bg-paper hover:text-ink" title="{{ $platform->is_active ? 'إيقاف' : 'تفعيل' }}">
                                            @if ($platform->is_active)
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="7" width="18" height="10" rx="5"/><circle cx="16" cy="12" r="3" fill="currentColor"/></svg>
                                            @else
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="7" width="18" height="10" rx="5"/><circle cx="8" cy="12" r="3" fill="currentColor"/></svg>
                                            @endif
                                        </button>
                                    </form>

                                    <button type="button" @click="editing = true" class="rounded-lg p-1.5 text-ink-muted hover:bg-paper hover:text-ink" title="تعديل">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                                    </button>

                                    <form method="POST" action="{{ route('platforms.destroy', $platform) }}"
                                          onsubmit="return confirm('حذف المنصة «{{ $platform->name }}»؟')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg p-1.5 text-ink-muted hover:bg-danger/10 hover:text-danger" title="حذف">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m2 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
