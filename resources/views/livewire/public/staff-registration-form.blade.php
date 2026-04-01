<div>
<style>
.reg-wrap { max-width: 560px; margin: 0 auto; padding: 24px 16px 60px; }

.reg-hero { margin-bottom: 28px; text-align: center; }
.reg-logo { width: 48px; height: 48px; background: var(--c-accent); border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; }
.reg-logo svg { color: #fff; }
.reg-title { font-size: 22px; font-weight: 700; color: var(--c-text-1); letter-spacing: -0.03em; }
.reg-sub   { font-size: 13px; color: var(--c-text-3); margin-top: 5px; line-height: 1.5; }

.form-card { background: var(--c-surface); border: 1px solid var(--c-border);
             border-radius: 16px; padding: 24px 20px; }
@media(min-width:480px) { .form-card { padding: 32px 28px; } }

.section-label { font-size: 11px; font-weight: 600; color: var(--c-accent);
                 text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px; }

.field-row { display: grid; grid-template-columns: 1fr; gap: 14px; margin-bottom: 14px; }
@media(min-width:480px) { .field-row.cols-2 { grid-template-columns: 1fr 1fr; } }

.field { display: flex; flex-direction: column; gap: 5px; }
.field label { font-size: 12px; font-weight: 500; color: var(--c-text-2); }
.field label .req { color: var(--c-error); margin-left: 2px; }

.field input,
.field select,
.field textarea {
    width: 100%; padding: 10px 12px;
    border: 1px solid var(--c-border); border-radius: 8px;
    font-size: 13px; font-family: inherit;
    background: var(--c-surface); color: var(--c-text-1);
    outline: none; transition: border-color 0.15s;
}
.field input:focus,
.field select:focus,
.field textarea:focus { border-color: var(--c-accent); }
.field select { appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='%23888' d='M4 6l4 4 4-4'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center; padding-right: 32px; }
.field-error { font-size: 11px; color: var(--c-error); }

.role-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
.role-card { border: 2px solid var(--c-border); border-radius: 10px; padding: 14px 12px;
             cursor: pointer; transition: all 0.15s; text-align: center; }
.role-card:hover { border-color: var(--c-accent); background: rgba(26,86,255,0.03); }
.role-card.selected { border-color: var(--c-accent); background: rgba(26,86,255,0.06); }
.role-card-title { font-size: 13px; font-weight: 600; color: var(--c-text-1); margin-top: 6px; }
.role-card-sub   { font-size: 11px; color: var(--c-text-3); margin-top: 2px; line-height: 1.4; }
.role-card svg   { color: var(--c-accent); }

.divider { border: none; border-top: 1px solid var(--c-border); margin: 20px 0; }

.submit-btn { width: 100%; padding: 12px; background: var(--c-accent); color: #fff;
              border: none; border-radius: 10px; font-size: 14px; font-weight: 600;
              cursor: pointer; font-family: inherit; margin-top: 20px;
              transition: opacity 0.15s; }
.submit-btn:hover { opacity: 0.88; }
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* Success state */
.success-card { background: var(--c-surface); border: 1px solid var(--c-border);
                border-radius: 16px; padding: 48px 28px; text-align: center; }
.success-icon { width: 56px; height: 56px; background: rgba(21,128,61,0.1);
                border-radius: 50%; display: flex; align-items: center; justify-content: center;
                margin: 0 auto 20px; }
.success-title { font-size: 20px; font-weight: 700; color: var(--c-text-1); letter-spacing: -0.02em; }
.success-sub   { font-size: 13px; color: var(--c-text-3); margin-top: 8px; line-height: 1.6; max-width: 360px; margin-left:auto; margin-right:auto; }
</style>

<div class="reg-wrap">

    {{-- Hero --}}
    <div class="reg-hero">
        <div class="reg-logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
        </div>
        <div class="reg-title">Staff Registration</div>
        <div class="reg-sub">
            Apply to join the Nurtureville team.<br>
            Complete your details below and our admin will review your application.
        </div>
    </div>

    @if($submitted)
        {{-- Success state --}}
        <div class="success-card">
            <div class="success-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803D" stroke-width="2.5">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
            <div class="success-title">Application Submitted!</div>
            <div class="success-sub">
                Thank you, <strong>{{ $name }}</strong>. Your application has been received and is
                pending review by our administration team. You will receive an email at
                <strong>{{ $email }}</strong> once a decision has been made.
            </div>
        </div>
    @else
        <div class="form-card">

            {{-- Role selection --}}
            <div class="section-label">Applying As</div>
            <div class="role-cards">
                <div class="role-card {{ $role === 'teacher' ? 'selected' : '' }}"
                     wire:click="$set('role', 'teacher')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="18" height="14" rx="2"/>
                        <path d="M8 21h8M12 17v4"/>
                        <path d="M8 10h8M8 7h4"/>
                    </svg>
                    <div class="role-card-title">Teacher</div>
                    <div class="role-card-sub">Lead class teacher</div>
                </div>
                <div class="role-card {{ $role === 'teaching_assistant' ? 'selected' : '' }}"
                     wire:click="$set('role', 'teaching_assistant')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/>
                        <path d="M16 11l2 2 4-4"/>
                    </svg>
                    <div class="role-card-title">Teaching Assistant</div>
                    <div class="role-card-sub">Support role in class</div>
                </div>
            </div>
            @error('role') <div class="field-error" style="margin-top:-8px;margin-bottom:10px;">{{ $message }}</div> @enderror

            <hr class="divider">
            <div class="section-label">Personal Details</div>

            <div class="field-row">
                <div class="field">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" wire:model="name" placeholder="e.g. Mrs. Adaeze Obi" autofocus>
                    @error('name') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="field-row cols-2">
                <div class="field">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" wire:model="email" placeholder="e.g. adaeze@gmail.com">
                    @error('email') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label>Phone Number</label>
                    <input type="text" wire:model="phone" placeholder="e.g. 08012345678">
                    @error('phone') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <hr class="divider">
            <div class="section-label">Additional Information</div>

            <div class="field">
                <label>Subject Specialisation / Notes <span style="font-weight:400;color:var(--c-text-3);">(optional)</span></label>
                <textarea wire:model="notes" rows="3"
                    placeholder="e.g. Mathematics and English — 5 years experience in primary education"></textarea>
                @error('notes') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <button class="submit-btn" wire:click="submit"
                wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submit">Submit Application</span>
                <span wire:loading wire:target="submit">Submitting…</span>
            </button>

        </div>
    @endif

</div>
</div>
