<?php

namespace App\Livewire\Parent;

use App\Models\MessageRecipient;
use Livewire\Component;
use Livewire\WithPagination;

class Messages extends Component
{
    use WithPagination;

    public string $search   = '';
    public ?int   $viewing  = null; // MessageRecipient ID being read

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function open(int $recipientId): void
    {
        $recipient = MessageRecipient::with('message.sender')
            ->findOrFail($recipientId);

        // Security: must belong to this parent
        $parentId = auth()->user()->parentProfile?->id;
        abort_if($recipient->parent_id !== $parentId, 403);

        // Mark as read
        $recipient->markRead();

        $this->viewing = $recipientId;
    }

    public function closeMessage(): void
    {
        $this->viewing = null;
    }

    public function render()
    {
        $parentProfile = auth()->user()->parentProfile;

        if (! $parentProfile) {
            return view('livewire.parent.messages', [
                'recipients' => collect(),
                'unread'     => 0,
                'viewing'    => null,
            ])->layout('layouts.parent', ['title' => 'Messages']);
        }

        $recipients = MessageRecipient::with('message.sender')
            ->where('parent_id', $parentProfile->id)
            ->when($this->search, function ($q) {
                $q->whereHas('message', fn($m) =>
                    $m->where('subject', 'like', "%{$this->search}%")
                      ->orWhere('body', 'like', "%{$this->search}%")
                );
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        $unread = MessageRecipient::where('parent_id', $parentProfile->id)
            ->whereNull('read_at')
            ->count();

        $viewingRecipient = $this->viewing
            ? MessageRecipient::with('message.sender')->find($this->viewing)
            : null;

        return view('livewire.parent.messages', compact('recipients', 'unread', 'viewingRecipient'))
            ->layout('layouts.parent', ['title' => 'Messages']);
    }
}
