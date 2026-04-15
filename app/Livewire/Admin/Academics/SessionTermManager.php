<?php
// Deploy to: app/Livewire/Admin/Academics/SessionTermManager.php
// REPLACES existing file — adds school_days_count and next_term_begins to term form.

namespace App\Livewire\Admin\Academics;

use App\Models\AcademicSession;
use App\Models\Term;
use Livewire\Component;

class SessionTermManager extends Component
{
    // ── Session form ──────────────────────────────────────────────────────────
    public bool   $showSessionForm  = false;
    public string $sessionName      = '';
    public ?int   $editingSessionId = null;

    // ── Term form ─────────────────────────────────────────────────────────────
    public bool   $showTermForm       = false;
    public ?int   $termSessionId      = null;
    public string $termName           = '';
    public string $termStartDate      = '';
    public string $termEndDate        = '';
    public string $termSchoolDays     = '';   // NEW: how many days school is open
    public string $termNextTermBegins = '';   // NEW: date printed on report cards
    public ?int   $editingTermId      = null;

    // ── Confirmation ──────────────────────────────────────────────────────────
    public ?int $confirmingDeleteSessionId = null;
    public ?int $confirmingDeleteTermId    = null;

    // ── Session CRUD ──────────────────────────────────────────────────────────

    public function openCreateSession(): void
    {
        $this->resetSessionForm();
        $this->showSessionForm  = true;
        $this->editingSessionId = null;
    }

    public function openEditSession(int $id): void
    {
        $session = AcademicSession::findOrFail($id);
        $this->sessionName      = $session->name;
        $this->editingSessionId = $id;
        $this->showSessionForm  = true;
    }

    public function saveSession(): void
    {
        $this->validate([
            'sessionName' => 'required|string|min:4|max:20',
        ], [
            'sessionName.required' => 'Session name is required (e.g. 2025/2026).',
            'sessionName.min'      => 'Session name must be at least 4 characters.',
        ]);

        if ($this->editingSessionId) {
            $session = AcademicSession::findOrFail($this->editingSessionId);
            $session->update(['name' => $this->sessionName]);
            session()->flash('success', "Session updated to {$this->sessionName}.");
        } else {
            $session = AcademicSession::create([
                'name'      => $this->sessionName,
                'is_active' => false,
            ]);

            foreach (['First', 'Second', 'Third'] as $termName) {
                Term::create([
                    'academic_session_id' => $session->id,
                    'name'                => $termName,
                    'is_active'           => false,
                ]);
            }

            session()->flash('success', "Session {$this->sessionName} created with 3 terms.");
        }

        $this->resetSessionForm();
    }

    public function activateSession(int $id): void
    {
        AcademicSession::query()->update(['is_active' => false]);
        AcademicSession::findOrFail($id)->update(['is_active' => true]);
        Term::query()->update(['is_active' => false]);
        session()->flash('success', 'Session activated. Please activate the current term below.');
    }

    public function confirmDeleteSession(int $id): void
    {
        $this->confirmingDeleteSessionId = $id;
    }

    public function deleteSession(): void
    {
        $session = AcademicSession::with('terms.invoices')->findOrFail($this->confirmingDeleteSessionId);

        foreach ($session->terms as $term) {
            if ($term->invoices()->exists()) {
                session()->flash('error', "Cannot delete session — {$term->name} Term has invoices attached.");
                $this->confirmingDeleteSessionId = null;
                return;
            }
        }

        if ($session->is_active) {
            session()->flash('error', 'Cannot delete the active session.');
            $this->confirmingDeleteSessionId = null;
            return;
        }

        $session->terms()->delete();
        $session->delete();
        session()->flash('success', 'Session deleted.');
        $this->confirmingDeleteSessionId = null;
    }

    // ── Term CRUD ─────────────────────────────────────────────────────────────

    public function openCreateTerm(int $sessionId): void
    {
        $this->resetTermForm();
        $this->termSessionId = $sessionId;
        $this->showTermForm  = true;
        $this->editingTermId = null;
    }

    public function openEditTerm(int $id): void
    {
        $term = Term::findOrFail($id);
        $this->termSessionId      = $term->academic_session_id;
        $this->termName           = $term->name;
        $this->termStartDate      = $term->start_date?->format('Y-m-d') ?? '';
        $this->termEndDate        = $term->end_date?->format('Y-m-d') ?? '';
        $this->termSchoolDays     = $term->school_days_count !== null ? (string) $term->school_days_count : '';
        $this->termNextTermBegins = $term->next_term_begins?->format('Y-m-d') ?? '';
        $this->editingTermId      = $id;
        $this->showTermForm       = true;
    }

    public function saveTerm(): void
    {
        $this->validate([
            'termName'           => 'required|in:First,Second,Third',
            'termStartDate'      => 'nullable|date',
            'termEndDate'        => 'nullable|date|after_or_equal:termStartDate',
            'termSchoolDays'     => 'nullable|integer|min:1|max:366',
            'termNextTermBegins' => 'nullable|date',
        ], [
            'termName.required'              => 'Term name is required.',
            'termName.in'                    => 'Term must be First, Second, or Third.',
            'termEndDate.after_or_equal'     => 'End date must be on or after start date.',
            'termSchoolDays.integer'         => 'School days must be a whole number.',
            'termSchoolDays.min'             => 'School days must be at least 1.',
            'termSchoolDays.max'             => 'School days cannot exceed 366.',
        ]);

        $payload = [
            'name'              => $this->termName,
            'start_date'        => $this->termStartDate        ?: null,
            'end_date'          => $this->termEndDate          ?: null,
            'school_days_count' => $this->termSchoolDays       !== '' ? (int) $this->termSchoolDays : null,
            'next_term_begins'  => $this->termNextTermBegins   ?: null,
        ];

        if ($this->editingTermId) {
            Term::findOrFail($this->editingTermId)->update($payload);
            session()->flash('success', "{$this->termName} Term updated.");
        } else {
            $exists = Term::where('academic_session_id', $this->termSessionId)
                ->where('name', $this->termName)
                ->exists();

            if ($exists) {
                $this->addError('termName', "{$this->termName} Term already exists in this session.");
                return;
            }

            Term::create(array_merge($payload, [
                'academic_session_id' => $this->termSessionId,
                'is_active'           => false,
            ]));
            session()->flash('success', "{$this->termName} Term added.");
        }

        $this->resetTermForm();
    }

    public function activateTerm(int $id): void
    {
        $term = Term::with('session')->findOrFail($id);

        if (! $term->session->is_active) {
            session()->flash('error', "Activate the {$term->session->name} session first.");
            return;
        }

        Term::query()->update(['is_active' => false]);
        $term->update(['is_active' => true]);
        session()->flash('success', "{$term->name} Term ({$term->session->name}) is now active.");
    }

    public function confirmDeleteTerm(int $id): void
    {
        $this->confirmingDeleteTermId = $id;
    }

    public function deleteTerm(): void
    {
        $term = Term::findOrFail($this->confirmingDeleteTermId);

        if ($term->is_active) {
            session()->flash('error', 'Cannot delete the active term.');
            $this->confirmingDeleteTermId = null;
            return;
        }

        if ($term->invoices()->exists()) {
            session()->flash('error', 'Cannot delete — this term has invoices attached.');
            $this->confirmingDeleteTermId = null;
            return;
        }

        $term->delete();
        session()->flash('success', 'Term deleted.');
        $this->confirmingDeleteTermId = null;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resetSessionForm(): void
    {
        $this->showSessionForm  = false;
        $this->sessionName      = '';
        $this->editingSessionId = null;
        $this->resetErrorBag();
    }

    private function resetTermForm(): void
    {
        $this->showTermForm       = false;
        $this->termSessionId      = null;
        $this->termName           = '';
        $this->termStartDate      = '';
        $this->termEndDate        = '';
        $this->termSchoolDays     = '';
        $this->termNextTermBegins = '';
        $this->editingTermId      = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $sessions = AcademicSession::with(['terms' => fn($q) => $q->orderByRaw("FIELD(name,'First','Second','Third')")])
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        return view('livewire.admin.academics.session-term-manager', compact('sessions'))
            ->layout('layouts.admin', ['title' => 'Sessions & Terms']);
    }
}
