{{-- Deploy to: resources/views/livewire/teacher/result-entry.blade.php --}}
{{-- REPLACES existing file. --}}
<div>
<style>
.pg-title { font-size:18px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:4px; }
.pg-sub   { font-size:13px; color:var(--c-text-3); margin-bottom:16px; }
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-error   { background:rgba(190,18,60,0.07); border:1px solid rgba(190,18,60,0.2); color:#BE123C; }

.selector-bar { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:14px 16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
.selector-bar label { font-size:12px; font-weight:500; color:var(--c-text-2); white-space:nowrap; }
.sel { padding:8px 10px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-family:var(--f-sans); background:var(--c-bg); color:var(--c-text-1); outline:none; -webkit-appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 8px center; padding-right:26px; min-width:140px; flex:1; }
.sel:focus    { border-color:var(--c-accent); }
.sel:disabled { opacity:0.5; }

.mode-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:rgba(180,83,9,0.08); color:#B45309; border:1px solid rgba(180,83,9,0.2); }
.submitted-banner { background:rgba(180,83,9,0.08); border:1px solid rgba(180,83,9,0.2); border-radius:var(--r-sm); padding:12px 16px; margin-bottom:14px; font-size:13px; color:#B45309; font-weight:500; }
.locked-banner    { background:rgba(190,18,60,0.07); border:1px solid rgba(190,18,60,0.2); border-radius:var(--r-sm); padding:12px 16px; margin-bottom:14px; font-size:13px; color:#BE123C; font-weight:500; }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; margin-bottom:16px; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid var(--c-border); flex-wrap:wrap; gap:6px; }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }
.panel-hint  { font-size:11px; color:var(--c-text-3); }

.score-table { width:100%; border-collapse:collapse; }
.score-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.07em; padding:9px 14px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.score-table th.center { text-align:center; }
.score-table td { padding:9px 14px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.score-table tr:last-child td { border-bottom:none; }
.score-table tr:hover td { background:#fafaf8; }

.student-name { font-weight:600; font-size:13px; }
.student-adm  { font-family:var(--f-mono); font-size:11px; color:var(--c-text-3); }

.score-input  { width:60px; padding:7px 8px; border:1px solid var(--c-border); border-radius:6px; font-size:13px; font-family:var(--f-mono); text-align:center; background:var(--c-bg); outline:none; transition:border-color 150ms; }
.score-input:focus { border-color:var(--c-accent); background:#fff; }

/* Remark dropdown */
.remark-sel { padding:7px 28px 7px 9px; border:1px solid var(--c-border); border-radius:6px; font-size:12px; font-family:var(--f-sans); background:var(--c-bg); color:var(--c-text-1); outline:none; min-width:130px;
    -webkit-appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 8px center; }
.remark-sel:focus { border-color:var(--c-accent); background:#fff; }

.eval-input { width:100%; padding:7px 10px; border:1px solid var(--c-border); border-radius:6px; font-size:12px; font-family:var(--f-sans); background:var(--c-bg); outline:none; resize:vertical; line-height:1.4; }
.eval-input:focus { border-color:var(--c-accent); background:#fff; }

.score-total { font-family:var(--f-mono); font-size:14px; font-weight:700; text-align:center; display:inline-block; min-width:36px; }
.grade-badge { display:inline-block; padding:2px 7px; border-radius:5px; font-size:11px; font-weight:700; }
.grade-Ap { background:rgba(21,128,61,0.15); color:#14532d; }
.grade-A  { background:rgba(21,128,61,0.1);  color:#15803D; }
.grade-B  { background:rgba(26,86,255,0.08); color:var(--c-accent); }
.grade-C  { background:rgba(3,105,161,0.08); color:#0369a1; }
.grade-D  { background:rgba(180,83,9,0.08);  color:#B45309; }
.grade-E  { background:rgba(190,18,60,0.08); color:var(--c-danger); }

/* Trait score select */
.trait-sel { width:64px; padding:5px 6px; border:1px solid var(--c-border); border-radius:5px; font-size:12px; font-family:var(--f-mono); text-align:center; background:var(--c-bg); outline:none; -webkit-appearance:none; }
.trait-sel:focus { border-color:var(--c-accent); background:#fff; }

/* Attendance input */
.att-input { width:70px; padding:7px 8px; border:1px solid var(--c-border); border-radius:6px; font-size:13px; font-family:var(--f-mono); text-align:center; background:var(--c-bg); outline:none; }
.att-input:focus { border-color:var(--c-accent); background:#fff; }

/* Panel action buttons */
.btn-save   { padding:8px 14px; background:var(--c-accent); color:#fff; border:none; border-radius:7px; font-size:12px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-save:hover { opacity:0.9; }
.btn-submit { padding:8px 14px; background:#15803D; color:#fff; border:none; border-radius:7px; font-size:12px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-submit:hover { opacity:0.9; }

/* Sticky save bar */
.save-bar { position:sticky; bottom:0; left:0; right:0; background:var(--c-surface); border-top:1px solid var(--c-border); padding:12px 16px; display:flex; align-items:center; justify-content:space-between; gap:10px; box-shadow:0 -4px 16px rgba(0,0,0,0.06); z-index:10; flex-wrap:wrap; }
.save-hint { font-size:11px; color:var(--c-text-3); }
.save-actions { display:flex; gap:8px; }
.btn-draft { padding:9px 16px; background:none; border:1px solid var(--c-border); color:var(--c-text-2); border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); }
.btn-draft:hover { background:var(--c-bg); }

.no-content { padding:32px 16px; text-align:center; font-size:13px; color:var(--c-text-3); }
.section-divider { border:none; border-top:1px solid var(--c-border); margin:0 0 16px; }

/* Two-column trait layout */
.traits-grid { display:grid; grid-template-columns:1fr 1fr; gap:0; }
@media(max-width:700px) { .traits-grid { grid-template-columns:1fr; } }
</style>

@if(session('success'))
    <div class="flash flash-success">&#10003; {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">&#10007; {{ session('error') }}</div>
@endif

<div class="pg-title">Enter Results</div>
<div class="pg-sub">
    @if($isRemarkOnly)
        Nursery mode — enter subject evaluations, trait scores, and attendance for each student.
    @else
        Enter CA and exam scores. Subject remarks are pre-filled from the score — override if needed.
    @endif
</div>

{{-- ── SELECTOR BAR ──────────────────────────────────────────────────────── --}}
<div class="selector-bar">
    <label>Term</label>
    <select wire:model.live="selectedTermId" class="sel">
        <option value="">Select term…</option>
        @foreach($terms as $term)
            <option value="{{ $term->id }}">{{ $term->name }} — {{ $term->session->name }}</option>
        @endforeach
    </select>

    <label>Class</label>
    <select wire:model.live="selectedClassId" class="sel" @if(!$selectedTermId) disabled @endif>
        <option value="">Select class…</option>
        @foreach($myClasses as $class)
            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
        @endforeach
    </select>

    <label>Subject</label>
    <select wire:model.live="selectedSubjectId" class="sel" @if(!$selectedClassId) disabled @endif>
        <option value="">Select subject…</option>
        @foreach($subjects as $subject)
            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
        @endforeach
    </select>

    @if($isRemarkOnly)
        <span class="mode-badge">Nursery — Remarks Only</span>
    @endif
</div>

{{-- ── LOCK / SUBMIT BANNERS ─────────────────────────────────────────────── --}}
@if($isLocked)
    <div class="locked-banner">&#128274; These results have been published by admin and are visible to parents. Contact admin if a correction is needed.</div>
@elseif(isset($isSubmitted) && $isSubmitted)
    <div class="submitted-banner">&#9989; Submitted for admin review. You can still edit and resubmit until admin publishes.</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     SECTION 1 — SUBJECT SCORES / REMARKS
     Only shown when a subject is selected.
══════════════════════════════════════════════════════════════════════════ --}}
@if($selectedSubjectId)
    @if($students->isEmpty())
        <div class="panel"><div class="no-content">No active students enrolled in this class.</div></div>
    @else
        <div class="panel">
            <div class="panel-head">
                <span class="panel-title">{{ $students->count() }} {{ Str::plural('Student', $students->count()) }}</span>
                @if(! $isRemarkOnly)
                    <span class="panel-hint">CA max: 40 &nbsp;|&nbsp; Exam max: 60 &nbsp;|&nbsp; Remark is pre-filled — override if needed.</span>
                @else
                    <span class="panel-hint">Enter the subject evaluation text and select the remark grade.</span>
                @endif
            </div>
            <div style="overflow-x:auto">
                <table class="score-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            @if($isRemarkOnly)
                                <th>Evaluation (paragraph)</th>
                                <th>Remark</th>
                            @else
                                <th class="center">CA (40)</th>
                                <th class="center">Exam (60)</th>
                                <th class="center">Total</th>
                                <th class="center">Grade</th>
                                <th>Remark</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $student)
                            @if($isRemarkOnly)
                                <tr>
                                    <td style="color:var(--c-text-3);width:28px;font-size:12px;">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="student-name">{{ $student->full_name }}</div>
                                        <div class="student-adm">{{ $student->admission_number }}</div>
                                    </td>
                                    <td>
                                        <textarea class="eval-input" rows="2"
                                            wire:model.lazy="scores.{{ $student->id }}.eval"
                                            placeholder="e.g. She can associate numbers to their given quantities…"
                                            maxlength="1000"
                                            @if($isLocked) disabled style="opacity:0.5;" @endif></textarea>
                                    </td>
                                    <td style="min-width:110px;">
                                        <select class="remark-sel"
                                            wire:model.lazy="scores.{{ $student->id }}.remark"
                                            @if($isLocked) disabled @endif>
                                            <option value="">— Select —</option>
                                            @foreach($remarkOptions as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @else
                                {{-- Primary: CA + Exam + live grade display + remark dropdown --}}
                                <tr x-data="{
                                    get ca()    { return parseInt($wire.scores['{{ $student->id }}']?.ca   || 0) },
                                    get exam()  { return parseInt($wire.scores['{{ $student->id }}']?.exam || 0) },
                                    get total() { return Math.min(100, this.ca + this.exam) },
                                    get grade() {
                                        const t = this.total;
                                        if(t>=90)return'A+'; if(t>=70)return'A'; if(t>=60)return'B';
                                        if(t>=50)return'C';  if(t>=40)return'D'; if(t>0)return'E'; return'—';
                                    },
                                    get gc() {
                                        const g = this.grade;
                                        if(g==='A+')return'grade-Ap';
                                        if(g==='—') return'';
                                        return 'grade-'+g;
                                    },
                                    get autoRemark() {
                                        const t = this.total;
                                        if(t>=90)return'Distinction'; if(t>=70)return'Excellent';
                                        if(t>=60)return'Very Good';   if(t>=50)return'Good';
                                        if(t>=40)return'Average';     if(t>0)return'Below Average';
                                        return'';
                                    }
                                }" x-init="
                                    $watch('autoRemark', val => {
                                        if($wire.scores['{{ $student->id }}']?.remark === '' || $wire.scores['{{ $student->id }}']?.remark === undefined) {
                                            $wire.scores['{{ $student->id }}'].remark = val;
                                        }
                                    })
                                ">
                                    <td style="color:var(--c-text-3);width:28px;font-size:12px;">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="student-name">{{ $student->full_name }}</div>
                                        <div class="student-adm">{{ $student->admission_number }}</div>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="number" min="0" max="40" class="score-input"
                                            wire:model.lazy="scores.{{ $student->id }}.ca" placeholder="—"
                                            @if($isLocked) disabled style="opacity:0.5;" @endif>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="number" min="0" max="60" class="score-input"
                                            wire:model.lazy="scores.{{ $student->id }}.exam" placeholder="—"
                                            @if($isLocked) disabled style="opacity:0.5;" @endif>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="score-total" :style="total>0?'color:var(--c-text-1)':'color:var(--c-text-3)'" x-text="total>0?total:'—'"></span>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="grade-badge" :class="gc" x-text="grade"></span>
                                    </td>
                                    <td>
                                        <select class="remark-sel"
                                            wire:model.lazy="scores.{{ $student->id }}.remark"
                                            @if($isLocked) disabled @endif>
                                            <option value="">Auto</option>
                                            @foreach($remarkOptions as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="save-bar">
            @if($isLocked)
                <span class="save-hint" style="color:#BE123C;font-weight:500;">&#128274; Published — contact admin to make corrections.</span>
                <div></div>
            @else
                <span class="save-hint">Drafts are not visible to parents. Edit and resubmit any time until admin publishes.</span>
                <div class="save-actions">
                    <button class="btn-draft" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">Save Draft</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                    <button class="btn-submit" wire:click="submitForReview"
                        wire:confirm="Submit for admin review? You can still edit and resubmit until admin publishes."
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submitForReview">Submit for Review</span>
                        <span wire:loading wire:target="submitForReview">Submitting…</span>
                    </button>
                </div>
            @endif
        </div>
    @endif
@elseif(! $selectedClassId)
    <div class="panel"><div class="no-content">Select a term, class, and subject to begin entering scores.</div></div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     SECTION 2 — ATTENDANCE
     Shown whenever a class is selected. Independent of subject selection.
══════════════════════════════════════════════════════════════════════════ --}}
@if($selectedClassId && $selectedTermId && $enrolmentsWithStudents->isNotEmpty())
    <hr class="section-divider" style="margin-top:24px;">
    <div class="panel">
        <div class="panel-head" style="background:rgba(26,86,255,0.03);border-bottom:1px solid rgba(26,86,255,0.12);">
            <div>
                <span class="panel-title" style="color:var(--c-accent);">Attendance</span>
                <div style="font-size:11px;color:var(--c-text-3);margin-top:2px;">
                    Enter times present and times absent for each student this term.
                    School opened: <strong>{{ Term::find($selectedTermId)?->school_days_count ?? '—' }}</strong> days.
                </div>
            </div>
            @if(! $isLocked)
                <button class="btn-save" wire:click="saveAttendance" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveAttendance">Save Attendance</span>
                    <span wire:loading wire:target="saveAttendance">Saving…</span>
                </button>
            @endif
        </div>
        <div style="overflow-x:auto;">
            <table class="score-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th class="center">Times Present</th>
                        <th class="center">Times Absent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enrolmentsWithStudents as $i => $enrolment)
                    <tr>
                        <td style="color:var(--c-text-3);width:28px;font-size:12px;">{{ $i + 1 }}</td>
                        <td>
                            <div class="student-name">{{ $enrolment->student->full_name }}</div>
                            <div class="student-adm">{{ $enrolment->student->admission_number }}</div>
                        </td>
                        <td style="text-align:center;">
                            <input type="number" min="0" max="366" class="att-input"
                                wire:model.lazy="attendance.{{ $enrolment->id }}.present"
                                placeholder="—"
                                @if($isLocked) disabled style="opacity:0.5;" @endif>
                        </td>
                        <td style="text-align:center;">
                            <input type="number" min="0" max="366" class="att-input"
                                wire:model.lazy="attendance.{{ $enrolment->id }}.absent"
                                placeholder="—"
                                @if($isLocked) disabled style="opacity:0.5;" @endif>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     SECTION 3 — TRAIT SCORES (Psychomotor + Affective)
     Shown whenever a class is selected.
══════════════════════════════════════════════════════════════════════════ --}}
@if($selectedClassId && $selectedTermId && $students->isNotEmpty())
    <div class="panel">
        <div class="panel-head" style="background:rgba(21,128,61,0.03);border-bottom:1px solid rgba(21,128,61,0.15);">
            <div>
                <span class="panel-title" style="color:#15803D;">Psychomotor &amp; Affective Scores</span>
                <div style="font-size:11px;color:var(--c-text-3);margin-top:2px;">
                    Rate each student 1–5 per trait. Leave blank if not assessed this term.
                    @if($isRemarkOnly)
                        (1=Poor, 2=Fair, 3=Good, 4=Very Good, 5=Excellent)
                    @else
                        (1=Not Applicable, 2=Poor, 3=Fair, 4=Good, 5=Very Good)
                    @endif
                </div>
            </div>
            @if(! $isLocked)
                <button class="btn-save" style="background:#15803D;" wire:click="saveTraitScores" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveTraitScores">Save Traits</span>
                    <span wire:loading wire:target="saveTraitScores">Saving…</span>
                </button>
            @endif
        </div>

        @foreach($students as $i => $student)
            <div style="padding:12px 16px; {{ ! $loop->last ? 'border-bottom:1px solid var(--c-border);' : '' }}">
                <div style="font-weight:600;font-size:13px;margin-bottom:8px;">
                    {{ $i + 1 }}. {{ $student->full_name }}
                    <span style="font-family:var(--f-mono);font-size:11px;color:var(--c-text-3);font-weight:400;margin-left:8px;">{{ $student->admission_number }}</span>
                </div>
                <div class="traits-grid">
                    {{-- Psychomotor --}}
                    <div style="padding-right:12px;">
                        <div style="font-size:10px;font-weight:700;color:var(--c-text-3);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Psychomotor Skills</div>
                        @foreach($psychomotorDef as $key => $label)
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                                <span style="font-size:12px;color:var(--c-text-2);">{{ $label }}</span>
                                <select class="trait-sel"
                                    wire:model.lazy="traitScores.{{ $student->id }}.{{ $key }}"
                                    @if($isLocked) disabled @endif>
                                    <option value="">—</option>
                                    @for($s = 1; $s <= 5; $s++)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endfor
                                </select>
                            </div>
                        @endforeach
                    </div>
                    {{-- Affective --}}
                    <div style="padding-left:12px;border-left:1px solid var(--c-border);">
                        <div style="font-size:10px;font-weight:700;color:var(--c-text-3);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Affective Areas</div>
                        @foreach($affectiveDef as $key => $label)
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                                <span style="font-size:12px;color:var(--c-text-2);">{{ $label }}</span>
                                <select class="trait-sel"
                                    wire:model.lazy="traitScores.{{ $student->id }}.{{ $key }}"
                                    @if($isLocked) disabled @endif>
                                    <option value="">—</option>
                                    @for($s = 1; $s <= 5; $s++)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endfor
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     SECTION 4 — TEACHER GENERAL COMMENTS
══════════════════════════════════════════════════════════════════════════ --}}
@if($selectedClassId && $selectedTermId && $students->isNotEmpty())
    <div class="panel">
        <div class="panel-head" style="background:rgba(26,86,255,0.03);border-bottom:1px solid rgba(26,86,255,0.12);">
            <div>
                <span class="panel-title" style="color:var(--c-accent);">Your General Comments</span>
                <div style="font-size:11px;color:var(--c-text-3);margin-top:2px;">One overall comment per student — printed on the report card. Not subject-specific.</div>
            </div>
            @if(! $isLocked)
                <div style="display:flex;gap:6px;">
                    <button class="btn-save" wire:click="saveTeacherComments" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveTeacherComments">Save Draft</span>
                        <span wire:loading wire:target="saveTeacherComments">Saving…</span>
                    </button>
                    <button class="btn-submit" wire:click="submitTeacherComments"
                        wire:confirm="Submit comments for admin review?"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submitTeacherComments">Submit</span>
                        <span wire:loading wire:target="submitTeacherComments">Submitting…</span>
                    </button>
                </div>
            @endif
        </div>
        <table class="score-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Your General Comment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $student)
                    <tr>
                        <td style="color:var(--c-text-3);width:28px;font-size:12px;">{{ $i + 1 }}</td>
                        <td>
                            <div class="student-name">{{ $student->full_name }}</div>
                            <div class="student-adm">{{ $student->admission_number }}</div>
                        </td>
                        <td>
                            <textarea class="eval-input" rows="2"
                                wire:model.lazy="teacherComments.{{ $student->id }}"
                                placeholder="e.g. Adaeze is a bright and enthusiastic learner…"
                                maxlength="500"
                                @if($isLocked) disabled style="opacity:0.5;" @endif></textarea>
                            @error("teacherComments.{$student->id}")
                                <div style="font-size:10px;color:var(--c-danger);margin-top:2px;">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

</div>
