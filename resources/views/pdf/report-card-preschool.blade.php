{{-- Deploy to: resources/views/pdf/report-card-preschool.blade.php --}}
{{-- Updated for 1.5 page fit: optimized padding, line-height, and header layout --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
/* Reset and Base Scaling */
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family:'DejaVu Sans', Arial, sans-serif;
    font-size:8.5px; /* Slightly increased for readability on 1.5 pages */
    color:#111111;
    background:#ffffff;
    padding:10px 15px; /* Tightened margins */
    line-height:1.25; /* More compact line height */
}

/* Print Cleanup: Removes browser-added URL/Date headers */
@media print {
    @page { margin: 0; }
    body { padding: 1cm; }
}

/* ── HEADER ── */
.hdr { width:100%; border-collapse:collapse; margin-bottom:4px; }
.hdr td { vertical-align:middle; }
.logo-img  { width:50px; height:50px; object-fit:contain; border-radius:5px; } /* Improved image fit */
.logo-fb   { width:50px; height:50px; background:#1A56FF; color:#fff; font-size:22px; font-weight:700; text-align:center; line-height:50px; border-radius:5px; }
.school-name { font-size:16px; font-weight:800; color:#B01E3E; text-transform:uppercase; letter-spacing:-0.01em; } /* Updated color to match reference */
.school-addr { font-size:8px; color:#555555; margin-top:1px; }
.school-sub  { font-size:8px; color:#1A3A2A; font-weight:700; margin-top:2px; text-transform:uppercase; }

/* Meta table - Right Side */
.meta { border-collapse:collapse; border:1px solid #E8E6E1; }
.meta td { font-size:7.5px; padding:2px 5px; border-bottom:1px solid #E8E6E1; border-left:1px solid #E8E6E1; }
.ml { color:#666666; font-weight:700; text-transform:uppercase; background:#F9F9F7; width:90px; }
.mv { color:#111111; font-weight:700; }

.rule { height:2px; background:#B01E3E; margin-bottom:8px; border-radius:1px; }

/* ── BIO ── */
.bio { width:100%; border-collapse:collapse; border:1px solid #E8E6E1; margin-bottom:8px; }
.bio-photo-cell { width:65px; background:#F5F4F0; text-align:center; padding:4px; border-right:1px solid #E8E6E1; }
.bio-photo-img { width:60px; height:70px; object-fit:cover; display:block; border-radius:2px; }
.bio-inner td { font-size:8px; padding:3px 6px; border-bottom:1px solid #F0EEE9; }
.bl { color:#777777; font-weight:700; text-transform:uppercase; font-size:7px; background:#F9F9F7; }
.bv { color:#111111; font-weight:700; }

/* ── SECTION LABELS ── */
.sec { font-size:7px; font-weight:800; color:#FFFFFF; text-transform:uppercase; letter-spacing:0.05em;
    padding:3px 8px; background:#3D4A5C; border-radius:3px 3px 0 0; margin-bottom:0; }

/* ── LAYOUT ── */
.body-tbl { width:100%; border-collapse:collapse; }
.left-col  { padding-right:8px; width:65%; vertical-align:top; }
.right-col { width:35%; vertical-align:top; }

/* ── EVALUATION TABLE ── */
.et { width:100%; border-collapse:collapse; margin-bottom:10px; }
.et td { border:1px solid #E8E6E1; padding:4px 6px; vertical-align:top; }
.et-head td { background:#F5F4F0; font-weight:800; font-size:7px; color:#444; text-transform:uppercase; }
.et td.sn { font-weight:800; width:25%; color:#B01E3E; }
.et td.ev { font-size:8px; line-height:1.4; color:#333; }
.et td.rm { text-align:center; width:65px; vertical-align:middle; }

/* Chips */
.chip { font-size:7px; font-weight:800; padding:2px 6px; border-radius:4px; text-transform:uppercase; }
.chip-e { background:#DCFCE7; color:#15803D; }
.chip-v { background:#F0FDF4; color:#166534; }
.chip-g { background:#FEF3C7; color:#B45309; }
.chip-f { background:#FFE4E6; color:#BE123C; }

/* ── TRAITS ── */
.tt { width:100%; border-collapse:collapse; margin-bottom:8px; border:1px solid #E8E6E1; }
.tt td { padding:3px 6px; border-bottom:1px solid #F0EEE9; font-size:8px; }
.tt .sc { text-align:center; font-weight:800; width:20px; background:#F9F9F7; border-left:1px solid #E8E6E1; }

/* ── COMMENTS ── */
.cmt-tbl { width:100%; border-collapse:collapse; margin-top:10px; }
.cmt-box { border:1px solid #E8E6E1; border-radius:4px; padding:6px; background:#FAFAF8; min-height:50px; }
.cmt-lbl { font-size:7px; font-weight:800; color:#B01E3E; text-transform:uppercase; margin-bottom:4px; border-bottom:1px solid #E8E6E1; padding-bottom:2px; }
.cmt-txt { font-size:8.5px; color:#222; font-style:italic; line-height:1.4; }

/* ── SIGNATURES ── */
.sig-tbl { width:100%; border-collapse:collapse; margin-top:20px; }
.sig-tbl td { text-align:center; width:33.3%; padding:0 15px; }
.sig-ln  { border-top:1px solid #333; margin-bottom:4px; }
.sig-lb  { font-size:7px; font-weight:700; color:#555; text-transform:uppercase; }

.footer { margin-top:15px; padding-top:5px; border-top:1px dotted #CCC; font-size:7px; color:#888; text-align:center; }
</style>
</head>
<body>

@php
    function preChip(string $r): string {
        return match(strtolower(trim($r))) {
            'excellent' => 'chip chip-e',
            'very good' => 'chip chip-v',
            'good'      => 'chip chip-g',
            'fair'      => 'chip chip-f',
            default     => 'chip chip-p',
        };
    }
@endphp

{{-- HEADER --}}
<table class="hdr">
    <tr>
        <td style="width:55px;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
            @else
                <div class="logo-fb">{{ strtoupper(substr($schoolName,0,1)) }}</div>
            @endif
        </td>
        <td style="padding-left:12px;">
            <div class="school-name">{{ $schoolName }}</div>
            <div class="school-addr">{{ $schoolAddress }} </div>
            <div class="school-sub">Preschool Performance Report</div>
        </td>
        <td style="text-align:right; vertical-align:top;">
            <table class="meta" style="margin-left:auto;">
                <tr><td class="ml">Begins</td><td class="mv">{{ $term->next_term_begins?->format('d/m/Y') ?? '—' }} [cite: 24]</td></tr>
                <tr><td class="ml">Session</td><td class="mv">{{ $term->session->name }} [cite: 24]</td></tr>
                <tr><td class="ml">Term</td><td class="mv">{{ strtoupper($term->name) }} [cite: 24]</td></tr>
                <tr><td class="ml">Class</td><td class="mv">{{ strtoupper($enrolment?->schoolClass?->display_name ?? '—') }} [cite: 24]</td></tr>
            </table>
        </td>
    </tr>
</table>
<div class="rule"></div>

{{-- BIO --}}
<table class="bio">
    <tr>
        <td class="bio-photo-cell">
            @if($photoBase64)
                <img src="{{ $photoBase64 }}" class="bio-photo-img" alt="Photo">
            @else
                <div class="bio-photo-fb">No Photo</div>
            @endif
        </td>
        <td>
            <table class="bio-inner" style="width:100%; border-collapse:collapse;">
                <tr>
                    <td class="bl" style="width:15%;">Name</td>
                    <td class="bv" colspan="3" style="font-size:9px;">{{ strtoupper($student->full_name) }} [cite: 25]</td>
                    <td class="bl" style="width:15%;">Adm No</td>
                    <td class="bv">{{ $student->admission_number }} [cite: 25]</td>
                </tr>
                <tr>
                    <td class="bl">Sex</td><td class="bv">{{ $student->gender }} [cite: 25]</td>
                    <td class="bl">D.O.B</td><td class="bv">{{ $student->date_of_birth?->format('d/m/Y') ?? '—' }} [cite: 25]</td>
                    <td class="bl">Opened</td><td class="bv">{{ $term->school_days_count ?? '—' }} [cite: 25]</td>
                </tr>
                <tr>
                    <td class="bl">Present</td><td class="bv">{{ $enrolment?->times_present ?? '—' }} [cite: 25]</td>
                    <td class="bl">Absent</td><td class="bv">{{ $enrolment?->times_absent ?? '—' }} [cite: 25]</td>
                    <td class="bl">Extra</td><td class="bv">N/A [cite: 25]</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- MAIN CONTENT --}}
<table class="body-tbl">
    <tr>
        {{-- EVALUATIONS --}}
        <td class="left-col">
            <div class="sec">Subject Evaluations</div>
            <table class="et">
                <tr class="et-head">
                    <td>Subject </td>
                    <td>Detailed Assessment </td>
                    <td class="rm">Remark </td>
                </tr>
                @foreach($results as $result)
                <tr>
                    <td class="sn">{{ $result->subject->name }}</td>
                    <td class="ev">{{ $result->admin_comment }}</td>
                    <td class="rm">
                        <span class="{{ preChip($result->remark) }}">{{ $result->remark }}</span>
                    </td>
                </tr>
                @endforeach
            </table>
        </td>

        {{-- TRAITS --}}
        <td class="right-col">
            <div class="sec">Psychomotor Skills</div>
            <table class="tt">
                @foreach($psychomotorDef as $key => $label)
                <tr><td>{{ $label }} </td><td class="sc">{{ $traitScores[$key] ?? '—' }}</td></tr>
                @endforeach
            </table>

            <div class="sec">Affective Areas</div>
            <table class="tt">
                @foreach($affectiveDef as $key => $label)
                <tr><td>{{ $label }} </td><td class="sc">{{ $traitScores[$key] ?? '—' }}</td></tr>
                @endforeach
            </table>

            <div class="sec">Rating Key</div>
            <table class="tt" style="background:#F9F9F7;">
                <tr><td>Excellent / Very Good </td><td class="sc">5 - 4</td></tr>
                <tr><td>Good / Fair </td><td class="sc">3 - 2</td></tr>
                <tr><td>Needs Improvement </td><td class="sc">1</td></tr>
            </table>
        </td>
    </tr>
</table>

{{-- COMMENTS SECTION (Moves to Page 2 naturally if needed) --}}
<div class="sec" style="margin-top:10px;">Observations & Remarks</div>
<table class="cmt-tbl">
    <tr>
        <td style="width:50%; padding-right:5px;">
            <div class="cmt-box">
                <div class="cmt-lbl">Class Teacher [cite: 31]</div>
                <div class="cmt-txt">{{ $termComment?->teacher_comment }} [cite: 34]</div>
            </div>
        </td>
        <td style="width:50%; padding-left:5px;">
            <div class="cmt-box">
                <div class="cmt-lbl">Head Teacher [cite: 32]</div>
                <div class="cmt-txt">{{ $termComment?->head_teacher_comment }} [cite: 35]</div>
            </div>
        </td>
    </tr>
</table>

{{-- SIGNATURES --}}
<table class="sig-tbl">
    <tr>
        <td><div class="sig-ln"></div><div class="sig-lb">Class Teacher</div></td>
        <td><div class="sig-ln"></div><div class="sig-lb">Head Teacher</div></td>
        <td><div class="sig-ln"></div><div class="sig-lb">Parent's Signature</div></td>
    </tr>
</table>

<div class="footer">
    <strong>{{ $schoolName }}</strong> &bull; Generated on {{ date('d M Y, h:i A') }} [cite: 28]
</div>

</body>
</html>
