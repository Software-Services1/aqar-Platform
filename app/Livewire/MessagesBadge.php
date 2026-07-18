<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class MessagesBadge extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('messages-updated')]
    public function refreshCount(): void
    {
        $this->count = auth()->user()->unreadMessagesCount();
    }

    public function render()
    {
        return view('livewire.messages-badge');
    }
}
