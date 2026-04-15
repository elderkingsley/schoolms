<?php
// Deploy to: app/Models/StudentTraitScore.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores a single psychomotor or affective rating for one student in one term.
 *
 * See migration 2026_04_15_100003 for full design rationale.
 *
 * Usage example in a controller or Livewire component:
 *
 *   // Get all scores for one student as a keyed array
 *   $scores = StudentTraitScore::forStudentTerm($student->id, $termId);
 *   // → ['handwriting' => 4, 'punctuality' => 5, 'games_sport' => null, ...]
 *
 *   // Save a batch submitted from a form
 *   StudentTraitScore::saveBatch($student->id, $termId, $formArray);
 */
class StudentTraitScore extends Model
{
    protected $fillable = ['student_id', 'term_id', 'trait_key', 'score'];
    protected $casts    = ['score' => 'integer'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function term(): BelongsTo   { return $this->belongsTo(Term::class); }

    // ── Trait definitions ─────────────────────────────────────────────────────

    /** Psychomotor traits — identical for Primary and Preschool */
    public const PSYCHOMOTOR = [
        'handwriting'      => 'Handwriting',
        'verbal_fluency'   => 'Verbal Fluency',
        'games_sport'      => 'Games & Sport',
        'handling_tools'   => 'Handling Tools',
        'drawing_painting' => 'Drawing & Painting',
        'musical_skills'   => 'Musical Skills',
    ];

    /** Affective traits for Primary classes */
    public const AFFECTIVE_PRIMARY = [
        'punctuality'             => 'Punctuality',
        'neatness'                => 'Neatness',
        'politeness'              => 'Politeness',
        'honesty'                 => 'Honesty',
        'leadership'              => 'Leadership',
        'helping_others'          => 'Helping Others',
        'emotional_stability'     => 'Emotional Stability',
        'health'                  => 'Health',
        'attitude_to_school_work' => 'Attitude To School Work',
        'attentiveness'           => 'Attentiveness',
        'perseverance'            => 'Perseverance',
    ];

    /** Affective traits for Preschool/Nursery — adds 'cooperation_with_others' */
    public const AFFECTIVE_PRESCHOOL = [
        'punctuality'             => 'Punctuality',
        'neatness'                => 'Neatness',
        'politeness'              => 'Politeness',
        'honesty'                 => 'Honesty',
        'cooperation_with_others' => 'Cooperation With Others',
        'leadership'              => 'Leadership',
        'helping_others'          => 'Helping Others',
        'emotional_stability'     => 'Emotional Stability',
        'health'                  => 'Health',
        'attitude_to_school_work' => 'Attitude To School Work',
        'attentiveness'           => 'Attentiveness',
        'perseverance'            => 'Perseverance',
    ];

    /**
     * Returns the full merged trait list (psychomotor + affective) for a class type.
     *
     * @param bool $isPreschool  Pass SchoolClass::isRemarkOnly() result here.
     * @return array ['trait_key' => 'Display Label', ...]
     */
    public static function allKeysFor(bool $isPreschool): array
    {
        $affective = $isPreschool ? self::AFFECTIVE_PRESCHOOL : self::AFFECTIVE_PRIMARY;
        return array_merge(self::PSYCHOMOTOR, $affective);
    }

    // ── Query helpers ─────────────────────────────────────────────────────────

    /**
     * Returns a flat array keyed by trait_key with integer scores.
     * Missing traits are absent from the array (not null-padded).
     */
    public static function forStudentTerm(int $studentId, int $termId): array
    {
        return self::where('student_id', $studentId)
            ->where('term_id', $termId)
            ->pluck('score', 'trait_key')
            ->toArray();
    }

    /**
     * Upserts a batch of trait scores for one student in one term.
     *
     * @param array $scores  ['handwriting' => 4, 'punctuality' => 5, ...]
     *                       Empty string or null values stored as null (not rated).
     */
    public static function saveBatch(int $studentId, int $termId, array $scores): void
    {
        foreach ($scores as $key => $value) {
            $parsed = ($value !== null && $value !== '') ? (int) $value : null;

            self::updateOrCreate(
                ['student_id' => $studentId, 'term_id' => $termId, 'trait_key' => $key],
                ['score' => $parsed]
            );
        }
    }
}
