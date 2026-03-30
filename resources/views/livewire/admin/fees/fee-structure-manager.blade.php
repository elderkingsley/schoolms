<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }

.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; border-radius:var(--r-sm); padding:12px 16px; margin-bottom:16px; font-size:13px; font-weight:500; }

/* Term selector */
.term-selector {
    background:var(--c-surface); border:1px solid var(--c-border);
    border-radius:var(--r-md); padding:16px 20px;
    display:flex; align-items:center; gap:14px;
    flex-wrap:wrap; margin-bottom:20px;
}
.term-selector label { font-size:12px; font-weight:500; color:var(--c-text-2); }
.term-select {
    padding:8px 12px; border:1px solid var(--c-border); border-radius:8px;
    font-size:13px; font-family:var(--f-sans); background:var(--c-bg);
    color:var(--c-text-1); outline:none; -webkit-appearance:none; min-width:200px;
}
.term-select:focus { border-color:var(--c-accent); }

.btn-copy {
    padding:8px 14px; border:1px solid var(--c-border); border-radius:8px;
    font-size:12px; font-weight:500; background:none; color:var(--c-text-2);
    cursor:pointer; font-family:var(--f-sans); transition:background 150ms;
}
.btn-copy:hover { background:var(--c-bg); color:var(--c-text-1); }

/* Structure table */
.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; margin-bottom:20px; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }
.panel-sub   { font-size:11px; color:var(--c-text-3); }

.structure-table { width:100%; border-collapse:collapse; }
.structure-table th {
    font-size:10px; font-weight:600; color:var(--c-text-3);
    text-transform:uppercase; letter-spacing:0.06em;
    padding:10px 12px; text-align:left;
    background:var(--c-bg); border-bottom:1px solid var(--c-border);
    white-space:nowrap;
}
.structure-table th:first-child { padding-left:20px; min-width:160px; position:sticky; left:0; background:var(--c-bg); z-index:1; }
.structure-table td { padding:10px 12px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.structure-table td:first-child { padding-left:20px; position:sticky; left:0; background:var(--c-surface); z-index:1; font-weight:500; font-size:13px; }
.structure-table tr:last-child td { border-bottom:none; }
.structure-table tr:hover td { background:rgba(26,86,255,0.02); }
.structure-table tr:hover td:first-child { background:rgba(26,86,255,0.02); }

.amount-input {
    width:110px; padding:7px 10px;
    border:1px solid var(--c-border); border-radius:6px;
    font-size:13px; font-family:var(--f-mono);
    text-align:right; background:var(--c-bg);
    color:var(--c-text-1); outline:none;
    transition:border-color 150ms;
}
.amount-input:focus { border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 2px rgba(26,86,255,0.08); }
.amount-input::placeholder { color:var(--c-text-3); font-family:var(--f-sans); font-size:12px; }

.badge-compulsory { background:rgba(26,86,255,0.08); color:var(--c-accent); }
.badge-optional   { background:rgba(180,83,9,0.08);   color:#B45309; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:2px 7px; border-radius:20px; font-size:10px; font-weight:500; }

/* Save bar */
.save-bar {
    position:sticky; bottom:0; left:0; right:0;
    background:var(--c-surface); border-top:1px solid var(--c-border);
    padding:14px 24px;
    display:flex; align-items:center; justify-content:space-between;
    box-shadow:0 -4px 20px rgba(0,0,0,0.06);
    z-index:10;
}
.save-hint { font-size:12px; color:var(--c-text-3); }
.btn-save {
    padding:10px 28px; background:var(--c-accent); color:#fff;
    border:none; border-radius:8px; font-size:14px; font-weight:500;
    cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms;
}
.btn-save:hover { opacity:0.9; }

.no-items { padding:28px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
</style>

@if(session('success'))
    <div class="flash-success">✓ {{ session('success') }}</div>
@endif

<div class="pg-header">
    <div>
        <h1 class="pg-title">Fee Structure</h1>
        <p class="pg-sub">Set fee amounts per item per class for each term.</p>
    </div>
    <a href="{{ route('admin.fees.items') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border:1px solid var(--c-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--c-text-2);text-decoration:none;background:var(--c-surface)">
        Manage Fee Items →
    </a>
</div>

{{-- Term selector --}}
<div class="term-selector">
    <label>Editing fee structure for:</label>
    <select class="term-select" wire:model.live="selectedTermId">
        <option value="">Select a term</option>
        @foreach($terms as $term)
            <option value="{{ $term->id }}">
                {{ $term->session->name }} — {{ $term->name }} Term
            </option>
        @endforeach
    </select>

    @if($previousTerm && $selectedTermId)
        <button class="btn-copy"
            wire:click="copyFromTerm({{ $previousTerm->id }})"
            wire:confirm="Copy all amounts from {{ $previousTerm->name }} Term? This will overwrite current amounts for this term.">
            Copy from {{ $previousTerm->name }} Term
        </button>
    @endif
</div>

@if(!$selectedTermId)
    <div class="panel">
        <div class="no-items">Select a term above to view or edit its fee structure.</div>
    </div>
@else

    {{-- Compulsory fees --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">Compulsory Fees</span>
            <span class="panel-sub">Applied automatically to all enrolled students</span>
        </div>

        @if($compulsoryItems->isEmpty())
            <div class="no-items">No compulsory fee items. <a href="{{ route('admin.fees.items') }}" style="color:var(--c-accent)">Create some →</a></div>
        @else
            <div style="overflow-x:auto">
                <table class="structure-table">
                    <thead>
                        <tr>
                            <th>Fee Item</th>
                            @foreach($classes as $class)
                                <th>{{ $class->display_name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($compulsoryItems as $item)
                            <tr>
                                <td>
                                    {{ $item->name }}
                                    <span class="badge badge-compulsory" style="margin-left:6px">C</span>
                                </td>
                                @foreach($classes as $class)
                                    <td>
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            class="amount-input"
                                            wire:model.lazy="amounts.{{ $item->id }}.{{ $class->id }}"
                                            placeholder="—"
                                            x-data
                                            x-on:focus="$el.value = $el.value.replace(/,/g, '')"
                                            x-on:blur="
                                                let raw = $el.value.replace(/,/g, '').replace(/[^0-9]/g, '');
                                                if (raw === '' || raw === '0') { $el.value = ''; return; }
                                                $el.value = parseInt(raw, 10).toLocaleString('en-NG');
                                            ">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Optional fees --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">Optional Fees</span>
            <span class="panel-sub">Added manually to individual student invoices</span>
        </div>

        @if($optionalItems->isEmpty())
            <div class="no-items">No optional fee items. <a href="{{ route('admin.fees.items') }}" style="color:var(--c-accent)">Create some →</a></div>
        @else
            <div style="overflow-x:auto">
                <table class="structure-table">
                    <thead>
                        <tr>
                            <th>Fee Item</th>
                            @foreach($classes as $class)
                                <th>{{ $class->display_name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($optionalItems as $item)
                            <tr>
                                <td>
                                    {{ $item->name }}
                                    <span class="badge badge-optional" style="margin-left:6px">O</span>
                                </td>
                                @foreach($classes as $class)
                                    <td>
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            class="amount-input"
                                            wire:model.lazy="amounts.{{ $item->id }}.{{ $class->id }}"
                                            placeholder="—"
                                            x-data
                                            x-on:focus="$el.value = $el.value.replace(/,/g, '')"
                                            x-on:blur="
                                                let raw = $el.value.replace(/,/g, '').replace(/[^0-9]/g, '');
                                                if (raw === '' || raw === '0') { $el.value = ''; return; }
                                                $el.value = parseInt(raw, 10).toLocaleString('en-NG');
                                            ">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Sticky save bar --}}
    <div class="save-bar">
        <span class="save-hint">Enter amounts in Naira (₦). Leave blank if a fee does not apply to that class.</span>
        <button class="btn-save" wire:click="save"
            wire:loading.attr="disabled" wire:loading.class="opacity-50">
            <span wire:loading.remove>Save Fee Structure</span>
            <span wire:loading>Saving...</span>
        </button>
    </div>

@endif
</div>
