<?php

namespace Tests\Feature;

use App\Jobs\PushInvoiceToPayGridJob;
use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeeItem;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Notifications\InvoiceUpdatedNotification;
use App\Services\FeeService;
use App\Services\InvoiceAdjustmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InvoiceAdjustmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_invoice_adjustment_creates_credit_notifies_parent_and_queues_paygrid_sync(): void
    {
        Queue::fake();
        Notification::fake();

        $admin = User::factory()->create(['user_type' => 'admin']);
        $this->actingAs($admin);

        [$parent, $student, $invoice] = $this->createParentStudentInvoiceGraph();
        $invoice->update(['sent_at' => now()]);

        app(FeeService::class)->recordPayment(
            invoice: $invoice,
            amount: 100,
            method: 'Cash',
            reference: 'MANUAL-PAID-001',
            recordedBy: $admin->id,
        );

        $item = $invoice->items()->firstOrFail();

        app(InvoiceAdjustmentService::class)->adjust(
            invoice: $invoice,
            action: 'item_amount_changed',
            mutation: fn () => $item->update(['amount' => 80]),
            metadata: ['test' => true],
        );

        $invoice->refresh();

        $this->assertSame('80.00', (string) $invoice->total_amount);
        $this->assertSame('100.00', (string) $invoice->amount_paid);
        $this->assertSame('0.00', (string) $invoice->balance);
        $this->assertSame('paid', $invoice->status);
        $this->assertSame(-20.0, $invoice->displayBalance());

        $this->assertDatabaseHas('fee_invoice_adjustments', [
            'fee_invoice_id' => $invoice->id,
            'adjusted_by' => $admin->id,
            'action' => 'item_amount_changed',
            'old_total_amount' => '100.00',
            'new_total_amount' => '80.00',
            'credit_adjustment_amount' => '20.00',
            'paygrid_sync_status' => 'queued',
        ]);

        $this->assertDatabaseHas('parent_credits', [
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'origin_fee_invoice_id' => $invoice->id,
            'total_amount' => '20.00',
            'balance_amount' => '20.00',
            'status' => 'open',
        ]);

        Queue::assertPushed(PushInvoiceToPayGridJob::class, 1);
        Notification::assertSentTo($parent->user, InvoiceUpdatedNotification::class);
    }

    public function test_later_adjustment_reduces_unused_invoice_adjustment_credit(): void
    {
        Notification::fake();
        Queue::fake();

        $admin = User::factory()->create(['user_type' => 'admin']);
        $this->actingAs($admin);

        [$parent, , $invoice] = $this->createParentStudentInvoiceGraph();

        app(FeeService::class)->recordPayment(
            invoice: $invoice,
            amount: 100,
            method: 'Cash',
            reference: 'MANUAL-PAID-002',
            recordedBy: $admin->id,
        );

        $item = $invoice->items()->firstOrFail();
        $service = app(InvoiceAdjustmentService::class);

        $service->adjust($invoice, 'item_amount_changed', fn () => $item->update(['amount' => 80]));
        $service->adjust($invoice->fresh(), 'item_amount_changed', fn () => $item->refresh()->update(['amount' => 90]));

        $invoice->refresh();
        $credit = $parent->credits()->where('origin_fee_invoice_id', $invoice->id)->firstOrFail();

        $this->assertSame('90.00', (string) $invoice->total_amount);
        $this->assertSame('10.00', (string) $credit->total_amount);
        $this->assertSame('10.00', (string) $credit->balance_amount);
        $this->assertSame(-10.0, $invoice->displayBalance());
    }

    private function createParentStudentInvoiceGraph(): array
    {
        $parentUser = User::factory()->create([
            'user_type' => 'parent',
            'email' => 'parent-adjustment@example.com',
        ]);

        $parent = ParentGuardian::create([
            'user_id' => $parentUser->id,
            'budpay_account_number' => '0123456789',
            'budpay_bank_name' => 'Test Bank',
            'budpay_bank_code' => '999',
            'budpay_wallet_status' => 'active',
        ]);

        $student = Student::create([
            'admission_number' => 'ADM-ADJ-001',
            'first_name' => 'Adjusted',
            'last_name' => 'Student',
            'gender' => 'Male',
            'date_of_birth' => '2015-01-01',
            'status' => 'active',
        ]);

        $parent->students()->attach($student->id, [
            'relationship' => 'Guardian',
            'is_primary_contact' => true,
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'is_active' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First',
            'is_active' => true,
        ]);

        $invoice = FeeInvoice::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'total_amount' => 100,
            'amount_paid' => 0,
            'balance' => 100,
            'status' => 'unpaid',
        ]);

        $feeItem = FeeItem::create([
            'name' => 'Tuition',
            'type' => 'compulsory',
            'is_active' => true,
        ]);

        FeeInvoiceItem::create([
            'fee_invoice_id' => $invoice->id,
            'fee_item_id' => $feeItem->id,
            'item_name' => 'Tuition',
            'amount' => 100,
            'added_by' => 'system',
        ]);

        return [$parent, $student, $invoice];
    }
}
