<?php

namespace App\Livewire\Admin\Academics;

use App\Models\AcademicSession;
use App\Models\Term;
use Livewire\Component;

/**
 * SessionTermManager
 *
 * Manages academic sessions and their terms.
 *
 * Rules:
 * - Only one session can be active at a time.
 * - Only one term can be active at a time (across all sessions).
 * - Activating a term automatically deactivates all other terms.
 * - Activating a session automatically deactivates all other sessions.
 * - Sessions can be created with 3 terms pre-generated (First, Second, Third).
 * - Terms cannot be deleted if they have invoices attached.
 * - Sessions cannot be deleted if they have any terms with invoices.
 */
class SessionTermManager extends Component
{
    // ── Session form ──────────────────────────────────────────────────────────
    public bool   $showSessionForm  = false;
    public string $sessionName      = '';
    public ?int   $editingSessionId = null;

    // ── Term form ─────────────────────────────────────────────────────────────
    public bool   $showTermForm     = false;
    public ?int   $termSessionId    = null; // which session we're adding a term to
    public string $termName         = '';
    public string $termStartDate    = '';
    public string $termEndDate      = '';
    public ?int   $editingTermId    = null;

    // ── Confirmation ──────────────────────────────────────────────────────────
    public ?int    $confirmingDeleteSessionId = null;
    public ?int    $confirmingDeleteTermId    = null;

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

            // Auto-create the 3 terms
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
        // Deactivate all sessions then activate the chosen one
        AcademicSession::query()->update(['is_active' => false]);
        AcademicSession::findOrFail($id)->update(['is_active' => true]);

        // Also deactivate all terms — admin must explicitly activate a term
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

        // Block if any term has invoices
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
        $this->termSessionId  = $term->academic_session_id;
        $this->termName       = $term->name;
        $this->termStartDate  = $term->start_date?->format('Y-m-d') ?? '';
        $this->termEndDate    = $term->end_date?->format('Y-m-d') ?? '';
        $this->editingTermId  = $id;
        $this->showTermForm   = true;
    }

    public function saveTerm(): void
    {
        $this->validate([
            'termName'      => 'required|in:First,Second,Third',
            'termStartDate' => 'nullable|date',
            'termEndDate'   => 'nullable|date|after_or_equal:termStartDate',
        ], [
            'termName.required' => 'Term name is required.',
            'termName.in'       => 'Term must be First, Second, or Third.',
            'termEndDate.after_or_equal' => 'End date must be on or after start date.',
        ]);

        if ($this->editingTermId) {
            $term = Term::findOrFail($this->editingTermId);
            $term->update([
                'name'       => $this->termName,
                'start_date' => $this->termStartDate ?: null,
                'end_date'   => $this->termEndDate ?: null,
            ]);
            session()->flash('success', "{$this->termName} Term updated.");
        } else {
            // Check for duplicate term name in this session
            $exists = Term::where('academic_session_id', $this->termSessionId)
                ->where('name', $this->termName)
                ->exists();

            if ($exists) {
                $this->addError('termName', "{$this->termName} Term already exists in this session.");
                return;
            }

            Term::create([
                'academic_session_id' => $this->termSessionId,
                'name'                => $this->termName,
                'is_active'           => false,
                'start_date'          => $this->termStartDate ?: null,
                'end_date'            => $this->termEndDate ?: null,
            ]);
            session()->flash('success', "{$this->termName} Term added.");
        }

        $this->resetTermForm();
    }

    public function activateTerm(int $id): void
    {
        $term = Term::with('session')->findOrFail($id);

        // The term's session must be active
        if (! $term->session->is_active) {
            session()->flash('error', "Activate the {$term->session->name} session first.");
            return;
        }

        // Deactivate all terms, activate this one
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
            session()->flash('error', "Cannot delete — this term has invoices attached.");
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
        $this->showTermForm   = false;
        $this->termSessionId  = null;
        $this->termName       = '';
        $this->termStartDate  = '';
        $this->termEndDate    = '';
        $this->editingTermId  = null;
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
