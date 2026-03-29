<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }

.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }

.compose-grid { display:grid; grid-template-columns:1fr; gap:16px; }
@media(min-width:900px) { .compose-grid { grid-template-columns:1.6fr 1fr; align-items:start; } }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.panel-head { padding:14px 20px; border-bottom:1px solid var(--c-border); font-size:13px; font-weight:600; color:var(--c-text-1); }
.panel-body { padding:20px; }

/* Form fields */
.form-field { margin-bottom:16px; }
.form-field:last-child { margin-bottom:0; }
.form-field label { display:block; font-size:12px; font-weight:500; color:var(--c-text-2); margin-bottom:5px; }
.form-field input, .form-field select, .form-field textarea {
    width:100%; padding:10px 12px; border:1px solid var(--c-border); border-radius:8px;
    font-family:var(--f-sans); font-size:14px; color:var(--c-text-1);
    background:var(--c-bg); outline:none; transition:border-color 150ms; -webkit-appearance:none;
}
.form-field input:focus, .form-field select:focus, .form-field textarea:focus {
    border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 3px rgba(26,86,255,0.08);
}
.form-field textarea { resize:vertical; min-height:180px; line-height:1.6; }
.field-error { font-size:11px; color:var(--c-danger); margin-top:4px; }

/* Recipient type selector */
.recipient-types { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px; }
.rtype-opt {
    padding:10px 12px; border:2px solid var(--c-border); border-radius:8px;
    cursor:pointer; transition:border-color 150ms, background 150ms;
    text-align:center;
}
.rtype-opt.selected { border-color:var(--c-accent); background:var(--c-accent-bg); }
.rtype-name { font-size:12px; font-weight:600; color:var(--c-text-1); }
.rtype-desc { font-size:10px; color:var(--c-text-3); margin-top:2px; }

/* Individual parent search */
.parent-search-wrap { position:relative; }
.parent-search-wrap input { padding-left:34px; }
.parent-search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--c-text-3); pointer-events:none; z-index:1; }
.parent-dropdown {
    position:absolute; top:100%; left:0; right:0; z-index:20;
    background:var(--c-surface); border:1px solid var(--c-border);
    border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.1);
    margin-top:4px; overflow:hidden;
}
.parent-option { padding:10px 14px; cursor:pointer; transition:background 100ms; border-bottom:1px solid var(--c-border); }
.parent-option:last-child { border-bottom:none; }
.parent-option:hover { background:var(--c-bg); }
.parent-option-name  { font-size:13px; font-weight:500; color:var(--c-text-1); }
.parent-option-meta  { font-size:11px; color:var(--c-text-3); margin-top:1px; }

/* Selected parents tags */
.selected-parents { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
.parent-tag {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 10px; background:var(--c-accent-bg);
    border-radius:20px; font-size:12px; color:var(--c-accent);
}
.parent-tag-remove { background:none; border:none; color:var(--c-accent); cursor:pointer; padding:0; font-size:14px; line-height:1; }

/* Preview card */
.preview-card {
    background:rgba(21,128,61,0.05); border:1px solid rgba(21,128,61,0.2);
    border-radius:var(--r-md); padding:16px; margin-bottom:16px;
}
.preview-title { font-size:13px; font-weight:600; color:#15803D; margin-bottom:4px; }
.preview-sub   { font-size:12px; color:var(--c-text-2); }

/* Info rows in sidebar */
.info-row { display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid var(--c-border); font-size:13px; }
.info-row:last-child { border-bottom:none; }
.info-label { color:var(--c-text-3); font-size:12px; }
.info-value { font-weight:500; }

/* Action buttons */
.btn-primary { display:inline-flex; align-items:center; gap:6px; padding:10px 20px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:14px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; width:100%; justify-content:center; }
.btn-primary:hover { opacity:0.9; }
.btn-primary:disabled { opacity:0.4; cursor:not-allowed; }
.btn-ghost { display:inline-flex; align-items:center; gap:6px; padding:10px 20px; background:none; border:1px solid var(--c-border); color:var(--c-text-2); border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; font-family:var(--f-sans); width:100%; justify-content:center; margin-bottom:8px; }
.btn-ghost:hover { background:var(--c-bg); }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif

<div class="pg-header">
    <div>
        <div class="pg-title">Compose Message</div>
        <div class="pg-sub">Send a message to parents. It will be delivered in-app and by email.</div>
    </div>
    <a href="{{ route('admin.messages') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border:1px solid var(--c-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--c-text-2);text-decoration:none;background:var(--c-surface)">
        ← Sent Messages
    </a>
</div>

<div class="compose-grid">

    {{-- ── Left: Compose form ── --}}
    <div>
        <div class="panel">
            <div class="panel-head">Message</div>
            <div class="panel-body">

                <div class="form-field">
                    <label>Subject <span style="color:var(--c-danger)">*</span></label>
                    <input type="text" wire:model="subject" placeholder="e.g. Second Term Fee Reminder">
                    @error('subject') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-field">
                    <label>Message Body <span style="color:var(--c-danger)">*</span></label>
                    <textarea wire:model="body" placeholder="Type your message here…"></textarea>
                    @error('body') <div class="field-error">{{ $message }}</div> @enderror
                </div>

            </div>
        </div>
    </div>

    {{-- ── Right: Recipients + Actions ── --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Recipients panel --}}
        <div class="panel">
            <div class="panel-head">Recipients</div>
            <div class="panel-body">

                <div class="recipient-types">
                    @foreach([
                        ['all',        'All Parents',         'Every parent with an account'],
                        ['class',      'By Class',            'Parents of students in one class'],
                        ['term',       'By Term',             'Parents enrolled in a specific term'],
                        ['unpaid',     'Unpaid Fees',         'Parents with outstanding balances'],
                        ['individual', 'Individual',          'Select specific parents'],
                    ] as [$val, $label, $desc])
                        <div class="rtype-opt {{ $recipientType === $val ? 'selected' : '' }}"
                             wire:click="$set('recipientType', '{{ $val }}')">
                            <div class="rtype-name">{{ $label }}</div>
                            <div class="rtype-desc">{{ $desc }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Class selector --}}
                @if($recipientType === 'class')
                    <div class="form-field">
                        <label>Select Class</label>
                        <select wire:model.live="classId">
                            <option value="">Choose a class…</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('classId') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                @endif

                {{-- Term selector --}}
                @if($recipientType === 'term')
                    <div class="form-field">
                        <label>Select Term</label>
                        <select wire:model.live="termId">
                            <option value="">Choose a term…</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}">{{ $term->name }} — {{ $term->session->name }}</option>
                            @endforeach
                        </select>
                        @error('termId') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                @endif

                {{-- Individual search --}}
                @if($recipientType === 'individual')
                    <div class="form-field">
                        <label>Search Parents</label>
                        <div class="parent-search-wrap" x-data style="position:relative;">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
                            </svg>
                            <input type="text"
                                   wire:model.live.debounce.300ms="parentSearch"
                                   placeholder="Search by parent name, email, or child name…">

                            @if(count($parentResults))
                                <div class="parent-dropdown">
                                    @foreach($parentResults as $p)
                                        <div class="parent-option" wire:click="addParent({{ $p['id'] }})">
                                            <div class="parent-option-name">{{ $p['name'] }}</div>
                                            <div class="parent-option-meta">
                                                {{ $p['email'] }}
                                                @if($p['children']) · Children: {{ $p['children'] }} @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @error('selectedParentIds') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    @if($selectedParents->isNotEmpty())
                        <div class="selected-parents">
                            @foreach($selectedParents as $parent)
                                <span class="parent-tag">
                                    {{ $parent->user?->name ?? $parent->_temp_name }}
                                    <button class="parent-tag-remove" wire:click="removeParent({{ $parent->id }})">×</button>
                                </span>
                            @endforeach
                        </div>
                    @endif
                @endif

            </div>
        </div>

        {{-- Preview result --}}
        @if($previewing)
            <div class="preview-card">
                <div class="preview-title">✓ Ready to send</div>
                <div class="preview-sub">
                    This message will be sent to <strong>{{ $previewCount }}</strong>
                    {{ Str::plural('parent', $previewCount) }}.
                    Each will receive an in-app notification and an email via Brevo.
                </div>
            </div>
        @endif

        {{-- Actions --}}
        <div class="panel">
            <div class="panel-body">
                @if(! $previewing)
                    <button class="btn-ghost" wire:click="preview"
                        wire:loading.attr="disabled" wire:loading.class="opacity-50">
                        <span wire:loading.remove wire:target="preview">Preview Recipients</span>
                        <span wire:loading wire:target="preview">Counting…</span>
                    </button>
                @endif

                <button class="btn-primary" wire:click="send"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50"
                    @if(!$previewing) style="margin-top:0" @endif>
                    <span wire:loading.remove wire:target="send">
                        @if($previewing)
                            Send to {{ $previewCount }} {{ Str::plural('Parent', $previewCount) }}
                        @else
                            Send Message
                        @endif
                    </span>
                    <span wire:loading wire:target="send">Sending…</span>
                </button>
            </div>
        </div>

    </div>
</div>
</div>
