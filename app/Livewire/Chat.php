<?php

namespace App\Livewire;

use App\Models\Employee;
use App\Models\Message;
use Livewire\Attributes\Url;
use Livewire\Component;

class Chat extends Component
{
    #[Url]
    public ?int $selectedId = null;

    public string $body = '';
    public string $search = '';

    public function selectConversation(int $id): void
    {
        $this->selectedId = $id;
        $this->markRead();
        $this->body = '';
    }

    /** يُستدعى دورياً (wire:poll) لتحديث الرسائل ووسمها مقروءة */
    public function refresh(): void
    {
        $this->markRead();
    }

    private function markRead(): void
    {
        if ($this->selectedId) {
            Message::where('sender_id', $this->selectedId)
                ->where('receiver_id', auth()->id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    public function send(): void
    {
        $this->validate([
            'body'       => ['required', 'string', 'max:2000'],
            'selectedId' => ['required', 'exists:employees,id'],
        ], [], ['body' => 'الرسالة']);

        Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $this->selectedId,
            'body'        => trim($this->body),
        ]);

        $this->body = '';
        $this->dispatch('messages-updated');
    }

    public function render()
    {
        $me = auth()->id();

        $unread = Message::where('receiver_id', $me)->whereNull('read_at')
            ->selectRaw('sender_id, count(*) as c')
            ->groupBy('sender_id')
            ->pluck('c', 'sender_id');

        $employees = Employee::select('id', 'name')
            ->where('id', '!=', $me)
            ->where('is_active', true)
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        // آخر 60 رسالة فقط (يمنع تضخّم الاستعلام والـDOM في المحادثات الطويلة)
        $messages = $this->selectedId
            ? Message::between($me, $this->selectedId)
                ->latest('id')
                ->limit(60)
                ->get()
                ->sortBy('id')
                ->values()
            : collect();

        $selected = $this->selectedId ? Employee::find($this->selectedId) : null;

        return view('livewire.chat', compact('employees', 'unread', 'messages', 'selected'));
    }
}
