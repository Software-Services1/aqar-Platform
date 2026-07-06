<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $count = 0;
    public bool $open = false;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('notifications-updated')]
    public function refreshCount(): void
    {
        $this->count = auth()->user()->unreadNotifications()->count();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markRead(int $id): void
    {
        $n = auth()->user()->notifications()->whereKey($id)->first();
        $n?->markAsRead();
        $this->refreshCount();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications()->update(['is_read' => true]);
        $this->refreshCount();
    }

    public function render()
    {
        return view('livewire.notification-bell', [
            'items' => auth()->user()->notifications()->take(8)->get(),
        ]);
    }
}
