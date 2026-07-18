<a href="{{ route('messages.index') }}" wire:poll.45s.visible="refreshCount"
   class="fixed bottom-6 left-6 z-40 grid h-14 w-14 place-items-center rounded-full bg-ink text-white shadow-lg ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:bg-ink-soft"
   title="الرسائل">
    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    @if ($count > 0)
        <span class="absolute -top-1 -left-1 grid h-6 min-w-6 place-items-center rounded-full bg-danger px-1.5 text-[12px] font-bold text-white ring-2 ring-paper">{{ $count > 99 ? '99+' : $count }}</span>
    @endif
</a>
