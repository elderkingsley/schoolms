{{-- Deploy to: resources/views/pdf/report-card-preschool.blade.php --}}
{{-- Nursery/preschool — remark_only classes. DomPDF-safe: all layout via HTML tables. --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family:'DejaVu Sans', Arial, sans-serif;
    font-size:8px;
    color:#111111;
    background:#ffffff;
    padding:16px 18px 12px;
    line-height:1.35;
}

/* ── HEADER ── */
.hdr { width:100%; border-collapse:collapse; margin-bottom:6px; }
.hdr td { vertical-align:middle; }
.logo-img  { width:42px; height:42px; object-fit:cover; border-radius:5px; }
.logo-fb   { width:42px; height:42px; background:#1A56FF; color:#fff; font-size:18px; font-weight:700; text-align:center; line-height:42px; border-radius:5px; }
.school-name { font-size:15px; font-weight:700; color:#111111; text-transform:uppercase; letter-spacing:-0.01em; }
.school-addr { font-size:7px; color:#999999; margin-top:1px; }
.school-sub  { font-size:7px; color:#1A3A2A; font-weight:600; margin-top:2px; letter-spacing:0.04em; text-transform:uppercase; }

/* Meta table */
.meta { border-collapse:collapse; border:1px solid #E8E6E1; }
.meta td { font-size:7px; padding:2px 6px; border-bottom:1px solid #E8E6E1; }
.meta tr:last-child td { border-bottom:none; }
.ml { color:#999999; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; background:#F5F4F0; width:94px; }
.mv { color:#111111; font-weight:600; min-width:70px; }

/* Accent rule */
.rule { height:2px; background:#1A56FF; margin-bottom:6px; border-radius:1px; }
/* (DomPDF doesn't support linear-gradient on block elements reliably, solid works) */

/* ── BIO ── */
.bio { width:100%; border-collapse:collapse; border:1px solid #E8E6E1; margin-bottom:6px; }
.bio td { vertical-align:middle; }
.bio-photo-cell { width:56px; background:#F5F4F0; text-align:center; padding:3px; border-right:1px solid #E8E6E1; }
.bio-photo-img { width:50px; height:58px; object-fit:cover; display:block; }
.bio-photo-fb  { width:50px; height:58px; background:#E8E6E1; display:inline-block; line-height:58px; text-align:center; font-size:7px; color:#999; }
.bio-inner { width:100%; border-collapse:collapse; }
.bio-inner td { font-size:7.5px; padding:2.5px 6px; border-bottom:1px solid #F5F4F0; }
.bio-inner tr:last-child td { border-bottom:none; }
.bl { color:#999999; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; font-size:6.5px; width:82px; background:#F5F4F0; }
.bv { color:#111111; font-weight:600; }

/* ── SECTION LABEL ── */
.sec { font-size:6.5px; font-weight:700; color:#1A3A2A; text-transform:uppercase; letter-spacing:0.09em;
    padding:3px 5px; background:#EEF3EE; border-left:3px solid #1A3A2A; display:block; margin-bottom:0; }

/* ── MAIN BODY TABLE: left 62% | right 38% ── */
.body-tbl { width:100%; border-collapse:collapse; }
.body-tbl > tbody > tr > td { vertical-align:top; }
.left-col  { padding-right:5px; width:62%; }
.right-col { width:38%; }

/* ── EVALUATION TABLE ── */
.et { width:100%; border-collapse:collapse; }
.et-head { background:#3D4A5C; }
.et-head td { font-size:6.5px; font-weight:700; color:rgba(255,255,255,0.85); text-transform:uppercase;
    letter-spacing:0.07em; padding:4px 5px; }
.et-head td.rc { text-align:center; width:58px; }
.et td.sn { font-weight:700; font-size:7.5px; color:#111111; width:20%; padding:3px 5px; border:1px solid #F0EEE9; vertical-align:top; }
.et td.ev { font-size:7px; color:#333333; padding:3px 5px; border:1px solid #F0EEE9; vertical-align:top; line-height:1.45; }
.et td.rm { text-align:center; font-size:6.5px; padding:3px 4px; border:1px solid #F0EEE9; vertical-align:middle; width:58px; }
.et tr:nth-child(even) td { background:#FAFAF8; }

/* Chips */
.chip { font-size:6.5px; font-weight:700; padding:1px 4px; border-radius:20px; display:inline-block; }
.chip-e { background:rgba(21,128,61,0.10);  color:#15803D; }
.chip-v { background:rgba(21,128,61,0.07);  color:#166534; }
.chip-g { background:rgba(180,83,9,0.08);   color:#B45309; }
.chip-f { background:rgba(190,18,60,0.07);  color:#BE123C; }
.chip-p { background:rgba(190,18,60,0.12);  color:#9F1239; }

/* ── TRAITS ── */
.tt { width:100%; border-collapse:collapse; margin-bottom:4px; }
.tt-head { background:#3D4A5C; }
.tt-head td { font-size:6.5px; font-weight:700; color:rgba(255,255,255,0.85); text-transform:uppercase;
    letter-spacing:0.06em; padding:4px 5px; }
.tt-head td.sc { text-align:center; width:16px; }
.tt td.tn { font-size:7.5px; padding:2px 5px; border:1px solid #F0EEE9; }
.tt td.sc { text-align:center; font-weight:700; font-size:7.5px; width:16px; padding:2px 4px; border:1px solid #F0EEE9; }
.tt tr:nth-child(even) td { background:#FAFAF8; }
.s0 { color:#E8E6E1; } .s1 { color:#BE123C; } .s2 { color:#B45309; }
.s3 { color:#1A56FF; } .s4 { color:#15803D; } .s5 { color:#111111; font-weight:700; }

/* Key rating */
.krt { width:100%; border-collapse:collapse; margin-top:4px; border:1px solid #E8E6E1; }
.krt-head { background:#3D4A5C; }
.krt-head td { font-size:6px; font-weight:700; color:rgba(255,255,255,0.75); text-transform:uppercase;
    letter-spacing:0.08em; padding:3px 5px; }
.krt td { font-size:7px; padding:2px 5px; border-bottom:1px solid #F5F4F0; }
.krt tr:last-child td { border-bottom:none; }
.kv { font-weight:700; text-align:center; background:#F5F4F0; width:16px; color:#1A56FF; padding:2px 4px; border-bottom:1px solid #F5F4F0; }

/* ── COMMENTS ── */
.cmt-tbl { width:100%; border-collapse:collapse; margin-top:5px; margin-bottom:5px; }
.cmt-tbl td { vertical-align:top; width:50%; }
.cmt-tbl td:first-child { padding-right:4px; }
.cmt-tbl td:last-child  { padding-left:4px; }
.cmt-box { border:1px solid #E8E6E1; border-radius:5px; padding:4px 6px; background:#FAFAF8; }
.cmt-lbl { font-size:6px; font-weight:700; color:#1A3A2A; text-transform:uppercase; letter-spacing:0.09em; margin-bottom:2px; }
.cmt-txt { font-size:7.5px; color:#111111; line-height:1.5; font-style:italic; }

/* ── SIGNATURES ── */
.sig-tbl { width:100%; border-collapse:collapse; margin-top:5px; }
.sig-tbl td { text-align:center; width:33%; padding:0 8px; }
.sig-ln  { border-top:1px solid #E8E6E1; margin-bottom:2px; }
.sig-lb  { font-size:6.5px; color:#999999; }

/* ── FOOTER ── */
.footer { margin-top:4px; padding-top:3px; border-top:1px solid #F5F4F0; font-size:6px; color:#999999; text-align:center; }
.footer-name { color:#1A3A2A; font-weight:600; }
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

{{-- ═══ HEADER ═══ --}}
<table class="hdr">
    <tr>
        <td style="width:46px;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
            @else
                <div class="logo-fb">{{ strtoupper(substr($schoolName,0,1)) }}</div>
            @endif
        </td>
        <td style="padding-left:9px;">
            <div class="school-name">{{ $schoolName }}</div>
            <div class="school-addr">{{ $schoolAddress }}</div>
            <div class="school-sub">Student Report Card</div>
        </td>
        <td style="text-align:right; width:168px; vertical-align:top;">
            <table class="meta" style="margin-left:auto;">
                <tr>
                    <td class="ml">Next Term Begins</td>
                    <td class="mv">{{ $term->next_term_begins?->format('d M Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="ml">Session</td>
                    <td class="mv">{{ $term->session->name }}</td>
                </tr>
                <tr>
                    <td class="ml">Term</td>
                    <td class="mv">{{ strtoupper($term->name) }} TERM</td>
                </tr>
                <tr>
                    <td class="ml">Class</td>
                    <td class="mv">{{ strtoupper($enrolment?->schoolClass?->display_name ?? '—') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div class="rule"></div>

{{-- ═══ BIO ═══ --}}
<table class="bio">
    <tr>
        <td class="bio-photo-cell">
            @if($photoBase64)
                <img src="{{ $photoBase64 }}" class="bio-photo-img" alt="Photo">
            @else
                <div class="bio-photo-fb">No Photo</div>
            @endif
        </td>
        <td style="padding:0; vertical-align:middle;">
            <table class="bio-inner">
                <tr>
                    <td class="bl">Name</td>
                    <td class="bv" style="font-size:8px;font-weight:700;" colspan="5">{{ strtoupper($student->full_name) }}</td>
                    <td class="bl">Adm. No.</td>
                    <td class="bv" style="font-family:'Courier New',monospace;">{{ $student->admission_number }}</td>
                </tr>
                <tr>
                    <td class="bl">Sex</td>
                    <td class="bv">{{ $student->gender }}</td>
                    <td class="bl">Date of Birth</td>
                    <td class="bv">{{ $student->date_of_birth?->format('d/m/Y') ?? '—' }}</td>
                    <td class="bl">School Opened</td>
                    <td class="bv">{{ $term->school_days_count ?? '—' }}</td>
                    <td class="bl">Times Present</td>
                    <td class="bv">{{ $enrolment?->times_present ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="bl">Times Absent</td>
                    <td class="bv">{{ $enrolment?->times_absent ?? '—' }}</td>
                    <td class="bl" colspan="2">Extra Curricular</td>
                    <td class="bv" colspan="5">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ═══ MAIN BODY: evaluations (left) | traits (right) ═══ --}}
<table class="body-tbl">
    <tr>

        {{-- LEFT: Subject evaluations --}}
        <td class="left-col">
            <div class="sec">Subject Evaluation</div>
            <table class="et">
                <tr class="et-head">
                    <td style="width:20%;">Subject</td>
                    <td>Evaluation</td>
                    <td class="rc">Remark</td>
                </tr>
                @forelse($results as $result)
                <tr>
                    <td class="sn">{{ $result->subject->name }}</td>
                    <td class="ev">{{ $result->admin_comment ?: '—' }}</td>
                    <td class="rm">
                        @if($result->remark)
                            <span class="{{ preChip($result->remark) }}">{{ strtoupper($result->remark) }}</span>
                        @else
                            <span style="color:#999;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center;color:#999;padding:8px;border:1px solid #F0EEE9;">No evaluations recorded.</td>
                </tr>
                @endforelse
            </table>
        </td>

        {{-- RIGHT: Traits --}}
        <td class="right-col">
            <div class="sec">Psychomotor Skills</div>
            <table class="tt" style="margin-bottom:4px;">
                <tr class="tt-head">
                    <td>Skill</td>
                    <td class="sc">&#9679;</td>
                </tr>
                @foreach($psychomotorDef as $key => $label)
                @php $sc = $traitScores[$key] ?? null; @endphp
                <tr>
                    <td class="tn">{{ $label }}</td>
                    <td class="sc s{{ $sc ?? 0 }}">{{ $sc ?? '—' }}</td>
                </tr>
                @endforeach
            </table>

            <div class="sec">Affective Areas</div>
            <table class="tt" style="margin-bottom:4px;">
                <tr class="tt-head">
                    <td>Trait</td>
                    <td class="sc">&#9679;</td>
                </tr>
                @foreach($affectiveDef as $key => $label)
                @php $sc = $traitScores[$key] ?? null; @endphp
                <tr>
                    <td class="tn">{{ $label }}</td>
                    <td class="sc s{{ $sc ?? 0 }}">{{ $sc ?? '—' }}</td>
                </tr>
                @endforeach
            </table>

            <table class="krt">
                <tr class="krt-head">
                    <td colspan="2">Skills Rating Key</td>
                </tr>
                <tr><td class="tn" style="font-size:7px;padding:2px 5px;border-bottom:1px solid #F5F4F0;">Excellent</td> <td class="kv">5</td></tr>
                <tr><td class="tn" style="font-size:7px;padding:2px 5px;border-bottom:1px solid #F5F4F0;">Very Good</td> <td class="kv">4</td></tr>
                <tr><td class="tn" style="font-size:7px;padding:2px 5px;border-bottom:1px solid #F5F4F0;">Good</td>      <td class="kv">3</td></tr>
                <tr><td class="tn" style="font-size:7px;padding:2px 5px;border-bottom:1px solid #F5F4F0;">Fair</td>      <td class="kv">2</td></tr>
                <tr><td class="tn" style="font-size:7px;padding:2px 5px;">Poor</td>                                       <td class="kv" style="color:#BE123C;">1</td></tr>
            </table>
        </td>

    </tr>
</table>

{{-- ═══ COMMENTS ═══ --}}
<table class="cmt-tbl">
    <tr>
        <td>
            <div class="cmt-box">
                <div class="cmt-lbl">Class Teacher's Comment</div>
                <div class="cmt-txt">{{ $termComment?->teacher_comment ?? '—' }}</div>
            </div>
        </td>
        <td>
            <div class="cmt-box">
                <div class="cmt-lbl">Head Teacher's Comment</div>
                <div class="cmt-txt">{{ $termComment?->head_teacher_comment ?? '—' }}</div>
            </div>
        </td>
    </tr>
</table>

{{-- ═══ SIGNATURES ═══ --}}
<table class="sig-tbl">
    <tr>
        <td><div class="sig-ln"></div><div class="sig-lb">Class Teacher</div></td>
        <td><div class="sig-ln"></div><div class="sig-lb">Head Teacher / Principal</div></td>
        <td><div class="sig-ln"></div><div class="sig-lb">Date</div></td>
    </tr>
</table>

<div class="footer">
    <span class="footer-name">{{ $schoolName }}</span>
    &nbsp;&middot;&nbsp; Generated {{ now()->format('d M Y') }}
    &nbsp;&middot;&nbsp; connect.nurturevilleschool.org
</div>

</body>
</html>
