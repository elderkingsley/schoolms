<?php

namespace App\Livewire\Public;

use App\Mail\EnrolmentConfirmationMail;
use App\Models\ParentGuardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Notifications\AdminNewEnrolmentNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class EnrolmentForm extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public int $totalSteps = 4;

    // ── Student details (Step 1) ──
    public string $student_first_name   = '';
    public string $student_last_name    = '';
    public string $student_other_name   = '';
    public string $student_gender       = '';
    public string $student_dob          = '';
    public string $class_applied_for    = '';
    public string $medical_notes        = '';
    public $student_photo               = null; // uploaded file

    // ── Primary parent (Step 2) ──
    public string $parent1_name         = '';
    public string $parent1_email        = '';
    public string $parent1_phone        = '';
    public string $parent1_address      = '';
    public string $parent1_relationship = 'Mother';
    public string $parent1_occupation   = '';

    // ── Second parent (Step 3) — optional ──
    public bool   $has_second_parent    = false;
    public string $parent2_name         = '';
    public string $parent2_email        = '';
    public string $parent2_phone        = '';
    public string $parent2_relationship = 'Father';
    public string $parent2_occupation   = '';

    // ── Emergency contact (Step 3) ──
    public string $emergency_name         = '';
    public string $emergency_phone        = '';
    public string $emergency_relationship = '';

    public bool $submitted = false;

    protected function stepRules(): array
    {
        return [
            1 => [
                'student_first_name' => 'required|string|min:2|max:100',
                'student_last_name'  => 'required|string|min:2|max:100',
                'student_gender'     => 'required|in:Male,Female',
                'student_dob'        => 'required|date|before:today',
                'class_applied_for'  => 'required|exists:school_classes,name',
                'student_photo'      => 'nullable|image|max:2048', // 2MB max
            ],
            2 => [
                'parent1_name'         => 'required|string|min:2|max:150',
                'parent1_email'        => 'required|email|max:200',
                'parent1_phone'        => 'required|string|min:10|max:20',
                'parent1_address'      => 'required|string|min:5|max:300',
                'parent1_relationship' => 'required|string',
            ],
            3 => [
                'emergency_name'         => 'required|string|min:2|max:150',
                'emergency_phone'        => 'required|string|min:10|max:20',
                'emergency_relationship' => 'required|string|min:2',
            ],
        ];
    }

    public function nextStep(): void
    {
        $rules = $this->stepRules()[$this->step] ?? [];
        $this->validate($rules);

        if ($this->step === 3 && $this->has_second_parent) {
            $this->validate([
                'parent2_name'         => 'required|string|min:2|max:150',
                'parent2_email'        => 'required|email|max:200|different:parent1_email',
                'parent2_phone'        => 'required|string|min:10|max:20',
                'parent2_relationship' => 'required|string',
            ]);
        }

        $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function submit(): void
    {
        $this->validate([
            'student_first_name' => 'required',
            'student_last_name'  => 'required',
            'parent1_email'      => 'required|email',
            'student_photo'      => 'nullable|image|max:2048',
        ]);

        $parentEmail  = $this->parent1_email;
        $parentName   = $this->parent1_name;
        $studentFirst = $this->student_first_name;
        $studentLast  = $this->student_last_name;

        // Store photo before transaction
        $photoPath = null;
        if ($this->student_photo) {
            $photoPath = $this->student_photo->store('student-photos', 'public');
        }

        DB::transaction(function () use ($studentFirst, $studentLast, $parentEmail, $parentName, $photoPath) {

            $student = Student::create([
                'admission_number'  => 'TEMP-' . strtoupper(Str::random(8)),
                'first_name'        => trim($studentFirst),
                'last_name'         => trim($studentLast),
                'other_name'        => trim($this->student_other_name),
                'gender'            => $this->student_gender,
                'date_of_birth'     => $this->student_dob,
                'status'            => 'pending',
                'class_applied_for' => $this->class_applied_for,
                'medical_notes'     => trim($this->medical_notes),
                'photo'             => $photoPath,
            ]);

            // ── Parent 1 ──────────────────────────────────────────────────
            // If a parent with this email already exists (i.e. they have
            // another child already enrolled), reuse that parent row instead
            // of creating a duplicate. Only create a new row if no match found.
            $parent1 = $this->findOrCreateParent(
                email:        $parentEmail,
                name:         $parentName,
                phone:        $this->parent1_phone,
                address:      $this->parent1_address,
                occupation:   $this->parent1_occupation,
                relationship: $this->parent1_relationship,
                emergencyName:         $this->emergency_name,
                emergencyPhone:        $this->emergency_phone,
                emergencyRelationship: $this->emergency_relationship,
            );

            // Only attach if not already linked to this student
            if (! $student->parents()->where('parents.id', $parent1->id)->exists()) {
                $student->parents()->attach($parent1->id, [
                    'relationship'       => $this->parent1_relationship,
                    'is_primary_contact' => true,
                ]);
            }

            // ── Parent 2 (optional) ───────────────────────────────────────
            if ($this->has_second_parent && $this->parent2_name) {
                $parent2 = $this->findOrCreateParent(
                    email:        $this->parent2_email,
                    name:         $this->parent2_name,
                    phone:        $this->parent2_phone,
                    address:      null,
                    occupation:   $this->parent2_occupation,
                    relationship: $this->parent2_relationship,
                );

                if (! $student->parents()->where('parents.id', $parent2->id)->exists()) {
                    $student->parents()->attach($parent2->id, [
                        'relationship'       => $this->parent2_relationship,
                        'is_primary_contact' => false,
                    ]);
                }
            }

            User::whereIn('user_type', ['super_admin', 'admin'])
                ->get()
                ->each(function ($admin) use ($student) {
                    $admin->notify(new AdminNewEnrolmentNotification($student));
                });
        });

        try {
            Mail::to($parentEmail)->send(
                new EnrolmentConfirmationMail($studentFirst, $studentLast, $parentName)
            );
        } catch (\Exception $e) {
            Log::error('Enrolment confirmation mail failed', [
                'email'   => $parentEmail,
                'student' => "{$studentFirst} {$studentLast}",
                'error'   => $e->getMessage(),
            ]);
        }

        $this->submitted = true;
    }

    /**
     * Find an existing parent by email or create a new one.
     *
     * Checks both the _temp_email field (for parents not yet approved)
     * and the user's email (for parents who already have a portal account).
     * This prevents duplicate parent rows when a family enrols a second child.
     */
    private function findOrCreateParent(
        string  $email,
        string  $name,
        ?string $phone        = null,
        ?string $address      = null,
        ?string $occupation   = null,
        ?string $relationship = null,
        ?string $emergencyName         = null,
        ?string $emergencyPhone        = null,
        ?string $emergencyRelationship = null,
    ): \App\Models\ParentGuardian {
        $email = trim(strtolower($email));

        // Check if a parent already exists via their user account email
        $existing = \App\Models\ParentGuardian::whereHas('user', fn($q) =>
            $q->whereRaw('LOWER(email) = ?', [$email])
        )->first();

        // Also check _temp_email for parents pending approval
        if (! $existing) {
            $existing = \App\Models\ParentGuardian::whereRaw('LOWER(_temp_email) = ?', [$email])
                ->first();
        }

        if ($existing) {
            return $existing;
        }

        // No existing parent — create a new row
        return \App\Models\ParentGuardian::create([
            'user_id'                        => null,
            'phone'                          => $phone,
            'address'                        => $address,
            'occupation'                     => $occupation,
            'relationship'                   => $relationship,
            'emergency_contact_name'         => $emergencyName,
            'emergency_contact_phone'        => $emergencyPhone,
            'emergency_contact_relationship' => $emergencyRelationship,
            '_temp_name'                     => $name,
            '_temp_email'                    => $email,
        ]);
    }

    public function render()
    {
        return view('livewire.public.enrolment-form', [
            'classes' => SchoolClass::orderBy('order')->orderBy('name')->get()->unique('name')->pluck('name')->values(),
        ])->layout('layouts.public', ['title' => 'Student Enrolment']);
    }
}
