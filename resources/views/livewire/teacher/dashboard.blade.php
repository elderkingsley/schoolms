<div>
<style>
.welcome-card { background:linear-gradient(135deg,#15803D 0%,#0d5f2e 100%); border-radius:var(--r-lg); padding:20px; color:#fff; margin-bottom:20px; }
.welcome-title { font-size:18px; font-weight:700; letter-spacing:-0.02em; }
.welcome-sub   { font-size:13px; opacity:0.7; margin-top:3px; }
.welcome-term  { font-size:11px; opacity:0.6; margin-top:8px; }

.section-title { font-size:12px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:12px; }

.class-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:16px; margin-bottom:12px; }
.class-card-head { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:12px; }
.class-display-name { font-size:16px; font-weight:700; color:var(--c-text-1); }
.class-meta { font-size:12px; color:var(--c-text-3); margin-top:2px; }

.class-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:12px; }
.stat-mini { background:var(--c-bg); border-radius:var(--r-sm); padding:8px 10px; text-align:center; }
.stat-mini-val { font-size:18px; font-weight:700; color:var(--c-text-1); }
.stat-mini-lbl { font-size:10px; color:var(--c-text-3); margin-top:2px; }

.subjects-list { display:flex; flex-wrap:wrap; gap:5px; margin-bottom:12px; }
.subject-tag { padding:3px 8px; background:var(--c-accent-bg); color:var(--c-accent); border-radius:4px; font-size:11px; font-weight:500; }

.btn-enter { display:block; text-align:center; padding:10px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; transition:opacity 150ms; }
.btn-enter:hover { opacity:0.9; }

.empty-state { text-align:center; padding:40px 20px; color:var(--c-text-3); font-size:13px; }
.empty-title { font-size:14px; font-weight:600; color:var(--c-text-2); margin-bottom:4px; }
</style>

<div class="welcome-card">
    <div class="welcome-title">Welcome, {{ auth()->user()->name }} 👋</div>
    <div class="welcome-sub">Nurtureville Teacher Portal</div>
    @if($activeTerm)
        <div class="welcome-term">{{ $activeTerm->name }} Term — {{ $activeTerm->session->name }}</div>
    @endif
</div>

<div class="section-title">My Classes</div>

@if($myClasses->isEmpty())
    <div class="empty-state">
        <div class="empty-title">No classes assigned yet</div>
        <div>Ask the admin to assign you as form teacher of your class.</div>
    </div>
@else
    @foreach($myClasses as $class)
        @php
            $studentCount  = $class->enrolments->count();
            $subjectCount  = $class->subjects->count();
            $submitted     = $submittedCounts[$class->id] ?? 0;
        @endphp
        <div class="class-card">
            <div class="class-card-head">
                <div>
                    <div class="class-display-name">{{ $class->display_name }}</div>
                    <div class="class-meta">{{ $studentCount }} {{ Str::plural('student', $studentCount) }} enrolled</div>
                </div>
                @if($submitted > 0)
                    <span style="background:rgba(180,83,9,0.1);color:#B45309;font-size:11px;font-weight:600;padding:3px 8px;border-radius:20px;">
                        {{ $submitted }} pending review
                    </span>
                @endif
            </div>

            <div class="class-stats">
                <div class="stat-mini">
                    <div class="stat-mini-val">{{ $studentCount }}</div>
                    <div class="stat-mini-lbl">Students</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-val">{{ $subjectCount }}</div>
                    <div class="stat-mini-lbl">Subjects</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-val">{{ $submitted }}</div>
                    <div class="stat-mini-lbl">Submitted</div>
                </div>
            </div>

            @if($class->subjects->isNotEmpty())
                <div class="subjects-list">
                    @foreach($class->subjects as $subject)
                        <span class="subject-tag">{{ $subject->name }}</span>
                    @endforeach
                </div>
            @endif

            <a href="{{ route('teacher.results') }}?class={{ $class->id }}" class="btn-enter">
                Enter Results →
            </a>
        </div>
    @endforeach
@endif
</div>
