{{-- Deploy to: resources/views/pdf/report-card-primary.blade.php --}}
{{-- Used for: All non-remark-only classes (Grade 1, Grade 2, etc.) --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family:'DejaVu Sans', Arial, sans-serif;
    font-size:8.5px;
    color:#111;
    background:#fff;
    padding:14px 16px;
}

/* HEADER */
.hdr { display:table; width:100%; border-bottom:2.5px solid #1c2b4a; padding-bottom:6px; margin-bottom:6px; }
.hdr-logo  { display:table-cell; width:52px; vertical-align:middle; }
.hdr-logo img { width:48px; height:48px; object-fit:cover; }
.hdr-logo-fb { width:48px; height:48px; background:#1c2b4a; color:#fff; font-size:18px; font-weight:700; text-align:center; line-height:48px; border-radius:3px; }
.hdr-school { display:table-cell; vertical-align:middle; padding-left:8px; }
.hdr-school-name { font-size:16px; font-weight:700; color:#1c2b4a; text-transform:uppercase; letter-spacing:0.01em; }
.hdr-school-addr { font-size:7.5px; color:#666; margin-top:1px; }
.hdr-meta  { display:table-cell; vertical-align:middle; text-align:right; }
.hdr-meta table { border-collapse:collapse; margin-left:auto; }
.hdr-meta td { font-size:7.5px; padding:1px 4px; border:1px solid #ccc; }
.hdr-meta td.lbl { background:#f0f0f0; font-weight:700; color:#444; text-transform:uppercase; letter-spacing:0.04em; }
.hdr-meta td.val { font-weight:600; color:#1c2b4a; min-width:80px; }
.status-pass { background:#dcfce7; color:#14532d; font-weight:700; padding:0 5px; border-radius:2px; }
.status-fail { background:#fee2e2; color:#991b1b; font-weight:700; padding:0 5px; border-radius:2px; }

/* BIO BAND */
.bio-band { display:table; width:100%; border:1px solid #ccc; border-collapse:collapse; margin-bottom:5px; }
.bio-photo-cell { display:table-cell; width:62px; border-right:1px solid #ccc; vertical-align:middle; text-align:center; padding:3px; }
.bio-photo-cell img { width:56px; height:64px; object-fit:cover; display:block; }
.bio-photo-fb { width:56px; height:64px; background:#eee; display:inline-block; font-size:7px; color:#aaa; border:1px solid #ddd; text-align:center; line-height:64px; }
.bio-fields { display:table-cell; vertical-align:top; }
.bio-fields table { width:100%; border-collapse:collapse; }
.bio-fields td { font-size:7.5px; padding:2.5px 5px; border:1px solid #ddd; }
.bio-lbl { background:#f5f5f5; font-weight:700; color:#444; text-transform:uppercase; letter-spacing:0.04em; width:88px; }
.bio-val { font-weight:600; color:#111; }

/* MAIN BODY SPLIT */
.body-outer  { display:table; width:100%; border-collapse:collapse; margin-bottom:4px; }
.col-results { display:table-cell; vertical-align:top; padding-right:5px; width:65%; }
.col-traits  { display:table-cell; vertical-align:top; width:35%; }

/* RESULTS TABLE */
.sec-head { font-size:7px; font-weight:700; color:#fff; background:#1c2b4a; text-transform:uppercase;
    letter-spacing:0.07em; padding:3px 5px; }
.rtable { width:100%; border-collapse:collapse; }
.rtable th { font-size:7px; font-weight:700; color:#fff; text-transform:uppercase;
    letter-spacing:0.04em; padding:3px 3px; text-align:center;
    background:#2e4070; border:1px solid #2e4070; }
.rtable th.lft { text-align:left; }
.rtable td { padding:2px 3px; font-size:7.5px; border:1px solid #ddd; text-align:center; }
.rtable td.lft { text-align:left; font-weight:600; }
.rtable tr:nth-child(even) td { background:#f8f8f8; }
.rm-D { color:#713f12; font-weight:700; }
.rm-E { color:#14532d; font-weight:700; }
.rm-V { color:#1e40af; font-weight:700; }
.rm-G { color:#0c4a6e; font-weight:700; }
.rm-A { color:#9a3412; font-weight:700; }
.rm-B { color:#991b1b; font-weight:700; }

/* SUMMARY STRIP */
.sum-strip { display:table; width:100%; border-collapse:collapse; margin-top:3px; }
.sum-cell  { display:table-cell; border:1px solid #ccc; text-align:center; padding:2px 3px; }
.sum-lbl   { font-size:6.5px; font-weight:700; color:#666; text-transform:uppercase; letter-spacing:0.04em; display:block; }
.sum-val   { font-size:11px; font-weight:700; color:#1c2b4a; display:block; margin-top:1px; }

/* GRADE KEY */
.gkey { display:table; width:100%; border-collapse:collapse; margin-top:3px; border:1px solid #ddd; }
.gkey-hdr { display:table-cell; background:#1c2b4a; color:#fff; font-size:6.5px; font-weight:700;
    text-transform:uppercase; letter-spacing:0.06em; padding:2px 4px; vertical-align:middle; width:46px; }
.gkey-items { display:table-cell; }
.gkey-items table { width:100%; border-collapse:collapse; }
.gkey-items td { font-size:7px; padding:2px 3px; border-left:1px solid #eee; text-align:center; line-height:1.4; }

/* TRAITS PANEL */
.ttable { width:100%; border-collapse:collapse; }
.ttable th { font-size:7px; font-weight:700; color:#fff; text-transform:uppercase;
    letter-spacing:0.04em; padding:3px 4px; background:#1c2b4a; border:1px solid #1c2b4a; text-align:left; }
.ttable th.sc { text-align:center; width:18px; }
.ttable td { padding:2px 4px; font-size:7.5px; border:1px solid #ddd; }
.ttable td.sc { text-align:center; font-weight:700; width:18px; }
.ttable tr:nth-child(even) td { background:#f8f8f8; }
.ts-0 { color:#bbb; } .ts-1 { color:#be123c; } .ts-2 { color:#b45309; }
.ts-3 { color:#0369a1; } .ts-4 { color:#15803d; } .ts-5 { color:#166534; font-weight:700; }

.key-tbl { width:100%; border-collapse:collapse; margin-top:3px; }
.key-tbl td { font-size:7px; padding:1.5px 4px; border:1px solid #ddd; }
.key-tbl td.kv { font-weight:700; text-align:center; background:#f0f0f0; width:16px; }

/* COMMENTS */
.cmt-band { display:table; width:100%; border-collapse:collapse; margin-top:4px; }
.cmt-cell { display:table-cell; vertical-align:top; padding-right:5px; width:50%; }
.cmt-cell:last-child { padding-right:0; padding-left:5px; }
.cmt-box  { border:1px solid #ddd; padding:3px 5px; }
.cmt-lbl  { font-size:6.5px; font-weight:700; color:#444; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:2px; }
.cmt-txt  { font-size:7.5px; color:#222; line-height:1.5; font-style:italic; }

/* SIGNATURES */
.sig-band { display:table; width:100%; margin-top:6px; }
.sig-cell { display:table-cell; text-align:center; width:33%; padding:0 8px; }
.sig-line { border-top:1px solid #888; margin-bottom:2px; }
.sig-lbl  { font-size:7px; color:#666; }

.footer { margin-top:4px; padding-top:3px; border-top:1px solid #eee; font-size:6.5px; color:#bbb; text-align:center; }
</style>
</head>
<body>

@php
    function rmClass(string $r): string {
        return match($r) {
            'Distinction'   => 'rm-D', 'Excellent'     => 'rm-E',
            'Very Good'     => 'rm-V', 'Good'          => 'rm-G',
            'Average'       => 'rm-A', default         => 'rm-B',
        };
    }
    function rmShort(string $r): string {
        return match($r) {
            'Distinction'   => 'DISTINCTION', 'Excellent'     => 'EXCELLENT',
            'Very Good'     => 'VERY GOOD',   'Good'          => 'GOOD',
            'Average'       => 'AVERAGE',     'Below Average' => 'BELOW AVG',
            default         => strtoupper($r),
        };
    }
@endphp

{{-- HEADER --}}
<div class="hdr">
    <div class="hdr-logo">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo">
        @else
            <div class="hdr-logo-fb">{{ strtoupper(substr($schoolName,0,1)) }}</div>
        @endif
    </div>
    <div class="hdr-school">
        <div class="hdr-school-name">{{ $schoolName }}</div>
        <div class="hdr-school-addr">{{ $schoolAddress }}</div>
    </div>
    <div class="hdr-meta">
        <table>
            <tr>
                <td class="lbl">Next Term Begins</td>
                <td class="val">{{ $term->next_term_begins?->format('Y-m-d') ?? '—' }}</td>
            </tr>
            <tr>
                <td class="lbl">Session</td>
                <td class="val">{{ $term->session->name }}</td>
            </tr>
            <tr>
                <td class="lbl">Term</td>
                <td class="val">{{ strtoupper($term->name) }} TERM</td>
            </tr>
            <tr>
                <td class="lbl">Class</td>
                <td class="val">{{ strtoupper($enrolment?->schoolClass?->display_name ?? '—') }}</td>
            </tr>
            <tr>
                <td class="lbl">Status</td>
                <td class="val">
                    @if($passStatus === 'PASS') <span class="status-pass">PASS</span>
                    @elseif($passStatus === 'FAIL') <span class="status-fail">FAIL</span>
                    @else —
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- BIO BAND --}}
<div class="bio-band">
    <div class="bio-photo-cell">
        @if($photoBase64)
            <img src="{{ $photoBase64 }}" alt="Photo">
        @else
            <div class="bio-photo-fb">No Photo</div>
        @endif
    </div>
    <div class="bio-fields">
        <table>
            <tr>
                <td class="bio-lbl">Name</td>
                <td class="bio-val" style="font-size:8.5px;font-weight:700;" colspan="3">{{ strtoupper($student->full_name) }}</td>
                <td class="bio-lbl">Adm. Number</td>
                <td class="bio-val">{{ $student->admission_number }}</td>
            </tr>
            <tr>
                <td class="bio-lbl">Sex</td>
                <td class="bio-val">{{ $student->gender }}</td>
                <td class="bio-lbl">Date of Birth</td>
                <td class="bio-val">{{ $student->date_of_birth?->format('d/m/Y') ?? '—' }}</td>
                <td class="bio-lbl">No. of Times School Opened</td>
                <td class="bio-val">{{ $term->school_days_count ?? '—' }}</td>
            </tr>
            <tr>
                <td class="bio-lbl">No. of Times Present</td>
                <td class="bio-val">{{ $enrolment?->times_present ?? '—' }}</td>
                <td class="bio-lbl">No. of Times Absent</td>
                <td class="bio-val">{{ $enrolment?->times_absent ?? '—' }}</td>
                <td class="bio-lbl">Extra Curricular</td>
                <td class="bio-val"> </td>
            </tr>
        </table>
    </div>
</div>

{{-- MAIN BODY --}}
<div class="body-outer">

    {{-- Left: Academic Results --}}
    <div class="col-results">
        <div class="sec-head">Max Mark Obtainable &nbsp;&nbsp;&nbsp; {{ strtoupper($term->name) }} Term Summary</div>
        <table class="rtable">
            <thead>
                <tr>
                    <th class="lft">Subject</th>
                    <th>CA<br><span style="font-weight:400;font-size:6px;">(40)</span></th>
                    <th>Exam<br><span style="font-weight:400;font-size:6px;">(60)</span></th>
                    <th>Total<br><span style="font-weight:400;font-size:6px;">(100)</span></th>
                    <th>Class<br>Ave</th>
                    <th>LS</th>
                    <th>HS</th>
                    <th>Teacher's Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $result)
                <tr>
                    <td class="lft">{{ $result->subject->name }}</td>
                    <td>{{ $result->ca_score ?? '—' }}</td>
                    <td>{{ $result->exam_score ?? '—' }}</td>
                    <td style="font-weight:700;">{{ $result->total ?? '—' }}</td>
                    <td>{{ $result->class_average ? number_format($result->class_average,1) : '—' }}</td>
                    <td>{{ $result->class_lowest  ?? '—' }}</td>
                    <td>{{ $result->class_highest ?? '—' }}</td>
                    <td class="{{ $result->remark ? rmClass($result->remark) : '' }}">
                        {{ $result->remark ? rmShort($result->remark) : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($results->isNotEmpty())
        <div class="sum-strip">
            <div class="sum-cell">
                <span class="sum-lbl">Total Mark</span>
                <span class="sum-val">{{ $results->sum('total') }}</span>
            </div>
            <div class="sum-cell">
                <span class="sum-lbl">Lowest Score (LS)</span>
                <span class="sum-val">{{ $results->min('total') ?? '—' }}</span>
            </div>
            <div class="sum-cell">
                <span class="sum-lbl">Highest Score (HS)</span>
                <span class="sum-val">{{ $results->max('total') ?? '—' }}</span>
            </div>
            <div class="sum-cell">
                <span class="sum-lbl">Average % Score</span>
                <span class="sum-val">{{ $average }}%</span>
            </div>
        </div>
        @endif

        <div class="gkey">
            <div class="gkey-hdr">Grading<br>Scale</div>
            <div class="gkey-items">
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

    {{-- Right: Traits --}}
    <div class="col-traits">
        <table class="ttable" style="margin-bottom:3px;">
            <thead>
                <tr><th>Psychomotor Skills</th><th class="sc"></th></tr>
            </thead>
            <tbody>
                @foreach($psychomotorDef as $key => $label)
                @php $sc = $traitScores[$key] ?? null; @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="sc ts-{{ $sc ?? 0 }}">{{ $sc ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="ttable">
            <thead>
                <tr><th>Affective Areas</th><th class="sc"></th></tr>
            </thead>
            <tbody>
                @foreach($affectiveDef as $key => $label)
                @php $sc = $traitScores[$key] ?? null; @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="sc ts-{{ $sc ?? 0 }}">{{ $sc ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="key-tbl">
            <tr>
                <td colspan="2" style="background:#1c2b4a;color:#fff;font-weight:700;font-size:6.5px;text-transform:uppercase;letter-spacing:0.05em;padding:2px 4px;">Key Rating</td>
            </tr>
            <tr><td>Excellent</td>    <td class="kv">5</td></tr>
            <tr><td>Very Good</td>    <td class="kv">4</td></tr>
            <tr><td>Good</td>         <td class="kv">3</td></tr>
            <tr><td>Fair</td>         <td class="kv">2</td></tr>
            <tr><td>Poor</td>         <td class="kv">1</td></tr>
            <tr><td>Not Applicable</td><td class="kv" style="color:#aaa;">—</td></tr>
        </table>
    </div>

</div>

{{-- COMMENTS --}}
<div class="cmt-band">
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
<div class="sig-band">
    <div class="sig-cell"><div class="sig-line"></div><div class="sig-lbl">Class Teacher</div></div>
    <div class="sig-cell"><div class="sig-line"></div><div class="sig-lbl">Head Teacher / Principal</div></div>
    <div class="sig-cell"><div class="sig-line"></div><div class="sig-lbl">Date</div></div>
</div>

<div class="footer">{{ $schoolName }} &middot; Generated {{ now()->format('d M Y') }}</div>

</body>
</html>
