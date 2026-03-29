<div>
<style>
.pg-title { font-size:18px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:4px; }
.pg-sub   { font-size:13px; color:var(--c-text-3); margin-bottom:16px; }

/* Search */
.search-wrap { position:relative; margin-bottom:14px; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--c-text-3); pointer-events:none; }
.search-wrap input {
    width:100%; padding:10px 12px 10px 34px;
    border:1px solid var(--c-border); border-radius:8px;
    font-family:var(--f-sans); font-size:13px; color:var(--c-text-1);
    background:var(--c-surface); outline:none;
}
.search-wrap input:focus { border-color:var(--c-accent); box-shadow:0 0 0 3px rgba(26,86,255,0.08); }

/* Message list */
.message-item {
    background:var(--c-surface); border:1px solid var(--c-border);
    border-radius:var(--r-md); padding:14px 16px;
    margin-bottom:8px; cursor:pointer;
    transition:box-shadow 150ms, border-color 150ms;
    display:flex; align-items:flex-start; gap:12px;
}
.message-item:hover { box-shadow:var(--shadow-card); border-color:#d0cec8; }
.message-item.unread { border-left:3px solid var(--c-accent); }

.msg-dot {
    width:8px; height:8px; border-radius:50%;
    background:var(--c-accent); flex-shrink:0; margin-top:5px;
}
.msg-dot.read { background:transparent; }

.msg-body { flex:1; min-width:0; }
.msg-subject {
    font-size:14px; font-weight:600; color:var(--c-text-1);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.message-item.unread .msg-subject { font-weight:700; }
.msg-preview {
    font-size:12px; color:var(--c-text-3);
    margin-top:3px;
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;
    overflow:hidden;
}
.msg-meta { font-size:11px; color:var(--c-text-3); margin-top:6px; }

/* Message detail view */
.msg-detail { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:20px; }
.msg-detail-subject { font-size:17px; font-weight:700; color:var(--c-text-1); margin-bottom:4px; }
.msg-detail-meta    { font-size:12px; color:var(--c-text-3); margin-bottom:16px; padding-bottom:16px; border-bottom:1px solid var(--c-border); }
.msg-detail-body    { font-size:14px; color:var(--c-text-1); line-height:1.7; white-space:pre-wrap; }

.btn-back {
    display:inline-flex; align-items:center; gap:6px;
    font-size:13px; color:var(--c-text-3); background:none; border:none;
    cursor:pointer; font-family:var(--f-sans); padding:0; margin-bottom:14px;
    transition:color 150ms;
}
.btn-back:hover { color:var(--c-text-1); }

.empty-state { text-align:center; padding:40px 20px; color:var(--c-text-3); font-size:13px; }
.empty-icon  { color:var(--c-border); margin-bottom:10px; }
.empty-title { font-size:14px; font-weight:600; color:var(--c-text-2); }
.empty-sub   { margin-top:4px; }

.pag-wrap { margin-top:12px; }
</style>

@if($viewingRecipient)
    {{-- ── Message detail view ── --}}
    <button class="btn-back" wire:click="closeMessage">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M10 3L5 8l5 5"/>
        </svg>
        Back to inbox
    </button>

    <div class="msg-detail">
        <div class="msg-detail-subject">{{ $viewingRecipient->message->subject }}</div>
        <div class="msg-detail-meta">
            From: <strong>{{ $viewingRecipient->message->sender->name }}</strong>
            &nbsp;·&nbsp;
            {{ $viewingRecipient->message->sent_at?->format('d M Y, g:ia') ?? $viewingRecipient->message->created_at->format('d M Y, g:ia') }}
        </div>
        <div class="msg-detail-body">{{ $viewingRecipient->message->body }}</div>
    </div>

@else
    {{-- ── Inbox ── --}}
    <div class="pg-title">Messages</div>
    <div class="pg-sub">
        @if($unread > 0)
            {{ $unread }} unread {{ Str::plural('message', $unread) }}
        @else
            All messages read
        @endif
    </div>

    <div class="search-wrap">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
        </svg>
        <input type="text"
               wire:model.live.debounce.300ms="search"
               placeholder="Search messages…">
    </div>

    @if($recipients->isEmpty())
        <div class="empty-state">
            <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <div class="empty-title">
                {{ $search ? 'No messages match your search' : 'No messages yet' }}
            </div>
            <div class="empty-sub">Messages from the school will appear here.</div>
        </div>
    @else
        @foreach($recipients as $recipient)
            <div class="message-item {{ is_null($recipient->read_at) ? 'unread' : '' }}"
                 wire:click="open({{ $recipient->id }})">
                <div class="msg-dot {{ $recipient->read_at ? 'read' : '' }}"></div>
                <div class="msg-body">
                    <div class="msg-subject">{{ $recipient->message->subject }}</div>
                    <div class="msg-preview">{{ $recipient->message->body }}</div>
                    <div class="msg-meta">
                        {{ $recipient->message->sender->name }}
                        &nbsp;·&nbsp;
                        {{ $recipient->message->sent_at?->diffForHumans() ?? $recipient->message->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        @endforeach

        @if($recipients->hasPages())
            <div class="pag-wrap">
                {{ $recipients->links() }}
            </div>
        @endif
    @endif
@endif
</div>
