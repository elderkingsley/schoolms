<?php

namespace App\Livewire\Admin\Messages;

use App\Jobs\SendBulkMessageJob;
use App\Models\Message;
use App\Models\ParentGuardian;
use App\Models\SchoolClass;
use App\Models\Term;
use Livewire\Component;

class MessageCompose extends Component
{
    public string $subject       = '';
    public string $body          = '';
    public string $recipientType = 'all';

    // Used when recipientType = 'class'
    public ?int $classId = null;

    // Used when recipientType = 'term'
    public ?int $termId = null;

    // Used when recipientType = 'individual'
    // Stores selected parent IDs
    public array $selectedParentIds = [];

    // Search for individual parents
    public string $parentSearch  = '';
    public array  $parentResults = [];

    // Preview state
    public bool  $previewing      = false;
    public int   $previewCount    = 0;

    protected function rules(): array
    {
        return [
            'subject'       => 'required|string|min:3|max:200',
            'body'          => 'required|string|min:10',
            'recipientType' => 'required|in:all,class,term,unpaid,individual',
            'classId'       => 'required_if:recipientType,class|nullable|exists:school_classes,id',
            'termId'        => 'required_if:recipientType,term|nullable|exists:terms,id',
            'selectedParentIds' => 'required_if:recipientType,individual|array',
            'selectedParentIds.*' => 'exists:parents,id',
        ];
    }

    public function updatedRecipientType(): void
    {
        $this->previewing = false;
        $this->previewCount = 0;
        $this->selectedParentIds = [];
        $this->parentSearch = '';
        $this->parentResults = [];
    }

    public function updatedParentSearch(): void
    {
        if (strlen($this->parentSearch) < 2) {
            $this->parentResults = [];
            return;
        }

        $this->parentResults = ParentGuardian::whereNotNull('user_id')
            ->where(function ($q) {
                $q->where('_temp_name', 'like', "%{$this->parentSearch}%")
                  ->orWhereHas('user', fn($u) =>
                      $u->where('name', 'like', "%{$this->parentSearch}%")
                        ->orWhere('email', 'like', "%{$this->parentSearch}%")
                  )
                  ->orWhereHas('students', fn($s) =>
                      $s->where('first_name', 'like', "%{$this->parentSearch}%")
                        ->orWhere('last_name', 'like', "%{$this->parentSearch}%")
                        ->orWhere('admission_number', 'like', "%{$this->parentSearch}%")
                  );
            })
            ->with('user', 'students')
            ->limit(8)
            ->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->user?->name ?? $p->_temp_name ?? 'Unknown',
                'email'    => $p->user?->email ?? '—',
                'children' => $p->students->pluck('first_name')->join(', '),
            ])
            ->toArray();
    }

    public function addParent(int $parentId): void
    {
        if (! in_array($parentId, $this->selectedParentIds)) {
            $this->selectedParentIds[] = $parentId;
        }
        $this->parentSearch  = '';
        $this->parentResults = [];
    }

    public function removeParent(int $parentId): void
    {
        $this->selectedParentIds = array_values(
            array_filter($this->selectedParentIds, fn($id) => $id !== $parentId)
        );
    }

    public function preview(): void
    {
        $this->validate();

        $this->previewCount = $this->resolveCount();
        $this->previewing   = true;
    }

    public function send(): void
    {
        $this->validate();

        $message = Message::create([
            'sender_id'      => auth()->id(),
            'subject'        => $this->subject,
            'body'           => $this->body,
            'recipient_type' => $this->recipientType,
            'school_class_id'=> $this->classId,
            'term_id'        => $this->termId,
            'recipient_count'=> 0,
        ]);

        SendBulkMessageJob::dispatch(
            $message,
            $this->recipientType === 'individual' ? $this->selectedParentIds : []
        );

        session()->flash('success', 'Message queued and will be delivered shortly.');
        $this->redirect(route('admin.messages'));
    }

    protected function resolveCount(): int
    {
        if ($this->recipientType === 'individual') {
            return count($this->selectedParentIds);
        }

        return Message::resolveRecipients(
            $this->recipientType,
            $this->classId,
            $this->termId,
        )->count();
    }

    public function render()
    {
        $classes = SchoolClass::orderBy('order')->get();
        $terms   = Term::with('session')->orderByDesc('id')->get();

        $selectedParents = count($this->selectedParentIds)
            ? ParentGuardian::whereIn('id', $this->selectedParentIds)
                ->with('user', 'students')
                ->get()
            : collect();

        return view('livewire.admin.messages.message-compose',
            compact('classes', 'terms', 'selectedParents'))
            ->layout('layouts.admin', ['title' => 'Compose Message']);
    }
}
