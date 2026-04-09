<?php

namespace App\Livewire\Admin;

use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * SchoolSettings — super_admin/admin only.
 *
 * Allows admins to configure:
 *   - School name, tagline, address, email, phone, website
 *   - School logo (uploaded image, shown on all PDFs)
 *   - Invoice payment instructions (bank name, account name, account number)
 */
class SchoolSettings extends Component
{
    use WithFileUploads;

    // School identity
    public string  $school_name    = '';
    public string  $school_tagline = '';
    public string  $school_address = '';
    public string  $school_email   = '';
    public string  $school_phone   = '';
    public string  $school_website = '';

    // Invoice settings
    public string  $invoice_bank_name      = '';
    public string  $invoice_account_name   = '';
    public string  $invoice_account_number = '';
    public string  $invoice_payment_note   = '';

    // Logo
    public ?string $current_logo = null;
    public $logo; // uploaded file

    public function mount(): void
    {
        abort_if(
            ! in_array(auth()->user()->user_type, ['super_admin', 'admin']),
            403
        );

        $settings = SchoolSetting::allCached();

        $this->school_name    = $settings['school_name']    ?? '';
        $this->school_tagline = $settings['school_tagline'] ?? '';
        $this->school_address = $settings['school_address'] ?? '';
        $this->school_email   = $settings['school_email']   ?? '';
        $this->school_phone   = $settings['school_phone']   ?? '';
        $this->school_website = $settings['school_website'] ?? '';

        $this->invoice_bank_name      = $settings['invoice_bank_name']      ?? '';
        $this->invoice_account_name   = $settings['invoice_account_name']   ?? '';
        $this->invoice_account_number = $settings['invoice_account_number'] ?? '';
        $this->invoice_payment_note   = $settings['invoice_payment_note']   ?? '';

        $this->current_logo = $settings['school_logo'] ?? null;
    }

    public function save(): void
    {
        $this->validate([
            'school_name'           => 'required|string|max:100',
            'school_tagline'        => 'nullable|string|max:150',
            'school_address'        => 'nullable|string|max:255',
            'school_email'          => 'nullable|email|max:100',
            'school_phone'          => 'nullable|string|max:30',
            'school_website'        => 'nullable|string|max:100',
            'invoice_bank_name'     => 'nullable|string|max:100',
            'invoice_account_name'  => 'nullable|string|max:100',
            'invoice_account_number'=> 'nullable|string|max:20',
            'invoice_payment_note'  => 'nullable|string|max:300',
            'logo'                  => 'nullable|image|max:2048', // 2MB max
        ]);

        // Handle logo upload
        if ($this->logo) {
            // Delete old logo if exists
            if ($this->current_logo) {
                Storage::disk('public')->delete($this->current_logo);
            }

            $path = $this->logo->store('school', 'public');
            SchoolSetting::set('school_logo', $path);
            $this->current_logo = $path;
            $this->logo = null;
        }

        SchoolSetting::setMany([
            'school_name'            => trim($this->school_name),
            'school_tagline'         => trim($this->school_tagline),
            'school_address'         => trim($this->school_address),
            'school_email'           => trim($this->school_email),
            'school_phone'           => trim($this->school_phone),
            'school_website'         => trim($this->school_website),
            'invoice_bank_name'      => trim($this->invoice_bank_name),
            'invoice_account_name'   => trim($this->invoice_account_name),
            'invoice_account_number' => trim($this->invoice_account_number),
            'invoice_payment_note'   => trim($this->invoice_payment_note),
        ]);

        session()->flash('success', 'School settings saved.');
    }

    public function removeLogo(): void
    {
        if ($this->current_logo) {
            Storage::disk('public')->delete($this->current_logo);
            SchoolSetting::set('school_logo', null);
            $this->current_logo = null;
        }
    }

    public function render()
    {
        return view('livewire.admin.school-settings')
            ->layout('layouts.admin', ['title' => 'School Settings']);
    }
}
