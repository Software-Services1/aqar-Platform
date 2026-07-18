<div class="relative" x-data="{ open: @entangle('open') }" wire:poll.30s.visible="refreshCount">
    <button wire:click="toggle" @click.away="open = false"
            class="relative grid h-10 w-10 place-items-center rounded-full bg-white shadow-card text-ink hover:bg-paper">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 1112 0c0 7 3 7 3 9H3c0-2 3-2 3-9M9 21a3 3 0 006 0"/></svg>
        @if ($count > 0)
            <span class="absolute -top-0.5 -left-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-danger px-1 text-[11px] font-bold text-white ring-2 ring-paper">{{ $count > 9 ? '9+' : $count }}</span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition.origin.top
         class="absolute left-0 mt-2 w-80 overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-ink/5">
        <div class="flex items-center justify-between border-b border-ink/8 px-4 py-3">
            <p class="font-display font-bold text-ink">الإشعارات</p>
            @if ($count > 0)
                <button wire:click="markAllRead" class="text-[12px] font-semibold text-brass hover:underline">تعليم الكل كمقروء</button>
            @endif
        </div>
        <ul class="max-h-80 divide-y divide-ink/5 overflow-y-auto">
            @forelse ($items as $n)
                <li wire:click="markRead({{ $n->id }})"
                    class="flex cursor-pointer gap-3 px-4 py-3 transition hover:bg-paper/70 {{ $n->is_read ? 'opacity-60' : '' }}">
                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $n->type === 'expiring_license' ? 'bg-danger' : 'bg-brass' }} {{ $n->is_read ? 'opacity-40' : '' }}"></span>
                    <div>
                        <p class="text-[13px] leading-snug text-ink">{{ $n->message }}</p>
                        <p class="mt-0.5 text-[11px] text-ink-muted">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-ink-muted">لا توجد إشعارات.</li>
            @endforelse
        </ul>
        <a href="{{ route('notifications.index') }}" class="block border-t border-ink/8 py-2.5 text-center text-[13px] font-semibold text-brass hover:bg-paper/60">عرض كل الإشعارات</a>
    </div>
</div>
