{{-- Deploy to: resources/views/pdf/report-card-primary.blade.php --}}
{{-- Primary (scored) classes — modern layout matching portal design system --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
/*
 * FONTS: DomPDF cannot load Google Fonts via @import.
 * Outfit and JetBrains Mono are not available in DomPDF's bundled font set.
 * DejaVu Sans is the best available match — clean, legible, professional.
 * We replicate the portal's spacing, colour, and weight conventions exactly.
 */
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 8px;
    color: #111111;
    background: #ffffff;
    padding: 18px 20px 14px;
    line-height: 1.4;
}

/* ── ACCENT PALETTE (portal tokens) ────────────────────────────────────── */
/* --c-accent:  #1A56FF  */
/* --c-bg:      #F5F4F0  */
/* --c-border:  #E8E6E1  */
/* --c-success: #15803D  */
/* --c-danger:  #BE123C  */
/* --c-sidebar: #0E0E0E  */
/* --c-text-2:  #555555  */
/* --c-text-3:  #999999  */

/* ── HEADER ──────────────────────────────────────────────────────────────── */
.hdr { display:table; width:100%; margin-bottom:8px; }
.hdr-l { display:table-cell; vertical-align:middle; width:46px; }
.hdr-l img { width:42px; height:42px; object-fit:cover; border-radius:6px; }
.hdr-l-fb {
    width:42px; height:42px; background:#1A56FF; color:#fff;
    font-size:18px; font-weight:700; text-align:center; line-height:42px;
    border-radius:6px;
}
.hdr-m { display:table-cell; vertical-align:middle; padding-left:9px; }
.hdr-school { font-size:15px; font-weight:700; color:#0E0E0E; letter-spacing:-0.01em; text-transform:uppercase; }
.hdr-addr   { font-size:7px; color:#999999; margin-top:1px; }
.hdr-sub    { font-size:7px; color:#1A56FF; font-weight:600; margin-top:2px; letter-spacing:0.04em; text-transform:uppercase; }
.hdr-r { display:table-cell; vertical-align:middle; text-align:right; width:160px; }

/* Meta table in header — portal card-style */
.meta-tbl { border-collapse:collapse; margin-left:auto; border:1px solid #E8E6E1; border-radius:8px; overflow:hidden; }
.meta-tbl td { font-size:7px; padding:2px 6px; border-bottom:1px solid #E8E6E1; }
.meta-tbl tr:last-child td { border-bottom:none; }
.meta-lbl { color:#999999; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; background:#F5F4F0; width:92px; }
.meta-val { color:#111111; font-weight:600; min-width:68px; }
.pill-pass { background:rgba(21,128,61,0.1); color:#15803D; font-weight:700; padding:0 5px; border-radius:3px; }
.pill-fail { background:rgba(190,18,60,0.1); color:#BE123C; font-weight:700; padding:0 5px; border-radius:3px; }

/* Thin accent rule under header */
.hdr-rule { height:2px; background:linear-gradient(90deg,#1A56FF 0%,rgba(26,86,255,0.15) 100%); margin-bottom:8px; border-radius:1px; }

/* ── BIO STRIP ────────────────────────────────────────────────────────────── */
.bio { display:table; width:100%; border:1px solid #E8E6E1; border-radius:8px; overflow:hidden; margin-bottom:7px; background:#fff; }
.bio-photo { display:table-cell; width:58px; border-right:1px solid #E8E6E1; vertical-align:middle; text-align:center; padding:4px; background:#F5F4F0; }
.bio-photo img { width:50px; height:58px; object-fit:cover; border-radius:4px; display:block; }
.bio-photo-fb { width:50px; height:58px; background:#E8E6E1; border-radius:4px; display:inline-block; line-height:58px; text-align:center; font-size:7px; color:#999; }
.bio-data { display:table-cell; vertical-align:middle; }
.bio-data table { width:100%; border-collapse:collapse; }
.bio-data td { font-size:7.5px; padding:2.5px 7px; border-bottom:1px solid #F5F4F0; }
.bio-data tr:last-child td { border-bottom:none; }
.bl { color:#999999; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; font-size:6.5px; width:88px; background:#F5F4F0; }
.bv { color:#111111; font-weight:600; }

/* ── MAIN TWO-COLUMN BODY ─────────────────────────────────────────────────── */
/* 64% results | 36% traits — mirrors reference layout */
.body { display:table; width:100%; border-collapse:collapse; margin-bottom:6px; }
.col-l { display:table-cell; vertical-align:top; width:64%; padding-right:6px; }
.col-r { display:table-cell; vertical-align:top; width:36%; }

/* ── SECTION LABEL ──────────────────────────────────────────────────────── */
.sec-label {
    font-size:6.5px; font-weight:700; color:#1A56FF;
    text-transform:uppercase; letter-spacing:0.1em;
    padding:0 0 3px; border-bottom:1.5px solid #1A56FF;
    margin-bottom:0; display:block;
}

/* ── RESULTS TABLE ──────────────────────────────────────────────────────── */
.rt { width:100%; border-collapse:collapse; }
.rt thead tr { background:#0E0E0E; }
.rt th {
    font-size:6.5px; font-weight:600; color:rgba(255,255,255,0.75);
    text-transform:uppercase; letter-spacing:0.07em;
    padding:4px 4px; text-align:center; border:none;
}
.rt th.lft { text-align:left; padding-left:7px; }
.rt td {
    padding:3px 4px; font-size:7.5px;
    border-bottom:1px solid #F5F4F0;
    text-align:center; color:#111111;
}
.rt td.lft { text-align:left; font-weight:600; padding-left:7px; }
.rt tbody tr:nth-child(even) td { background:#FAFAF8; }
.rt tbody tr:last-child td { border-bottom:none; }

/* Remark chips — portal colour palette */
.chip { font-size:6.5px; font-weight:700; padding:1px 5px; border-radius:20px; display:inline-block; }
.chip-d { background:rgba(26,86,255,0.10); color:#1A56FF; }       /* Distinction  */
.chip-e { background:rgba(21,128,61,0.10); color:#15803D; }        /* Excellent    */
.chip-v { background:rgba(21,128,61,0.07); color:#166534; }        /* Very Good    */
.chip-g { background:rgba(180,83,9,0.08);  color:#B45309; }        /* Good         */
.chip-a { background:rgba(190,18,60,0.07); color:#BE123C; }        /* Average      */
.chip-b { background:rgba(190,18,60,0.12); color:#9F1239; }        /* Below Avg    */

/* ── SUMMARY STRIP ──────────────────────────────────────────────────────── */
.sum { display:table; width:100%; border-collapse:collapse; margin-top:4px; border:1px solid #E8E6E1; border-radius:6px; overflow:hidden; }
.sum-cell { display:table-cell; text-align:center; padding:4px 3px; border-right:1px solid #E8E6E1; }
.sum-cell:last-child { border-right:none; }
.sum-lbl { font-size:6px; font-weight:700; color:#999999; text-transform:uppercase; letter-spacing:0.06em; display:block; }
.sum-num { font-size:12px; font-weight:700; color:#0E0E0E; display:block; margin-top:1px; line-height:1; }
/* Highlight the student's total */
.sum-cell.accent { background:rgba(26,86,255,0.05); }
.sum-cell.accent .sum-num { color:#1A56FF; }

/* ── GRADE KEY ──────────────────────────────────────────────────────────── */
.gkey { display:table; width:100%; border-collapse:collapse; margin-top:4px; border:1px solid #E8E6E1; border-radius:6px; overflow:hidden; }
.gkey-hd { display:table-cell; background:#0E0E0E; color:rgba(255,255,255,0.6); font-size:6px; font-weight:700;
    text-transform:uppercase; letter-spacing:0.08em; padding:3px 5px; vertical-align:middle; width:40px; }
.gkey-body { display:table-cell; }
.gkey-body table { width:100%; border-collapse:collapse; }
.gkey-body td { font-size:6.5px; padding:2.5px 4px; border-left:1px solid #F5F4F0; text-align:center; line-height:1.3; }

/* ── TRAITS PANEL (right column) ────────────────────────────────────────── */
.tt { width:100%; border-collapse:collapse; margin-bottom:4px; }
.tt thead tr { background:#0E0E0E; }
.tt th { font-size:6.5px; font-weight:600; color:rgba(255,255,255,0.75); text-transform:uppercase;
    letter-spacing:0.06em; padding:4px 5px; text-align:left; border:none; }
.tt th.sc { text-align:center; width:16px; }
.tt td { padding:2.5px 5px; font-size:7.5px; border-bottom:1px solid #F5F4F0; }
.tt td.sc { text-align:center; font-weight:700; width:16px; }
.tt tbody tr:nth-child(even) td { background:#FAFAF8; }
.tt tbody tr:last-child td { border-bottom:none; }

/* Score dot colours */
.s0 { color:#E8E6E1; }
.s1 { color:#BE123C; } .s2 { color:#B45309; } .s3 { color:#1A56FF; }
.s4 { color:#15803D; } .s5 { color:#0E0E0E; font-weight:700; }

/* Key rating compact table */
.krt { width:100%; border-collapse:collapse; margin-top:4px; border:1px solid #E8E6E1; border-radius:4px; overflow:hidden; }
.krt th { background:#0E0E0E; color:rgba(255,255,255,0.6); font-size:6px; font-weight:700;
    text-transform:uppercase; letter-spacing:0.08em; padding:3px 5px; text-align:left; }
.krt td { font-size:7px; padding:2px 5px; border-bottom:1px solid #F5F4F0; }
.krt tr:last-child td { border-bottom:none; }
.krt td.kv { font-weight:700; text-align:center; background:#F5F4F0; width:16px; color:#1A56FF; }

/* ── COMMENTS BAND ──────────────────────────────────────────────────────── */
.cmt { display:table; width:100%; border-collapse:collapse; margin-bottom:6px; }
.cmt-cell { display:table-cell; vertical-align:top; padding-right:5px; width:50%; }
.cmt-cell:last-child { padding-right:0; padding-left:5px; }
.cmt-box { border:1px solid #E8E6E1; border-radius:6px; padding:5px 7px; background:#FAFAF8; }
.cmt-lbl { font-size:6px; font-weight:700; color:#1A56FF; text-transform:uppercase; letter-spacing:0.09em; margin-bottom:2px; }
.cmt-txt { font-size:7.5px; color:#111111; line-height:1.55; font-style:italic; }

/* ── SIGNATURES ──────────────────────────────────────────────────────────── */
.sigs { display:table; width:100%; margin-top:6px; }
.sig  { display:table-cell; text-align:center; width:33%; padding:0 10px; }
.sig-ln { border-top:1px solid #E8E6E1; margin-bottom:2px; }
.sig-lb { font-size:6.5px; color:#999999; }

/* ── FOOTER ──────────────────────────────────────────────────────────────── */
.footer {
    margin-top:5px; padding-top:4px; border-top:1px solid #F5F4F0;
    font-size:6px; color:#999999; text-align:center; letter-spacing:0.04em;
}
.footer span { color:#1A56FF; font-weight:600; }
</style>
</head>
<body>

@php
    function chipClass(string $r): string {
        return match($r) {
            'Distinction'   => 'chip chip-d',
            'Excellent'     => 'chip chip-e',
            'Very Good'     => 'chip chip-v',
            'Good'          => 'chip chip-g',
            'Average'       => 'chip chip-a',
            default         => 'chip chip-b',
        };
    }
@endphp

{{-- ══ HEADER ══ --}}
<div class="hdr">
    <div class="hdr-l">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo">
        @else
            <div class="hdr-l-fb">{{ strtoupper(substr($schoolName,0,1)) }}</div>
        @endif
    </div>
    <div class="hdr-m">
        <div class="hdr-school">{{ $schoolName }}</div>
        <div class="hdr-addr">{{ $schoolAddress }}</div>
        <div class="hdr-sub">Student Report Card</div>
    </div>
    <div class="hdr-r">
        <table class="meta-tbl">
            <tr>
                <td class="meta-lbl">Next Term Begins</td>
                <td class="meta-val">{{ $term->next_term_begins?->format('d M Y') ?? '—' }}</td>
            </tr>
            <tr>
                <td class="meta-lbl">Session</td>
                <td class="meta-val">{{ $term->session->name }}</td>
            </tr>
            <tr>
                <td class="meta-lbl">Term</td>
                <td class="meta-val">{{ strtoupper($term->name) }} TERM</td>
            </tr>
            <tr>
                <td class="meta-lbl">Class</td>
                <td class="meta-val">{{ strtoupper($enrolment?->schoolClass?->display_name ?? '—') }}</td>
            </tr>
            <tr>
                <td class="meta-lbl">Status</td>
                <td class="meta-val">
                    @if($passStatus === 'PASS') <span class="pill-pass">PASS</span>
                    @elseif($passStatus === 'FAIL') <span class="pill-fail">FAIL</span>
                    @else —
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
<div class="hdr-rule"></div>

{{-- ══ BIO STRIP ══ --}}
<div class="bio">
    <div class="bio-photo">
        @if($photoBase64)
            <img src="{{ $photoBase64 }}" alt="Photo">
        @else
            <div class="bio-photo-fb">No Photo</div>
        @endif
    </div>
    <div class="bio-data">
        <table>
            <tr>
                <td class="bl">Name</td>
                <td class="bv" style="font-size:8.5px;font-weight:700;" colspan="5">{{ strtoupper($student->full_name) }}</td>
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
                <td class="bv" colspan="5"> </td>
            </tr>
        </table>
    </div>
</div>

{{-- ══ MAIN BODY ══ --}}
<div class="body">

    {{-- Left column: results --}}
    <div class="col-l">
        <span class="sec-label">Academic Performance &nbsp;—&nbsp; Max Mark: CA (40) + Exam (60) = 100</span>
        <table class="rt">
            <thead>
                <tr>
                    <th class="lft">Subject</th>
                    <th>CA<br><span style="font-weight:400;font-size:5.5px;opacity:0.6;">/40</span></th>
                    <th>Exam<br><span style="font-weight:400;font-size:5.5px;opacity:0.6;">/60</span></th>
                    <th>Total<br><span style="font-weight:400;font-size:5.5px;opacity:0.6;">/100</span></th>
                    <th>Class<br><span style="font-weight:400;font-size:5.5px;opacity:0.6;">Ave</span></th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $result)
                <tr>
                    <td class="lft">{{ $result->subject->name }}</td>
                    <td>{{ $result->ca_score ?? '—' }}</td>
                    <td>{{ $result->exam_score ?? '—' }}</td>
                    <td style="font-weight:700;">{{ $result->total ?? '—' }}</td>
                    <td style="color:#999999;">{{ $result->class_average ? number_format($result->class_average,1) : '—' }}</td>
                    <td>
                        @if($result->remark)
                            <span class="{{ chipClass($result->remark) }}">{{ $result->remark }}</span>
                        @else
                            <span style="color:#999;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary strip --}}
        @if($results->isNotEmpty())
        <div class="sum">
            <div class="sum-cell accent">
                <span class="sum-lbl">Total Mark</span>
                <span class="sum-num">{{ $results->sum('total') }}</span>
            </div>
            <div class="sum-cell">
                <span class="sum-lbl">Class Lowest (LS)</span>
                <span class="sum-num">{{ $classLowest ?? '—' }}</span>
            </div>
            <div class="sum-cell">
                <span class="sum-lbl">Class Highest (HS)</span>
                <span class="sum-num">{{ $classHighest ?? '—' }}</span>
            </div>
            <div class="sum-cell">
                <span class="sum-lbl">Average Score</span>
                <span class="sum-num">{{ $average }}%</span>
            </div>
        </div>
        @endif

        {{-- Grade key --}}
        <div class="gkey">
            <div class="gkey-hd">Grade<br>Scale</div>
            <div class="gkey-body">
                <table>
                    <tr>
                        <td><strong>A+</strong> 90–100<br>Distinction</td>
                        <td><strong>A</strong> 70–89<br>Excellent</td>
                        <td><strong>B</strong> 60–69<br>Very Good</td>
                        <td><strong>C</strong> 50–59<br>Good</td>
                        <td><strong>D</strong> 40–49<br>Average</td>
                        <td><strong>E</strong> 0–39<br>Below Avg</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Right column: traits --}}
    <div class="col-r">

        <span class="sec-label">Psychomotor Skills</span>
        <table class="tt" style="margin-bottom:4px;">
            <thead><tr><th>Skill</th><th class="sc">&#9679;</th></tr></thead>
            <tbody>
                @foreach($psychomotorDef as $key => $label)
                @php $sc = $traitScores[$key] ?? null; @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="sc s{{ $sc ?? 0 }}">{{ $sc ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <span class="sec-label">Affective Areas</span>
        <table class="tt" style="margin-bottom:4px;">
            <thead><tr><th>Trait</th><th class="sc">&#9679;</th></tr></thead>
            <tbody>
                @foreach($affectiveDef as $key => $label)
                @php $sc = $traitScores[$key] ?? null; @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="sc s{{ $sc ?? 0 }}">{{ $sc ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="krt">
            <tr><th colspan="2">Key Rating</th></tr>
            <tr><td>Very Good</td><td class="kv">5</td></tr>
            <tr><td>Good</td>     <td class="kv">4</td></tr>
            <tr><td>Fair</td>     <td class="kv">3</td></tr>
            <tr><td>Poor</td>     <td class="kv">2</td></tr>
            <tr><td>N/A</td>      <td class="kv" style="color:#999;">1</td></tr>
        </table>

    </div>
</div>

{{-- ══ COMMENTS ══ --}}
<div class="cmt">
    <div class="cmt-cell">
        <div class="cmt-box">
            <div class="cmt-lbl">Class Teacher's Comment</div>
            <div class="cmt-txt">{{ $termComment?->teacher_comment ?? '—' }}</div>
        </div>
    </div>
    <div class="cmt-cell">
        <div class="cmt-box">
            <div class="cmt-lbl">Head Teacher's Comment</div>
            <div class="cmt-txt">{{ $termComment?->head_teacher_comment ?? '—' }}</div>
        </div>
    </div>
</div>

{{-- ══ SIGNATURES ══ --}}
<div class="sigs">
    <div class="sig"><div class="sig-ln"></div><div class="sig-lb">Class Teacher</div></div>
    <div class="sig"><div class="sig-ln"></div><div class="sig-lb">Head Teacher / Principal</div></div>
    <div class="sig"><div class="sig-ln"></div><div class="sig-lb">Date</div></div>
</div>

<div class="footer">
    <span>{{ $schoolName }}</span> &nbsp;&middot;&nbsp; Generated {{ now()->format('d M Y') }}
    &nbsp;&middot;&nbsp; connect.nurturevilleschool.org
</div>

</body>
</html>
