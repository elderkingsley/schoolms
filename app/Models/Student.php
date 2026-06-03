<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Student extends Model
{
    protected $fillable = [
        'admission_number', 'first_name', 'last_name', 'other_name',
        'gender', 'date_of_birth', 'photo', 'status', 'notes',
        'class_applied_for', 'medical_notes', 'approved_at', 'approved_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'approved_at' => 'datetime',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            ParentGuardian::class,
            'parent_student',
            'student_id',
            'parent_id'
        )->withPivot(['relationship', 'is_primary_contact'])
            ->withTimestamps();
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function feeInvoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function currentEnrolment(): HasOne
    {
        $activeSession = AcademicSession::current();

        return $this->hasOne(Enrolment::class)
            ->where('academic_session_id', optional($activeSession)->id);
    }

    /**
     * All parent records that belong to this student's billing family.
     *
     * A family is treated as every parent linked to this student and every
     * parent linked to those parents' other children. This keeps siblings on
     * one deterministic virtual account even if their parent relation order
     * differs.
     */
    public function familyParentsForBilling(): Collection
    {
        $directParentIds = $this->parents()
            ->pluck('parents.id')
            ->unique()
            ->values();

        if ($directParentIds->isEmpty()) {
            return collect();
        }

        $familyStudentIds = DB::table('parent_student')
            ->whereIn('parent_id', $directParentIds)
            ->pluck('student_id')
            ->unique()
            ->values();

        return ParentGuardian::query()
            ->whereHas('students', fn ($query) => $query->whereIn('students.id', $familyStudentIds))
            ->with('user')
            ->get()
            ->unique('id')
            ->values();
    }

    public function billingParent(bool $requireAccount = false): ?ParentGuardian
    {
        $provider = ParentGuardian::getActiveWalletProvider();

        $parents = $this->familyParentsForBilling()
            ->filter(fn (ParentGuardian $parent) => $parent->user !== null);

        if ($parents->isEmpty()) {
            return null;
        }

        $ranked = $parents->sortBy(function (ParentGuardian $parent) use ($provider) {
            return sprintf(
                '%d-%d-%d-%010d',
                $parent->hasProviderAccount($provider) ? 0 : 1,
                ! empty($parent->active_account_number) ? 0 : 1,
                $parent->wallet_status === 'active' ? 0 : 1,
                $parent->id
            );
        })->values();

        if ($requireAccount) {
            return $ranked->first(fn (ParentGuardian $parent) => ! empty($parent->active_account_number));
        }

        return $ranked->first();
    }

    public function billingAccountNumbers(): array
    {
        return $this->familyParentsForBilling()
            ->flatMap(fn (ParentGuardian $parent) => [
                $parent->juicyway_account_number,
                $parent->budpay_account_number,
                $parent->korapay_account_number,
            ])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
