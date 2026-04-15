{{-- Deploy to: resources/views/pdf/report-card-preschool.blade.php --}}
{{-- Nursery / preschool classes (result_type = remark_only) --}}
{{-- Layout: compact two-column body matching primary card design system --}}
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
    padding:18px 20px 14px;
    line-height:1.4;
}

/* ── PORTAL COLOUR TOKENS ── */
/* accent:  #1A56FF | bg:    #F5F4F0 | border: #E8E6E1 */
/* success: #15803D | danger:#BE123C | text-3: #999999 */
/* header:  #3D4A5C (slate, good white contrast)        */

/* ── HEADER ── */
.hdr { display:table; width:100%; margin-bottom:8px; }
.hdr-l { display:table-cell; width:46px; vertical-align:middle; }
.hdr-l img { width:42px; height:42px; object-fit:cover; border-radius:6px; }
.hdr-l-fb { width:42px; height:42px; background:#1A56FF; color:#fff; font-size:18px; font-weight:700; text-align:center; line-height:42px; border-radius:6px; }
.hdr-m { display:table-cell; vertical-align:middle; padding-left:9px; }
.hdr-school { font-size:15px; font-weight:700; color:#111111; letter-spacing:-0.01em; text-transform:uppercase; }
.hdr-addr   { font-size:7px; color:#999999; margin-top:1px; }
.hdr-sub    { font-size:7px; color:#1A3A2A; font-weight:600; margin-top:2px; letter-spacing:0.04em; text-transform:uppercase; }
.hdr-r { display:table-cell; vertical-align:middle; text-align:right; width:160px; }
.meta-tbl { border-collapse:collapse; margin-left:auto; border:1px solid #E8E6E1; border-radius:8px; overflow:hidden; }
.meta-tbl td { font-size:7px; padding:2px 6px; border-bottom:1px solid #E8E6E1; }
.meta-tbl tr:last-child td { border-bottom:none; }
.meta-lbl { color:#999999; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; background:#F5F4F0; width:92px; }
.meta-val { color:#111111; font-weight:600; min-width:68px; }
.hdr-rule { height:2px; background:linear-gradient(90deg,#1A56FF 0%,rgba(26,86,255,0.15) 100%); margin-bottom:8px; border-radius:1px; }

/* ── BIO STRIP ── */
.bio { display:table; width:100%; border:1px solid #E8E6E1; border-radius:8px; overflow:hidden; margin-bottom:7px; }
.bio-photo { display:table-cell; width:58px; border-right:1px solid #E8E6E1; vertical-align:middle; text-align:center; padding:4px; background:#F5F4F0; }
.bio-photo img { width:50px; height:58px; object-fit:cover; border-radius:4px; display:block; }
.bio-photo-fb { width:50px; height:58px; background:#E8E6E1; border-radius:4px; display:inline-block; line-height:58px; text-align:center; font-size:7px; color:#999; }
.bio-data { display:table-cell; vertical-align:middle; }
.bio-data table { width:100%; border-collapse:collapse; }
.bio-data td { font-size:7.5px; padding:2.5px 7px; border-bottom:1px solid #F5F4F0; }
.bio-data tr:last-child td { border-bottom:none; }
.bl { color:#999999; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; font-size:6.5px; width:88px; background:#F5F4F0; }
.bv { color:#111111; font-weight:600; }

/* ── SECTION LABEL ── */
.sec-label {
    font-size:6.5px; font-weight:700; color:#1A3A2A;
    text-transform:uppercase; letter-spacing:0.1em;
    padding:0 0 3px; border-bottom:1.5px solid #1A3A2A;
    margin-bottom:0; display:block;
}

/* ── MAIN BODY: eval left (~62%) | traits right (~38%) ── */
.body { display:table; width:100%; border-collapse:collapse; margin-bottom:5px; }
.col-l { display:table-cell; vertical-align:top; width:62%; padding-right:6px; }
.col-r { display:table-cell; vertical-align:top; width:38%; }

/* ── EVALUATION TABLE (left column) ── */
.et { width:100%; border-collapse:collapse; }
.et thead tr { background:#3D4A5C; }
.et th {
    font-size:6.5px; font-weight:600; color:rgba(255,255,255,0.85);
    text-transform:uppercase; letter-spacing:0.07em;
    padding:4px 5px; text-align:left; border:none;
}
.et th.rc { text-align:center; width:56px; }
.et td {
    padding:3px 5px; font-size:7.5px;
    border-bottom:1px solid #F5F4F0;
    vertical-align:top; line-height:1.45;
}
.et td.sn { font-weight:700; width:18%; color:#111111; }
.et td.ev { color:#444444; font-size:7px; }
.et tbody tr:nth-child(even) td { background:#FAFAF8; }
.et tbody tr:last-child td { border-bottom:none; }

/* Remark chips — same palette as primary */
.chip { font-size:6.5px; font-weight:700; padding:1px 5px; border-radius:20px; display:inline-block; }
.chip-e { background:rgba(21,128,61,0.10);  color:#15803D; }   /* Excellent  */
.chip-v { background:rgba(21,128,61,0.07);  color:#166534; }   /* Very Good  */
.chip-g { background:rgba(180,83,9,0.08);   color:#B45309; }   /* Good       */
.chip-f { background:rgba(190,18,60,0.07);  color:#BE123C; }   /* Fair       */
.chip-p { background:rgba(190,18,60,0.12);  color:#9F1239; }   /* Poor       */

/* ── TRAITS PANEL (right column) ── */
.tt { width:100%; border-collapse:collapse; margin-bottom:4px; }
.tt thead tr { background:#3D4A5C; }
.tt th { font-size:6.5px; font-weight:600; color:rgba(255,255,255,0.85);
    text-transform:uppercase; letter-spacing:0.06em;
    padding:4px 5px; text-align:left; border:none; }
.tt th.sc { text-align:center; width:16px; }
.tt td { padding:2.5px 5px; font-size:7.5px; border-bottom:1px solid #F5F4F0; }
.tt td.sc { text-align:center; font-weight:700; width:16px; }
.tt tbody tr:nth-child(even) td { background:#FAFAF8; }
.tt tbody tr:last-child td { border-bottom:none; }
.s0 { color:#E8E6E1; } .s1 { color:#BE123C; } .s2 { color:#B45309; }
.s3 { color:#1A56FF; } .s4 { color:#15803D; } .s5 { color:#111111; font-weight:700; }

/* Rating key */
.krt { width:100%; border-collapse:collapse; margin-top:4px; border:1px solid #E8E6E1; border-radius:4px; overflow:hidden; }
.krt th { background:#3D4A5C; color:rgba(255,255,255,0.75); font-size:6px; font-weight:700;
    text-transform:uppercase; letter-spacing:0.08em; padding:3px 5px; text-align:left; }
.krt td { font-size:7px; padding:2px 5px; border-bottom:1px solid #F5F4F0; }
.krt tr:last-child td { border-bottom:none; }
.krt td.kv { font-weight:700; text-align:center; background:#F5F4F0; width:16px; color:#1A56FF; }

/* ── COMMENTS ── */
.cmt { display:table; width:100%; border-collapse:collapse; margin-bottom:5px; }
.cmt-cell { display:table-cell; vertical-align:top; padding-right:5px; width:50%; }
.cmt-cell:last-child { padding-right:0; padding-left:5px; }
.cmt-box { border:1px solid #E8E6E1; border-radius:6px; padding:5px 7px; background:#FAFAF8; }
.cmt-lbl { font-size:6px; font-weight:700; color:#1A3A2A; text-transform:uppercase; letter-spacing:0.09em; margin-bottom:2px; }
.cmt-txt { font-size:7.5px; color:#111111; line-height:1.55; font-style:italic; }

/* ── SIGNATURES ── */
.sigs { display:table; width:100%; margin-top:5px; }
.sig  { display:table-cell; text-align:center; width:33%; padding:0 10px; }
.sig-ln { border-top:1px solid #E8E6E1; margin-bottom:2px; }
.sig-lb { font-size:6.5px; color:#999999; }

/* ── FOOTER ── */
.footer { margin-top:5px; padding-top:4px; border-top:1px solid #F5F4F0; font-size:6px; color:#999999; text-align:center; letter-spacing:0.04em; }
.footer span { color:#1A3A2A; font-weight:600; }
</style>
</head>
<body>

@php
    function preChip(string $r): string {
        return match(strtolower(trim($r))) {
            'excellent'  => 'chip chip-e',
            'very good'  => 'chip chip-v',
            'good'       => 'chip chip-g',
            'fair'       => 'chip chip-f',
            default      => 'chip chip-p',
        };
    }
@endphp

{{-- HEADER --}}
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
        </table>
    </div>
</div>
<div class="hdr-rule"></div>

{{-- BIO STRIP --}}
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

{{-- MAIN BODY --}}
<div class="body">

    {{-- Left: Subject evaluations --}}
    <div class="col-l">
        <span class="sec-label">Subject Evaluation</span>
        <table class="et">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Evaluation</th>
                    <th class="rc">Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $result)
                <tr>
                    <td class="sn">{{ $result->subject->name }}</td>
                    <td class="ev">{{ $result->admin_comment ?: '—' }}</td>
                    <td style="text-align:center; vertical-align:middle;">
                        @if($result->remark)
                            <span class="{{ preChip($result->remark) }}">{{ strtoupper($result->remark) }}</span>
                        @else
                            <span style="color:#999;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;color:#999;padding:8px;">No evaluations recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Right: Traits --}}
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
            <tr><th colspan="2">Skills Rating Key</th></tr>
            <tr><td>Excellent</td> <td class="kv">5</td></tr>
            <tr><td>Very Good</td> <td class="kv">4</td></tr>
            <tr><td>Good</td>      <td class="kv">3</td></tr>
            <tr><td>Fair</td>      <td class="kv">2</td></tr>
            <tr><td>Poor</td>      <td class="kv" style="color:#BE123C;">1</td></tr>
        </table>

    </div>
</div>

{{-- COMMENTS --}}
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

{{-- SIGNATURES --}}
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
