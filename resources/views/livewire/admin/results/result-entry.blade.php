{{-- Deploy to: resources/views/livewire/admin/results/result-entry.blade.php --}}

<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pg-title { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub { font-size:13px; color:var(--c-text-3); margin-top:2px; }

.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-error { background:rgba(190,18,60,0.08); border:1px solid rgba(190,18,60,0.2); color:#BE123C; }

.selector-bar {
background:var(--c-surface); border:1px solid var(--c-border);
border-radius:var(--r-md); padding:16px 18px;
display:flex; align-items:center; gap:12px;
flex-wrap:wrap; margin-bottom:20px;
}
.selector-bar label { font-size:12px; font-weight:500; color:var(--c-text-2); white-space:nowrap; }
.sel {
padding:8px 12px; border:1px solid var(--c-border); border-radius:8px;
font-size:13px; font-family:var(--f-sans); background:var(--c-bg);
color:var(--c-text-1); outline:none; -webkit-appearance:none;
background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
background-repeat:no-repeat; background-position:right 8px center; padding-right:28px;
min-width:150px;
}
.sel:focus { border-color:var(--c-accent); }
.sel:disabled { opacity:0.5; cursor:not-allowed; }

.mode-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:rgba(180,83,9,0.08); color:#B45309; border:1px solid rgba(180,83,9,0.2); }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; margin-bottom:16px; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); flex-wrap:wrap; gap:8px; }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }
.panel-hint { font-size:11px; color:var(--c-text-3); }

.score-table { width:100%; border-collapse:collapse; }
.score-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; padding:10px 16px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); white-space:nowrap; }
.score-table th.center { text-align:center; }
.score-table td { padding:10px 16px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.score-table tr:last-child td { border-bottom:none; }
.score-table tr:hover td { background:#fafaf8; }

.student-name { font-weight:600; font-size:13px; color:var(--c-text-1); }
.student-adm { font-family:var(--f-mono); font-size:11px; color:var(--c-text-3); margin-top:1px; }

.score-input { width:64px; padding:7px 8px; border:1px solid var(--c-border); border-radius:6px; font-size:13px; font-family:var(--f-mono); text-align:center; background:var(--c-bg); color:var(--c-text-1); outline:none; transition:border-color 150ms; }
.score-input:focus { border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 2px rgba(26,86,255,0.08); }

.remark-input { width:100%; min-width:220px; padding:7px 10px; border:1px solid var(--c-border); border-radius:6px; font-size:13px; font-family:var(--f-sans); background:var(--c-bg); color:var(--c-text-1); outline:none; resize:vertical; line-height:1.4; transition:border-color 150ms; }
.remark-input:focus { border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 2px rgba(26,86,255,0.08); }

.comment-input { width:100%; padding:7px 10px; border:1px solid var(--c-border); border-radius:6px; font-size:13px; font-family:var(--f-sans); background:var(--c-bg); color:var(--c-text-1); outline:none; resize:vertical; line-height:1.4; transition:border-color 150ms; min-width:250px; }
.comment-input:focus { border-color:#15803D; background:#fff; box-shadow:0 0 0 2px rgba(21,128,61,0.08); }

.score-total { font-family:var(--f-mono); font-size:14px; font-weight:700; text-align:center; min-width:40px; display:inline-block; }
.grade-badge { display:inline-block; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:700; text-align:center; min-width:28px; }
.grade-A { background:rgba(21,128,61,0.1); color:#15803D; }
.grade-B { background:rgba(26,86,255,0.08); color:var(--c-accent); }
.grade-C { background:rgba(180,83,9,0.08); color:#B45309; }
.grade-D { background:rgba(180,83,9,0.06); color:#B45309; }
.grade-E { background:rgba(190,18,60,0.06); color:var(--c-danger); }
.grade-F { background:rgba(190,18,60,0.1); color:var(--c-danger); }
.grade- { background:var(--c-bg); color:var(--c-text-3); }

/* Head teacher comments panel */
.comments-panel-head { background:rgba(21,128,61,0.04); border-bottom:1px solid rgba(21,128,61,0.15); }
.comments-panel-head .panel-title { color:#15803D; }
.btn-save-comments { padding:9px 18px; background:#15803D; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-save-comments:hover { opacity:0.9; }

.save-bar { position:sticky; bottom:0; left:0; right:0; background:var(--c-surface); border-top:1px solid var(--c-border); padding:14px 24px; display:flex; align-items:center; justify-content:space-between; gap:10px; box-shadow:0 -4px 20px rgba(0,0,0,0.06); z-index:10; flex-wrap:wrap; }
.save-hint { font-size:12px; color:var(--c-text-3); }
.save-actions { display:flex; gap:8px; }
.btn-save { padding:10px 20px; background:var(--c-accent); color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-save:hover { opacity:0.9; }
.btn-publish { padding:10px 20px; background:#15803D; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-publish:hover { opacity:0.9; }

.no-content { padding:40px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
.section-divider { border:none; border-top:1px solid var(--c-border); margin:0 0 16px; }
</style>

@if(session('success'))
<div class="flash flash-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="flash flash-error">⚠ {{ session('error') }}</div>
@endif

<div class="pg-header">
<div>
<h1 class="pg-title">Results Entry</h1>
<p class="pg-sub">
@if($isRemarkOnly)
Nursery mode — enter a detailed assessment and select a remark per subject, plus a general comment per student.
@else
Enter CA and exam scores, then add a general comment per student.
@endif
</p>
</div>
<a href="{{ route('admin.results.overview') }}"
style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border:1px solid var(--c-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--c-text-2);text-decoration:none;background:var(--c-surface);">
Results Overview →
</a>
</div>

{{-- Selector bar --}}
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
@foreach($classes as $class)
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
<span class="mode-badge">
<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l2 2-8 8H4v-2L12 2z"/></svg>
Nursery — Remarks Only
</span>
@endif
</div>

@if(! $selectedSubjectId && ! $selectedClassId)
<div class="panel">
<div class="no-content">Select a term, class, and subject above to begin entering results.</div>
</div>
@else

{{-- ── Subject scores / remarks panel ── --}}
@if($selectedSubjectId)
@if($students->isEmpty())
<div class="panel">
<div class="no-content">No active students enrolled in this class for the selected term.</div>
</div>
@else
@if($isPublished && ! $confirmingOverwrite)
<div style="background:rgba(21,128,61,0.07);border:1px solid rgba(21,128,61,0.2);border-radius:var(--r-sm);padding:12px 16px;margin-bottom:12px;font-size:13px;color:#15803D;font-weight:500;">
✓ These results are published and visible to parents. Click "Edit Published Results" below to make changes.
</div>
@endif

<div class="panel">
<div class="panel-head">
<span class="panel-title">{{ $students->count() }} {{ Str::plural('student', $students->count()) }}</span>
@if(! $isRemarkOnly)
<span class="panel-hint">CA max: 40 &nbsp;|&nbsp; Exam max: 60 &nbsp;|&nbsp; Remark auto-fills from score — override if needed.</span>
@else
<span class="panel-hint">Enter detailed assessment and select a remark for each student.</span>
@endif
</div>

<div style="overflow-x:auto">
<table class="score-table">
<thead>
<tr>
<th>#</th>
<th>Student</th>
@if($isRemarkOnly)
<th>Detailed Assessment</th>
<th>Teacher's Remark</th>
@else
<th class="center">CA <span style="font-weight:400;text-transform:none;">(/ 40)</span></th>
<th class="center">Exam <span style="font-weight:400;text-transform:none;">(/ 60)</span></th>
<th class="center">Total</th>
<th class="center">Grade</th>
<th class="center">Remark</th>
@endif
</tr>
</thead>
<tbody>
@foreach($students as $i => $student)
@if($isRemarkOnly)
<tr>
<td style="color:var(--c-text-3);font-size:12px;width:36px;">{{ $i + 1 }}</td>
<td>
<div class="student-name">{{ $student->full_name }}</div>
<div class="student-adm">{{ $student->admission_number }}</div>
</td>
<td>
<textarea class="remark-input" rows="2"
wire:model.lazy="scores.{{ $student->id }}.admin_comment"
placeholder="e.g. Shows good understanding of numbers and can count to 20..."
maxlength="500"></textarea>
@error("scores.{$student->id}.admin_comment")
<div style="font-size:10px;color:var(--c-danger);margin-top:2px;">{{ $message }}</div>
@enderror
</td>
<td>
<select class="sel" style="min-width:140px;font-size:12px;"
wire:model.lazy="scores.{{ $student->id }}.remark">
<option value="">— Select —</option>
<option value="Excellent">Excellent</option>
<option value="Very Good">Very Good</option>
<option value="Good">Good</option>
<option value="Fair">Fair</option>
<option value="Needs Improvement">Needs Improvement</option>
</select>
@error("scores.{$student->id}.remark")
<div style="font-size:10px;color:var(--c-danger);margin-top:2px;">{{ $message }}</div>
@enderror
</td>
</tr>
@else
<tr x-data="{
get ca() { return parseInt($wire.scores['{{ $student->id }}']?.ca || 0) },
get exam() { return parseInt($wire.scores['{{ $student->id }}']?.exam || 0) },
get total() { return Math.min(100, this.ca + this.exam) },
get grade() {
const t = this.total;
if(t>=90)return'A+'; if(t>=70)return'A'; if(t>=60)return'B';
if(t>=50)return'C'; if(t>=40)return'D'; if(t>0)return'E'; return'—';
},
get gc() {
const g = this.grade;
if(g==='A+')return'grade-A'; if(g==='—')return'';
return 'grade-'+g;
},
remarkFor(t) {
if(t>=90)return'Distinction'; if(t>=70)return'Excellent';
if(t>=60)return'Very Good'; if(t>=50)return'Good';
if(t>=40)return'Average'; if(t>0) return'Below Average';
return'';
}
}" x-init="
$nextTick(() => {
const existing = $wire.scores['{{ $student->id }}']?.remark;
if (!existing && this.total > 0) {
$wire.scores['{{ $student->id }}'].remark = this.remarkFor(this.total);
}
});
$watch('total', t => {
if (t > 0) {
$wire.scores['{{ $student->id }}'].remark = this.remarkFor(t);
}
});
">
<td style="color:var(--c-text-3);font-size:12px;width:36px;">{{ $i + 1 }}</td>
<td>
<div class="student-name">{{ $student->full_name }}</div>
<div class="student-adm">{{ $student->admission_number }}</div>
</td>
<td style="text-align:center">
<input type="number" min="0" max="40" class="score-input"
wire:model.lazy="scores.{{ $student->id }}.ca" placeholder="—">
</td>
<td style="text-align:center">
<input type="number" min="0" max="60" class="score-input"
wire:model.lazy="scores.{{ $student->id }}.exam" placeholder="—">
</td>
<td style="text-align:center">
<span class="score-total" :style="total>0?'color:var(--c-text-1)':'color:var(--c-text-3)'" x-text="total>0?total:'—'"></span>
</td>
<td style="text-align:center">
<span class="grade-badge" :class="gc" x-text="grade"></span>
</td>
<td>
<select class="sel" style="min-width:120px;font-size:12px;"
wire:model.lazy="scores.{{ $student->id }}.remark">
<option value="">— Auto —</option>
@foreach($remarkOptions as $opt)
<option value="{{ $opt }}">{{ $opt }}</option>
@endforeach
</select>
@error("scores.{$student->id}.remark")
<div style="font-size:10px;color:var(--c-danger);margin-top:2px;">{{ $message }}</div>
@enderror
</td>
</tr>
@endif
@endforeach
</tbody>
</table>
</div>
</div>

{{-- Sticky save bar for scores --}}
<div class="save-bar">
@if($isPublished && ! $confirmingOverwrite)
<span style="font-size:13px;font-weight:600;color:#15803D;">✓ Published — visible to parents</span>
<div class="save-actions">
<button class="btn-save" style="background:none;border:1px solid var(--c-border);color:var(--c-text-2);"
wire:click="unpublish"
wire:confirm="Unpublish these results? Parents will no longer see them.">
Unpublish
</button>
<button class="btn-publish" wire:click="requestEdit">Edit Published Results</button>
</div>
@elseif($confirmingOverwrite)
<span style="font-size:13px;color:#B45309;font-weight:500;">⚠️ You are editing results that parents can currently see.</span>
<div class="save-actions">
<button class="btn-save" style="background:none;border:1px solid var(--c-border);color:var(--c-text-2);" wire:click="$set('confirmingOverwrite', false)">Cancel</button>
<button class="btn-save" wire:click="save" wire:loading.attr="disabled">
<span wire:loading.remove wire:target="save">Save Changes</span>
<span wire:loading wire:target="save">Saving…</span>
</button>
<button class="btn-publish" wire:click="saveAndPublish" wire:loading.attr="disabled">
<span wire:loading.remove wire:target="saveAndPublish">Save & Re-publish</span>
<span wire:loading wire:target="saveAndPublish">Saving…</span>
</button>
</div>
@else
<span class="save-hint">
{{ $isRemarkOnly ? 'Assessments and remarks are not saved until you click Save.' : 'Scores are not saved until you click Save.' }}
Publishing makes results visible to parents.
</span>
<div class="save-actions">
<button class="btn-save" wire:click="save" wire:loading.attr="disabled">
<span wire:loading.remove wire:target="save">Save Draft</span>
<span wire:loading wire:target="save">Saving…</span>
</button>
<button class="btn-publish" wire:click="saveAndPublish"
wire:confirm="Publish results? Parents will be able to see these in their portal."
wire:loading.attr="disabled">
<span wire:loading.remove wire:target="saveAndPublish">Save & Publish</span>
<span wire:loading wire:target="saveAndPublish">Publishing…</span>
</button>
</div>
@endif
</div>
@endif
@endif

{{-- ── Head Teacher General Comments — shown whenever a class is selected ── --}}
@if($selectedClassId && $selectedTermId && $students->isNotEmpty())
<hr class="section-divider" style="margin-top:24px;">

<div class="panel">
<div class="panel-head comments-panel-head">
<div>
<span class="panel-title" style="color:#15803D;">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
Head Teacher's General Comments
</span>
<div style="font-size:11px;color:var(--c-text-3);margin-top:2px;">
One overall comment per student — appears on the report card below the results table.
</div>
</div>
<button class="btn-save-comments"
wire:click="saveHeadComments"
wire:loading.attr="disabled"
wire:loading.class="opacity-50">
<span wire:loading.remove wire:target="saveHeadComments">Save Comments</span>
<span wire:loading wire:target="saveHeadComments">Saving…</span>
</button>
</div>

<table class="score-table">
<thead>
<tr>
<th>#</th>
<th>Student</th>
<th>Head Teacher's Comment</th>
</tr>
</thead>
<tbody>
@foreach($students as $i => $student)
<tr>
<td style="color:var(--c-text-3);font-size:12px;width:36px;">{{ $i + 1 }}</td>
<td>
<div class="student-name">{{ $student->full_name }}</div>
<div class="student-adm">{{ $student->admission_number }}</div>
</td>
<td>
<textarea class="comment-input" rows="2"
wire:model.lazy="headComments.{{ $student->id }}"
placeholder="e.g. A focused and determined learner. Keep it up!"
maxlength="500"></textarea>
@error("headComments.{$student->id}")
<div style="font-size:10px;color:var(--c-danger);margin-top:2px;">{{ $message }}</div>
@enderror
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
@endif

@endif
</div>
