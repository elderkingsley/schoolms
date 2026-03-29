<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }

.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }

.btn-new { display:inline-flex; align-items:center; gap:6px; padding:9px 16px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); text-decoration:none; transition:opacity 150ms; }
.btn-new:hover { opacity:0.9; }

/* Search */
.search-wrap { position:relative; margin-bottom:14px; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--c-text-3); pointer-events:none; }
.search-wrap input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--c-border); border-radius:8px; font-family:var(--f-sans); font-size:13px; color:var(--c-text-1); background:var(--c-surface); outline:none; }
.search-wrap input:focus { border-color:var(--c-accent); box-shadow:0 0 0 3px rgba(26,86,255,0.08); }

/* Message list panel */
.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; padding:10px 20px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.data-table td { padding:13px 20px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:#fafaf8; }

.msg-subject { font-weight:600; color:var(--c-text-1); }
.msg-preview { font-size:11px; color:var(--c-text-3); margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:280px; }

.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-all      { background:rgba(26,86,255,0.08); color:var(--c-accent); }
.badge-class    { background:rgba(21,128,61,0.08); color:#15803D; }
.badge-term     { background:rgba(180,83,9,0.08);  color:#B45309; }
.badge-unpaid   { background:rgba(190,18,60,0.08); color:var(--c-danger); }
.badge-individual { background:rgba(100,100,100,0.08); color:#555; }

.read-stats { font-size:12px; color:var(--c-text-3); }
.read-stats strong { color:var(--c-text-1); }

.btn-view { font-size:12px; font-weight:500; color:var(--c-accent); background:none; border:none; cursor:pointer; font-family:var(--f-sans); }

/* Detail view */
.detail-header { padding:20px; border-bottom:1px solid var(--c-border); }
.detail-subject { font-size:18px; font-weight:700; color:var(--c-text-1); margin-bottom:6px; }
.detail-meta    { font-size:12px; color:var(--c-text-3); }
.detail-body    { padding:20px; font-size:14px; color:var(--c-text-1); line-height:1.7; white-space:pre-wrap; border-bottom:1px solid var(--c-border); }
.detail-stats   { padding:14px 20px; background:var(--c-bg); font-size:12px; color:var(--c-text-2); display:flex; gap:20px; }

.btn-back { display:inline-flex; align-items:center; gap:6px; font-size:13px; color:var(--c-text-3); background:none; border:none; cursor:pointer; font-family:var(--f-sans); padding:0; margin-bottom:16px; }
.btn-back:hover { color:var(--c-text-1); }

.empty-state { padding:40px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
.pag-wrap { padding:14px 20px; border-top:1px solid var(--c-border); }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif

@if($viewingMessage)
    {{-- ── Detail view ── --}}
    <button class="btn-back" wire:click="closeView">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M10 3L5 8l5 5"/>
        </svg>
        All Messages
    </button>

    <div class="panel">
        <div class="detail-header">
            <div class="detail-subject">{{ $viewingMessage->subject }}</div>
            <div class="detail-meta">
                Sent by <strong>{{ $viewingMessage->sender->name }}</strong>
                ·
                {{ $viewingMessage->sent_at?->format('d M Y, g:ia') ?? $viewingMessage->created_at->format('d M Y, g:ia') }}
                ·
                {{ $viewingMessage->recipientLabel() }}
            </div>
        </div>
        <div class="detail-body">{{ $viewingMessage->body }}</div>
        <div class="detail-stats">
            <span>Sent to: <strong>{{ $viewingMessage->recipient_count }}</strong> {{ Str::plural('parent', $viewingMessage->recipient_count) }}</span>
            <span>Read: <strong>{{ $viewingMessage->readCount() }}</strong> of {{ $viewingMessage->recipient_count }}</span>
        </div>
    </div>

@else
    {{-- ── Inbox list ── --}}
    <div class="pg-header">
        <div>
            <div class="pg-title">Messages</div>
            <div class="pg-sub">All messages sent to parents</div>
        </div>
        <a href="{{ route('admin.messages.compose') }}" class="btn-new">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M8 2v12M2 8h12"/>
            </svg>
            Compose
        </a>
    </div>

    <div class="search-wrap">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
        </svg>
        <input type="text"
               wire:model.live.debounce.300ms="search"
               placeholder="Search by subject or message content…">
    </div>

    <div class="panel">
        @if($messages->isEmpty())
            <div class="empty-state">No messages sent yet. <a href="{{ route('admin.messages.compose') }}" style="color:var(--c-accent)">Compose one →</a></div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Recipients</th>
                        <th>Sent</th>
                        <th>Read</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $msg)
                        <tr>
                            <td>
                                <div class="msg-subject">{{ $msg->subject }}</div>
                                <div class="msg-preview">{{ $msg->body }}</div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $msg->recipient_type }}">
                                    {{ $msg->recipientLabel() }}
                                </span>
                            </td>
                            <td style="font-size:12px;color:var(--c-text-3);white-space:nowrap;">
                                {{ $msg->sent_at?->format('d M Y') ?? $msg->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <span class="read-stats">
                                    <strong>{{ $msg->readCount() }}</strong> / {{ $msg->recipient_count }}
                                </span>
                            </td>
                            <td>
                                <button class="btn-view" wire:click="view({{ $msg->id }})">View</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($messages->hasPages())
                <div class="pag-wrap">{{ $messages->links() }}</div>
            @endif
        @endif
    </div>
@endif
</div>
