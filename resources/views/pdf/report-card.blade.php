{{-- Deploy to: resources/views/pdf/report-card.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'DejaVu Sans', Arial, sans-serif; font-size:11px; color:#111; background:#fff; padding:32px; }
    .header { border-bottom:3px solid #1A56FF; padding-bottom:16px; margin-bottom:20px; display:table; width:100%; }
    .logo-cell { display:table-cell; width:56px; vertical-align:middle; }
    .logo { width:48px; height:48px; border-radius:50%; background:#1A56FF; color:#fff; font-size:20px; font-weight:700; text-align:center; line-height:48px; }
    .school-cell { display:table-cell; vertical-align:middle; padding-left:12px; }
    .school-name { font-size:17px; font-weight:700; letter-spacing:-0.02em; }
    .school-sub  { font-size:10px; color:#777; margin-top:2px; }
    .report-cell { display:table-cell; vertical-align:middle; text-align:right; }
    .report-label { font-size:14px; font-weight:700; color:#1A56FF; letter-spacing:-0.01em; }
    .report-term  { font-size:10px; color:#777; margin-top:3px; }
    .info-band { display:table; width:100%; background:#F5F4F0; border-radius:6px; padding:12px 16px; margin-bottom:20px; }
    .info-col  { display:table-cell; vertical-align:top; padding-right:20px; }
    .info-label { font-size:9px; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:3px; }
    .info-value { font-size:12px; font-weight:600; color:#111; }
    .info-mono  { font-family:'Courier New', monospace; font-size:11px; }
    .results-table { width:100%; border-collapse:collapse; margin-bottom:16px; }
    .results-table th { font-size:9px; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:0.07em; padding:8px 10px; text-align:left; background:#F5F4F0; border-bottom:1px solid #E8E6E1; }
    .results-table th.center { text-align:center; }
    .results-table td { padding:9px 10px; font-size:11px; border-bottom:1px solid #F0EEE9; }
    .results-table td.center { text-align:center; }
    .results-table .subtotal-row td { background:#F5F4F0; font-weight:700; font-size:12px; border-top:2px solid #E8E6E1; border-bottom:none; }
    .grade-pill { display:inline-block; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700; }
    .grade-A { background:#dcfce7; color:#15803D; }
    .grade-B { background:#dbeafe; color:#1A56FF; }
    .grade-C { background:#fef3c7; color:#B45309; }
    .grade-D { background:#fff7ed; color:#B45309; }
    .grade-E { background:#fef2f2; color:#BE123C; }
    .grade-F { background:#fee2e2; color:#BE123C; }
    .comment-cell { font-size:10px; color:#555; font-style:italic; max-width:120px; }
    .summary-row { display:table; width:100%; margin-bottom:20px; }
    .summary-card { display:table-cell; text-align:center; padding:12px; border:1px solid #E8E6E1; border-radius:6px; width:33%; }
    .summary-card + .summary-card { border-left:none; border-radius:0; }
    .summary-card:last-child { border-radius:0 6px 6px 0; }
    .summary-card:first-child { border-radius:6px 0 0 6px; }
    .sum-label { font-size:9px; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:0.07em; }
    .sum-value { font-size:18px; font-weight:700; color:#111; margin-top:4px; }
    .grade-key { background:#F5F4F0; border-radius:6px; padding:10px 14px; margin-bottom:16px; }
    .grade-key-title { font-size:9px; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:8px; }
    .grade-key-row { display:table; width:100%; }
    .grade-key-item { display:table-cell; text-align:center; }
    .gk-range { font-size:9px; color:#777; }
    .gk-grade { font-size:11px; font-weight:700; margin-top:1px; }
    .principal-remark { border:1px solid #E8E6E1; border-radius:6px; padding:12px 14px; margin-bottom:16px; background:#FAFAF8; }
    .remark-title { font-size:9px; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:6px; }
    .remark-text  { font-size:11px; color:#333; line-height:1.6; font-style:italic; }
    .signatures { display:table; width:100%; margin-top:24px; }
    .sig-cell { display:table-cell; width:33%; text-align:center; padding:0 8px; }
    .sig-line { border-top:1px solid #999; margin-bottom:4px; }
    .sig-label { font-size:9px; color:#777; }
    .footer { margin-top:16px; padding-top:10px; border-top:1px solid #E8E6E1; font-size:9px; color:#aaa; text-align:center; }
</style>
</head>
<body>

<div class="header">
    <div class="logo-cell"><div class="logo">N</div></div>
    <div class="school-cell">
        <div class="school-name">Nurtureville School</div>
        <div class="school-sub">Nurturing Minds, Building Futures</div>
    </div>
    <div class="report-cell">
        <div class="report-label">REPORT CARD</div>
        <div class="report-term">{{ $term->name }} Term · {{ $term->session->name }}</div>
    </div>
</div>

<div class="info-band">
    <div class="info-col">
        <div class="info-label">Student Name</div>
        <div class="info-value">{{ $student->full_name }}</div>
    </div>
    <div class="info-col">
        <div class="info-label">Admission No.</div>
        <div class="info-value info-mono">{{ $student->admission_number }}</div>
    </div>
    <div class="info-col">
        <div class="info-label">Class</div>
        <div class="info-value">{{ $enrolment?->schoolClass?->display_name ?? '—' }}</div>
    </div>
    <div class="info-col">
        <div class="info-label">Gender</div>
        <div class="info-value">{{ $student->gender }}</div>
    </div>
    <div class="info-col" style="padding-right:0">
        <div class="info-label">Term</div>
        <div class="info-value">{{ $term->name }}</div>
    </div>
</div>

@php $hasAnyComment = $results->whereNotNull('admin_comment')->isNotEmpty(); @endphp

@if($isRemarkOnly)
{{-- Nursery / remark-only layout: Subject | Teacher's Remark --}}
<table class="results-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Subject</th>
            <th>Teacher's Remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $i => $result)
        <tr>
            <td style="color:#aaa;width:24px;">{{ $i + 1 }}</td>
            <td style="font-weight:500;">{{ $result->subject->name }}</td>
            <td style="color:#333;">{{ $result->remark ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
{{-- Standard scored layout: CA | Exam | Total | Grade | Remark --}}
<table class="results-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Subject</th>
            <th class="center">CA (40)</th>
            <th class="center">Exam (60)</th>
            <th class="center">Total %</th>
            <th class="center">Grade</th>
            <th>Remark</th>
            @if($hasAnyComment)<th>Comment</th>@endif
        </tr>
    </thead>
    <tbody>
        @foreach($results as $i => $result)
        <tr>
            <td style="color:#aaa;width:24px;">{{ $i + 1 }}</td>
            <td style="font-weight:500;">{{ $result->subject->name }}</td>
            <td class="center">{{ $result->ca_score }}</td>
            <td class="center">{{ $result->exam_score }}</td>
            <td class="center" style="font-weight:700;">{{ $result->total }}</td>
            <td class="center">
                @if($result->grade)
                    <span class="grade-pill grade-{{ $result->grade }}">{{ $result->grade }}</span>
                @else — @endif
            </td>
            <td style="color:#555;">{{ $result->remark ?? '—' }}</td>
            @if($hasAnyComment)
                <td class="comment-cell">{{ $result->admin_comment ?? '' }}</td>
            @endif
        </tr>
        @endforeach
        @if($results->isNotEmpty())
        <tr class="subtotal-row">
            <td colspan="{{ $hasAnyComment ? 6 : 5 }}" style="text-align:right;padding-right:20px;">Average Score</td>
            <td class="center">{{ $average }}%</td>
            @if($hasAnyComment)<td></td>@endif
        </tr>
        @endif
    </tbody>
</table>
@endif

@if($results->isNotEmpty() && ! $isRemarkOnly)
<div class="summary-row">
    <div class="summary-card">
        <div class="sum-label">Subjects Taken</div>
        <div class="sum-value">{{ $subjectCount }}</div>
    </div>
    <div class="summary-card">
        <div class="sum-label">Total Score</div>
        <div class="sum-value">{{ $results->sum('total') }}</div>
    </div>
    <div class="summary-card">
        <div class="sum-label">Average</div>
        <div class="sum-value">{{ $average }}%</div>
    </div>
</div>
@endif

@if(! $isRemarkOnly)
<div class="grade-key">
    <div class="grade-key-title">Grading Scale</div>
    <div class="grade-key-row">
        @foreach([['75–100','A','Excellent'],['65–74','B','Very Good'],['55–64','C','Good'],['45–54','D','Fair'],['35–44','E','Pass'],['0–34','F','Fail']] as $g)
        <div class="grade-key-item">
            <div class="gk-range">{{ $g[0] }}%</div>
            <div class="gk-grade">{{ $g[1] }} — {{ $g[2] }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

@php
    // Use the first non-null admin_comment as the principal's remark
    $principalRemark = $results->whereNotNull('admin_comment')->first()?->admin_comment;
@endphp
@if($principalRemark)
<div class="principal-remark">
    <div class="remark-title">Principal's Remark</div>
    <div class="remark-text">{{ $principalRemark }}</div>
</div>
@endif

<div class="signatures">
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">Class Teacher</div>
    </div>
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">Head Teacher / Principal</div>
    </div>
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">Date</div>
    </div>
</div>

<div class="footer">
    Nurtureville School · connect.nurturevilleschool.org · Generated {{ now()->format('d M Y') }}
</div>

</body>
</html>
