{{-- Updated Preschool Report: Header matched to Primary Section --}}
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
    line-height:1.2;
}

/* ── HEADER (Matched to Primary) ── */
.hdr { width:100%; border-collapse:collapse; margin-bottom:6px; }
.hdr td { vertical-align:middle; }
.logo-img { width:42px; height:42px; object-fit:cover; border-radius:5px; }
.logo-fb  { width:42px; height:42px; background:#1A56FF; color:#fff; font-size:18px; font-weight:700; text-align:center; line-height:42px; border-radius:5px; }
.school-name { font-size:15px; font-weight:700; color:#111111; text-transform:uppercase; letter-spacing:-0.01em; }
.school-addr { font-size:7px; color:#999999; margin-top:1px; }
.school-sub  { font-size:7px; color:#1A3A2A; font-weight:700; margin-top:2px; text-transform:uppercase; }

/* Meta table - Right Side */
.meta { border-collapse:collapse; border:1px solid #E8E6E1; }
.meta td { font-size:6.5px; padding:2px 5px; border-bottom:1px solid #E8E6E1; border-left:1px solid #E8E6E1; }
.ml { color:#999999; font-weight:700; text-transform:uppercase; background:#F5F4F0; width:65px; }
.mv { color:#111111; font-weight:700; }

.rule { height:1px; background:#E8E6E1; margin-bottom:8px; }

/* ── BIO ── */
.bio { width:100%; border-collapse:collapse; border:1px solid #E8E6E1; margin-bottom:8px; }
.bio-photo-cell { width:60px; background:#F5F4F0; text-align:center; padding:4px; border-right:1px solid #E8E6E1; }
.bio-photo-img { width:52px; height:60px; object-fit:cover; display:block; border-radius:2px; }
.bio-inner td { font-size:7.5px; padding:3px 6px; border-bottom:1px solid #F5F4F0; }
.bl { color:#999999; font-weight:700; text-transform:uppercase; font-size:6.5px; background:#F5F4F0; width:15%; }
.bv { color:#111111; font-weight:700; }

/* ── LAYOUT ── */
.sec { font-size:10px; font-weight:800; color:#FFFFFF; text-transform:uppercase; padding:3px 8px; background:#3D4A5C; margin-bottom:0; }
.body-tbl { width:100%; border-collapse:collapse; table-layout: fixed; }
.et { width:100%; border-collapse:collapse; page-break-inside: auto; }
.et tr { page-break-inside: avoid; }
.et td { border:1px solid #E8E6E1; padding:4px 5px; vertical-align:top; }

.tt { width:100%; border-collapse:collapse; border:1px solid #E8E6E1; }
.tt td { padding:2px 4px; border-bottom:1px solid #F5F4F0; font-size:7.5px; }
.sc { text-align:center; font-weight:bold; width:22px; background:#F5F4F0; border-left:1px solid #E8E6E1; }

/* ── REMARKS ── */
.cmt-box { border:1px solid #E8E6E1; padding:6px; background:#F5F4F0; margin-top:5px; min-height:45px; }
.cmt-lbl { font-size:12px; font-weight:800; color:#3D4A5C; border-bottom:1px solid #E8E6E1; margin-bottom:3px; padding-bottom:2px; }
.cmt-txt { font-size:10px; font-style:italic; line-height:1.3; }

.sig-tbl { width:100%; margin-top:20px; }
.sig-ln { border-top:1px solid #333; margin-bottom:2px; }
.sig-lb { font-size:6.5px; text-align:center; font-weight:bold; color:#555; text-transform:uppercase; }
</style>
</head>
<body>

{{-- HEADER (Identical to Primary) --}}
<table class="hdr">
    <tr>
        <td style="width:48px;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
            @else
                <div class="logo-fb">{{ strtoupper(substr($schoolName,0,1)) }}</div>
            @endif
        </td>
        <td style="padding-left:10px;">
            <div class="school-name">{{ $schoolName }}</div>
            <div class="school-addr">{{ $schoolAddress }}</div>
            <div class="school-sub">Preschool Performance Report</div>
        </td>
        <td style="text-align:right;">
            <table class="meta" style="margin-left:auto;">
                <tr><td class="ml">Begins</td><td class="mv">{{ $term->next_term_begins?->format('d/m/Y') ?? '—' }}</td></tr>
                <tr><td class="ml">Session</td><td class="mv">{{ $term->session->name }}</td></tr>
                <tr><td class="ml">Term</td><td class="mv">{{ strtoupper($term->name) }}</td></tr>
                <tr><td class="ml">Class</td><td class="mv">{{ strtoupper($enrolment?->schoolClass?->display_name ?? '—') }}</td></tr>
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
                <img src="{{ $photoBase64 }}" class="bio-photo-img">
            @else
                <div style="font-size:6px; color:#999;">NO PHOTO</div>
            @endif
        </td>
        <td>
            <table class="bio-inner" style="width:100%;">
                <tr>
                    <td class="bl">NAME</td><td class="bv" colspan="3">{{ strtoupper($student->full_name) }}</td>
                    <td class="bl">ADM NO</td><td class="bv">{{ $student->admission_number }}</td>
                </tr>
                <tr>
                    <td class="bl">SEX</td><td class="bv">{{ $student->gender }}</td>
                    <td class="bl">D.O.B</td><td class="bv">{{ $student->date_of_birth?->format('d/m/Y') ?? '—' }}</td>
                    <td class="bl">OPENED</td><td class="bv">{{ $term->school_days_count ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="bl">PRESENT</td><td class="bv">{{ $enrolment?->times_present ?? '—' }}</td>
                    <td class="bl">ABSENT</td><td class="bv">{{ $enrolment?->times_absent ?? '—' }}</td>
                    <td class="bl">EXTRA</td><td class="bv">N/A</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="body-tbl">
    <tr>
        <td style="width:65%; vertical-align:top; padding-right:10px;">
            <div class="sec">Subject Evaluations</div>
            <table class="et">
                <tr style="background:#F5F4F0; font-weight:bold; font-size:7px; text-transform:uppercase;">
                    <td style="width:25%;">Subject</td><td>Detailed Assessment</td><td style="width:18%;">Remark</td>
                </tr>
                @foreach($results as $result)
                <tr>
                    <td style="font-weight:bold; color:#3D4A5C;">{{ $result->subject->name }}</td>
                    <td>{{ $result->admin_comment }}</td>
                    <td style="text-align:center; font-weight:bold;">{{ strtoupper($result->remark) }}</td>
                </tr>
                @endforeach
            </table>
        </td>
        <td style="width:35%; vertical-align:top;">
            <div class="sec">Psychomotor</div>
            <table class="tt">
                @foreach($psychomotorDef as $key => $label)
                <tr><td>{{ $label }}</td><td class="sc">{{ $traitScores[$key] ?? '—' }}</td></tr>
                @endforeach
            </table>
            <div class="sec">Affective</div>
            <table class="tt">
                @foreach($affectiveDef as $key => $label)
                <tr><td>{{ $label }}</td><td class="sc">{{ $traitScores[$key] ?? '—' }}</td></tr>
                @endforeach
            </table>
            <div class="sec">Rating Key</div>
            <table class="tt">
                <tr><td>Excellent</td><td class="sc">5</td></tr>
                <tr><td>Very Good</td><td class="sc">4</td></tr>
                <tr><td>Good</td><td class="sc">3</td></tr>
                <tr><td>Fair</td><td class="sc">2</td></tr>
                <tr><td>Needs Improvement</td><td class="sc">1</td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="sec">Observations & Remarks</div>
<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:50%; padding-right:5px;">
            <div class="cmt-box">
                <div class="cmt-lbl">Class Teacher</div>
                <div class="cmt-txt">{{ $termComment?->teacher_comment }}</div>
            </div>
        </td>
        <td style="width:50%; padding-left:5px;">
            <div class="cmt-box">
                <div class="cmt-lbl">Head Teacher</div>
                <div class="cmt-txt">{{ $termComment?->head_teacher_comment }}</div>
            </div>
        </td>
    </tr>
</table>

</body>
</html>
