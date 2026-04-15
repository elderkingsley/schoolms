{{-- Deploy to: resources/views/pdf/report-card-primary.blade.php --}}
{{-- Used for: All non-remark-only classes (Grade 1, Grade 2, etc.) --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'DejaVu Sans', Arial, sans-serif; font-size:10.5px; color:#111; background:#fff; padding:24px 28px; }

/* ── Header ── */
.header-table { width:100%; border-collapse:collapse; margin-bottom:14px; border-bottom:3px solid #1A1A2E; padding-bottom:12px; }
.header-table td { vertical-align:middle; padding-bottom:12px; }
.school-name { font-size:20px; font-weight:700; color:#1A1A2E; letter-spacing:-0.01em; text-transform:uppercase; }
.school-addr { font-size:9px; color:#666; margin-top:2px; }
.report-title { font-size:13px; font-weight:700; color:#1A1A2E; text-align:right; }
.report-meta  { font-size:9px; color:#666; text-align:right; margin-top:3px; line-height:1.7; }

/* ── Student info band ── */
.info-table { width:100%; border-collapse:collapse; margin-bottom:12px; }
.info-table td { vertical-align:top; }
.photo-cell { width:90px; }
.photo-img  { width:80px; height:90px; object-fit:cover; border:1px solid #ddd; }
.photo-placeholder { width:80px; height:90px; background:#f0f0f0; border:1px solid #ddd; display:table-cell; text-align:center; vertical-align:middle; font-size:9px; color:#aaa; }
.bio-cell   { padding-left:14px; }
.bio-grid   { display:table; width:100%; border:1px solid #ccc; border-collapse:collapse; }
.bio-row    { display:table-row; }
.bio-label  { display:table-cell; font-size:8.5px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:0.06em; padding:5px 8px; border:1px solid #ddd; background:#f7f7f7; width:130px; }
.bio-value  { display:table-cell; font-size:10px; font-weight:600; padding:5px 8px; border:1px solid #ddd; }
.status-pass { display:inline-block; padding:1px 8px; background:#dcfce7; color:#15803D; font-weight:700; font-size:9px; border-radius:3px; }
.status-fail { display:inline-block; padding:1px 8px; background:#fee2e2; color:#BE123C; font-weight:700; font-size:9px; border-radius:3px; }

/* ── Results table ── */
.section-title { font-size:9px; font-weight:700; color:#1A1A2E; text-transform:uppercase; letter-spacing:0.09em; padding:5px 0 4px; border-bottom:2px solid #1A1A2E; margin-bottom:0; }
.results-table { width:100%; border-collapse:collapse; margin-bottom:10px; }
.results-table th { font-size:8.5px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.06em; padding:6px 7px; text-align:center; background:#1A1A2E; border:1px solid #1A1A2E; }
.results-table th.left { text-align:left; }
.results-table td { padding:5px 7px; font-size:10px; border:1px solid #ddd; text-align:center; }
.results-table td.left { text-align:left; font-weight:600; }
.results-table tr:nth-child(even) td { background:#f9f9f9; }
.results-table tr:last-child td { border-bottom:2px solid #1A1A2E; }
.remark-tag { font-size:8.5px; font-weight:700; padding:1px 6px; border-radius:3px; display:inline-block; }
.r-distinction { background:#fef9c3; color:#713f12; }
.r-excellent   { background:#dcfce7; color:#14532d; }
.r-verygood    { background:#dbeafe; color:#1e40af; }
.r-good        { background:#e0f2fe; color:#0c4a6e; }
.r-average     { background:#fff7ed; color:#9a3412; }
.r-below       { background:#fee2e2; color:#991b1b; }

/* ── Summary row ── */
.summary-table { width:100%; border-collapse:collapse; margin-bottom:10px; }
.summary-table td { border:1px solid #ccc; padding:6px 10px; text-align:center; }
.sum-label { font-size:8px; font-weight:700; color:#666; text-transform:uppercase; letter-spacing:0.06em; display:block; }
.sum-value { font-size:14px; font-weight:700; color:#1A1A2E; display:block; margin-top:2px; }

/* ── Grade key ── */
.key-table { width:100%; border-collapse:collapse; border:1px solid #ddd; margin-bottom:10px; }
.key-table td { padding:5px 8px; font-size:9px; border:1px solid #ddd; text-align:center; }
.key-header { background:#1A1A2E; color:#fff; font-weight:700; font-size:8px; text-transform:uppercase; }

/* ── Traits tables (Psychomotor + Affective) ── */
.traits-outer { display:table; width:100%; border-collapse:collapse; margin-bottom:10px; }
.traits-col   { display:table-cell; width:50%; vertical-align:top; padding-right:6px; }
.traits-col:last-child { padding-right:0; padding-left:6px; }
.trait-table  { width:100%; border-collapse:collapse; }
.trait-table th { font-size:8px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.05em; padding:5px 7px; background:#1A1A2E; border:1px solid #1A1A2E; text-align:left; }
.trait-table th.center { text-align:center; }
.trait-table td { padding:4px 7px; font-size:9.5px; border:1px solid #ddd; }
.trait-table td.center { text-align:center; font-weight:700; }
.trait-table tr:nth-child(even) td { background:#f9f9f9; }
.trait-score-0 { color:#aaa; }
.trait-score-1 { color:#be123c; }
.trait-score-2 { color:#b45309; }
.trait-score-3 { color:#0369a1; }
.trait-score-4 { color:#15803d; }
.trait-score-5 { color:#166534; font-weight:700; }

/* ── Comments ── */
.comment-box { border:1px solid #ddd; padding:8px 10px; margin-bottom:8px; }
.comment-label { font-size:8.5px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:4px; }
.comment-text  { font-size:10px; color:#222; line-height:1.55; font-style:italic; }

/* ── Signatures ── */
.sig-table { width:100%; border-collapse:collapse; margin-top:14px; }
.sig-table td { text-align:center; padding:0 10px; width:33%; }
.sig-line  { border-top:1px solid #888; margin-bottom:3px; }
.sig-label { font-size:8.5px; color:#666; }

/* ── Footer ── */
.footer { margin-top:10px; padding-top:7px; border-top:1px solid #ddd; font-size:8px; color:#aaa; text-align:center; }
</style>
</head>
<body>

{{-- ── HEADER ─────────────────────────────────────────────────────────────── --}}
<table class="header-table">
    <tr>
        <td style="width:60px;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" style="width:54px;height:54px;object-fit:cover;" alt="Logo">
            @else
                <div style="width:54px;height:54px;background:#1A1A2E;color:#fff;font-size:22px;font-weight:700;text-align:center;line-height:54px;border-radius:4px;">
                    {{ strtoupper(substr($schoolName, 0, 1)) }}
                </div>
            @endif
        </td>
        <td style="padding-left:12px;">
            <div class="school-name">{{ $schoolName }}</div>
            <div class="school-addr">{{ $schoolAddress }}</div>
        </td>
        <td style="text-align:right;">
            <div class="report-title">STUDENT REPORT CARD</div>
            <div class="report-meta">
                SESSION: {{ $term->session->name }}<br>
                TERM: {{ strtoupper($term->name) }} TERM<br>
                @if($term->next_term_begins)
                    NEXT TERM BEGINS: {{ $term->next_term_begins->format('Y-m-d') }}
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- ── STUDENT INFO BAND ───────────────────────────────────────────────────── --}}
<table class="info-table">
    <tr>
        <td class="photo-cell">
            @if($photoBase64)
                <img src="{{ $photoBase64 }}" class="photo-img" alt="Student Photo">
            @else
                <div class="photo-placeholder">No Photo</div>
            @endif
        </td>
        <td class="bio-cell">
            <div class="bio-grid">
                <div class="bio-row">
                    <div class="bio-label">Name</div>
                    <div class="bio-value" style="font-size:11px;">{{ strtoupper($student->full_name) }}</div>
                    <div class="bio-label">Adm. Number</div>
                    <div class="bio-value">{{ $student->admission_number }}</div>
                </div>
                <div class="bio-row">
                    <div class="bio-label">Sex</div>
                    <div class="bio-value">{{ $student->gender }}</div>
                    <div class="bio-label">Date of Birth</div>
                    <div class="bio-value">{{ $student->date_of_birth?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div class="bio-row">
                    <div class="bio-label">Class</div>
                    <div class="bio-value">{{ $enrolment?->schoolClass?->display_name ?? '—' }}</div>
                    <div class="bio-label">Status</div>
                    <div class="bio-value">
                        @if($passStatus === 'PASS')
                            <span class="status-pass">PASS</span>
                        @elseif($passStatus === 'FAIL')
                            <span class="status-fail">FAIL</span>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="bio-row">
                    <div class="bio-label">No. of Times School Opened</div>
                    <div class="bio-value">{{ $term->school_days_count ?? '—' }}</div>
                    <div class="bio-label">No. of Times Present</div>
                    <div class="bio-value">{{ $enrolment?->times_present ?? '—' }}</div>
                </div>
                <div class="bio-row">
                    <div class="bio-label">No. of Times Absent</div>
                    <div class="bio-value">{{ $enrolment?->times_absent ?? '—' }}</div>
                    <div class="bio-label">Extra Curricular</div>
                    <div class="bio-value"> </div>
                </div>
            </div>
        </td>
    </tr>
</table>

{{-- ── SUBJECT RESULTS ─────────────────────────────────────────────────────── --}}
@php
    function remarkClass(string $remark): string {
        return match($remark) {
            'Distinction'   => 'r-distinction',
            'Excellent'     => 'r-excellent',
            'Very Good'     => 'r-verygood',
            'Good'          => 'r-good',
            'Average'       => 'r-average',
            default         => 'r-below',
        };
    }
@endphp

<div class="section-title">Academic Performance</div>
<table class="results-table">
    <thead>
        <tr>
            <th class="left" style="width:28%;">Subject</th>
            <th>CA (40)</th>
            <th>Exam (60)</th>
            <th>Total</th>
            <th>Class Ave</th>
            <th>LS</th>
            <th>HS</th>
            <th>Grade</th>
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $result)
        <tr>
            <td class="left">{{ $result->subject->name }}</td>
            <td>{{ $result->ca_score ?? '—' }}</td>
            <td>{{ $result->exam_score ?? '—' }}</td>
            <td style="font-weight:700;">{{ $result->total ?? '—' }}</td>
            <td>{{ $result->class_average ? number_format($result->class_average, 1) : '—' }}</td>
            <td>{{ $result->class_lowest ?? '—' }}</td>
            <td>{{ $result->class_highest ?? '—' }}</td>
            <td style="font-weight:700;">{{ $result->grade ?? '—' }}</td>
            <td>
                @if($result->remark)
                    <span class="remark-tag {{ remarkClass($result->remark) }}">{{ $result->remark }}</span>
                @else
                    —
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ── SUMMARY ROW ─────────────────────────────────────────────────────────── --}}
@if($results->isNotEmpty())
<table class="summary-table">
    <tr>
        <td>
            <span class="sum-label">Total Mark</span>
            <span class="sum-value">{{ $results->sum('total') }}</span>
        </td>
        <td>
            <span class="sum-label">Lowest Score (LS)</span>
            <span class="sum-value">{{ $results->min('total') ?? '—' }}</span>
        </td>
        <td>
            <span class="sum-label">Highest Score (HS)</span>
            <span class="sum-value">{{ $results->max('total') ?? '—' }}</span>
        </td>
        <td>
            <span class="sum-label">Average % Score</span>
            <span class="sum-value">{{ $average }}%</span>
        </td>
    </tr>
</table>
@endif

{{-- ── GRADE KEY ────────────────────────────────────────────────────────────── --}}
<table class="key-table">
    <tr>
        <td class="key-header" colspan="6">Grading Scale</td>
    </tr>
    <tr>
        <td><strong>A+</strong> (90–100) Distinction</td>
        <td><strong>A</strong> (70–89) Excellent</td>
        <td><strong>B</strong> (60–69) Very Good</td>
        <td><strong>C</strong> (50–59) Good</td>
        <td><strong>D</strong> (40–49) Average</td>
        <td><strong>E</strong> (0–39) Below Average</td>
    </tr>
</table>

{{-- ── PSYCHOMOTOR + AFFECTIVE TRAITS ─────────────────────────────────────── --}}
@php
    // Rating key for Primary: 1=Not Applicable, 2=Poor, 3=Fair, 4=Good, 5=Very Good
    // (score 5 is the entered maximum; label shown in key box)
    $ratingKey = [1=>'Not Applicable', 2=>'Poor', 3=>'Fair', 4=>'Good', 5=>'Very Good'];
@endphp

<div class="traits-outer">
    {{-- Psychomotor Skills --}}
    <div class="traits-col">
        <table class="trait-table">
            <thead>
                <tr>
                    <th>Psychomotor Skills</th>
                    <th class="center" style="width:36px;">Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($psychomotorDef as $key => $label)
                @php $score = $traitScores[$key] ?? null; @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="center trait-score-{{ $score ?? 0 }}">{{ $score ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Affective Areas --}}
    <div class="traits-col">
        <table class="trait-table">
            <thead>
                <tr>
                    <th>Affective Areas</th>
                    <th class="center" style="width:36px;">Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($affectiveDef as $key => $label)
                @php $score = $traitScores[$key] ?? null; @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="center trait-score-{{ $score ?? 0 }}">{{ $score ?? '—' }}</td>
                </tr>
                @endforeach

                {{-- Rating Key embedded in last rows of affective table --}}
                <tr>
                    <td colspan="2" style="padding-top:6px;background:#f7f7f7;">
                        <strong style="font-size:8px;text-transform:uppercase;letter-spacing:0.06em;">Key Rating</strong>
                    </td>
                </tr>
                @foreach($ratingKey as $val => $lbl)
                <tr>
                    <td style="font-size:9px;background:#f7f7f7;">{{ $lbl }}</td>
                    <td class="center" style="font-weight:700;background:#f7f7f7;font-size:9px;">{{ $val }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── COMMENTS ─────────────────────────────────────────────────────────────── --}}
@if($termComment?->teacher_comment)
<div class="comment-box">
    <div class="comment-label">Class Teacher's Comment</div>
    <div class="comment-text">{{ $termComment->teacher_comment }}</div>
</div>
@endif

@if($termComment?->head_teacher_comment)
<div class="comment-box">
    <div class="comment-label">Head Teacher's Comment</div>
    <div class="comment-text">{{ $termComment->head_teacher_comment }}</div>
</div>
@endif

{{-- ── SIGNATURES ───────────────────────────────────────────────────────────── --}}
<table class="sig-table">
    <tr>
        <td><div class="sig-line"></div><div class="sig-label">Class Teacher</div></td>
        <td><div class="sig-line"></div><div class="sig-label">Head Teacher / Principal</div></td>
        <td><div class="sig-line"></div><div class="sig-label">Date</div></td>
    </tr>
</table>

<div class="footer">
    {{ $schoolName }} &middot; Generated {{ now()->format('d M Y') }}
</div>

</body>
</html>
