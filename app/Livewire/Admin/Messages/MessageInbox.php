<?php

namespace App\Livewire\Admin\Messages;

use App\Models\Message;
use Livewire\Component;
use Livewire\WithPagination;

class MessageInbox extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int   $viewing = null; // Message ID

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function view(int $id): void
    {
        $this->viewing = $id;
    }

    public function closeView(): void
    {
        $this->viewing = null;
    }

    public function render()
    {
        $messages = Message::with('sender')
            ->when($this->search, fn($q) =>
                $q->where('subject', 'like', "%{$this->search}%")
                  ->orWhere('body', 'like', "%{$this->search}%")
            )
            ->orderByDesc('created_at')
            ->paginate(20);

        $viewingMessage = $this->viewing
            ? Message::with('sender', 'recipients.parent.user')->find($this->viewing)
            : null;

        return view('livewire.admin.messages.message-inbox', compact('messages', 'viewingMessage'))
            ->layout('layouts.admin', ['title' => 'Messages']);
    }
}
