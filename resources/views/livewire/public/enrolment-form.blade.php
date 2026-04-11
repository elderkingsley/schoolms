{{-- Deploy to: resources/views/livewire/public/enrolment-form.blade.php --}}
<div>
<style>
.enrol-wrap { }

.enrol-hero {
    margin-bottom: 28px;
}

.enrol-title {
    font-size: 22px; font-weight: 700;
    color: var(--c-text-1); letter-spacing: -0.03em;
}

.enrol-sub {
    font-size: 13px; color: var(--c-text-3); margin-top: 4px;
}

/* Progress */
.progress-bar {
    display: flex; gap: 6px; margin-bottom: 28px;
}

.prog-step {
    flex: 1; height: 4px; border-radius: 4px;
    background: var(--c-border);
    transition: background 300ms;
}

.prog-step.done { background: var(--c-accent); }

/* Card */
.form-card {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: 16px;
    padding: 24px 20px;
    margin-bottom: 16px;
}

@media (min-width: 640px) {
    .form-card { padding: 28px 28px; }
}

.step-label {
    font-size: 11px; font-weight: 600;
    color: var(--c-accent); text-transform: uppercase;
    letter-spacing: 0.08em; margin-bottom: 4px;
}

.step-title {
    font-size: 18px; font-weight: 700;
    color: var(--c-text-1); letter-spacing: -0.02em;
    margin-bottom: 20px;
}

/* Fields */
.field-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
    margin-bottom: 14px;
}

@media (min-width: 480px) {
    .field-row.cols-2 { grid-template-columns: 1fr 1fr; }
}

.field { display: flex; flex-direction: column; gap: 5px; }

.field label {
    font-size: 12px; font-weight: 500;
    color: var(--c-text-2);
}

.field label .req { color: var(--c-error); margin-left: 2px; }

.field input,
.field select,
.field textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--c-border);
    border-radius: 8px;
    font-family: var(--f-sans);
    font-size: 14px;
    color: var(--c-text-1);
    background: var(--c-bg);
    transition: border-color 150ms;
    outline: none;
    -webkit-appearance: none;
}

.field input:focus,
.field select:focus,
.field textarea:focus {
    border-color: var(--c-accent);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(26,86,255,0.08);
}

.field textarea { resize: vertical; min-height: 80px; }

.field-error {
    font-size: 11px; color: var(--c-error); margin-top: 2px;
}

/* Toggle */
.toggle-row {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 0; border-top: 1px solid var(--c-border);
    margin-top: 4px; margin-bottom: 14px; cursor: pointer;
}

.toggle-label { font-size: 13px; font-weight: 500; color: var(--c-text-1); }
.toggle-sub   { font-size: 11px; color: var(--c-text-3); }

.toggle-switch {
    width: 38px; height: 22px;
    background: var(--c-border); border-radius: 11px;
    position: relative; flex-shrink: 0;
    transition: background 200ms;
}

.toggle-switch.on { background: var(--c-accent); }

.toggle-knob {
    width: 16px; height: 16px; border-radius: 50%;
    background: #fff; position: absolute;
    top: 3px; left: 3px;
    transition: transform 200ms;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.toggle-switch.on .toggle-knob { transform: translateX(16px); }

/* Review */
.review-section { margin-bottom: 20px; }

.review-section-title {
    font-size: 11px; font-weight: 600;
    color: var(--c-text-3); text-transform: uppercase;
    letter-spacing: 0.08em; margin-bottom: 10px;
    padding-bottom: 8px; border-bottom: 1px solid var(--c-border);
}

.review-row {
    display: flex; justify-content: space-between;
    padding: 6px 0; font-size: 13px;
}

.review-key { color: var(--c-text-3); }
.review-val { font-weight: 500; color: var(--c-text-1); text-align: right; max-width: 60%; }

/* Buttons */
.btn-row {
    display: flex; gap: 10px; justify-content: space-between;
    margin-top: 24px;
}

.btn {
    padding: 11px 20px;
    border-radius: 8px;
    font-family: var(--f-sans);
    font-size: 14px; font-weight: 500;
    cursor: pointer; border: none;
    transition: opacity 150ms, transform 100ms;
}

.btn:active { transform: scale(0.98); }

.btn-primary {
    background: var(--c-accent); color: #fff;
    flex: 1;
}

.btn-primary:hover { opacity: 0.9; }

.btn-ghost {
    background: none;
    border: 1px solid var(--c-border);
    color: var(--c-text-2);
}

.btn-ghost:hover { background: var(--c-bg); }

/* Success */
.success-wrap {
    text-align: center; padding: 40px 20px;
}

.success-icon {
    width: 64px; height: 64px; border-radius: 50%;
    background: rgba(21,128,61,0.1);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
}

.success-title {
    font-size: 22px; font-weight: 700;
    color: var(--c-text-1); letter-spacing: -0.02em;
    margin-bottom: 8px;
}

.success-msg { font-size: 14px; color: var(--c-text-2); line-height: 1.6; }

/* Photo upload */
.photo-upload-area {
    border: 2px dashed var(--c-border);
    border-radius: 10px; padding: 20px;
    text-align: center; cursor: pointer;
    transition: border-color 150ms, background 150ms;
    position: relative;
}
.photo-upload-area:hover { border-color: var(--c-accent); background: rgba(26,86,255,0.02); }
.photo-upload-area input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; }
.photo-preview { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 8px; display: block; border: 2px solid var(--c-border); }
.photo-placeholder { width: 64px; height: 64px; border-radius: 50%; background: var(--c-bg); border: 2px solid var(--c-border); display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; color: var(--c-text-3); }
.photo-hint { font-size: 12px; color: var(--c-text-3); margin-top: 4px; }
.photo-hint strong { color: var(--c-accent); }
</style>

@if($submitted)
    {{-- ── Success state ── --}}
    <div class="form-card">
        <div class="success-wrap">
            <div class="success-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803D" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <h2 class="success-title">Enrolment Submitted!</h2>
            <p class="success-msg">
                Thank you. Your child's enrolment form has been received.<br><br>
                Our admin team will review the details and you will receive
                a welcome email with your login credentials once the
                application is approved. This usually takes 1–2 business days.
            </p>
        </div>
    </div>

@elseif($isDuplicate)
    {{-- ── Duplicate detected ── --}}
    <div class="form-card">
        <div class="success-wrap">
            <div class="success-icon" style="background:rgba(180,83,9,0.1);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#B45309" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <h2 class="success-title" style="color:#B45309;">Application Already Received</h2>
            <p class="success-msg">
                Our records show that an enrolment application for this child has already been submitted
                using the same name, date of birth, and parent email address.<br><br>
                If you believe this is an error, or need to check the status of your application,
                please contact the school directly.
            </p>
        </div>
    </div>

@else
    <div class="enrol-wrap">
        <div class="enrol-hero">
            <h1 class="enrol-title">Student Enrolment</h1>
            <p class="enrol-sub">Complete the form below to enrol your child. All fields marked <span style="color:var(--c-error)">*</span> are required.</p>
        </div>

        {{-- Progress bar --}}
        <div class="progress-bar">
            @for($i = 1; $i <= $totalSteps; $i++)
                <div class="prog-step {{ $i <= $step ? 'done' : '' }}"></div>
            @endfor
        </div>

        {{-- ── Step 1: Student details ── --}}
        @if($step === 1)
        <div class="form-card">
            <div class="step-label">Step 1 of {{ $totalSteps }}</div>
            <h2 class="step-title">Child's Details</h2>

            <div class="field-row cols-2">
                <div class="field">
                    <label>First Name <span class="req">*</span></label>
                    <input type="text" wire:model="student_first_name" placeholder="e.g. Chidera">
                    @error('student_first_name') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label>Last Name <span class="req">*</span></label>
                    <input type="text" wire:model="student_last_name" placeholder="e.g. Okafor">
                    @error('student_last_name') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="field-row cols-2">
                <div class="field">
                    <label>Other Name</label>
                    <input type="text" wire:model="student_other_name" placeholder="Optional">
                </div>
                <div class="field">
                    <label>Gender <span class="req">*</span></label>
                    <select wire:model="student_gender">
                        <option value="">Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    @error('student_gender') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="field-row cols-2">
                <div class="field">
                    <label>Date of Birth <span class="req">*</span></label>
                    <input type="date" wire:model="student_dob">
                    @error('student_dob') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label>Class Applying For <span class="req">*</span></label>
                    <select wire:model="class_applied_for">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class }}">{{ $class }}</option>
                        @endforeach
                    </select>
                    @error('class_applied_for') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>Medical / Health Notes</label>
                    <textarea wire:model="medical_notes" placeholder="Any allergies, conditions or medications the school should know about..."></textarea>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>Passport Photograph <span style="font-size:10px;color:var(--c-text-3);font-weight:400">(optional but recommended)</span></label>
                    <div class="photo-upload-area">
                        <input type="file" wire:model="student_photo" accept="image/jpeg,image/png,image/webp">
                        @if($student_photo)
                            <img src="{{ $student_photo->temporaryUrl() }}" class="photo-preview" alt="Preview">
                            <div style="font-size:12px;color:#15803D;font-weight:500">✓ Photo selected</div>
                            <div class="photo-hint">Click to change</div>
                        @else
                            <div class="photo-placeholder">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                                </svg>
                            </div>
                            <div style="font-size:13px;font-weight:500;color:var(--c-text-2)">Upload passport photograph</div>
                            <div class="photo-hint">JPG or PNG · <strong>Max 2MB</strong></div>
                        @endif
                        <div wire:loading wire:target="student_photo" style="font-size:12px;color:var(--c-accent);margin-top:6px">
                            Uploading...
                        </div>
                    </div>
                    @error('student_photo') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        @endif

        {{-- ── Step 2: Primary parent ── --}}
        @if($step === 2)
        <div class="form-card">
            <div class="step-label">Step 2 of {{ $totalSteps }}</div>
            <h2 class="step-title">Primary Parent / Guardian</h2>

            <div class="field-row cols-2">
                <div class="field">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" wire:model="parent1_name" placeholder="e.g. Mrs. Ngozi Okafor">
                    @error('parent1_name') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label>Relationship <span class="req">*</span></label>
                    <select wire:model="parent1_relationship">
                        <option value="Mother">Mother</option>
                        <option value="Father">Father</option>
                        <option value="Guardian">Guardian</option>
                        <option value="Grandparent">Grandparent</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="field-row cols-2">
                <div class="field">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" wire:model="parent1_email" placeholder="e.g. ngozi@email.com">
                    @error('parent1_email') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label>Phone Number <span class="req">*</span></label>
                    <input type="tel" wire:model="parent1_phone" placeholder="e.g. 08012345678">
                    @error('parent1_phone') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>Home Address <span class="req">*</span></label>
                    <textarea wire:model="parent1_address" placeholder="Full home address"></textarea>
                    @error('parent1_address') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>Occupation</label>
                    <input type="text" wire:model="parent1_occupation" placeholder="e.g. Nurse, Engineer">
                </div>
            </div>
        </div>
        @endif

        {{-- ── Step 3: Second parent + emergency contact ── --}}
        @if($step === 3)
        <div class="form-card">
            <div class="step-label">Step 3 of {{ $totalSteps }}</div>
            <h2 class="step-title">Second Parent & Emergency Contact</h2>

            {{-- Second parent toggle --}}
            <div class="toggle-row" wire:click="$toggle('has_second_parent')">
                <div>
                    <div class="toggle-label">Add second parent / guardian</div>
                    <div class="toggle-sub">Optional — father, other guardian etc.</div>
                </div>
                <div class="toggle-switch {{ $has_second_parent ? 'on' : '' }}" style="margin-left:auto">
                    <div class="toggle-knob"></div>
                </div>
            </div>

            @if($has_second_parent)
            <div class="field-row cols-2">
                <div class="field">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" wire:model="parent2_name">
                    @error('parent2_name') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label>Relationship</label>
                    <select wire:model="parent2_relationship">
                        <option value="Father">Father</option>
                        <option value="Mother">Mother</option>
                        <option value="Guardian">Guardian</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="field-row cols-2">
                <div class="field">
                    <label>Email <span class="req">*</span></label>
                    <input type="email" wire:model="parent2_email">
                    @error('parent2_email') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label>Phone <span class="req">*</span></label>
                    <input type="tel" wire:model="parent2_phone">
                    @error('parent2_phone') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Occupation</label>
                    <input type="text" wire:model="parent2_occupation">
                </div>
            </div>
            @endif

            <h3 style="font-size:14px;font-weight:600;margin:20px 0 14px;color:var(--c-text-1)">
                Emergency Contact
            </h3>

            <div class="field-row cols-2">
                <div class="field">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" wire:model="emergency_name">
                    @error('emergency_name') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label>Relationship <span class="req">*</span></label>
                    <input type="text" wire:model="emergency_relationship" placeholder="e.g. Aunt, Uncle">
                    @error('emergency_relationship') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Phone Number <span class="req">*</span></label>
                    <input type="tel" wire:model="emergency_phone">
                    @error('emergency_phone') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        @endif

        {{-- ── Step 4: Review & submit ── --}}
        @if($step === 4)
        <div class="form-card">
            <div class="step-label">Step 4 of {{ $totalSteps }}</div>
            <h2 class="step-title">Review & Submit</h2>

            @if($student_photo)
            <div style="text-align:center;margin-bottom:16px">
                <img src="{{ $student_photo->temporaryUrl() }}"
                     style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid var(--c-border)"
                     alt="Passport">
                <div style="font-size:11px;color:var(--c-text-3);margin-top:4px">Passport photograph</div>
            </div>
            @endif

            <div class="review-section">
                <div class="review-section-title">Child's Details</div>
                <div class="review-row">
                    <span class="review-key">Full Name</span>
                    <span class="review-val">{{ $student_first_name }} {{ $student_other_name }} {{ $student_last_name }}</span>
                </div>
                <div class="review-row">
                    <span class="review-key">Gender</span>
                    <span class="review-val">{{ $student_gender }}</span>
                </div>
                <div class="review-row">
                    <span class="review-key">Date of Birth</span>
                    <span class="review-val">{{ $student_dob ? \Carbon\Carbon::parse($student_dob)->format('d M Y') : '—' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-key">Class Applied For</span>
                    <span class="review-val">{{ $class_applied_for }}</span>
                </div>
                @if($medical_notes)
                <div class="review-row">
                    <span class="review-key">Medical Notes</span>
                    <span class="review-val">{{ Str::limit($medical_notes, 60) }}</span>
                </div>
                @endif
            </div>

            <div class="review-section">
                <div class="review-section-title">Primary Parent</div>
                <div class="review-row">
                    <span class="review-key">Name</span>
                    <span class="review-val">{{ $parent1_name }}</span>
                </div>
                <div class="review-row">
                    <span class="review-key">Email</span>
                    <span class="review-val">{{ $parent1_email }}</span>
                </div>
                <div class="review-row">
                    <span class="review-key">Phone</span>
                    <span class="review-val">{{ $parent1_phone }}</span>
                </div>
            </div>

            <p style="font-size:12px;color:var(--c-text-3);line-height:1.6;margin-top:16px">
                By submitting this form you confirm that the information provided
                is accurate. The school will review and contact you within 1–2 business days.
            </p>
        </div>
        @endif

        {{-- Navigation buttons --}}
        <div class="btn-row">
            @if($step > 1)
                <button class="btn btn-ghost" wire:click="prevStep">← Back</button>
            @else
                <div></div>
            @endif

            @if($step < $totalSteps)
                <button class="btn btn-primary" wire:click="nextStep">
                    Continue →
                </button>
            @else
                <button class="btn btn-primary" wire:click="submit"
                        wire:loading.attr="disabled" wire:loading.class="opacity-50">
                    <span wire:loading.remove>Submit Enrolment</span>
                    <span wire:loading>Submitting...</span>
                </button>
            @endif
        </div>

    </div>
@endif
</div>
