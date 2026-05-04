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

    public ?int $classId = null;
    public ?int $termId  = null;

    // Individual recipient IDs
    public array $selectedParentIds = [];

    // Live search
    public string $parentSearch  = '';
    public array  $parentResults = [];

    // Preview
    public bool  $previewing    = false;
    public int   $previewCount  = 0;
    public array $previewParents = []; // [{id, name, email, children}]

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        // Support pre-populating from the Users page "Email" button:
        // /admin/messages/compose?parent=123
        $parentId = request()->query('parent');
        if ($parentId) {
            $parent = ParentGuardian::with('user', 'students')->find($parentId);
            if ($parent) {
                $this->recipientType      = 'individual';
                $this->selectedParentIds  = [$parent->id];
            }
        }
    }

    // ── Rules ─────────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'subject'               => 'required|string|min:3|max:200',
            'body'                  => 'required|string|min:10',
            'recipientType'         => 'required|in:all,class,term,unpaid,individual',
            'classId'               => 'required_if:recipientType,class|nullable|exists:school_classes,id',
            'termId'                => 'required_if:recipientType,term|nullable|exists:terms,id',
            'selectedParentIds'     => 'required_if:recipientType,individual|array',
            'selectedParentIds.*'   => 'exists:parents,id',
        ];
    }

    // ── Recipient type change ─────────────────────────────────────────────────

    public function updatedRecipientType(): void
    {
        $this->previewing        = false;
        $this->previewCount      = 0;
        $this->previewParents    = [];
        $this->selectedParentIds = [];
        $this->parentSearch      = '';
        $this->parentResults     = [];
    }

    // ── Live parent search ────────────────────────────────────────────────────

    /**
     * Fires on every keystroke (wire:model.live, no debounce floor).
     * Starts searching from the very first character typed.
     */
    public function updatedParentSearch(): void
    {
        $term = trim($this->parentSearch);

        if ($term === '') {
            $this->parentResults = [];
            return;
        }

        $this->parentResults = ParentGuardian::whereNotNull('user_id')
            ->where(function ($q) use ($term) {
                // Search parent's own name (stored on User)
                $q->whereHas('user', fn($u) =>
                        $u->where('name',  'like', "{$term}%")   // starts-with for speed
                          ->orWhere('name',  'like', "%{$term}%")  // also contains
                          ->orWhere('email', 'like', "{$term}%")
                    )
                    // Search temp name (pre-portal parents)
                    ->orWhere('_temp_name', 'like', "{$term}%")
                    ->orWhere('_temp_name', 'like', "%{$term}%")
                    // Search by child's name
                    ->orWhereHas('students', fn($s) =>
                        $s->where('first_name', 'like', "{$term}%")
                          ->orWhere('last_name',  'like', "{$term}%")
                          ->orWhere('admission_number', 'like', "%{$term}%")
                    );
            })
            ->with('user', 'students')
            ->limit(8)
            ->get()
            // Exclude already-selected parents from dropdown
            ->reject(fn($p) => in_array($p->id, $this->selectedParentIds))
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->user?->name ?? $p->_temp_name ?? 'Unknown',
                'email'    => $p->user?->email ?? $p->_temp_email ?? '—',
                'children' => $p->students->pluck('first_name')->join(', '),
            ])
            ->values()
            ->toArray();
    }

    // ── Add / Remove individual recipients ───────────────────────────────────

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

    // ── Preview & Send ────────────────────────────────────────────────────────

    public function preview(): void
    {
        $this->validate();

        if ($this->recipientType === 'individual') {
            $parents = ParentGuardian::whereIn('id', $this->selectedParentIds)
                ->with('user', 'students')
                ->get();
        } else {
            $parents = Message::resolveRecipients(
                $this->recipientType,
                $this->classId,
                $this->termId,
            )->load('user', 'students');
        }

        $this->previewCount   = $parents->count();
        $this->previewParents = $parents->map(fn($p) => [
            'id'       => $p->id,
            'name'     => $p->user?->name ?? $p->_temp_name ?? 'Unknown',
            'email'    => $p->user?->email ?? $p->_temp_email ?? '—',
            'children' => $p->students->pluck('first_name')->join(', '),
        ])->values()->toArray();

        $this->previewing = true;
    }

    public function send(): void
    {
        $this->validate();

        $message = Message::create([
            'sender_id'       => auth()->id(),
            'subject'         => $this->subject,
            'body'            => $this->body,
            'recipient_type'  => $this->recipientType,
            'school_class_id' => $this->classId,
            'term_id'         => $this->termId,
            'recipient_count' => 0,
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

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $classes = SchoolClass::ordered()->get();
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
