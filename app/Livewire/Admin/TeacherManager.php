<?php

namespace App\Livewire\Admin;

use App\Models\SchoolClass;
use App\Models\TeacherRegistration;
use App\Models\User;
use App\Notifications\UserWelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * TeacherManager
 *
 * Two tabs:
 *
 * 1. Staff List — all teachers and TAs.
 *    - Add manually (generates temp password, sends welcome email).
 *    - Edit name / email / phone.
 *    - Assign form class + teaching assistant class independently.
 *    - Reset password.
 *    - Deactivate / reactivate.
 *
 * 2. Registrations — self-registration queue from /staff/register.
 *    - View pending applications.
 *    - Approve → creates User account, sends welcome email.
 *    - Reject → records reason (not shown publicly).
 *
 * Class assignments:
 *    - form_teacher_id      → lead class teacher (one per class)
 *    - assistant_teacher_id → teaching assistant (one per class)
 *    A teacher can be form teacher of one class AND assistant of another simultaneously.
 */
class TeacherManager extends Component
{
    use WithPagination;

    // ── Tab state ─────────────────────────────────────────────────────────────
    public string $activeTab = 'staff'; // 'staff' | 'registrations'

    // ── Staff list ────────────────────────────────────────────────────────────
    public string $search = '';

    // ── Add/Edit staff form ───────────────────────────────────────────────────
    public bool   $showForm          = false;
    public ?int   $editingId         = null;
    public string $name              = '';
    public string $email             = '';
    public string $phone             = '';
    public string $staffRole         = 'teacher'; // 'teacher' | 'teaching_assistant'
    public ?int   $formClassId       = null;
    public ?int   $assistantClassId  = null;

    // ── Confirmation states ───────────────────────────────────────────────────
    public ?int   $confirmingResetId  = null;
    public ?int   $confirmingToggleId = null;

    // ── Registration review ───────────────────────────────────────────────────
    public ?int   $reviewingId        = null; // registration being approved/rejected
    public string $rejectionReason    = '';
    public bool   $showRejectForm     = false;

    // ── Livewire lifecycle ────────────────────────────────────────────────────

    public function updatedSearch(): void { $this->resetPage(); }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // ── Staff CRUD ────────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetStaffForm();
        $this->showForm  = true;
        $this->editingId = null;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        abort_if(! in_array($user->user_type, ['teacher', 'teaching_assistant']), 403);

        $formClass      = SchoolClass::where('form_teacher_id', $id)->first();
        $assistantClass = SchoolClass::where('assistant_teacher_id', $id)->first();

        $this->name             = $user->name;
        $this->email            = $user->email;
        $this->phone            = $user->phone ?? '';
        $this->staffRole        = $user->user_type;
        $this->formClassId      = $formClass?->id;
        $this->assistantClassId = $assistantClass?->id;
        $this->editingId        = $id;
        $this->showForm         = true;
    }

    public function save(): void
    {
        $uniqueEmail = $this->editingId
            ? "unique:users,email,{$this->editingId}"
            : 'unique:users,email';

        $this->validate([
            'name'             => 'required|string|min:2|max:100',
            'email'            => "required|email|{$uniqueEmail}",
            'phone'            => 'nullable|string|max:20',
            'staffRole'        => 'required|in:teacher,teaching_assistant',
            'formClassId'      => 'nullable|exists:school_classes,id',
            'assistantClassId' => 'nullable|exists:school_classes,id',
        ], [
            'email.unique'    => 'A user with this email already exists.',
            'staffRole.in'    => 'Role must be Teacher or Teaching Assistant.',
        ]);

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);

            $user->update([
                'name'      => $this->name,
                'email'     => $this->email,
                'phone'     => $this->phone ?: null,
                'user_type' => $this->staffRole,
            ]);

            $this->syncClassAssignments($user->id);
            session()->flash('success', "{$user->name}'s details updated.");
        } else {
            $tempPassword = Str::upper(Str::random(4)) . rand(10, 99) . Str::lower(Str::random(4));

            $user = User::create([
                'name'                  => $this->name,
                'email'                 => $this->email,
                'phone'                 => $this->phone ?: null,
                'password'              => Hash::make($tempPassword),
                'user_type'             => $this->staffRole,
                'is_active'             => true,
                'force_password_change' => true,
            ]);

            // Assign Spatie role so the role middleware grants portal access.
            // teaching_assistant uses the 'teacher' Spatie role (same portal, same permissions).
            $user->assignRole($this->staffRole === 'teaching_assistant' ? 'teacher' : $this->staffRole);

            $this->syncClassAssignments($user->id);
            $user->notify(new UserWelcomeNotification($user, $tempPassword));

            $roleLabel = $this->staffRole === 'teaching_assistant' ? 'Teaching Assistant' : 'Teacher';
            session()->flash('success', "{$roleLabel} {$user->name} added. Credentials sent to {$user->email}.");
        }

        $this->resetStaffForm();
    }

    /**
     * Sync form class and assistant class assignments for a staff member.
     * A staff member can hold both roles simultaneously — e.g. form teacher
     * of Primary 1 AND teaching assistant in Nursery 2.
     */
    private function syncClassAssignments(int $userId): void
    {
        // Clear all existing assignments for this user
        SchoolClass::where('form_teacher_id', $userId)
            ->update(['form_teacher_id' => null]);
        SchoolClass::where('assistant_teacher_id', $userId)
            ->update(['assistant_teacher_id' => null]);

        // Assign form class
        if ($this->formClassId) {
            SchoolClass::find($this->formClassId)?->update(['form_teacher_id' => $userId]);
        }

        // Assign assistant class (can be same class — e.g. TA and form teacher are different people)
        if ($this->assistantClassId) {
            SchoolClass::find($this->assistantClassId)?->update(['assistant_teacher_id' => $userId]);
        }
    }

    // ── Password reset ────────────────────────────────────────────────────────

    public function resetPassword(int $id): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $user = User::findOrFail($id);

        $tempPassword = Str::upper(Str::random(4)) . rand(10, 99) . Str::lower(Str::random(4));
        $user->update([
            'password'              => Hash::make($tempPassword),
            'force_password_change' => true,
        ]);

        $user->notify(new UserWelcomeNotification($user, $tempPassword));
        $this->confirmingResetId = null;
        session()->flash('success', "Password reset for {$user->name}. New credentials sent to {$user->email}.");
    }

    // ── Toggle active ─────────────────────────────────────────────────────────

    public function toggleActive(int $id): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'reactivated' : 'deactivated';
        $this->confirmingToggleId = null;
        session()->flash('success', "{$user->name}'s account has been {$status}.");
    }

    // ── Registration review ───────────────────────────────────────────────────

    public function approveRegistration(int $id): void
    {
        $reg = TeacherRegistration::findOrFail($id);
        abort_if(! $reg->isPending(), 422);

        // Check email not already taken (race condition guard)
        if (User::where('email', $reg->email)->exists()) {
            session()->flash('error', "A user account already exists for {$reg->email}.");
            return;
        }

        $tempPassword = Str::upper(Str::random(4)) . rand(10, 99) . Str::lower(Str::random(4));

        $user = User::create([
            'name'                  => $reg->name,
            'email'                 => $reg->email,
            'phone'                 => $reg->phone,
            'password'              => Hash::make($tempPassword),
            'user_type'             => $reg->role,
            'is_active'             => true,
            'force_password_change' => true,
        ]);

        // Assign Spatie role — teaching_assistant uses 'teacher' role for portal access
        $user->assignRole($reg->role === 'teaching_assistant' ? 'teacher' : $reg->role);

        $reg->update([
            'status'      => 'approved',
            'user_id'     => $user->id,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $user->notify(new UserWelcomeNotification($user, $tempPassword));

        session()->flash('success', "{$reg->name} approved. Portal credentials sent to {$reg->email}.");
    }

    public function openRejectForm(int $id): void
    {
        $this->reviewingId     = $id;
        $this->rejectionReason = '';
        $this->showRejectForm  = true;
    }

    public function submitRejection(): void
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:5|max:500',
        ], [
            'rejectionReason.required' => 'Please provide a reason for rejection.',
            'rejectionReason.min'      => 'Reason must be at least 5 characters.',
        ]);

        $reg = TeacherRegistration::findOrFail($this->reviewingId);
        abort_if(! $reg->isPending(), 422);

        $reg->update([
            'status'           => 'rejected',
            'rejection_reason' => $this->rejectionReason,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        $this->showRejectForm  = false;
        $this->reviewingId     = null;
        $this->rejectionReason = '';
        session()->flash('success', "{$reg->name}'s application has been rejected.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resetStaffForm(): void
    {
        $this->showForm         = false;
        $this->editingId        = null;
        $this->name             = '';
        $this->email            = '';
        $this->phone            = '';
        $this->staffRole        = 'teacher';
        $this->formClassId      = null;
        $this->assistantClassId = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $staff = User::with(['formClasses', 'assistantClasses'])
            ->whereIn('user_type', ['teacher', 'teaching_assistant'])
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
            )
            ->orderByRaw("FIELD(user_type,'teacher','teaching_assistant')")
            ->orderBy('name')
            ->paginate(25);

        $classes = SchoolClass::ordered()
            ->with(['formTeacher', 'assistantTeacher'])
            ->get();

        $pendingCount = TeacherRegistration::where('status', 'pending')->count();

        $registrations = $this->activeTab === 'registrations'
            ? TeacherRegistration::with('reviewer')
                ->orderByRaw("FIELD(status,'pending','approved','rejected')")
                ->orderByDesc('created_at')
                ->paginate(20)
            : collect();

        return view('livewire.admin.teacher-manager', compact(
            'staff', 'classes', 'pendingCount', 'registrations'
        ))->layout('layouts.admin', ['title' => 'Staff']);
    }
}
<?php

namespace App\Livewire\Admin;

use App\Models\SchoolClass;
use App\Models\TeacherRegistration;
use App\Models\User;
use App\Notifications\UserWelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * TeacherManager
 *
 * Two tabs:
 *
 * 1. Staff List — all teachers and TAs.
 *    - Add manually (generates temp password, sends welcome email).
 *    - Edit name / email / phone.
 *    - Assign form class + teaching assistant class independently.
 *    - Reset password.
 *    - Deactivate / reactivate.
 *
 * 2. Registrations — self-registration queue from /staff/register.
 *    - View pending applications.
 *    - Approve → creates User account OR promotes existing parent account.
 *    - Reject → records reason (not shown publicly).
 *
 * Parent-teacher promotion (Option 1):
 *   If the applicant already has a parent User account, approveRegistration()
 *   updates their user_type to teacher/teaching_assistant and assigns the
 *   correct Spatie role. Their parent role is removed. They log in with
 *   their existing password and are redirected to the teacher portal.
 *   force_password_change is NOT set — they already know their password.
 *
 * Class assignments:
 *    - form_teacher_id      → lead class teacher (one per class)
 *    - assistant_teacher_id → teaching assistant (one per class)
 *    A teacher can be form teacher of one class AND assistant of another simultaneously.
 */
class TeacherManager extends Component
{
    use WithPagination;

    // ── Tab state ─────────────────────────────────────────────────────────────
    public string $activeTab = 'staff'; // 'staff' | 'registrations'

    // ── Staff list ────────────────────────────────────────────────────────────
    public string $search = '';

    // ── Add/Edit staff form ───────────────────────────────────────────────────
    public bool   $showForm          = false;
    public ?int   $editingId         = null;
    public string $name              = '';
    public string $email             = '';
    public string $phone             = '';
    public string $staffRole         = 'teacher'; // 'teacher' | 'teaching_assistant'
    public ?int   $formClassId       = null;
    public ?int   $assistantClassId  = null;

    // ── Confirmation states ───────────────────────────────────────────────────
    public ?int   $confirmingResetId  = null;
    public ?int   $confirmingToggleId = null;

    // ── Registration review ───────────────────────────────────────────────────
    public ?int   $reviewingId        = null; // registration being approved/rejected
    public string $rejectionReason    = '';
    public bool   $showRejectForm     = false;

    // ── Livewire lifecycle ────────────────────────────────────────────────────

    public function updatedSearch(): void { $this->resetPage(); }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // ── Staff CRUD ────────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetStaffForm();
        $this->showForm  = true;
        $this->editingId = null;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        abort_if(! in_array($user->user_type, ['teacher', 'teaching_assistant']), 403);

        $formClass      = SchoolClass::where('form_teacher_id', $id)->first();
        $assistantClass = SchoolClass::where('assistant_teacher_id', $id)->first();

        $this->name             = $user->name;
        $this->email            = $user->email;
        $this->phone            = $user->phone ?? '';
        $this->staffRole        = $user->user_type;
        $this->formClassId      = $formClass?->id;
        $this->assistantClassId = $assistantClass?->id;
        $this->editingId        = $id;
        $this->showForm         = true;
    }

    public function save(): void
    {
        $uniqueEmail = $this->editingId
            ? "unique:users,email,{$this->editingId}"
            : 'unique:users,email';

        $this->validate([
            'name'             => 'required|string|min:2|max:100',
            'email'            => "required|email|{$uniqueEmail}",
            'phone'            => 'nullable|string|max:20',
            'staffRole'        => 'required|in:teacher,teaching_assistant',
            'formClassId'      => 'nullable|exists:school_classes,id',
            'assistantClassId' => 'nullable|exists:school_classes,id',
        ], [
            'email.unique'    => 'A user with this email already exists.',
            'staffRole.in'    => 'Role must be Teacher or Teaching Assistant.',
        ]);

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);

            $user->update([
                'name'      => $this->name,
                'email'     => $this->email,
                'phone'     => $this->phone ?: null,
                'user_type' => $this->staffRole,
            ]);

            $this->syncClassAssignments($user->id);
            session()->flash('success', "{$user->name}'s details updated.");
        } else {
            $tempPassword = Str::upper(Str::random(4)) . rand(10, 99) . Str::lower(Str::random(4));

            $user = User::create([
                'name'                  => $this->name,
                'email'                 => $this->email,
                'phone'                 => $this->phone ?: null,
                'password'              => Hash::make($tempPassword),
                'user_type'             => $this->staffRole,
                'is_active'             => true,
                'force_password_change' => true,
            ]);

            // Assign Spatie role so the role middleware grants portal access.
            // teaching_assistant uses the 'teacher' Spatie role (same portal, same permissions).
            $user->assignRole($this->staffRole === 'teaching_assistant' ? 'teacher' : $this->staffRole);

            $this->syncClassAssignments($user->id);
            $user->notify(new UserWelcomeNotification($user, $tempPassword));

            $roleLabel = $this->staffRole === 'teaching_assistant' ? 'Teaching Assistant' : 'Teacher';
            session()->flash('success', "{$roleLabel} {$user->name} added. Credentials sent to {$user->email}.");
        }

        $this->resetStaffForm();
    }

    /**
     * Sync form class and assistant class assignments for a staff member.
     * A staff member can hold both roles simultaneously — e.g. form teacher
     * of Primary 1 AND teaching assistant in Nursery 2.
     */
    private function syncClassAssignments(int $userId): void
    {
        // Clear all existing assignments for this user
        SchoolClass::where('form_teacher_id', $userId)
            ->update(['form_teacher_id' => null]);
        SchoolClass::where('assistant_teacher_id', $userId)
            ->update(['assistant_teacher_id' => null]);

        // Assign form class
        if ($this->formClassId) {
            SchoolClass::find($this->formClassId)?->update(['form_teacher_id' => $userId]);
        }

        // Assign assistant class (can be same class — e.g. TA and form teacher are different people)
        if ($this->assistantClassId) {
            SchoolClass::find($this->assistantClassId)?->update(['assistant_teacher_id' => $userId]);
        }
    }

    // ── Password reset ────────────────────────────────────────────────────────

    public function resetPassword(int $id): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $user = User::findOrFail($id);

        $tempPassword = Str::upper(Str::random(4)) . rand(10, 99) . Str::lower(Str::random(4));
        $user->update([
            'password'              => Hash::make($tempPassword),
            'force_password_change' => true,
        ]);

        $user->notify(new UserWelcomeNotification($user, $tempPassword));
        $this->confirmingResetId = null;
        session()->flash('success', "Password reset for {$user->name}. New credentials sent to {$user->email}.");
    }

    // ── Toggle active ─────────────────────────────────────────────────────────

    public function toggleActive(int $id): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'reactivated' : 'deactivated';
        $this->confirmingToggleId = null;
        session()->flash('success', "{$user->name}'s account has been {$status}.");
    }

    // ── Registration review ───────────────────────────────────────────────────

    public function approveRegistration(int $id): void
    {
        $reg = TeacherRegistration::findOrFail($id);
        abort_if(! $reg->isPending(), 422);

        $spatieRole = $reg->role === 'teaching_assistant' ? 'teacher' : $reg->role;
        $roleLabel  = $reg->role === 'teaching_assistant' ? 'Teaching Assistant' : 'Teacher';

        // ── Check for an existing User account with this email ────────────
        $existingUser = User::where('email', $reg->email)->first();

        if ($existingUser) {
            if ($existingUser->user_type === 'parent') {
                // ── Promote existing parent account to teacher ─────────────
                // Option 1: parent portal access is replaced by teacher access.
                // We update user_type, swap the Spatie role, and do NOT reset
                // the password — they already know it and log in as normal.
                // Their parent-linked data (ParentGuardian, students) is
                // untouched in the database; they simply can no longer access
                // the parent portal after this change.
                $existingUser->syncRoles([$spatieRole]);
                $existingUser->update(['user_type' => $reg->role]);

                $reg->update([
                    'status'      => 'approved',
                    'user_id'     => $existingUser->id,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                ]);

                session()->flash(
                    'success',
                    "{$reg->name} approved. Their existing parent account has been promoted to {$roleLabel}. They can log in with their current password."
                );
                return;
            }

            // Email belongs to a non-parent user (another teacher, admin, etc.)
            // This should not reach here because StaffRegistrationForm blocks it,
            // but we guard defensively in case of a manually-created registration.
            session()->flash('error', "A staff account already exists for {$reg->email}. Cannot approve.");
            return;
        }

        // ── No existing account — create fresh (original behaviour) ───────
        $tempPassword = Str::upper(Str::random(4)) . rand(10, 99) . Str::lower(Str::random(4));

        $user = User::create([
            'name'                  => $reg->name,
            'email'                 => $reg->email,
            'phone'                 => $reg->phone,
            'password'              => Hash::make($tempPassword),
            'user_type'             => $reg->role,
            'is_active'             => true,
            'force_password_change' => true,
        ]);

        $user->assignRole($spatieRole);

        $reg->update([
            'status'      => 'approved',
            'user_id'     => $user->id,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $user->notify(new UserWelcomeNotification($user, $tempPassword));

        session()->flash('success', "{$reg->name} approved. Portal credentials sent to {$reg->email}.");
    }

    public function openRejectForm(int $id): void
    {
        $this->reviewingId     = $id;
        $this->rejectionReason = '';
        $this->showRejectForm  = true;
    }

    public function submitRejection(): void
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:5|max:500',
        ], [
            'rejectionReason.required' => 'Please provide a reason for rejection.',
            'rejectionReason.min'      => 'Reason must be at least 5 characters.',
        ]);

        $reg = TeacherRegistration::findOrFail($this->reviewingId);
        abort_if(! $reg->isPending(), 422);

        $reg->update([
            'status'           => 'rejected',
            'rejection_reason' => $this->rejectionReason,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        $this->showRejectForm  = false;
        $this->reviewingId     = null;
        $this->rejectionReason = '';
        session()->flash('success', "{$reg->name}'s application has been rejected.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resetStaffForm(): void
    {
        $this->showForm         = false;
        $this->editingId        = null;
        $this->name             = '';
        $this->email            = '';
        $this->phone            = '';
        $this->staffRole        = 'teacher';
        $this->formClassId      = null;
        $this->assistantClassId = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $staff = User::with(['formClasses', 'assistantClasses'])
            ->whereIn('user_type', ['teacher', 'teaching_assistant'])
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
            )
            ->orderByRaw("FIELD(user_type,'teacher','teaching_assistant')")
            ->orderBy('name')
            ->paginate(25);

        $classes = SchoolClass::ordered()
            ->with(['formTeacher', 'assistantTeacher'])
            ->get();

        $pendingCount = TeacherRegistration::where('status', 'pending')->count();

        $registrations = $this->activeTab === 'registrations'
            ? TeacherRegistration::with('reviewer')
                ->orderByRaw("FIELD(status,'pending','approved','rejected')")
                ->orderByDesc('created_at')
                ->paginate(20)
            : collect();

        return view('livewire.admin.teacher-manager', compact(
            'staff', 'classes', 'pendingCount', 'registrations'
        ))->layout('layouts.admin', ['title' => 'Staff']);
    }
}
