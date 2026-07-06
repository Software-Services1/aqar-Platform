@extends('layouts.app')
@section('title', 'الإشعارات')
@section('subtitle', 'كل التنبيهات المتعلقة بالعقود والتراخيص')
@section('content')

<div class="mx-auto max-w-3xl">

    @php $unread = auth()->user()->unreadNotifications()->count(); @endphp

    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-sm text-ink-muted">غير المقروءة:</span>
            <span class="rounded-full bg-danger/10 px-2.5 py-0.5 text-[13px] font-bold text-danger">{{ $unread }}</span>
        </div>
        @if ($unread > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button class="rounded-xl border border-ink/12 bg-white px-4 py-2 text-[13px] font-semibold text-ink hover:bg-paper">
                    تعليم الكل كمقروء
                </button>
            </form>
        @endif
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-card">
        @forelse ($notifications as $n)
            @php
                $isExpiring = $n->type === 'expiring_license';
                $accent = $isExpiring ? 'danger' : 'brass';
            @endphp
            <div class="flex items-start gap-4 border-b border-ink/5 px-5 py-4 transition last:border-0 {{ $n->is_read ? 'bg-white' : 'bg-brass/[0.04]' }}">

                {{-- أيقونة النوع --}}
                <span class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center rounded-xl
                    {{ $isExpiring ? 'bg-danger/12 text-danger' : 'bg-brass/12 text-brass' }}">
                    @if ($isExpiring)
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L14.7 3.9a2 2 0 00-3.4 0z"/></svg>
                    @else
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M9 15l2 2 4-4"/></svg>
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold
                            {{ $isExpiring ? 'bg-danger/10 text-danger' : 'bg-brass/10 text-brass' }}">
                            {{ $isExpiring ? 'ترخيص يقترب من الانتهاء' : 'عقد جديد' }}
                        </span>
                        @unless ($n->is_read)
                            <span class="h-2 w-2 rounded-full bg-danger"></span>
                        @endunless
                    </div>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink">{{ $n->message }}</p>
                    <p class="mt-1 text-[11px] text-ink-muted">{{ $n->created_at->diffForHumans() }} · {{ $n->created_at->format('Y/m/d - H:i') }}</p>
                </div>

                @unless ($n->is_read)
                    <form method="POST" action="{{ route('notifications.read', $n) }}" class="shrink-0">
                        @csrf
                        <button class="rounded-lg px-3 py-1.5 text-[12px] font-semibold text-brass hover:bg-brass/8" title="تعليم كمقروء">
                            مقروء
                        </button>
                    </form>
                @endunless
            </div>
        @empty
            <div class="px-5 py-16 text-center">
                <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-paper text-ink-muted">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 1112 0c0 7 3 7 3 9H3c0-2 3-2 3-9M9 21a3 3 0 006 0"/></svg>
                </div>
                <p class="text-sm font-semibold text-ink">لا توجد إشعارات</p>
                <p class="mt-1 text-xs text-ink-muted">ستظهر هنا تنبيهات العقود الجديدة والتراخيص المقتربة من الانتهاء.</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
