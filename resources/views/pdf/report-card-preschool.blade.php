{{-- Updated for Nurtureville: Forces content onto Page 1 --}}
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
    padding:10px 20px;
    line-height:1.2;
}

@media print { @page { margin: 0; } }

/* ── HEADER ── */
.hdr { width:100%; border-collapse:collapse; margin-bottom:4px; }
.school-name { font-size:15px; font-weight:800; color:#B01E3E; text-transform:uppercase; }
.school-addr { font-size:7.5px; color:#555; }

.meta td { font-size:7px; padding:1px 4px; border:1px solid #EEE; }
.ml { background:#F9F9F7; font-weight:bold; width:70px; }

/* ── BIO (Compressed) ── */
.bio { width:100%; border-collapse:collapse; border:1px solid #EEE; margin-bottom:6px; }
.bio-photo-cell { width:55px; text-align:center; padding:2px; border-right:1px solid #EEE; }
.bio-photo-img { width:50px; height:55px; object-fit:cover; }
.bio-inner td { font-size:7.5px; padding:2px 5px; border-bottom:1px solid #EEE; }
.bl { color:#777; font-weight:700; background:#F9F9F7; width:12%; }

/* ── LAYOUT & TABLES ── */
.sec { font-size:7px; font-weight:800; color:#FFF; background:#3D4A5C; padding:3px 6px; margin-top:5px; }

.body-tbl { width:100%; border-collapse:collapse; table-layout: fixed; }
.et { width:100%; border-collapse:collapse; page-break-inside: auto; } /* Forces table to allow splitting */
.et tr { page-break-inside: avoid; page-break-after: auto; }
.et td { border:1px solid #EEE; padding:3px 5px; vertical-align:top; }

.tt { width:100%; border-collapse:collapse; border:1px solid #EEE; }
.tt td { padding:2px 4px; border-bottom:1px solid #EEE; font-size:7.5px; }
.sc { text-align:center; font-weight:bold; width:20px; background:#F9F9F7; }

/* ── REMARKS ── */
.cmt-box { border:1px solid #EEE; padding:5px; background:#FAFAF8; margin-top:5px; }
.cmt-lbl { font-size:7px; font-weight:800; color:#B01E3E; border-bottom:1px solid #EEE; margin-bottom:3px; }
.cmt-txt { font-size:8px; font-style:italic; }

.sig-tbl { width:100%; margin-top:15px; }
.sig-ln { border-top:1px solid #333; margin-bottom:2px; }
.sig-lb { font-size:6.5px; text-align:center; font-weight:bold; }
</style>
</head>
<body>

<table class="hdr">
    <tr>
        <td>
            <div class="school-name">{{ $schoolName }}</div>
            <div class="school-addr">{{ $schoolAddress }}</div>
            <div style="font-size:8px; font-weight:700; margin-top:2px;">PRESCHOOL PERFORMANCE REPORT</div>
        </td>
        <td style="text-align:right;">
            <table class="meta" style="margin-left:auto;">
                <tr><td class="ml">BEGINS</td><td>{{ $term->next_term_begins?->format('d/m/Y') ?? '—' }}</td></tr>
                <tr><td class="ml">SESSION</td><td>{{ $term->session->name }}</td></tr>
                <tr><td class="ml">TERM</td><td>{{ strtoupper($term->name) }}</td></tr>
                <tr><td class="ml">CLASS</td><td>{{ strtoupper($enrolment?->schoolClass?->display_name ?? '—') }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="bio">
    <tr>
        <td class="bio-photo-cell">
            @if($photoBase64) <img src="{{ $photoBase64 }}" class="bio-photo-img"> @else <div style="font-size:6px;">No Photo</div> @endif
        </td>
        <td>
            <table class="bio-inner" style="width:100%;">
                <tr>
                    <td class="bl">NAME</td><td colspan="3"><strong>{{ strtoupper($student->full_name) }}</strong></td>
                    <td class="bl">ADM NO</td><td>{{ $student->admission_number }}</td>
                </tr>
                <tr>
                    <td class="bl">SEX</td><td>{{ $student->gender }}</td>
                    <td class="bl">D.O.B</td><td>{{ $student->date_of_birth?->format('d/m/Y') ?? '—' }}</td>
                    <td class="bl">OPENED</td><td>{{ $term->school_days_count ?? '—' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="body-tbl">
    <tr>
        <td style="width:65%; vertical-align:top; padding-right:10px;">
            <div class="sec">SUBJECT EVALUATIONS</div>
            <table class="et">
                <tr style="background:#F5F4F0; font-weight:bold; font-size:7px;">
                    <td style="width:25%;">SUBJECT</td><td>DETAILED ASSESSMENT</td><td style="width:15%;">REMARK</td>
                </tr>
                @foreach($results as $result)
                <tr>
                    <td style="font-weight:bold; color:#B01E3E;">{{ $result->subject->name }}</td>
                    <td>{{ $result->admin_comment }}</td>
                    <td style="text-align:center; font-weight:bold;">{{ $result->remark }}</td>
                </tr>
                @endforeach
            </table>
        </td>
        <td style="width:35%; vertical-align:top;">
            <div class="sec">PSYCHOMOTOR</div>
            <table class="tt">
                @foreach($psychomotorDef as $key => $label)
                <tr><td>{{ $label }}</td><td class="sc">{{ $traitScores[$key] ?? '—' }}</td></tr>
                @endforeach
            </table>
            <div class="sec">AFFECTIVE</div>
            <table class="tt">
                @foreach($affectiveDef as $key => $label)
                <tr><td>{{ $label }}</td><td class="sc">{{ $traitScores[$key] ?? '—' }}</td></tr>
                @endforeach
            </table>
            <div class="sec">RATING KEY</div>
            <table class="tt">
                <tr><td>Excellent / Very Good</td><td class="sc">5-4</td></tr>
                <tr><td>Good / Fair</td><td class="sc">3-2</td></tr>
                <tr><td>Needs Improvement</td><td class="sc">1</td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="sec">OBSERVATIONS & REMARKS</div>
<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:50%; padding-right:5px;">
            <div class="cmt-box">
                <div class="cmt-lbl">CLASS TEACHER</div>
                <div class="cmt-txt">{{ $termComment?->teacher_comment }}</div>
            </div>
        </td>
        <td style="width:50%; padding-left:5px;">
            <div class="cmt-box">
                <div class="cmt-lbl">HEAD TEACHER</div>
                <div class="cmt-txt">{{ $termComment?->head_teacher_comment }}</div>
            </div>
        </td>
    </tr>
</table>

<table class="sig-tbl">
    <tr>
        <td style="width:33%; padding:0 10px;"><div class="sig-ln"></div><div class="sig-lb">Class Teacher</div></td>
        <td style="width:33%; padding:0 10px;"><div class="sig-ln"></div><div class="sig-lb">Head Teacher</div></td>
        <td style="width:33%; padding:0 10px;"><div class="sig-ln"></div><div class="sig-lb">Parent</div></td>
    </tr>
</table>

</body>
</html>
