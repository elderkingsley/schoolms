<?php

namespace App\Livewire\Admin;

use App\Models\SchoolSetting;
use App\Models\ParentGuardian;
use App\Jobs\ProvisionParentWalletJob;
use App\Jobs\ProvisionJuicyWayWalletJob;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

/**
 * SchoolSettings — super_admin/admin only.
 *
 * Allows admins to configure:
 *   - School name, tagline, address, email, phone, website
 *   - School logo (uploaded image, shown on all PDFs)
 *   - Invoice payment instructions (bank name, account name, account number)
 *   - Wallet provider (BudPay / JuicyWay) for virtual account provisioning
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

    // Wallet provider
    public string  $wallet_provider = 'budpay';
    public array   $available_providers = ['budpay', 'juicyway'];
    public bool    $showProviderConfirmModal = false;
    public ?string $pendingProvider = null;
    public int     $parentsNeedingProvisioning = 0;

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

        // Load wallet provider: admin override first, then config default
        $this->wallet_provider = $settings['wallet_provider']
            ?? config('services.wallet.default', 'budpay');

        $this->current_logo = $settings['school_logo'] ?? null;
    }

    /**
     * When provider dropdown changes, check if we need to show confirmation modal.
     */
    public function updatedWalletProvider(string $value): void
    {
        if ($value === $this->getCurrentProvider()) {
            return;
        }

        // Count parents who need provisioning for the new provider
        $this->pendingProvider = $value;
        $this->parentsNeedingProvisioning = $this->countParentsNeedingProvider($value);
        $this->showProviderConfirmModal = true;
    }

    /**
     * Confirm provider change and dispatch backfill jobs.
     */
    public function confirmProviderChange(): void
    {
        if (! $this->pendingProvider) {
            $this->showProviderConfirmModal = false;
            return;
        }

        // Save the new provider setting
        SchoolSetting::set('wallet_provider', $this->pendingProvider);
        $this->wallet_provider = $this->pendingProvider;

        // Dispatch backfill jobs for parents missing this provider's account
        $count = $this->dispatchBackfillJobs($this->pendingProvider);

        Log::info("SchoolSettings: wallet provider changed to {$this->pendingProvider}", [
            'admin_id' => auth()->id(),
            'parents_provisioned' => $count,
        ]);

        session()->flash('success', "Wallet provider updated to " . ucfirst($this->pendingProvider) . ". {$count} parent account(s) queued for provisioning.");

        $this->showProviderConfirmModal = false;
        $this->pendingProvider = null;
        $this->parentsNeedingProvisioning = 0;
    }

    /**
     * Cancel provider change.
     */
    public function cancelProviderChange(): void
    {
        $this->wallet_provider = $this->getCurrentProvider();
        $this->showProviderConfirmModal = false;
        $this->pendingProvider = null;
        $this->parentsNeedingProvisioning = 0;
    }

    /**
     * Get the current provider from settings or config.
     */
    protected function getCurrentProvider(): string
    {
        return SchoolSetting::get('wallet_provider')
            ?? config('services.wallet.default', 'budpay');
    }

    /**
     * Count parents who need provisioning for a given provider.
     */
    protected function countParentsNeedingProvider(string $provider): int
    {
        return ParentGuardian::whereNotNull('user_id')
            ->get()
            ->filter(fn($parent) => $parent->needsProviderAccount($provider))
            ->count();
    }

    /**
     * Dispatch backfill jobs for parents missing the provider's account.
     * Returns the number of jobs dispatched.
     */
    protected function dispatchBackfillJobs(string $provider): int
    {
        $jobClass = match($provider) {
            'juicyway' => ProvisionJuicyWayWalletJob::class,
            'budpay'   => ProvisionParentWalletJob::class,
            default    => null,
        };

        if (! $jobClass) {
            return 0;
        }

        $parents = ParentGuardian::whereNotNull('user_id')
            ->get()
            ->filter(fn($parent) => $parent->needsProviderAccount($provider));

        foreach ($parents as $parent) {
            $jobClass::dispatch($parent)->onQueue('provisioning');
        }

        return $parents->count();
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
            // Note: wallet_provider is saved in confirmProviderChange(), not here
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
