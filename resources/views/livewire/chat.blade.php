<div wire:poll.5s.visible="refresh" class="grid gap-4 lg:grid-cols-3" style="height: calc(100dvh - 9.5rem); min-height: 26rem;">
    {{-- قائمة المحادثات --}}
    <div class="{{ $selected ? 'hidden lg:flex' : 'flex' }} flex-col overflow-hidden rounded-2xl bg-white shadow-card">
        <div class="border-b border-ink/8 p-3">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="بحث عن موظف…"
                   class="w-full rounded-xl border border-ink/10 bg-paper/50 px-3 py-2 text-sm outline-none focus:border-brass">
        </div>
        <div class="flex-1 overflow-y-auto">
            @forelse ($employees as $emp)
                @php $u = $unread[$emp->id] ?? 0; @endphp
                <button type="button" wire:key="emp-{{ $emp->id }}" wire:click="selectConversation({{ $emp->id }})"
                        class="flex w-full items-center gap-3 border-b border-ink/5 px-4 py-3 text-right transition hover:bg-paper/70 {{ $selected && $selected->id === $emp->id ? 'bg-brass/8' : '' }}">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-ink/8 text-[13px] font-bold text-ink">{{ mb_substr($emp->name,0,1) }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[14px] font-semibold text-ink">{{ $emp->name }}</span>
                    </span>
                    @if ($u > 0)
                        <span class="grid h-5 min-w-5 place-items-center rounded-full bg-danger px-1.5 text-[11px] font-bold text-white">{{ $u }}</span>
                    @endif
                </button>
            @empty
                <p class="p-6 text-center text-sm text-ink-muted">لا يوجد موظفون.</p>
            @endforelse
        </div>
    </div>

    {{-- نافذة المحادثة --}}
    <div class="{{ $selected ? 'flex' : 'hidden lg:flex' }} flex-col overflow-hidden rounded-2xl bg-white shadow-card lg:col-span-2">
        @if ($selected)
            <div class="flex items-center gap-3 border-b border-ink/8 px-4 py-3 sm:px-5">
                <button type="button" wire:click="$set('selectedId', null)" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-ink-muted hover:bg-paper lg:hidden" aria-label="رجوع">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <span class="grid h-9 w-9 place-items-center rounded-full bg-brass/15 text-[13px] font-bold text-brass">{{ mb_substr($selected->name,0,1) }}</span>
                <p class="font-display font-bold text-ink">{{ $selected->name }}</p>
            </div>

            <div id="msgs" class="flex-1 space-y-2 overflow-y-auto bg-paper/40 p-5">
                @forelse ($messages as $m)
                    @php $mine = $m->sender_id === auth()->id(); @endphp
                    <div wire:key="msg-{{ $m->id }}" class="flex {{ $mine ? 'justify-start' : 'justify-end' }}">
                        <div class="max-w-[75%] rounded-2xl px-4 py-2 text-sm shadow-sm {{ $mine ? 'bg-ink text-white rounded-tr-sm' : 'bg-white text-ink ring-1 ring-ink/8 rounded-tl-sm' }}">
                            <p class="whitespace-pre-wrap break-words leading-relaxed">{{ $m->body }}</p>
                            <p class="mt-1 text-[10px] {{ $mine ? 'text-white/50' : 'text-ink-muted' }}">{{ $m->created_at->format('H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex h-full items-center justify-center">
                        <p class="text-sm text-ink-muted">ابدأ المحادثة — لا رسائل بعد.</p>
                    </div>
                @endforelse
            </div>

            <form wire:submit="send" class="flex items-end gap-2 border-t border-ink/8 p-3">
                <textarea wire:model="body" rows="1" placeholder="اكتب رسالة… (Enter للإرسال)"
                          @keydown.enter.prevent="$wire.send()"
                          class="flex-1 resize-none rounded-xl border border-ink/12 px-3 py-2.5 text-sm outline-none focus:border-brass focus:ring-2 focus:ring-brass/20"></textarea>
                <button type="submit" wire:loading.attr="disabled" wire:target="send"
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-ink text-white transition hover:bg-ink-soft disabled:opacity-50" aria-label="إرسال">
                    <svg wire:loading.remove wire:target="send" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4z"/></svg>
                    <svg wire:loading wire:target="send" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".3"/>
                        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </button>
            </form>
            @error('body')<p class="px-4 pb-2 text-[12px] text-danger">{{ $message }}</p>@enderror
        @else
            <div class="flex h-full flex-col items-center justify-center gap-3 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-brass/10 text-brass">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
                <p class="text-sm text-ink-muted">اختر موظفاً من القائمة لبدء المحادثة.</p>
            </div>
        @endif
    </div>
</div>
