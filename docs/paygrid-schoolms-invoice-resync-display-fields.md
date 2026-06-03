# PayGrid / SchoolMS Invoice Resync Account Field Handoff

## Context

SchoolMS sends already-issued school fee invoices to PayGrid using:

`POST /api/school-invoices`

PayGrid handles this in:

`/var/www/backyardfarms/paygrid10/app/Http/Controllers/Api/SchoolInvoiceController.php`

The sync payload includes the SchoolMS invoice id:

```php
'schoolms_invoice_id' => (string) $invoice->id
```

PayGrid correctly uses that id as the idempotency key together with the PayGrid organization. When an invoice already exists, PayGrid currently updates the existing invoice and returns:

```json
{ "status": "updated" }
```

So replaying the SchoolMS invoice push is safe and should not create duplicates.

## Current PayGrid Behavior

On an existing SchoolMS invoice, PayGrid updates these fields:

```php
'schoolms_account_number' => $primaryAccount ?: $existing->schoolms_account_number,
'schoolms_account_numbers' => $mergedAccounts,
```

These are the important backend matching fields.

PayGrid inflow matching checks:

```php
schoolms_account_number
schoolms_account_numbers
```

So after SchoolMS resyncs invoices, PayGrid should correctly match payments using the updated family/sibling account numbers.

## The Caveat

PayGrid invoices also have separate display/payment snapshot fields:

```php
payment_provider
payment_account_number
payment_bank_name
payment_bank_code
```

These are used by PayGrid-native invoice views, PDFs, emails, and mobile/API invoice display. They are separate from the SchoolMS matching fields.

The current SchoolMS invoice sync endpoint updates `schoolms_account_number` and `schoolms_account_numbers`, but it does not update `payment_account_number` / `payment_bank_name`.

## What Could Go Wrong

Backend settlement should work after resync because PayGrid matches inflows against `schoolms_account_number` and `schoolms_account_numbers`.

The risk is display mismatch:

- SchoolMS may show the corrected shared sibling/family account.
- PayGrid backend matching may also have the corrected account.
- But PayGrid’s own invoice PDF/email/UI may still show an older `payment_account_number`.

If parents or staff use the SchoolMS invoice page/email/PDF as the payment instruction, this is not a practical issue.

If parents or staff use PayGrid’s invoice page/email/PDF as the payment instruction, they may see and pay into an old account number.

That payment may still settle if the old account number remains in `schoolms_account_numbers`, but it defeats the goal of showing one consistent family account everywhere.

## Recommended PayGrid Fix

Update `SchoolInvoiceController` so SchoolMS invoice sync also updates PayGrid’s display/payment snapshot fields.

On both create and update paths, set:

```php
'payment_account_number' => $primaryAccount,
'payment_bank_name' => $validated['bank_name'] ?? null,
'payment_bank_code' => $validated['bank_code'] ?? null,
'payment_provider' => $validated['payment_provider'] ?? 'schoolms',
```

If PayGrid wants to preserve existing values when SchoolMS does not send bank details, use a fallback on update:

```php
'payment_account_number' => $primaryAccount ?: $existing->payment_account_number,
'payment_bank_name' => $validated['bank_name'] ?? $existing->payment_bank_name,
'payment_bank_code' => $validated['bank_code'] ?? $existing->payment_bank_code,
'payment_provider' => $validated['payment_provider'] ?? $existing->payment_provider ?? 'schoolms',
```

The important minimum fix is:

```php
'payment_account_number' => $primaryAccount,
```

That ensures PayGrid-native invoice displays show the same account number used for SchoolMS matching.

## Request Validation Change Needed In PayGrid

PayGrid should extend:

`/var/www/backyardfarms/paygrid10/app/Http/Requests/Api/StoreSchoolInvoiceRequest.php`

to accept optional display fields:

```php
'bank_name' => ['nullable', 'string', 'max:100'],
'bank_code' => ['nullable', 'string', 'max:20'],
'payment_provider' => ['nullable', 'string', 'max:30'],
```

Alternatively, PayGrid can skip bank/provider fields for now and only store `payment_account_number`.

## SchoolMS Changes Also Needed?

For the minimum PayGrid-side fix, no SchoolMS code change is strictly required.

SchoolMS already sends:

```php
'account_number' => $parent->active_account_number,
'account_numbers' => $allAccountNumbers,
```

So PayGrid can copy `account_number` into `payment_account_number`.

For the complete fix, yes, SchoolMS should also be updated to send the display metadata:

```php
'bank_name' => $parent->active_bank_name,
'bank_code' => $parent->active_bank_code,
'payment_provider' => App\Models\ParentGuardian::getActiveWalletProvider(),
```

SchoolMS currently has helpers for active account number and bank name. It does not currently expose an `active_bank_code` helper, so SchoolMS would need a small helper or inline provider-based selection for bank code.

## Recommended Implementation Order

1. PayGrid updates `SchoolInvoiceController` to set `payment_account_number` from the incoming `account_number`.
2. PayGrid optionally accepts and stores `bank_name`, `bank_code`, and `payment_provider`.
3. SchoolMS adds those optional fields to the invoice push payload.
4. SchoolMS replays sent invoices through `PushInvoiceToPayGridJob`.

## Safe Resync After Fix

From SchoolMS:

```bash
php artisan tinker
```

```php
use App\Models\FeeInvoice;
use App\Jobs\PushInvoiceToPayGridJob;

FeeInvoice::whereNotNull('sent_at')
    ->chunkById(50, function ($invoices) {
        foreach ($invoices as $invoice) {
            PushInvoiceToPayGridJob::dispatch($invoice->fresh());
        }

        sleep(60);
    });
```

This is duplicate-safe because PayGrid upserts by `schoolms_invoice_id`.

## Summary

PayGrid’s backend matching fields already resync safely.

The remaining issue is PayGrid-native invoice display fields. If PayGrid invoice views/PDFs/emails are used as payment instructions, they should be updated during SchoolMS invoice sync so they show the same shared sibling/family account as SchoolMS.
